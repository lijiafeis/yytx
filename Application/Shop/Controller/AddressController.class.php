<?php
namespace Shop\Controller;
use Think\Controller;
class AddressController extends Controller {
	
	function __construct(){
		parent::__construct();		
		if(F('config_info','',DATA_ROOT)){
			$config_info = F('config_info','',DATA_ROOT);
		}else{
			$config_info = M('config')->field("appid,appsecret") ->select();F('config_info',$config_info,DATA_ROOT);
		}
		$this->appid=$config_info[0]['appid'];
		$this->appserect=$config_info[0]['appsecret'];
		$arr = $this->get_access_token();//dump($arr);exit;
		if($arr){
			$this->access_token=$arr['access_token'];
			$this->token=$arr['token'];
		}
	}
	
	function index(){
		//参与 addrSign 签名的字段包括：appId、url（调用 JavaScript API 的网页 url）、timestamp、noncestr、accessToken
		//accesstoken=OezXcEiiBSKSxW0eoylIeBFk1b8VbNtfWALJ5g6aMgZHaqZwK4euEskSn78Qd5pLsfQtuMdgmhajVM5QDm24W8X3tJ18kz5mhmkUcI3RoLm7qGgh1cEnCHejWQo8s5L3VvsFAdawhFxUuLmgh5FRA
		//&appid=wx17ef1eaef46752cb&noncestr=123456&timestamp=1384841012&url=http://open.weixin.qq.com/
		//echo $this->access_token;
		//var_dump($_GET);
		
		$good_id = $_GET["id"];
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp="1384841012";
		$noncestr="123456";
		$str="accesstoken=".$this->access_token."&appid=".$this->appid."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
		$addrSign=SHA1($str);
		$editAddress=array(
				"appid"=>$this->appid,
				"addrSign"=>$addrSign,
				"timestamp"=>$timestamp,
				"noncestr"=>$noncestr,
				);
		$this->assign("info",$info);
		$this->assign("token",$this->token);
		$this->assign("editAddress",$editAddress);
		$address_info = M('user_address') -> getByUser_id(session('xigua_user_id'));
		$this -> assign('address_info',$address_info);
		$this->display();
	}
	

	
	
	
	//获取access_token并缓存处理
	function get_access_token(){
		if(!$_GET['code']){
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$redirect_uri = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			//echo $redirect_uri;exit;
			$url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=#wechat_redirect";
			redirect($url);
		}elseif($_GET['code']){
			//var_dump($_GET);exit;
			$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appserect."&code=".$_GET[code]."&grant_type=authorization_code";
			$res=$this->http_request($url);
			$result=json_decode($res, true);
			$access_token=$result['access_token'];
			$arr = array('access_token'=>$access_token);
			return $arr;
			
			
		}
	}
	
	//https请求(支持GET和POST)
	protected function http_request($url,$data = null){
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
		curl_close($curl);
		return $output;
	}

}
	
