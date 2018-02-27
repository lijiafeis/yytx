<?php
namespace Agent\Controller;
use Think\Controller;

class SendmsgController extends ActionController{
	function __construct(){
		parent::__construct();
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'),5);
		}
		
	}
	
	function message(){
		// $ticket = S($this->token.'getJsApiTicket');
		// dump($ticket);
		// $weixin = A("Wxapi/Weixin");
		// $weixin ->__construct($this->token);
		// $card_id = "pBm-YuHmI0MYFCLZIyPhwIgxlptk";
		// $signPackage=$weixin->getSignPackage("card",$this->token);
		// $cardsign=$weixin->getcardsign($card_id,$this->token);
		// dump($cardsign);
		
		$this->display();
	}
	
	function newslist(){
		$this -> display();
	}
	function newslist_ajax(){
		
		$news = M('news');
		
		$pagecount = 10;
		$count = $news ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$news_info = $news->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("id desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		
		$this->assign("news",$news_info);
		
		$this->display();
	}
	
	function index(){
		
		$this->display();
	}
	
	function get_send_fans_list(){
		/* 如果选择的是平台粉丝，则去拉取openid列表，如果是系统粉丝，则去数据库条件查询 */
		$type = $_POST['type'];
		if($type[0] == 1){
			$openid_all = $this-> create_fans_info();
		}else{
			$where = array();
			if($_POST['sex'][0] != '3'){
				$where['sex'] = $_POST['sex'][0];
			}
			if($_POST['agent'][0] != '2'){
				$where['agent'] = $_POST['agent'][0];
			}
			$where['subscribe'] = 1;
			$res = M('users')->field("openid") -> where($where) -> select();
			foreach($res as $k=>$v){
				$openid_all[$k] = $v['openid'];
			}
		}
		S('send_openid_list',$openid_all);
		$arr['num'] = count($openid_all);
		$arr['type'] = $type;
		echo json_encode($arr);
	}
	
	function send_message(){
		
		$type = $_POST['type'];
		$content = $_POST['content'];
		$system_show = $_POST['system_show'];
		$openid_list = S('send_openid_list');
		$count = count($openid_list);
		switch($type){
			case 'text':
			$data = array(
				"type"=>'text',
				"text"=>$content,
				"time"=>time(),
				"count"=>$count,
				"system_show"=>$system_show,
			);
			break;
			case 'news':
			$data = array(
				"type"=>'news',
				"news_id"=>$content,
				"time"=>time(),
				"count"=>$count,
				"system_show"=>$system_show,
			);
			break;
			case 'image':
			$weixin = A("Wxapi/Weixin");
			$content = substr($content,1);
			$res = $weixin -> media_pic($content);
			if($res['media_id'] == null){$arr['success'] = 0;$arr['info']='上传图片时发生错误！请刷新页面重试。错误码：'.$res['errcode'];echo json_encode($arr);exit;}
			$data = array(
				"type"=>'image',
				"text"=>$content,
				"media_id"=>$res['media_id'],
				"time"=>time(),
				"count"=>$count,
				"system_show"=>$system_show,
			);
			break;
			case 'radio':
			$weixin = A("Wxapi/Weixin");
			$content = substr($content,1);
			$res = $weixin -> media_pic($content,'voice');
			if($res['media_id'] == null){$arr['success'] = 0;$arr['info']='音频文件格式不正确！大小不超过5M，时间不超过60s。错误码：'.$res['errcode'];echo json_encode($arr);exit;}
			$data = array(
				"type"=>'radio',
				"text"=>$content,
				"media_id"=>$res['media_id'],
				"time"=>time(),
				"count"=>$count,
				"system_show"=>$system_show,
			);
			break;
			case 'vedio':
			$weixin = A("Wxapi/Weixin");
			$content = substr($content,1);
			$res = $weixin -> media_pic($content,'video');
			if($res['media_id'] == null){$arr['success'] = 0;$arr['info']='音频文件格式不正确！大小不超过5M，时间不超过60s。错误码：'.$res['errcode'];echo json_encode($arr);exit;}
			$data = array(
				"type"=>'vedio',
				"text"=>$content,
				"media_id"=>$res['media_id'],
				"time"=>time(),
				"count"=>$count,
				"system_show"=>$system_show,
			);
			break;
			case 'card':
			break;
			default:
			break;
		}
		
		$send_message = M('send_message');
		$id = $send_message->add($data);
		$arr['id'] = $id;
		echo json_encode($arr);
		
		
		// $weixin = A("Wxapi/Weixin");
		// $openid_list = S('send_openid_list');
		// foreach($openid_list as $val){
			// $weixin -> send_word($val,$content);
		// }
		
	}
	function sending(){
		$id = I('get.id');
		if(!$id){exit;}
		$send_message = M('send_message');
		$info = $send_message -> where("id = '$id'")->find();if($info == null){exit;}
		$this->assign('info',$info);
		$this->assign('id',$id);
		$this->display();
	}
	
	function sending_ajax(){
		$id = $_POST['id'];$p = $_POST['p'];
		$user_info = S('send_openid_list');
		$count = count($user_info);
		if(!$user_info || $count <= $p){
			if($count <= $p){
				S('send_openid_list',null);
				S('article_list',null);
				M('send_message')->where(" id = '$id' ")->setInc('is_sure');
			}
			$arr['success'] = 0;echo json_encode($arr);exit;
		}
		
		
		$send_message = M('send_message');
		$send_info = $send_message ->where(" id = '$id' ")->find();
		if($send_info['is_sure'] == 1){$arr['success'] = 0;echo json_encode($arr);exit;}
		$weixin = A("Wxapi/weixin");	
		switch($send_info['type']){
			//文本内容
			case 'text':
				$res = $weixin ->send_word($user_info[$p],$send_info['text']);
				if($res["errmsg"] == "ok"){
					$send_message->where(" id = '$id' ")->setInc('success');$arr['success'] = 1;
				}else{
					$send_message->where(" id = '$id' ")->setInc('fail');$arr['success'] = 2;
				}
			break;
			//图文内容
			case 'news':
				$article = S('article_list');
				if(!$article){
					$news_list = explode(",",$send_info['news_id']);
					$i=0;$news = M('news');$article=array();
					foreach($news_list as $val){
						if($val != ''){
							$info = $news ->where(" id = '$val' ")->find();
							$article[$i]['url']=urlencode('http://'.$_SERVER['HTTP_HOST'].U('/home/wap/index')."?id=".$info['id']);
							$article[$i]['title']=urlencode($info['title']);
							$article[$i]['description']=urlencode($info['desc']);
							$article[$i]['picurl']=urlencode('http://'.$_SERVER['HTTP_HOST'].$info['pic_url']);
							$i++;
						}
					}
					S('article_list',$article,'30');
				}
				
				
					$data = array(
						'touser'=>$user_info[$p],
						'msgtype'=>'news',
						'news'=>array(
							'articles'=>$article
						),
					);
					$data = urldecode(json_encode($data));
					$res = $weixin ->send_news($data);
					if($res["errmsg"] == "ok"){
						$send_message->where(" id = '$id' ")->setInc('success');$arr['success'] = 1;
					}else{
						$send_message->where(" id = '$id' ")->setInc('fail');$arr['success'] = 2;
					}
				
			break;
			case 'image':
			$res = $weixin -> send_pic($user_info[$p],$send_info['media_id']);
			if($res["errmsg"] == "ok"){
						$send_message->where(" id = '$id' ")->setInc('success');$arr['success'] = 1;
					}else{
						$send_message->where(" id = '$id' ")->setInc('fail');$arr['success'] = 2;
					}
			break;
			case 'radio':
			$res = $weixin -> send_voice($user_info[$p],$send_info['media_id']);
			if($res["errmsg"] == "ok"){
						$send_message->where(" id = '$id' ")->setInc('success');$arr['success'] = 1;
					}else{
						$send_message->where(" id = '$id' ")->setInc('fail');$arr['success'] = 2;
					}
			break;
			case 'vedio':
			$res = $weixin -> send_vedio($user_info[$p],$send_info['media_id']);
			if($res["errmsg"] == "ok"){
						$send_message->where(" id = '$id' ")->setInc('success');$arr['success'] = 1;
					}else{
						$send_message->where(" id = '$id' ")->setInc('fail');$arr['success'] = 2;
					}
			break;
		}
		$p++;
		$arr['p'] = $p;
		
		echo json_encode($arr);
	}
	
	function file_upload(){
		$upload = new \Think\Upload();// 实例化上传类  
		$upload->rootPath = './Uploads/';  
		$upload->maxSize   =     5242848 ;// 设置附件上传大小 
		$upload->autoSub = false;   
		//$upload->exts      =     array('mp3,mp4');// 设置附件上传类型    
		$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
		$upload->autoSub = true;
		$upload->subName = '';   
		$imginfo   =   $upload->upload();
		if($imginfo){
			$arr['success'] = 1;$arr['info']="/Uploads/".$imginfo["file"]["savepath"].$imginfo["file"]["savename"];
		}else{
			$arr['success'] = 0;$arr['info'] = $upload->getError();
		}
		echo json_encode($arr);
	}
	
	function file_upload1(){
		$upload = new \Think\Upload();// 实例化上传类  
		$upload->rootPath = './Uploads/';  
		$upload->maxSize   =     10485697 ;// 设置附件上传大小 
		$upload->autoSub = false;   
		//$upload->exts      =     array('mp3,mp4');// 设置附件上传类型    
		$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
		$upload->autoSub = true;
		$upload->subName = '';   
		$imginfo   =   $upload->upload();
		if($imginfo){
			$arr['success'] = 1;$arr['info']="/Uploads/".$imginfo["file1"]["savepath"].$imginfo["file1"]["savename"];
		}else{
			$arr['success'] = 0;$arr['info'] = $upload->getError();
		}
		echo json_encode($arr);
	}
	
	function create_fans_info(){
		$weixin = A("Wxapi/weixin");
		$next_openid= "";
		$openid_all=array();
		do{	
			$openid_list = $weixin ->get_openid_list($next_openid);
			$res = $this->objectToArray($openid_list);
			$total = $res['total'];
			$next_openid = $res['next_openid'];
			$arr = $res['data']['openid'];
			//dump($openid_all);
			if(!empty($arr)){
				$openid_all = array_merge($openid_all,$arr);
			}
		}while($next_openid != "");
		return $openid_all;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	function record(){
		
		$this->display();
	}
	
	function record_ajax(){
		$send_message=M('send_message');
		$pagecount = 10;
		$count = $send_message ->where($where)-> count();
		$Page = new \Think\Pageajax($count,$pagecount);
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		$info = $send_message->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("id desc") -> select();
		$show = $Page->show();
		$this->assign('page',$show);
		$news = M('news');
		foreach($info as $k=>$val){
			$info[$k]['time'] = date("Y-m-d H:i:s",$val['time']);
			if($val['type'] == 'news'){
				$news_list = explode(",",$val['news_id']);
				
				foreach($news_list as $v){
					$title = $news->field("title")->where(" id = '$v' ")->find();
					if($title != null){
						if($info[$k]['text'] != null){$info[$k]['text'] .= '<br/>';}
						$info[$k]['text'] .= '<button class="btn-link">'.$title['title'].'</button>'; 
					}
					
				}
				
			}
			
		}
		$this->assign("info",$info);
		$this->display();
	}
	function changetype(){
		//if(!$_POST['id'] || !$_POST['type']){exit;}
		$id = $_POST['id'];
		$type = $_POST['type'];
		if($type == 1){$data = 0;$arr['success'] = 0;}else{$data = 1;$arr['success'] = 1;}
		$send_message=M('send_message');
		$send_message -> where("id = '$id' ") -> setField('system_show',$data);
		echo json_encode($arr);
	}
	

	
	function objectToArray($e){
		$e=(array)$e;
		foreach($e as $k=>$v){
			if( gettype($v)=='resource' ) return;
			if( gettype($v)=='object' || gettype($v)=='array' )
				$e[$k]=(array)$this->objectToArray($v);
		}
		return $e;
	}  
 
	
	
	
	
	

	

    

}

 ?>