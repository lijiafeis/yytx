<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20 0020
 * Time: 13:46
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Pageajax;

class TestController extends Controller{
	public function test(){
		 $data = M('user') 
		-> alias('a')
		-> field("a.user_id")
		-> join("left join __SHOP_ORDER_DETAIL__ as b on a.user_id = b.user_id")
		-> where("sj_userid = 90 and b.state = 1 and b.flag = 0")
		-> group('b.user_id')
		-> select();
		$number = count($data);
		dump($data);
		dump($number);
        return $number;
		
	}

    public function myYeji(){
	    $this -> user_id = 1;
        $zonge = M('zonge') -> field('number') -> where(['user_id' => $this -> user_id]) -> find();
        $today = $this -> getMyYeji($this -> user_id);
        $arr = array();
        $names = $this -> getAllName($this -> user_id,$arr);
        dump($arr);exit;
        $this -> assign('zonge',$zonge['number']);
        $this -> assign('today',$today);
        $this -> assign('name',$names);
        $this -> display();
    }

    private function getAllName($user_id,&$arr){
        if(!$user_id){return 0;}
        $time = strtotime('today');
        $arr1 = M('shop_order_detail')
            -> alias('a')
            -> field('b.name')
            -> join("left join __USER__ as b on a.user_id = b.user_id")
            -> where("a.state = 1 and a.time > {$time} and b.sj_userid = {$user_id}")
            -> group('a.user_id')
            -> select();
        if($arr1){
            $arr = array_merge_recursive($arr,$arr1);
        }
        $res = M('user') -> field('user_id,sj_userid') -> where("sj_userid = {$user_id}") -> select();
        if(!$res){
            return 1;
        }

        foreach ($res as $k => $v){
            $this -> getAllName($v['user_id'],$arr);

        }
        return 1;
    }

    private function getMyYeji($user_id){
        $zonge = 0;
        if(!$user_id){return 0;}
        //计算自己的
        $time = strtotime('today');
        $money = M('shop_order_detail')
            -> alias('a')
            -> field("sum(a.good_price) as money")
            -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
            -> where(" b.new = 1 and a.state = 1 and a.user_id = {$user_id} and time > {$time}")
            ->select();
        $zonge += $money[0]['money'];
        $res = M('user') -> field('user_id,sj_userid') -> where("sj_userid = {$user_id}") -> select();
        if(!$res){
            return $zonge;
        }

        foreach ($res as $k => $v){
            $yj = $this -> getMyYeji($v['user_id']);
            $zonge += $yj;

        }
        return $zonge;
    }


}