<?php
namespace Pay\Controller;
use Think\Controller;

class Alipay1Controller extends Controller{

	function pay(){
        $pay_id = I('sn')  * 1;
        $goods_order = M('goods_order');
        if(!$pay_id){echo "无效订单，无法进行支付";exit;}
        //订单总金额
        $order_info = $goods_order -> where(['id' => $pay_id]) -> find();
        if(!$order_info){echo "无效订单，无法进行支付";exit;}
        if($order_info['state'] != 0){echo "订单已支付，请勿重复支付";exit;}
        require_once("alilib/alipay.config.php");
        require_once("alilib/alipay_submit.class.php");
        // $order_info = M('agent_orders') -> getByOrder_id($_POST['order_id']);
        //商户订单号，商户网站订单系统中唯一订单号，必填
        if(!$order_info['order_sn']){
            $order_sn = $order_info['user_id'] . time() . mt_rand(10000);
        }else{
            $order_sn = $order_info['order_sn'];
        }
        $address = M('user_address') -> where(['user_id' => $order_info['user_id'],'is_true' => 1]) -> find();
        if(!$address){
            $returnInfo['info'] = '请选择收获地址';
            $returnInfo['success'] = 0;
            $this -> ajaxReturn($returnInfo);exit;
        }
        $data['name'] = $address['username'];
        $data['order_sn'] = $order_sn;
        $data['pay_type'] = 2;
        $data['address'] = $address['username'] . ',' . $address['telphone'] . ',' . $address['city'] . $address['address'];
        $goods_order -> where(['id' => $pay_id]) -> save($data);
        $out_trade_no = $order_sn;
        //订单名称，必填
        $subject = '商品购买';
        //付款金额，必填
        $total_fee = $order_info['price'];
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
        $alipay_config['notify_url'] = "http://".$_SERVER['SERVER_NAME'].U('/Pay/alipay1/notify_url');
// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $alipay_config['return_url'] = "http://".$_SERVER['SERVER_NAME'].U('/Pay/alipay1/return_url');
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
        $this -> assign('order_id',$pay_id);
        $this -> display('Alipay_index');
	}
	
	function notify_url(){
		require_once("alilib/alipay.config.php");
		require_once("alilib/alipay_notify.class.php");
        $alipay_config['notify_url'] = "http://".$_SERVER['SERVER_NAME'].U('/Pay/alipay1/notify_url');
// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $alipay_config['return_url'] = "http://".$_SERVER['SERVER_NAME'].U('/Pay/alipay1/return_url');
		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
        //file_put_contents('c.txt',$_POST['trade_status']);
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
	    //file_put_contents('b.txt',$pay_id);
		$order_info = M('goods_order') -> where(['order_sn' => $pay_id]) -> find();
		if($order_info == null){return;}
		if($order_info['state'] == 1){return;}
		$system = A("Pay/Notify1");
		$system -> shop1($order_info['id']);
	}
	
	function return_url(){
		require_once("alilib/alipay.config.php");
		require_once("alilib/alipay_notify.class.php");
        $alipay_config['notify_url'] = "http://".$_SERVER['SERVER_NAME'].U('/Pay/alipay1/notify_url');
// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $alipay_config['return_url'] = "http://".$_SERVER['SERVER_NAME'].U('/Pay/alipay1/return_url');
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		//file_put_contents('c.txt',$_GET['trade_status']);
		if($verify_result) {//验证成功
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理，进行商户处理
                $order_info = M('goods_order') -> field('id,state,is_game,game_type,game_state') -> where(['order_sn' => $_GET['out_trade_no']]) -> find();
				$this -> pay_sure($_GET['out_trade_no']);
			}
			else {
			  echo "trade_status=".$_GET['trade_status'];
			}
			echo "<script>alert('支付成功！');</script>";
			if($order_info){
			    if($order_info['state'] == 1 && $order_info['is_game'] == 1 && $order_info['game_type'] == 0 && $order_info['game_state'] == 0){
                    redirect(U('/Home/Goods/orderInfo') . "?order_id=" . $order_info['id']);
                }else{
                    redirect(U('/Home/Goods/myOrder'));
                }

            }else{
                redirect(U('/Home/Goods/myOrder'));
            }


		}
		else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			echo "验证失败";
		}
	}
}