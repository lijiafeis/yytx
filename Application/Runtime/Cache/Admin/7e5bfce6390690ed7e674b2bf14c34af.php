<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<head>
	<meta name="renderer" content="webkit">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" >
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>上传图片--西瓜科技上传插件</title>
	<link href="//cdn.bootcss.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
	<link href="//cdn.bootcss.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	<script src="//cdn.bootcss.com/jquery/2.2.1/jquery.js"></script>
	<script src="//cdn.bootcss.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script src="/Public/admin/layer/layer.js"></script>
	<script src="/Public/admin/laypage/laypage.js"></script>
	<script src="/Public/admin/js/xigua.upload.js"></script>
	<script src="/Public/admin/js/ajaxfileupload.js"></script>
	<style>
	.file {position: relative;margin:10px 0;display: inline-block;background: #f0ad4e;border: 1px solid #f0ad4e;border-radius: 4px;padding: 4px 12px;overflow: hidden;
		color: #fff;text-decoration: none;text-indent: 0;line-height: 25px;}
	.enter {position: absolute;right:20px;top:15px;}
	.file input {position: absolute;font-size: 100px;right: 0;top: 0;opacity: 0;}
	.file:hover {background: #44b549;border-color: #44b549;color: #fff;text-decoration: none;}
	.col-xs-3{margin:10px 0;}
	.col-xs-3 img{cursor:pointer}
	.select{line-height:120px;background:rgba(0,0,0,0.5);width:100px;height:100px;position:absolute;top:0;left:15px;color:#fff;display:none;}
	</style>
</head>
<body style="width:500px;margin:0;padding:10px;">
	<a href="javascript:;" class="file"><input type="file" name="file" id="file" accept="image/*" />上传新图片</a>
	<a href="javascript:;" class="btn btn-success enter" data="" data-loading-text="未选择图片">确 定</a>
	<div class="row text-center" id="content" style="width:100%;height:auto;margin:0;padding:0;">
		<i class="icon-spinner icon-spin icon-4x" style="line-height:200px"> </i>
		<!-- <div class="col-xs-3 text-center" onclick="select()"><div class="select" style=""><i class="icon-ok-sign icon-3x"></i> </div><img src="./case/1.jpg" width="100px" height="100px"></div> -->
		
	</div>
	<div id="page4" style="margin:10px auto;width:100%;text-align:center;border-top:1px solid #ccc;padding:3px 0 0 0;"></div>
<script>
var xigua = {
		pic_address:"Uploads/",//图片文件夹地址
		nums:8,   //每页显示数
		a_url:"<?php echo U('pic_data');?>", //请求地址
		pageid:'page4',//页码显示id
		cid:'content', //文件显示容器Id
	};
	xigua_upload(xigua);
$(document).ready(function(){
	var index = parent.layer.getFrameIndex(window.name);
	$('.enter').button('loading');
	$('.enter').click(function(){
		var pic_url = $(this).attr('data');
		if(pic_url == ''){
			$(this).button('loading');
		}else{
			parent.$("input[name='<?php echo ($id); ?>']").val(pic_url);
			parent.layer.close(index);
		}
	});
	//监听上传文件变更
	
		$("#file").on('change',function(e){
		var file = document.getElementById("file").files[0];  
		if(!/image\/\w+/.test(file.type)){
			layer.msg("请选择图片上传");  
			return false;  
		}
		layer.msg('正在上传', {icon: 16});//exit;
		$.ajaxFileUpload({
			url: "<?php echo U('pic_upload');?>", //用于文件上传的服务器端请求地址
			secureuri: false, //一般设置为false
			fileElementId: 'file', //文件上传空间的id属性  <input type="file" id="file" name="file" />
			dataType: 'json', //返回值类型 一般设置为json
			success: function (data, status){
				location.reload();//xigua_upload(xigua);
			},
			error: function (data, status, e){
				alert(1);
			}
        });
	});
});
</script>
</body>
</html>