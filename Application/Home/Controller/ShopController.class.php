<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9 0009
 * Time: 09:40
 */
namespace Home\Controller;
use Think\Controller;

class ShopController extends Controller{

    // /*用户来，判断session存不存在，*/
    function __construct(){
        parent::__construct();
        $this->user_id = session('xigua_user_id');
        $res = $this->is_weixin();
        if ($res == 1){
            //$info = M('zhuce')->where("user_id = '$this->user_id'")->find();
            if (!$this->user_id){
                redirect(U('/Login/User/index'));
            }else {
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
        $this -> display();
    }

    //商品的详情页
    public function goodXiangqing(){
        if(!$_GET['good_id']){
            echo '数据错误';exit;
        }else{
            $good_id = $_GET['good_id']*1;
        }
        $data = M('shop_goods')
            -> alias('a')
            -> field('a.*')
            -> where(['a.good_id' => $good_id])
            -> find();
        //获取头像图片
        $pic = M('good_pic') -> field('pic_url') -> where(['good_id' => $data['good_id']]) -> select();

        $this -> assign('data',$data);
        $this -> assign('pic',$pic);
        $this -> display();
    }

}