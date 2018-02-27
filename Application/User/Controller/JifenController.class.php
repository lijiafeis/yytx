<?php
namespace User\Controller;
use Think\Controller;

class JifenController extends Controller{
	function __construct(){
		parent::__construct();
		$this->user_id = session('xigua_user_id');
		if(!$this->user_id){
			$redirect_uri='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			redirect('http://'.$_SERVER['HTTP_HOST'].U('/wxapi/oauth/index/')."?surl=".$redirect_uri);exit;
		}
		$this -> assign('app_info',F('config_info','',DATA_ROOT));
		$weixin = A("Wxapi/Weixin");
		$signPackage=$weixin->getSignPackage();
		$this->assign("signPackage",$signPackage);
		$this->assign("action_name",ACTION_NAME);
	}
	function index(){
		$users = D('Xigua/users');
		$this->assign('user_info',$users->get_user_info_by_user_id($this->user_id));
		$user_jifen = M('jifen')-> where("user_id = '$this->user_id' ")->order('time desc') -> select();
		foreach($user_jifen as $k=>$v){
			$user_jifen[$k]['time'] = date('Y-m-d H:i',$v['time']);
		}
		/* 判断用户今日是否签到 */
		$day_res = $this -> day_add(time(),'+1 day','-0 day');
		$this->assign('day_res',$day_res);
		$this->assign('user_jifen',$user_jifen);//$res = $this -> day_add(time(),'-0 day','-1 day');//dump($res);
		$jifen_info = F('jifen_info','',DATA_ROOT);
		$this->assign('jifen_info',$jifen_info);
		$this -> display();
	}
	function index_ajax(){
		$jifen = M('jifen');
		$pagecount = 15;
		$where['user_id'] = $this->user_id;
		
		$count = $jifen ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$user_jifen = $jifen->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("time desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		foreach($user_jifen as $k=>$v){
			$user_jifen[$k]['time'] = date('Y-m-d H:i',$v['time']);
		}
	$this->assign('order_empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe60e;</p>没有任何发现</div>');
		$this->assign('user_jifen',$user_jifen);
		$this -> display();
	}
	/* 购买积分 */
	function buy_jifen(){
		$buy_per = $_POST['number'];
		if(!$buy_per || $buy_per <= 0 ){$arr['success'] = 0;$arr['error'] = '请求发生严重错误！请勿攻击程序';echo json_encode($arr);exit;}
		/* 比对用户账户余额是否足够 */
		$users = M('users');
		$user_shop_income = $users -> where() -> getFieldByUser_id($this->user_id,'shop_income');
		if($user_shop_income < $buy_per){$arr['success'] = 0;$arr['error'] = '钱包余额不足，请调整金额重试！';echo json_encode($arr);exit;}
		/* 增加积分，扣除余额，记录 */
		$jifen_info = F('jifen_info','',DATA_ROOT);
		$jifen_add = $jifen_info['buy_per']*$buy_per;
		$users -> where("user_id = '$this->user_id' ") -> setDec('shop_income',$buy_per);
		$users -> where("user_id = '$this->user_id' ") -> setInc('jifen',$jifen_add);
		$jifen_data = array(
			'number'=>$jifen_add,
			'user_id'=>$this->user_id,
			'type'=>7,
			'time'=>time()
		);
		M('jifen') -> add($jifen_data);
		$arr['success'] = 1;$arr['error'] = '购买成功！';$arr['jifen']=$jifen_add;echo json_encode($arr);
	}
	/* 签到后端处理 */
	function qiandao(){
		if(!$this->user_id){exit;}$arr = array();
		$jifen_info = F('jifen_info','',DATA_ROOT);
		/* 判断签到功能是否开启 */
		if($jifen_info['qiandao_switch'] != 1){$arr['success'] = 0;$arr['error'] = '签到功能未开启，请联系管理员';echo json_encode($arr);exit;}
		/* 判断用户今日是否签到 */
		$day_res = $this -> day_add(time(),'+1 day','-0 day');
		if(!$day_res){$arr['success'] = 4;$arr['error'] = '今天已经签过到啦！明天再来吧';echo json_encode($arr);exit;}
		/* 规则判定，是否连续签到,今日要得到的积分数 */
		$days = $jifen_info['qiandao_week'];
		for($i=0;$i<$days;$i++){
			$j = $i+1;
			$res = $this -> day_add(time(),'-'.$i.' day','-'.$j.' day');
			//dump($res);
			if($res){break;}
		}
		/* 今日积分数 */
		$add_jifen = $jifen_info['qiandao_number']*1 + $jifen_info['more_number']*$i*1;
		M('users') -> where("user_id = '$this->user_id'")->setInc('jifen',$add_jifen);
		/* 记录签到 */
		$jifen_data = array(
			'number'=>$add_jifen,
			'user_id'=>$this->user_id,
			'type'=>2,
			'time'=>time()
		);
		M('jifen') -> add($jifen_data);
		$arr['success'] = 1;$arr['jifen']=$add_jifen;$arr['error'] = '您已连续签到'.$j.'天，今日得到积分'.$add_jifen;echo json_encode($arr);
	}
	
	function day_add($time,$date,$mdate){
		$jifen = M('jifen');
		$day = date("m-d",strtotime($date));
		$time1 = strtotime(date("Y-m-d",strtotime($date)));//dump($time1);dump(date("Y-m-d H:i:s",$time1));
		$time2 = strtotime(date("Y-m-d",strtotime($mdate)));//dump($time2);dump(date("Y-m-d H:i:s",$time2));
		$res = $jifen -> where("time>='$time2' and time < '$time1' and user_id = '$this->user_id' and type = 2") -> find();
		//dump($res);
		if($res != null){return false;}else{return true;}
		// $result = array('day'=>$day,'num'=>$num);
		// return $result;
	}
	
	function enter_gongpai(){
		/* 判断平台是否开启积分入口 */
		$gongpai_info = F('gongpai_info','',DATA_ROOT);
		if($gongpai_info['gongpai_switch'] != 1 || $gongpai_info['jifen_switch'] != 1){$arr['success'] = 0; $arr['error_info'] = '入口未启用，请联系管理员开启';echo json_encode($arr);exit;}
		/* 判断积分是否足够 */
		$users = M('users');
		$user_jifen = $users -> getFieldByUser_id($this->user_id,'jifen');
		if($user_jifen < $gongpai_info['jifen_fee']){$arr['success'] = 0; $arr['error_info'] = '唉，钱包积分不足噢！无法进入';echo json_encode($arr);exit;}
		/* 扣除用户积分，记录 */
		$users -> where("user_id = '$this->user_id' ")->setDec('jifen',$gongpai_info['jifen_fee']);
		$jifen_data = array(
			'number'=>$gongpai_info['jifen_fee'],
			'user_id'=>$this->user_id,
			'type'=>4,//参与公排扣除积分，4
			'time'=>time()
		);
		M('jifen') -> add($jifen_data);
		/* 引入公排 */
		$gongpai = A("Xigua/Gongpai");
		$user_id = session('xigua_user_id');
		$gongpai -> __construct($user_id,'daijin');
		$gongpai -> index();
		$arr['success'] = 1; $arr['error_info'] = '您已成功进入活动！';echo json_encode($arr);
	}
	
}

?>