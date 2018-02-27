<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20 0020
 * Time: 13:46
 */
namespace Agent\Controller;
use Think\Controller;
use Think\Pageajax;

class ZiJinController extends Controller{

    public function __construct()
    {
        parent::__construct();

        if(!session('admin_id')){
            $this->error('您还未登录，请登录',U('User/index'),5);exit;
        }
        $this -> id = session('admin_id');
    }

    public function tixianList(){
		$money = M('tixian') -> where("agent_id = {$this->id}") -> sum('sjmoney');
		$this -> assign('money',$money);
        $this -> display();
    }

    public function tixianbb(){
        $name = $_GET['name'];
        $id = $_GET['id'];
        $model = M('tixian');
        if(!$name && !$id){
            $count=$model-> where("type = 1 and agent_id = {$this->id}") -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,a.time as atime,a.money as txmoney,a.user_id as uid,a.type as ty,b.*')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where(['a.type' => 1,'a.agent_id' => $this->id])
                -> order('a.time desc')
                -> select();
        }else if($name){
            $count=$model
                -> alias('a')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> where(['b.name' => $name,'a.type' => 1,'a.agent_id' => $this->id])
                -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,a.time as atime,a.money as txmoney,a.user_id as uid,a.type as ty,b.*')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where(['a.type' => 1,'name' => $name,'a.agent_id' => $this->id])
                -> order('a.time desc')
                -> select();
        }else if($id){
            $count=$model
                -> alias('a')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> where(['b.username' => $id,'a.type' => 1,'a.agent_id' => $this->id])
                -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,a.time as atime,a.money as txmoney,a.user_id as uid,a.type as ty,b.*')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where(['a.type' => 1,'b.username' => $id,'a.agent_id'=>$this->id])
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
        $count=$model-> where("type = 0 and agent_id = {$this->id}") -> count();
        $Page = $this -> setPage($count);
		//select * from wx_user where user_id in (select user_id from wx_user where sj_userid=15);
        $arr= $model
            -> alias('a')
            -> field('a.time,a.id as aid,a.user_id as uid,a.money as txmoney,a.type as ty,a.state as sta,c.*,c.tel as banktel,c.type as te,b.type as tp,b.name')
            -> join("left join __USER__ as b on a.user_id = b.user_id")
            -> join("left join __USER_BANK__ as c on a.user_id = c.user_id")
            -> limit($Page -> firstRow . ',' . $Page -> listRows)
            -> where(['a.type' => 0,'a.agent_id' => $this->id])
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

    //佣金记录
    public function yongjinList(){
		$money = M('yongjin') -> where("agent_id = {$this->id}") -> sum('money');
		$this -> assign('money',$money);
        $this -> display();
    }

    public function yongjinbb(){
        $name = $_GET['name'];
        $id = $_GET['id'];
        $model = M('yongjin');
        if(!$name && !$id){
            $count=$model-> where("agent_id = {$this->id}") -> count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,a.type as atype,b.*,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> where("a.agent_id = {$this->id}")
                -> order('a.time desc')
                -> select();
        }

        if($name && !$id){
            $count=$model
                -> alias("a")
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.name' => $name,'a.agent_id' => $this->id])->count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,a.type as atype,b.*,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> where(['b.name' => $name,'a.agent_id' => $this->id])
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> order('a.time desc')
                -> select();
        }

        if(!$name && $id){
            $count=$model
                -> alias("a")
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.username' => $id,'a.agent_id' => $this->id])->count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,a.type as atype,b.*,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> where(['b.username' => $id,'a.agent_id' => $this->id])
                -> limit($Page -> firstRow . ',' . $Page -> listRows)
                -> order('a.time desc')
                -> select();
        }

        if($name && $id){
            $count=$model
                -> alias("a")
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                ->where(['b.username' => $id,'b.name' => $name,'a.agent_id' => $this->id])->count();
            $Page = $this -> setPage($count);
            $arr= $model
                -> alias('a')
                -> field('a.*,b.*,a.type as atype,a.time as yjtime,a.money as yjmoney')
                -> join("left join __USER__ as b on a.user_id = b.user_id")
                -> where(['b.username' => $id,'b.name' => $name,'a.agent_id' => $this->id])
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




}