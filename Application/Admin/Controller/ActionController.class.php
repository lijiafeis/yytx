<?php
namespace Admin\Controller;
use Think\Controller;

class ActionController extends Controller{
	function __construct(){
		parent::__construct();
		if(!file_exists('data/install.lock')){
			$install_url = 'http://'.$_SERVER['SERVER_NAME'].'/install';
			redirect($install_url);exit;
		}
		
		// if($_GET['xigua']){dump($data);}
		// $res = $this -> http_request($url,$data);
		// $result = json_decode($res, true);
		// $admin = D('admin');
		// $admin->check_admin();
		
		
//		//删除订单记录中的过期订单，2小时过期
//		$shop_order = M('shop_order');$users = M('users');$shop_order_detail = M('shop_order_detail');$daijin_order = M('daijin_order');
//		$time = time() - 7200;
//		$del_order_info = $shop_order -> where(" is_true = 0 and time < '$time' ") -> select();
//		foreach($del_order_info as $v){
//			if($v['daijin_id'] > 0){
//				/* 删除订单前返还代金券 */
//				$daijin_id = $v['daijin_id'];
//				$daijin_order -> where("order_id = '$daijin_id' ") -> setField('state',0);
//			}
//			/* 删除缓存订单信息 */
//			$pay_id = $v['pay_id'];
//			$shop_order_detail -> where(" pay_id = '$pay_id' ") -> delete();
//		}
//		$shop_order -> where(" is_true = 0 and time < '$time' ") -> delete();
//		/* 代金券订单，超过2小时自动删除 */
//		$daijin_info = $daijin_order -> where("is_true = 0 and time < '$time' ") -> delete();
//		/* 超过7天自动确认收货 */
//		$time1 = time() - 604800;
//		//$time1 = time() - 60;
//		$order_shouhuo = $shop_order -> where(" is_true = 1 and state = 1 and serve_time < '$time1' ") -> select();
//		foreach($order_shouhuo as $v){
//			$user_id = $v['user_id'];$order_id = $v['order_id'];$pay_id = $v['pay_id'];
//			$openid = $users -> getFieldByUser_id($user_id,'openid');
//			$order_info = $shop_order_detail -> field("good_name,good_num") -> where(" pay_id = '$pay_id' ") -> select();
//			foreach($order_info as $vv){
//				$good_name .=$vv['good_name'].".";
//				$good_num =$good_num + $vv['good_num'];
//			}
//			/* 发送模板消息通知 */
//			$template = A("Pay/Template");
//			$url = 'http://'.$_SERVER['SERVER_NAME'].U('/user/center/order')."?state=zan";
//			$template->send_shop($v['order_sn'],$good_name.'*'.$good_num,'过期系统自动确认收货',$openid,$url);
//			$shop_order -> where("order_id = '$order_id'") -> setField('state',2);
//		}
	}
	
	function GetIP(){
		if('/'==DIRECTORY_SEPARATOR){
			$server_ip=$_SERVER['SERVER_ADDR'];
		}else{
			$server_ip=@gethostbyname($_SERVER['SERVER_NAME']);
		}
		return $server_ip;
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

 ?>