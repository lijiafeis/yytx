<?php
namespace User\Controller;
use Think\Controller;

class GongpaiController extends Controller{
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
	
	function test(){
		//$daijin_id = 1; $user_id = 2;
		$gongpai_info = F('gongpai_info','',DATA_ROOT);
		if($gongpai_info['gongpai_switch'] == 1){
			$state = 0;
			switch($gongpai_info['gongpai_exist']){
				case 3:
				if($daijin_id == $gongpai_info['daijin_id']){$state = 1;}else{$state = 0;}
				break;
				default:
				$state = 0;
				break;
			}
			
			if($state == 1){
				$gongpai = A("Xigua/Gongpai");
				$gongpai -> __construct($user_id,'daijin');
				$gongpai -> index();
			}
		}
	}
	
	
	/* 应用首页视图 */
	function index(){
		$users = D('Xigua/users');$hbrecord = M('hbrecord');
		$this->assign('user_info',$users->get_user_info_by_user_id($this->user_id));
		/* 红包总金额 */
		$hb_total = $hbrecord -> where(" user_id = '$this->user_id' ") -> sum('hongbao_fee');
		if(!$hb_total){$hb_total = 0;}
		/* 查询卡位列表 */
		$gongpai_user = M('gongpai_user');
		$pagecount = 10;
		$where['user_id'] = $this->user_id;
		$count = $gongpai_user ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$dianwei_info = $gongpai_user->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("dianwei_id desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe60e;</p>没有任何发现</div>');
		
		$chuju_num = $gongpai_user -> where("user_id='$this->user_id' and type = 1") ->count();
		//dump($dianwei_info);
		$gongpai_info = F('gongpai_info','',DATA_ROOT);
		$this->assign('gongpai_info',$gongpai_info);
		
		$this->assign('count',$count);
		$this->assign('chuju_num',$chuju_num);
		$this->assign('dianwei_info',$dianwei_info);
		$this->assign('hb_total',$hb_total);
		$this->display();
	}
	function dianwei_list(){
		$gongpai_user = M('gongpai_user');
		$pagecount = 10;
		$where['user_id'] = $this->user_id;
		$count = $gongpai_user ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$dianwei_info = $gongpai_user->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("dianwei_id desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe60e;</p>没有任何发现</div>');
		$this ->assign("dianwei_info",$dianwei_info);
		$this->display();
	}
	
	/* 卡位详情 */
	function kawei(){
		$dianwei_id = I('get.dianwei_id');
		if(!dianwei_id){redirect(index);exit;}
		$gongpai_user = M('gongpai_user');
		/* 确认是否本人操作 */
		$dianwei_info = $gongpai_user -> where("user_id = '$this->user_id' and dianwei_id = '$dianwei_id'")->find();
		if($dianwei_info == null){echo '<div style="font-size:6em;text-align:center;margin-top:10%;">Error</div><br /><br /><div style="font-size:3em;text-align:center;">该点位不属于您！</div>';exit;}
		
		/* 确认目前的公排方式 */
		$gongpai_info = F('gongpai_info','',DATA_ROOT);
		if(!$gongpai_info){
			$gongpai_info = M('gongpai_info')->find();
			F('gongpai_info',$gongpai_info,DATA_ROOT);
		}
		$gongpai_level = $gongpai_info['gongpai_level'];
		$gongpai_number = $gongpai_info['gongpai_number'];
		
		/* 根据层级计算出局总人数 */
		$chuju_all_num = 0;
		for($i=1;$i<=$gongpai_level;$i++){
			$str = pow($gongpai_number,$i);
			$chuju_all_num = $chuju_all_num + $str;
		}
		
		/* 该点位当前进展数 */
		$now_num = M('gongpai_contact')->where("user_id = '$dianwei_id' ") -> count();
		if($chuju_all_num == $now_num){$name = '已出局';}else{$name = '进行中';}
		$this->assign('name',$name);
		$this->assign('now_num',$now_num);
		$this->assign('gongpai_state',$gongpai_number);
		$this->assign('chuju_all_num',$chuju_all_num);
		$this->assign('dianwei_id',$dianwei_id);
		$this->display();
	}
	
	function enter(){
		$order_info = M('shop_order_detail') -> field("good_id,good_name,good_profit,good_num,pay_id") -> where(" user_id = '$this->user_id' ") -> select();
		if($order_info == null){echo '没有发现消费记录';exit;}
		$gongpai_info = F('gongpai_info','',DATA_ROOT);
		if($gongpai_info['gongpai_switch'] == 1){
			$state = 0;
			switch($gongpai_info['gongpai_exist']){
				case 1:
				$state = 1;
				break;
				case 2:
				foreach($order_info as $v){
					if($v['good_id'] == $gongpai_info['gongpai_good_id']){
						$state = 1;
						break;
					}
				}
				break;
			}
			if($state == 1){
				$user_id = $this->user_id;
				$gongpai = A("Xigua/Gongpai");
				$gongpai -> __construct($user_id);
				$gongpai -> index();
			}
			
		}
		$this->display();
		
	}
	function ganen_list(){
		$hbrecord = M('hbrecord');$users = M('users');
		$dianwei_id = 0;
		$pagecount = 10;
		$count = $hbrecord ->where("user_id = '$this->user_id' and dianwei_id = '$dianwei_id'")-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$hongbao_list = $hbrecord->where("user_id = '$this->user_id' and dianwei_id = '$dianwei_id'")->limit($Page->firstRow.','.$Page->listRows)->order("time desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe60e;</p>没有任何发现</div>');
		
		
		foreach($hongbao_list as $k=>$v){
			$hongbao_list[$k]['from_nickname'] = $users -> getFieldByUser_id($v['from_user_id'],'nickname');
		}
		//dump($hongbao_list);
		$this->assign('hongbao_info',$hongbao_list);
		$this->display();
	}
	
	/* 红包列表 */
	function hongbao_list(){
		$hbrecord = M('hbrecord');$users = M('users');
		$dianwei_id = I('get.dianwei_id');
		if(!$dianwei_id){redirect(index);exit;}
		$pagecount = 10;
		$count = $hbrecord ->where("user_id = '$this->user_id' and dianwei_id = '$dianwei_id'")-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$hongbao_list = $hbrecord->where("user_id = '$this->user_id' and dianwei_id = '$dianwei_id'")->limit($Page->firstRow.','.$Page->listRows)->order("time desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe60e;</p>没有任何发现</div>');
		
		
		foreach($hongbao_list as $k=>$v){
			$hongbao_list[$k]['from_nickname'] = $users -> getFieldByUser_id($v['from_user_id'],'nickname');
		}
		//dump($hongbao_list);
		$this->assign('hongbao_info',$hongbao_list);
		$this->display();
	}
	
	function send_hongbao(){
			/* 派发红包 */
			$hb_id = I('post.id');
			if(!$hb_id){exit;}
			$hbrecord = M('hbrecord');
			if(!$this->user_id){exit;}
			/* 查询红包是否存在 */
			$hb_info = $hbrecord -> where("hb_id = '$hb_id' and user_id = '$this->user_id' ") -> find();
			if(!$hb_info){exit;}$arr = array();
			/* 查询红包是否已发放 */
			if($hb_info['is_true'] != 0){$arr['success'] = 0;$arr['info'] = '该红包已领取过，请勿重复领取';echo json_encode($arr);exit;}
			$hongbao = A('Wxapi/Hongbao');
			$openid = M('users') -> getFieldByUser_id($this->user_id,'openid');
			$res = $hongbao -> index($hb_info['hongbao_fee'],$openid,'平台公排活动奖金');
			$ress = $this->objectToArray($res);
			if($ress['result_code'] == 'SUCCESS'){
				/* 判断是否发积分 */
				$gongpai_info = F('gongpai_info','',DATA_ROOT);
				if($gongpai_info['tojifen_switch'] == 1){
					$jifen_number = $hb_info['hongbao_fee']*0.01*$gongpai_info['jifen_per'];
					/* 增加用户积分，记录 */
					M('users') -> where("user_id = '$this->user_id' ")->setInc('jifen',$jifen_number);
					$jifen_data = array(
						'number'=>$jifen_number,
						'user_id'=>$this->user_id,
						'type'=>5,//参与公排返还积分，5
						'time'=>time()
					);
					M('jifen') -> add($jifen_data);
				}
				$arr['success'] = 1;
				/* 更改红包状态 */
				$hbrecord -> where("hb_id = '$hb_id'")->setField('is_true',1);
			}else{
				$arr['success'] = 0;$arr['err_info'] = $ress['return_msg'];
			}
			echo json_encode($arr);
	}
	private function objectToArray($e){
		$e=(array)$e;
		foreach($e as $k=>$v){
			if( gettype($v)=='resource' ) return;
			if( gettype($v)=='object' || gettype($v)=='array' )
				$e[$k]=(array)$this->objectToArray($v);
		}
		return $e;
	}
}