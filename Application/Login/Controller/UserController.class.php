<?php
/**
 * Created by PhpStorm.
 * 用户登录和注册的页面
 * Date: 2017/7/18 0018
 * Time: 16:50
 */
namespace Login\Controller;
use Think\Controller;

class UserController extends Controller{

    /*登录首页视图*/
    public function index(){
        $agent_id =$_GET['agent_id'];
        //如果是app，无法携带变化的参数，写一个固定参数，当时这个参数是，全都放行
        if($agent_id == 'OZfBaUKX' || $agent_id == 'abcdef'){
            session('agent_id','OZfBaUKX');
        }else{
            if($agent_id){
                $agent_id = M('agent_token') -> getFieldByAgentToken($agent_id,'agent_id');
                if(!$agent_id){
                    echo '参数错误';exit;
                }
                session('agent_id',$agent_id);
            }
            $is_weixin = $this -> is_weixin();
            if($is_weixin === 2){
                if(!$agent_id){
                    echo '请通过正确的方式进入商城，建议扫描推荐人二维码';exit;
//                session('agent_id','abcdef');
                }
            }
        }

        $data['username'] = cookie('username');
        $data['password'] = cookie('password');
        if ($data){
            $this->assign('data',$data);
        }
        $this -> display();
    }
    //判断打开方式
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return 1;
        }
        return 2;
    }
    /*异步处理登录请求*/
    function user_login(){
        $username = $_POST['tel'];
        $password = $_POST['pwd1'];
        $user_info = M('user') -> getByUsername($username);
        if($user_info == null){
            $arr['success'] = 0;
            $arr['error_info'] = '请注册';
        }else{
            if($user_info['password'] == md5('xiguakeji'.$password)){
                if ($user_info['black'] == 1){
                    $arr['success'] = 0;
                    $arr['error_info'] = '账号被冻结，请联系管理员';
                }else{
                    if(session('agent_id')){
                        //判断当前用户是否是该商户的人
                        $agent_flag = session('agent_id');
                        session("agent_token",$agent_flag);
                        if($agent_flag == 'OZfBaUKX'){
                            //session("agent_token",$agent_flag);
                            session('agent_id',$user_info['agent_id']);
                        }
                        if($user_info['agent_id'] == session('agent_id')){
                            session('xigua_user_id',$user_info['user_id']);
//                            if($user_info['user_id'] != 418 && $user_info['user_id'] != 5  &&$user_info['user_id'] != 6 && $user_info['user_id'] != 121){
//                                $arr['success'] = -5;
//                                $arr['error_info'] = '商城修改中';
//                                echo json_encode($arr);
//                            }
                            //session('tel',$username);
                            cookie('username',$username);
                            cookie('password',$password);
                            $arr['success'] = 1;
                        }else{
                            $arr['success'] = -5;
                            $arr['error_info'] = '你不是改商户平台的人，无法登录';
                        }
                    }

                }
            }else{
                $arr['success'] = 0;
                $arr['error_info'] = '账号或密码错误，请重试';
            }
        }

        echo json_encode($arr);
    }

    //忘记密码重置密码
    function chongzhi(){
        $this -> display();
    }


    public function register(){

        $uid_info = M('user') ->field("user_id,name,agent_id") -> getByUser_id($_GET['id']);
        //get有id 扫描二维码  没有  则是下面的链接
        $agent_id = $_GET['agent_id'];
        $agent_id = M('agent_token') -> getFieldByAgentToken($agent_id,'agent_id');
        if(!session('agent_id') && $agent_id){
            session('agent_id',$agent_id);
        }
        if(!$agent_id){
            $agent_id = session('agent_id');
        }
        if($agent_id != $uid_info['agent_id'] && $_GET['id'] != 0){
            echo '数据错乱，请重试';exit;
        }
        if(!$agent_id){
            echo '请重新获取二维码';
            exit;
        }
        $this -> assign('uid_info',$uid_info);
        $this -> assign('agent_id',$agent_id);
        $this -> display();
    }

    /*检测手机号是否已被注册*/
    function check_tel(){
        $tel = $_POST['tel'];
        if(!$tel){exit;}
        $info = M('user') -> getByUsername($tel);
        if($info == null){$str = 'true';}else{$str = 'false';}
        echo $str;
    }

    /*发送短信验证码*/
    function get_verify(){
        $juhe = A("Xigua/Juhe");
        $code = rand(100000,999999);
        $tel = $_POST['tel'];
        if(!$tel){exit;}
        $appname = "西瓜科技商城";
        $xigua_verify = session('xigua_verify');
        if($xigua_verify){
            echo 'error';
        }else{
            $juhe->msg_everify($code,$tel,$appname);
            session(array('name'=>'xigua_verify','expire'=>1800));
            session('xigua_verify',$code);
        }
    }

    /*注册新用户*/
    function new_user_add(){
        /*{tel:tel,verify:verify,pwd1:pwd1,uid:1}*/
        $arr = array();
        $username = $_POST['tel'];$verify = $_POST['verify'];$pwd1 = $_POST['pwd1'];$uid = $_POST['uid'];
        $agent_id = $_POST['agent_id'];
        if($uid){
            $res = M('user') -> where("user_id = {$uid}") -> select();
            if(!$res || !$_POST['uid']){
                $arr['success'] = 0; $arr['error_info'] = "数据有误，请扫描上级二维码";
                $this->ajaxReturn($arr);
                exit;
            }
        }else{
            //判断是否是第一个人
            $re = M('user') -> where("agent_id = {$agent_id}") -> select();
            if($re){
                $arr['success'] = 0; $arr['error_info'] = "数据有误，请扫描上级二维码";
                $this->ajaxReturn($arr);exit;
            }
        }
        if(!$agent_id){
            $arr['success'] = 0;$arr['error_info'] = "数据有误，请重新注册";
        }else{
            $name = $_POST['name'];
            /*检测用户是否已注册*/
            $users = M('user');
            $info = $users -> getByUsername($username);
            if($info != null){$arr['success'] = 0; $arr['error_info'] = "";echo json_encode($arr);exit;}
            /*检测上层用户是否存在*/
            //$pid_info = $users -> getByUser_id($uid);
            //if($pid_info == null){$arr['success'] = 0; $arr['error_info'] = "缺少推荐人，无法完成注册！";echo json_encode($arr);exit;}
            /*比对验证码是否正确*/
            $xigua_verify = session('xigua_verify');
            if(!$xigua_verify){$arr['success'] = 0; $arr['error_info'] = "手机验证码失效，请重新获取";echo json_encode($arr);exit;}
            if($xigua_verify != $verify){$arr['success'] = 0; $arr['error_info'] = "手机验证码错误，请查看手机短信记录！";echo json_encode($arr);exit;}
            //判断上级的商户和自己的商户是否一样
            if($uid != 0){
                $sj_agentId = M('user') -> getFieldByUserId($uid,'agent_id');
                if($sj_agentId != $agent_id){
                    $arr['success'] = 0; $arr['error_info'] = "数据错乱，请重试";
                }
            }


            $data = array('name' => $name,'username'=>$username,'sj_userid'=>$uid,'agent_id' => $agent_id,'time'=>time(),'password'=>md5('xiguakeji'.$pwd1));
            $user_id = $users -> add($data);
            if($user_id){$arr['success'] = 1;}else{$arr['success'] = 0;$arr['error_info'] = "注册失败！请重试";}
        }

        echo json_encode($arr);
    }
    /*发送短信验证码*/
    function get_verify1(){
        $juhe = A("Xigua/Juhe");
        $code = rand(100000,999999);
        $tel = $_POST['tel'];
        if(!$tel){exit;}
        $appname = "西瓜科技商城";
        $xigua_verify = session('xigua_verify1');
        if($xigua_verify){
            echo 'error';
        }else{
            $juhe->msg_everify($code,$tel,$appname);
            session(array('name'=>'xigua_verify1','expire'=>1800));
            session('xigua_verify1',$code);
        }
    }

    //重置密码修改
    public function chongzhiPass(){
        $arr = array();
        $verify = $_POST['verify'];
        $tel = $_POST['tel'];
        $pwd1 = $_POST['pwd1'];
        $pwd2 = $_POST['pwd2'];
        if($pwd1 != $pwd2){
            $arr['success'] = 0;$arr['error_info'] = "两次输入的密码不一样";
            echo json_encode($arr);
        }
        $xigua_verify = session('xigua_verify1');
        if(!$xigua_verify){
            $arr['success'] = 0;
            $arr['error_info'] = "手机验证码失效，请重新获取";
            echo json_encode($arr);exit;
        }
        if($verify != $xigua_verify){
            $arr['success'] = 0;
            $arr['error_info'] = "验证码不正确";
            echo json_encode($arr);exit;
        }
        $res = M('user') -> where("username = '$tel'") -> select();
        if(!$res){
            $arr['success'] = 0;
            $arr['error_info'] = "当前用户不存在";
            echo json_encode($arr);exit;
        }
        $password = md5('xiguakeji'.$pwd1);
        $res = M('user') -> where("username = '$tel'") -> setField('password',$password);
        if($res){
            $arr['success'] = 1;
            $arr['error_info'] = "修改成功";
            echo json_encode($arr);exit;
        }else{
            $arr['success'] = 0;
            $arr['error_info'] = "修改失败";
            echo json_encode($arr);exit;
        }
        echo json_encode($arr);
    }

    function yanzheng(){
        $this->display();
    }
    //验证
    function yanzheng_ajax(){
        $arr = array();
        $username = $_POST['tel'];$verify = $_POST['verify'];
        $users = M('user');
        $info = $users ->where("username = '$username'")->find();
        $xigua_verify = session('aaa');
        if(!$xigua_verify){$arr['success'] = 0; $arr['error_info'] = "手机验证码失效，请重新获取";echo json_encode($arr);exit;}
        if($xigua_verify != $verify){$arr['success'] = 0; $arr['error_info'] = "手机验证码错误，请查看手机短信记录！";echo json_encode($arr);exit;}
        if ($username == $info['username']){
            session('name',$username);
            $arr['success'] = 1;
        }
        echo json_encode($arr);
    }

    /*补充个人信息页面*/
    function xiugai(){
        $user_id = session('name');
        $user_info = M('user') -> getByUsername($user_id);
        $this -> assign("user_info",$user_info);
        $this -> display();
    }

    function xiugai_ajax(){
        $password = trim($_POST['password']);
        $password1 = trim($_POST['password1']);
        if($password != $password1){
            $this->success('两次密码不一样','Index');exit;
        }
        $user_id = session('name');
        $user_info = M('user') -> getByUsername($user_id);
        $data = array(
            'password' =>md5('xiguakeji'.$password)
        );
        M('user') -> where("username = '$user_id' ") -> save($data);
        session('name',null);
        $this->success('保存成功','Index');
    }



}