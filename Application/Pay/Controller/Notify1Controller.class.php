<?php
namespace Pay\Controller;
use Think\Controller;

class Notify1Controller extends Controller{

    //购买商品后的回调,扫码支付的回调
    public function shop(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
       //$info = $this->sign($postObj);
        //if(!$info){return;}
        file_put_contents('a.txt',$postObj);
        $id = trim($postObj->attach); //当前用户押注存入数据库的id号；
        $order_sn = trim($postObj->out_trade_no); //订单号
        $total_fee = trim($postObj->total_fee)/100; //金额
        $model =M('goods_order');
        $t = $model -> field('state,price,order_sn') -> where(['id' => $id]) -> select();
		if(!$t){
			file_put_contents('order.txt',$id . ',' . $order_sn . ',' . $total_fee . ',',FILE_APPEND);
		}
        if($t[0]['state'] == 1){
			file_put_contents('order.txt',$id . ',' . $order_sn . ',' . $total_fee . ',',FILE_APPEND);
            echo '<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[OK]]></return_msg>
			</xml>';
            exit;
        }
        $data['pay_time'] = time();
        $data['state'] = 1;
        $model->where("id = '$id'")->save($data);
        $this -> sendTemplate($id);
        echo '<xml>
		   <return_code><![CDATA[SUCCESS]]></return_code>
		   <return_msg><![CDATA[OK]]></return_msg>
		</xml>';
        exit;
    }

    //购买商品后的回调,支付宝的回调
    public function shop1($id){
        $model =M('goods_order');
        $t = $model -> where(['id' => $id]) -> find();
        if($t['state'] != 0){
			file_put_contents('zhifub.txt',$id . ',',FILE_APPEND);
            return 1;
        }
        //把订单的地址保存到user_addresss中
        $data['pay_time'] = time();
        $data['state'] = 1;
        $model->where(['id' => $id])->save($data);
        $this -> sendTemplate($id);
        return 1;
    }

    public function sendTemplate($order_id){
        $orderInfo = M('goods_order') -> where(['id' => $order_id]) -> find();
        $money = $orderInfo['price'];

        $user_id = $orderInfo['user_id'];
        $openid = M('users') -> getFieldByUser_id($user_id,'openid');
        $address_info = explode(',',$orderInfo['address']);
        $template = A("Pay/Template");
        $url = 'http://'.$_SERVER['SERVER_NAME'].U('/Home/Order/orderXiangQing')."?order_id=$order_id";
        $template->send_order(time(),$money,$address_info[2].$address_info['2'],$address_info['1'],$openid,$url);
    }

    /*支付解密*/
    function sign($postObj){
        $sign = trim($postObj->sign);
        $appid = trim($postObj->appid);
        $attach = trim($postObj->attach);
        $bank_type = trim($postObj->bank_type);
        $cash_fee = trim($postObj->cash_fee);
        $fee_type = trim($postObj->fee_type);
        $is_subscribe = trim($postObj->is_subscribe);
        $mch_id = trim($postObj->mch_id);
        $nonce_str = trim($postObj->nonce_str);
        $openid = trim($postObj->openid);
        $out_trade_no = trim($postObj->out_trade_no);
        $result_code = trim($postObj->result_code);
        $return_code = trim($postObj->return_code);
        $time_end = trim($postObj->time_end);
        $total_fee = trim($postObj->total_fee);
        $trade_type = trim($postObj->trade_type);
        $transaction_id = trim($postObj->transaction_id);
        $str1 = 'appid='.$appid.'&attach='.$attach.'&bank_type='.$bank_type.'&cash_fee='.$cash_fee.'&fee_type='.$fee_type.'&is_subscribe='.$is_subscribe.'&mch_id='.$mch_id.'&nonce_str='.$nonce_str.'&openid='.$openid.'&out_trade_no='.$out_trade_no.'&result_code='.$result_code.'&return_code='.$return_code.'&time_end='.$time_end.'&total_fee='.$total_fee.'&trade_type='.$trade_type.'&transaction_id='.$transaction_id;
        $auth_info = M('config')->field("mkey") -> where("appid = '$appid' ")->find();
        $str2 = $str1.'&key='.$auth_info['mkey'];
        $new_sign = strtoupper(MD5($str2));
        if($new_sign == $sign){return true;}else{return false;}
    }

}