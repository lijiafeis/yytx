<?php
namespace Wxapi\Controller;
use Think\Controller;

class OauthController extends Controller{
	function index($surl=''){
		if(I('state')){$surl=I('state');}else{$surl = $_GET['surl'];}
		/* 判断是否允许未关注的人 */
		//dump(I());exit;
		if(I('userinfo') == 'userinfo'){
			$res = $this->openid($surl,'snsapi_userinfo');
		}else{
			$res = $this->openid($surl);
		}
		if(!$res){exit;}
		$users = D('Xigua/Users');
		/* 查询会员信息进行缓存 */
		if(!$res['openid']){
			redirect(U('index')."?userinfo=userinfo&surl=".$surl);exit;
		}else{
			$user_id = $users->get_user_by_openid($res['openid'],$res);
			//session('xigua_user_id',$user_id);
			redirect($surl);
		}
		
		
		dump($surl);
		
	}
	//获取用户openid
	 function openid($surl='',$scope='snsapi_base'){
		if(F('config_info','',DATA_ROOT)){
			$config_info = F('config_info','',DATA_ROOT);
		}else{
			$config_info = M('config')->select();F('config_info',$config_info,DATA_ROOT);
		}
		$appid=$config_info[0]['appid'];
		$appserect=$config_info[0]['appsecret'];
		if(empty($_GET['code'])){
			$redirect_uri='http://'.$_SERVER['HTTP_HOST'].U('/wxapi/oauth/index');
			//echo $redirect_uri;exit;
			$url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=".$scope."&state=".$surl."#wechat_redirect";
			header("Location:$url");
		}else{
			$code=$_GET['code'];
			$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appserect."&code=".$code."&grant_type=authorization_code";
			$res=$this->http_request($url);
			$result=json_decode($res,true);
			$user_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$result['access_token'].'&openid='.$result['openid'].'&lang=zh_CN';
			$user_info = $this->http_request($user_url);
			$user_res = json_decode($user_info,true);
			$user_res['surl'] = I('state');
			return $user_res;
		}
	}
	
	//https请求(支持GET和POST)
	 function http_request($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		//var_dump(curl_error($curl));
		curl_close($curl);
		return $output;
	}
}