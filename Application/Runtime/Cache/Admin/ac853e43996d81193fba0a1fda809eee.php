<?php if (!defined('THINK_PATH')) exit();?><table class="table table-striped table-hover" style="font-size:14px;table-layout:fixed">
			<th>ID</th>
			<th>名称</th>
			<th>创建时间</th>
			<th>库存</th>
			<th>游戏</th>
			<th>排序</th>
			<th>操作</th>
			<?php if(is_array($good_list)): $kk = 0; $__LIST__ = $good_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk;?><tr height="20px" style="overflow:hidden">
				<td><?php echo ($vv["id"]); ?></td>
				<td><?php echo ($vv["good_name"]); ?></td>
				<td><button type="button" class="btn btn-link"><?php echo (date("Y-m-d H:i:s",$vv["time"])); ?></button></td>
				<td>∞</td>
				<td><?php if($vv['is_game'] == 1): ?><input type="checkbox" checked="checked" class="icon-check icon-large" data_id="<?php echo ($vv["id"]); ?>" alt="<?php echo ($vv["is_game"]); ?>" data="is_game"/><?php else: ?><input type="checkbox" class="icon-check-empty icon-large" data_id="<?php echo ($vv["id"]); ?>" alt="<?php echo ($vv["is_game"]); ?>" data="is_game"/><?php endif; ?></td>
				<td><?php echo ($vv["code"]); ?></td>
				<td>
				<a href="<?php echo U('goodedit');?>?good_id=<?php echo ($vv["id"]); ?>&p=<?php echo ($p); ?>"><button type="button" class="btn btn-default btn-sm">修改</button></a>
				<button type="button" class="btn btn-default btn-sm" onclick="del(this,'<?php echo ($vv["id"]); ?>')">删除</button></td>
			</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</table>
		<div class="pagelist"><?php echo ($page); ?></div>
<script>
$('.icon-large').click(function(){
	var good_id = $(this).attr("data_id");//alert(good_id);
	var type = $(this).attr("data");//alert(type);
	var type_id = $(this).attr("alt");//alert(type_id);
	$(this).attr("id","change");
	
	$.ajax({
		type: "POST",
		url: "<?php echo U('changetype');?>",
		dataType: "json",
		data: {"good_id":good_id,"type_id":type_id,"type":type},
		success: function(json){
			if(json.success==1){
				if(json.type == 1){
					$('#change').removeClass('icon-check-empty');$('#change').addClass('icon-check');$('#change').attr('alt','1');
				}else{
					$('#change').removeClass('icon-check');$('#change').addClass('icon-check-empty');$('#change').attr('alt','0');
				}
				$('#change').attr("id","");	
			}else{
				layer.msg("处理失败，请重新尝试");				
			}
		},
		error:function(){
			layer.msg("发生异常！");
		}
	});
});
</script>