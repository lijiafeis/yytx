<?php
namespace Shop\Controller;
use Think\Controller;

class WapController extends Controller{
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
	/* 手机端首页视图 */
	function index(){
		//dump(session('xigua_user_id'));
		/* 查询首页幻灯片数据 */
		$shop_bannar = D('Xigua/Shopbannar');
		$bannar_info = $shop_bannar->get_bannar();
		$this->assign('bannar_info',$bannar_info);
		//首页横幅广告
		$ad = M('shop_ad')->order("code desc")-> select();
		$this->assign('ad',$ad);
		/* 查询商品精品，热销，新品列表 */
		$goods = D('Xigua/Goods');
		//dump($goods->goods_list('best'));
		$this->assign('hot_list',$goods->goods_list('hot'));
		$this->assign('new_list',$goods->goods_list('new'));
		$this->assign('best_list',$goods->goods_list('best'));
		$this->display();
	}
	/* //分类页 */
	public function catelist(){
		$shop_categrey = M('shop_categrey');
		$categrey = $shop_categrey -> where(" pid = 0 and hidden = 0 ") ->order("code desc") -> select();
		foreach($categrey as $k=>$v){
			$categrey[$k]['arr'] = $shop_categrey -> where(" pid = '$v[cate_id]' and hidden = 0 ") ->order("code desc") -> select();	
		}
		//F('data',$categrey,DATA_ROOT );
		// $data = F('data','',DATA_ROOT);
		// dump($data);
		//购物车数量
		$num = M('shop_order_temp') -> where(" user_id = '$this->user_id' ") -> sum('good_num');
		$this -> assign("num",$num);
		$this->assign('empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;padding-bottom:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe6bc;</p>没有任何分类~</div>');
		$this->assign("categrey",$categrey);
		$this->display();
	}
	/* 测试分类页子分类列表 */
	function children_catelist(){
		$parent_id = $_GET['id'];
		if(!$parent_id){exit;}
		$shop_categrey = M('shop_categrey');
		$parent_name = $shop_categrey -> getFieldByCate_id($parent_id,'cate_name');
		$this -> assign('parent_name',$parent_name);
		$children_list = $shop_categrey -> where(" pid = '$parent_id' and hidden = 0 ") ->order("code desc") -> select();
		$this -> assign('children_list',$children_list);
		$this -> assign('empty','<div style="margin:20px 10px;color:#999;">暂无子分类</div>');
		$this -> display('wap_one');
	}
	
	/* 手机端商品详情页视图 */
	function good(){
		if(!I('good_id')){redirect(index);exit;}
		$goods = D('Xigua/Goods');
		$good_info = $goods->get_one(I('good_id'));
		$this->assign('good_info',$good_info);
		$this->assign('type_info',$goods->get_good_type($good_info['type_id']));
		$this->assign('num',$goods->get_categrey_num($this->user_id));
		$this->display();
	}
	/* 加入购物车 */
	function addcategrey(){
		$arr = array();
		if($_POST['good_id'] == null){
			$arr['success']= 0;
		}else{
			$goods = D('Xigua/Goods');
			$result = $goods->save_good_temp($_POST['good_id'],$this->user_id,$_POST['type']);
			if($result){$arr['success']= 1;}else{$arr['success']= 0;}
		}
		echo json_encode($arr);
	}
	/* 购物车页面视图 */
	function categrey(){
		$address_info = M('user_address') -> getByUser_id($this->user_id);
		$temp = M('shop_order_temp') -> where(" user_id = '$this->user_id' ") -> order("order_id desc") -> select();
		$shop_goods = M('shop_goods');$good_pic = M('good_pic');
		foreach($temp as $k=>$v){
			$temp[$k]['info'] = $shop_goods -> getByGood_id($v['good_id']);
			$temp[$k]['info']['pic_url'] = $good_pic -> getFieldByGood_id($v['good_id'],'pic_url');
			$temp[$k]['type'] = explode(',',$v['type']);
		}
		$this->assign("address_info",$address_info);
		$this->assign("temp",$temp);
		
		$this->display();
	}
	/* 保存用户的收货信息 */
	public function save_address(){
		$arr = array();
		$data = $_POST;
		$user_address = M('user_address');
		$info = $user_address ->getByUser_id($this->user_id);
		$data['address'] = trim($data['address']);
		if($info){
			$res = $user_address ->where(" user_id = '$this->user_id' ") -> save($data);
		}else{
			$data['user_id'] = $this->user_id;
			$res = M('user_address') -> add($data);
		}
		echo json_encode($arr);
	}
	/* 删除购物车商品 */
	function del_categrey(){
			$arr = array();
		if($_POST['order_id'] == null){
			$arr['success'] = 0;
		}else{
			$order_id = $_POST['order_id'];
			$result = M('shop_order_temp') -> where(" order_id = '$order_id' ") -> delete();
			if($result){$arr['success']= 1;}else{$arr['success']= 0;}
		}
		echo json_encode($arr);
	}
	/* 更改购物车商品数量 */
	function change_categrey(){
		if($_POST['order_id'] == null){
			$arr['success'] = 0;
		}else{
			$order_id = $_POST['order_id'];$shop_order_temp = M('shop_order_temp');
			switch($_POST['type']){
				case 'min':
				$result = $shop_order_temp -> where(" order_id = '$order_id' ") -> setDec('good_num');
				break;
				case 'max':
				$result = $shop_order_temp -> where(" order_id = '$order_id' ") -> setInc('good_num');
				break;
				default:
				
				break;
			}
			if($result){$arr['success']= 1;$arr['info']= $result;}else{$arr['success']= 0;}
		}
		echo json_encode($arr);
	}
	/* 请求满足条件的购物券 */
	function get_daijin(){
		/* 购物券 */
		$now_money = I('post.total_fee');
		$daijin_order = M('daijin_order');$shop_daijin = M('shop_daijin');
		$daijin_info = $daijin_order -> where("user_id='$this->user_id' and is_true = 1 and state = 0")->order("order_id desc")->select();
		$result = '<option>请选择</option>';
		$daijin_num = 0;
		foreach($daijin_info as $v){
			$daijin_info = $shop_daijin -> getByDaijin_id($v['daijin_id']);
			if($daijin_info['date_switch'] == 1){
				$str = '永久有效';
			}else{
				if($daijin_info['daijin_date'] < time()){continue;}
				$str = date('m月d日',$daijin_info['daijin_date']).'前有效';
			}
			
			if($daijin_info['daijin_rule'] == 1){
				$result .='<option value="'.$v['order_id'].'">'.$daijin_info['daijin_name'].'元代金券·'.$str.'</option>';$daijin_num++;
			}elseif($daijin_info['daijin_rule'] == 2){
				if($daijin_info['daijin_line'] <= $now_money){
					$result .='<option value="'.$v['order_id'].'">'.$daijin_info['daijin_name'].'元代金券·'.$str.'</option>';$daijin_num++;
				}
			}
		}
		$arr = array('result'=>$result,'daijin_num'=>$daijin_num);
		echo json_encode($arr);
	}
	/* 订单提交 */
	function order_sure(){
		/* 待支付订单信息 */
		$shop_order_temp = M('shop_order_temp');$shop_order = M('shop_order');$shop_order_detail = M('shop_order_detail');$good_pic = M('good_pic');$shop_goods = M('shop_goods');
		$order_temp = $shop_order_temp -> where(" user_id = '$this->user_id' ") -> select();
		if($order_temp == null){redirect(index);}
		//写入订单数据
		//创建订单号
		if(S('order_sn') && S('order_sn') < 9990){
			$order_rand = S('order_sn')+1;S('order_sn',$order_rand);
		}else{
			$order_rand = 1111;S('order_sn',$order_rand);
		}
		if(S('pay_id')){
			$pay_id = S('pay_id')+1;S('pay_id',$pay_id);
		}else{
			$pay_id = $shop_order_detail-> max("pay_id");$pay_id++;S('pay_id',$pay_id);
		}
		$order_sn = date('Y').$order_rand.time();
		$order_time = time();
		foreach($order_temp as $val){
			$pic_url = $good_pic -> getFieldByGood_id($val['good_id'],'pic_url');
			$good_info = $shop_goods -> getByGood_id($val['good_id']);
			$order_data = array();
			//新订单数据
			$order_data = array(
				"order_sn"=>$order_sn,//订单号
				"pay_id"=>$pay_id,
				"user_id"=>$this->user_id,
				"good_id"=>$val['good_id'],
				"good_name"=>$good_info['good_name'],
				"good_price"=>$good_info['good_price'],
				"jifen_profit"=>$good_info['jifen_profit'],
				"good_profit"=>$good_info['good_profit'],
				"good_num"=>$val['good_num'],
				"good_jifen"=>$good_info['good_jifen'],
				"time"=>$order_time,
			);
			if($pic_url != null){$order_data['pic_url'] = $pic_url;}
			if($val['type'] != null){
				$order_data['type'] = $val['type'];
			}
			$res = $shop_order_detail -> add($order_data);
			if($res){$shop_order_temp -> where(" order_id = '$val[order_id]' ") -> delete();}	
		}
		$arr = array('pay_id'=>$pay_id);
		echo json_encode($arr);
	}
	/* 搜索页面 */
	public function search(){
		
		$shop_categrey = M('shop_categrey');$shop_goods = M('shop_goods');$good_pic = M('good_pic');
		$where = array();$where['is_true'] = 1;
		if($_POST){
			$keyword = $_POST['keyword'];
			
			$where['good_name'] = array('like','%'.$keyword.'%');
		}
		if($_GET['pid'] != null){$where['cate_pid'] = $_GET['pid'];}
		if($_GET['gid'] != null){$where['cate_gid'] = $_GET['gid'];}
		$good_list = $shop_goods -> where($where)-> order("code desc") -> select();
		foreach($good_list as $k=>$v){
			$good_list[$k]['pic_url'] = $good_pic -> getFieldByGood_id($v['good_id'],"pic_url");
		}
		//购物车数量
		$num = M('shop_order_temp') -> where(" user_id = '$this->user_id' ")  -> sum('good_num');
		$this -> assign("keyword",$keyword);
		$this -> assign("num",$num);
		$this->assign('empty','<div style="text-align:center;padding-top:10%;font-size:14px;color:#777;line-height:20px;padding-bottom:20px;"><p class="icon iconfont" style="font-size:42px;color:#999;line-height:20px;">&#xe6bc;</p>没有任何商品~</div>');
	
		$this->assign("good_list",$good_list);
		$this->display();
	}
	
}