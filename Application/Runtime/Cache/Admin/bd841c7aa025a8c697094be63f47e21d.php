<?php if (!defined('THINK_PATH')) exit();?><table class="table table-striped table-hover" style="font-size:14px;table-layout:fixed">
			<th style="width:8%;">ID</th>
			<th style="width:;">名称</th>
			<th style="width:">所属分类</th>
			<!--<th style="width:50px;text-align:center;">特惠</th>-->
			<!--&lt;!&ndash;<th style="width:50px;text-align:center;">热销</th>&ndash;&gt;-->
			<th style="width:50px;text-align:center;">特殊</th>
			<!--<th style="width:50px;text-align:center;">上架</th>-->
			<th style="width:10%;text-align:center;">库存</th>
			<th>操作</th>
			<?php if(is_array($good_list)): $kk = 0; $__LIST__ = $good_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk;?><tr height="20px" style="overflow:hidden">
				<td><?php echo ($vv["good_id"]); ?></td>
				<td><?php echo ($vv["good_name"]); ?></td>
				<td  style="white-space: nowrap;text-overflow:ellipsis;overflow:hidden;"><button type="button" class="btn btn-link"><?php echo ($vv["cate_name"]); ?></button></td>
				<!--<td style="text-align:center;color:#777"><?php if($vv['best'] == 1): ?><input type="checkbox" checked="checked" class="icon-check icon-large" data_id="<?php echo ($vv["good_id"]); ?>" alt="<?php echo ($vv["best"]); ?>" data="best"/><?php else: ?><input type="checkbox" class="icon-check-empty icon-large" data_id="<?php echo ($vv["good_id"]); ?>" alt="<?php echo ($vv["best"]); ?>" data="best"/><?php endif; ?></td>-->
				<td style="text-align:center;color:#777"><?php if($vv['new'] == 1): ?><input type="checkbox" checked="checked" class="icon-check icon-large" data_id="<?php echo ($vv["good_id"]); ?>" alt="<?php echo ($vv["new"]); ?>" data="new"/><?php else: ?><input type="checkbox" class="icon-check-empty icon-large" data_id="<?php echo ($vv["good_id"]); ?>" alt="<?php echo ($vv["new"]); ?>" data="new"/><?php endif; ?></td>
				<td style="text-align:center;color:#777"><button type="button" class="btn btn-link"><?php echo ($vv["number"]); ?></button></td>
				<td>
				<a href="<?php echo U('goodedit');?>?good_id=<?php echo ($vv["good_id"]); ?>&p=<?php echo ($p); ?>"><button type="button" class="btn btn-default btn-sm">修改</button></a>
				<button type="button" class="btn btn-default btn-sm" onclick="del(this,'<?php echo ($vv["good_id"]); ?>')">删除</button></td>						
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