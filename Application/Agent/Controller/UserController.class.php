<?php
namespace Agent\Controller;
use Think\Controller;

class UserController extends Controller{
	function index(){
		$this -> display();
	}
	/* 检查登录信息 */
	function check(){
		$admin =  M('agent_info');
        $username = I('post.username');
        $password = I('post.password');
        $info = $admin ->where("username='$username' ") -> find();
        if($info == null){
            $arr['success'] = 0;
        }else{
            if( $info['password'] == md5('xiguakeji' . $password) ){
                session('admin_id',$info['id']);
//			M('admin')->where("id = '$res'") -> setField('last_time',time());
                $arr['success'] = 1;
            }else{
                $arr['success'] = 0;
            }
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