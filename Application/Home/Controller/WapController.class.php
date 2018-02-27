<?php
namespace Home\Controller;
use Think\Controller;
class WapController extends Controller {
	
	function __construct(){
		parent::__construct();//session(null);exit;
		/* $this->user_id = session('xigua_user_id');
		if(!$this->user_id){
			$redirect_uri='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if($_GET['uid'] > 0){session('uid',$_GET['uid']);}
			redirect('http://'.$_SERVER['HTTP_HOST'].U('/wxapi/oauth/index/')."?surl=".$redirect_uri);exit;
		} */
		$this -> assign('app_info',F('config_info','',DATA_ROOT));
		$weixin = A("Wxapi/Weixin");
		$signPackage=$weixin->getSignPackage();
		$this->assign("signPackage",$signPackage);
	}
	
	
    public function index(){
		$news_id = $_GET['id'];
		if($news_id == ''){exit;}
		$newsinfo = M('news')->where(" id = '$news_id' ")->find();
		$newsinfo['createtime'] = date("Y-m-d",$newsinfo['createtime']);
		if($newsinfo['out_link'] != null){redirect($newsinfo['out_link']);exit;}
		$this->assign("newsinfo",$newsinfo);
		$user_info = M('users')->getByUser_id($this->user_id);
		$this -> assign('user_info',$user_info);
		if($_GET['uid'] > 0 || session('uid')){
			if(!$_GET['uid']){$uid = session('uid');}else{$uid = $_GET['uid'];}
			$uid_nickname = M('users') -> getFieldByUser_id($uid,'nickname');
			$this -> assign('uid_nickname',$uid_nickname);
			$this -> assign('uid',$uid);
			session('uid',null);
		}else{
			$this -> assign('uid','0');
		}
		/* 判定文章阅读权限 */
		$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
		switch($newsinfo['read_type']){
			case 1:
			if($user_info['agent'] == 0){$read_state = 0;$read_info = '开放人群为'.$fenxiao_info['fenxiao_name'].'。您是'.C('USER_NORMAL_NAME');}else{$read_state = 1;}
			break;
			case 2:
			if($user_info['subscribe'] == 0){$read_state = 0;$read_info = '开放人群为平台关注会员。您还未关注哦';}else{$read_state = 1;}
			break;
			case 3:
			$read_state = 1;
			break;
			case 0:
			$read_state = 0;$read_info = '级别不够';
			break;
			default:
			$read_state = 0;$read_info = '未知原因';
			break;
		}
		if($read_state == 0){
			$info = 'error_info:'.$read_info;
			$this->assign('info',$info);
			$this->display('error');exit;
		}
		/* 外链跳转 */
		
		//echo date("Y-m-d H:i:s",$time2);
		
		
		
		
		
		/* 分享权限 */
		if($newsinfo['is_share'] != 1){
			$this->assign('is_share',1);
		}else{$this->assign('is_share',0);}
		/* 赞赏权限 */
		if($newsinfo['is_shang'] != 1){
			$this->assign('is_shang',1);
		}else{
			$this->assign('is_shang',0);
			/* 查询赞赏人员列表 */
			$shang_list = M('news_shang') -> where("new_id = '$news_id'") -> select();
			$users = M('users');
			foreach($shang_list as $k=>$v){
				$shang_user_list[$k] = $users -> getFieldByUser_id($v['user_id'],'headimgurl');
			}
			$shang_user_list = array_unique($shang_user_list);
			$this-> assign('shang_user_list_num',count($shang_user_list));
			$this-> assign('shang_user_list',$shang_user_list);
		}
		$time1 = strtotime(date("Y-m-d",time()));//$time2 = strtotime(date("Y-m-d",strtotime('+1 day')));
		/* 增加阅读量 */
		$news_read = M('news_read');
		$read_info = $news_read -> where("user_id = '$this->user_id' and new_id = '$news_id' and time >= '$time1' ")->find();
		if($read_info == null){
			M('news')->where("id='$newsinfo[id]'") -> setInc('read_num');
			$news_read -> add(array('new_id'=>$news_id,'user_id'=>$this->user_id,'time'=>time()));
		}
		/* 今天是否点过赞 */
		$zan_info = M('news_zan') -> where("user_id = '$this->user_id' and new_id = '$news_id' and time >= '$time1' ")->find();
		$this->assign('zan_info',$zan_info);
		$comment_info = array(array(
			'user_info'=>array('nickname'=>'测试')
		));
		//dump($comment_info);
		$this->display();
	}
	/* 用户打赏 */
	function dashang(){
		$uid = $_POST['uid'];
		$new_id = $_POST['new_id'];	
		$number = $_POST['number'];
		//if($uid == 0){$uid = 0;}
		if(!$uid){$uid = 0;}
		if(!$new_id){exit;}
		if(!$number){exit;}
		if($number >20000 || $number < 1){exit;}
		//$arr = $this->user_id.'打赏了'.$new_id.'给'.$uid.'钱'.$number;
		$attach = $uid.",".$new_id.",".$this->user_id;
		$total_fee = $number*100;
		$weixin = A("Wxapi/Weixin");
		$out_trade_no = "2016".$this->user_id.time();//订单号
		$notify_url = "http://".$_SERVER['SERVER_NAME'].U('/Pay/Notify/dashang');//交易成功后通知地址
		$openid = M('users') -> getFieldByUser_id($this->user_id,'openid');//openid信息
		$prepay_id = $weixin -> get_prepay_id($openid,$total_fee,$out_trade_no,$notify_url,$attach,"会员充值");
		$paysign = $weixin->paysign($prepay_id);
		
		$arr = $paysign;
		$arr['timeStamp'] = (string)$arr['timeStamp'];
		$arr['success'] = 1;
		echo json_encode($arr);
	}
	
	/* 用户分享 */
	function share(){
		$new_id = $_POST['id'];
		$data = array(
			'new_id'=>$new_id,
			'user_id'=>$this->user_id,
			'time'=>time()
		); 
		M('news')->where("id = '$new_id' ")->setInc('share');
		M('news_share') -> add($data);
		$arr = array();
		echo json_encode($arr);
	}
	
	/* 用户点赞 */
	function zan(){
		$id = $_POST['news'];
		if(!$id){exit;}
		$news_zan = M('news_zan');$news = M('news');
		/* 查询是否点赞 */
		$time1 = strtotime(date("Y-m-d",time()));
		$time2 = strtotime(date("Y-m-d",strtotime('+1 day')));
		$zan_info = $news_zan -> where("user_id = '$this->user_id' and new_id = '$id' and time >= '$time1' ")->find();
		if($zan_info == null){
			$data = array(
				'new_id'=>$id,
				'user_id'=>$this->user_id,
				'time'=>time()
			);
			$news_zan -> add($data);
			$news -> where("id = '$id' ") -> setInc('zan');
			$arr['type'] = 1;
		}else{
			$news_zan -> where(" id = '$zan_info[id]' ") -> delete();
			$news -> where("id = '$id' ") -> setDec('zan');
			$arr['type'] = 0;
		}
		echo json_encode($arr);
		
	}
	function comment(){
		$content = $_POST['content'];
		$new_id = $_POST['id'];
		if(!$content){exit;}
		if(!$new_id){exit;}
		
		$data = array(
			'new_id'=>$new_id,
			'user_id'=>$this->user_id,
			'content'=>$content,
			'time'=>time(),
		);
		$res = M('news_comment')->add($data);
		$user_info = M('users') -> field("headimgurl,nickname") -> where("user_id = '$this->user_id'") -> find();
		$comment_info = array(array(
			'id'=>$res,'user_info'=>$user_info,'content'=>$content,'time'=>date('m-d H:i',time()),'zan_num'=>0
		));
		$this ->assign("comment_info",$comment_info);
		$this->display('wap_get_comment');
	}
	
	function get_comment(){
		$new_id = I('get.id');
		if(!$new_id){exit;}
		$news_comment = M('news_comment');$users = M('users');
		$pagecount = 10;
		$where['new_id'] = $new_id;
		$count = $news_comment ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$comment_info = $news_comment->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("time desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		$news_comment_zan = M('news_comment_zan');
		foreach($comment_info as $k=>$v){
			$comment_info[$k]['user_info'] = $users -> field("headimgurl,nickname") -> where("user_id = '$v[user_id]'") -> find();
			$comment_info[$k]['time'] = date('m-d H:i',$v['time']);
			$zan_info = $news_comment_zan -> where("user_id = '$this->user_id' and comment_id = '$v[id]' ") -> find();
			if($zan_info != null){
				$comment_info[$k]['zan'] = 1;
			}else{
				$comment_info[$k]['zan'] = 0;
			}
		}
		$this ->assign("comment_info",$comment_info);
		$this -> display();
	}
	
	function comment_zan(){
		$new_id = $_POST['news'];
		$comment_id = $_POST['comment_id'];
		if(!$new_id){exit;}
		if(!$comment_id){exit;}
		$news_comment = M('news_comment');$news_comment_zan = M('news_comment_zan');
		/* 查询是否点赞 */
		$zan_info = $news_comment_zan -> where("user_id = '$this->user_id' and comment_id = '$comment_id' ") -> find();
		if($zan_info == null){
			/* 增加赞数 */
			$zan_data = array(
				'comment_id'=>$comment_id,
				'new_id'=>$new_id,
				'user_id'=>$this->user_id,
				'time'=>time()
			);
			$res = $news_comment_zan -> add($zan_data);
			if($res){$news_comment->where("id = '$comment_id' ") ->setInc('zan_num');$arr['type'] = 1;echo json_encode($arr);}
		}else{
			/* 减去赞数 */
			$res = $news_comment_zan -> where(" id = '$zan_info[id]'")->delete();
			if($res){$news_comment->where("id = '$comment_id' ") ->setDec('zan_num');$arr['type'] = 0;echo json_encode($arr);}
		}
	}
	
	
	
	
	
}