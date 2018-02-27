<?php
namespace User\Controller;
use Think\Controller;

class DaijinController extends Controller{
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
	function index(){
		if(!I('get.daijin_id')){redirect(U('/shop/wap/index'));exit;}
		$daijin = M('shop_daijin');
		$daijin_info = $daijin -> getByDaijin_id(I('get.daijin_id'));
		//dump($daijin_info);
		if($daijin_info['daijin_date'] <= time()){$daijin_info['now_date'] = 1;}else{$daijin_info['now_date'] = 0;}
		$daijin_info['daijin_date'] = date("Y-m-d H:i",$daijin_info['daijin_date']);
		$daijin_info['now_number'] = $daijin_info['daijin_number'] - $daijin_info['sale_number'];
		$this -> assign('daijin_info',$daijin_info);
		$this -> display();
	}
	
	/* 代金券中心 */
	function center(){
		
		/* 查询出所有的代金券 */
		$shop_daijin = M('shop_daijin');
		$daijin_info = $shop_daijin ->where("daijin_number > 0") -> order("daijin_id desc")-> select();
		foreach($daijin_info as $k=>$v){
			$daijin_info[$k]['now_number'] = $v['daijin_number'] - $v['sale_number'];
		}
		$this -> assign('daijin_info',$daijin_info);
		$this -> assign('empty','<div style="text-align:center;padding-top:30px;"><img src="/public/images/daijin-empty.png" width="20%"></div>');
		/* 查询出用户的代金券订单 */
		$daijin_order = M('daijin_order') -> where("user_id = '$this->user_id' and is_true = 1 and state = 0") ->order("order_id desc")-> select();
		foreach($daijin_order as $k=>$v){
			$daijin_order[$k]['info'] = $shop_daijin -> getByDaijin_id($v['daijin_id']);
		}
		$this -> assign('daijin_order',$daijin_order);
		$this -> display();
	}
}

?>