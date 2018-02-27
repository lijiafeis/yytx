<?php
namespace Agent\Controller;
use Think\Controller;

class ImgController extends ActionController{
	
	function __construct(){
		parent::__construct();
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'),5);
		}
	}
	
	function index(){
		$id = $_GET['id'];
		$this->assign('id',$id);
		$this->display();
	}
	
	function pic_upload(){
		$upload = new \Think\Upload();// 实例化上传类  
		$upload->rootPath = './Uploads/';  
		$upload->maxSize   =     3145728 ;// 设置附件上传大小 
		$upload->autoSub = false;   
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
		$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
		$upload->autoSub = true;
		$upload->subName = '';   
		$imginfo   =   $upload->upload();
		$data = $_POST;
		if($imginfo){
			$imgres="/Uploads/".$imginfo["file"]["savepath"].$imginfo["file"]["savename"];
		}
		echo json_encode($imgres);
	}
	
	function pic_data(){
		$arr =array();
		//获取到指定文件夹内的照片数量
		$img = array('gif','png','jpg');//所有图片的后缀名
		$dir = $_POST['address'];//文件夹名称
		$num = $_POST['num'];
		$pic = array();
		// foreach($img as $k=>$v)
		// {
			// $pattern = $dir.'*.'.$v;
			// $all = glob($pattern);
			// $pic = array_merge($pic,$all);
		// }
		if (is_dir($dir)){
			if ($dh = opendir($dir)){
				while (($file = readdir($dh))!= false){
					$filePath = $dir.$file;
					$arr1 = explode('.',$file);
					if(in_array('png',$arr1) || in_array('jpg',$arr1) || in_array('gif',$arr1)){
						$all = array($filePath);
						$pic = array_merge($pic,$all);
						
					}
					
				}
				closedir($dh);
			}
		}
		//echo "<pre>";

		foreach($pic as $k=>$p)
		{
			$new[$k]['time'] =  filemtime($p);
			$new[$k]['pic'] =  $p;
		//分行分页显示代码
		}
		rsort($new);
		//var_dump($new);
		foreach($new as $k=>$v)
		{
			$pic[$k] =  $v['pic'];
		//分行分页显示代码
		}
		$arr['num'] = ceil(count($pic)/$num);
		$arr['pic'] = $pic;
		echo json_encode($arr);
	}
}