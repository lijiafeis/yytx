<?php if (!defined('THINK_PATH')) exit();?><table class="table table-bordered table-hover">
	<th width="5%">订单号</th>
	<th>创建时间</th>
	<th>收货人</th>
	<th>升级状态</th>
	<th>用户选择</th>
	<th>结果</th>
	<?php if(is_array($recordInfo)): $kk = 0; $__LIST__ = $recordInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk;?><tr id="<?php echo ($kk); ?>">
		<td width="5%"><?php echo ($vv["order_sn"]); ?></td>
		<td><?php echo (date("Y-m-d H:i:s",$vv["create_time"])); ?></td>
		<td><?php echo ($vv["name"]); ?></td>
		<?php if($vv["type"] == 1): ?><td width="30%">升级成功</td>
			<?php elseif($vv["type"] == 0): ?>
			<td width="30%">升级失败</td><?php endif; ?>
		<?php if($vv["user_select"] == 0): ?><td>藕</td>
			<?php elseif($vv["user_select"] == 1): ?>
			<td>鸡</td><?php endif; ?>
		<td><?php echo ($vv["game_number"]); ?></td>
	</tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>
<div class="pagelist"><?php echo ($page); ?></div>
<script>
	function confirm(order_id){
	    $.ajax({
	        type:"post",
	       url:"<?php echo U('orderConfirm');?>",
			dataType:"json",
			data:{
	            order_id:order_id,
			},
			success:function (data) {
				if(data == 0){
				    layer.msg('确认订单成功');
				    setTimeout(function () {
						history.go(0);
                    },1000);
				}else{
				    layer.msg('未知错误');
				}
            }
		});
	}
</script>