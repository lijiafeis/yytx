<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11 0011
 * Time: 16:09
 */
namespace Home\Controller;
use Think\Controller;
use Think\Page;

class OrderController extends Controller{

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

    //用户待付款订单
    public function dfu(){
        $data = M('shop_order')
            -> alias("a")
            -> field('a.id,a.zonge,a.time,b.good_name,b.pic_url,sum(b.good_num) as num')
		    -> where("a.user_id = {$this->user_id} and a.state = 0")
            -> join("left join __SHOP_ORDER_DETAIL__ as b on a.id = b.order_id")
            -> group('a.id')
            -> order('a.time desc')
            -> select();
//		dump($data);exit;
        $this -> assign('data',$data);
        $this -> display();
    }
	
	//删除订单
	public function delOrder(){
		$order_id = $_POST['order_id'];
		if(!$order_id){
		    echo -2;exit;
        }
        //查看当前订单是否复消积分，有的话返还给用户
        $fxmoney = M('shop_order') -> getFieldById($order_id,'fxmoney');
		if($fxmoney){
		    M('user') -> where("user_id = {$this->user_id}") -> setInc('fxmoney',$fxmoney);
        }
		$res = M('shop_order') -> delete($order_id);
		M('shop_order_detail') -> where(['order_id' => $order_id]) -> delete();
		if($res){
			echo 0;
		}else{
			echo -1;
		}
	}

    /**
     * 用户的待发货订单
     */
	public function dfahuo(){
        $data = M('shop_order')
            -> alias("a")
            -> field('a.id,a.zonge,a.order_sn,a.time,b.good_name,b.pic_url,sum(b.good_num) as num')
            -> where("a.user_id = {$this->user_id} and a.state = 1 and a.type = 0")
            -> join("left join __SHOP_ORDER_DETAIL__ as b on a.id = b.order_id")
            -> group('a.id')
            -> order('a.time desc')
            -> select();
        $this -> assign('data',$data);
        $this -> display();
	}
	
	//代发货的订单详情
	public function orderXiangQing(){
		$order_id = $_GET['order_id'];
		$address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        $address = M('user_addresss') -> getByOrder_id($order_id);
        $data = M('shop_order_detail') -> where(['order_id' => $order_id]) -> select();
        $num = count($data);
        $info = M('shop_order') -> field('time,zonge,order_sn,fxmoney') -> find($order_id);
        $this -> assign('data',$data);
        $this -> assign('info',$info);
        $this -> assign('add',$address);
        $this -> display();
	}
	
	public function dsh(){
        $data = M('shop_order')
            -> alias("a")
            -> field('a.id,a.zonge,a.order_sn,a.time,b.good_name,b.pic_url,sum(b.good_num) as num')
            -> where("a.user_id = {$this->user_id} and a.state = 1 and a.type = 1")
            -> join("left join __SHOP_ORDER_DETAIL__ as b on a.id = b.order_id")
            -> group('a.id')
            -> order('a.time desc')
            -> select();
//        dump($data);
        $this -> assign('data',$data);
        $this -> display();
	}
	
	public function dshXiangQing(){
		$order_id = $_GET['order_id'];
		$address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        $address =  M('user_addresss') -> getByOrder_id($order_id);
        $data = M('shop_order_detail') -> where(['order_id' => $order_id]) -> select();
        $num = count($data);
        $info = M('shop_order') -> find($order_id);
        $this -> assign('data',$data);
        $this -> assign('info',$info);
        $this -> assign('add',$address);
        $this -> display();
	}
	
	public function qdsh(){
		$order_id = $_POST['order_id'];
		$res = M('shop_order') -> where(['id' => $order_id]) -> setField('type',2);
		if($res){
			echo 0;
			//收获成功吧商品的信息保存到评价表中，用于评论
            $data = M('shop_order_detail') -> where(['order_id' => $order_id]) -> select();
            foreach ($data as $k => $v){
                $a['good_id'] = $v['good_id'];
                $a['user_id'] = $this->user_id;
                $a['time'] = time();
                $a['order_id'] = $v['order_id'];
                M('pingjia') -> add($a);
            }
		}else{
			echo -1;
		}
	}

	//查看物流信息
    //通过订单号得到物流信息
    public function getWuliu(){
	    $order_id = $_GET['order_id'];
	    $order_sn = M('shop_order') -> getFieldById($order_id,'order_sn');
        $kd1 = M('shop_order') -> getFieldById($order_id,'kd_number');
        $kd = A("Wxapi/Kuaidi");
        $data = $kd -> getMessage($kd1,$order_sn);
		//dump($data);
        $data = json_decode($data);
        $arr = $this -> infoToArray($data);
        //dump($arr);
        //exit;
        if($arr[0]['kd']){
            $this -> assign('data',$arr[0]);
            $this -> assign('info',$arr[1]);
        }else{
            $sd['kd'] = $kd1;
            $sd['ddh'] = $order_sn;
            $this->assign('data',$sd);
        }

        $this -> display();
    }

    public function infoToArray($data){
        $arr = array();
        $arr['ddh'] = $data -> LogisticCode;
        $ress = array();
        switch ($data -> ShipperCode){
            case 'SF':
                $arr['kd'] = '顺丰快递';
                break;
            case 'STO':
                $arr['kd'] = '申通快递';
                break;
            case 'YD':
                $arr['kd'] = '韵达快递';
                break;
            case 'YTO':
                $arr['kd'] = '圆通速递';
                break;
            case 'ZJS':
                $arr['kd'] = '宅急送';
                break;
            case 'ZTO':
                $arr['kd'] = '中通速递';
                break;
            case 'AMAZON':
                $arr['kd'] = '亚马逊物流';
                break;
        }
        foreach ($data -> Traces as $k => $v){
            $info['time'] = $v -> AcceptTime;
            $info['info'] = $v -> AcceptStation;
            $ress[$k] = $info;
        }
        $re[0] = $arr;
        $re[1] = $ress;
        return $re;
    }


	//待评价的商品
	public function pingjia(){
		$data = M('pingjia')
			-> alias("a")
			-> field("a.*,b.*,b.type as btype,a.pj_id as aid")
			-> join("left join __SHOP_ORDER_DETAIL__ as b on a.order_id = b.order_id")
			-> where(['a.user_id' => $this->user_id,'a.type' => 0])
			-> select();
		$this -> assign('data',$data);
		$this -> display();
	}
	
	public function pingjia1(){
		$good_id = $_GET['good_id'];
		$pj_id = I('get.pj_id');
		$pic_url = M('good_pic') -> where(['good_id' => $good_id]) -> select();
		$this -> assign('pic_url',$pic_url[0]['pic_url']);
		$this -> assign('good_id',$good_id);
		$this -> assign('pj_id',$pj_id);
		$this -> display();
	}
	
	public function pj_add(){
        $pj_id = $_POST['pj_id'];
		$data['content'] = $_POST['content'];
		$data['type'] = 1;
		$type = M('pingjia') -> getFieldByPjId($pj_id,'type');
		if($type == 1){
		    echo 2;
        }else{
            $res = M('pingjia') -> where("pj_id = {$pj_id}")  -> save($data);
        }
		if($res){
			echo 1;
		}else{
			echo 2;
		}
	}

    //下级会员的订单信息
	public function team(){
	    $user_id = $_GET['user_id'];
	    if(!$user_id){
	        echo '缺少参数';exit;
        }
        $data = M('shop_order')
            -> alias("a")
            -> field('a.id,a.zonge,a.time,b.good_name,b.pic_url,sum(b.good_num) as num')
            -> where("a.user_id = {$user_id} and a.state = 0")
            -> join("left join __SHOP_ORDER_DETAIL__ as b on a.id = b.order_id")
            -> group('a.id')
            -> order('a.time desc')
            -> select();
        $this -> assign('data',$data);
        $this -> assign('user_id',$user_id);
	    $this -> display();
    }

    /**
     * 通过type得到订单状态，
     * 0 未付款
     * 1 未发货
     * 2 已收货
     * 3 未评价
     */
    public function getTeamOrder(){
	    $type = $_POST['type'];
	    $user_id = $_POST['user_id'];
	    if($type === ''){
	        echo -1;exit;
        }
        if(!$user_id){
            echo -1;exit;
        }
        $where['a.user_id'] = $user_id;
        switch ($type){
            case 0:
                $where['a.state'] = 0;
                break;
            case 1:
                $where['a.state'] = 1;
                $where['a.type'] = 0;
                break;
            case 2:
                $where['a.state'] = 1;
                $where['a.type'] = 1;
                break;
            case 3:
                $where['a.state'] = 1;
                $where['a.type'] = 2;
                break;
        }
        $data = M('shop_order')
            -> alias("a")
            -> field('a.id,a.zonge,a.time,a.order_sn,b.good_name,b.pic_url,sum(b.good_num) as num')
            -> where($where)
            -> join("left join __SHOP_ORDER_DETAIL__ as b on a.id = b.order_id")
            -> group('a.id')
            -> order('a.time desc')
            -> select();
        $this -> ajaxReturn($data);

    }

    public function teamXiangQing(){
        $order_id = $_GET['order_id'];
        $type = $_GET['type'];
        $address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        $address =  M('user_addresss') -> getByOrder_id($order_id);
        $data = M('shop_order_detail') -> where(['order_id' => $order_id]) -> select();
        $num = count($data);
//        dump($type);
        $info = M('shop_order') -> find($order_id);
        $this -> assign('data',$data);
        $this -> assign('info',$info);
        $this -> assign('add',$address);
        $this -> assign('type',$type);
        $this -> display();
    }

    /**
     * 已收货
     */
    public function ysh(){
        $data = M('shop_order')
            -> alias("a")
            -> field('a.id,a.zonge,a.order_sn,a.time,b.good_name,b.pic_url,sum(b.good_num) as num')
            -> where("a.user_id = {$this->user_id} and a.state = 1 and a.type = 2")
            -> join("left join __SHOP_ORDER_DETAIL__ as b on a.id = b.order_id")
            -> group('a.id')
            -> order('a.time desc')
            -> select();
//        dump($data);
        $this -> assign('data',$data);
        $this -> display();
    }

    /**
     * 已收货详情
     */
    public function yshXiangQing(){
        $order_id = $_GET['order_id'];
        $address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        $address =  M('user_addresss') -> getByOrder_id($order_id);
        $data = M('shop_order_detail') -> where(['order_id' => $order_id]) -> select();
        $num = count($data);
        $info = M('shop_order') -> find($order_id);
        $this -> assign('data',$data);
        $this -> assign('info',$info);
        $this -> assign('add',$address);
        $this -> display();
    }
    
}