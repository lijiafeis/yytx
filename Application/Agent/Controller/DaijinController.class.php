<?php
namespace Admin\Controller;
use Think\Controller;

class DaijinController extends ActionController{
	function __construct(){
		parent::__construct();
		//echo session('admin_id');
		if(!session('admin_id')){
			$this->error('登录已超时，请重新登录',U('User/index'));
		}
		
		
	}
    function index(){
		
		$this->display();
    }
	function index_ajax(){
		
		$daijin = M('shop_daijin');
		$pagecount = 15;
		$count = $daijin -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=$daijin->order("daijin_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $k=>$v){
			
			$info[$k]['daijin_date'] = date("Y-m-d H:i:s",$v['daijin_date']);
		}
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function add(){
		
		if($_POST){
			if(!$_POST['date_switch']){$_POST['date_switch'] = 0;}
			$_POST['daijin_date'] = strtotime($_POST['daijin_date']);
			if($_POST['id']){
				M('shop_daijin')->where("daijin_id = '$_POST[id]'")->field("daijin_date,daijin_fee,daijin_number") -> save($_POST);
			}else{
				
				M('shop_daijin') -> add($_POST);
			}
			
			$this -> success('代金券信息已生成',index);
		}else{
			if(I('get.daijin_id')){
				$info = M('shop_daijin') -> getByDaijin_id(I('get.daijin_id'));
				$info['daijin_date'] = date("Y-m-d H:i:s",$info['daijin_date']);
				$this -> assign('info',$info);
			}
			$this -> display();
		}
	}
	function order(){
		$this -> display();
	}
	function order_ajax(){
		
		$daijin_order = M('daijin_order');$users = M('users');$shop_daijin = M('shop_daijin');
		$pagecount = 10;
		$count = $daijin_order -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=$daijin_order->order("order_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $k=>$v){
			$user_id = $v['user_id'];
			$info[$k]['user'] = $users -> field("nickname") -> where(" user_id = '$user_id' ") -> find();
			$info[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
			$info[$k]['name'] = $shop_daijin -> getFieldByDaijin_id($v['daijin_id'],'daijin_name');
		}
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function dailichange(){
		$user_id = $_POST['id'];
		if(intval($_POST['type'])>1){exit;}
		M('users')-> where(" user_id = '$user_id' ") -> setField('daili',intval($_POST['type']));
		echo json_encode($arr);
	}
	function daili_ajax(){
		if($_GET['nickname']){
			$where['nickname'] =  array('like','%'.$_GET['nickname'].'%');
		}elseif($_GET['user_id']){
			$where = array('user_id'=>$_GET['user_id']);
		}else{
			$where = array();
		}
		$pagecount = 10;
		$count = M('users') ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=M('users')->where($where)->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['subscribe_time'] = date("Y-m-d H:i:s",$value['subscribe_time']);
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	
	function qr(){
		if(!$_POST){exit;}
		$upload = new \Think\Upload();// 实例化上传类  
		$upload->rootPath = './Uploads/';  
		$upload->maxSize   =     3145728 ;// 设置附件上传大小 
		$upload->autoSub = false;   
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
		$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
		$upload->autoSub = true;
		$upload->subName = date("Ymd");
		$imginfo   =   $upload->upload();
		if($imginfo){
			$_POST['pic_url'] = "Uploads/".$imginfo['image1']["savepath"].$imginfo['image1']["savename"];
		}
		if($_POST[qr] == null){
			$res = M('qrset') -> add($_POST);
		}else{
			$res = M('qrset') -> where(" id = '$_POST[qr]' ") -> save($_POST);
		}
		M('qrcode') -> where(" update_time > 0 ") -> setField('update_time','0');
		$this->success("保存成功");
	}
	
	function imgtest(){
		$info = M('qrset')->select();
		if(!$info){
			$text = "发现您还没有设置推广二维码相关参数，请先行设置后再查看";
			$this->assign("text",$text);
		}else{
			$weixin=A("Wxapi/Qrimg");
			$res = $weixin->index(0,0,"未知用户");
			$url = __ROOT__."/".$res;
			$this->assign("url",$url);
			
		}
		$this->display();
	}
	
	function users(){
		$pagecount = 10;
		$count = M('users') -> count();
		$Page = new \Think\Page($count,$pagecount);
		$info=M('users')->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['subscribe_time'] = date("Y-m-d H:i:s",$value['subscribe_time']);
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function users_ajax(){
		if($_GET['nickname']){
			$where['nickname'] =  array('like','%'.$_GET['nickname'].'%');
		}elseif($_GET['user_id']){
			$where = array('user_id'=>$_GET['user_id']);
		}elseif($_GET['subscribe'] != null ){
			$where = array('subscribe'=>$_GET['subscribe']);
		}else{
			$where = array();
		}
		$pagecount = 10;
		$count = M('users') ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=M('users')->where($where)->order("user_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['subscribe_time'] = date("Y-m-d H:i:s",$value['subscribe_time']);
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	function contact(){
		$user_id = $_GET['user_id'];
		if(!$user_id){exit;}
		$users = M('users');
		$user_info = $users -> field("user_id,nickname,headimgurl,pid,gid,oid,subscribe_time,shop_income,agent") ->where(" user_id = $user_id ") -> select();
		//上上上级信息
		if($user_info[0]['oid']!= 0){
			$oid_id = $user_info[0]['oid'];
			$oid_info = $users -> field("nickname,headimgurl,user_id,subscribe_time,shop_income,agent") -> where(" user_id = '$oid_id' ") -> select();
		}
		//上上级信息
		if($user_info[0]['gid']!= 0){
			$gid_id = $user_info[0]['gid'];
			$gid_info = $users -> field("nickname,headimgurl,user_id,subscribe_time,shop_income,agent") -> where(" user_id = '$gid_id' ") -> select();
		}
		//上级信息
		if($user_info[0]['pid']!= 0){
			$pid_id = $user_info[0]['pid'];
			$pid_info = $users -> field("nickname,headimgurl,user_id,subscribe_time,shop_income,agent") -> where(" user_id = '$pid_id' ") -> select();
		}
		//下级信息
		$first_info = $users -> field("nickname,headimgurl,user_id,subscribe_time,shop_income,agent") -> where(" pid = '$user_id' ") -> select();
		//下下级信息
		$second_info = $users -> field("nickname,headimgurl,user_id,subscribe_time,shop_income,agent") -> where(" gid = '$user_id' ") -> select();
		//下下下级信息
		$third_info = $users -> field("nickname,headimgurl,user_id,subscribe_time,shop_income,agent") -> where(" oid = '$user_id' ") -> select();
		$this -> assign("user_info",$this->contact_more($user_info));
		$this -> assign("oid_info",$this->contact_more($oid_info));
		$this -> assign("gid_info",$this->contact_more($gid_info));
		$this -> assign("pid_info",$this->contact_more($pid_info));
		$this -> assign("first_info",$this->contact_more($first_info));
		$this -> assign("second_info",$this->contact_more($second_info));
		$this -> assign("third_info",$this->contact_more($third_info));
		$a = M('hbrecord')->where("user_id =2") -> sum('hongbao_Fee');
		$this -> display();
	}
	function contact_more($info){
		//转换时间戳、查询红包总收入
		$hbrecord = M('hbrecord');
		foreach($info as $k=>$v){
			$info[$k]['subscribe_time'] = date("m-d H:i",$v['subscribe_time']);
			$hongbao = $hbrecord -> where(" user_id = '$v[user_id]' ") -> sum("hongbao_fee");
			 $info[$k]['hongbao'] = intval($hongbao);
		}
		$info = $this -> infoto($info);
		return $info;
	}
	function get_address(){
		$user_id = $_POST['id'];
		$info = M('user_address') -> getByUser_id($user_id);
		$arr['info'] = '<h5>姓名：'.$info['username'].'</h5><h5>电话：'.$info['telphone'].'</h5><h5>地址：'.$info['address'].'</h5>';
		echo json_encode($arr);
	}
	
	private function infoto($info){
		if(F('daili_info','',DATA_ROOT)){
			$daili_info = F('daili_info','',DATA_ROOT);
		}else{
			$daili_info = M('daili_info') -> select();F('daili_info',$daili_info,DATA_ROOT);
		}
		foreach($info as $k=>$v){
			switch($v['agent']){
				case 0:
				$info[$k]['agent'] = "普通会员";
				break;
				case 1:
				$info[$k]['agent'] = $daili_info[0]['first_name'];
				break;
				case 2:
				$info[$k]['agent'] = $daili_info[0]['second_name'];
				break;
				case 3:
				$info[$k]['agent'] = $daili_info[0]['third_name'];
				break;
			}
		}
		return $info;
	}
	
	
	function excel_down(){
		
		//dump($info);exit;
		$excel = A('Admin/Excel');
		$excel ->index($info,$username);
	}
    function hongbao(){
		$this->display();
	}
	function hongbao_ajax(){
		$pagecount = 10;
		if($_GET['from_user_id']){
			$where = array('from_user_id'=>$_GET['from_user_id']);
		}elseif($_GET['user_id']){
			$where = array('user_id'=>$_GET['user_id']);
		}else{
			$where = array();
		}
		$agent_orders = M('agent_orders');$hbrecord = M('hbrecord');$users = M('users');
		$count = $hbrecord -> where($where) -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$info=$hbrecord-> where($where)->order("time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach($info as $key=>$value){
			$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
			$info[$key]['tonickname'] = $users -> getFieldByUser_id($value['user_id'],"nickname");
			$info[$key]['fromnickname'] = $users -> getFieldByUser_id($value['from_user_id'],"nickname");
			$info[$key]['order_sn'] = $agent_orders -> getFieldByOrder_id($value['order_id'],"order_sn");
			if(empty($info[$key]['tonickname'])){$info[$key]['nickname'] = "未知用户";}
			if(empty($info[$key]['fromnickname'])){$info[$key]['nickname'] = "未知用户";}
			
		}
		$info = $this -> infoto($info);
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign("info",$info);
		$this->display();
	}
	
	function del(){
		if($_POST['id']){
			$user_id = $_POST['id'];
			M('users')->where(" user_id = '$user_id' ")->delete();
			$arr = array();
			echo json_encode($arr);
		}
	}

}

?>