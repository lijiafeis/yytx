<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller{

    // /*用户来，判断session存不存在，*/
    function __construct(){
        parent::__construct();
//        session(null);
        $this->user_id = session('xigua_user_id');

        $res = $this->is_weixin();
       // dump($res);return;
        if ($res == 1){
            //$info = M('zhuce')->where("user_id = '$this->user_id'")->find();
            if (!$this->user_id){
                redirect(U('/Login/User/index',array('agent_id'=> $_GET['agent_id'])));
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
                $this -> assign('now_url',$now_url) ;
                $this->display('Login_error');exit;
            }else{
                $this -> user_id = session('xigua_user_id');
            }
        }
//        $agent_id = M('user') -> getFieldByUserId($this->user_id,'agent_id');
//        $this -> token = M('agent_token') -> getFieldByAgentId($agent_id,'agent_token');
//        $this -> assign("token",$this->token);
    }

    //判断打开方式
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return 1;
        }
        return 2;
    }

	public function index(){
        $data = M('shop_goods')
            -> alias('a')
            -> field('a.*,b.pic_url')
            -> join("left join __GOOD_PIC__ as b on a.good_id = b.good_id")
            -> group('b.good_id')
            -> where(['a.new' => 1])
            -> order('a.good_id desc')
            -> select();
        $this -> assign('data',$data);
        $new_name = F('list_name','',DATA_ROOT);
        if(!$new_name){
            $data1 = M('user')
                -> alias('a')
                -> field('a.name')
                -> join("left join __SHOP_ORDER__ as b on a.user_id = b.user_id")
                -> where(['b.state' => 1])
                -> select();
            $new_name1 = array();
            foreach ($data1 as $k => $v){
                $new_name1[] = $v['name'];
            }
            F('list_name',$new_name1,DATA_ROOT);
        }else{
            for ($i = 1; $i < 100;$i++){
                $number = mt_rand(1,500);
                $new_name1[] = $new_name[$number];
            }
        }

        $this -> assign('new_name',$new_name1);
        $this -> assign('data',$data);
        $this->display();
	}
	public function setSj(){
		$this -> display('setSjUserId');
	}

	public function setSjUserId(){
        $sj_user = $_GET['sj_userid'];
		$sj_user = $sj_user - 10000;
		$id = M('users') -> where(['user_id' => $sj_user]) -> select();
		if($id){
			$res = M('users') -> where(['user_id' => $this->user_id]) -> setField('sj_userid',$sj_user);
		}else{
			$this -> error('输入的上级不存在','setSj');
			exit;
		}
        
//        dump($res);
//        exit;
        //创建用户
        //有上级，创建当前用户信息。
		$is_true = M('user') -> where(['user_id' => $this -> user_id]) -> select();
		if($is_true){
			
		}else{
			$data['user_id'] = $this->user_id;
			$data['sj_userid'] = $sj_user;
			$data['time'] = time();
			M('user') -> add($data);
		}
        if($res){
            $this -> redirect('index');
        }
	}

	//特价
    public function tejia(){
//        $info = M('shop_goods')
//            -> alias('a')
//            -> field('a.*,b.pic_url')
//            -> join("left join __GOOD_PIC__ as b on a.good_id = b.good_id")
//            -> where(['a.best' => 1])
//            -> select();
//        $this -> assign('data',$info);
//        $this -> display('Shop_cateDetails');
        $info = M('shop_goods')
            -> alias('a')
            -> field('a.*,b.pic_url')
            -> join("left join __GOOD_PIC__ as b on a.good_id = b.good_id")
            -> where(['a.best' => 1])
            -> select();
        $is_true = M('user') -> getFieldByUserId($this->user_id,'is_true');
        $this -> assign('data',$info);
        $this -> assign('is_true',$is_true);
//        $this -> assign('cate_id',1);
        $this -> assign('type',1);
        $this -> display('cateDetails');
    }

    //新品
    public function xinpin(){
//        $info = M('shop_goods')
//            -> alias('a')
//            -> field('a.*,b.pic_url')
//            -> join("left join __GOOD_PIC__ as b on a.good_id = b.good_id")
//            -> where(['a.new' => 1])
//            -> select();
//        $this -> assign('data',$info);
//        $this -> display('Shop_cateDetails');

        $info = M('shop_goods')
            -> alias('a')
            -> field('a.*,b.pic_url')
            -> join("left join __GOOD_PIC__ as b on a.good_id = b.good_id")
            -> where(['a.new' => 1])
            -> select();
        $is_true = M('user') -> getFieldByUserId($this->user_id,'is_true');
        $this -> assign('data',$info);
//        dump($info);
//        exit;
        $this -> assign('is_true',$is_true);
//        $this -> assign('cate_id',1);
        $this -> assign('type',2);
        $this -> display('cateDetails');
    }
	
	
}
