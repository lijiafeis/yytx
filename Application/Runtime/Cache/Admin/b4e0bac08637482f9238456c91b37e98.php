<?php if (!defined('THINK_PATH')) exit();?><table class="table table-bordered table-hover">
	<th width="5%">用户id</th>
	<th>用户名</th>
	<th>金额</th>
	<th>订单号</th>
	<th>时间</th>
	<?php if(is_array($refundInfo)): $kk = 0; $__LIST__ = $refundInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk;?><tr id="<?php echo ($kk); ?>">
		<td width="5%"><?php echo ($vv["user_id"]); ?></td>
		<td><?php echo ($vv["name"]); ?></td>
		<td><?php echo ($vv["money"]); ?></td>
		<td><?php echo ($vv["order_sn"]); ?></td>
		<td><?php echo (date("Y-m-d H:i:s",$vv["create_time"])); ?></td>

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