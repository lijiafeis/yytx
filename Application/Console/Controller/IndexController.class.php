<?php

namespace Console\Controller;
use Think\Controller;
use Think\Exception;

class IndexController extends Controller{
    static public $fee = array();
    static public $list = array();



    /**
     * 推荐奖
     */
	public function test(){
        ini_set('max_execution_time','0');
        $testModel = M();
        $testModel -> startTrans();
        try{
//返还推荐奖的部分
            $data = M('yongjin') -> field('id,user_id,order_id') -> where(" type = 0 and number = 1") -> select();


            foreach ($data as $k => $v){
                $tuijianjiang  = M('feetuijian') -> find(1);
                $tuijianjiang = $tuijianjiang['two'];
                $shopList = M('shop_order_detail') -> field('sum(good_num) as num') -> where("order_id = {$v['order_id']}") -> select();
                $tuijianjiang = $tuijianjiang * $shopList[0]['num'];
                //每个人返还推荐奖的第二个部分
                // dump($tuijianjiang);
                $res = M('user') -> where("user_id = {$v['user_id']}") -> setInc('money',$tuijianjiang);
                if($res){
                    $yytx1['number'] = 2;
                    $yytx1['time1'] = time();
                    M('yongjin') -> where("id = {$v['id']}") -> save($yytx1);
                    M('yongjin') -> where("id = {$v['id']}") -> setInc('money',$tuijianjiang);
                    $re = M('fee') -> where("user_id = {$v['user_id']}") -> select();
                    if($re){
                        //由当前人的数据
                        M('fee') -> where("user_id = {$v['user_id']}") -> setInc('w1',$tuijianjiang);
                        M('fee') -> where("user_id = {$v['user_id']}") -> setField('time',time());
                    }else{
                        $data2['user_id'] = $v['user_id'];
                        $data2['w1'] = $tuijianjiang;
                        $data2['time'] = time();
                        M('fee') -> add($data2);
                    }
                }
            }
//        exit;
            $this -> lingdao();
            $this -> fenhong();
            $this -> huikui();
            //备注：个人W2+W3+W4之和不能超过个人所购买W的两倍，超过之后没有W3、W4的收益
            $data = M('user') -> field("user_id,name") -> select();
            //记录人数数数组
            $noarray = array();
            foreach ($data as $k => $v){

                $res = M('fee') -> field('w2,w3,w4') -> where("user_id = {$v['user_id']}") -> select();
                //判断是否超过了
                $user_order_info = M('shop_order_detail') -> field('user_id,sum(good_price) as fee,min(time) as time') ->  where("user_id = {$v['user_id']} and state = 1 and flag = 0") -> select();
                $fee = $user_order_info[0]['fee'];
                $order_time = $user_order_info[0]['time'];
                if($order_time){
                    $zMoney = M('yongjin') -> where("user_id = {$v['user_id']} and is_flag = 0 and time >= {$order_time}") -> sum('money');
                    if($zMoney > $fee * 2){
                        M('shop_order_detail') -> where("user_id = {$v['user_id']} and state = 1 and flag = 0") -> setField('flag',1);
                        M('yongjin') -> where("user_id = {$v['user_id']}") -> setField('is_flag',1);
                        $noarray[] = array(
                            'user_id' => $v['user_id'],
                            'name' => $v['name'],
                        );
                    }
                }


                if(1 < 5){
                    //进行发放w3,w4
                    if($res[0]['w3'] <= 0){
                        continue;
                    }
                    $re = M('user') -> where("user_id = {$v['user_id']}") -> setInc('money',$res[0]['w3']);
                    $agent_id = M('user') -> getFieldByUserId($v['user_id'],'agent_id');
                    //记录到fee表中记录到佣金表中
                    if($re){
                        $data1['user_id'] = $v['user_id'];
                        $data1['money'] = $res[0]['w3'];
                        $data1['time'] = time();
                        $data1['type'] = 2;
                        $data1['agent_id'] = $agent_id;
                        M('yongjin') -> add($data1);
                    }
                    if($res[0]['w4'] <= 0){
                        continue;
                    }
                    $re = M('user') -> where("user_id = {$v['user_id']}") -> setInc('money',$res[0]['w4']);
                    //记录到fee表中记录到佣金表中
                    if($re){
                        $data2['user_id'] = $v['user_id'];
                        $data2['money'] = $res[0]['w4'];
                        $data2['time'] = time();
                        $data2['type'] = 3;
                        $data2['agent_id'] = $agent_id;
                        M('yongjin') -> add($data2);
                    }
                }



            }

            $testModel -> commit();
        }catch (Exception $e){
            $testModel -> rollback();
            file_put_contents('error_index.txt',$e,FILE_APPEND);
        }

        F('nojilu',null);
        F('nojilu',$noarray);
        $data = M('fee') -> select();
//        dump($data);
        $dataList = M('fee1') -> select();
        foreach ($dataList as $k => $v){
            M('fee1') -> delete($v['id']);
        }
        foreach ($data as $k => $v){
            M('fee1') -> add($v);
        }
        $sql = "TRUNCATE wx_fee";
        $m = M();
        $m -> query($sql);
	}


    /**
     * 领导奖w2
     * 领导奖（W2）改为加上自己的业绩。
    A→B→C→D→E→F→G→..........以此类推
    A的领导奖计算方式
    所有人购买特殊商品W之和（B+C+D+E+F+G+......）*10%=A1
    所有人购买特殊商品W之和（C+D+E+F+G+......）*9%=A2
    所有人购买特殊商品W之和（D+E+F+G+......）*8%=A3
    所有人购买特殊商品W之和（E+F+G+......）*7%=A4
    .........以此类推
    A的领导奖（W2）=A1-A2-A3-A4-......
    B的领导奖=A2-A3-A4-......
    C的领导奖=A3-A4-......
    极差的形式计算领导奖
     */
	public function lingdao(){
        $order_detail = M('shop_order_detail');
        $data = M('user') -> field('user_id') -> select();
        $arr = array();
        foreach ($data as $k => $v){
            //计算下级的所有的记录
            $w2 = $this -> getMoney($v['user_id']);
            $res = M('zonge') -> where("user_id = {$v['user_id']}") -> select();
            if($res){
                $yytx2['number'] = $w2['money'];
                $yytx2['number1'] = $w2['currentmoney'];
                M('zonge') -> where("user_id = {$v['user_id']}") -> save($yytx2);
            }else{
                $data1['user_id'] = $v['user_id'];
                $data1['number'] = $w2['money'];
                $data1['number1'] = $w2['currentmoney'];
                M('zonge') -> add($data1);
            }
        }
        foreach ($data as $k => $v){

              $xiangjia = $this -> abc($v['user_id']);
              $yy = M('zonge') -> field('number,number1') -> where(['user_id' => $v['user_id']]) -> find();
              $number = $yy['number'];
              $number1 = $yy['number1'];
              $number = $this -> getFanli($number,$number1,$v['user_id']);
              $w2 = $number - $xiangjia;
              if($w2 <= 0){
                  continue;
              }
            $res = M('user') -> where("user_id = {$v['user_id']}") -> setInc('money',$w2);
            $agent_id = M('user') -> getFieldByUserId($v['user_id'],'agent_id');
            if($res){
                $dat['user_id'] = $v['user_id'];
                $dat['money'] = $w2;
                $dat['time'] = time();
                $dat['type'] = 1;
                $dat['agent_id'] = $agent_id;
                M('yongjin') -> add($dat);
                $re = M('fee') -> where("user_id = {$v['user_id']}") -> select();
                if($re){
                    //由当前人的数据
                    $yytx3['w2'] = $w2;
                    $yytx3['time'] = time();
                    M('fee') -> where("user_id = {$v['user_id']}") -> save($yytx3);
                }else{
                    $data2['user_id'] = $v['user_id'];
                    $data2['w2'] = $w2;
                    $data2['time'] = time();
                    M('fee') -> add($data2);
                }
            }


        }
    }


    private function abc($user_id){
	    $xjuser = M('user') -> field('user_id') -> where("sj_userid = {$user_id}") -> select();
        if(!$xjuser){
            return 0;
        }
	    $zonge = 0;
	    $model = M('zonge');
	    foreach ($xjuser as $k => $v){
	        $yy = $model -> field('number,number1') -> where(['user_id' => $v['user_id']]) -> find();
            $number = $yy['number'];
            $number1 = $yy['number1'];
            $zonge += $this -> getFanli($number,$number1);
        }
        return $zonge;
    }

    private function getUser($user_id,$i){
	    $res = M('user') -> where("sj_userid = {$user_id}") -> select();
        if(!$res){
            return;
        }
        foreach ($res as $k => $v){

            self::$list[$user_id][] = $v['user_id'];
            $j = $i +  1;
            $fee = $this -> getUser($v['user_id'],$j);
        }
    }

    private function getMoney($user_id){
        $yeji['money'] = 0;
        $yeji['currentmoney'] = 0;
        if($user_id == 0){
            return $yeji;
        }
        $order_detail = M('shop_order_detail');
        $money1 = $order_detail
            -> alias('a')
            -> field("sum(a.good_price) as money")
            -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
            -> where(" b.new = 1 and a.state = 1 and a.user_id = {$user_id}")
            ->select();
        //计算当天的
        $time = $this->getTime();
        $money2 = $order_detail
            -> alias('a')
            -> field("sum(a.good_price) as money")
            -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
            -> where(" b.new = 1 and a.state = 1 and a.user_id = {$user_id} and a.time > '$time[0]' and a.time < '$time[1]' ")
            ->select();

        $yeji['money'] += $money1[0]['money'];
        $yeji['currentmoney'] += $money2[0]['money'];
        $res = M('user') -> field('user_id,sj_userid') -> where("sj_userid = {$user_id}") -> select();
        if(!$res){
            return $yeji;
        }

        foreach ($res as $k => $v){
            $yj = $this -> getMoney($v['user_id']);
            $yeji['money'] += $yj['money'];
            $yeji['currentmoney'] += $yj['currentmoney'];
        }
        return $yeji;
    }


    /**
     * 分红奖w3
     * 当天所有人购买W之和为B1
    奖金池B2等于B1  乘以20%（后台可以自动调整百分比）
    W3=B2除以平台所有购买特殊商品的总数
    然后再根据每个人购买了多少单计算当天分红（分红只能第二天到账前一天的）
     */
	private function fenhong(){
	    $agentList = M('agent_info') -> field('id') -> select();
        foreach ($agentList as $k1 => $v1){
            $agent_id = $v1['id'];
            $order_detail = M('shop_order_detail');
            $shop_goods = M('shop_goods');
            $time = $this -> getTime();
            //查询特殊奖的id
            $zonge = $order_detail
                -> alias('a')
                -> field('sum(a.good_price) as zonge')
                -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
                -> where("a.state = 1 and b.new = 1 and a.time > '$time[0]' and a.time < '$time[1]' and a.agent_id = {$agent_id}")
                -> select();
            $zonge = $zonge[0]['zonge'];
            $bili = M('feefenhong') -> find();
            $bili = $bili['bili']/100;
            if($bili){
                $jiangjin = $zonge * $bili;
                $number = $order_detail
                    -> alias('a')
                    -> field('sum(a.good_num) as number')
                    -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
                    -> where("a.state = 1 and b.new = 1 and a.agent_id = {$agent_id}")
                    -> select();
                $number = $number[0]['number'];
                $w3 = $jiangjin / $number;
                //$w3 = $bili;
                $data = M('user')
                    -> alias('a')
                    -> field('a.user_id,sum(b.good_num) as number')
                    -> join("left join __SHOP_ORDER_DETAIL__ as b on a.user_id = b.user_id")
                    -> join("left join __SHOP_GOODS__ as c on b.good_id = c.good_id")
                    -> where("c.new = 1 and b.state = 1 and a.agent_id = {$agent_id} and b.flag = 0")
                    -> group('b.user_id')
                    -> select();
                //计算每个人的w3
                $fee = M('fee');
                foreach($data as $k => $v){
                    //   dump($v['number']);
                    if($v['number'] != 0){
                        $money = $w3 * $v['number'];
                        $re = $fee -> where("user_id = {$v['user_id']}") -> select();
                        if($re){
                            //由当前人的数据
                            $yytx4['w3'] = $money;
                            $yytx4['time'] = time();
                            $fee -> where("user_id = {$v['user_id']}") -> save($yytx4);
                        }else{
                            $data2['user_id'] = $v['user_id'];
                            $data2['w3'] = $money;
                            $data2['time'] = time();
                            $fee -> add($data2);
                        }
                    }
                }

            }
        }
    }

    /**
     * 回馈奖w4
     * W4=(W1+W2)*20%除以直推人数（后台可以自动调整百分比）
     */
	private function huikui(){
        $data = M('user') -> field("user_id") -> select();
        $bili = M('feehuikui') -> find(1);
        $bili = $bili['bili'];
        $model = M('fee');
        foreach ($data as $k => $v){
            $res = $model -> where("user_id = {$v['user_id']}") -> select();
            $w4 = $res[0]['w1'] + $res[0]['w2'];
            $w4 = $w4 * $bili/100;
            $number = $this -> getOrderNumber($v['user_id']);
            if($number){
                $w4 = $w4 / $number;
                $userList = M('user')
                    -> alias('a')
                    -> field('a.user_id')
                    -> join("left join __SHOP_ORDER_DETAIL__ as b on a.user_id = b.user_id")
                    -> where("a.sj_userid = {$v['user_id']} and b.state = 1 and b.flag = 0")
                    -> select();
                foreach ($userList as $k1 => $v1){
                    $wfee = $w4;
                    $re = M('fee') -> where("user_id = {$v1['user_id']}") -> select();
                    if($re){
                        //由当前人的数据
                        $yytx5['w4'] = $wfee;
                        $yytx5['time'] = time();
                        M('fee') -> where("user_id = {$v1['user_id']}") -> save($yytx5);
                    }else{
                        $data2['user_id'] = $v1['user_id'];
                        $data2['w4'] = $wfee;
                        $data2['time'] = time();
                        M('fee') -> add($data2);
                    }
                }



            }


        }
    }

//    public function text(){
//	    $a = $this -> getOrderNumber(1);
//        echo $a;
//	}

    private function getOrderNumber($user_id){
       /* $data = M('user') 
		-> alias('a')
		-> field("count(a.user_id) as num")
		-> join("left join __SHOP_ORDER_DETAIL__ as b on a.user_id = b.user_id")
		-> where("sj_userid = {$user_id} and b.state = 1 and b.flag = 0")
		-> select();
        return $data[0]['num'];*/
		$data = M('user') 
		-> alias('a')
		-> field("a.user_id")
		-> join("left join __SHOP_ORDER_DETAIL__ as b on a.user_id = b.user_id")
		-> where("sj_userid = {$user_id} and b.state = 1 and b.flag = 0")
		-> group('b.user_id')
		-> select();
		$number = count($data);
		
        return $number;
    }

    //得到前一天12点到12点的时间戳两个，开始时间和结束时间
    private function getTime(){
	    $startTime = time() - 86400;
	    $endTime = time();
	    $res[] = $startTime;
	    $res[] = $endTime;
	    return $res;
    }

    private function getFanli($yeji = 0,$number = 0,$user_id = 0){
        $data = M('feelingdao') -> find(1);
        $fanyong = 0;
        if($user_id == 1){
				$yeji += 2000000;
        }else if($user_id == 2){
			$yeji += 500000;
        }else if($user_id == 14){
			$yeji += 1000000;
        }else if($user_id == 42){
            $yeji += 100000;
        }else if($user_id == 66){
            $yeji += 500000;
        }else if($user_id == 68){
            $yeji += 500000;
        }else if($user_id == 75){
            $yeji += 1000000;
        }else if($user_id == 3){
			$yeji += 200000;
		}else if($user_id == 309){
			$yeji += 500000;
		}


        if($yeji >= 5000 && $yeji < 20000){
            $fanyong = $number * $data['fee1']/100;
        }else if($yeji >= 20000 && $yeji < 50000){
            $fanyong = $number * $data['fee2']/100;
        }else if($yeji >= 50000 && $yeji < 100000){
            $fanyong = $number * $data['fee3']/100;
        }else if($yeji >= 100000 && $yeji < 200000){
            $fanyong = $number * $data['fee4']/100;
        }else if($yeji >= 200000 && $yeji < 300000){
            $fanyong = $number * $data['fee5']/100;
        }else if($yeji >= 300000 && $yeji < 500000){
            $fanyong = $number * $data['fee6']/100;
        }else if($yeji >= 500000 && $yeji < 1000000){
            $fanyong = $number * $data['fee7']/100;
        }else if($yeji >= 1000000 && $yeji < 2000000){
            $fanyong = $number * $data['fee8']/100;
        }else if($yeji >= 2000000 && $yeji < 5000000){
            $fanyong = $number * $data['fee9']/100;
        }else if($yeji >= 5000000 && $yeji < 10000000){
            $fanyong = $number * $data['fee10']/100;
        }else if($yeji >= 10000000 && $yeji < 20000000){
            $fanyong = $number * $data['fee11']/100;
        }else if($yeji >= 20000000 && $yeji < 50000000){
            $fanyong = $number * $data['fee12']/100;
        }else if($yeji >= 50000000 && $yeji < 100000000){
            $fanyong = $number * $data['fee13']/100;
        }else if($yeji >= 100000000){
            $fanyong = $number * $data['fee14']/100;
        }
        return $fanyong;
    }

}
