<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Pageajax;

class AgentController extends ActionController{
	
	function __construct(){
		parent::__construct();
		
		if(!session('admin_id')){
			$this->error('您还未登录，请登录',U('User/index'),5);exit;
		}
	}
	
	/* 充值记录 */
	function chongzhi(){
		if(I('get.type') == 'del'){
			if($_POST['id']){
				$id = $_POST['id'];$arr = array();
				$info = M('broke_record')->where(" id = '$id' ")->find();
				if($info['state'] == 1){$arr['success'] = 0;$arr['info'] = '订单已成功提现，不支持删除！';echo json_encode($arr);exit;}
				M('broke_record') -> where("id = '$id'")->delete();
				
				$arr['success'] = 1;
				echo json_encode($arr);
			}
		}elseif(I('get.type') == 'list'){
			$users = M('users');
			$pagecount = 20;
			$where = array('type'=>2);
			$count = M('broke_record') ->where($where)-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			$info=M('broke_record')->where($where)->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
			
			$Page->setConfig('first','首页');
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('last','尾页');
			$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
			$fenxiao_info = F('fenxiao_info','',DATA_ROOT);
			foreach($info as $key=>$value){
				$info[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
				$info[$key]['user_info']  = $users ->field("user_id,nickname,agent,shop_income")-> where(" user_id = '$value[user_id]' ") -> find();
				if($info[$key]['user_info']['agent'] == 0){$info[$key]['user_info']['agent_name'] = '普通会员';}else{$info[$key]['user_info']['agent_name'] = $fenxiao_info['fenxiao_name'];}
			}
			// 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show();
			$this->assign('count',$count);
			$this->assign('page',$show);
			$this->assign("info",$info);
			$this->display('Agent_chongzhi_ajax');
		}else{
			$this->display();
		}
	}

	
	/* 会员列表 */
	function users()
    {
        //查询商户名称
        $data = M('agent_info') -> field('id,wxname')-> select();
        $this -> assign('data',$data);
        $this->display();
    }

    /**
     * 修改用户的平台
     */
    public function setUserAgent(){
        $user_id = $_POST['user_id'];
        $agent_id = $_POST['agent_id'];
        $res = M('user') -> where("user_id = {$user_id}") -> setField("agent_id",$agent_id);
        if($res){
            echo 0;
        }else{
            echo -1;
        }
    }

    public function usersbb(){
	    $name = $_GET['nickname'];
	    $user_id = $_GET['user_id'];
		$type = $_GET['type'];
	    $where = array();
//	    $where['is_true'] = 1;
	    if($name){
	        $where['name']= $name;
        }
        if($user_id){
	        $where['username'] = $user_id;
        }
		
//        $where['a.is_true'] = 1;
        $count=M('user')->where($where) -> count();
        $Page = $this -> setPage($count);
		if($type != 0){
		    $where['agent_id'] = $type;
            $count=M('user')->where("agent_id = {$type}")-> count();
			 $arr= M('user')
            -> where($where)
			-> limit($Page -> firstRow . ',' . $Page -> listRows)
			-> order('user_id desc')
            -> select();
		}else{
			 $arr= M('user')
            //-> alias('a')
            //-> field('a.*,b.name as sj_name')
            //-> join('left join __USER__ as b on a.sj_userid = b.user_id')
            -> where($where)
            -> where(['is_true' => 1])
			-> order('user_id desc')
			-> limit($Page -> firstRow . ',' . $Page -> listRows)
            -> select();
		}

        foreach ($arr as $key => $val){
            if($val['sj_userid'] != 0){
                $arr[$key]['sj_name'] = M('user') -> getFieldByUserId($val['sj_userid'],'name');
            }else{
                $wxname = M('agent_info') -> getFieldById($val['agent_id'],'wxname');
                $arr[$key]['sj_name'] = $wxname;
            }
			 $arr[$key]['wxname'] = M('agent_info') -> getFieldById($val['agent_id'],'wxname');
            //订单总金额，提现金额
            $order_all_money = M('shop_order')
                -> field('sum(zonge) as a')
                -> where("user_id = {$val['user_id']} and state = 1")
                -> select();
            $arr[$key]['order_money'] = $order_all_money[0]['a'];

            $tixian_money = M('tixian')
                -> field("sum(money) as a")
                -> where("user_id = {$val['user_id']} and type = 1")
                -> select();
            $arr[$key]['tixian_money'] = $tixian_money[0]['a'];
            //查询下级的订单
            $arr[$key]['yeji'] = $this->getYeji($val['user_id']);
		}

        $this->assign('arr',$arr);
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();
    }

    private function getYeji($user_id){
        $data = M('user') -> field('user_id') -> where("sj_userid = {$user_id}") -> select();
        if(!$data){return 0;}
        $money = 0;
        foreach ($data as $k => $v){
            $money += M('shop_order_detail') -> where("user_id = {$v['user_id']} and state = 1") -> sum('good_price');
        }
        return $money;
    }
	
	public function black(){
		$user_id = $_POST['user_id'];
		$black = M('user') -> getFieldByUserId($user_id,'black');
		if($black == 0){
			$res = M('user') -> where(['user_id' => $user_id]) -> setField('black',1);
		}else{
			$res = M('user') -> where(['user_id' => $user_id]) -> setField('black',0);
		}
		if($res){
			echo 0;
		}else{echo 1;}
		
	}

    public function setPage($count){
        $pagecount = 10;
        $Page = new Pageajax($count,$pagecount);
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        return $Page;
    }

    public function yongjinList(){
        $user_id = $_GET['user_id'];
        $this -> assign('user_id',$user_id);
        $this -> display();
    }

    public function yongjinbb(){
        $user_id = $_GET['user_id'];
        $user_id = intval($user_id);
        $model = M('yongjin');
            $count=$model-> where(['user_id' => $user_id]) -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,a.type as atype,b.*,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where(['a.user_id' => $user_id])
                -> order('a.time desc')
                -> select();
        $this->assign('arr',$arr);
        // 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();
    }

    public function agent(){
        $this -> display();
    }

    public function agentbb(){
        $name = $_GET['nickname'];
        $id = $_GET['user_id'];
        $where = array();
        if($name){
            $where['wxname']= $name;
        }
        if($id){
            $where['id'] = $id;
        }
        $count=M('agent_info')->where($where) -> count();
        $Page = $this -> setPage($count);
        $arr= M('agent_info')
            -> alias("a")
            -> field("a.*")
            -> where($where)
            -> group("a.id")
            -> order('id desc')
            -> limit($Page -> firstRow . ',' . $Page -> listRows)
            -> select();
        foreach ($arr as $k => $v){
            $a = M('shop_order') -> where("state = 1 and agent_id = {$v['id']}") -> sum('zonge');
            $arr[$k]['zonge'] = $a;
            //计算当天营业额，订单数，总订单数

            //订单总金额，提现金额
            $sql1 = "select sum(zonge) a from wx_shop_order where state = 1 and  from_unixtime(
time,'%Y-%m-%d') = current_date() and agent_id = {$v['id']};";
            $model = M();
            $data = $model -> query($sql1);

            $arr[$k]['currentMoney'] = $data[0]['a'];

            $sql1 = "select sum(good_num) a from wx_shop_order_detail where state = 1 and  from_unixtime(
time,'%Y-%m-%d') = current_date() and agent_id = {$v['id']};";
            $model = M();
            $data = $model -> query($sql1);
            $arr[$k]['currentOrder'] = $data[0]['a'];

            /* 总的订单数 */
            $user_all_tixian = M('shop_order_detail')
                -> field("sum(good_num) as a")
                -> where("state = 1 and agent_id = {$v['id']}")
                -> select();
            $arr[$k]['orderNum'] = $user_all_tixian[0]['a'];



        }
        $this->assign('arr',$arr);
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();
    }

    //添加新代理
    public function agentAdd(){
        if(IS_GET){
            $this -> display();
        }else{
            $pass1 = $_POST['password'];
            $pass2 = $_POST['password1'];
            if($pass1 !== $pass2){
                $this -> error('两次密码不一样','agentAdd');
            }else{
                //判断密码是否保存一样
                $pass1 = md5('xiguakeji' . $pass1);
                $re = M('agent_info') -> where("username = '{$_POST['username']}'") -> select();
                if($re){
                    $this -> error('用户名重复','agentAdd');
                }else{
                    $_POST['password'] = $pass1;
                    $res = M('agent_info') -> add($_POST);
                    if($res){
                        //创建成功，生成唯一的标志token
                        $this -> createToken($res);
                        //创建一个菜单
                        $data['pid'] = 0;
                        $data['title'] = '商城';
                        $data['url'] = '';
                        $data['is_show'] = 1;
                        $data['type'] = 'view';
                        $data['agent_id'] = $res;
                        M('menu') -> add($data);
                        $this -> success('创建成功','agent');
                    }else{
                        $this -> error('创建失败','agent');
                    }
                }

            }
        }

    }

    private function createToken($agent_id){
        $model = M('agent_token');
        $flag = true;
        while($flag){
            $agent_token = $this -> createStr(6);
            $res = $model -> where("agent_token = '$agent_token'") -> select();
            if($res){

            }else{
                $flag = false;
                //保存代理商对照表
                $data['agent_id'] = $agent_id;
                $data['agent_token'] = $agent_token;
                $model -> add($data);
            }
        }
        return true;
    }

    //生成长度16的随机字符串
    private function createStr($length = 6) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function getYongJin(){
        $user_id = I('post.user_id');
//        $data = F('fee','',DATA_ROOT);
        $arr['success'] = 0;
//        foreach ($data as $k => $v){
//            if($v['user_id'] == $user_id){
//                $arr['data'] = $v;break;
//            }
//        }
        $yeji = M('fee1') -> where("user_id = {$user_id}") -> select();
        $arr['data'] = $yeji[0];
        //计算总的
        $res = M('yongjin')
            -> field("sum(money) as money,type")
            -> where("user_id = {$user_id}")
            -> group('type')
            -> select();
        $a = array('w1' => 0,'w2' => 0,'w3' => 0,'w4' => 0);
        foreach ($res as $k => $v){
            switch ($v['type']){
                case 0:
                    $a['w1'] = $v['money'];
                    break;
                case 1:
                    $a['w2'] = $v['money'];
                    break;
                case 2:
                    $a['w3'] = $v['money'];
                    break;
                case 3:
                    $a['w4'] = $v['money'];
                    break;
            }
        }
        $arr['data1'] = $a;
        if(!$arr['data']){
            $b = array('w1' => 0,'w2' => 0,'w3' => 0,'w4' => 0);
            $arr['data'] = $b;
        }
        $this -> ajaxReturn($arr);
    }

    public function xiaji(){
        $user_id = I('user_id');
        if(!$user_id){
            $this -> error('数据不完整','users');
        }
        $this -> assign('user_id',$user_id);
        $this -> display();
    }

    public function xiajibb(){
        $user_id = I('get.user_id');
        $count=M('user')->where("sj_userid = {$user_id}") -> count();
        $Page = $this -> setPage($count);
        $arr = M('user')
            -> field('user_id,name,username')
            -> where("sj_userid = {$user_id}")
            -> limit($Page -> firstRow . ',' . $Page -> listRows)
            -> select();
        foreach ($arr as $k => $v){
            $res = M('shop_order_detail') -> where("user_id = {$v['user_id']} and state = 1 and flag = 0") -> select();
            if($res){
                $arr[$k]['is_order'] = 1;
            }else{
                $arr[$k]['is_order'] = 0;
            }
        }
        $this->assign('arr',$arr);
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();
    }


}