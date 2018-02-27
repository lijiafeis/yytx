<?php
namespace Xigua\Model;
use Think\Model;

class GoodsModel extends Model{
	 protected $tableName = 'shop_goods'; 
	/* 查询商品数据 */
	function goods_list($type){
		$where = array();
		switch($type){
			case 'best':
			$where['best'] = 1;
			break;
			case 'hot':
			$where['hot'] = 1;
			break;
			case 'new':
			$where['new'] = 1;
			break;
		}
		$where['is_true'] = 1;
		$res = $this->where($where)->order("code desc")->select();
		$good_pic = M('good_pic');
		foreach($res as $k=>$v){
			$good_id = $v['good_id'];
			$res[$k]['pic_url'] = $good_pic->where("good_id = '$v[good_id]'") ->  order('code desc')->select();
		}
		return $res;
	}
	/* 查询单个商品数据 */
	function get_one($good_id){
		$res = $this->where("good_id='$good_id'")->find();
		$res['good_pic'] = M('good_pic')->where("good_id='$good_id'") -> order("code desc") -> select();
		return $res;
	}
	
	/* 查询购物车商品数量 */
	function get_categrey_num($user_id){
		$num = M('shop_order_temp') -> where(" user_id = '$user_id' ") -> sum('good_num');
		return $num;
	}
	/* 查询商品属性 */
	function get_good_type($type_id){
		$spec_info = M('shop_spec')->where("type_id='$type_id'")->order("spec_id desc")->select();
		foreach($spec_info as $k=>$v){
			$spec_info[$k]['info'] = explode(',',$v['value']);
		}
		return $spec_info;
	}
	/* 缓存商品数据加入购物车 */
	function save_good_temp($good_id,$user_id,$type=''){
		$shop_order_temp = M('shop_order_temp');
		$res = $shop_order_temp -> where(array("good_id"=>$good_id,"user_id"=>$user_id)) -> find();
		if($res == null){
			$data = array('good_id'=>$good_id,'user_id'=>$user_id);
			if($type != ''){$data['type']=$type;}
			$result = $shop_order_temp -> add($data);
		}else{
			//合并订单
			if($type == $res['type']){
				$order_id = $res['order_id'];
				$result = $shop_order_temp -> where(" order_id = '$order_id' ") -> setInc('good_num');
			}else{
				$data = array('good_id'=>$good_id,'user_id'=>$user_id);
				if($type != ''){$data['type']=$type;}
				$result = $shop_order_temp -> add($data);
			}
			
		}
		return $result;
	}
	
}
?>