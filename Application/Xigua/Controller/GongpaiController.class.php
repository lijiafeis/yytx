<?php
namespace Xigua\Controller;
use Think\Controller;
/* 
公排核心程序，检查公排是否开启，是否支持多点卡位，每排人数，何时出局，出局后是否允许再次进入
 */
class GongpaiController extends Controller{
	
	function __construct($user_id,$order_id){
		 parent::__construct();
		$this->user_id = $user_id;
		if(!$this->user_id){return;}
		$gongpai_info = F('gongpai_info','',DATA_ROOT);
		if(!$gongpai_info){
			$gongpai_info = M('gongpai_info')->find();
			F('gongpai_info',$gongpai_info,DATA_ROOT);
		}
		$this->gongpai_info = $gongpai_info;
		$this->order_id = $order_id;
	}
	
	function index(){
		/* 当auto存在时是开启了单点卡位，且免费自动再进入模式 */
		/* 判断是否开启 */
		if($this->gongpai_info['gongpai_switch'] != 1){return;}
		
		if($this->gongpai_info['duodian_switch'] == 0 && $this->gongpai_info['gongpai_again'] == 2){
			
		}else{
			if(!$this->order_id){/* echo '订单不存在'.$this->order_id */;return;}
			/* 查询订单是否已经创建过了关联点位 */
			if($this->order_id != 'daijin'){
				$order_dianwei_id = M('shop_order') -> getFieldByOrder_id($this->order_id,'gongpai_dianwei_id');
				if($order_dianwei_id != 0){/* echo '订单不存在或已加入点位' */;return;}
			}
			
		}
		
		/* 判断是否支持多点卡位 */
		if($this->gongpai_info['duodian_switch'] != 1){
			/* 不支持，则进入单点卡位模式 */
			$this->dandian();
		}else{
			/* 支持多点，进入多点卡位模式 */
			$this->duodian();
		}
	}
	
	/* 单点卡位模式 */
	function dandian(){
		$dianwei_info = $this->check_user();
		if($dianwei_info['type'] == 1){return false;}
		/* 当前用户的点位id */
		$dianwei_id = $dianwei_info['dianwei_id'];
		/* 当前正在排位的用户点位id */
		$pid_dianwei_id = $this->get_pid_dianwei_id();
		if(!$pid_dianwei_id){return;}
		if($dianwei_id == $pid_dianwei_id){
			/* 是本人，不进行关系写入，一般首位进入会员会到这里 */
			return;
		}else{
			/* echo '该写入关系数据了'; */
			$this->creat_contact($dianwei_id,$pid_dianwei_id);
		}
		
	}
	
	/* 多点卡位模式 */
	function duodian(){
		/* 当前用户的点位id */
		$dianwei_id = $this->add_user();
		/* 当前正在排位的用户点位id */
		$pid_dianwei_id = $this->get_pid_dianwei_id();
		/* echo '该写入关系数据了'; */
		if($dianwei_id == $pid_dianwei_id){
			/* 是同一个点位不操作 */
			return;
		}
		$this->creat_contact($dianwei_id,$pid_dianwei_id);
	}
	
	/* 检查用户是否已有未出局点位 */
	function check_user(){
		$gongpai_user = M('gongpai_user');
		$user_info = $gongpai_user -> where("user_id = '$this->user_id'") -> order("dianwei_id desc") -> find();
		if($user_info == null){
			$dianwei_id = $this->add_user();
			$type = 0;
		}else{
			$dianwei_id = $user_info['dianwei_id'];
			$type = $user_info['type'];
			/* 当单点模式时，进行判断，是否允许再次进入公排 */
			/* 用户已有出局历史时 */
			if($type == 1 && $this->gongpai_info['duodian_switch'] == 0){
				switch($this->gongpai_info['gongpai_again']){
					/* 值为1时，重复购买进入下一轮，值为2时自动进入下一轮，值为3禁止再次进入 */
					case 1:
					$dianwei_id = $this->add_user();/* echo '重复购买可以再次排位'; */
					$type = 0;
					break;
					case 2:
					$dianwei_id = $this->add_user();/* echo '自动进入下一轮'; */
					$type = 0;
					break;
					default:
					$dianwei_id = 0;$type = 0;/* echo '已排位，不能再排位'; */
					break;
				}
			}	
		}
		$return = array('dianwei_id'=>$dianwei_id,'type'=>$type);
		return $return;
	}
	
	/* 为用户创造新点位 */
	function add_user(){
		$gongpai_user = M('gongpai_user');
		$data = array(
			'user_id'=>$this->user_id,
			'time'=>time()
		);
		$dianwei_id = $gongpai_user -> add($data);
		/* 更改用户订单关联点位id */
		M('shop_order') -> where(" order_id = '$this->order_id' ") -> setField('gongpai_dianwei_id',$dianwei_id);
		/* 发送点位创建成功通知 */
		$openid = M('users')->getFieldByUser_id($this->user_id,'openid');
		$notify_url = 'http://'.$_SERVER['HTTP_HOST'].U('user/gongpai/kawei').'?dianwei_id='.$dianwei_id;
		$weixin = A('Wxapi/Weixin');$weixin -> send_word($openid,"感谢您对平台的支持！您已自动加入公排复制活动！您的公排位置ID为".$dianwei_id."，坐享无限层收益\n\n<a href='".$notify_url."'>详情点击这里>></a>");
		return $dianwei_id;
	}
	
	/* 寻找到当前正在进行的排位id */
	function get_pid_dianwei_id(){
		$gongpai_user = M('gongpai_user');
		/* 查询出轮到排谁下面 */
		$pid_dianwei_info = $gongpai_user -> where("type = 0 and state = 0 ") -> order("dianwei_id asc ") -> find();
		if($pid_dianwei_info == null){return false;}
		/* 再次确认该父点位是否已满人数 */
		$pid_dianwei_id = $pid_dianwei_info['dianwei_id'];
		$pid_num = M('gongpai_contact')-> where("user_id = '$pid_dianwei_id' and level = 1") -> count();
		if($pid_num >= $this->gongpai_info['gongpai_number']){
			/* 1层已满，更改用户state,并寻找下一个要进行排位的父id */
			$gongpai_user -> where("dianwei_id = '$pid_dianwei_id' ") -> setField('state',1);
			return $this->get_pid_dianwei_id();
		}
		return $pid_dianwei_id;
	}
	
	/* 写入各层级人员逻辑关系 */
	function creat_contact($dianwei_id,$pid_dianwei_id){
		$gongpai_contact = M('gongpai_contact');
		/* 查询用户点位是否存在，避免重复执行 */
		$res = $gongpai_contact -> where("children_id = '$dianwei_id'") -> find();
		if($res){/* echo '该点位已加入公排<Br />'; */return;}
		
		/* 循环找出上面各层的关系 */
		$level = $this->gongpai_info['gongpai_level'];$i = 1;
		/* 查询出该父级上面的所有点位 */
		$all_dianwei = $gongpai_contact -> where("children_id = '$pid_dianwei_id'")->order("level asc") -> select();
		
		do{
			$data = array('user_id'=>$pid_dianwei_id,'children_id'=>$dianwei_id,'time'=>time(),'level'=>$i);
			$gongpai_contact -> add($data);
			/* 发送奖励红包和模板消息 */
			$this -> send_hongbao($i,$pid_dianwei_id,$dianwei_id);
			if($i == $level){
				/* 当前是给用户增加最高层的下排，实时监控其排是否人数够数 */
				$chuju_num = $gongpai_contact -> where("user_id = '$pid_dianwei_id' and level = '$level'")-> count();
				if( $chuju_num == pow($this->gongpai_info['gongpai_number'],$level) ){
					/* 该用户到达出局条件，更改type值为1， */
					M('gongpai_user') -> where("dianwei_id = '$pid_dianwei_id'") -> setField('type',1);
					/* 如果设置为单点、允许复投的话，发送消息给用户 */
					$pid_user_id = M('gongpai_user') -> getFieldByDianwei_id($pid_dianwei_id,'user_id');
					$openid = M('users')->getFieldByUser_id($pid_user_id,'openid');
					$notify_url = 'http://'.$_SERVER['HTTP_HOST'].U('user/gongpai/kawei').'?dianwei_id='.$pid_dianwei_id;
					$weixin = A('Wxapi/Weixin');
					/* 如果是单点且允许免费进入继续卡点 */
					if($this->gongpai_info['duodian_switch'] == 0 && $this->gongpai_info['gongpai_again'] == 2){
						$dandian_notice_url = 'http://'.$_SERVER['HTTP_HOST'].U('user/gongpai/enter');
						$dandian_notice_word = "\n\n<a href='".$dandian_notice_url."'>点击这里自动再次进入公排</a>";
					}else{
						$dandian_notice_word = "";
					}
					$weixin -> send_word($openid,"感谢您对平台的支持！您参与的公排活动位置ID【".$pid_dianwei_id."】您已出局！您可以再次商城任意消费即可再次加入公排\n\n<a href='".$notify_url."'>详情点击这里>></a>".$dandian_notice_word);
					/* 发感恩奖 */
					if($this->gongpai_info['ganen_switch'] == 1 && $this->gongpai_info['ganen_fee'] > 0 ){
						$zhitui_pid_user_id = M('users') ->getFieldByUser_id($pid_user_id,'pid');
						if($zhitui_pid_user_id > 0){
							$hb_data = array(
								'user_id'=>$zhitui_pid_user_id,
								'from_user_id'=>$pid_user_id,
								'hongbao_fee'=>$this->gongpai_info['ganen_fee'],
								'type'=>1,
								'time'=>time(),
								'is_true'=>0
							);
							M('hbrecord') -> add($hb_data);
							/* 通知父级用户收到感恩奖 */
							$template = A("Pay/Template");
							$pid_openid = M('users')-> getFieldByUser_id($zhitui_pid_user_id,'openid');
							$template -> send_hongbao($this->gongpai_info['ganen_fee'],$pid_openid,'公排活动【感恩奖】');
						}
						
					}
					
					/* echo "排位id为".$pid_dianwei_id."的用户出局"; */
				}
			}
			$j = $i - 1;
			/* echo $all_dianwei[$j]['user_id']; */
			if($all_dianwei[$j]['user_id']){
				$pid_dianwei_id = $all_dianwei[$j]['user_id'];
			}else{
				break;
			}
			$i++;
		}while($i<=$level);
	}
	
	/* 发送奖励红包和模板消息 */
	function send_hongbao($level,$pid_dianwei_id,$dianwei_id){
		switch($level){
			case 1:
			$hongbao_fee = $this->gongpai_info['first_fee'];
			break;
			case 2:
			$hongbao_fee = $this->gongpai_info['second_fee'];
			break;
			case 3:
			$hongbao_fee = $this->gongpai_info['third_fee'];
			break;
			case 4:
			$hongbao_fee = $this->gongpai_info['four_fee'];
			break;
			case 5:
			$hongbao_fee = $this->gongpai_info['five_fee'];
			break;
			case 6:
			$hongbao_fee = $this->gongpai_info['six_fee'];
			break;
			case 7:
			$hongbao_fee = $this->gongpai_info['seven_fee'];
			break;
			case 8:
			$hongbao_fee = $this->gongpai_info['eight_fee'];
			break;
			case 9:
			$hongbao_fee = $this->gongpai_info['nine_fee'];
			break;
			case 10:
			$hongbao_fee = $this->gongpai_info['ten_fee'];
			break;
			case 11:
			$hongbao_fee = $this->gongpai_info['eleven_fee'];
			break;
			case 12:
			$hongbao_fee = $this->gongpai_info['twelve_fee'];
			break;
			default:
			$hongbao_fee = 0;
		}
		if($hongbao_fee == 0 || !$hongbao_fee){return;}
		$gongpai_user_info = M('gongpai_user') -> where("dianwei_id = '$pid_dianwei_id'") ->find();
		$pid_user_id = $gongpai_user_info['user_id'];
		
		/* 创建红包数据 */
		$data = array(
			'user_id'=>$pid_user_id,
			'dianwei_id'=>$pid_dianwei_id,
			'from_user_id'=>$this->user_id,
			'from_dianwei_id'=>$dianwei_id,
			'hongbao_fee'=>$hongbao_fee,
			'type'=>1,
			'time'=>time(),
			'is_true'=>0
		);
		M('hbrecord')->add($data);
		$openid = M('users') -> getFieldByUser_id($pid_user_id,'openid');
		/* 模板消息 */
		$template = A("Pay/Template");
		$template -> send_hongbao($hongbao_fee,$openid,'公排活动',$pid_dianwei_id);
	}
}
?>