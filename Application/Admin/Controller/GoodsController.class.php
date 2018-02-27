<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * Class GoodsController
 * @package Admin\Controller
 * 后台新商城的控制器
 */

Class GoodsController extends ActionController{
	function __construct(){
		parent::__construct();
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'),5);
		}
	}



	function good(){
		$this->display();
	}
	function good_ajax(){
		$p = isset($_GET['p'])?$_GET['p'] : 1;
		session('p',$p);
		$shop_goods = M('goods');
		$pagecount = 10;
		$count = $shop_goods -> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$good_list=$shop_goods->limit($Page->firstRow.','.$Page->listRows)
            ->order("code asc")
            ->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		foreach ($good_list as $k => $v){
		    //把规格格式化一下
            $data = json_decode($v['good_spec'],true);
            $str = '';
            foreach ($data as $k1 => $v1){
                if($v1){
                    $str .= "" . $v1['spec'] . ';价格:' . $v1['price'] . ';';
                }
            }
            $good_list[$k]['good_spec1'] = $str;
        }
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign("good_list",$good_list);
		$this->assign("p",$_GET['p']);
		$this->display();
	}
	function delgood(){
		$good_id = $_POST['id'];$arr = array();
		$res = M('goods') -> where(['id' => $good_id*1]) -> delete();
		if($res){$arr['success'] = 1;}else{$arr['success'] = 0;}
		echo json_encode($arr);
	}

	
	
	function goodadd(){
		if($_POST){
			$shop_goods = M('goods');
			if(!$_POST['good_name'] || !$_POST['spec1'] || !$_POST['price1']){
                $this->error("缺少参数",good);exit;
            }
			$data['good_name'] = I('good_name','','htmlspecialchars');
			$data['good_price'] = I('price1','','htmlspecialchars');
			$data['good_desc'] = $_POST['good_desc'];
			$data['game_pic'] = $_POST['pica1'];
			$data['time'] = time();
			$data['code'] = I('code1','','htmlspecialchars');;
            $data['good_pic'] = $_POST['pic1'];
            for ($i = 2; $i < 9; $i++){
                if($_POST['pic' . $i]){
                    $data['good_pic'] .= ',' . $_POST['pic' . $i];
                }            }
            $spec[] = [
                'spec' => $_POST['spec1'],
                'price' => I('price1','','htmlspecialchars'),
            ];
            $spec[] = [
                'spec' => $_POST['spec2'],
                'price' => I('price2','','htmlspecialchars'),
            ];
            $data['good_spec'] = json_encode($spec);
            $res = $shop_goods -> add($data);
            if($res){
                $this->success("添加商品成功",good);exit;
            }else{
                $this->error("添加商品失败",good);exit;
            }

		}
		$this->display();
	}
	
	function goodedit(){
		//if(!$_GET['good_id']){exit;}
		$shop_goods = M('goods');$good_id =I('id','','htmlspecialchars') * 1;
		if($_POST && $_POST['id']){
            $shop_goods = M('goods');
            if(!$_POST['good_name'] || !$_POST['spec1'] || !$_POST['price1']){
                $this->error("缺少参数",good);exit;
            }
            $data['good_name'] = I('good_name','','htmlspecialchars');
            $data['good_price'] = I('price1','','htmlspecialchars');
            $data['good_desc'] = $_POST['good_desc'];
            $data['code'] = (int)I('code1','','htmlspecialchars');
            if($_POST['pica1']){
                $data['game_pic'] = $_POST['pica1'];
            }
            $data['time'] = time();
            $good_pic = $shop_goods -> getFieldById($good_id,'good_pic');
            if($good_pic){
                $data['good_pic'] = $good_pic . ',' . $_POST['pic1'];
            }else{
                $data['good_pic'] = $_POST['pic1'];
            }

            for ($i = 2; $i < 9; $i++){
                if($_POST['pic' . $i]){
                    $data['good_pic'] .= ',' . $_POST['pic' . $i];
                }            }
            $spec[] = [
                'spec' => $_POST['spec1'],
                'price' => I('price1','','htmlspecialchars'),
            ];
            $spec[] = [
                'spec' => $_POST['spec2'],
                'price' => I('price2','','htmlspecialchars'),
            ];
            $data['good_spec'] = json_encode($spec);
            //dump($data);exit;
            $res = $shop_goods -> where(['id' => $good_id]) ->  save($data);
            if($res){
                $this->success("添加商品成功",good);exit;
            }else{
                $this->error("添加商品失败",good);exit;
            }
			$this->success("已更新商品信息",good);exit;
		}
		$good_id = $_GET['good_id'];
		$good_info = $shop_goods -> getById($good_id);
		$good_info['spec'] = json_decode($good_info['good_spec'],true);
		$bannar = explode(',',$good_info['good_pic']);
		$this -> assign('bannar',$bannar);
		$this->assign("good_info",$good_info);
		$this -> display();
	}

	function del_good_pic(){
		$good_pic = M('goods');
		$good_id = I('good_id','','htmlspecialchars');
		$index = I('index','','htmlspecialchars');
		$index -= 1;
        if(!$good_id){exit;}
		$pic = $good_pic -> getFieldById($good_id,'good_pic');
        $pic = explode(',',$pic);
        unset($pic[$index]);
        $pic = implode(',',$pic);
        $res = $good_pic -> where(['id' => $good_id]) -> setField('good_pic',$pic);
        if($res){
            $arr = array();echo json_encode($arr);
        }
	}

    function changetype(){
        $good_id = $_POST['good_id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];
        if($type_id == 0){$set_id = 1;}
        if($type_id == 1){$set_id = 0;}
        $res = M('goods') -> where(['id' => $good_id]) -> setField($type,$set_id);
        if($res){
            $arr['success'] = 1;
            $arr['type'] = $set_id;
        }else{
            $arr['success'] = 0;
            $arr['info'] = $type_id;
        }
        echo json_encode($arr);
    }
	
	function order(){
        $p = isset($_GET['p'])?$_GET['p']:1;
        $this -> assign('p',$p);
		$this->display();
	}
	function order_ajax(){
		$shop_order = M('goods_order');
		$user_address = M('user_addresss');
		$shname = $_GET['shname'];
        if($_GET['is_true'] >= 0){
            $where = array('state'=>$_GET['is_true']);
        }elseif($_GET['state'] >= 0){
            $where = array('type'=>$_GET['state'],'state' => 1);
        }elseif($_GET['start'] > 0){
            $where[]['pay_time']  = array('egt',$_GET['start']);
            $where[]['pay_time']  = array('elt',$_GET['end']);
        }elseif($shname){
            $where = array('name'=>$shname);
        }else{
            $where = array();
        }
        $where['is_game'] = 0;
        $pagecount = 10;
        $count = $shop_order ->where($where)-> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $order_info=$shop_order
            -> field("order_sn,pay_time,user_id,address,price,state,type,id")
            ->where($where)->order("pay_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $Page->show();
        $p = $_GET['p'];
        $this->assign('page',$show);
        $this->assign('p',$p);
        $this -> assign("order_info",$order_info);
        $this->display();
		
	}


    function order_excel(){
        if(I() == null){exit;}
        $time1 = strtotime(I('start'));
        $time2 = strtotime(I('end'));
        //从gameOrder中跳转过来，表示导出的是游戏的已提货未发货的订单
        $type = I('get.type');
        $shop_order = M('goods_order');
        $users = M('user');
        if($type == 1){
            $order_info = $shop_order ->where(" state = 1 and is_game = 1 and game_type = 1 and pay_time >= '$time1' and pay_time <= '$time2'  ")->order("id desc") -> select();
        }else{
            $order_info = $shop_order ->where(" state = 1 and is_game = 0 and pay_time >= '$time1' and pay_time <= '$time2'  ")->order("id desc") -> select();
        }
//        -> setCellValue('A'.$k,time())
//            -> setCellValue('B'.$k,$val['address']['username'])
//            -> setCellValue("C".$k,$val['address']['telphone'])
//            -> setCellValue("D".$k,'')
//            -> setCellValue("E".$k,$val['address']['city'] . $val['address']['address'])
//            -> setCellValue("F".$k,'【'.$val['pay_more'].'】'.$val['product'])
//            -> setCellValue("G".$k,$val['fh_name'])
//            -> setCellValue("H".$k,$val['fh_tel'])
//            -> setCellValue("I".$k,'')
//            -> setCellValue("J".$k,'');//填充数据
        //dump($order_info);exit;
        //dump($order_info);exit;
        ob_end_clean();
        $excel = A('Admin/Excel');
        $excel ->index_order($order_info,'平台');
    }

	function order_more(){
        $page = $_GET['page'];
		$pay_id = $_GET['pay_id']*1;
		//订单详情
		$order = M('goods_order') -> getById($pay_id);
		//$this->assign("hexiao_info",$hexiao_info);
		$this->assign("order",$order);
		$this->assign("p",$page);
		$this -> display();
	}

	//商品发货
    public function fahuo(){
	    $data['kd_number'] = $_POST['kd_number'];
        $kd_name = $_POST['kd_name'];
	    if($kd_name == -1){
            $kd_name = $_POST['sdwuliu'];
	        if($kd_name){
	            $data['kd_name'] = $kd_name;
            }
        }else{
            $data['kd_name'] = $kd_name;
        }
	    $data['type'] = 1;
	    $res = M('goods_order') -> where(['id' => $_POST['order_id']]) -> save($data);
	    if($res){
	        //发送模板消息
            $this -> sendTemplate($_POST['order_id']);
	        echo 0;
        }else{
	        echo -1;
        }
    }
	
	public function fahuoxiugai(){
		$data['kd_number'] = $_POST['kd_number'];
		$order_id = I('order_id');
        $kd_name = $_POST['kd_name'];
        if($kd_name == -1){
            $kd_name = $_POST['sdwuliu'];
            if($kd_name){
                $data['kd_name'] = $kd_name;
            }
        }else{
            $data['kd_name'] = $kd_name;
        }
	    $data['type'] = 1;
	    $res = M('goods_order') -> where(['id' => $order_id]) -> save($data);
	    if($res){
	        //发送模板消息
           // $this -> sendTemplate($_POST['order_id']);
	        echo 0;
        }else{
	        echo -1;
        }
	}
	
	
    public function sendTemplate($order_id){

        /* 发送模板消息通知 */
        //获取订单号
        $info = M('goods_order') -> field('order_sn,user_id,kd_name,address') -> where(['id' => $order_id]) -> find();
        $order_sn = $info['order_sn'];
        //获取openid
        $user_id = $info['user_id'];
        $openid = M('users') -> getFieldByUser_id($user_id,'openid');

        //获取收货信息
//        $address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        //获取快递名

        $kd_name = '';
        switch ($info['kd_name']){
            case 'SF':
                $kd_name = '顺丰快递';
                break;
            case 'STO':
                $kd_name = '申通快递';
                break;
            case 'YD':
                $kd_name = '韵达快递';
                break;
            case 'YTO':
                $kd_name = '圆通速递';
                break;
            case 'ZJS':
                $kd_name = '宅急送';
                break;
            case 'ZTO':
                $kd_name = '中通速递';
                break;
            case 'AMAZON':
                $kd_name = '亚马逊物流';
                break;
        }
        $addInfo = explode(',',$info['address']);
        $info['username'] = $addInfo[0];
        $info['telphone'] = $addInfo[1];
        $info['address1'] = $addInfo[2];
//        file_put_contents('abc.txt',$order_id);
        $template = A("Pay/Template");
        $url = 'http://'.$_SERVER['SERVER_NAME'].U('/Home/Order/getWuliu')."?order_id=$order_id";
		//$kd_name,$order_sn,$name,$tel,$address,$openid,$url,$address
        $template->send_shop($kd_name,$order_sn,$info['username'],$info['telphone'],$info['address1'],$openid,$url,$info['address']);
    }

    //显示物流信息
    public function showWuliu(){
        $order_id = $_GET['order_id'];
        $res = M('goods_order') -> find($order_id);
        $kd = A("Wxapi/Kuaidi");
        $data = $kd -> getMessage($res['kd_name'],$res['kd_number']);
        $data = json_decode($data);

        $arr = $this -> infoToArray($data);
//        dump($arr);

        $this -> assign('data',$arr[0]);
        $this -> assign('info',$arr[1]);
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

    function gameOrder(){
        $p = isset($_GET['p'])?$_GET['p']:1;
        $this -> assign('p',$p);
        $this->display();
    }

    /**
     * 当type为0的时候，就要看game_type的状态了，0未提货1提货2退款
     */
    function gameOrder_ajax(){
        $shop_order = M('goods_order');
        $shname = $_GET['shname'];
        if($_GET['state'] >= 0){
            $where = array('state'=>$_GET['state']);
        }elseif($_GET['type'] >= 0){
            if($_GET['type'] <= 2){
                $where = array('type'=>$_GET['type'],'state' => 1);
            }else{
                $where = array('game_type'=>$_GET['type']-3,'state' => 1,'type' => 0);
            }

        }elseif($_GET['start'] > 0){
            $where[]['pay_time']  = array('egt',$_GET['start']);
            $where[]['pay_time']  = array('elt',$_GET['end']);
        }elseif($shname){
            $where = array('name'=>$shname);
        }else{
            $where = array();
        }
        $where['is_game'] = 1;
        $pagecount = 10;
        $count = $shop_order ->where($where)-> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $order_info=$shop_order
            -> field("order_sn,pay_time,user_id,address,price,state,type,id,game_type,game_state")
            ->where($where)->order("pay_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $Page->show();
        $p = $_GET['p'];
        $this->assign('page',$show);
        $this->assign('p',$p);
        $this -> assign("order_info",$order_info);
        $this->display();
    }

    function gameOrder_more(){
        $page = $_GET['page'];
        $pay_id = $_GET['pay_id']*1;
        //订单详情
        $order = M('goods_order') -> getById($pay_id);
        //$this->assign("hexiao_info",$hexiao_info);
        $this->assign("order",$order);
        $this->assign("p",$page);
        $this -> display();
    }

    /**
     * 发货的异步，当订单已支付，并且没有发货，也就是state = 1 type = 0,
     * 同时游戏订单的game_type有三个状态，0 未提货，1 已提货，可以进行发货 2 已退款，也就是不能发货
     */
    public function gameFahuo(){
        $data['kd_number'] = $_POST['kd_number'];
        $kd_name = $_POST['kd_name'];
        if($kd_name == -1){
            $kd_name = $_POST['sdwuliu'];
            if($kd_name){
                $data['kd_name'] = $kd_name;
            }
        }else{
            $data['kd_name'] = $kd_name;
        }
        $info = M('goods_order') -> field('state,type,game_type') -> where(['id' => $_POST['order_id']*1]) -> find();
        if($info['state'] != 1 || $info['type'] != 0 || $info['game_type'] != 1){
            echo -1;exit;
        }
        $data['type'] = 1;
        $res = M('goods_order') -> where(['id' => $_POST['order_id']*1]) -> save($data);
        if($res){
            //发送模板消息
            $this -> sendTemplate($_POST['order_id']);
            echo 0;
        }else{
            echo -1;
        }
    }

    public function gameFahuoxiugai(){
        $data['kd_number'] = $_POST['kd_number'];
        $order_id = I('order_id');
        $kd_name = $_POST['kd_name'];
        if($kd_name == -1){
            $kd_name = $_POST['sdwuliu'];
            if($kd_name){
                $data['kd_name'] = $kd_name;
            }
        }else{
            $data['kd_name'] = $kd_name;
        }
        $data['type'] = 1;
        $res = M('goods_order') -> where(['id' => $order_id]) -> save($data);
        if($res){
            //发送模板消息
            // $this -> sendTemplate($_POST['order_id']);
            echo 0;
        }else{
            echo -1;
        }
    }

    //这是输赢比例和退款收取的手续费比例
    public function setGameScale(){
        if(IS_GET){
            $data = F('gameScale.php','',DATA_ROOT);
            $data = json_decode($data,true);
            $data['refund'] *= 100;
            $this -> assign('data',$data);
            $this -> display();
        }else{
            $data['win'] = I('post.win');
            $data['fail'] = I('post.fail');
            $data['refund'] = I('post.refund')/100;
            if($data['win'] + $data['fail'] != 10){
                $this -> error('输赢比例设置错误','setGameScale');
            }
            $data = json_encode($data);
            F('gameScale.php',$data,DATA_ROOT);
            $this -> success('设置成功','setGameScale');
        }
    }

    public function gameRecord(){
        $this -> display();
    }

    /**
     * 如果前台有赛选条件，订单姓名，状态（1 升级失败 2 升级成功） order_sn
     */
    public function gameRecord_ajax(){
        $name = I('get.name');
        $state = I('get.state');
        $order_sn = I('get.order_sn');
        $where = array();
        if($name){
            $where = array('b.name'=>$name);
        }
        if($state > 0){
            $where = array('a.type'=>$state - 1);
        }
        if($order_sn){
            $where = array('b.order_sn'=>$order_sn);

        }
        $pagecount = 10;
        $count = M('game_record')
            -> alias('a')
            -> join("left join __GOODS_ORDER__ as b on a.order_id = b.id")
            -> where($where)
            -> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $recordInfo = M('game_record')
            -> alias('a')
            -> field("a.*,b.name,b.order_sn")
            -> join("left join __GOODS_ORDER__ as b on a.order_id = b.id")
            -> where($where)
            ->order("id desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $Page->show();
        $p = $_GET['p'];
        $this->assign('page',$show);
        $this->assign('p',$p);
        $this -> assign("recordInfo",$recordInfo);
        $this->display();
    }

    public function gameUserRefund(){
        $this -> display();
    }

    public function gameUserRefund_ajax(){
        $name = I('get.name');
        $order_sn = trim(I('get.order_sn'));
        $where = array();
        if($name){
            $where = array('b.name'=>$name);
        }
        if($order_sn){
            $where = array("c.order_sn" => $order_sn);
        }
        $pagecount = 10;
        $count = M('game_refund')
            -> alias('a')
            -> join("left join __USER__ as b on a.user_id = b.user_id")
            -> join("left join __GOODS_ORDER__ as c on a.order_id = c.id")
            -> where($where)
            -> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $recordInfo = M('game_refund')
            -> alias('a')
            -> field("a.*,b.name,c.order_sn")
            -> join("left join __USER__ as b on a.user_id = b.user_id")
            -> join("left join __GOODS_ORDER__ as c on a.order_id = c.id")
            -> where($where)
            ->order("a.user_id desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $Page->show();
        $p = $_GET['p'];
        $this->assign('page',$show);
        $this->assign('p',$p);
        $this -> assign("refundInfo",$recordInfo);
        $this->display();
    }



    public function rand(){
        for ($i = 0; $i < 10; $i++){
            $numberList[] = mt_rand(10000000,99999999);
        }
        $numberList[] = intval(mt_rand(1000000,9999999) . 2);
        dump($numberList);

    }

}