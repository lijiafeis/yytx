<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10 0010
 * Time: 10:19
 */
namespace Home\Controller;
use Think\Controller;
use Think\Page;

class CartController extends Controller
{

    // /*用户来，判断session存不存在，*/
    function __construct()
    {
        parent::__construct();
        $this->user_id = session('xigua_user_id');
        $res = $this->is_weixin();
        if ($res == 1){
            //$info = M('zhuce')->where("user_id = '$this->user_id'")->find();
            if (!$this->user_id){
                redirect(U('/Login/User/index'));
            }else {
                $user_info=M('users')->where("user_id = '$this->user_id'")->find();
                if(!$user_info){
                    $redirect_uri='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    redirect('http://'.$_SERVER['HTTP_HOST'].U('/wxapi/oauth/index/')."?surl=".$redirect_uri);exit;
                }
            }

        }else {
            if(!$this->user_id){
                $now_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $this -> assign('now_url',$now_url) ;
                $this->display('Login_error');exit;
            }else{
                $this -> user_id = session('xigua_user_id');
            }
        }
        $this -> agent_id = M('user') -> getFieldByUserId($this->user_id,'agent_id');
    }

    //判断打开方式
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return 1;
        }
        return 2;
    }

    public function showGwc()
    {

        //查询到添加到购物车的商品信息
        $data = M('shop_order_temp')
            ->alias("a")
            ->field('a.*,b.good_name,b.good_chengben,c.pic_url')
            ->join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
            ->join('left join __GOOD_PIC__ as c on a.good_id = c.good_id')
            ->where(['user_id' => $this->user_id])
            ->select();
        $money = 0;
        foreach ($data as $k => $v) {
            $money += $v['good_chengben'] * $v['good_num'];
        }
        $this->assign('money', $money);
        $this->assign('data', $data);
        $this->display();
    }

    //删除购物车中的商品
    public function delShop()
    {
        $good_id = $_POST['good_id'];
        $type = $_POST['type'];
        $res = M('shop_order_temp')->where(['user_id' => $this->user_id, 'good_id' => $good_id,'type' => $type])->delete();
        if ($res) {
            echo 0;
        } else {
            echo -1;
        }
    }


    //增加购物车中商品的数量
    public function setShopNum()
    {
        $good_id = $_POST['good_id'];
        $data['good_num'] = $_POST['number'];
        M('shop_order_temp')->where(['good_id' => $good_id, 'user_id' => $this->user_id])->save($data);
    }

    /**
     * 1.保存商品到订单表，生成订单
     *
     */
    public function jiesuan(){
        $goodList = $_POST['goodlist'];
        $goodList = explode(',',$goodList);
        $detail = M('shop_order_detail');
        $shopmoney = 0;
        $yunfei = 0;
        $zonge = 0;
        $data['user_id'] = $this->user_id;
        $address = M('user_address') -> where(['user_id' => $this->user_id,'is_true' => 1]) -> select();
        $data['address_id'] = $address[0]['address_id'] == null ? 0 : $address[0]['address_id'];
        $data['time'] = time();
        $data['agent_id'] = $this->agent_id;
        $order_id = M('shop_order') -> add($data);
		if(!$order_id){
			exit;
		}
        unset($data);
        $str = '默认邮费8元,新疆，西藏地区15元，内蒙，海南地区10元，购买商品超过重量上限，要加额外的运费';
        $weight = 0;
        foreach ($goodList as $k => $v){
            if($v){
				$shop_id = M('shop_order_temp') -> getFieldByOrderId($v,'good_id');
                $info = M('shop_goods') -> find($shop_id);
                $info1 = M('shop_order_temp') -> where(['order_id' =>  $v]) -> select();
                $info1 = $info1[0];
                //计算商品的总重量
                $data['user_id'] = $this->user_id;
                $data['order_id'] = $order_id;
                $data['good_id'] = $shop_id;
                $data['good_name'] = $info['good_name'];
                $data['good_danjia'] = $info['good_chengben'];
                $data['good_price'] = $info['good_chengben'] * $info1['good_num'];
                $shopmoney += $data['good_price'];
                $data['good_yunfei'] = 0;
                $data['good_num'] = $info1['good_num'];
                $data['pic_url'] = M('good_pic') -> getFieldByGoodId($shop_id,'pic_url');
                $data['time'] = time();
                $data['type'] = $info1['type'];
                $data['agent_id'] = $this->agent_id;;
                M('shop_order_detail') -> add($data);
                M('shop_order_temp') -> delete($v);
            }
        }
//        file_put_contents('a.txt',$weight);
        //通过总重量计算是否超过最大重量
        $yunfei = 0;
        $zonge = $shopmoney + $yunfei;
        unset($data);
        $data['shopmoney'] = $shopmoney;
        $data['yunfei'] = $yunfei;
        $data['zonge'] = $zonge;
        $res = M('shop_order') -> where(['id' => $order_id]) -> save($data);
        if($res){
            echo $order_id;
        }else{
            echo $order_id;
        }
    }

    //立即结算
    /**
     * 1.保存商品到订单表，生成订单
     *
     */
    public function Ljjiesuan(){
        //判断是否到当天最大分数
        $fen = $this -> setFen($_POST['number']);
        if(!$fen){
            echo -17;exit;
        }


        //先判断是否购买代理
        $type = M('user') -> getFieldByUserId($this->user_id,'is_true');
        if($type == 0){
            echo -2;
            exit;
        }
        $good_id = $_POST['good_id']*1;
        $number = $_POST['number']*1;
        $type = $_POST['type'];
        if(!$good_id || !$number){
            exit;
        }
        $shopmoney = 0;
        $yunfei = 0;
        $zonge = 0;
        $data['user_id'] = $this->user_id;
        $address = M('user_address') -> where(['user_id' => $this->user_id,'is_true' => 1]) -> select();
        $data['address_id'] = $address[0]['address_id'] == null ? 0 : $address[0]['address_id'];
        $data['time'] = time();
        $data['agent_id'] = $this -> agent_id;
        $order_id = M('shop_order') -> add($data);
		if(!$order_id){
			exit;
		}
        unset($data);
        $info = M('shop_goods') -> find($good_id);

        $data['user_id'] = $this->user_id;
        $data['order_id'] = $order_id;
        $data['good_id'] = $good_id;
        $data['good_name'] = $info['good_name'];
        $data['good_danjia'] = $info['good_chengben'];
        $data['good_price'] = $info['good_chengben'] * $number;
        $shopmoney += $data['good_price'];
        $data['good_yunfei'] = 0;
        $data['good_num'] = $number;
        $data['pic_url'] = M('good_pic') -> getFieldByGoodId($good_id,'pic_url');
        $data['time'] = time();
        $data['type'] = $type;
        $data['agent_id'] = $this->agent_id;
        M('shop_order_detail') -> add($data);
        $zonge = $shopmoney + $yunfei;
        unset($data);
        $data['shopmoney'] = $shopmoney;
        $data['yunfei'] = $yunfei;
        $data['zonge'] = $zonge;
        $res = M('shop_order') -> where(['id' => $order_id]) -> save($data);
        if($res){
            echo $order_id;
        }else{
            echo $order_id;
        }
    }

    private function setFen($number){
        $a = M('shop_order_detail')
            -> field("sum(good_num) as num")
            -> where("user_id = {$this->user_id} and state = 1")
            -> select();
        $num = F('setFen','',DATA_ROOT);
        if($num && $number){
            $count = $number + $a[0]['num'];
            if($count > $num['number']){
                return false;
            }else{
                return true;
            }
        }
        return true;
    }

    //显示订单信息
    public function order(){
        $order_id = $_GET['order_id'];
        if(!$order_id){
            $order_id = session('order_id');
        }else{
            session('order_id',$order_id);
        }
		
		$order_id = htmlspecialchars($order_id,ENT_QUOTES);
        $order_id = $order_id * 1;
		
        $address = M('user_address') -> where(['user_id' => $this->user_id,'is_true' => 1]) -> select();
        $address = $address[0];
        $data = M('shop_order_detail') -> where(['order_id' => $order_id]) -> select();
        $num = count($data);
        $info = M('shop_order') -> find($order_id);
		if(!$info){
			exit;
		}
		if($info['user_id'] != $this->user_id){
		    exit;
        }
		if($info['state'] == 1){
			echo '你已支付成功';exit;
		}

        //判断是否有复消积分
        $fxmoney = M('user') -> getFieldByUserId($this->user_id,'fxmoney');
		if($fxmoney > 0){
		    $this -> assign('fxmoney',$fxmoney);
        }
		
        //修改地址
        $is_wx = $this -> is_weixin();
        M('shop_order') -> where(['id' => $order_id]) -> setField('address_id',$address['address_id']);
        $this -> assign('data',$data);
        $this -> assign('is_wx',$is_wx);
        $this -> assign('info',$info);
        $this -> assign('add',$address);
        $this -> display();
    }

    /*生成微信支付二维码*/
    function weixin(){
        $pay_id = I('sn');
		$pay_id = $pay_id * 1;
        $shop_order = M('shop_order');
		//dump($pay_id);
        if(!$pay_id){echo "无效订单，无法进行支付";exit;}
        //订单总金额
        $order_info = $shop_order -> where("id = '$pay_id' ") -> find();
        if(!$order_info){echo "无效订单，无法进行支付";exit;}
        if($order_info['state'] == 1){echo "订单已支付，请勿重复支付";exit;}
		if($order_info['user_id'] != $this -> user_id){
			echo '数据错误，请重试';exit;
		}
        $total_fee = $order_info['zonge'];$good_name = "fukuan";
        $out_trade_no = 'w' . '2017' . $this->user_id . time();
		M('shop_order') -> where(['id' => $pay_id]) -> setField('order_sn',$out_trade_no);
        $notify_url = "http://".$_SERVER['SERVER_NAME'].U('/Pay/Notify/shop');//交易成功后通知地址
        $weixin = A("Wxapi/Weixin");
        //file_put_contents('qianming.txt',$total_fee . ',' . $out_trade_no . ',' . $notify_url . ',' . $pay_id . ',' . $good_name);
        $code_url = $weixin -> get_prepay_id($total_fee*100,$out_trade_no,$notify_url,$pay_id,$good_name);
        $order_id = $order_info['id'];
        //M('shop_order') -> where("id = '$order_id' ") -> setField('code_url',$code_url);
        $this -> assign('code_url',$code_url);//exit;
        $this -> assign('order_info',$order_info);//exit;
        $this -> assign('pay_id',$pay_id);
        $this -> display();
    }

    function wxpay_qr(){
        import("Org.Util.Erweima");
        $value=I('get.url');
        $errorCorrectionLevel = "L";
        $matrixPointSize = "6";
        \QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize,1,true);
    }

    function order_state_sure(){
        /*订单支付完成，更改订单状态，增加资格数据，增加层级分红数据网络*/
        $pay_id = I("pay_id");
        /*确定订单拥有者身份*/
        $shop_order = M('shop_order');
        $order = $shop_order -> getById($pay_id);
        if($order['state'] == 1){
            $arr['success'] = 1; $arr['error_info'] = '订单已完成！';
        }else{
            $arr['success'] = 0; $arr['error_info'] = '未查询到订单支付成功，请点击重新查询';
        }
        echo json_encode($arr);
    }

    //支付购买商品
    public function zhifu(){
        $fee=I('money');
        $daijin_id=I('daijin_id');
        //$fee = utf8_encode($fee);
        $fee = $fee;
        $fh_name = I('fh_name');
        $fh_tel = I('fh_tel');
//        $fee = $_POST['money'];
        $return = I('order_id');
		$return = $return * 1;
        $abcd = M('shop_order') -> find($return);
        if(!$abcd){
            exit;
        }
        if($abcd['user_id'] != $this->user_id){
            exit;
        }
		if($abcd['state'] == 1){exit;}
        //M('shop_order') -> where(['id' => $return]) -> setField('fh_name',$fh_name);
       // M('shop_order') -> where(['id' => $return]) -> setField('fh_tel',$fh_tel);
		$fee = $abcd['zonge'];
        $total_fee = $fee*100;
        if($fee == 0){
            $this -> setShop($return);
            $paysign['success'] = -4;
            echo json_encode($paysign);
            exit;
        }
        $weixin = A("Wxapi/Weixin");
        $out_trade_no = 'w' . "2017".$this->user_id.time();//订单号
		M('shop_order') -> where(['id' => $return]) -> setField('order_sn',$out_trade_no);
        $notify_url = "http://".$_SERVER['SERVER_NAME'].U('/Home/Notice/buy_yz');//交易成功后通知地址
        $openid = M('users') -> getFieldByUser_id($this->user_id,'openid');//openid信息
        $prepay_id = $weixin -> get_prepay_idd($openid,$total_fee,$out_trade_no,$notify_url,$return,'shop');

        $paysign = $weixin->paysign($prepay_id);
        $paysign['timeStamp'] = (string)$paysign['timeStamp'];
        $paysign['success'] = 1;
        echo json_encode($paysign);
    }


    /**
     * 使用复消积分为0的时候执行，扣除复消积分，改变状态
     */
    public function fxZhifu(){
        $fee=I('money');
        $fee = $fee;
        $return = I('order_id');
        $abcd = M('shop_order') -> find($return);
        if(!$abcd){
            exit;
        }
        if($abcd['user_id'] != $this->user_id){
            exit;
        }
        if($abcd['fxmoney'] < 1600 || $abcd['zonge'] != 0){
            exit;
        }
        if($abcd['state'] != 0){
            exit;
        }
//        $res = M('user') -> where("user_id = {$this->user_id}") -> setDec('fxmoney',$abcd['fxmoney']);
//        if($res){
            $pay = A('Pay/Notify');
            $pay -> shop1($return,2);
            echo 0;
//        }else{
//            echo -1;
//        }

    }

    public function setFuXiao(){
        $money = I('post.money');
        $order_id = I('post.order_id')*1;
        $flag = I('post.flag');
        $zonge = I("post.zonge");
        if($flag == 1){
            $fxmoney = $zonge - $money;
            $res = M('shop_order') -> where("id = {$order_id}") -> setField('fxmoney',$fxmoney);
            $res = M('shop_order') -> where("id = {$order_id}") -> setField('zonge',$money);
            //把另外一个表的数据也减去
            M('shop_order_detail') -> where("order_id = {$order_id}") -> setField('good_price',$money);
            M('user') -> where("user_id = {$this->user_id}") -> setDec('fxmoney',$fxmoney);
        }else{
            $fxmoney = M('shop_order') -> getFieldById($order_id,'fxmoney');
            $res = M('shop_order') -> where("id = {$order_id}") -> setField('fxmoney',0);
            $res = M('shop_order') -> where("id = {$order_id}") -> setField('zonge',$zonge);
            M('shop_order_detail') -> where("order_id = {$order_id}") -> setField('good_price',$zonge);
            M('user') -> where("user_id = {$this->user_id}") -> setInc('fxmoney',$fxmoney);
        }
        if($res){
            echo 0;
        }
    }
	
    //显示收获地址
    public function address(){
        $data = M('user_address') -> where(['user_id' => $this->user_id]) -> select();
//		dump($data);
		//exit;
        $this -> assign('data',$data);
        $this -> display();
    }

    public function add_del(){
        $address_id = $_POST['id'];
        $res = M('user_address') -> delete($address_id);
        if($res){
            echo 1;
        }else{
            echo 2;
        }
    }


    public function add_save(){
        $add_id=I('id');//$order_id=I('order_id');
		$add=M('user_address')->where("address_id={$add_id}")->find();
		$this->assign("add",$add);
		//$this->assign("order_id",$order_id);
		$this->display();
    }
	
	public function save(){
		$id=I('add_id');
	   $data['username']=I('username');	
	   $data['telphone']=I('telphone');	
	   $data['user_id']=$this->user_id;	
	   	
	   $data['city']=I('province1') . I('city1') . I('area1');
	   $data['address']=I('address');
	   $data['code'] = I('province');
	   if(!$data['code']){
		   exit;
	   }
	   $re=M('user_address')->where("address_id={$id}")->save($data);
	   if(I('true')==1){
        $re=M('user_address')->where("user_id={$this->user_id}")->setField('is_true',0);
		$re=M('user_address')->where("address_id={$id}")->setField('is_true',1);
        if($re){echo 1;exit;}else{echo 2;exit;} 		
	   }	   
	   if($re){echo 1;exit;}else{echo 2;exit;} 		
	}  
	
	//设置默认地址
	public function add_moren(){
		$id=I('id');
		$address=M('user_address');
        $re=$address->where("user_id={$this->user_id}")->setField('is_true',0);
		$re=$address->where("address_id={$id}")->setField('is_true',1);
        if($re){echo 1;}else{echo 2;}		
	}
	
	//添加地址
	public function	 add_address(){
		$this->display();
	}
	
	public function add(){
	   $a=M('user_address')->where("user_id={$this->user_id} and is_true=1")->find();
	   if(!$a){
		   $data['is_true']=1;
	   }
	   $data['username']=I('username');
	   $data['telphone']=I('tel');
	   $data['user_id']=$this->user_id;	
	   $data['city']=I('province1') . I('city1') . I('area1');
	   if(!$data['city']){
		   exit;
	   }
	   $data['address']=I('add');
	   $data['code']=I('province');
	   if(!$data['code']){
		   exit;
	   }
	   $id=M('user_address')->add($data);
	   if(I('is_true')==1){
        $re=M('user_address')->where("user_id={$this->user_id}")->setField('is_true',0);
		$re=M('user_address')->where("address_id={$id}")->setField('is_true',1);
        if($re){echo 1;exit;}else{echo 2;exit;} 		
	   }
	   if($id){echo 1;}else{echo 2;}
	}



    //我要开店
    public function kaidian(){
        if(IS_GET){
            $info = M('dailifee') -> select();
            $info = $info[0];
            $this -> assign('money',$info['fee']);
            $this -> display();
        }
    }

    //发送验证码
    public function getCode(){
        $juhe = A("Xigua/Juhe");
        $code = rand(100000,999999);
        $tel = $_POST['tel'];
		$res = M('user') -> where(['tel' => $_POST['tel']]) -> select();
        if($res){
           echo -2;
            exit;
        }else{
		
        }
        if (!$tel){$data = 0;}
        $appname = "橙子乐购商城";
        $time = session('time');
        if(time() - $time < 60){
            $data = -1;
        }else{
            //file_put_contents('abc.txt',$code . ',' . $tel . $appname);
            $juhe-> msg_everify($code,$tel,$appname);
            session('xigua_code',$code);
            $data = 1;
            session('time',time());
        }
        $this->ajaxReturn($data);

    }
	
	public function yzCode(){
		$yz_number = $_POST['yz_number'];
		$code = session('xigua_code');
       
        if($yz_number != $code){
            echo -1;
            exit;
        }
        $res = M('user') -> where(['tel' => $_POST['tel']]) -> select();
        if($res){
          
        }else{
			
        }
		echo 0;
	}

    //用户购买代理
    public function kaidianChongZhi(){
        $data['tel'] = $_POST['tel'];
        
		$is_true = M('user') -> getFieldByUserId($this->user_id,'is_true');
		if($is_true == 1){
			$paysign['success'] = -3;
			echo json_encode($paysign);
			exit;
		}
        
        $fee = I('money');
        $data['name'] = $_POST['name']; 
        M('user') -> where(['user_id' => $this->user_id]) -> save($data);
        $data['user_id'] = $this->user_id;
        $data['money'] = $fee;
        $data['time'] = time();
        //记录购买信息
        $return = M('goumai')->add($data);
        $total_fee = $fee*100;
        $weixin = A("Wxapi/Weixin");
        $out_trade_no = "2017".$this->user_id.time();//订单号
        $notify_url = "http://".$_SERVER['SERVER_NAME'].U('/Home/Notice/buy_dl');//交易成功后通知地址
        $openid = M('users') -> getFieldByUser_id($this->user_id,'openid');//openid信息
//        file_put_contents('45.txt',$openid);
        $str = utf8_encode('购买代理');
        $prepay_id = $weixin -> get_prepay_id($openid,$total_fee,$out_trade_no,$notify_url,$return,'aa');
        $paysign = $weixin->paysign($prepay_id);
        file_put_contents('23.txt',$paysign);
        $paysign['timeStamp'] = (string)$paysign['timeStamp'];
        $paysign['success'] = 1;
        echo json_encode($paysign);
    }

	//购买代理的跳转图片
	public function tupian(){
		$this -> display();
	}
	
	//购买代理的跳转图片
	public function tupian1(){
		$this -> display();
	}
	
	public function testZf(){
	    file_put_contents('123.txt',$_POST['res']);
    }
}