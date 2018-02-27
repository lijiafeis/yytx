<?php
namespace Admin\Controller;
use Think\Controller;

class MainController extends ActionController{
	/* 用户欢迎页 */
	function __construct(){
		parent::__construct();
		
		if(!session('admin_id')){
			$this->error('您还未登录，请登录',U('User/index'),5);exit;
		}

	}
	
	function index(){
        $shop_order = M('shop_order');
        /* 商城成交总额 */
        $shop_all_fee = $shop_order -> where("state = 1")-> sum('zonge');
        $this->assign('shop_all_fee',$shop_all_fee);



        /* 当天营业额 */
        //计算当天的营业额
        $sql1 = "select sum(zonge) a from wx_shop_order where state = 1 and  from_unixtime(
time,'%Y-%m-%d') = current_date();";
        $model = M();
        $data = $model -> query($sql1);

        $this -> assign('zonge',$data[0]);



//		/* 当天的交易额 */
        $sql1 = "select sum(good_num) a from wx_shop_order_detail where state = 1 and  from_unixtime(
time,'%Y-%m-%d') = current_date();";
        $model = M();
        $data = $model -> query($sql1);
        $this->assign('user_all_broke',$data[0]['a']);

        /* 总的订单数 */
        $user_all_tixian = M('shop_order_detail')
            -> field("sum(good_num) as a")
            -> where("state = 1")
            -> select();
        $this->assign('user_all_tixian',$user_all_tixian[0]['a']);


        //提现金额
        $tixian_all_money = M('tixian')
            -> field("sum(sjmoney) as a")
            -> where("type = 1")
            -> select();
        $this -> assign('tixian',$tixian_all_money[0]['a']);

        $sql1 = "select sum(sjmoney) a from wx_tixian where type = 1 and  from_unixtime(
time,'%Y-%m-%d') = current_date();";
        $model = M();
        $data = $model -> query($sql1);
        $this->assign('currentTx',$data[0]['a']);


        //钱包余额
        $money = M('user') -> field("sum(money) as a") -> select();
        $this -> assign('qb_money',$money[0]['a']);

        //当天新进钱包余额
        $sql1 = "select sum(money) a from wx_yongjin where from_unixtime(
time,'%Y-%m-%d') = current_date();";
        $model = M();
        $data1 = $model -> query($sql1);
        $this->assign('xinjinmoney',$data1[0]['a']);

        //支付宝支付
        $zhifubao = M('shop_order')
            -> field("sum(zonge) as a")
            -> join("left join __ORDER_SN__ as b on wx_shop_order.id = b.order_id")
            -> where("state = 1 and b.order_sn like 'z%'")
            -> select();
        $this -> assign('zhifubao',$zhifubao[0]['a']);

        //微信支付
        $weixin = M('shop_order')
            -> field("sum(zonge) as a")
            -> join("left join __ORDER_SN__ as b on wx_shop_order.id = b.order_id")
            -> where("state = 1 and b.order_sn like 'w%'")
            -> select();
        $this -> assign('weixin',$weixin[0]['a']);

        //手动确认
        $shoudong = M('shop_order')
            -> field("sum(zonge) as a")
            -> join("left join __ORDER_SN__ as b on wx_shop_order.id = b.order_id")
            -> where("state = 1 and b.order_sn like 's%'")
            -> select();
        $this -> assign('shoudong',$shoudong[0]['a']);

        //查询商城总金额
        $good_zonge = M('goods_order') -> where(['state' => 1]) -> sum('price');
        $this -> assign('good_zonge',$good_zonge);
        //支付宝
        $good_weixin = M('goods_order') -> where("state = 1 and pay_type in (0,1)") -> sum('price');
        $this -> assign('good_weixin',$good_weixin);
        //微信
        $good_zhifubao = M('goods_order') -> where(['state' => 1,'pay_type' => 2]) -> sum('price');
        $this -> assign('good_zhifubao',$good_zhifubao);
        //未发货
        $good_type1 = M('goods_order') -> where(['state' => 1,'type' => 0]) -> count();
        $this -> assign('good_type1',$good_type1);
        //未收货
        $good_type2 = M('goods_order') -> where(['state' => 1,'type' => 1]) -> count();
        $this -> assign('good_type2',$good_type2);
        //完成
        $good_type3 = M('goods_order') -> where(['state' => 1,'type' => 2]) -> count();
        $this -> assign('good_type3',$good_type3);


        /* 当天营业额 */
        //计算当天的营业额
        $sql1 = "select sum(price) a from wx_goods_order where state = 1 and  from_unixtime(
pay_time,'%Y-%m-%d') = current_date();";
        $model = M();
        $good_price = $model -> query($sql1);

        $this -> assign('good_price',$good_price[0]['a']);

        $this->display();
	}
	
	
	
	
	function day_add($time,$date,$mdate){
		$users = M('users');
		$day = date("m-d",strtotime($date));
		$time1 = strtotime(date("Y-m-d",strtotime($date)));//dump($time1);
		$time2 = strtotime(date("Y-m-d",strtotime($mdate)));//dump($time2);
		$num = $users -> where(" subscribe_time > '$time1' and subscribe_time < '$time2' ") -> count();
		$result = array('day'=>$day,'num'=>$num);
		return $result;
	}
	
//	function zhitui(){
//		if($_POST){
//			$fenhong_fee = $_POST['fenhong_fee'];
//			F('fenhong_fee',$fenhong_fee,DATA_ROOT);
//			$this->success('保存成功');exit;
//		}
//		$fenhong_fee = F('fenhong_fee','',DATA_ROOT);
//		$this->assign('fenhong_fee',$fenhong_fee);
//		$week = date("w",time());
//		if($week <= 3){
//			$start = mktime(0,0,0,date('m'),date('d')-date('w')+1-5,date('Y'));
//			$end = mktime(0,0,0,date("m"),date("d")-date("w")+3,date("Y"));
//		}else{
//			$start = mktime(0,0,0,date("m"),date("d")-date("w")+3,date("Y"));
//			$end = mktime(0,0,0,date("m"),date("d")-date("w")+10,date("Y"));
//		}
//
//		$start_date = date("Y-m-d H:i:s",$start);
//		$end_date = date("Y-m-d H:i:s",$end);
//		$this -> assign('start',$start_date);
//		$this -> assign('end',$end_date);
//		$this->display();
//	}
	
	function get_team_sale($user_id){
		$week = date("w",time());
		if($week <= 3){
			$start = mktime(0,0,0,date('m'),date('d')-date('w')+1-5,date('Y'));
			$end = mktime(0,0,0,date("m"),date("d")-date("w")+3,date("Y"));
		}else{
			$start = mktime(0,0,0,date("m"),date("d")-date("w")+3,date("Y"));
			$end = mktime(0,0,0,date("m"),date("d")-date("w")+10,date("Y"));
		}
		//echo date("Y-m-d H:i:s",$start);
		//echo date("Y-m-d H:i:s",$end);
		
		$xigua_users = D('Xigua/users');$sale_fee = 0;$shop_order = M('shop_order');
		for($i=1;$i<=9;$i++){
			$arr = $xigua_users -> get_team_level($user_id,$i);
			if($arr == null){break;}else{
				foreach($arr as $val){
					if($val['agent'] > 0){
						$fee = $shop_order -> where("user_id = '$val[user_id]' and is_true = 1 and pay_time >= '$start' and pay_time < '$end' ") -> sum('total_fee');
						$sale_fee = $sale_fee*1 + $fee*1;
					}
				}
				
			}
		}
		return $sale_fee;
	}
	
	function zhitui_ajax(){
		//echo $_GET['json'];
		/********************
		查询出直推满30个的人，并且校验红包领取数是否满足4w 
		********************/
		
		$users = M('users');
		$infor = $users -> query("select pid,count(*) from wx_users where agent < 10  group by pid order by count(*) desc ");
		$i=0;$info = array();
		
		
		
		foreach($infor as $v){
			if($v['count(*)'] > 0){
				$user_id = $v['pid'];
				if($user_id > 0){
					$info[$i] = $users ->field("user_id,nickname,headimgurl,agent") -> where("user_id = '$user_id' ") -> find();
					$info[$i]['count'] = $v['count(*)'];
					$info[$i]['sale_fee'] = $this->get_team_sale($user_id);
					$i++;
				}
				
			}else{
				break;
			}
		}
		$pagecount = 10;
		$count = count($info);
		$Page = new \Think\Pageajax($count,$pagecount);
		$i=0;$v_info = array();
		foreach($info as $k=>$v){
			if($i >=$Page->listRows){break;}
			if($k >= $Page->firstRow){
				$v_info[$i]= $info[$k];$i++;
			}
		}
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this -> assign('vinfo',$v_info);
		$this->display();
	}
	
}