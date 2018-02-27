<?php
namespace User\Controller;
use Think\Controller;

class CenterController extends Controller{
	function __construct(){
		parent::__construct();
		$this->user_id = session('xigua_user_id');
		if(!$this->user_id){
			$redirect_uri='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			redirect('http://'.$_SERVER['HTTP_HOST'].U('/wxapi/oauth/index/')."?surl=".$redirect_uri);exit;
		}
		$this -> assign('app_info',F('config_info','',DATA_ROOT));
		$weixin = A("Wxapi/Weixin");
		$signPackage=$weixin->getSignPackage();
		$this->assign("signPackage",$signPackage);
		$this->assign("action_name",ACTION_NAME);
	}
	/* 用户中心页 */
	function user(){
		$users = D('Xigua/users');
		$this->assign('user_info',$users->get_user_info_by_user_id($this->user_id));
		$hb_fee = M('hbrecord')-> where("user_id = '$this->user_id'")->sum('hongbao_fee');
		if(!$hb_fee){$hb_fee = 0;}
		$pay_num = M('shop_order')->where("user_id = '$this->user_id' and is_true = 0 ") -> count();
		$wuliu_num = M('shop_order')->where("user_id = '$this->user_id' and state = 1 ") -> count();
		$this->assign('pay_num',$pay_num);
		$this->assign('wuliu_num',$wuliu_num);
		$this->assign('hb_fee',$hb_fee);
		$system_notice_num = M('send_message') -> where("system_show = 1") ->  count();
		$this->assign('system_notice_num',$system_notice_num);
		$this->display();
	}
	/* 系统消息页面 */
	function system_notice(){
		$system_notice_info = M('send_message') -> where("system_show = 1")-> order('id desc') ->  select();
		foreach($system_notice_info as $k=>$vv){
			if($vv['type'] == 'news'){
				$news = M('news');
				$news_list = explode(",",$vv['news_id']);
				$i = 0;
				foreach($news_list as $v){
					$info = $news->where(" id = '$v' ")->find();
					if($info != null){
						
						$system_notice_info[$k]['news_info'][$i] = $info;
						$i++;
					}
					
				}
				
			}
			$system_notice_info[$k]['time'] = date("Y-m-d H:i:s",$vv['time']);
		}
		$this -> assign('system_notice_info',$system_notice_info);
		$this -> display();
	}
	function system_notice_ajax(){
		$send_message = M('send_message');
		$pagecount = 20;
		$where['system_show'] = 1;
		$count = $send_message ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$system_notice_info = $send_message->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("id desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		foreach($system_notice_info as $k=>$vv){
			if($vv['type'] == 'news'){
				$news = M('news');
				$news_list = explode(",",$vv['news_id']);
				$i = 0;
				foreach($news_list as $v){
					$info = $news->where(" id = '$v' ")->find();
					if($info != null){
						
						$system_notice_info[$k]['news_info'][$i] = $info;
						$i++;
					}
					
				}
				
			}
			$system_notice_info[$k]['time'] = date("Y-m-d H:i",$vv['time']);
		}
		$this -> assign('system_notice_info',$system_notice_info);
		$this -> assign('empty','<div class="weui-loadmore weui-loadmore_line"><span class="weui-loadmore__tips">暂无数据</span></div>');
		$this->display();
	}
	
	
	/* 团队页面 */
	function team(){
		$users = D('Xigua/users');
		$this->assign('team_num',$users->get_team_num($this->user_id));
		
		$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
		$this->assign('fenxiao_info',$fenxiao_info);
		$this->display();
	}
	/* 层级团队人员列表 */
	function team_ajax(){
		if(!I('get.p')){exit;}
		$id = I('get.p');
		$users = D('Xigua/users');
		$user_list =$users->get_team_level($this->user_id,$id);
		rsort($user_list);
		$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
		$this->assign('fenxiao_info',$fenxiao_info);
		$this->assign('user_list',$user_list);
		$this->assign('id',$id);
		$this->display();
	}
	
	/* 订单详情页 */
	function order(){
		$state = I('state');
		if(!$state){$state = 'all';}
		$this->assign('state',$state);
		$this -> display();
	}
	function order_ajax(){
		$shop_order = M('shop_order');$shop_order_detail = M('shop_order_detail');
		$pagecount = 10;
		$where['user_id'] = $this->user_id;
		if(I('type') == 'pay'){$where['is_true'] = 0;}elseif(I('type') == 'wuliu'){$where['state']=1;}elseif(I('type') == 'zan'){$where['state']=2;}
		$count = $shop_order ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$order_info = $shop_order->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("order_id desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		foreach($order_info as $k=>$v){
			$pay_id = $v['pay_id'];
			$order_info[$k]['info'] = $shop_order_detail -> where(" pay_id = '$pay_id' ") -> select();
		}
	$this->assign('order_empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe60e;</p>没有任何发现</div>');
		$this ->assign("order_info",$order_info);
		$this -> display();
	}
	/* 确认收货 */
	function order_shouhuo(){
		$order_id = I('post.order_id');
		$pay_id = I('post.pay_id');
		if(!$order_id){exit;}
		/* 发送模板消息通知 */
		$openid = M('users') -> getFieldByUser_id($this->user_id,'openid');
		$order_info = M('shop_order_detail') -> field("order_sn,good_name,good_num") -> where(" pay_id = '$pay_id' ") -> select();
			foreach($order_info as $vv){
				$good_name .=$vv['good_name'].".";
				$good_num =$good_num + $vv['good_num'];
			}
		$template = A("Pay/Template");
		$url = 'http://'.$_SERVER['SERVER_NAME'].U('/user/center/order')."?state=zan";
		$template->send_shop($order_info[0]['order_sn'],$good_name.'*'.$good_num,'已确认收货',$openid,$url);
		M('shop_order')-> where("order_id = '$order_id'") -> setField('state',2);
		$arr= array();
		echo json_encode($arr);
	}
	
	/* 二维码生成 */
	public function qr(){
		$arr = array();
		if(empty($this->user_id)){$arr['success'] = 0;$arr['info'] = '网页会话过期，请重新打开';echo json_encode($arr);exit;}
		$user_info = M('users') -> field("user_id,agent,openid") -> where("user_id = '$this->user_id' ") -> find();
		
		if($user_info['agent'] == 0){
			$arr['info'] = C('USER_QR_NOTICE');
		}else{
			$weixin = A('Wxapi/Weixin');
			$qrcode_info = M('qrcode') -> getByUser_id($this->user_id);
			if($qrcode_info['update_time'] == 0){
				$media_id = $weixin -> get_qr_path_new($this->user_id,$qrcode_info['scene_id']);
			}else{
				if(file_exists('Public/qr_path/'.$this->user_id.'.jpg')){
				
					$media_id = $qrcode_info['media_id'];
					//判断media_id是否过期以及临时二维码是否已经超过30天
					$time1 = time() - $qrcode_info['created_at'];
					if($time1>= 259200){
						//临时素材已过期需重新上传
						$media=$weixin->media_pic('Public/qr_path/'.$this->user_id.'.jpg');
						$data['created_at'] = $media['created_at'];
						$data['media_id'] = $media_id = $media['media_id'];
						M('qrcode') -> where(" scene_id = '$qrcode_info[scene_id]' ") -> save($data);
					}
					$time2 = time() - $qrcode_info['update_time'];
					if($time2 >= 2592000 || $qrcode_info['update_time'] == 0){
						//参数二维码超过了30天，需重新生成
						$media_id = $weixin -> get_qr_path_new($this->user_id,$qrcode_info['scene_id']);
					}	
				}else{
					$media_id = $weixin -> get_qr_path_new($this->user_id,$qrcode_info['scene_id']);
				}
			}
			
			$res = $weixin -> send_pic($user_info['openid'],$media_id);
			if($res['errmsg'] == 'ok'){
				$arr['success'] = 1;
			}else{
				$arr['success'] = 0;$arr['info'] = '发送失败，请点击重试一次'.$media_id;
			}
			
		}
		echo json_encode($arr);
	}
	public function updtuserinfo(){
		if(empty($this->user_id)){exit;}
		$arr = array();
		$users = M('users');
		$openid = $users -> getFieldByUser_id($this->user_id,'openid');
		$weixin = A("Wxapi/Weixin");
		$info = $weixin -> get_user_info($openid);
		$res = $users -> where(" user_id = '$this->user_id' ") -> field("nickname,headimgurl") -> save($info);
		if($res){
			$arr['success'] = 1;
			$arr['info'] = "已成功更新基本信息，请刷新页面";
		}else{
			$arr['success'] = 0;
			$arr['info'] = "当您微信昵称或头像有变动时再来更新";
		}
		
		echo json_encode($arr);
	}
	public function resetqr(){
		if(empty($this->user_id)){exit;}
		$arr = array();
		$users = M('users');
		$user_agent = $users -> getFieldByUser_id($this->user_id,'agent');
		if($user_agent == 0){
			$arr['success'] = 0;
			$arr['info'] = C('USER_QR_NOTICE');
		}else{
			M('qrcode') -> where(" user_id = '$this->user_id' ") -> setField('update_time',0);
			$arr['success'] = 1;
			$arr['info'] = "重置成功！请点击二维码重新获取";
		}
		
		echo json_encode($arr);
	}
}