<?php
namespace Wxapi\Controller;
use Think\Controller;

class IndexController extends Controller{
	function index(){
		define("TOKEN", 'weixin');
		//$wechatObj = new ApiController();
		if (!isset($_GET['echostr'])) {
		    $this->responseMsg();
		}else{
		    $this->valid();
		}
	}
 //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $tmpArr = array(TOKEN, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }

   //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            //$this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
             
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    //$result = $this->receiveImage($postObj);
                    break;
                case "location":
                    //$result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    //$result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    //$result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    //$result = $this->receiveLink($postObj);
                    break;
                default:
                    //$result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
           // $this->logger("T ".$result);
		   if($result){
			   echo $result;
		   }else{
			   echo '';
		   }
        }else {
            echo "";
            exit;
        }
    }
	
	function test(){
		$openid = 'oBm-YuJBSIKUiyJBqAvAd6CgZGok';
		$scene_id = 2;
		$usermodel = D('Xigua/users');
		$user_id = $usermodel->weixin_add_user($openid,$scene_id);
		echo $user_id;
	}

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
		$openid = trim($object->FromUserName);
        //$name = trim($object->ToUserName);

        switch ($object->Event)
        {
            case "subscribe": //关注事件
                //file_put_contents('name.txt',$name);
				$subscribe = M('subscribe') ->find();
				$scene_id = str_replace("qrscene_","",$object->EventKey);
				$users = M('users');
				$user_info = $users -> field("user_id") -> where(" openid = '$openid' ") ->find();
				if($user_info == null){
					/* 用户不存在 */
					//$usermodel = D('Xigua/users');
					//$user_id = $usermodel->weixin_add_user($openid,$scene_id);

				}
				else{
					/* 用户已存在 */
					//$user_id = $user_info['user_id'];
					//$users -> where("user_id = '$user_id' ") -> save(array('subscribe'=>1,'subscribe_time'=>time(),'sj_userid' => $scene_id));
				}
				//$jifen = A('Xigua/jifen');
				//$jifen -> add_jifen_subscribe($user_id,$openid);
				$content = $subscribe['content'];
                break;
            case "unsubscribe"://取消关注事件
                M('users') ->  where(" openid = '$openid' ") ->save(array('subscribe'=>0));
                break;
            case "SCAN": //扫描事件
                //$content = "扫描场景 ".$object->EventKey;
                break;
            case "CLICK": //菜单点击事件
                if(trim($object->EventKey) != ''){
					$result = $this->receiveText($object);
					echo $result;
					exit;
				}
                break;
            case "LOCATION": //地理位置上传事件
                //$content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "VIEW":
               // $content = "跳转链接 ".$object->EventKey;
                break;
            case "MASSSENDJOBFINISH":
                //$content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
                break;
            default:
              //  $content = "receive a new event: ".$object->Event;
                break;
        }
        if(is_array($content)){
            if (isset($content[0])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
			if($content == ''){
				echo '';exit;
			}else{
				$result = $this->transmitText($object, $content);
			}
            
        }

        return $result;
    }

    //接收文本消息
    private function receiveText($object)
    {
       if($object->Content){
			$keyword = trim($object->Content);
		}else{
			$keyword = trim($object->EventKey);
		}
        //自动回复模式
		if (strstr($keyword, "qr")){
			//得到用户user_id
			$user_info = M('users') -> field("user_id,agent") -> where(" openid = '$object->FromUserName' ") -> select();
			$user_id = $user_info[0]['user_id'];
			if($user_info[0]['agent'] == 0){
				$content = C('QR_NOTICE');
			}else{
				
				if(S($user_id.'qr')){echo '';exit;}
				S($user_id.'qr',1,5);
				$weixin = A('Wxapi/Weixin');
				$weixin -> send_word($object->FromUserName,"入口已关闭，在个人中心生成");
				exit;
				$weixin -> send_word($object->FromUserName,"后台正在自动合成宣传二维码，请稍后几秒~");
				$qrcode_info = M('qrcode') -> getByUser_id($user_id);
				if(file_exists('Public/qr_path/'.$user_id.'.jpg')){
					
					$media_id = $qrcode_info['media_id'];
					//判断media_id是否过期以及临时二维码是否已经超过30天
					$time1 = time() - $qrcode_info['created_at'];
					if($time1>= 259200){
						//临时素材已过期需重新上传
						$media=$weixin->media_pic('Public/qr_path/'.$user_id.'.jpg');
						$data['created_at'] = $media['created_at'];
						$data['media_id'] = $media_id = $media['media_id'];
						M('qrcode') -> where(" scene_id = '$qrcode_info[scene_id]' ") -> save($data);
					}
					$time2 = time() - $qrcode_info['update_time'];
					if($time2 >= 2592000 || $qrcode_info['update_time'] == 0){
						//参数二维码超过了30天，需重新生成
						$media_id = $weixin -> get_qr_path_new($user_id,$qrcode_info[scene_id]);
					}	
				}else{
					$media_id = $weixin -> get_qr_path_new($user_id,$qrcode_info[scene_id]);
				}
				//$weixin -> send_pic($object->FromUserName,$media_id);
				$content['MediaId'] = $media_id;
				$result = $this->transmitImage($object,$content );
				return $result;exit;
			}
			
			
		}elseif($keyword == C('GUANONG')){
			$users = M('users');
			$openid = $object->FromUserName;
			$res = $users -> field("user_id") -> where(" openid = '$openid' ") ->select();
			if(!$res[0]['user_id']){
				$weixin = A("Wxapi/Weixin");
				$data = $weixin -> get_user_info($openid);
				$users -> add($data);
				$content = "success";
			}
			
		}else{
			$content = "";
			$news = M('news');
			$news_all = $news->field("id,pic_url,keyword,keyword_type,title,desc") -> where("keyword != '' ") ->order("code asc") -> select();
			$newsinfo = array();$i=0;
			foreach($news_all as $item){
				$keyword_arr = explode(',',$item['keyword']);
				foreach($keyword_arr as $vv){
					if($item['keyword_type'] == 1 && strstr($keyword,$vv)){
						$newsinfo[$i] = $item;$i++;
					}
					if($item['keyword_type'] == 0 && $keyword == $vv){
						$newsinfo[$i] = $item;$i++;
					}
				}
			}
			//如果有图文内容
			if($newsinfo != null){
				$content = $newsinfo;
				foreach($content as $key =>$value){
					$id = $value['id'];
					$content[$key]['url']="http://".$_SERVER['SERVER_NAME'].U('/home/wap/index')."?id=".$id;
					$content[$key]['pic_url']="http://".$_SERVER['SERVER_NAME'].$value['pic_url'];
					$res=$news->where(" id = '$id' ")->setInc('click',1);
				}
			}else{
				$text = M('text');
				$text_all=$text->select();
				$textinfo = array();$i=0;
				foreach($text_all as $item){
					$keyword_arr = explode(',',$item['keyword']);
					foreach($keyword_arr as $vv){
						if($item['keyword_type'] == 1 && strstr($keyword,$vv)){
							$textinfo[$i] = $item;$i++;
						}
						if($item['keyword_type'] == 0 && $keyword == $vv){
							$textinfo[$i] = $item;$i++;
						}
					}
				}
				
				
				if($textinfo){
					//如果有创建，从结果中取随机值
					$temp = array_rand($textinfo,1);  
					$content = $textinfo[$temp]['content'];
					//增加调用次数
					$id = $textinfo[$temp]['id'];
					$res=$text->where(" id = '$id' ")->setInc('click',1); 
				}else{				
					$custominfo=M('custom')->getByToken($token);
					//如果开启了自定义回复
					if($custominfo['switch'] == 1){
						if($custominfo['keyword']){			
							$where=array(
								'keyword'=>$custominfo['keyword']
							  );
							 $newsinfo=M('news')->where($where)->order("code asc")->select();
							if($newsinfo){
								//如果有，赋值
								$content = $newsinfo;
								foreach($content as $key =>$value){
									$id = $value['id'];
									$content[$key]['url']="http://".$_SERVER['SERVER_NAME'].U('/home/wap/index')."?id=".$id;
									$content[$key]['pic_url']="http://".$_SERVER['SERVER_NAME'].$value['pic_url'];
								}
							}else{
								$content = "平台未做关键词图文回复";
							}
						}else{
							$content = $custominfo['content'];
						}
					}elseif($custominfo['switch'] == 2){
						//进入多客服模式
						$result = $this->transmitService($object);
						return $result;
						exit;
					}

				}
			}    
        }

       if(is_array($content)){
			$result = $this->transmitNews($object, $content);
			
		}elseif(empty($content)){
			echo "";exit;
		}else{
			$result = $this->transmitText($object, $content);
		}
		return $result;
    }

    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
    <MediaId><![CDATA[%s]]></MediaId>
</Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
           $item_str .= sprintf($itemTpl, $item['title'], $item['desc'], $item['pic_url'], $item['url']);
        }
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 10000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }


}


