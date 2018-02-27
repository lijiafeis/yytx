<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/30 0030
 * Time: 11:42
 * 亿赢天下新商城的前台控制器
 */
namespace Home\Controller;
use Think\Controller;

class GoodsController extends Controller{
    /*用户来，判断session存不存在，*/
    function __construct(){
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
		
		
    }

    //判断打开方式
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return 1;
        }
        return 2;
    }

    public function index(){
        $this -> display();
    }

    /**
     * 显示主页面
     */
    public function shop(){
//        if($this -> user_id != 418 && $this -> user_id != 5 && $this -> user_id != 6){
//			echo '暂未开启';exit;
//		}
        $info = M('goods') -> field('id,good_name,good_pic,good_price')
            -> where(['id' => ['gt',13]])
            -> order("code asc") -> select();
        foreach ($info as $k => $v){
            $pic = explode(',',$v['good_pic']);
			foreach($pic as $k1 => $v1){
				if($v1){
					$info[$k]['pic'] = $v1;break;
				}
			}
        }
        $token = I('get.agent_id');
        $this -> assign('info',$info);
        $this -> display();
    }

    /**
     * 通过商品id获取商品详情
     */
    public function goodInfo(){
        $good_id = I('good_id','','htmlspecialchars')*1;
        //这个只有从Game_game.html跳转过来携带，其他的一律不带这个参数，表示生成订单时是游戏订单。
        $order_type = I('get.order_type','','htmlspecialchars')*1;
        if($order_type){
            $order_type = 1;
        }else{
            $order_type = 0;
        }
        if(!$good_id){
            echo '参数缺失';exit;
        }
        $info = M('goods') -> where(['id' => $good_id]) -> find();
        if(!$info){
            echo '无当前商品信息';
        }
        $pic = explode(',',$info['good_pic']);
        //购买时缩略图
        foreach ($pic as $k => $v){
            if($v){
                $info['pic1'] = $v;
            }
        }
        $info['pic'] = $pic;
        $spec = json_decode($info['good_spec'],true);
		if($spec[0]['price']){
			$info['price'] = $spec[0]['price'];
		}
		if($spec[1]['price']){
			$info['price'] .=  '-' . $spec[1]['price'].'';
		}
		if($order_type == 1){
		    unset($spec[1]);
        }
        $this -> assign('spec',$spec);
        $this -> assign("good_info",$info);
		$this -> assign('order_type',$order_type);
        $this -> display();
    }

    /**
     * 通过good_id 和spec的下表得到价格
     */
    public function getSpecPrice(){
        $good_id = I('good_id','','htmlspecialchars')*1;
        $spec_index = I('spec_index','','htmlspecialchars')*1;
        if(!$good_id || !is_int($spec_index)){
            $array = array(
              'code' => -1,
                'info' => '数据错误'
            );
            $this -> ajaxReturn($array);exit;
        }
        $spec = M('goods') -> field('good_spec') -> where(['id' => $good_id]) -> find();
        if(!$spec){
            $array = array(
                'code' => -1,
                'info' => '当前商品信息不存在'
            );
            $this -> ajaxReturn($array);exit;
        }
        $spec = json_decode($spec['good_spec'],true);
        $spec = $spec[$spec_index];
        if($spec){
            $array = array(
                'code' => 1,
                'info' => $spec
            );
            $this -> ajaxReturn($array);exit;
        }
        $array = array(
            'code' => -1,
            'info' => '数据错误,请刷新重试'
        );
        $this -> ajaxReturn($array);
    }

    /**
     * 前台在商品详情页异步生成订单
     */
    public function createOrder(){
        $good_id = I('good_id','','htmlspecialchars')*1;
        $spec = I('spec','','htmlspecialchars')*1;
        $number = I('number','','htmlspecialchars')*1;
        $order_type = I('post.order_type','','htmlspecialchars')*1;
        if($order_type == 1){
            //判断是否超标
            $flag = $this -> getUserTodayMoney();
            if($flag == -1){
                $array = array(
                    'code' => -1,
                    'info' => '请注意劳逸结合'
                );
                $this -> ajaxReturn($array);exit;
            }else if($flag == -2){
                $array = array(
                    'code' => -1,
                    'info' => '请注意劳逸结合'
                );
                $this -> ajaxReturn($array);exit;
            }
        }
        if(!$good_id || !$spec || !$number){
            $array = array(
                'code' => -1,
                'info' => '数据错误'
            );
            $this -> ajaxReturn($array);exit;
        }
        $spec = $spec - 1;
        //通过信息生成订单信息
        $goodInfo = M('goods') -> where(['id' => $good_id]) -> find();
        if(!$goodInfo){
            $array = array(
                'code' => -1,
                'info' => '数据错误'
            );
            $this -> ajaxReturn($array);exit;
        }
        $data['user_id'] = $this->user_id;
        $data['good_id'] = $good_id;
        $data['good_name'] = $goodInfo['good_name'];
        $data['good_num'] = $number;
        $spec = json_decode($goodInfo['good_spec'],true)[$spec];
        //如果没有这个属性是不能生成订单的。
        if(!$spec){
            $array = array(
                'code' => -1,
                'info' => '数据错误'
            );
            $this -> ajaxReturn($array);exit;
        }
        $pic = explode(',',$goodInfo['good_pic']);
        foreach ($pic as $k => $v){
            if($v){
                $data['good_pic'] = $v;break;
            }
        }
        $data['good_spec'] = $spec['spec'];
        $data['price'] = $number * $spec['price'];
        $data['create_time'] = time();

        //根据order_type确定是否是游戏订单，order_type = 1 是 0 不是，从goodInfo中跳转过来
        if($order_type){
            $data['is_game'] = 1;
        }else{
            $data['is_game'] = 0;
        }
        $res = M('goods_order') -> add($data);
        if($res){
            $array = array(
                'code' => 1,
                'info' => $res
            );
            $this -> ajaxReturn($array);exit;
        }
        $array = array(
            'code' => -1,
            'info' => '申请失败'
        );
        $this -> ajaxReturn($array);exit;
    }

    private function getUserTodayMoney(){
        //game_refund表的price当天的相加是否大于三万
        $time = strtotime('today');
        $successMoney = M('game_refund') -> where(['user_id' => $this -> user_id,'create_time' => ['gt',$time]]) -> sum('price');
        //计算当天的订单没有中的订单的金额相加是否超过6万
        $failMoney = M('goods_order') -> where(['state' => 1,'is_game' => 1,'game_state' => 1,'user_id' => $this -> user_id,'create_time' => ['gt',$time]]) -> sum('game_price');
        if($successMoney >= 30000){
            return -1;
        }else if($failMoney >= 60000){
            return -2;
        }else{
            return 1;
        }
    }

    /**
     * 订单详情页
     */
    public function order(){
        $order_id = I('get.order_id','','htmlspecialchars')*1;
        if(!$order_id){
            $order_id = session('order_id');
        }else{
            session('order_id',$order_id);
        }
        if(!$order_id){
            echo '参数缺失';exit;
        }
        $address = M('user_address') -> where(['user_id' => $this->user_id,'is_true' => 1]) -> find();
        $info = M('goods_order') -> find($order_id);
        if(!$info || $info['state'] != 0){
            exit;
        }
        if($info['user_id'] != $this->user_id){
            exit;
        }

        //判断是否有复消积分
        $userInfo = M('user') -> field('fxmoney,money') -> where(['user_id' => $this->user_id]) -> find();
        if($userInfo['fxmoney'] > 0){
            $this -> assign('fxmoney',$userInfo['fxmoney']);
        }else{
            $this -> assign('fxmoney',0);
        }
        if($userInfo['money'] > 0){
            $this -> assign('money',$userInfo['money']);
        }else{
            $this -> assign('money',0);
        }
        //修改地址
        $is_wx = $this -> is_weixin();
        $this -> assign('data',$info);
        $this -> assign('is_wx',$is_wx);
        $this -> assign('add',$address);
        $this -> display();
    }

    /**
     * 订单页面用户点击，显示不同的类型
     * flag = 1 使用积分或余额 0 取消使用
     * type = 1 复消积分 2 余额
     */
    public function setFuXiao(){
        $order_id = I('post.order_id')*1;
        $flag = I('post.flag')*1;
        $type = I('post.type')*1;
        if(!$order_id){
            echo 0;exit;
        }
        if($flag == 1){
            $orderInfo = M('goods_order') -> field('price,user_id') -> where(['id' => $order_id]) -> find();
            if(!$orderInfo){
                echo 0;exit;
            }
            $userInfo = M('user') -> field('money,fxmoney') -> where(['user_id' => $orderInfo['user_id']]) -> find();
            $zonge = $orderInfo['price'];
            if($type == 1){
                //复消积分
                $money = $userInfo['fxmoney'];
                $data['fxmoney'] = $money;
                $zonge = $zonge - $money;
                if($zonge <= 0){
                    $money = $orderInfo['price'];
                    $data['fxmoney'] = $money;
                    $zonge = 0;
                }
                M('user') -> where("user_id = {$this->user_id}") -> setDec('fxmoney',$money);
            }else if($type == 2){
                //余额
                $money = $userInfo['money'];
                $data['money'] = $money;
                $zonge = $zonge - $money;
                if($zonge <= 0){
                    $money = $orderInfo['price'];
                    $data['money'] = $money;
                    $zonge = 0;
                }
                M('user') -> where("user_id = {$this->user_id}") -> setDec('money',$money);
            }
            $data['price'] = $zonge;
            $res = M('goods_order') -> where(['id' => $order_id]) -> save($data);
            //把另外一个表的数据也减去
        }else{
            $orderInfo = M('goods_order') -> field('price,fxmoney,money,user_id') -> where(['id' => $order_id]) -> find();
            if(!$orderInfo){
                echo 0;exit;
            }
            if($type == 1){
                //把复消积分加上
                $zonge = $orderInfo['price'] + $orderInfo['fxmoney'];
                $data['fxmoney'] = 0;
                $data['price'] = $zonge;
                $res = M('goods_order') -> where(['id' => $order_id]) -> save($data);
                M('user') -> where(['user_id' => $orderInfo['user_id']]) -> setInc('fxmoney',$orderInfo['fxmoney']);
            }else if($type == 2){
                //把复消积分加上
                $zonge = $orderInfo['price'] + $orderInfo['money'];
                $data['money'] = 0;
                $data['price'] = $zonge;
                $res = M('goods_order') -> where(['id' => $order_id]) -> save($data);
                M('user') -> where(['user_id' => $orderInfo['user_id']]) -> setInc('money',$orderInfo['money']);
            }
        }
        if($res){
            echo 0;
        }
    }

    /**
     * 余额为0
     */
    public function fxZhifu(){
        $fee=I('money')*1;
        $return = I('order_id')*1;
        $address_id = I('post.address_id')*1;
        $abcd = M('goods_order') -> find($return);
        if(!$abcd || $abcd['user_id'] != $this->user_id || $abcd['price'] != 0 || $abcd['state'] !=0){
            exit;
        }
        $address = M('user_address') -> where(['address_id' => $address_id]) -> find();
        //改变状态，发模板消息
        $data['state'] = 1;
        $data['pay_time'] = time();
        $data['pay_type'] = 3;
        $data['name'] = $address['username'];
        $data['address'] = $address['username'] . ',' . $address['telphone'] . ',' . $address['city'] . $address['address'];
        $data['order_sn'] = $this -> user_id . time();
        $res = M('goods_order') -> where(['id' => $return]) -> save($data);
        if($res){
            //发送模板消息
            $system = A("Pay/Notify1");
            $system -> sendTemplate($return);
            echo 0;
        }else{
            echo -1;exit;
        }
    }

    //Goods_order在微信内支付跳转过来购买商品
    public function zhifu_order(){
        $return = I('order_id');
        $return = $return * 1;
        $abcd = M('goods_order') -> find($return);
        if(!$abcd || $abcd['user_id'] != $this->user_id || $abcd['state'] != 0){
            exit;
        }
        $fee = $abcd['price'];
        $total_fee = $fee*100;
        if($fee == 0){
            exit;
        }
        $address = M('user_address') -> where(['user_id' => $abcd['user_id'],'is_true' => 1]) -> find();
        if(!$address){
            $returnInfo['info'] = '请选择收获地址';
            $returnInfo['success'] = 0;
            $this -> ajaxReturn($returnInfo);exit;
        }
        $data['name'] = $address['username'];
        $data['address'] = $address['username'] . ',' . $address['telphone'] . ',' . $address['city'] . $address['address'];
        $weixin = A("Wxapi/Weixin");
        $out_trade_no = 'w'.$this->user_id.time();//订单号
        $data['order_sn'] = $out_trade_no;
        M('goods_order') -> where(['id' => $return]) -> save($data);
        $notify_url = "http://".$_SERVER['SERVER_NAME'].U('/Pay/Notify1/shop');//交易成功后通知地址
        $openid = M('users') -> getFieldByUser_id($this->user_id,'openid');//openid信息
        $prepay_id = $weixin -> get_prepay_idd($openid,$total_fee,$out_trade_no,$notify_url,$return,'shop');
        $paysign = $weixin->paysign($prepay_id);
        $paysign['timeStamp'] = (string)$paysign['timeStamp'];
        $paysign['success'] = 1;
        echo json_encode($paysign);
    }


    /*生成微信支付二维码*/
    function weixin(){
        $pay_id = I('sn');
        $pay_id = $pay_id * 1;
        $goods_order = M('goods_order');
        //dump($pay_id);
        if(!$pay_id){echo "无效订单，无法进行支付";exit;}
        //订单总金额
        $order_info = $goods_order -> where(['id' => $pay_id]) -> find();
        if(!$order_info){echo "无效订单，无法进行支付";exit;}
        if($order_info['state'] != 0){echo "订单已支付，请勿重复支付";exit;}
        if($order_info['user_id'] != $this -> user_id){
            echo '数据错误，请重试';exit;
        }
        $address = M('user_address') -> where(['user_id' => $order_info['user_id'],'is_true' => 1]) -> find();
        if(!$address){
            $returnInfo['info'] = '请选择收获地址';
            $returnInfo['success'] = 0;
            $this -> ajaxReturn($returnInfo);exit;
        }
        $data['name'] = $address['username'];
        $data['pay_type'] = 1;
        $data['address'] = $address['username'] . ',' . $address['telphone'] . ',' . $address['city'] . $address['address'];
        $total_fee = $order_info['price'];$good_name = "fukuan";
        $out_trade_no = 'w' . '2017' . $this->user_id . time();
        $data['order_sn'] = $out_trade_no;
        $goods_order -> where(['id' => $pay_id]) -> save($data);
        $notify_url = "http://".$_SERVER['SERVER_NAME'].U('/Pay/Notify1/shop');//交易成功后通知地址
        $weixin = A("Wxapi/Weixin");
        //file_put_contents('qianming.txt',$total_fee . ',' . $out_trade_no . ',' . $notify_url . ',' . $pay_id . ',' . $good_name);
        $code_url = $weixin -> get_prepay_id($total_fee*100,$out_trade_no,$notify_url,$pay_id,$good_name);
        $order_id = $order_info['id'];
        //M('goods_order') -> where("id = '$order_id' ") -> setField('code_url',$code_url);
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
        $goods_order = M('goods_order');
        $order = $goods_order -> getById($pay_id);
        if($order['state'] == 1){
            //判断当前订单是什么订单，如果是游戏订单要跳转到游戏的详情页面
            if($order['is_game'] == 1 && $order['game_type'] == 0 && $order['game_state'] == 0){
                $arr['success'] = 2; $arr['error_info'] = '订单已完成！';
            }else{
                $arr['success'] = 1; $arr['error_info'] = '订单已完成！';
            }
        }else{
            $arr['success'] = 0; $arr['error_info'] = '未查询到订单支付成功，请点击重新查询';
        }
        echo json_encode($arr);
    }

    public function redirectUrl(){
        $order_id = I('post.order_id');
        if(!$order_id){
            echo -1;exit;
        }
        $orderInfo = M('goods_order') -> field('state,type,is_game,game_type,game_state') -> where(['id' => $order_id]) -> find();
        if(!$orderInfo || $orderInfo['state'] != 1 || $orderInfo['is_game'] != 1 || $orderInfo['game_type'] != 0 || $orderInfo['game_state'] != 0){
            echo -1;exit;
        }else if($orderInfo && $orderInfo['state'] == 1 && $orderInfo['type'] == 0 || $orderInfo['is_game'] == 1 && $orderInfo['game_state'] == 0){
            echo 1;exit;
        }

    }


    /**
     * 显示我的订单
     */
    public function myOrder(){
        $this -> display();
    }

    /**
     * 根据传递过来的state判断显示那种状态
     */
    public function myOrderList(){
        $state = I('post.state');
        $page = I('get.p');
        $state = isset($state) ? $state : 1;
        $page = $page * 1;
        $start = ($page -1) * 10;
        switch ($state){
            case 1:
                $data = M('goods_order')
                    -> where(['user_id' => $this -> user_id,'state' => 0])
                    -> limit($start,10)
                    -> order('id desc')
                    -> select();
                break;
            case 2:
                $data = M('goods_order')
                    -> where(['user_id' => $this -> user_id,'state' => 1,'type' => 0])
                    -> limit($start,10)
                    -> order('id desc')
                    -> select();
                break;
            case 3:
                $data = M('goods_order')
                    -> where(['user_id' => $this -> user_id,'state' => 1,'type' => 1])
                    -> limit($start,10)
                    -> order('id desc')
                    -> select();
                break;
            case 4:
                $data = M('goods_order')
                    -> where(['user_id' => $this -> user_id,'state' => 1,'type' => 2])
                    -> limit($start,10)
                    -> order('id desc')
                    -> select();
                break;
            default:
                exit;
                break;
        }
        $this -> assign('data',$data);
        $this -> assign('state',$state);
        $this -> display();
    }

    /**
     * 删除未支付订单
     */
    public function delOrder(){
        $order_id = I('post.order_id');
        $orderInfo = M('goods_order') -> where(['id' => $order_id]) -> find();
        if(!$orderInfo || $orderInfo['state'] != 0 || $orderInfo['user_id'] != $this -> user_id){
            $array = array(
                'code' => -1,
                'info' => '数据错误'
            );
            $this -> ajaxReturn($array);exit;
        }
        //判断是否有复消积分和余额，返回
        $fxmoney = $orderInfo['fxmoney'];
        $money = $orderInfo['money'];
        if($fxmoney || $money){
            M('user') -> where(['user_id' => $orderInfo['user_id']]) -> setInc('money',$money);
            M('user') -> where(['user_id' => $orderInfo['user_id']]) -> setInc('fxmoney',$fxmoney);
        }
        //删除数据
        $res = M('goods_order') -> where(['id' => $order_id]) -> delete();
        if($res){
            $array = array(
                'code' => 1,
                'info' => '取消订单成功'
            );
            $this -> ajaxReturn($array);exit;
        }
        $array = array(
            'code' => -1,
            'info' => '取消订单失败'
        );
        $this -> ajaxReturn($array);exit;
    }

    public function shouhuo(){
        $order_id = I('post.order_id')*1;
        if(!$order_id){
            $array = array(
                'code' => -1,
                'info' => '数据错误'
            );
            $this -> ajaxReturn($array);exit;
        }
        $orderInfo = M('goods_order') -> where(['id' => $order_id]) -> find();
        if(!$orderInfo || $orderInfo['user_id'] != $this -> user_id || $orderInfo['type'] != 1){
            $array = array(
                'code' => -1,
                'info' => '数据错误'
            );
            $this -> ajaxReturn($array);exit;
        }
        $res = M('goods_order') -> where(['id' => $order_id]) -> setField("type",2);
        if($res){
            $array = array(
                'code' => 1,
                'info' => '收货成功'
            );
            $this -> ajaxReturn($array);exit;
        }
        $array = array(
            'code' => -1,
            'info' => '收货失败'
        );
        $this -> ajaxReturn($array);exit;
    }

    /**
     * 如果订单是游戏订单也就是订单中的is_game是1，显示可以玩游戏
     */
    public function orderInfo(){
        $order_id = I('order_id','','htmlspecialchars')*1;
        if(!$order_id){
            exit;
        }
        $info = M('goods_order')
            -> alias("a")
            -> field("a.*,b.game_pic")
            -> join("left join __GOODS__ as b on a.good_id = b.id")
            -> where(['a.id' => $order_id])
            -> find();
        if(!$info || $info['state'] != 1 || $info['is_game'] != 1 || $info['type'] != 0){
            echo '订单状态不对';
            exit;
        }
        //可能没有设置游戏图片
        if(!$info['game_pic']){
            $info['game_pic'] = $info['good_pic'];
        }
        //设置价格
        $info['price'] = $info['price'] + $info['fxmoney'] + $info['money'];

        $this -> assign('info',$info);
        $this -> display();
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







}