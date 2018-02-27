<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/Public/bootstrap/css/font-awesome.min.css">
<link rel="stylesheet" href="/Public/admin/css/base.css">
<link href="/Public/admin/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>订单管理</div>
	<div class="main-content">

		<div>
		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">订单列表</a></li>		    
		  </ul>
		  
		  <!-- Tab panes -->
		  <div class="tab-content">
		    <div role="tabpanel" class="tab-pane active" id="home">
				
				<div class="well" style="">
			<div class="col-lg-1 col-md-1 text-right">开始日：</div><div class="laydate-icon col-lg-3 col-md-3" id="start" style="width:200px; margin-right:10px;"></div>
			<div class="col-lg-1 col-md-1 text-right">结束日：</div><div class="laydate-icon col-lg-3 col-md-3" id="end" style="width:200px;"></div>

			
			<a href="javascript::" onclick="down()"><div type="button" class="btn btn-danger">导出已支付订单</div></a>
			<div class="clearfix visible-xs-block"></div><div style="clear:both"></div>
			</div>
				
				<div class="alert alert-success" style="padding:5px 10px;margin:15px 0;line-height:30px;">
					<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
						<div class="input-group input-group">
						  <span class="input-group-addon" id="sizing-addon1">收货人</span>
						  <input type="text" class="form-control" name="shname" id="shname" placeholder="输入收货人姓名" aria-describedby="sizing-addon1">
						</div>
					</div>
					<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
						<div class="input-group input-group">
						  <span class="input-group-addon" id="sizing-addon1">支付状态</span>
						  <select class="form-control" name="subscribe" id="is_true">
						  <option value="-1">请选择</option>
						  <option value="1">已支付</option>
						  <option value="0">未支付</option>
						  </select>
						</div>
					</div>
					<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
						<div class="input-group input-group">
						  <span class="input-group-addon" id="sizing-addon1">订单状态</span>
						  <select class="form-control" name="state" id="state">
						  <option value="-1">请选择</option>
						  <option value="0">未发货</option>
						  <option value="1">已发货</option>
						  </select>
						</div>
					</div>
					<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
						<div class="input-group input-group">
						  <span class="input-group-addon" id="sizing-addon1">开始日</span>
						  <input type="text" class="form-control" name="" id="start1" placeholder="" readonly>
						</div>
					</div>
					<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
						<div class="input-group input-group">
						  <span class="input-group-addon" id="sizing-addon1">结束日</span>
						  <input type="text" class="form-control" name="" id="end1" placeholder="" readonly>
						</div>
					</div>
<style>
.table th{font-size:14px;}
.table tr td{font-size:10px;color:#777;}
</style>

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
<script>
laydate.skin('yalan');
var start = {
    elem: '#start',
    format: 'YYYY-MM-DD hh:mm:ss',
    //min: laydate.now(), //设定最小日期为当前日期
    max: '2099-06-16 23:59:59', //最大日期
    istime: true,
    istoday: false,
    choose: function(datas){
         end.min = datas; //开始日选好后，重置结束日的最小日期
         end.start = datas //将结束日的初始值设定为开始日
    }
};
var end = {
    elem: '#end',
    format: 'YYYY-MM-DD hh:mm:ss',
    min: laydate.now(),
    max: '2099-06-16 23:59:59',
    istime: true,
    istoday: false,
    choose: function(datas){
        start.max = datas; //结束日选好后，重置开始日的最大日期
    }
};
laydate(start);
laydate(end);

var start1 = {
    elem: '#start1',
    format: 'YYYY-MM-DD hh:mm:ss',
    //min: laydate.now(), //设定最小日期为当前日期
    max: '2099-06-16 23:59:59', //最大日期
    istime: true,
    istoday: false,
    choose: function(datas){
         end1.min = datas; //开始日选好后，重置结束日的最小日期
         end1.start1 = datas //将结束日的初始值设定为开始日
    }
};
var end1 = {
    elem: '#end1',
    format: 'YYYY-MM-DD hh:mm:ss',
    min: laydate.now(),
    max: '2099-06-16 23:59:59',
    istime: true,
    istoday: false,
    choose: function(datas){
        start1.max = datas; //结束日选好后，重置开始日的最大日期
    }
};
laydate(start1);
laydate(end1);
</script>
<script src="/Public/admin/layer/layer.js"></script>
<script>
$(document).ready(function(){
$p = $('#pa').val();
getpage($p);
});

	$('#myTabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
function getpage(p){
var shname = $('#shname').val();
var is_true = $('#is_true').val();
var state = $('#state').val();
if($('#start').val() == '' ){
	var start = 0;
}else{
	var start = $.myTime.DateToUnix($('#start').val());
}
var end = $.myTime.DateToUnix($('#end').val());
	$('#list').html('<div style="text-align:center;margin-top:30px;"><img src="/Public/admin/images/loading.gif" width="60px" ></div>');
	$("#list").load(
		"<?php echo U('order_ajax');?>?shname="+shname+"&p="+p+"&is_true="+is_true+"&state="+state+"&start="+start+"&end="+end,
		function() {}
	);
}
function down(){
	var start = $('#start').text();
	var end = $('#end').text();
	location.href="<?php echo U('order_excel');?>?start="+start+"&end="+end;
}
function del(obj,id){
	//alert(id);
	layer.confirm('删除后无法恢复，确认删除吗', {
		btn: ['确认','取消'] //按钮
	}, function(){
		$.ajax({
			type: "POST",
			url: "<?php echo U('del');?>?time="+new Date(),
			dataType: "json",
			data: {
				"id":id,
			},
			success: function(json){
				layer.msg("删除成功");
				var td = $(obj).parent();//alert(a);
				td.parent().css("display","none");	
			},
			error:function(){	
			}
		});
	}, function(){
		
	});
	
	
}

</script>