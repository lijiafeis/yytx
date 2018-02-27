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
        $model = M('users');
        $head_img = $model -> getFieldByUserId($this->user_id,'headimgurl');
        $nickname = $model -> getFieldByUserId($this -> user_id,'nickname');
//        $type = M('user') -> getFieldByUserId($this->user_id,'type');
//        switch ($type){
//            case 0:
//                $type = '无';
//                break;
//            case 1:
//                $type = '总代';
//                break;
//            case 2:
//                $type = '见习股东';
//                break;
//            case 3:
//                $type = '高级股东';
//                break;
//            case 4:
//                $type = '首席股东';
//                break;
//        }

        $this -> assign('head_img',$head_img);
        $this -> assign('nickname',$nickname);
        $this -> assign('user_id',$this->user_id);
//        $this -> assign('type',$type);
        $this -> display();
    }

    //我的团队
    public function team(){
        $data = M('user')
            -> where(['sj_userid' => $this -> user_id])
            -> select();
        $this -> assign('data',$data);
        $this -> assign('type',0);
        $this -> display();
    }

    public function team2(){
        $data = M('user')
            -> where(['sj_userid' => $this -> user_id])
            -> select();
        $arr = $this -> getUserInfo($data);
        $this -> assign('data',$arr);
        $this -> assign('type',1);
        $this -> display('team');
    }

    public function team3(){
        $data = M('user')
            -> where(['sj_userid' => $this -> user_id])
            -> select();
        $arr = $this -> getUserInfo($data);
        $arr = $this -> getUserInfo($arr);
        $this -> assign('data',$arr);
        $this -> assign('type',2);
        $this -> display('team');
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
    public function duihuan(){
        $head_img = M('users') -> getFieldByUserId($this->user_id,'headimgurl');
        $info = M('user') -> where(['user_id' => $this->user_id]) -> select();
//        dump($info);
        $this -> assign('head_img',$head_img);
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
        $data['user_id'] = $this->user_id;
        $bank_res = M('user_bank') -> where("user_id = '$this->user_id' ")->find();
        if($bank_res == null){
            $res = M('user_bank') -> add($data);
        }else{
            $res = M('user_bank') ->where("bank_id = '$bank_res[bank_id]' ") -> save($data);
        }

        $money = $_POST['money'];
		if($money < 10){
			exit;
		}
        $fee = M('user') -> getFieldByUserId($this->user_id,'money');
        if($money > $fee){
            echo -1;
            exit;
        }
        $data1 = array(
            'user_id'=>$this->user_id,
            'time'=>time(),
            'money'=>$money,
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


    //人脉成长值
    public function renmai(){
        $this -> display();
    }

    public function renmaibb(){
        $page = $_GET['p'];
        $start = ($page -1) * 20;
        $data = M('yongjin')
            -> where(['user_id' => $this -> user_id,'type' => 0])
			-> order('time desc')
            -> limit($start,20)
            -> select();
        foreach($data as $k => $v){
            $data[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
        }
        $this -> ajaxReturn($data);
    }

    //我的收藏
    public function collect(){
        $this -> display();
    }

    public function collectbb(){
        $page = $_GET['p'];
        $start = ($page -1) * 10;
        $data = M('collect')
            -> alias('a')
            -> field('a.*,b.*,a.time as colltime,c.pic_url')
            -> join("left join __SHOP_GOODS__ as b on a.good_id = b.good_id")
            -> join("left join __GOOD_PIC__ as c on a.good_id = c.good_id")
            -> order('a.time desc')
            -> where(['user_id' => $this->user_id])
            -> limit($start,20)
            -> select();
//        dump($data);
        $this -> ajaxReturn($data);
    }

    //收藏删除
    public function delCollect(){
        $good_id = $_POST['good_id'];
        $res = M('collect') -> where(['user_id' => $this->user_id,'good_id' => $good_id]) -> delete();
        if($res){
            echo 0;
        }else{
            echo -1;
        }
    }

    //我的卡卷
    public function kajuan(){
//        $info = M('daijinjuan') -> where(['user_id' => $this->user_id]) -> select();
//        $head_img = M('users') -> getFieldByUserId($this->user_id,'headimgurl');
//        $this -> assign('head',$head_img);
//        $this -> assign('data',$info);
//        $this -> display();
    }

    public function order(){
        $this -> display();
    }

    //我的授权
    public function shouquan(){

            $users=M('users')->getByUser_id($this->user_id);
            $ewm=A("Wxapi/Weixin");
            if(!file_exists('public/head_pic/'.$this->user_id.'.jpg')){
                $ewm->save_head_pic($this->user_id);
            }
//            if (! file_exists ( "Public/qrimg/{$this->user_id}.jpg" )) {
//
//                $ewm->get_qr_limit($this->user_id,$this->user_id);
//            }

            if (! is_dir ( 'Public/qrimg/' )) {
                mkdir ( 'Public/qrimg/' );
            }
            $data = M('user') -> where(['user_id' => $this->user_id]) -> select();
            $type = '';
            switch ($data[0]['type']){
                case 1:
                    $type = '总代';
                    break;
                case 2:
                    $type = '见习股东';
                    break;
                case 3:
                    $type = '高级股东';
                    break;
                case 4:
                    $type = '首席股东';
                    break;
            }
			
            $qrimg = A ( "Wxapi/Qrimg" );
            // $user_info = M('users') ->field("nickname,headimgurl") -> getByUser_id($this->user_id);
            // $user_info['headimgurl'] = substr($user_info['headimgurl'],1);
			//dump($data[0]['tel']);
			$name = M('user') -> getFieldByUserId($this->user_id,'name');
			$time = M('user') -> getFieldByUserId($this->user_id,'time');
			
            $qrimg->index1( $this->user_id,$name,$type,$data[0]['tel'],date('Y-m-d',$time));
            $this->assign ( 'user_id', $this->user_id );
            $this->display ();
    }

    //我的海报
    /*public function qr(){
        $users=M('users')->getByUser_id($this->user_id);
        $ewm=A("Wxapi/Weixin");
        if(!file_exists('public/head_pic/'.$this->user_id.'.jpg')){
            $ewm->save_head_pic($this->user_id);
        }
        if (! file_exists ( "Public/qrimg/{$this->user_id}.jpg" )) {

            $ewm->get_qr_limit($this->user_id,$this->user_id);
        }

        if (! is_dir ( 'Public/qrimg/' )) {
            mkdir ( 'Public/qrimg/' );
        }

        $arr = M('user') -> where(['user_id' => $this->user_id]) -> select();
        $arr = $arr[0];

        $qrimg = A ( "Wxapi/Qrimg" );
        $url = "http://" . $_SERVER['SERVER_NAME'];
        $this -> assign('head','public/head_pic/'.$this->user_id.'.jpg');
        $this -> assign('url',$url);
        $this -> assign('arr',$arr);
        $this -> assign('qr',"Public/qrimg/{$this->user_id}.jpg");
        $this->display ();
    }*/
//	public function qr(){
//		$is_true = M('user') -> getFieldByUserId($this->user_id,'is_true');
//		if(!$is_true){
//			$this -> error('请先购买代理','index');
//			exit;
//		}
//		$users=M('users')->getByUser_id($this->user_id);
//		$ewm=A("Wxapi/Weixin");
//		if(!file_exists('public/head_pic/'.$this->user_id.'.jpg')){
//			$ewm->save_head_pic($this->user_id);
//		}
////		if (! file_exists ( "Public/qrimg/{$this->user_id}.jpg" )) {
//
//		    $ewm->get_qr_limit($this->user_id);
////            import ( "Org.Util.Erweima" );
////            $value = "http://" . $_SERVER['SERVER_NAME'] . "/Login/User/register&id=" . $this->user_id;
////            $errorCorrectionLevel = "L";
////            $matrixPointSize = "6";
////            $file_name = 'Public/qrimg/' . $this -> user_id . '.png';
////            if(file_exists($file_name)){
////                unlink($file_name);
////            }
////            \QRcode::png ( $value,$file_name , $errorCorrectionLevel, $matrixPointSize, 1, true );
////		}
//
//		if (! is_dir ( 'Public/qrimg/' )){
//			mkdir ( 'Public/qrimg/' );
//		}
//		$qrimg = A ( "Wxapi/Qrimg" );
//			// $user_info = M('users') ->field("nickname,headimgurl") -> getByUser_id($this->user_id);
//			// $user_info['headimgurl'] = substr($user_info['headimgurl'],1);
//
//			$qrimg->index ( $this->user_id,$this->user_id );
//
//		$this->assign ( 'user_id', $this->user_id );
//		$this->display ();
//	}

    public function qr(){
        if (! is_dir ( 'Public/webqr/' )) {
            mkdir ( 'Public/webqr/' );
        }
        //if (!file_exists ( 'Public/webqr/' . $this->user_id . '.png' )) {
        import ( "Org.Util.Erweima" );
        //得到当前人的是属于哪个平台的agent_id;
        $agent_id = M('user') -> getFieldByUserId($this->user_id,'agent_id');
        $value = "http://".$_SERVER['SERVER_NAME'].U('/Login/User/register')."?id=".$this->user_id . "&agent_id=" . $agent_id;
        $errorCorrectionLevel = "L";
        $matrixPointSize = '7';
        \QRcode::png ( $value, 'Public/webqr/' . $this->user_id . '.png', $errorCorrectionLevel, $matrixPointSize, 1, true );
        //}
        $qrimg = A ("Wxapi/Qrimg" );
        $qrimg->web_qr ( $this->user_id);
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
	
	 //提现记录
    public function yongjinShow(){
        $this -> display();
    }

    public function yongjinshowbb(){
        $page = $_GET['p'];
        $start = ($page -1) * 20;
        $data = M('yongjin')
            -> alias('a')
            -> field('a.*,b.name')
            -> join("left join __USER__ as b on a.xj_userid = b.user_id")
            -> where(['a.user_id' => $this->user_id])
            -> limit($start,20)
            -> order('a.time desc')
            -> select();
        foreach ($data as $k => $v){
            $data[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
			if($v['type'] == 0){
				$data[$k]['type'] = "新人入住";
			}else if($v['type'] == 1){
				$data[$k]['type'] = "商品返佣";
			}else if($v['type']){
				$data[$k]['type'] = "股东分红";
			}
        }
        $this -> ajaxReturn($data);
    }
	
	//提现记录
    public function goumaiczz1(){
        $this -> display();
    }

    public function goumaiczzbb(){
        $page = $_GET['p'];
        $start = ($page -1) * 20;
        $data = M('yongjin')
            -> alias('a')
            -> field('a.*,b.name')
            -> join("left join __USER__ as b on a.xj_userid = b.user_id")
            -> where(['a.user_id' => $this->user_id,'a.type' => ['in',[1,2]]])
            -> limit($start,20)
            -> order('a.time desc')
            -> select();
        foreach ($data as $k => $v){
            $data[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
			if($v['type'] == 0){
				$data[$k]['type'] = "购买代理";
			}else if($v['type'] == 1){
				$data[$k]['type'] = "商品返佣";
			}else if($v['type']){
				$data[$k]['type'] = "股东分红";
			}
        }
        $this -> ajaxReturn($data);
    }
	
	//zongheczz
    public function zongheczz(){
		//查询当天的佣金和当月佣金
		$sql1 = "select sum(money) a from wx_yongjin where user_id = {$this -> user_id} and  from_unixtime(
time,'%Y-%m-%d') = current_date();";
		$sql2 = "select sum(money) b from wx_yongjin where user_id = {$this->user_id} and year(from_unixtime(time,'%Y-%m-%d')) = year(current_date) and month(from_unixtime(time,'%Y-%m-%d')) = month(current_date)";
		$model = M();
		$data = $model -> query($sql1);
		$data1 = $model -> query($sql2);
		$this -> assign('day',$data[0]);
		$this -> assign('month',$data1[0]);
        $this -> display();
    }

    public function zongheczzbb(){
        $page = $_GET['p'];
        $start = ($page -1) * 20;
        $data = M('yongjin')
            -> alias('a')
            -> field('a.*,b.name')
            -> join("left join __USER__ as b on a.xj_userid = b.user_id")
            -> where(['a.user_id' => $this->user_id])
            -> limit($start,20)
            -> order('a.time desc')
            -> select();
        foreach ($data as $k => $v){
            $data[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
			if($v['type'] == 0){
				$data[$k]['type'] = "入驻商城佣金";
			}else if($v['type'] == 1){
				$data[$k]['type'] = "商品购买佣金";
			}else if($v['type'] == 2){
				$data[$k]['type'] = "股东分红佣金";
			}
        }
        $this -> ajaxReturn($data);
    }

}