<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/11 0011
 * Time: 13:38
 */
namespace Home\Controller;
use Think\Controller;
use Think\Exception;

class GameController extends Controller{

    /*用户来，判断session存不存在，*/
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

    public function game(){
        if($this -> user_id != 418 && $this -> user_id != 5  && $this -> user_id != 6 && $this -> user_id != 121){
            echo '暂未开启';exit;
        }
        $info = M('goods')
            -> field('id,good_name,good_pic,good_price,game_pic')
            -> where(['is_game' => 1])
            -> order("code asc")
            -> select();
        foreach ($info as $k => $v){
            $pic = explode(',',$v['good_pic']);
            foreach($pic as $k1 => $v1){
                if($v1){
                    if(!$v['game_pic']){
                        $info[$k]['game_pic'] = $v1;
                    }
                    $info[$k]['pic'] = $v1;break;
                }
            }
        }
        $this -> assign('info',$info);
        $this -> display();
    }


    /**
     * 用户点击开始游戏的时候,ajax过来判断是否玩过有，如果没有玩过，根据后台设置的输赢比例生成结果，并保存记录
     * return code = -1 错误 1 开奖成功或失败 2 已经玩过，没有升级成功，== 3 升级成功跳出来让用户选择提货或退款
     * user_select 0 用户选择的是偶  1 鸡
     */
    public function setGameState(){
        $order_id = I('post.order_id','','htmlspecialchars')*1;
        $user_select = I('post.user_select','','htmlspecialchars')*1;
        if(!$order_id){
            $return['code'] = -1;
            $return['info'] = '缺少参数';
            $this -> ajaxReturn($return);exit;
        }
        $info = M('goods_order') -> field('price,money,fxmoney,game_price,good_num,state,type,is_game,game_type,user_id,game_state') -> where(['id' => $order_id]) -> find();
        if(!$info || $info['state'] != 1 || $info['is_game'] != 1 || $info['type'] != 0 || $info['user_id'] != $this -> user_id){
            $return['code'] = -1;
            $return['info'] = '订单状态错误';
            $this -> ajaxReturn($return);exit;
        }
        //判断是否玩过
        if($info['game_type'] != 0){
            if($info['game_type'] == 1){
                $return['info'] = '当前订单已经提货';
            }else if($info['game_type'] == 2){
                $return['info'] = '当前订单已经退款';
            }
            $return['code'] = -1;
            $this -> ajaxReturn($return);exit;
        }
        if($info['game_state'] != 0){
            if($info['game_state'] == 1){
                $return['info'] = '当前订单已经玩过,很遗憾没有升级成功';
                $return['code'] = 2;
            }else if($info['game_state'] == 2){
                $return['info'] = '当前订单已经玩过,升级成功';
                $return['code'] = 3;
            }

            $this -> ajaxReturn($return);exit;
        }

        //用户没有玩过，根据后台设置的输赢比例在F方法的gameScale中，生成结果，并记录到表中
        $gameScale = F('gameScale.php','',DATA_ROOT);
        $gameScale = json_decode($gameScale,true);
        if(!$gameScale || !$gameScale['win'] || !$gameScale['fail']){
            $return['code'] = -1;
            $return['info'] = '数据错误,请稍后重试';
            $this -> ajaxReturn($return);exit;
        }
        $win = 1000 * $gameScale['win'];
        $number = mt_rand(0,10000);
        if($number <= $win && $number > 0){
            $this -> setUserOrderState(1,$order_id,$user_select,$info);
        }else if($number > $win && $number < 10000){
            $this -> setUserOrderState(0,$order_id,$user_select,$info);
        }
    }

    /**
     * @param $type 1 表示用户猜对了，0表示用户猜错了
     * @param $order_id 订单id
     * @param $user_select 用户选择的奇偶 0 偶 1 鸡
     * @param $info 用户的订单信息
     * return code 1 type 1 中奖 0 没有中 numberList 返回去的数字
     */
    private function setUserOrderState($type,$order_id,$user_select,$info){
        $data['create_time'] = time();
        $data['user_id'] = $info['user_id'];
        $data['order_id'] = $order_id;
        $data['type'] = $type;
        $data['user_select'] = $user_select;
        $model = M();
        $model -> startTrans();
        for ($i = 0; $i < 10; $i++){
            $numberList[] = mt_rand(10000000,99999999);
        }
        try{
            if(($type == 1 && $user_select == 0) || ($type == 0 && $user_select == 1)){
                //开出来一个偶数
                $ouArr = array(0,2,4,6,8);
                $game_number = intval(mt_rand(1000000,9999999) . $ouArr[array_rand($ouArr,1)]);
                $numberList[] = $game_number;
            }else if(($type == 1 && $user_select == 1) || ($type == 0 && $user_select == 0)){
                //开出来一个奇数
                $jiArr = array(1,3,5,7,9);
                $game_number = intval(mt_rand(1000000,9999999) . $jiArr[array_rand($jiArr,1)]);
                $numberList[] = $game_number;
            }

            $data['game_number'] = $game_number;
            M('game_record') -> add($data);
            //根据输赢改变订单的状态
            $orderInfo = array();
            $game_price = $info['price'] + $info['fxmoney'] + $info['money'];
            $orderInfo['game_price'] = $game_price;
            if($type == 1){
                //赢了，改变订单的状态，添加游戏订单的金额
                $orderInfo['game_price'] = $game_price * 2;
                $orderInfo['good_num'] = $info['good_num'] * 2;
                $orderInfo['game_state'] = 2;
            }else if($type == 0){
                //输了，自动提货
                $orderInfo['game_type'] = 1;
                $orderInfo['game_state'] = 1;
            }
            $res = M('goods_order') -> where(['id' => $order_id]) -> save($orderInfo);
            if($res){
                $model -> commit();
                $return['code'] = 1;
                $return['type'] = $type;
                $return['numberList'] = $numberList;
                $this -> ajaxReturn($return);exit;
            }
        }catch (Exception $e){
            $model -> rollback();
            $return['code'] = -1;
            $return['info'] = '网络错误,请稍后重试';
            $this -> ajaxReturn($return);exit;
        }

    }

    /**
     * 如果用户升级成功，可以让用户选择是否提货，和是否退款，如果退款，收取手续费
     * order_id 当前的订单号
     * user_select 用户选择的选项，用户可以选择提货（1）和退款（2）
     */
    public function userSelect(){
        $order_id = I('post.order_id','','htmlspecialchars')*1;
        $user_select = I('post.user_select','','htmlspecialchars')*1;
        if(!$order_id || !$user_select){
            $return['code'] = -1;
            $return['info'] = '数据错误';
            $this -> ajaxReturn($return);exit;
        }
        $info = M('goods_order') -> field('user_id,game_price,price,state,type,is_game,game_type,game_state') -> where(['id' => $order_id]) -> find();
        if(!$info || $info['state'] != 1 || $info['type'] != 0 || $info['is_game'] != 1 || $info['game_type'] != 0 || $info['game_state'] != 2 || $info['user_id'] != $this->user_id){
            $return['code'] = -1;
            $return['info'] = '数据错误';
            $this -> ajaxReturn($return);exit;
        }
        //根据用户的选择改变订单状态
        if($user_select == 1){
            //用户想提货
            $res = M('goods_order') -> where(['id' => $order_id]) -> setField('game_type',1);
            $return['info'] = '提货成功';
        }else if($user_select == 2){
            //用户想退款,game_type = 2,把钱退给用户，并记录
            $scale = F('gameScale.php','',DATA_ROOT);
            $scale = json_decode($scale,true);
            if(!$scale || !$scale['refund']){
                $return['code'] = -1;
                $return['info'] = '数据错误,请稍后重试';
                $this -> ajaxReturn($return);exit;
            }
            $money = $info['game_price'] * (1 - $scale['refund']);
            $data['user_id'] = $info['user_id'];
            $data['money'] = $money;
            $data['order_id'] = $order_id;
            $data['price'] = $info['game_price'];
            $data['scale'] = 1 - $scale['refund'];
            $data['create_time'] = time();
            $model = M();
            $model -> startTrans();
            try{
                M('game_refund') -> add($data);
                M('user') -> where(['user_id' => $info['user_id']]) -> setInc('money',$money);
                $res = M('goods_order') -> where(['id' => $order_id]) -> setField('game_type',2);
                $return['info'] = '退款成功,请到账户余额查看';
                $model -> commit();
            }catch (Exception $e){
                $model -> rollback();
                $return['code'] = -1;
                $return['info'] = '数据错误,请稍后重试';
                $this -> ajaxReturn($return);exit;
            }

        }
        if($res){
            $return['code'] = 1;
            $this -> ajaxReturn($return);exit;
        }
        $return['code'] = -1;
        $return['info'] = '网络错误';
        $this -> ajaxReturn($return);exit;
    }


}