<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/Public/css/font-awesome.min.css">
<link rel="stylesheet" href="/Public/admin/css/base.css">
<link href="/Public/admin/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<style>
.table tr td{height:30px;}
.icon-large:hover{cursor:hand;}
</style>
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>商城管理</div>
	<div class="main-content">
			<div style="margin-bottom:20px;"><a href="<?php echo U('goodadd');?>"><button type="button" class="btn btn-warning" >添加新商品</button></a></div>
			<div id="list">
			</div>		  
	</div>
	<input type="hidden" name="pa" id="pa" value="<?php echo ($p); ?>">
</div>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/Public/admin/js/bootstrap.min.js"></script>

<script src="/Public/admin/js/fileinput.js" type="text/javascript"></script>
<script src="/Public/admin/js/fileinput_locale_zh.js" type="text/javascript"></script>
<script src="/Public/admin/layer/layer.js"></script>
<script>
$(document).ready(function(){
$p = $('#pa').val();
getpage($p);
});
function getpage(p){
	$('#list').html('<div style="text-align:center;margin-top:30px;"><img src="/Public/admin/images/loading.gif" width="60px" ></div>');
	$("#list").load(
		"<?php echo U('good_ajax');?>?p="+p,
		function() {}
	);
}


	function del(obj,i){
		layer.confirm('确定删除？', {
			btn: ['是，确认','否，再看看'] //按钮
		}, function(){
		    var index = layer.load(2,{
		        shade:[0.6,"#000"]
			});
			$.ajax({
					type: "POST",
					url: "<?php echo U('delgood');?>",
					dataType: "json",
					data: {"id":i},
					success: function(json){
					    layer.close(index)
						if(json.success==1){
							$(obj).parent().parent().remove();layer.msg('删除成功', {icon: 1});
						}else{
							layer.msg("处理失败，请重新尝试");				
						}
					},
					error:function(){
                        layer.close(index)
						layer.msg("发生异常！");
					}
				});
		}, function(){
			
		});
	}
</script>