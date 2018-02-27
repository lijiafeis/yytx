<?php
namespace Xigua\Controller;
use Think\Controller;

class JifenController extends Controller{
	
	/* 关注增加会员积分 */
	function add_jifen_subscribe($user_id,$openid){
		
		$jifen_info = F('jifen_info','',DATA_ROOT);
		if($jifen_info['subscribe_switch'] != 1){return;}
		/* 查询用户是不是第一次参与奖励关注积分 */
		$jifen = M('jifen');
		$subscribe_info = $jifen -> where("type = 1 and user_id = '$user_id' ")->find();
		if($subscribe_info != null){return;}else{
			/* 增加积分，记录 */
			M('users') -> where("user_id = '$user_id'")-> setInc('jifen',$jifen_info['subscribe_number']);
			$jifen_data = array(
				'number'=>$jifen_info['subscribe_number'],
				'user_id'=>$user_id,
				'type'=>1,
				'time'=>time()
			);
			$jifen -> add($jifen_data);
			$jifen = $jifen_info['subscribe_number'];
			$word = $jifen_info['subscribe_word'];
			$match = array();  
			preg_match_all('/{\$(.*?)}/', $word, $match);  
			foreach($match[1] as $key => $value) {  
				if(isset($$value)) {  
					$word = str_replace($match[0][$key], $$value, $word);  
				}  
			}
			if($word != null){$weixin = A("Wxapi/weixin");$weixin->send_word($openid,$word);}
			
		}
	}
}

?>