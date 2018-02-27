<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/8 0008
 * Time: 18:33
 */
namespace Home\Controller;
use Think\Controller;

class UserController extends Controller{
    // /*用户来，判断session存不存在，*/
    function __construct(){
        parent::__construct();
        $this->user_id = session('xigua_user_id');
        $res = $this->is_weixin();
        if ($res == 1){
            //$info = M('zhuce')->where("user_id = '$this->user_id'")->find();
            if (!$this->user_id){
                redirect(U('/Login/User/register',array('id'=>$_GET['id'],'agent_id'=>$_GET['agent_id'])));
            }else{
                $user_info=M('users')->where("user_id = '$this->user_id'")->find();
                if(!$user_info){
                    $redirect_uri='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    redirect('http://'.$_SERVER['HTTP_HOST'].U('/wxapi/oauth/index/')."?surl=".$redirect_uri);exit;
                }
            }

        }else {
            if(!$this->user_id){
                $now_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                //dump($_GET);
                //dump($now_url);
                $this -> assign('now_url',$now_url);
                $this->display('Login_error');exit;
            }else{
                $this -> user_id = session('xigua_user_id');
            }
        }
        $agent_id = M('user') -> getFieldByUserId($this->user_id,'agent_id');
        $this -> token = M('agent_token') -> getFieldByAgentId($agent_id,'agent_token');

    }

    //判断打开方式
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return 1;
        }
        return 2;
    }

    public function index(){
        $model = M('users');
        $head_img = $model -> getFieldByUserId($this->user_id,'headimgurl');
        $nickname = M('user') -> getFieldByUserId($this -> user_id,'name');

        $res = M('shop_order') -> where("user_id = {$this->user_id} and state = 1") -> select();
        if(!$res){
            $this -> assign('flag',1);
        }

        $this -> assign('head_img',$head_img);
        $this -> assign('nickname',$nickname);
        $this -> assign('user_id',$this->user_id);
//        $this -> assign('type',$type);
        $this -> display();
    }

    //我的团队
    public function team(){
        $this -> display();
    }

    public function teambb(){
        $page = $_GET['p'];
        $start = ($page -1) * 15;
        $data = M('user')
            -> field('user_id,name,username')
            -> where("sj_userid = {$this->user_id}")
            -> limit($start,15)
            -> select();
        foreach ($data as $k => $v){
            $res = M('shop_order') -> where("user_id = {$v['user_id']} and state = 1") -> select();
            if($res){
                $data[$k]['is_order'] = 1;
            }else{
                $data[$k]['is_order'] = 0;
            }
        }
        $this -> ajaxReturn($data);
    }

    public function getUserInfo($data){
        $model = M('user');
        $arr = array();
        foreach ($data as $k => $v){
            $info = $model -> where(['sj_userid' => $v['user_id']]) -> select();
            $arr = array_merge_recursive($arr,$info);
        }
        return $arr;
    }

    //佣金的的兑换
    public function wodeqianbao(){
        $info = M('user') -> field('money,fxmoney') -> where(['user_id' => $this->user_id]) -> select();
        $bank = M('user_bank') -> where("user_id = {$this->user_id}") -> select();
        $this -> assign('bank',$bank[0]);
        $this -> assign('info',$info[0]);
        $this -> display();
    }

    /* 用户提现 */
    function user_tixian(){
//        $this->user_id = session('xigua_user_id');
        if(!$this->user_id){
            $arr['success'] = 0;
            $arr['info']='网页会话已超时，请重新打开页面';
            echo json_encode($arr);exit;}
        //判断是否有为操作的提现
        $is_tixian = M('tixian') -> where("user_id = {$this->user_id} and type = 0") -> select();
        if($is_tixian){
            echo -4;exit;
        }


        /******写入用户的提现银行信息*******/
        $data = array();
        $data['user_name'] = I('user_name');
        $data['tel'] = I('tel');
        if(I('pay_type') == 2){
            $data['alipay_number'] = I('alipay_number');
            $data['type'] = 0;
        }elseif(I('pay_type') == 1){
            $data['bank_name'] = I('bank_name');
            $data['bank_number'] = I('bank_number');
            // $data['alipay_number'] = I('alipay_number');
            $data['type'] = 1;
        }
        $money = $_POST['money'];
        if(!$money){exit;}
        $money = $money * 1;
        $data['user_id'] = $this->user_id;
        $bank_res = M('user_bank') -> where("user_id = '$this->user_id' ")->find();
        if($bank_res == null){
            $res = M('user_bank') -> add($data);
        }else{
            $res = M('user_bank') ->where("bank_id = '$bank_res[bank_id]' ") -> save($data);
        }


        if($money < 10){
            exit;
        }
        $fee = M('user') -> getFieldByUserId($this->user_id,'money');
        if($money > $fee){
            echo -1;
            exit;
        }
        $agent_id = M('user') -> getFieldByUserId($this->user_id,'agent_id');
        $data1 = array(
            'user_id'=>$this->user_id,
            'time'=>time(),
            'money'=>$money,
            'agent_id' => $agent_id
        );
        if(I('pay_type') == 2){
            $data1['state'] = 1;
        }elseif(I('pay_type') == 1){
            $data1['state'] = 0;
        }
        $res = M('tixian') -> add($data1);
        if($res){
            M('user') -> where(['user_id' => $this->user_id]) -> setDec('money',$money);
            echo 0;
        }else{
            echo -2;
        }
    }

    //我的收藏
    public function collect(){
        $data = M('collect')
            -> alias('a')
            -> field('a.id,b.good_id,b.good_name,b.good_chengben,b.number,c.pic_url')
            -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
            -> join("left join __GOOD_PIC__ as c on a.good_id = c.good_id")
            -> order('a.time desc')
            -> where("a.user_id = $this->user_id")
            -> select();
//        dump($data);exit;
        $this -> assign('data',$data);
        $this -> display();
    }


    //收藏删除
    public function delCollect(){
        $collect_id = $_POST['good_id'];
        $res = M('collect') -> delete($collect_id);
        if($res){
            echo 0;
        }else{
            echo -1;
        }
    }

    public function order(){
        $this -> display();
    }


    public function qr(){
        //判断当前用户是否购买商品，只有购买商品才可能出现二维码
        $res = M('shop_order') -> where("user_id = {$this->user_id} and state = 1") -> select();
        if(!$res){
            echo '只用购买产品，才能生成专属二维码';
//            redirect(U('/Login/User/register',array('id'=>$_GET['id'],'agent_id'=>$_GET['agent_id'])));
            exit;
        }
        if (! is_dir ( 'Public/webqr/' )) {
            mkdir ( 'Public/webqr/' );
        }
        //if (!file_exists ( 'Public/webqr/' . $this->user_id . '.png' )) {
        import ( "Org.Util.Erweima" );
        //得到当前人的是属于哪个平台的agent_id;
        $agent_id = M('user') -> getFieldByUserId($this->user_id,'agent_id');
        $agent_token = M('agent_token') -> getFieldByAgentId($agent_id,'agent_token');
        if(!$agent_token){
            $this -> error('未知错误','index');exit;
        }
        $value = "http://".$_SERVER['SERVER_NAME'].U('/Login/User/register')."?id=".$this->user_id . "&agent_id=" . $agent_token;
        $errorCorrectionLevel = "L";
        $matrixPointSize = '7';
        \QRcode::png ( $value, 'Public/webqr/' . $this->user_id . '.png', $errorCorrectionLevel, $matrixPointSize, 1, true );
        //}
        $qrimg = A ("Wxapi/Qrimg" );
        $name = M('user') -> getFieldByUserId($this->user_id,'name');
        $qrimg->web_qr ( $this->user_id,$name);
        $this->assign ( 'user_id', $this->user_id );
        $this->display ();
    }


    //提现记录
    public function tixianShow(){
        $this -> display();
    }

    public function tixianshowbb(){
        $page = $_GET['p'];
        $start = ($page -1) * 20;
        $data = M('tixian')
            -> alias('a')
            -> field('a.*,b.name')
            -> join("left join __USER__ as b on a.user_id = b.user_id")
            -> where(['a.user_id' => $this->user_id])
            -> limit($start,20)
            -> order('a.time desc')
            -> select();
        foreach ($data as $k => $v){
            $data[$k]['time'] = date('Y-m-d H:i:s',$v['time']);

        }
        $this -> ajaxReturn($data);
    }

    //zongheczz
    public function yongjin(){
        $tuijianjiang  = M('feetuijian') -> find(1);
        $zong = $tuijianjiang['one'] + $tuijianjiang['two'];
        $one = $tuijianjiang['one']/$zong;
        $two = $tuijianjiang['two']/$zong;
        $this -> assign('one',$one);
        $this -> assign('two',$two);
        $this -> display();
    }

    public function yongjinbb(){
        $page = $_GET['p'];
        $start = ($page -1) * 12;
        $type = I('post.type');
        $data = M('yongjin')
            -> alias('a')
            -> field('a.time,a.money,b.name,a.time1')
            -> join("left join __USER__ as b on a.xj_userid = b.user_id")
            -> where("a.user_id = {$this->user_id} and a.type = {$type}")
            -> limit($start,12)
            -> order('a.time desc')
            -> select();
        foreach ($data as $k => $v){
            $data[$k]['time'] = date('m-d H:i:s',$v['time']);
            $data[$k]['time1'] = date('m-d H:i:s',$v['time1']);
        }
        $this -> ajaxReturn($data);
    }

    /**
     * 设置关于我们
     */
    public function guanyu(){
        //生成二维码
        //得到商户id
        $agent_id = M('user') -> getFieldByUserId($this->user_id,'agent_id');
        if (! is_dir ( 'Public/shanghu/' )) {
            mkdir ( 'Public/shanghu/' );
        }
        $ewm=A("Wxapi/Weixin");
        if (! file_exists ( "Public/shanghu/$agent_id.jpg" )) {
            $ewm->get_qr_limit($agent_id);
        }
        $this -> assign("agent_id",$agent_id);
        $this -> display();
    }

    public function myYeji(){
        $name = M('user') -> field('name') -> where(['user_id' => $this -> user_id]) -> find();
        $zonge = M('zonge') -> field('number') -> where(['user_id' => $this -> user_id]) -> find();
        $today = $this -> getMyYeji($this -> user_id);
        $this -> assign('zonge',$zonge['number']);
        $this -> assign('today',$today);
        $this -> assign('name',$name['name']);
        $this -> display();
    }

    private function getMyYeji($user_id){
        $zonge = 0;
        if(!$user_id){return 0;}
        //计算自己的
        $time = strtotime('today');
        $money = M('shop_order_detail')
            -> alias('a')
            -> field("sum(a.good_price) as money")
            -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
            -> where(" b.new = 1 and a.state = 1 and a.user_id = {$user_id} and time > {$time}")
            ->select();
        $zonge += $money[0]['money'];
        $res = M('user') -> field('user_id,sj_userid') -> where("sj_userid = {$user_id}") -> select();
        if(!$res){
            return $zonge;
        }

        foreach ($res as $k => $v){
            $yj = $this -> getMyYeji($v['user_id']);
            $zonge += $yj;

        }
        return $zonge;
    }

}