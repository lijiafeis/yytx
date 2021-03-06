<?php
namespace Agent\Controller;
use Think\Controller;

class IndexController extends ActionController{
	function __construct(){
		parent::__construct();
		if(!session('admin_id')){
			$this->error('您还未登录，请登录',U('User/index'),5);
		}
	}
    function index(){
        $this->display();
    }
    function top(){
        $this->display();
    }
	function notice(){
		/* 查询15秒内有没有新订单 */
		$time = time() - 15;
		$res = M('shop_order') -> where(" is_true = 1 and pay_time > '$time' ") -> find();
		if($res){
			$arr['success'] = 1;
		}else{
			$arr['success'] = 0;
		}
		echo json_encode($arr);
	}
    function left(){
        $this->display();
    }
	function system(){
		if($_POST){
			$system_info = array('subscribe_all'=>$_POST['subscribe_all'],'shop_all'=>$_POST['shop_all'],'shop_share'=>$_POST['shop_share']);
			F('system_info',$system_info,DATA_ROOT);
			$this -> success('系统配置信息已保存');
		}else{
			$this->assign('info',F('system_info','',DATA_ROOT));
			$this->display();
		}
        
    }
    function main(){
//		$auth=new \Think\Auth();
//		$res = $auth->check('Main',session('admin_id'));
//		if($res){
//			redirect(U('main/index'));exit;
//		}
//		$shop_order = M('shop_order');
//		/* 商城成交总额 */
//		$shop_all_fee = $shop_order -> where("is_true = 1")-> sum('total_fee');
//		$this->assign('shop_all_fee',$shop_all_fee);
//		/* 商城佣金 */
//		$broke_record = M('broke_record');
//		$shop_all_broke = $broke_record -> where("type = 1") -> sum('fee');
//		$this->assign('shop_all_broke',$shop_all_broke);
//		/* 订单总量 */
//		$shop_order_num = $shop_order -> count();
//		$this->assign('shop_order_num',$shop_order_num);
//		/* 会员总量 */
//		$users = M('users');
//		$user_all_num = $users -> count();
//		$this->assign('user_all_num',$user_all_num);
//		/* 会员充值金额 */
//		$user_all_broke = $broke_record -> where("type = 2") -> sum('fee');
//		$this->assign('user_all_broke',$user_all_broke);
//		/* 会员提现金额 */
//		$user_all_tixian = $broke_record -> where("type = 3") -> sum('fee');
//		$this->assign('user_all_tixian',$user_all_tixian);
//		/* 会员提现金额 */
//		$user_all_income = $users  -> sum('shop_income');
//		$this->assign('user_all_income',$user_all_income);
//		/* 待付款订单量 */
//		$shop_order_pay = $shop_order -> where("is_true = 0") -> count();
//		$this->assign('shop_order_pay',$shop_order_pay);
//		/* 待发货订单量 */
//		$shop_order_wuliu = $shop_order -> where("is_true = 1 and state = 0") -> count();
//		$this->assign('shop_order_wuliu',$shop_order_wuliu);
//		/* 待收货订单量 */
//		$shop_order_shouhuo = $shop_order -> where("is_true = 1 and state = 1") -> count();
//		$this->assign('shop_order_shouhuo',$shop_order_shouhuo);
//		/* 已完成订单量 */
//		$shop_order_done = $shop_order -> where("is_true = 1 and state = 2") -> count();
//		$this->assign('shop_order_done',$shop_order_done);
//
//		$user_info['no_subscribe'] = $users -> where(" subscribe = 0 ") -> count();
//		//普通会员数
//		$user_info['normal'] = $users -> where(" subscribe = 1 and agent = 0 ") -> count();
//		//一级会员数
//		$user_info['first'] = $users -> where(" subscribe = 1 and agent = 1 ") -> count();
//
//		//最近一周新增数量
//		$i = 0;$arr = array();
//		for($i;$i<15;$i++){
//			$j = $i - 1;
//			$arr[$i] = $this -> day_add(time(),'-'.$i.' day','-'.$j.' day');
//		}
//
//		$this -> assign('user_info',$user_info);
//		$this -> assign('user_line',$arr);
//		$this->display();
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
	
	
	function broke(){
		$this->display();
	}
	function broke_ajax(){
		$users = M('users');
		$pagecount = 10;
		$count = $users ->where(" agent > 0 ")-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$broke_info=$users->where(" agent > 0 ")->limit($Page->firstRow.','.$Page->listRows)->order("shop_income+shop_outcome desc") ->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($broke_info as $k=>$v){
			
			$broke_info[$k]['shop_come'] = $v['shop_income'] + $v['shop_outcome'];
		}
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign("broke_info",$broke_info);
		$this->display();
	}
   function broke_record(){
		$this->display();
	}
	function broke_record_ajax(){
		$broke_record = M('broke_record');$users = M('users');
		$pagecount = 10;
		$where = array('type'=>1);
		$count = $broke_record ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$broke_info=$broke_record->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("time desc") ->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($broke_info as $k=>$v){
			$broke_info[$k]['nickname'] = $users -> getFieldByUser_id($v['user_id'],'nickname');
			$broke_info[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
		}
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign("broke_info",$broke_info);
		$this->display();
	}
	
	function person(){
		
		$admin_id = session('admin_id');
		if($_POST){
			$data = array(
				'phone'=>$_POST['phone'],			
				'email'=>$_POST['email'],			
			);
			if($_POST['pwd1']){
				$data['password']=md5($_POST['pwd1']);
			}
			$res= M('admin')->where(" id = '$admin_id' ")->save($data);
			if($res){
				$this->success("修改已生效",config);
			}else{
				$this->error("修改失败");
			}
		}else{
			$info = M('admin')->where(" id = '$admin_id' ")->select();
			$info[0]['last_time'] = date("Y-m-d H:i:s",$info[0]['last_time']);
			$this->assign("info",$info);
			$this->display();
		}
		
	}
	function question(){
		$this->display();
	}
	function question_ajax(){
		$question = M('question');$user = M('users');
		$pagecount = 10;
		$count = $question -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=$question->limit($Page->firstRow.','.$Page->listRows)->order("time desc") ->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('page',$show);
		foreach($info as $k=>$v){
			$info[$k]['nickname'] = $user -> getFieldByUser_id($v['user_id'],'nickname');
			$info[$k]['time'] =date('Y-m-d H:i:s',$v['time']);
		}
		$this -> assign('info',$info);
		$this->display();
	}
	function question_reply(){
		//dump(I());exit;
		if(!$_GET['id']){redirect(question);exit;}
		$question = M('question');
		if($_POST){
			$id = $_POST['id'];
			$data = array('content'=>$_POST['content'],'is_true'=>1);
			$res = $question -> where(" id = '$id' ") -> save($data);
			if($res){
				if($_POST['template'] == 1){
					$template_info = F('template_info','',DATA_ROOT);
					$openid = M('users') -> getFieldByUser_id($_POST['user_id'],'openid');
					$weixin = A("Wxapi/Weixin");
					$tem_data = '{
					   "touser":"'.$openid.'",
					   "template_id":"'.$template_info['fuwu_done_template_id'].'",
					   "url":"http://'.$_SERVER['SERVER_NAME'].U('/User/Center/').'",           
					   "data":{
							   "first": {
								   "value":"您提交的问题客服已处理\n",
								   "color":"'.$template_info['fuwu_done_top'].'"
							   },
							   "keyword1":{
								   "value":"会员常见问题",
								   "color":"'.$template_info['fuwu_done_text'].'"
							   },
							   "keyword2": {
								   "value":"'.$_POST['info'].'",
								   "color":"'.$template_info['fuwu_done_text'].'"
							   },
							   "keyword3": {
								   "value":"'.date("Y-m-d H:i:s",time()).'",
								   "color":"'.$template_info['fuwu_done_text'].'"
							   },
							   "remark":{
								   "value":"\n点击查看详情",
								   "color":"'.$template_info['fuwu_done_top'].'"
							   }
					   }
				   }';
					$c = $weixin ->send_template($openid,$tem_data);
					
				}
				
				$this->success("问题已处理成功",question);
			}else{
				$this->error("问题处理失败，请检查");
			}
		}else{
			$id = $_GET['id'];
			$info = $question -> where(" id = '$id' ") -> find();
			$this->assign('info',$info);
			$this->display();
		}
		
	}
	
	function out(){
		session(null);
		redirect(U('User/index'));
	}
	function del(){
		$app_id = $_GET['app_id'];
		$res = M('appinfo')->where(" app_id = '$app_id' ")->delete();
		if($res){
			$this->success("删除成功");
		}else{
			$this->error("删除失败");
		}
	}
	function template(){
		if($_POST){
			F('template_info',$_POST,DATA_ROOT);
			$this->success("保存配置信息成功");
		}else{
			$template_info = F('template_info','',DATA_ROOT);//dump($template_info);
			$this -> assign('template_info',$template_info);
			$this->display();
		}
		
	}
    function daiyan(){
        var_dump($_POST);
    	$this->display();
    }
    function edit(){
        if($_POST){
            //如果提交了修改信息，保存信息
			$app_id = $_POST['app_id'];
			//echo $app_id;exit;
			$data=$_POST;
            $res=M('appinfo')->where(" app_id = '$app_id' ")->save($data);
			$this->success("保存成功","cg");
        }elseif($_GET['app_id']){
			$app_id = $_GET['app_id'];
			$info = M('appinfo')->where(" app_id = '$app_id' ")->select();
			$this->assign("info",$info);
			$this->display();
		}
        
    }
	function cg(){
		$this->display();
	}

}

?>