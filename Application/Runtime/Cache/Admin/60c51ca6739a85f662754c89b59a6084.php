<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/Public/admin/css/base.css">
<link href="/Public/admin/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<style>
	.table th{font-size:14px;}
	.table tr td{font-size:10px;color:#777;}
</style>
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>用户退款</div>
	<div class="main-content">

		<div>
		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">退款列表</a></li>		    
		  </ul>
		  
		  <!-- Tab panes -->
		  <div class="tab-content">
		    <div role="tabpanel" class="tab-pane active" id="home">
				<div class="alert alert-success" style="padding:5px 10px;margin:15px 0;line-height:30px;">
					<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
						<div class="input-group input-group">
						  <span class="input-group-addon" id="sizing-addon1">姓名</span>
						  <input type="text" class="form-control" name="name" id="name" placeholder="姓名" aria-describedby="sizing-addon1">
						</div>
					</div>
					<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
						<div class="input-group input-group">
							<span class="input-group-addon" id="sizing-addon1">订单号</span>
							<input type="text" class="form-control" name="order_sn" id="order_sn" placeholder="" aria-describedby="sizing-addon1">
						</div>
					</div>
					<div class="col-lg-2 col-md-2" style="padding:0;margin:3px;">
						<div class="input-group input-group">
						  <input class="btn btn-default" type="submit" value="搜索" onclick="getpage(1)">
						</div>
					</div>
					 <div class="clearfix visible-xs-block"></div><div style="clear:both"></div>
				</div>
				<div id="list">
				</div>
		    </div>
		
		  </div>
		</div>
		<input type="hidden" name="pa" id="pa" value="<?php echo ($p); ?>">
	</div>
</div>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/Public/admin/js/bootstrap.min.js"></script>
<script src="/Public/laydate/laydate.js"></script>
<script src="/Public/admin/js/datetonuix.js" type="text/javascript"></script>
<script src="/Public/admin/js/fileinput.js" type="text/javascript"></script>
<script src="/Public/admin/js/fileinput_locale_zh.js" type="text/javascript"></script>
<script src="/Public/admin/layer/layer.js"></script>
<script>
$(document).ready(function(){
	$p = $('#pa').val();
	getpage($p);
});

function getpage(p){
	var name = $("#name").val();
	var order_sn = $("#order_sn").val();
	$('#list').html('<div style="text-align:center;margin-top:30px;"><img src="/Public/admin/images/loading.gif" width="60px" ></div>');
	$("#list").load(
		"<?php echo U('gameUserRefund_ajax');?>?name="+name+"&p="+p + "&order_sn=" + order_sn,
		function() {}
	);
}


</script>