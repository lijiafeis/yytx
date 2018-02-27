<?php
namespace Wxapi\Controller;
use Think\Controller;

class QrimgController extends Controller{
	function index($user_id,$nickname){
		if($user_id == 0){
			$erweima_img='Public/2.jpg';
			$head_img="Public/head.jpg";
		}else{
			$erweima_img='Public/qrimg/'.$user_id.'.jpg';
			$head_img="Public/head_pic/".$user_id.".jpg";
		}
		//相关参数
		$info = M('qrset')->select();
		$head_height=$head_width=$info[0]['head_size'];
		$erweima_height=$erweima_width=$info[0]['qr_size'];
		$dst_path=$info[0]['pic_url'];
		$str=$nickname;
		$font_size=$info[0]['font_size'];
		$fnt_x=$info[0]['font_x'];
		$fnt_y=$info[0]['font_y'];
		//载入字体zt.ttf 
		$fnt = "Public/msyh.ttf"; 
		//头像缩小
		$src1=$this->img_suo($head_img,$head_width,$head_height);
		//二维码缩小
		$src=$this->img_suo($erweima_img,$erweima_width,$erweima_height);
		//创建图片的实例
		$dst = imagecreatefromstring(file_get_contents($dst_path));
		//$src = imagecreatefromstring(file_get_contents($src_path));
		//获取水印图片的宽高
		//将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
		imagecopymerge($dst, $src1, $info[0]['head_x'], $info[0]['head_y'], 0, 0, $head_width, $head_height, 100);
		imagecopymerge($dst, $src, $info[0]['qr_x'], $info[0]['qr_y'], 0, 0, $erweima_width, $erweima_height, 100);
		//如果水印图片本身带透明色，则使用imagecopy方法
		//imagecopy($dst, $src, 10, 10, 0, 0, $src_w, $src_h);
		//创建颜色，用于文字字体的白和阴影的黑 
		$white=imagecolorallocate($dst,222,229,207); 
		$black=imagecolorallocate($dst,50,50,50);
		imagettftext($dst,$font_size, 0, $fnt_x+1, $fnt_y+1, $black, $fnt, $str); 
		imagettftext($dst,$font_size, 0, $fnt_x, $fnt_y, $white, $fnt, $str); 
		if(!is_dir('Public/qr_path/')){
			mkdir('Public/qr_path/');
		}
		ImageJPEG($dst,'Public/qr_path/'.$user_id.'.jpg'); // 保存图片,但不显示 
		//销毁对象 
		ImageDestroy($dst);
		return 'Public/qr_path/'.$user_id.'.jpg';
	}
	
function web_qr($user_id,$name = '亿赢天下'){
		if($user_id == 0){
			$erweima_img='Public/2.jpg';
			$head_img="Public/head.jpg";
		}else{
			$erweima_img='Public/webqr/'.$user_id.'.png';
		}
		//相关参数
		$info = M('qrset')->select();
		$head_height=$head_width=$info[0]['head_size'];
		$erweima_height=$erweima_width=$info[0]['qr_size'];
		$dst_path=substr($info[0]['pic_url'],1);
		$str=$name;
		$font_size=$info[0]['font_size'];
		$fnt_x=$info[0]['font_x'];
		$fnt_y=$info[0]['font_y'];
		//载入字体zt.ttf 
		$fnt = "Public/msyh.ttf"; 
		//头像缩小
		//$src1=$this->img_suo($head_img,$head_width,$head_height);
		//二维码缩小
		//二维码缩小

		$this->img_suo_png($erweima_img,$erweima_width,$erweima_height,$user_id);
//		dump($src);
		$src ='Public/webqr/'.$user_id.'.png';
		$erweima_img = imagecreatefromstring(file_get_contents($src));
		//创建图片的实例
		$dst = imagecreatefromstring(file_get_contents($dst_path));
		//$src = imagecreatefromstring(file_get_contents($src_path));
		//获取水印图片的宽高
		//将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
		//imagecopymerge($dst, $src1, $info[0]['head_x'], $info[0]['head_y'], 0, 0, $head_width, $head_height, 100);
		imagecopymerge($dst, $erweima_img, $info[0]['qr_x'], $info[0]['qr_y'], 0, 0, $erweima_width, $erweima_height, 100);
		//如果水印图片本身带透明色，则使用imagecopy方法
		imagecopy($dst, $erweima_img, $info[0]['qr_x'], $info[0]['qr_y'], 0, 0, $erweima_width, $erweima_width);
		//创建颜色，用于文字字体的白和阴影的黑 
		$white=imagecolorallocate($dst,222,229,207);
		$black=imagecolorallocate($dst,50,50,50);
		imagettftext($dst,$font_size, 0, $fnt_x+1, $fnt_y+1, $black, $fnt, $str);
		imagettftext($dst,$font_size, 0, $fnt_x, $fnt_y, $white, $fnt, $str);
		if(!is_dir('Public/web_path/')){
			mkdir('Public/web_path/');
		}
		ImageJPEG($dst,'Public/web_path/'.$user_id.'.jpg'); // 保存图片,但不显示 
		//销毁对象 
		ImageDestroy($dst);
	}

	function img_suo($img='head.jpg',$new_width=100,$new_height=100){
		list($width, $height) = getimagesize($img);
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = imagecreatefromjpeg($img);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

		return $image_p;
	}
	function img_suo_png($img='head.jpg',$new_width=100,$new_height=100,$user_id){
		list($width, $height) = getimagesize($img);
		$image_p = imagecreate($new_width, $new_height);
		$image = imagecreatefrompng($img);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		ImagePNG($image_p,'Public/webqr/'.$user_id.'.png');
	}
}
	