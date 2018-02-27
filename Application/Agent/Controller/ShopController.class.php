<?php
namespace Agent\Controller;
use Think\Controller;

Class ShopController extends ActionController{
	function __construct(){
		parent::__construct();
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'),5);
		}
		$this -> id = session('admin_id');
		if(!$this -> id){
		    $this -> redirect('User/Index');
        }
	}


	function order_excel(){
		if(I() == null){exit;}
		$time1 = strtotime(I('start'));
		$time2 = strtotime(I('end'));
		$shop_order = M('shop_order');
		$user_address = M('user_addresss');
		$users = M('user');
		$shop_order_detail = M('shop_order_detail');
		$order_info = $shop_order ->where(" state = 1 and time >= '$time1' and time <= '$time2'  ")->order("id desc") -> select();
		
		foreach($order_info as $k=>$v){
			$order_info[$k]['time'] = date("Y-m-d H:i",$v['time']);
			$order_info[$k]['nickname'] = $users -> getFieldByUser_id($v['user_id'],'name');
			//$order_info[$k]['address'] = $user_address -> getByUser_id($v['user_id']);
			$order_info[$k]['address'] = $user_address -> getByOrderId($v['id']);
			$detail = $shop_order_detail -> where(['order_id' => $v['id']]) -> select();
			foreach($detail as $vv){
				$order_info[$k]['pay_more'] .='【'.$vv['good_id'].'】'.$vv['good_name'].  ' 规格: ' .$vv['type'] . " 数量：" . $vv['good_num'];
			}
//
			if($v['is_true'] == 1){
				$order_info[$k]['is_true'] = "已付款";
			}else{
				$order_info[$k]['is_true'] = "未付款";
			}
		}
		//dump($order_info);exit;
		//dump($order_info);exit;
		ob_end_clean();
		$excel = A('Admin/Excel');
		$excel ->index($order_info,'平台');
	}
	
	function order(){
		$p = isset($_GET['p'])?$_GET['p']:1; 
		$this -> assign('p',$p);
		$this->display();
	}
	function order_ajax(){
		$shop_order = M('shop_order');
		$user_address = M('user_addresss');
		$shname = $_GET['shname'];

		if(!$shname){
			if($_GET['is_true'] != null){
				$where = array('state'=>$_GET['is_true']);
			}elseif($_GET['state'] != null){
				$where = array('type'=>$_GET['state'],'state' => 1);
			}elseif($_GET['start'] > 0){
//				$where[]['time']  = array('egt',$_GET['start']);
//				$where[]['time']  = array('elt',$_GET['end']);
                $where['time'] = ['between',[$_GET['start'],$_GET['end']]];
			}else{
				$where =array();
			}
            $where['agent_id'] = $this->id;
			$pagecount = 10;
			$count = $shop_order ->where($where)-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			$order_info=$shop_order->where($where)->order("time desc")
                ->limit($Page->firstRow.','.$Page->listRows)->select();
			$Page->setConfig('first','首页');
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('last','尾页');
			$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
			foreach($order_info as $k=>$v){
				//$order_info[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
				$order_info[$k]['address'] = $user_address -> getByOrderId($v['id']);
			}
			// 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show();
			$p = $_GET['p'];
			$this->assign('page',$show);
			$this->assign('p',$p);
			$this -> assign("order_info",$order_info);
			$this->display();
		}else{
			
			$shname = $_GET['shname'];
			$pagecount = 10;
			$count = $shop_order
				-> alias('a')
				-> join('left join __USER_ADDRESS__ as b on a.address_id = b.address_id')
				->where(['b.username'=> $shname,'agent_id' => $this->id ])
				-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			unset($where['b.username']);
			$order_info=$shop_order
			-> alias("a")
				//->where($where)
				->order("time desc")
				->join("left join __USER_ADDRESS__ as b on a.address_id = b.address_id")
				-> where(['b.username' => $shname,'agent_id' => $this->id])
				->select();
			$Page->setConfig('first','首页');
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('last','尾页');
			$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
			foreach($order_info as $k=>$v){
				
				$address = $user_address -> getByOrderId($v['id']);
				if($address['username'] != $shname){
					unset($order_info[$k]);
					continue;
				}
				$order_info[$k]['address'] = $address;
			}
			// 实例化分页类 传入总记录数和每页显示的记录数(25)
			//dump($order_info);
			$show = $Page->show();
			$p = $_GET['p'];
			$this->assign('page',$show);
			$this->assign('p',$p);
			$this -> assign("order_info",$order_info);
			$this->display();
		}
		
	}

	public function orderConfirm(){
	    $order_id = $_POST['order_id'];
        $model = A('Pay/Notify');
        $model -> shop1($order_id,1);
        echo 0;
    }



	function order_more(){
		$page = $_GET['page'];
		$pay_id = $_GET['pay_id'];
		//订单详情
		$order = M('shop_order') -> getById($pay_id);

		$user_address = M('user_addresss') -> getByOrder_id($pay_id);
		$order_info = M('shop_order_detail') -> where(" order_id = '$pay_id' ") -> select();
		foreach($order_info as $k=>$v){
			$order_info[$k]['good_fee'] = $v['good_price']*$v['good_num'];
			$order_id = $v['order_id'];
		}


		//$this->assign("hexiao_info",$hexiao_info);
		$this->assign("order",$order);
		$this->assign("p",$page);
		$this->assign("user_address",$user_address);
		$this->assign("order_info",$order_info);
		$this -> display();
	}

	//商品发货
    public function fahuo(){
	    $data['order_sn'] = $_POST['order_sn'];
	    $data['kd_number'] = $_POST['kd_number'];
	    $data['type'] = 1;
	    $res = M('shop_order') -> where(['id' => $_POST['order_id']]) -> save($data);
	    if($res){
	        //发送模板消息
            $this -> sendTemplate($_POST['order_id']);
	        echo 0;
        }else{
	        echo -1;
        }
    }
	
	public function fahuoxiugai(){
		$data['order_sn'] = $_POST['order_sn'];
	    $data['kd_number'] = $_POST['kd_number'];
	    $data['type'] = 1;
	    $res = M('shop_order') -> where(['id' => $_POST['order_id']]) -> save($data);
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
        $order_sn = M('shop_order') -> getFieldById($order_id,'order_sn');
        $address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        $address = M('user_addresss') -> getFieldByOrderId($order_id,'address');
        //获取openid
        $user_id = M('shop_order') -> getFieldById($order_id,'user_id');
        $openid = M('users') -> getFieldByUser_id($user_id,'openid');

        //获取收货信息
//        $address_id = M('shop_order') -> getFieldById($order_id,'address_id');
        $address_info = M('user_addresss') -> getByOrderId($order_id);
        //获取快递名
        $kd_number = M('shop_order') -> getFieldById($order_id,'kd_number');

        $kd_name = '';
        switch ($kd_number){
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
//        file_put_contents('abc.txt',$order_id);
        $template = A("Pay/Template");
        $url = 'http://'.$_SERVER['SERVER_NAME'].U('/Home/Order/getWuliu')."?order_id=$order_id";
		//$kd_name,$order_sn,$name,$tel,$address,$openid,$url,$address
        $template->send_shop($kd_name,$order_sn,$address_info['username'],$address_info['telphone'],$address_info['address'],$openid,$url,$address);
    }

    //显示物流信息
    public function showWuliu(){
        $order_id = $_GET['order_id'];
        $res = M('shop_order') -> find($order_id);
        $kd = A("Wxapi/Kuaidi");
        $data = $kd -> getMessage($res['kd_number'],$res['order_sn']);
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
}