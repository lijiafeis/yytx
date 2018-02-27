<?php
namespace Admin\Controller;
use Think\Controller;

class UserController extends Controller{
	function index(){
		$this -> display();
	}
	/* 检查登录信息 */
	function check(){
		$admin =  D('Admin');
		$res = $admin->check_user(I());
		$arr = array();
		if(!$res){
			$arr['success'] = 0;
		}else{
			session('admin_id',$res);
			M('admin')->where("id = '$res'") -> setField('last_time',time());
			$arr['success'] = 1;
		}
		echo json_encode($arr);
	}
	/* 清楚sign */
	function del_sign(){
		$admin = D('admin');
		$admin -> del_sign();
	}
	/* 校验sign值 */
	function check_key(){
		if($_POST){
			$admin = D('admin');
			$res = $admin -> save_admin($_POST['xigua_key']);
			if($res){
				
				$this->success('验证成功！');
			}else{
				$this->error('验证失败');
			}
		}
	}
	
}
?>