<?php
namespace Admin\Controller;
use Think\Controller;

class GongpaiController extends ActionController{
	function __construct(){
		parent::__construct();
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'),5);
		}

	}
	/* 公排首页视图 */
	function index(){
		$gongpai_user_num = M('gongpai_user') -> count();
		$this->assign('gongpai_user_num',$gongpai_user_num);
		$gongpai_user_out_num = M('gongpai_user') -> where("type = 1") -> count();
		$this->assign('gongpai_user_out_num',$gongpai_user_out_num);
		$hb_all_fee = M('hbrecord') -> where("type = 1") -> sum('hongbao_fee');
		$this->assign('hb_all_fee',$hb_all_fee);
		$this->display();
	}
	function index_more(){
		if(I('user_id')){$where['user_id'] = I('user_id');}
		if(I('get.type') == 1){
			/* 参与用户数据 */
			$pagecount = 15;
			$count = M('gongpai_user') ->where($where)-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			$info=M('gongpai_user')->where($where)->order("dianwei_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
			$Page->setConfig('first','首页');
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('last','尾页');
			$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
			
			$hbrecord = M('hbrecord');$users = M('users');$gongpai_contact = M('gongpai_contact');
			$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
			foreach($info as $key=>$value){
				
				$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
				$info[$key]['num'] = $gongpai_contact -> where("user_id = '$value[dianwei_id]'") -> count();
				$info[$key]['info'] = $users ->field("nickname,agent,headimgurl") -> where("user_id = '$value[user_id]' ") -> find();
				if($info[$key]['info']['agent'] == 0){$info[$key]['agent_name'] = '普通会员';}else{$info[$key]['info']['agent_name'] = $fenxiao_info['fenxiao_name'];}
				$info[$key]['hb_total'] = $hbrecord -> where("dianwei_id = '$value[dianwei_id]'") -> sum('hongbao_fee');
				if(trim($info[$key]['hb_total']) == null){$info[$key]['hb_total'] = 0;}
			}
			// 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show();
			$this->assign('page',$show);
			$this->assign("info",$info);
			$this->assign("type",1);
		}elseif(I('get.type') == 2){
			/* 参与用户数据 */
			$pagecount = 15;
			$where['type'] = 1;
			$count = M('gongpai_user') ->where($where)-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			$info=M('gongpai_user')->where($where)->order("dianwei_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
			$Page->setConfig('first','首页');
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('last','尾页');
			$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
			
			$hbrecord = M('hbrecord');$users = M('users');$gongpai_contact = M('gongpai_contact');
			$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
			foreach($info as $key=>$value){
				
				$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
				$info[$key]['num'] = $gongpai_contact -> where("user_id = '$value[dianwei_id]'") -> count();
				$info[$key]['info'] = $users ->field("nickname,agent,headimgurl") -> where("user_id = '$value[user_id]' ") -> find();
				if($info[$key]['info']['agent'] == 0){$info[$key]['agent_name'] = '普通会员';}else{$info[$key]['info']['agent_name'] = $fenxiao_info['fenxiao_name'];}
				$info[$key]['hb_total'] = $hbrecord -> where("dianwei_id = '$value[dianwei_id]'") -> sum('hongbao_fee');
				if(trim($info[$key]['hb_total']) == null){$info[$key]['hb_total'] = 0;}
			}
			// 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show();
			$this->assign('page',$show);
			$this->assign("info",$info);
			$this->assign("type",1);
		}elseif(I('get.type') == 3){
			/* 参与用户数据 */
			$pagecount = 15;
			$hbrecord = M('hbrecord');$where['type'] = 1;
			$count = $hbrecord ->where($where)-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			$info=$hbrecord->where($where)->order("dianwei_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
			$Page->setConfig('first','首页');
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('last','尾页');
			$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
			
			$users = M('users');
			
			foreach($info as $key=>$value){
				
				$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
				$info[$key]['user_nickname'] = $users ->getFieldByUser_id($value['user_id'],'nickname');
				$info[$key]['from_user_nickname'] = $users ->getFieldByUser_id($value['from_user_id'],'nickname');
			}
			// 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show();
			$this->assign('page',$show);
			$this->assign("info",$info);
			$this->assign("type",3);
		}
			
		$this->display('gongpai_index_more');
	}
	/* 公排设置 */
	function set(){
		if($_POST['set'] == 1){
			/* 保存公排设置信息 */
			$gongpai_info = M('gongpai_info');
			$res = $gongpai_info -> find();
			$data = $_POST;
			if(!$_POST['gongpai_switch']){$data['gongpai_switch'] = 0;}
			if(!$_POST['jifen_switch']){$data['jifen_switch'] = 0;}
			if(!$_POST['duodian_switch']){$data['duodian_switch'] = 0;}
			if(!$_POST['ganen_switch']){$data['ganen_switch'] = 0;}
			if(!$res){$gongpai_info->add($data);}else{$gongpai_info->where(" id = '$res[id]' ")->save($data);}
			$this->success('配置信息保存成功！',set);
		}else{
			$daijin_info = M('shop_daijin') -> order("daijin_id desc")-> select();
			$this -> assign('empty','<option disabled>你还没有创建代金券</option>');
			$this -> assign('daijin_info',$daijin_info);
			$gongpai_info = M('gongpai_info')->find();
			F('gongpai_info',$gongpai_info,DATA_ROOT);
			$this->assign('info',$gongpai_info);
			$this->display();
		}
	}
	/* 公排层级视图 */
	function level(){
		if(!I('level')){exit;}else{$level = I('level');}
		$gongpai_info = F('gongpai_info','',DATA_ROOT);
		if(!$gongpai_info){
			$gongpai_info = M('gongpai_info')->find();
			F('gongpai_info',$gongpai_info,DATA_ROOT);
		}
		$fee = array(
			array('name'=>'first_fee','val'=>$gongpai_info['first_fee']),
			array('name'=>'second_fee','val'=>$gongpai_info['second_fee']),
			array('name'=>'third_fee','val'=>$gongpai_info['third_fee']),
			array('name'=>'four_fee','val'=>$gongpai_info['four_fee']),
			array('name'=>'five_fee','val'=>$gongpai_info['five_fee']),
			array('name'=>'six_fee','val'=>$gongpai_info['six_fee']),
			array('name'=>'seven_fee','val'=>$gongpai_info['seven_fee']),
			array('name'=>'eight_fee','val'=>$gongpai_info['eight_fee']),
			array('name'=>'nine_fee','val'=>$gongpai_info['nine_fee']),
			array('name'=>'ten_fee','val'=>$gongpai_info['ten_fee']),
			array('name'=>'eleven_fee','val'=>$gongpai_info['eleven_fee']),
			array('name'=>'twelve_fee','val'=>$gongpai_info['twelve_fee']),
			
		);
		if($level > 12){
			if($gongpai_info['gongpai_level'] != null){$level = $gongpai_info['gongpai_level'];}else{$level = 1;}
		}
		$this->assign('fee',$fee);
		$this->assign('level',$level);
		$this->display();
	}
	function good_list(){
		$keyword =  I('keyword');
		if(!$keyword){exit;}
		if(I('good_id')){
			$where['good_id'] = I('good_id');
			$this->assign('str','selected');
		}else{
			$where['good_name'] = array('like','%'.$keyword.'%');
		}
		$good_info = M('shop_goods')->field("good_id,good_name")-> where($where) -> select();
		$this->assign('good_info',$good_info);
		$this->display();
	}
	/* 应用云市场 */
	function yun(){
		
		$this->display();
	}
}