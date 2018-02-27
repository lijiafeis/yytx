<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/Public/admin/js/bootstrap.min.js"></script>
<style>
.modal-dialog{margin-top:5px;}
</style>
<link rel="stylesheet" href="/Public/admin/css/base.css">
    <header class="header top"  oncontextmenu=self.event.returnValue=false onselectstart="return false">   
    <a href="<?php echo U('Index/main');?>" target="main-frame"><div class="top_head"></div></a>
	
    <!-- <a onclick="if (confirm('确定要退出吗？')) return true; else return false;"href="<?php echo U('out');?>" target=_top>
		<div class="top_close"><i class="glyphicon glyphicon-off"></i> 退出</div>
	</a> -->
	<a href="javascript:void(0);" data-toggle="modal" data-target="#myModal">
		<div class="top_close"><i class="glyphicon glyphicon-off"></i> 退出</div>
	</a>
	<!-- <a href="<?php echo U('person');?>" target="main-frame"><div class="top_person"><i class="icon-user"></i> 个人设置</div></a> -->
    </header>
<!-- Button trigger modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-footer">
		<span style="float:left;font-size:16px;">确定要退出登录吗？</span>
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-success" onclick="out()">退出</button>
      </div>
    </div>
  </div>
</div>
<div type="button" id="notice" data-toggle="modal" data-target=".bs-example-modal-lg" style="width:0;height:0;"></div>


<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-sm">
    <div class="modal-content alert-success" style="font-size:16px;line-height:50px;padding:0 20px;text-align:center;">
     <i class="glyphicon glyphicon-volume-up"></i>  有新支付订单，请及时处理
    </div>
  </div>
</div>
<audio id="audio" src="/Public/notice.mp3" preload="auto" style="display:none;"></audio>

<script>
var audio = $("#audio")[0]; 
function out(){
	location.href="<?php echo U('out');?>";
}
var time = 0;
$(document).ready(function(){
	//settime();
});
function settime(){
	if(time == 12){
		notice();
		time = 0;settime();
	}else{
		
		setTimeout(function(){time++;settime();},1000);
	}
}
function notice(){
	$.ajax({
		type:'post',
		url:"<?php echo U('notice');?>",
		dataType:'json',
		data:{},
		success:function(json){
			if(json.success == 1){
				notice_window();
			}
		},
		error:function(){
		
		}
	});
}
function notice_window(){
	$('#notice').click();audio.play();setTimeout(function(){$('#notice').click();},10000);
}
</script>