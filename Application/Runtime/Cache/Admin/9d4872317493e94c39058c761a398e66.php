<?php if (!defined('THINK_PATH')) exit();?><!--<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">-->
<link rel="stylesheet" href="/Public/admin/css/base.css">
<link rel="stylesheet" href="/Public/css/bootstrap.min.css">
<link href="/Public/admin/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<!--<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">-->
<link rel="stylesheet" href="/Public/admin/css/base.css">
<!--<link href="/Public/admin/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />-->
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>订单列表</div>
		<div class="main-content">
			<div>
				<div class="well">
					<div class="btn-group" style="">

						<!--<button type="button" class="btn btn-default province all" style="margin:3px 0;" onclick="select_province(this,'17')">所有会员</button>-->
					</div><br/>
					<!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="home">
							<div class="alert alert-success" style="padding:5px 10px;margin:15px 0;line-height:30px;">
								<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
									<div class="input-group input-group">
										<span class="input-group-addon" id="sizing-addon1">订单号</span>
										<input type="text" class="form-control" name="order_sn" id="order_sn"  value="<?php echo ($data[ddh]); ?>" disabled aria-describedby="sizing-addon1">
									</div>
								</div>
								<div class="col-lg-3 col-md-3" style="padding:0;margin:3px;">
									<div class="input-group input-group">
										<span class="input-group-addon" id="sizing-addon1">快递公司</span>
										<input type="text" class="form-control" name="order_sn" id="order_sn"  value="<?php echo ($data[kd]); ?>" disabled aria-describedby="sizing-addon1">
									</div>
								</div>

				</div>
						</div>
					</div>
				</div>
				<input type="hidden" name="order" id="order"/>
			<!-- Tab panes -->
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="home">
					<div id="list">
						<table class="table table-striped"  style="font-size:14px;margin-top: 20px;">

							<th>时间</th>
							<th>信息</th>

							<?php if(is_array($info)): $kk = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "$empty" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk;?><tr id="<?php echo ($kk); ?>" style="font-size:13px;">
									<td><?php echo ($vv["time"]); ?></td>
									<td><?php echo ($vv["info"]); ?></td>
								</tr><?php endforeach; endif; else: echo "$empty" ;endif; ?>
						</table>
					</div>
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
<script src="/Public/admin/laydate/laydate.js"></script>
<script>
var cid = '';
var city_name = '';
$(document).ready(function(){
getpage(1)
});



$('.all').click()
$('body').on('click','.all_check',function(){
    if($(this).is(':checked')){
        $('body .one_check').each(function(){
            $(this).prop('checked','checked')
        })
    }else{
        $('body .one_check').each(function(){
            $(this).removeAttr('checked')
        })
    }
});

function createMan() {
	location.href="<?php echo U('create');?>"
}
</script>