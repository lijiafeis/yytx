<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/yytx/Public/bootstrap/css/font-awesome.min.css">
<link rel="stylesheet" href="/yytx/Public/admin/css/base.css">
<link href="/yytx/Public/admin/css/jquery.minicolors.css" rel="stylesheet" type="text/css" />
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>商城管理--分类管理</div>
	<div class="main-content">
<style>
.table tr td{font-size:12px;vertical-align:middle;}
.form-control{font-size:8px;}
</style>
<div>
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">分类列表</a></li>		    		    	    
  </ul>
  
  <!-- Tab panes -->
  <div class="tab-content">
	<div role="tabpanel" class="tab-pane active" id="home">
		<div class="alert alert-success" style="padding:5px 10px;margin:15px 0;line-height:30px;">
			创建一个属性类，然后加入各个具体属性
		</div>
		<div class="well">
			<a href="<?php echo U('type_add');?>" class="btn btn-warning" >添加产品类型</button></a><br/><br/>
			
			<table class="table table-striped" style="font-size:14px;">
			<th>ID</th>
			<th>产品类型名称</th>
			<th>包含属性</th>
			<th>操作</th>
			<?php if(is_array($info)): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($i % 2 );++$i;?><tr>
				<td><?php echo ($vv["type_id"]); ?></td>
				<td><?php echo ($vv["type_name"]); ?></td>
				<td>
				<button class="btn btn-link btn-sm">
				<?php echo ($vv["name"]); ?>
				</button>
				</td>
				<td>
				<a href="<?php echo U('type_spec');?>?id=<?php echo ($vv["type_id"]); ?>"><button class="btn btn-success btn-sm">编辑属性</button></a>
				<a href="<?php echo U('type_add');?>?id=<?php echo ($vv["type_id"]); ?>"><button class="btn btn-warning btn-sm">修改</button></a>
				<button class="btn btn-danger btn-sm" onclick="del(this,'<?php echo ($vv["type_id"]); ?>','type_del')">删除</button>
				</td>
			</tr><?php endforeach; endif; else: echo "" ;endif; ?>
			</table>
			<div class="pagelist"><?php echo ($page); ?></div>
		</div>
		
		
	</div>
  </div>
</div>
		
	</div>
</div>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/yytx/Public/admin/js/bootstrap.min.js"></script>
<script src="/yytx/Public/admin/layer/layer.js"></script>
<script src="/yytx/Public/admin/js/jquery.minicolors.min.js" type="text/javascript"></script>
<script>
	$('#myTabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})


function del(obj,id,url){
	layer.confirm('删除后无法恢复，确认删除吗', {
		btn: ['确认','取消'] //按钮
	}, function(){
		$.ajax({
			type: "POST",
			url: "<?php echo U('"+url+"');?>?time="+new Date(),
			dataType: "json",
			data: {
				"id":id,
			},
			success: function(json){
				if(json.success == 1){
					layer.msg("删除成功");
					var td = $(obj).parent();//alert(a);
					td.parent().css("display","none");	
				}else{
					layer.msg(json.info);
				}
				

			},
			error:function(){
				alert('发生错误');
			}
		});
	}, function(){
		
	});	
}

</script>