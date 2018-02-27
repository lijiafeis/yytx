<?php
namespace Admin\Controller;
use Think\Controller;

Class ShopController extends ActionController{
	function __construct(){
		parent::__construct();
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'),5);
		}
	}

	//设置商城的首页的图片
	function setting(){

		$shop_ad = M('shop_ad');
		if($_POST['ad'] == 1) {
            // dump($_POST);exit;
            $data['pic_url'] = $_POST['pic11'];
            if ($_POST['id'] != null) {
                $id = $_POST['id'];
                $data['link'] = $_POST['link'];
                $shop_ad->where(" id = '$id' ")->save($data);
            } else {
                $data['link'] = $_POST['link'];
                $shop_ad->add($data);
            }
            $this->success("首页广告保存成功");

            exit;
        }
		$ad_info  = $shop_ad -> order("code desc") -> select();
		$this ->assign("empty",'<div class="text-center">暂无数据</div>');
		$this ->assign("ad",$ad_info);
		$this -> display();
	}

	//删除商城的首页的图片
	function del_shop_bannar(){
		if($_POST['type'] == 'ad'){$model = M('shop_ad');}
		$id = $_POST['id'];
		$model -> where(" id = '$id' ") -> delete();
		$arr = array();
		echo json_encode($arr);
	}

	function good(){

		$p = session('p');
		$p = $p == null ? 1 : $p;
		$this -> assign('p',$p);
		$this->display();
	}
	function good_ajax(){
		$p = isset($_GET['p'])?$_GET['p'] : 1;
		session('p',$p);
		$shop_goods = M('shop_goods');$shop_categrey = M('shop_categrey');
		$pagecount = 10;
		$count = $shop_goods ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$good_list=$shop_goods->where($where)->order("time desc")->limit($Page->firstRow.','.$Page->listRows)->order("good_id desc") ->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		
		// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();
		$this->assign('page',$show);
		foreach($good_list as $k=>$v){
			$good_list[$k]['cate_name'] = $shop_categrey -> getFieldByCate_id($v['cate_gid'],'cate_name');
			$gid_name = $shop_categrey -> getFieldByCate_id($v['cate_pid'],'cate_name');
			if($gid_name != null){
				$good_list[$k]['cate_name'] .= " -- ".$gid_name;
			}
			
		}
		$this->assign("good_list",$good_list);
		$this->assign("p",$_GET['p']);
		$this->display();
	}
	function delgood(){
		$good_id = $_POST['id'];$arr = array();
		$url_path = M('good_pic') -> getFieldByGoodId($good_id,'pic_url');
        $path = dirname(dirname(dirname(__DIR__)));
        $filepath = $path . $url_path;
        @unlink($filepath);
		$res = M('shop_goods') -> where(" good_id = '$good_id' ") -> delete();
		if($res){$arr['success'] = 1;}else{$arr['success'] = 0;}
		echo json_encode($arr);
	}
	function changetype(){
		$good_id = $_POST['good_id'];
		$type = $_POST['type'];
		$type_id = $_POST['type_id'];
		if($type_id == 0){$set_id = 1;}
		if($type_id == 1){$set_id = 0;}
		$res = M('shop_goods') -> where(" good_id = '$good_id' ") -> setField($type,$set_id);
		if($res){
			$arr['success'] = 1;
			$arr['type'] = $set_id;
		}else{
			$arr['success'] = 0;
			$arr['info'] = $type_id;
		}
		echo json_encode($arr);
	}
	
	public function setStyle($str){
		$a = explode('<img',$str);
		$abc = '';
		$num = count($a);
		if($num != 1){
			foreach ($a as $k => $v){
				if($k != $num-1){
					$abc = $abc . $v . '<img style="margin:0px;"';
				}else{
					$abc = $abc . $v;
				}
			}
		}
		
		return $abc;
	}
	
	
	function goodadd(){
		if($_POST){
			$_POST['good_desc'] = $this -> setStyle($_POST['good_desc']);
			$shop_goods = M('shop_goods');$good_pic = M('good_pic');
			if(!$_POST['cate_pid']){$_POST['cate_pid'] = 0;}
			$_POST['good_price'] = $_POST['good_chengben'];
			$good_id = $shop_goods -> add($_POST);
			if($_POST && $good_id){
				$arr = array_keys(I());
				foreach($arr as $val){
					if(strstr($val,'pic')){
						if(I($val)){
							$good_pic->add(array('good_id'=>$good_id,'pic_url'=>I($val)));
						}
					}
				}
			}
			$this->success("添加商品成功",good);exit;
		}
		$shop_categrey = M('shop_categrey');
		$categrey = $shop_categrey -> where(" pid = 0 ") -> select();
		$this->assign("categrey",$categrey);
		foreach($categrey as $k=>$v){
			$id = $v['cate_id'];
			$jscategrey[$id] = $shop_categrey -> where(" pid = $v[cate_id] ") -> select();
		}
		foreach($categrey as $k=>$v){
			$categrey[$k]['arr'] = $shop_categrey -> where(" pid = '$v[cate_id]' ") ->order("code desc") -> select();	
		}
		$shop_good_type = M('shop_good_type');
		$type_info = $shop_good_type ->order("type_id desc")-> select();
		$this->assign('type_info',$type_info);
		$this->assign("jscategrey",json_encode($jscategrey));
		$this->display();
	}
	
	function goodedit(){
		//if(!$_GET['good_id']){exit;}
		$shop_goods = M('shop_goods');$good_pic = M('good_pic');$good_id =$_POST['id'];
		if($_POST && $_POST['id']){
			
			$_POST['good_desc'] = $this->setStyle($_POST['good_desc']);
			//exit;
			//$_POST['good_desc'] = $this -> setStyle($_POST['good_desc']);
			$arr = array_keys(I());
			foreach($arr as $val){
				if(strstr($val,'pic')){
					if(I($val)){
						$good_pic->add(array('good_id'=>$good_id,'pic_url'=>I($val)));
					}
				}
			}
			if(!$_POST['cate_pid']){$_POST['cate_pid'] = 0;}
            $_POST['good_price'] = $_POST['good_chengben'];
			$shop_goods -> where(" good_id = '$good_id' ") -> save($_POST);
			$this->success("已更新商品信息",good);exit;
		}
		$good_id = $_GET['good_id'];
		$shop_categrey = M('shop_categrey');
		$categrey = $shop_categrey -> where(" pid = 0 ") -> select();
		$good_info = $shop_goods -> getByGood_id($good_id);
		$bannar = $good_pic -> where(" good_id = '$good_id' ") -> order("code desc") -> select();
		$this->assign("categrey",$categrey);
		$this->assign("good_info",$good_info);
		$this->assign("bannar",$bannar);
		foreach($categrey as $k=>$v){
			$id = $v['cate_id'];
			$jscategrey[$id] = $shop_categrey -> where(" pid = $v[cate_id] ") -> select();
		}
		foreach($categrey as $k=>$v){
			$categrey[$k]['arr'] = $shop_categrey -> where(" pid = '$v[cate_id]' ") ->order("code desc") -> select();	
		}
		/* 查询商品类型属性 */
		$shop_good_type = M('shop_good_type');
		$type_info = $shop_good_type ->order("type_id desc")-> select();
		$this->assign('type_info',$type_info);
		$this->assign("jscategrey",json_encode($jscategrey));
		$this -> display();
	}
	function del_good_pic(){
		$good_pic = M('good_pic');
		$id = $_POST['id'];
		$good_pic -> where(" id = '$id' ") -> delete();
		$arr = array();echo json_encode($arr);
		
	}
	function change_shop_pic(){
		$good_pic = M('good_pic');
		$id = $_POST['id'];
		$good_pic -> where(" id = '$id' ") -> setField("code",$_POST['code']);
		$arr = array();echo json_encode($arr);
		
	}
	
	
	function categrey(){

		//查询分类
		$categrey = M('shop_categrey');
		$pid_info = $categrey -> where(" pid = 0 ")->order("code desc") -> select();
		foreach($pid_info as $k=>$val){
			$pid = $val['cate_id'];
			$pid_info[$k]['children'] = $categrey -> where(" pid = '$pid' ")->order("code desc") -> select();
		}
		//dump($pid_info);
		$this -> assign("pid_info",$pid_info);
		$this->display();
	}

	function change_categrey_type(){
		$model = M('shop_categrey');
		$cate_id = $_POST['id'];$arr = array();
		switch($_POST['type']){
			case 'is_show':
			if($_POST['data'] == '1'){
				$model -> where(" cate_id = '$cate_id' ") -> setField("is_show",0);$arr['state'] = 0;
			}else{
				$model -> where(" cate_id = '$cate_id' ") -> setField("is_show",1);$arr['state'] = 1;
			}
			break;
			case 'hidden':
			if($_POST['data'] == '1'){
				$model -> where(" cate_id = '$cate_id' ") -> setField("hidden",0);$arr['state'] = 0;
			}else{
				$model -> where(" cate_id = '$cate_id' ") -> setField("hidden",1);$arr['state'] = 1;
			}
			break;
		}
		
		echo json_encode($arr);
		
	}

	function categreyadd(){
		$shop_categrey = M('shop_categrey');
		if($_POST){
			$data = $_POST;//dump($data);exit;
			//上传图片处理
			$upload = new \Think\Upload();// 实例化上传类  
			$upload->rootPath = './Uploads/';  
			$upload->maxSize   =     3145728 ;// 设置附件上传大小 
			$upload->autoSub = false;   
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
			$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
			$upload->autoSub = true;
			$upload->subName = date("Ymd");   
			$imginfo   =   $upload->upload();//dump($imginfo);exit;
			//dump($_POST);exit;
			if($imginfo){
				$data['pic_url'] = "Uploads/".$imginfo['image']['savepath'].$imginfo['image']['savename'];
			}
			if($_POST['type'] == null){
				$shop_categrey -> add($data);$this->success("新分类创建成功",categrey);exit;
			}else{
				$cate_id = $_POST['type'];
				$shop_categrey -> where(" cate_id = '$cate_id' ") -> save($data);
				$this->success("分类信息保存成功",categrey);exit;
			}
			
			
		}
		if($_GET['cate_id']){
			$cate_info =  $shop_categrey -> getByCate_id($_GET['cate_id']);
			$this -> assign("cate_info",$cate_info);
		}
		//顶级分类信息
		$cate_pid = $shop_categrey -> where(" pid = 0 ") -> select();
		$this -> assign("cate_pid",$cate_pid);
		$this ->display();
	}

	function del_shop_categrey(){
		$cate_id = $_POST['id'];
		M('shop_categrey') -> where(" cate_id = '$cate_id' ") -> delete();
		$arr = array();
		echo json_encode($arr);
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
				$where[]['time']  = array('egt',$_GET['start']);
				$where[]['time']  = array('elt',$_GET['end']);
			}else{
				$where =array();
			}
			$pagecount = 10;
			$count = $shop_order ->where($where)-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			$order_info=$shop_order->where($where)->order("time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
			$Page->setConfig('first','首页');
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('last','尾页');
			$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
			foreach($order_info as $k=>$v){
				//$order_info[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
				$order_info[$k]['address'] = $user_address -> getByOrderId($v['id']);
                $order_info[$k]['wxname'] = M('agent_info') -> getFieldById($v['agent_id'],'wxname');

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
				->where(['b.username'=> $shname])
				-> count();
			$Page = new \Think\Pageajax($count,$pagecount);
			unset($where['b.username']);
			$order_info=$shop_order
			-> alias("a")
				//->where($where)
				->order("time desc")
				->join("left join __USER_ADDRESS__ as b on a.address_id = b.address_id")
				-> where(['b.username' => $shname])
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
                $order_info[$k]['wxname'] = M('agent_info') -> getFieldById($v['agent_id'],'wxname');

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

        //判断当前订单是否使用了代金卷
        if($order['daijin_id'] != 0){
            $type = M('daijinjuan') -> getById($order['daijin_id']);
            if($type['type'] == 1){
                $this -> assign('info',$type);
                //$order['zonge'] = $order['zonge'] - $type['money'] <0 ? 0 :  $order['zonge'] - $type['money'];
                //$order['zonge'];
            }
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
	    $kd_number = $_POST['kd_number'];
	    if($kd_number == -1){
	        $kd_number = $_POST['sdwuliu'];
	        if($kd_number){
	            $data['kd_number'] = $kd_number;
            }
        }else{
            $data['kd_number'] = $kd_number;
        }
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
        $kd_number = $_POST['kd_number'];
        if($kd_number == -1){
            $kd_number = $_POST['sdwuliu'];
            if($kd_number){
                $data['kd_number'] = $kd_number;
            }
        }else{
            $data['kd_number'] = $kd_number;
        }
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


    //商品评价
    public function pingjia(){
        $this -> display();
    }

    public function pingjiabb(){
        $shop_goods = M('shop_goods');$shop_categrey = M('shop_categrey');
        $pagecount = 10;
        $count = $shop_goods ->where($where)-> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $good_list=$shop_goods
            -> alias('a')
            -> field('a.*,b.pic_url')
            -> join("left join __GOOD_PIC__ as b on a.good_id = b.good_id")
            ->where($where)
            ->order("time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order("good_id desc")
            ->select();
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');

        // 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();
        $this->assign('page',$show);
        foreach($good_list as $k=>$v){
            $good_list[$k]['cate_name'] = $shop_categrey -> getFieldByCate_id($v['cate_gid'],'cate_name');
            $gid_name = $shop_categrey -> getFieldByCate_id($v['cate_pid'],'cate_name');
            if($gid_name != null){
                $good_list[$k]['cate_name'] .= " -- ".$gid_name;
            }
            $good_list[$k]['geshu'] = M('pingjia') -> where("good_id = {$v['good_id']} and type = 1") -> count();

        }
        $this->assign("good_list",$good_list);
        $this->display();
    }

    //评价详情
    public function pingjiaXq(){
        $good_id = $_GET['good_id'];
        $this -> assign('good_id',$good_id);
        $this -> display();
    }

    public function pingjiaXqbb(){
        $good_id = $_GET['good_id'];
        $pingjia = M('pingjia');
        $pagecount = 10;
        $count = $pingjia ->where(['good_id' => $good_id])-> count();
        $Page = new \Think\Pageajax($count,$pagecount);
        $good_list=$pingjia
            -> alias('a')
             -> field('a.*,b.name as u_name,b.username as u_tel,c.wxname')
            -> join('left join __USER__ as b on a.user_id = b.user_id')
            -> join('left join __AGENT_INFO__ as c on b.agent_id = c.id')
            ->where(['good_id' => $good_id,'a.type' => 1])
            ->order("a.time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');

        // 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();
        $this->assign('page',$show);
        $this->assign("good_list",$good_list);
        $this->display();
    }

    public function delPingJia(){
        $pj_id = $_POST['pj_id'];
        $res = M('pingjia') -> delete($pj_id);
        if($res){
            echo 0;
        }else{
            echo -1;
        }
    }

	
	function type(){


		$type = M('shop_good_type');
		$spec = M('shop_spec');
		
		$pagecount = 10;
		$count = $type-> count();
		$Page = new \Think\Page($count,$pagecount);
		$info=$type->limit($Page->firstRow.','.$Page->listRows)->order('type_id desc')->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		$show = $Page->show();
		$this->assign('page',$show);
		
		foreach($info as $k=>$v){
			$content = $spec -> where("type_id = '$v[type_id]'") ->order("spec_id desc") -> select();
			$name = '';
			foreach($content as $jj){
				if($name != ''){
					$str = '、';
				}else{$str = '';}
				$name = $name.$str.$jj['spec_name'];
			}
			$info[$k]['name'] = $name;
		}
		$this->assign('info',$info);
		$this->display();
	}
	function type_add(){
		$spec = M('shop_good_type');
		if($_POST){
			if(I('type_name')==''){exit;}
			
			if(I('id')){
				/* 保存修改数据 */
				$type_id = I('id');
				$spec->where(" type_id = '$type_id' ") -> setField('type_name',I('type_name'));
			}else{
				/* 保存新建数据 */
				$spec_id = $spec -> add(array('type_name'=>I('type_name')));
			}
			$this->success('属性组信息保存成功',type);
		}else{
			if($_GET['id']){
				/* 加载修改视图数据 */
				$type_id = $_GET['id'];
				$info = $spec -> where("type_id = '$type_id' ") -> find();
				if($info == null){redirect(type);exit;}
				$this->assign('info',$info);
			}
			$this->display();
		}
	}
	
	function type_del(){
		if(!I('id')){exit;}
		$id = I('id');
		M('shop_good_type') -> where("type_id = '$id'") -> delete();
		$arr =array('success'=>1);
		echo json_encode($arr);
	}
	
	function type_spec(){
		if(I('id')){$type_id = I('id');}else{exit;}
		$type = M('shop_good_type');
		$type_info = $type->where("type_id='$type_id'")->find();$this->assign('type_info',$type_info);
		$spec = M('shop_spec');
		$pagecount = 10;
		$count = $type-> count();
		$Page = new \Think\Page($count,$pagecount);
		$info=$spec->where("type_id = '$type_id'")->limit($Page->firstRow.','.$Page->listRows)->order('spec_id desc')->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		$show = $Page->show();
		$this->assign('page',$show);
		//dump($info);exit;
		$this->assign('info',$info);
		$this->display();
	}
	
	function type_spec_add(){
		$type = M('shop_good_type');$spec = M('shop_spec');
		if($_POST){
			if(I('spec_name')=='' || I('value') ==''){exit;}
			$arr = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',I('value'));
//			$shop_spec_info = M('shop_spec_info');
			if(I('id')){
				/* 保存修改数据 */
				$spec_id = I('id');
				$spec->where(" spec_id = '$spec_id' ") -> save(array('spec_name'=>I('spec_name'),'type_id'=>I('type_id'),'value'=>trim($arr)));
			}else{
				/* 保存新建数据 */
				$spec_id = $spec -> add(array('spec_name'=>I('spec_name'),'type_id'=>I('type_id'),'value'=>trim($arr)));
			}
			$this->success('属性组信息保存成功',U('type_spec',array('id'=>I('type_id'))));
		}else{
			if($_GET['type_id']){$type_id = $_GET['type_id'];}else{redirect(type);exit;}
			if($_GET['spec_id']){
				/* 加载修改视图数据 */
				$spec_id = $_GET['spec_id'];
				$info = $spec -> where("spec_id = '$spec_id' ") -> find();
				if($info == null){redirect(type);exit;}
				$this->assign('info',$info);
			}
			$type_info = $type -> where("type_id = '$type_id' ") -> find();
			$this->assign('type_info',$type_info);
			$this->display();
		}
	}
	function type_spec_del(){
		if(!I('id')){exit;}
		$id = I('id');
		M('shop_spec') -> where("spec_id = '$id'") -> delete();
		$arr =array('success'=>1);
		echo json_encode($arr);
	}
}