<?php if (!defined('THINK_PATH')) exit();?><table class="table table-bordered table-hover">
	<th width="5%">订单号</th>
	<th>支付时间</th>
	<th>会员ID</th>
	<th>收货人</th>
	<th>总金额</th>
	<th>支付状态</th>
	<th>发货状态</th>
	<th>操作</th>
	<?php if(is_array($order_info)): $kk = 0; $__LIST__ = $order_info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk;?><tr id="<?php echo ($kk); ?>">
		<td width="5%"><?php echo ($vv["order_sn"]); ?></td>
		<td><?php echo (date("Y-m-d H:i:s",$vv["pay_time"])); ?></td>
		<td><?php echo ($vv["user_id"]); ?></td>
		<td width="30%"><?php echo ($vv["address"]); ?></td>
		<td><?php echo ($vv["price"]); ?></td>
		<td><?php if($vv["state"] == 1 ): ?>已支付<?php else: ?> <font color="red">未支付</font><?php endif; ?></td>
		<td>
		<?php if($vv["state"] == 1 ): if($vv["type"] == 0 ): ?><font color="red" size="4">待发货</font><?php endif; ?>
		<?php if($vv["type"] == 1 ): ?><font color="#000" size="4">待收货</font><?php endif; ?>
		<?php if($vv["type"] == 2 ): ?><font color="#555">已收货</font><?php endif; ?>
		<?php else: ?>
		<font color="#555">未支付</font><?php endif; ?>
		</td>
		<td>
		<a href="<?php echo U('order_more');?>?pay_id=<?php echo ($vv["id"]); ?>&page=<?php echo ($p); ?>"><button class="btn btn-default btn-sm">详情</button></a>
		</td>
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