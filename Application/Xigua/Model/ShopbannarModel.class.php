<?php
namespace Xigua\Model;
use Think\Model;

class ShopbannarModel extends Model{
	 protected $tableName = 'shop_bannar'; 
	/* 查询商城海报数据 */
	function get_bannar(){
		$res = $this->order("code")->select();
		return $res;
	}
	
	
}
?>