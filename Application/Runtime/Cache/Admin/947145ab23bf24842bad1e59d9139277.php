<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/Public/css/font-awesome.min.css">

<link rel="stylesheet" href="/Public/admin/css/base.css">
<style>
.btn-default{background:#44b549;color:#fff;}
.form-group1:hover{background:#fff;}
</style>
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>商城管理--商城设置</div>
	<div class="main-content">
  <!-- Nav tabs -->
	  <ul class="nav nav-tabs" role="tablist">
		<!--<li role="presentation" class="active"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">首页广告位</a></li>-->
		<li role="presentation" class="active"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">首页广告位</a></li>
		<!--<li role="presentation"><a href="#fenxiao" aria-controls="fenxiao" role="tab" data-toggle="tab">分销设置</a></li>-->
		<!--<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">支付参数</a></li>-->
	  </ul>
<style>
.table tr td img{height:40px;cursor:hand;}
.code:hover{cursor:hand;}
</style>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/Public/admin/js/bootstrap.min.js"></script>
<script src="/Public/admin/layer/layer.js"></script>
<script>
var num = 1;
var ad_num = 11;
function upload(obj){
//alert(num);
if(num > 8){layer.msg('单次最多支持上传8张！');exit;}
	layer.open({
	  type: 2,
	  skin:'demo',
	  title:'上传图片--西瓜科技上传插件',
	  area: ['520px', '430px'],
	  fix: false, //不固定
	  //maxmin: true,
	  content: "<?php echo U('Img/index');?>?id=pic"+num,
	  end: function(){
			if($("input[name='pic"+num+"']").val() != ''){
			var url = $("input[name='pic"+num+"']").val();
			num++;
			$('#pic_img').append('<div class="col-sm-3 col-lg-2"><img src="'+url+'" alt="..." class="img-rounded" width="100%"></div>');
			}
			
		}
	});
}
function ad_upload(obj){
//alert(num);
if(ad_num > 11){layer.msg('一个链接地址对应添加一个广告图片！');exit;}
	layer.open({
	  type: 2,
	  skin:'demo',
	  title:'上传图片--西瓜科技上传插件',
	  area: ['520px', '430px'],
	  fix: false, //不固定
	  //maxmin: true,
	  content: "<?php echo U('Img/index');?>?id=pic"+ad_num,
	  end: function(){
			if($("input[name='pic"+ad_num+"']").val() != ''){
			var url = $("input[name='pic"+ad_num+"']").val();
			ad_num++;
			$('#ad_img').append('<div class="col-sm-3 col-lg-2"><img src="'+url+'" alt="..." class="img-rounded" width="100%"></div>');
			}
			
		}
	});
}

function change_exist(value){
//	$('.exist').each(function(){$(this).css('display','none');});
//	$('#exist'+value).css('display','block');
}
function get_level(p){
	$('#level_list').html('<div style="text-align:center;margin-top:30px;"><img src="/Public/admin/images/loading.gif" width="60px" ></div>');
	$("#level_list").load(
		"<?php echo U('setting_fenxiao');?>?level="+p,
		function() {}
	);
}
function search(good_id){
if(good_id == null){
	var keyword = $('#good_name').val();if(keyword == ''){layer.msg('请输入商品名称再查询');exit;}
	var good_id = '';
}
	$('#good_list').html('<div style="text-align:center;margin-top:30px;"><img src="/Public/admin/images/loading.gif" width="60px" ></div>');
	$("#good_list").load(
		"<?php echo U('fenxiao_good_list');?>?keyword="+keyword+"&good_id="+good_id,
		function() {}
	);
}
</script>


<link href="/Public/status/switch/highlight.css" rel="stylesheet">
<link href="/Public/status/switch/bootstrap-switch.min.css" rel="stylesheet">
<script src="/Public/status/switch/highlight.js"></script>
<script src="/Public/status/switch/bootstrap-switch.min.js"></script>
<script src="/Public/status/switch/main.js"></script>
	  <!-- Tab panes -->
	  <!--<div class="tab-content well" style="margin-top:30px">-->
<!---->
		<!--<div role="tabpanel" class="tab-pane" id="profile">-->
		
		<table class="table table-striped" style="font-size:14px;">
			<th>编号</th>
			<th>缩略图</th>
			<!--<th style="width:40%;">广告地址</th>-->
			<!--<th>排序</th>-->
			<th>操作</th>
			<div style="clear:both"></div>
			<?php if(is_array($ad)): $kk = 0; $__LIST__ = $ad;if( count($__LIST__)==0 ) : echo "$empty" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk;?><tr>
				<td><?php echo ($vv["id"]); ?></td>
				<td ><img src="<?php echo ($vv["pic_url"]); ?>" onclick="yulan(this)"></td>
				<td style="white-space: normal;text-overflow:ellipsis;overflow:hidden;font-size:12px;text-decoration:underline;width:40%;"><?php echo ($vv["link"]); ?></td>
				<!--<td>-->
					<!--<div class="code" data-toggle="tooltip" data-placement="bottom" title="点击修改排序" onclick="changeCode(this)"><?php echo ($vv["code"]); ?></div>-->
					<!--<div class="form-inline" style="display:none;">-->
					<!--<input type="text" class="form-control" style="width:50px;" name="code" value="<?php echo ($vv["code"]); ?>">-->
					<!--<button class="btn btn-success btn-sm savecode" data="<?php echo ($vv["id"]); ?>" alt="ad">保存</button>-->
					<!--</div>-->
					<!--</td>-->
				<td class="text-left">
				<button class="btn btn-success btn-sm" onclick="edit('<?php echo ($vv["id"]); ?>','<?php echo ($vv["link"]); ?>')">修改</button>
				<button class="btn btn-danger btn-sm" onclick="del(this,'<?php echo ($vv["id"]); ?>','ad')">删除</button>
				</td>
			  </tr><?php endforeach; endif; else: echo "$empty" ;endif; ?>
			</table>
			 <div class="col-sm-12 text-center" id="ad-notice" style="margin-top:30px;border-top:2px solid #f6f6f6;line-height:50px;display:none;">
			 <button class="btn btn-link">修改下方信息后，点击保存完成修改流程，如果不更换广告图片无需重复上传图片</button>
			 </div>
			  <div style="clear:both"></div>
			 
			<form action="/Admin/Shop/setting.html" method="post"  enctype="multipart/form-data">
			<div class="form-group" style="margin-top:30px;">
				 <label for="inputPassword3" class="col-sm-2 col-lg-1 control-label">广告链接地址</label>
				<div class="col-sm-6">
					 <input type="text" class="form-control" id="ad-link" name="link" value="" placeholder="不要忘记加上 http:// 或 https://">
				</div>
			 </div>
			<input type="hidden" name="pic11" value="">
			<input type="hidden" name="ad" value="1" >
			 <div style="clear:both"></div><Br/>
			  <div type="button" class="btn btn-warning" onclick="ad_upload(this)">添加广告图片</div>
			<span style="color:#999">建议图片为宽幅，宽640px * 高120px</span>
			<div style="margin:20px 0;" id="ad_img"></div>
			<div style="clear:both"></div>
			 <div style="margin-top:30px"><button type="submit" class="btn btn-success">保存</button></div>
			 </form>
			  
			  
			  
		
		<!--</div>-->
<!---->
	  <!--</div>	-->
	</div>
</div>

<script>
    function changeCode(obj) {
        $(obj).css("display", "none");
        $(obj).next().next().css("display", "block");
    }
    function edit(id, link) {
        $('#ad-notice').css("display", "block");
        $('#ad-link').val(link);
        $('#ad-edit').val(id);
    }

</script>
<script>
    function del(obj, id, type) {
        layer.confirm('确定要删除这条数据吗？', {
            btn: ['确定', '取消'] //按钮
        }, function () {
            $.ajax({
                type: 'post',
                url: "<?php echo U('del_shop_bannar');?>",
                dataType: 'json',
                data: {'id': id, 'type': type},
                success: function () {
                    layer.msg('删除成功', {icon: 1});
                    $(obj).parent().parent().remove();
                },
                error: function () {
                    layer.msg('通信通道发生错误！刷新页面重试！');
                }
            });
        }, function () {

        });
    }
</script>