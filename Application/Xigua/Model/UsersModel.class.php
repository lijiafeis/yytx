<?php
namespace Xigua\Model;
use Think\Model;

class UsersModel extends Model{
	/* 根据openid查出user_id */
	function get_user_by_openid($openid,$data){
	    //file_put_contents('openid.txt',$openid);
		$res = $this->where("openid = '$openid'")->find();
		if($res == null){
			if(session('uid')){
				$data['pid'] = session('uid');
				$pid_openid = $this->getFieldByUser_id($data['pid'],'openid');
				$weixin = A('Wxapi/Weixin');
				$weixin -> send_word('「'.$pid_openid,$data['nickname'].'」查看了您的分享，成为了您的下层会员');
			}
			if(session('xigua_user_id')){
                $data['user_id'] = session('xigua_user_id');
                $user_id = $this->add($data);
            }else{
			    $this -> error('请重新登录','/Login/User/Index');
            }

		}else{
			$user_id = $res['user_id'];
		}
		return $user_id;
	}
	
	/* 加入会员信息 */
	function add_user($openid){
		/* 判断会员存不存在 */
		$user_id = $this-> getFieldByOpenid($openid,'user_id');
		if(!$user_id){
			$weixin = A("Wxapi/Weixin");
			$user_data = $weixin ->get_user_info($openid);
			$user_id = $this -> add($user_data);
		}
		return $user_id;
	}
	
	/* 接口加入会员信息 */
	function weixin_add_user($openid,$scene_id){
		/* 判断会员存不存在 */
		$user_id = $this-> getFieldByOpenid($openid,'user_id');
		if(!$user_id){
			
			$weixin = A("Wxapi/Weixin");
			$user_data = $weixin ->get_user_info($openid);
			/* 判断引入会员信息 */
			if($scene_id != ''){
				$user_data['pid'] = $scene_id;
				$pid_openid = $this-> getFieldByUser_id($scene_id,'openid');
				$weixin -> send_word($pid_openid,'「'.$user_data['nickname'].'」'.C('PID_NOTIEC'));
			}
			$user_data['sj_userid'] = $scene_id;
			$user_id = $this -> add($user_data);
			/* 判断是否无门槛成为分销商，是，成为分销商 */
			$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
			if($fenxiao_info['fenxiao_exist'] == 1){
				$res = $this->where("user_id = '$user_id'") -> setField('agent',1);
				/* 发送升级消息模板提醒 */
				if($res){
					$template = A("Pay/Template");
					$template->send_agent(C('USER_AGENT_ONE').$fenxiao_info['fenxiao_name'].C('USER_AGENT_TWO'),$user_data['nickname'],$user_data['openid']);
				}
			}
		}
		return $user_id;
	}
	
	/* 根据user_id查询用户详细信息 */
	function get_user_info_by_user_id($user_id){
		$res = $this -> getByUser_id($user_id);
		$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
		switch($res['agent']){
			case 0:
			$res['agent_name'] = C('USER_NORMAL_NAME');
			break;
			case 1:
			$res['agent_name'] = $fenxiao_info['fenxiao_name'];
			break;
		}
		return $res;
	}
	
	/* 查询团队人数 */
	function get_team_num($user_id){
		
		$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
		$fenxiao_level = $fenxiao_info['fenxiao_level'];
		$num = $this->each_team($user_id,$fenxiao_level);
		return $num;
	}
	/* 循环查出各层级人数和 */
	function each_team($user_id,$level,$i = 0,$num = 0){
		$level_info = $this-> where("pid = '$user_id'") -> select();
		//dump($level_info);
		/* 当$i等于 $level 时 查询到了总层级数，返回结果 */
		/* 当$level_info为空时，该支路下线无人，返回结果 */
		$j = $i+ 1;
		if($i == $level){
			return $num;
		}
		if($level_info == null){
			return $num;
		}else{
			$i++;
			$num = count($level_info) + $num;
			foreach($level_info as $v){
				$num = $this -> each_team($v['user_id'],$level,$i,$num,$arr);
				$num = $num + $num1;
			}
			return $num;
		}
	}
	
	/* 查询特定层级用户集 */
	function get_team_level($user_id,$level,$i=1){
		$level_info = $this -> where("pid='$user_id'") -> select();
		if($level_info == null){
			return array();
		}else{
			if($level == $i){
				return $level_info;
			}else{
				$i++;
				$arr = array();
				foreach($level_info as $v){
					$arr1 = $this->get_team_level($v['user_id'],$level,$i);
					$arr = array_merge($arr,$arr1);
				}
				return $arr;
			}
			
		}
	}
	
}
?>