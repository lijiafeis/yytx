<?php
namespace Admin\Controller;
use Think\Controller;

class JifenController extends ActionController{
	function __construct(){
		parent::__construct();
		//echo session('admin_id');
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'));
		}
		
	}
	function _empty(){
		echo "";exit;
	}
	function index(){
		if($_POST){
			$data = array(
				'subscribe_switch'=>$_POST['subscribe_switch'],
				'subscribe_number'=>$_POST['subscribe_number'],
				'qiandao_switch'=>$_POST['qiandao_switch'],
				'qiandao_number'=>$_POST['qiandao_number'],
				'qiandao_week'=>$_POST['qiandao_week'],
				'more_number'=>$_POST['more_number'],
				'subscribe_word'=>$_POST['subscribe_word'],
				'buy_per'=>$_POST['buy_per'],
			);
			if(!$_POST['subscribe_switch']){$data['subscribe_switch'] = 0;}
			if(!$_POST['qiandao_switch']){$data['qiandao_switch'] = 0;}
			if($_POST['jifen']){M('jifen_info')-> where(" id = '$_POST[jifen]' ") -> save($data);}else{M('jifen_info')-> add($data);}
			$this -> success('配置信息保存成功');
		}else{
			$jifen_info = M('jifen_info') -> find();
			F('jifen_info',$jifen_info,DATA_ROOT);
			$this->assign('jifen_info',$jifen_info);
			$this->display();
		}
		
	}
	
}
?>