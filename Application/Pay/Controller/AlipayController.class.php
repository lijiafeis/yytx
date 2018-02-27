<?php
namespace Pay\Controller;
use Think\Controller;

class AlipayController extends Controller{

	function pay(){
        $pay_id = I('sn');
        $shop_order = M('shop_order');
        if(!$pay_id){echo "无效订单，无法进行支付";exit;}
        //订单总金额
        $order_info = $shop_order -> where("id = '$pay_id' ") -> find();
        if(!$order_info){echo "无效订单，无法进行支付";exit;}
        if($order_info['state'] == 1){echo "订单已支付，请勿重复支付";exit;}
        require_once("alilib/alipay.config.php");
        require_once("alilib/alipay_submit.class.php");
        // $order_info = M('agent_orders') -> getByOrder_id($_POST['order_id']);
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $pay_id;
        //订单名称，必填
        $subject = '商品购买';
        //付款金额，必填
        $total_fee = $order_info['zonge'];
        // if($total_fee != $order_info['total_fee']){$this->error('订单金额不符！');exit;}
        // if($out_trade_no != $order_info['order_sn']){$this->error('订单号不符！');exit;}
        //收银台页面上，商品展示的超链接，必填
//        $show_url = "http://".$_SERVER['SERVER_NAME'].U('/user/center/user');
        $show_url = "http://".$_SERVER['SERVER_NAME'].U('/Home/Index/index');
        //商品描述，可空
        $body = '';
		/************************************************************/
		//构造要请求的参数数组，无需改动
		//dump($alipay_config);exit;
		$parameter = array(
				"service"       => $alipay_config['service'],
				"partner"       => $alipay_config['partner'],
				"seller_id"  => $alipay_config['seller_id'],
				"payment_type"	=> $alipay_config['payment_type'],
				"notify_url"	=> $alipay_config['notify_url'],
				"return_url"	=> $alipay_config['return_url'],
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"show_url"	=> $show_url,
				"body"	=> $body,
		);
		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
//		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
//		echo $html_text;
        $html_text = $alipaySubmit->getHtml($parameter);
        $this -> assign('html_text',$html_text);
        $this -> assign('order_id',0);
        $this -> display('Alipay_index');
	}
	
	function notify_url(){
		require_once("alilib/alipay.config.php");
		require_once("alilib/alipay_notify.class.php");
		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {//验证成功
			//请在这里加上商户的业务逻辑程序代
			//商户订单号
			//S('alipay',$_POST);
			$out_trade_no = $_POST['out_trade_no'];
			//支付宝交易号
			$trade_no = $_POST['trade_no'];
			//交易状态
			$trade_status = $_POST['trade_status'];
			if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理，进行商户处理
				$this -> pay_sure($out_trade_no);
			}
			else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理，进行商户处理
				$this -> pay_sure($out_trade_no);
			}	
			echo "success";		//请不要修改或删除
		}
		else {
			//验证失败
			echo "fail";
		}
	}

	private function pay_sure($pay_id){
		$order_info = M('shop_order') -> where("id = '$pay_id' ") -> find();
		if($order_info == null){return;}
		if($order_info['state'] == 1){return;}
		//$order_id = $order_info['order_id'];
		/*支付成功*/
		$system = A("Pay/Notify");
		$system -> shop1($pay_id);
	}
	
	function return_url(){
		require_once("alilib/alipay.config.php");
		require_once("alilib/alipay_notify.class.php");
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {//验证成功
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理，进行商户处理
				$this -> pay_sure($_GET['out_trade_no']);
			}
			else {
			  echo "trade_status=".$_GET['trade_status'];
			}
			echo "<script>alert('支付成功！');</script>";
			redirect(U('/Home/Index/index'));

		}
		else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			echo "验证失败";
		}
	}
}