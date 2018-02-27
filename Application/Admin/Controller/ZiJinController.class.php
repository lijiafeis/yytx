<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20 0020
 * Time: 13:46
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Pageajax;

class ZiJinController extends Controller{
	function __construct(){
		parent::__construct();
		//echo session('admin_id');
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'));
		}
		
	}
	
    public function tixianList(){
		$money = M('tixian') -> sum('sjmoney');
		$this -> assign('money',$money);
        $this -> display();
    }

    public function tixianbb(){
        $name = $_GET['name'];
        $id = $_GET['id'];
        $model = M('tixian');
        if(!$name && !$id){
            $count=$model-> where('type = 1') -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('d.wxname,a.*,a.time as atime,a.money as txmoney,a.user_id as uid,a.type as ty,b.*')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where(['a.type' => 1])
                -> order('a.time desc')
                -> select();
        }else if($name){
            $count=$model
                -> alias('a')
                -> field('a.*,d.wxname')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> where(['b.name' => $name,'a.type' => 1])
                -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('d.wxname,a.*,a.time as atime,a.money as txmoney,a.user_id as uid,a.type as ty,b.*')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where(['a.type' => 1,'name' => $name])
                -> order('a.time desc')
                -> select();
        }else if($id){
            $count=$model
                -> alias('a')
                -> field('a.*,d.wxname')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> where(['b.username' => $id,'a.type' => 1])
                -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('d.wxname,a.*,a.time as atime,a.money as txmoney,a.user_id as uid,a.type as ty,b.*')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where(['a.type' => 1,'b.username' => $id])
                -> order('a.time desc')
                -> select();
        }
        $this->assign('arr',$arr);
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();

    }

    public function tixianSq(){
        $this -> display();
    }

    public function tixianSqbb(){
        $model = M('tixian');
        $count=$model-> where("type = 0") -> count();
        $Page = $this -> setPage($count);
		//select * from wx_user where user_id in (select user_id from wx_user where sj_userid=15);
        $arr= $model
            -> alias('a')
            -> field('d.wxname,a.time,a.id as aid,a.user_id as uid,a.money as txmoney,a.type as ty,a.state as sta,c.*,c.tel as banktel,c.type as te,b.type as tp,b.name')
            -> join("left join __USER__ as b on a.user_id = b.user_id")
            -> join("left join __USER_BANK__ as c on a.user_id = c.user_id")
            -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
            -> limit($Page -> firstRow . ',' . $Page -> listRows)
            -> where(['a.type' => 0])
            -> order('a.time desc')
            -> select();
        $this->assign('arr',$arr);
        //dump($arr);
		//exit;
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();

    }
    public function setType(){
        $tx_id = $_POST['tx_id'];
        $res = M('tixian') -> where(['id' => $tx_id]) -> setField('type',1);
        //扣除手续费和复消积分
        if($res){
            $money = M('tixian') -> getFieldById($tx_id,'money');
            $user_id = M('tixian') -> getFieldById($tx_id,'user_id');
            $fxmoney = $money * 0.05;
            $sjmoney = $money * 0.9;
            $a = M('user') -> where("user_id = {$user_id}") -> setInc("fxmoney",$fxmoney);
            if($a){
//                $money = $money - $fxmoney;
                M('tixian') -> where(['id' => $tx_id]) -> setField('sjmoney',$sjmoney);
                M('tixian') -> where(['id' => $tx_id]) -> setField('jifen',$fxmoney);
            }
            echo 0;
        }else{
            echo -1;
        }
    }

    //佣金记录
    public function yongjinList(){
		$money = M('yongjin') -> sum('money');
        //查询商户名称
        $data = M('agent_info') -> field('id,wxname')-> select();
        $this -> assign('data',$data);
		$this -> assign('money',$money);
        $this -> display();
    }

    public function yongjinbb(){
        $name = $_GET['name'];
        $id = $_GET['id'];
        $type = $_GET['type'];
        $model = M('yongjin');
        if(!$name && !$id){
            if($type != 0){
                $where['agent_id'] = $type;
                $where1['a.agent_id'] = $type;
            }
            $count=$model-> where($where) ->count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('d.wxname,a.*,a.type as atype,b.*,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where($where1)
                -> order('a.time desc')
                -> select();
        }

        if($name && !$id){
            $count=$model
                -> alias("a")
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.name' => $name])->count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('d.wxname,a.*,a.type as atype,b.*,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> where(['b.name' => $name])
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> order('a.time desc')
                -> select();
        }

        if(!$name && $id){
            $count=$model
                -> alias("a")
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.username' => $id])->count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('d.wxname,a.*,a.type as atype,b.*,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> where(['b.username' => $id])
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> order('a.time desc')
                -> select();
        }

        if($name && $id){
            $count=$model
                -> alias("a")
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.username' => $id,'b.name' => $name])->count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('d.wxname,a.*,b.*,a.type as atype,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> join("left join __AGENT_INFO__ as d on b.agent_id = d.id")
                -> where(['b.username' => $id,'b.name' => $name])
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> order('a.time desc')
                -> select();
        }

        $this->assign('arr',$arr);
        // 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();
    }

    //购买代理页面
    public function dailiList(){
		$money = M('goumai') -> sum('money');
		$this -> assign('money',$money);
        $this -> display();
    }

    /**
     * 代理的显示具体的内容页面
     */
    public function dailibb()
    {
        $name = $_GET['name'];
        $id = $_GET['id'];
        $model = M('goumai');
        if (!$name && !$id) {
            $count = $model->count();
            $Page = $this->setPage($count);
            $arr = $model
                ->alias('a')
                ->field('a.*,b.*,b.type as btype,a.time as atime')
                ->join("left join __USER__ as b on a.user_id = b.user_id")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->where(['a.type' => 1])
                ->order('a.time desc')
                ->select();
        }

        if ($name && !$id) {
            $count = $model
                ->alias("a")
                ->join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.name' => $name])->count();
            $Page = $this->setPage($count);
            $arr = $model
                ->alias('a')
                ->field('a.*,b.*,b.type as btype,a.time as atime')
                ->join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.name' => $name, 'a.type' => 1])
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order('a.time desc')
                ->select();
        }

        if (!$name && $id) {
            $count = $model
                ->alias("a")
                ->join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.user_id' => $id])->count();
            $Page = $this->setPage($count);
            $arr = $model
                ->alias('a')
                ->field('a.*,b.*,b.type as btype,a.time as atime')
                ->join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.user_id' => $id, 'a.type' => 1])
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order('a.time desc')
                ->select();
        }

        if ($name && $id) {
            $count = $model
                ->alias("a")
                ->join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.user_id' => $id, 'b.name' => $name])->count();
            $Page = $this->setPage($count);
            $arr = $model
                ->alias('a')
                ->field('a.*,b.*,b.type as btype,a.time as atime')
                ->join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.user_id' => $id, 'b.name' => $name, 'a.type' => 1])
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order('a.time desc')
                ->select();
        }
        $this->assign('arr',$arr);
        // 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();
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

    //显示未发放的记录
    public function weifafang(){
        $data = F('nojilu');
        $this -> assign('data',$data);
        $this -> display();
    }


}