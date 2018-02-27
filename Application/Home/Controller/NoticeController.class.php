<?php
namespace Home\Controller;
use Think\Controller;

class NoticeController extends Controller{
    //购买商品后的异步回调
    public function buy_yz(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
//        $sign_info = $this->sign($postObj);
//        if(!$sign_info){return;}
        $id = trim($postObj->attach); //当前用户押注存入数据库的id号；
        $order_sn = trim($postObj->out_trade_no); //订单号
        $total_fee = trim($postObj->total_fee)/100; //金额
        $model =M('shop_order');
		$t = $model -> where("id = '$id'") -> select();
		if($t[0]['state'] == 1){
			file_put_contents('ceshi.txt',$order_sn . $id . ',',FILE_APPEND);
			echo '<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[OK]]></return_msg>
			</xml>';
			exit;
		}
        $model->where("id = '$id'")->setField('state',1);
        //$model->where("id = '$id'")->setField('order_sn',$order_sn);
        $model->where("id = '$id'")->setField('time',time());

//        //测试修改数据
//        $csfee = $total_fee * 160000;
//        $model->where("id = '$id'")->setField('zonge',$csfee);


        $user_id = $model -> getFieldById($id,'user_id');
        //把订单的地址保存到user_addresss中
        $address_id = $model -> getFieldById($id,"address_id");
        $abc = M('user_address') -> find($address_id);
        $abc['order_id'] = $id;
        M('user_addresss') -> add($abc);
		//记录到订单表中
		$data5['user_id'] = $user_id;
		$data5['order_sn'] = $order_sn;
		$data5['order_id'] = $id;
		M('order_sn') -> add($data5);

        $data = M('shop_order_detail') -> where(['order_id' => $id]) -> select();
        $news = array();
        //记录特殊商品总有多少件
        $num = 0;
        foreach ($data as $k => $v){
//            //测试修改数据
//            $csfee = $v['good_price'] * 160000;
//            M('shop_order_detail') -> where(['order_id' => $id]) -> setField('good_price',$csfee);

            M('shop_order_detail') -> where(['order_id' => $id]) -> setField('state',1);
            M('shop_order_detail') -> where(['order_id' => $id]) -> setField('time',time());
            M('shop_goods')->where(['good_id' => $v['good_id']])->setInc('xiaoliang',1);
//            $number = M('shop_goods') -> getFieldByGoodId($v['good_id'],'number');
//            if($number <= 0){
//                continue;
//            }
//            M('shop_goods')->where(['good_id' => $v['good_id']])->setDec('number',1);
            $new = M('shop_goods') -> field('new') -> where(['good_id' => $v['good_id']]) -> select();
            if($new[0]['new'] == 1){
                $news[] = $v['good_id'];
                $num += $v['good_num'];
            }
        }
        file_put_contents('test.txt',$news . ',' . $user_id);
        //查询特殊商品，进行推荐奖的返利
        if($news){
            //向上级返钱推荐奖的钱
            $sj_userid = M('user') -> getFieldByUserId($user_id,'sj_userid');
           // file_put_contents('sjuser.txt',$sj_userid);
            if($sj_userid){
                //返钱
                $data = M('feetuijian') -> find(1);
                $fee = $data['one'] * $num;
                $res = M('user') -> where("user_id = {$sj_userid}") -> setInc('money',$fee);
                $agent_id = M('user') -> getFieldByUserId($sj_userid,'agent_id');
                if($res){
                    //保存到佣金表中
                    $res1['user_id'] = $sj_userid;
                    $res1['money'] = $fee;
                    $res1['time'] = time();
                    $res1['order_id'] = $id;
                    $res1['xj_userid'] = $user_id;
                    $res1['number'] = 1;
                    $res1['agent_id'] = $agent_id;
                    M('yongjin') -> add($res1);
                    $re = M('fee') -> where("user_id = {$sj_userid}") -> select();
                    if($re){
                        //由当前人的数据
                        M('fee') -> where("user_id = {$sj_userid}") -> setInc('w1',$fee);
                        M('fee') -> where("user_id = {$sj_userid}") -> setField('time',time());
                    }else{
                        $data2['user_id'] = $sj_userid;
                        $data2['w1'] = $fee;
                        $data2['time'] = time();
                        M('fee') -> add($data2);
                    }
                    //发送推荐奖佣金模板
                    $this->sendTem($sj_userid,$fee,0);
                }
            }
        }
        $this -> sendTemplate($id);

        echo '<xml>
		   <return_code><![CDATA[SUCCESS]]></return_code>
		   <return_msg><![CDATA[OK]]></return_msg>
		</xml>';
        exit;
   }
    public function sendTemplate($order_id){

        /* 发送模板消息通知 */
        //获取订单号
//        $order_sn = M('shop_order') -> getFieldById($order_id,'order_sn');
        $money = M('shop_order') -> getFieldById($order_id,'zonge');
        $address_id = M('shop_order') -> getFieldById($order_id,'address_id');
//        $address = M('user_address') -> getFieldById($address_id,'address');
        //获取openid
        $user_id = M('shop_order') -> getFieldById($order_id,'user_id');
        $openid = M('users') -> getFieldByUser_id($user_id,'openid');

        //获取收货信息
//        $address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        $address_info = M('user_address') -> find($address_id);
//        file_put_contents('abc.txt',$order_id);
        $template = A("Pay/Template");
        $url = 'http://'.$_SERVER['SERVER_NAME'].U('/Home/Order/orderXiangQing')."?order_id=$order_id";
        $template->send_order(time(),$money,$address_info['city'].$address_info['address'],$address_info['telphone'],$openid,$url);
    }
    public function sendTem($user_id,$money,$type){
//        $money,$info,$openid,$url
        if($type == 0){
            $info = '下级购买特殊商品,返还推荐奖';
        }
        $openid = M('users') -> getFieldByUserId($user_id,'openid');
        $template = A("Pay/Template");
        $url = 'http://'.$_SERVER['SERVER_NAME'].U('/Home/User/duihuan');
        $template->send_money($money,$info,$openid,$url);
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
        $auth_info = M('auth_info')->field("mkey") -> where("appid = '$appid' ")->find();
        $str2 = $str1.'&key='.$auth_info['mkey'];
        $new_sign = strtoupper(MD5($str2));
        if($new_sign == $sign){return true;}else{return false;}
    }

}