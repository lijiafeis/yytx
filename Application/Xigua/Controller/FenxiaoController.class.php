<?php
namespace Xigua\Controller;
use Think\Controller;

class FenxiaoController extends Controller{
	
	function test(){
		// $arr = array();
		// $abc = $this->get_deal_user_id(6,9);
		// dump($abc);
		$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
		$order_info = M('shop_order_detail')->where("pay_id = 38") -> select();
		$this->deal_all(2,$fenxiao_info,$order_info);
	}
	/* 商城分销处理 */
	function shop_deal($user_id,$order_info){
		$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
		
		/* 判断商城是否开启了分销功能 */
		if($fenxiao_info['fenxiao_switch'] == 0){return;}
		
		
		/* 普通会员是否进阶 */
		$this->change_agent($user_id,$fenxiao_info,$order_info[0]['pay_id']);
		
		
		/* 判断用户自身是否返佣 */
		if($fenxiao_info['self_switch'] != 0 and $fenxiao_info['self_per'] > 0){
			$this->check_self_deal($user_id,$fenxiao_info,$order_info);
		}
		
		/* 给各层返佣 */
		$this->deal_all($user_id,$fenxiao_info,$order_info);
	}
	
	/* 返佣给各层级 */
	function deal_all($user_id,$fenxiao_info,$order_info){
		$fenxiao_level = $fenxiao_info['fenxiao_level'];
		/* 从一层开始，循环进行返佣 $pid_array 是从近到远的上层用户id集合 */
		$pid_array = $this->get_deal_user_id($user_id,$fenxiao_level);
		$i = 1;$users = M('users');$template = A("Pay/Template");$broke_record = M('broke_record');
		$nickname = $users -> getFieldByUser_id($user_id,'nickname');
		foreach($pid_array as $pid){
			if($pid == 0){break;}
			/* 计算每层佣金数额 */
			$pid_profit = $this-> profit_sum($order_info,$fenxiao_info,$i);
			if($pid_profit > 0){
				/* 下发佣金并记录 */
				$users->where("user_id='$pid'")->setInc('shop_income',$pid_profit);
				$data = array(
					'user_id'=>$pid,
					'desc'=>$i.'级会员'.$nickname.'商城消费返佣金',
					'time'=>time(),
					'type'=>1,
					'fee'=>$pid_profit,
				);
				$broke_record->add($data);
				/* 发送消息模板提醒 */
				$openid = $users->getFieldByUser_id($pid,'openid');
				$template->send_shop_deal($pid_profit,$openid);
			}
			/* 计算返积分总额 */
			$pid_jifen_profit = $this-> jifen_profit_sum($order_info,$fenxiao_info,$i);
			if($pid_jifen_profit > 0){
				/* 下发积分并记录 */
				$users->where("user_id='$pid'")->setInc('jifen',$pid_jifen_profit);
				$jifen_data = array(
					'number'=>$pid_jifen_profit,
					'user_id'=>$pid,
					'type'=>8,
					'time'=>time()
				);
				M('jifen') -> add($jifen_data);
				if(!$openid){$openid = $users->getFieldByUser_id($pid,'openid');}
				$template->send_jifen($pid_jifen_profit,$openid);
			}
			$i++;
		}
	}
	
	/* 查询受益人ID号 */
	function get_deal_user_id($user_id,$fenxiao_level,$i=0,$arr){
		$users = M('users');
		if($fenxiao_level == 0){return $arr;}
		$pid = $users-> getFieldByUser_id($user_id,'pid');
		$arr[$i] = $pid;
		if($pid == 0){return $arr;}else{$i++;$fenxiao_level--;return $this->get_deal_user_id($pid,$fenxiao_level,$i,$arr);}
	}
	
	/* 会员自身返佣 */
	function check_self_deal($user_id,$fenxiao_info,$order_info){
		/* 计算订单总利润 */
		$profit = $this-> profit_sum($order_info,$fenxiao_info,0);
		/* 下发佣金并记录 */
		M('users')->where("user_id='$user_id'")->setInc('shop_income',$profit);
		$data = array(
			'user_id'=>$user_id,
			'desc'=>'商城自身消费返佣金',
			'time'=>time(),
			'type'=>1,
			'fee'=>$profit,
		);
		M('broke_record')->add($data);
		/* 发送升级消息模板提醒 */
		$template = A("Pay/Template");
		$openid = M('users')->getFieldByUser_id($user_id,'openid');
		$template->send_shop_deal($profit,$openid);
	}
	
	/* 各层级分成利润 */
	public function profit_sum($order_info,$fenxiao_info,$type){
		$profit = 0;
		foreach($order_info as $k=>$v){
			$good_id = $v['good_id'];
			$good_profit = $v['good_profit']*$v['good_num'];
			switch($type){
				case 0:
				$profit = $profit*1 + $good_profit*($fenxiao_info['self_per']/100);
				break;
				case 1:
				$profit= $profit + $good_profit*($fenxiao_info['first_per']/100);
				break;
				case 2:
				$profit= $profit + $good_profit*($fenxiao_info['second_per']/100);
				break;
				case 3:
				$profit= $profit + $good_profit*($fenxiao_info['third_per']/100);
				break;
				case 4:
				$profit= $profit + $good_profit*($fenxiao_info['four_per']/100);
				break;
				case 5:
				$profit= $profit + $good_profit*($fenxiao_info['five_per']/100);
				break;
				case 6:
				$profit= $profit + $good_profit*($fenxiao_info['six_per']/100);
				break;
				case 7:
				$profit= $profit + $good_profit*($fenxiao_info['seven_per']/100);
				break;
				case 8:
				$profit= $profit + $good_profit*($fenxiao_info['eight_per']/100);
				break;
				case 9:
				$profit= $profit + $good_profit*($fenxiao_info['nine_per']/100);
				break;
			}
		}
		return $profit;
		
	}
	/* 各层级分成利润 */
	public function jifen_profit_sum($order_info,$fenxiao_info,$type){
		$profit = 0;
		foreach($order_info as $k=>$v){
			$jifen_profit = $v['jifen_profit']*$v['good_num'];
			switch($type){
				case 1:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_first_per']/100);
				break;
				case 2:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_second_per']/100);
				break;
				case 3:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_third_per']/100);
				break;
				case 4:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_four_per']/100);
				break;
				case 5:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_five_per']/100);
				break;
				case 6:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_six_per']/100);
				break;
				case 7:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_seven_per']/100);
				break;
				case 8:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_eight_per']/100);
				break;
				case 9:
				$profit= $profit + $jifen_profit*($fenxiao_info['jifen_nine_per']/100);
				break;
			}
		}
		return $profit;
		
	}
	
	/* 会员进阶分销商 */
	function change_agent($user_id,$fenxiao_info,$pay_id){
		
		/* 判断会员当前级别 */
		$users = M('users');
		$user_info = $users ->field("agent,nickname,openid") -> getByUser_id($user_id);
		if($user_info['agent'] == 1){return;}
		
		/* 赋值变量 state，等于1可以进阶，等于0不满足进阶条件 */
		/* 判断当前是否满足条件 */
		switch($fenxiao_info['fenxiao_exist']){
			/* 无门槛自动成为分销商 */
			case 1:
			$state = 1;
			break;
			/* 消费满金额成为分销商 */
			case 2:
			$shop_order = M('shop_order');
			$total_fee = $shop_order -> where("user_id = '$user_id' and is_true = 1") -> sum('total_fee');
			if($total_fee>= $fenxiao_info['fenxiao_total']){
				$state = 1;
			}else{
				$state = 0;
			}
			break;
			/* 购买指定商品成为分销商 */
			case 3:
			$shop_order_detail = M('shop_order_detail');$good_id = $fenxiao_info['fenxiao_good_id'];
			$exist_info = $shop_order_detail -> where(" user_id = '$user_id' and pay_id = '$pay_id' and good_id = '$good_id' ") -> find();
			if($exist_info == null){$state = 0;}else{$state = 1;}
			break;
		}
		
		if($state == 1){
			
			$res = $users->where("user_id = '$user_id'") -> setField('agent',1);
			/* 发送升级消息模板提醒 */
			if($res){
				$template = A("Pay/Template");
				$template->send_agent(C('USER_AGENT_ONE').$fenxiao_info['fenxiao_name'].C('USER_AGENT_TWO'),$user_info['nickname'],$user_info['openid']);
			}
			
		}
	}
}