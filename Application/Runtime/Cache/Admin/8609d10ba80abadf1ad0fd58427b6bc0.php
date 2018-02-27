<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/Public/bootstrap/css/font-awesome.min.css">
<link rel="stylesheet" href="/Public/admin/css/base.css">
<link href="/Public/admin/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>订单管理</div>
	<div class="main-content">
<style>
.col-sm-6{margin:5px 0;border-bottom:1px solid #f8f8f8;font-size:14px;}
.col-sm-6 span{font-weight:bold;color:#777}
</style>
		<div>
		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">订单详情</a></li>		    
		    <li role="presentation"><a href="javascript:void(0);" onclick="goLastPage(<?php echo ($p); ?>)">返回上一页</a></li>		    
		  </ul>
		  
		  <!-- Tab panes -->
		  <div class="tab-content" style="margin-top:30px;border:1px solid #dddddd;padding:10px 2%;">
		    <div role="tabpanel" class="tab-pane active" id="home">
				<div class="bg-success" style="padding:10px;margin:5px 0;">基本信息</div>
				<div class="col-sm-6"><span>订单号：</span><?php echo ($order['id']); ?></div>
				<div class="col-sm-6"><span>订单状态：</span>
				<?php if($order["type"] == 0 ): if($order["game_type"] == 0 ): ?><font color="blue" size="4">未提货</font>
					<?php elseif($order["game_type"] == 1): ?>
						<font color="red" size="4">已提货,未发货</font>
					<?php elseif($order["game_type"] == 2): ?>
						<font color="black" size="4">已退货</font><?php endif; endif; ?>
					<?php if($order["type"] == 1 ): ?><font color="#000" size="4">待收货</font><?php endif; ?>
					<?php if($order["type"] == 2 ): ?><font color="#555">已收货</font><?php endif; ?></div>
				<div class="col-sm-6"><span>购买人ID：</span>[<?php echo ($order['user_id']); ?>]</div>
				<div class="col-sm-6"><span>支付时间：</span><?php echo (date("Y-m-d H:i:s",$order['pay_time'])); ?></div>
				<div style="clear:both;"></div>
				<div class="bg-success" style="padding:10px;margin:5px 0;">收货人信息</div>
				<div class="col-sm-6"><span>地址</span><?php echo ($order['address']); ?></div>
				<div style="clear:both;"></div>
				<div class="bg-success" style="padding:10px;margin:5px 0;">商品信息</div>
				<table class="table table-striped" style="font-size:14px;">
					<th>商品ID</th>
					<th>商品名称</th>
					<th>缩略图</th>
					<th>规格</th>
					<th>数量</th>
					<th>金额</th>
					<th></th>
					<tr>
						<td><?php echo ($order["good_id"]); ?></td>
						<td><?php echo ($order["good_name"]); ?></td>
						<td><img src="<?php echo ($order["good_pic"]); ?>" width="30px"></td>
						<td><?php echo ($order["good_spec"]); ?></td>
						<td><?php echo ($order["good_num"]); ?></td>
						<td>￥<?php echo ($order["price"]); ?></td>
					</tr>
					<tr><td style="color:red">订单总金额 ￥<?php echo ($order["price"]); ?>
						&nbsp;&nbsp;&nbsp;
						<?php if($order["fxmoney"] != 0): ?><span>复消积分抵消:<?php echo ($order["fxmoney"]); ?></span><?php endif; ?>
						<?php if($order["money"] != 0): ?><span>余额抵消:<?php echo ($order["money"]); ?></span><?php endif; ?>
					</td>

					</tr>
					<?php if($info != null): ?><tr><td style="color:red">代金卷</td><td><?php echo ($info["title"]); ?></td><td><?php echo ($info["money"]); ?></td></tr><?php endif; ?>
				</table>
				<div class="form-horizontal" style="height: 400px">

						<div class="form-group">
							<label for="inputEmail3" class="col-sm-2 control-label">物流订单号</label>
							<div class="col-sm-6">
							<?php if($order["kd_number"] == 0): ?><input type="text"  class="form-control" value="" name="order_sn" id="kd_number" />
							<?php else: ?>
							<input type="text"  class="form-control" value="<?php echo ($order["kd_number"]); ?>" name="order_sn" id="kd_number" /><?php endif; ?>
								
							</div>
						</div>

						<div class="form-group">
							<label for="inputEmail3" class="col-sm-2 control-label">物流选择</label>
							<div class="col-sm-6">
								<select name="kd" class="form-control" id="kd">
									<option value="-1" >快递选择</option>
									<option value="SF" <?php if($order["kd_name"] == SF): ?>selected<?php endif; ?>>顺丰快递</option>
									<option value="STO" <?php if($order["kd_name"] == STO): ?>selected<?php endif; ?>>申通快递</option>
									<option value="YD"  <?php if($order["kd_name"] == YD): ?>selected<?php endif; ?>>韵达快递</option>
									<option value="YTO" <?php if($order["kd_name"] == YTO): ?>selected<?php endif; ?>>圆通速递</option>
									<option value="ZJS" <?php if($order["kd_name"] == ZJS): ?>selected<?php endif; ?>>宅急送</option>
									<option value="ZTO" <?php if($order["kd_name"] == ZTO): ?>selected<?php endif; ?>>中通速递</option>
									<option value="AMAZON" <?php if($order["kd_name"] == AMAZON): ?>selected<?php endif; ?>>亚马逊物流</option>
								</select>
							</div>
						</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">手动输入</label>
						<div class="col-sm-6">
							<input type="text" placeholder="请手动输入物流名称" id="sdwuliu"/>
						</div>
					</div>
					<div style="width: 100%;text-align: center;">
					<button onclick="fahuo(<?php echo ($order["id"]); ?>,<?php echo ($order["type"]); ?>,<?php echo ($order["game_type"]); ?>)" style="width: 90px;height: 40px;border: 0px; border-radius: 4px;">确认发货</button>
					<!--<button onclick="shouhuo(<?php echo ($order["id"]); ?>,<?php echo ($order["type"]); ?>)" style="width: 90px;height: 40px;border: 0px; border-radius: 4px;">确认收货</button>-->
					<button onclick="wuliu(<?php echo ($order["id"]); ?>,<?php echo ($order["type"]); ?>)" style="width: 90px;height: 40px;border: 0px; border-radius: 4px;">查看物流</button>
					</div>
				</div>
				<!--<div class="bg-success" style="padding:10px;margin:5px 0;">操作信息</div>-->

		    </div>
		
		  </div>
		</div>
		
	</div>
</div>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/Public/admin/js/bootstrap.min.js"></script>
<script src="/Public/admin/js/fileinput.js" type="text/javascript"></script>
<script src="/Public/admin/js/fileinput_locale_zh.js" type="text/javascript"></script>
<script src="/Public/admin/layer/layer.js"></script>
<script>
	$('#myTabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
	function fahuo(order_id,type,game_type) {
		if(type != 0){

            kd_number = $('#kd_number').val();
            kd_name = $('#kd').val();
			sdwuliu = $("#sdwuliu").val();
			   $.ajax({
				type:'post',
				url:"<?php echo U('fahuoxiugai');?>",
				dataType:'json',
				data:{
                    kd_number:kd_number,
                    kd_name:kd_name,
                    sdwuliu:sdwuliu,
                    order_id:order_id
				},
				success:function (data) {
					if(data == 0){
						layer.msg('修改成功');
						setTimeout(function () {
							history.go(-1);
						},1000);
					}else{
						layer.msg('发货失败');
					}
				}
			});
				return;
		}
        kd_number = $('#kd_number').val();
		kd_name = $('#kd').val();
        sdwuliu = $("#sdwuliu").val();
		if(kd_number == null || kd_name == -1){
		    if(sdwuliu == ''){
                layer.msg('请填写物流信息');
                return;
			}
		}
		if(game_type == 0){
		    //当前订单还没有提货，也就是用户没有玩
			layer.msg('当前订单还未提货');return;
		}else if(game_type == 2){
		    //当前订单用户已退款，不能发货
			layer.msg('当前订单已退款，不能发货');return;
		}
		var index = layer.load(2,{
		    shade:[0.6,"#000"]
		})
		$.ajax({
		    type:'post',
			url:"<?php echo U('fahuo');?>",
			dataType:'json',
			data:{
                kd_number:kd_number,
                kd_name:kd_name,
				sdwuliu:sdwuliu,
				order_id:order_id
			},
			success:function (data) {
		        layer.close(index);
				if(data == 0){
				    layer.msg('发货成功');
                    setTimeout(function () {
                        history.go(-1);
                    },1000);
				}else{
				    layer.msg('发货失败');
				}
            },
			error:function (data) {
				layer.close(index);
				layer.msg('网络错误');
            }
		});
    }

    function wuliu(order_id,type) {
		if(type == 0){
		    layer.msg('请先发货');
			return;
		}
        location.href = "<?php echo U('showWuliu');?>?order_id=" + order_id;
    }
	
	function goLastPage(p){
		location.href = "<?php echo U('gameOrder');?>?p=" + p;
	}

</script>