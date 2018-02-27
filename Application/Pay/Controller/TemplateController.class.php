<?php
namespace Pay\Controller;
use Think\Controller;

class TemplateController extends Controller{
	function __construct(){
		parent::__construct();
		$this->template_info = F('template_info','',DATA_ROOT);
	}
	
	/* 发送订单追踪相关通知 */
//$template->send_shop($order_sn,$address_info['name'],$address_info['tel'],$address_info['address'],$openid,$url);


    function send_shop($kd_name,$order_sn,$name,$tel,$address,$openid,$url,$address1){
		$tem_data = '{
		   "touser":"'.$openid.'",
		   "template_id":"q9LD16ZobFVjYrudeql2Cylo8AsY0N6GYTSiJstk5wk",
		   "url":"'.$url.'",            
		   "data":{
			       "keyword1": {
					   "value":"'.$kd_name.'",
					   "color":""
				   },
				   "keyword2": {
					   "value":"'.$order_sn.'",
					   "color":""
				   },
				   "keyword3": {
					   "value":"'.$name.'",
					   "color":""
				   },
				   "keyword4": {
					   "value":"'.$tel.'",
					   "color":""
				   },
				   "keyword5": {
					   "value":"'.$address.'",
					   "color":""
				   },
				   "remark":{
					   "value":"时间：'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   }
		   }
	   }';
	   $weixin = A("Wxapi/Weixin");
	   $a = $weixin ->send_template($openid,$tem_data);
        file_put_contents('a.txt',$a);
    }

	/* 新订单 */
	function send_order($order_sn,$money,$address,$tel,$openid,$url){
		$tem_data = '{
		   "touser":"'.$openid.'",
		   "template_id":"GZGQaH1tTjK7fDfmEO8kp0t5SL15N0uZhzJw8lcxOnI",
		   "url":"'.$url.'",         
		   "data":{
				  "keyword1": {
					   "value":"'.$order_sn.'",
					   "color":""
				   },
				   "keyword2": {
					   "value":"'.$money.'",
					   "color":""
				   },
				   "keyword3": {
					   "value":"'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   },
				   "keyword4": {
					   "value":"'.$tel.'",
					   "color":""
				   },
				   "keyword5": {
					   "value":"'.$address.'",
					   "color":""
				   },
				   "remark":{
					   "value":"时间：'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   }
		   }
	   }';
	   $weixin = A("Wxapi/Weixin");
	   $a = $weixin ->send_template($openid,$tem_data);
	   //file_put_contents('a.txt',$a);
	}

//亲，您有一笔资金到账，请即时查收
//到账金额：100.00元
//到账时间：2015-11-05 23:00:00
//到账详情：张女士预约小米4手机的预约款（预约单号：27BD137CAF31E127）已汇入您的账号
//感谢你的使用。
    /* 新佣金 */
    function send_money($money,$info,$openid,$url){
        $tem_data = '{
		   "touser":"'.$openid.'",
		   "template_id":"thmyM-YP91iUsw72XT-EQDRrlVgSRhv-7WdIhJm8PWA",
		   "url":"'.$url.'",         
		   "data":{
				  "keyword1": {
					   "value":"'.$money.'元",
					   "color":""
				   },
				   "keyword2": {
					   "value":"'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   },
				   "keyword3": {
					   "value":"'.$info.'",
					   "color":""
				   },
				   "remark":{
					   "value":"时间：'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   }
		   }
	   }';
        $weixin = A("Wxapi/Weixin");
        $a = $weixin ->send_template($openid,$tem_data);
//        file_put_contents('a.txt',$a);
    }
	
	function send_money1($money,$info,$openid,$url){
        $tem_data = '{
		   "touser":"'.$openid.'",
		   "template_id":"thmyM-YP91iUsw72XT-EQDRrlVgSRhv-7WdIhJm8PWA",
		   "url":"'.$url.'",         
		   "data":{
				  "keyword1": {
					   "value":"'.$money.'元",
					   "color":""
				   },
				   "keyword2": {
					   "value":"'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   },
				   "keyword3": {
					   "value":"'.$info.'",
					   "color":""
				   },
				   "remark":{
					   "value":"时间：'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   }
		   }
	   }';
        $weixin = A("Wxapi/Weixin");
        $a = $weixin ->send_template($openid,$tem_data);
//        file_put_contents('a.txt',$a);
    }

	
	function send_shengji($type,$user_id,$openid,$url){
		file_put_contents('abs.txt','avc');
        $tem_data = '{
		   "touser":"'.$openid.'",
		   "template_id":"fz08ku55ZgQqmvL3cZQMrRSoUcKFT3i0PEQXZeKnGvw",
		   "url":"'.$url.'",         
		   "data":{
				  "first": {
					   "value":"亲爱的,恭喜你升级为橙子乐购商城'. $type .'",
					   "color":""
				   },
				  "keyword1": {
					   "value":"'.$user_id.'",
					   "color":""
				   },
				   "keyword2": {
					   "value":"无",
					   "color":""
				   },
				   "remark":{
					   "value":"时间：'.date("Y-m-d H:i:s",time()).'",
					   "color":""
				   }
		   }
	   }';
        $weixin = A("Wxapi/Weixin");
        $a = $weixin ->send_template($openid,$tem_data);
        file_put_contents('abcdefg.txt',$a);
    }
}