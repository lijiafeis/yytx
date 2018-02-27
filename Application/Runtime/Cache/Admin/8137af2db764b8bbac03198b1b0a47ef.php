<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/Public/css/font-awesome.min.css">
<link rel="stylesheet" href="/Public/css/weui.min.css">
<link rel="stylesheet" href="/Public/admin/css/base.css">
<link href="/Public/admin/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<style>
.btn-default{background:#44b549;color:#fff;}
.form-group1:hover{background:#fff;}
</style>
<div class="container-fluid main">
	<div class="main-top"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>商城管理--添加商品</div>
	<div class="main-content">
	<!-- Nav tabs -->
	  <ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">商品参数</a></li>
		<li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">商品缩略图</a></li>
		<li role="presentation"><a href="#game" aria-controls="game" role="tab" data-toggle="tab">商品缩略图</a></li>
		<li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">商品详情</a></li>
		
		<li role="presentation"><a href="javascript:void(0);" onclick="goLastPage()">返回上一页</a></li>
	  </ul>
<style>
.table tr td img{height:40px;cursor:hand;}
.code:hover{cursor:hand;}
.abc{display:inline-block;}
</style>
	  <!-- Tab panes -->
	  <form class="form-horizontal" action="/Admin/Goods/goodedit.html?good_id=9&amp;p=" method="post" onsubmit="" enctype="multipart/form-data" >
	  <div class="tab-content well" style="margin-top:30px;border:1px solid #dddddd;padding:10px 2%;">
	  
		<div role="tabpanel" class="tab-pane active" id="home">
			
			<div class="form-group">
				<label for="inputEmail3" class="col-sm-2 col-lg-1 control-label">商品名称</label>
				<div class="col-sm-6 col-lg-4">
					<input type="text" class="form-control" id="good_name" name="good_name" value="<?php echo ($good_info['good_name']); ?>" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="inputEmail3" class="col-sm-2 col-lg-1 control-label">商品排序</label>
				<div class="col-sm-6 col-lg-4">
					<input type="number" class="form-control" id="code1" name="code1" value="<?php echo ($good_info['code']); ?>" placeholder="">
				</div>
			</div>

			<div class="form-group has-success">
				<label for="inputEmail3" class="col-sm-2 col-lg-1 control-label">产品价格</label>
				<div class="col-sm-6 col-lg-4">

					<div class="col-sm-6 col-lg-6" style="margin-bottom:10px;">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon">规格</span>
							<input type="text" class="form-control" id="spec1" name="spec1" value="<?php echo ($good_info['spec'][0]['spec']); ?>" placeholder="一瓶装"> <input type="text" class="form-control" id="price1" name="price1" value="<?php echo ($good_info['spec'][0]['price']); ?>" placeholder="">
							<span class="input-group-addon" id="basic-addon1">元</span>
						</div>
						<div class="input-group" style="margin-top: 20px;">
							<span class="input-group-addon" id="basic-addon3">规格</span>
							<input type="text" class="form-control" id="spec2" name="spec2" value="<?php echo ($good_info['spec'][1]['spec']); ?>" placeholder="一件六瓶装"> <input type="text" class="form-control" id="price2" name="price2" value="<?php echo ($good_info['spec'][1]['price']); ?>" placeholder="">
							<span class="input-group-addon" id="basic-addon2">元</span>
						</div>
					</div>

				</div>

			</div>

			<div style="clear:both"></div>
			<div style="margin-top:30px"><button type="submit" class="btn btn-success">保存商品信息</button></div>
		</div>
		<div role="tabpanel" class="tab-pane" id="profile">
		<table class="table table-striped" style="font-size:14px;">
			<th>编号</th>
			<th>缩略图</th>
			<th>排序</th>
			<th>操作</th>
			<?php if(is_array($bannar)): $kk = 0; $__LIST__ = $bannar;if( count($__LIST__)==0 ) : echo "暂无幻灯片" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($kk % 2 );++$kk; if($vv): ?><tr>
				<td><?php echo ($vv["id"]); ?></td>
				<td ><img src="<?php echo ($vv); ?>" onclick="yulan(this)"></td>
				<td>
					<div class="code" data-toggle="tooltip" data-placement="bottom" title="点击修改排序" onclick="changeCode(this)"><?php echo ($vv["code"]); ?></div>
					<div class="form-inline" style="display:none;">
					<input type="text" class="form-control" style="width:50px;" name="code" value="<?php echo ($vv["code"]); ?>">
					<button type="button" class="btn btn-success btn-sm savecode" data="<?php echo ($vv["id"]); ?>">保存</button>
					</div>
					</td>
				<td class="text-left"><button type="button" class="btn btn-danger btn-sm" onclick="del(this,<?php echo ($good_info['id']); ?>,'<?php echo ($kk); ?>')">删除</button></td>
			  </tr><?php endif; endforeach; endif; else: echo "暂无幻灯片" ;endif; ?>
			</table>
			<Br/><br/>
			<div type="button" class="btn btn-warning" onclick="upload(this)">添加商品图片</div>
			
			<div style="margin:20px 0;" id="pic_img"></div>
			<input type="hidden" name="pic1" value="">
			<input type="hidden" name="pic2" value="">
			<input type="hidden" name="pic3" value="">
			<input type="hidden" name="pic4" value="">
			<input type="hidden" name="pic5" value="">
			<input type="hidden" name="pic6" value="">
			<input type="hidden" name="pic7" value="">
			<input type="hidden" name="pic8" value="">
			 <div style="clear:both"></div>
			 <div style="margin-top:30px"><button type="submit" class="btn btn-success">保存商品信息</button></div>
			  <div style="clear:both"></div>
			
		</div>

		  <div role="tabpanel" class="tab-pane" id="game">
			  <span style="margin-bottom:10px;color:red;">新上传的会覆盖老的</span>
			  <table class="table table-striped" style="font-size:14px;">

				  <th>缩略图</th>

				  <tr>

					  <td ><img src="<?php echo ($good_info['game_pic']); ?>" onclick="yulan(this)"></td>

				  </tr>

			  </table>
			  <Br/><br/>
			  <div type="button" class="btn btn-warning" onclick="upload1(this)">添加商品图片</div>

			  <div style="margin:20px 0;" id="pic_img1"></div>
			  <input type="hidden" name="pica1" value="">
			  <input type="hidden" name="pica2" value="">
			  <input type="hidden" name="pica3" value="">
			  <input type="hidden" name="pica4" value="">
			  <input type="hidden" name="pica5" value="">
			  <input type="hidden" name="pica6" value="">
			  <input type="hidden" name="pica7" value="">
			  <input type="hidden" name="pica8" value="">
			  <div style="clear:both"></div>
			  <div style="margin-top:30px"><button type="submit" class="btn btn-success">保存商品信息</button></div>
			  <div style="clear:both"></div>

		  </div>

		<div role="tabpanel" class="tab-pane" id="messages">
		<span style="margin-bottom:10px;color:red;">上传图片建议上传320 * 400</span>
		<div class="form-group">
			<div class="col-sm-12">
				
				<script id="editor" type="text/plain" name="good_desc" value=""  style="width:100%;height:500px;"><?php echo ($good_info['good_desc']); ?></script>
			</div>
		</div>
		<input type="hidden" class="form-control" id="hidden" name="id" value="<?php echo ($good_info['id']); ?>" placeholder="">
			<div style="margin-top:30px"><button type="submit" class="btn btn-success">保存商品信息</button></div>
		 <div style="clear:both"></div>
		</div>
		
	  </div>	
	</form>
		
			
			
			
			 
		
	</div>
</div>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/Public/admin/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/Public/ueditor/ueditor.config.js"></script>
    <!-- 编辑器源码文件 -->
<script type="text/javascript" src="/Public/ueditor/ueditor.all.js"></script>
<script src="/Public/admin/js/fileinput.js" type="text/javascript"></script>
<script src="/Public/admin/js/fileinput_locale_zh.js" type="text/javascript"></script>
<script src="/Public/admin/layer/layer.js"></script>
<script src="/Public/admin/js/ajaxfileupload.js"></script>
<script>
$(document).ready(function(){
	var val = $('#pid').val();
	change_pid(val);
	$('#pid').bind("change",function(){
		var v = $(this).val();
		change_pid(v);
  });
 $('.savecode').click(function(){
	var code = $(this).prev().val();
	var id = $(this).attr('data');
	var obj = $(this);
	$.ajax({
		type:'post',
		url:"<?php echo U('change_shop_pic');?>",
		dataType:'json',
		data:{'id':id,'code':code},
		success:function(){
			$(obj).parent().css("display","none");
			$(obj).parent().prev().css("display","block");
			$(obj).parent().prev().text(code);
		},
		error:function(){
			layer.msg('通信通道发生错误！刷新页面重试！');
		}
	});
	
  });
});
function change_pid(v){
}
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
function changeCode(obj){
	$(obj).css("display","none");
	$(obj).next().next().css("display","block");
}
 var ue = UE.getEditor('editor');
  imagePathFormat: "/images/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}";
  /*
  ["good_name"] => string(12) "的淡淡的"
  ["cate_pid"] => string(1) "1"
  ["code"] => string(1) "3"
  ["id"] => string(0) ""
  ["good_desc"]
  */
function check(){
	if($('#good_name').val()==""){layer.msg("商品名称不能为空");return false;}
	if($('#cate_pid').val()==""){layer.msg("商品必须选择分类");return false;}
	if($('#good_price').val()==""){layer.msg("商品价格不能为空");return false;}
	if($('#market_price').val()==""){layer.msg("市场价格不能为空");return false;}
	if($('#number').val()==""){layer.msg("库存不能为空");return false;}
}

function goLastPage(){
	location.href = "<?php echo U('good');?>";
}

function del(obj,id,index1){
	layer.confirm('确定要删除这条数据吗？', {
	  btn: ['确定','取消'] //按钮
	}, function(){
	    var index = layer.load(2,{
	        shade:[0.6,"#000"]
		});
	  $.ajax({
			type:'post',
			url:"<?php echo U('del_good_pic');?>",
			dataType:'json',
			data:{'good_id':id,'index':index1},
			success:function(){
			    layer.close(index)
				layer.msg('删除成功', {icon: 1});
				$(obj).parent().parent().remove();
			},
			error:function(){
			    layer.close(index);
				layer.msg('通信通道发生错误！刷新页面重试！');
			}
		});
	}, function(){
	  
	});
	
}
	
</script>
<script>
var num = 1;
var num1 = 1;
function upload(){
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
function upload1(){
//alert(num);
    if(num1 > 1){layer.msg('单次最多支持上传8张！');exit;}
    layer.open({
        type: 2,
        skin:'demo',
        title:'上传图片--西瓜科技上传插件',
        area: ['520px', '430px'],
        fix: false, //不固定
        //maxmin: true,
        content: "<?php echo U('Img/index');?>?id=pica"+num1,
        end: function(){
            if($("input[name='pica"+num1+"']").val() != ''){
                var url = $("input[name='pica"+num1+"']").val();
                num++;
                $('#pic_img1').append('<div class="col-sm-3 col-lg-2"><img src="'+url+'" alt="..." class="img-rounded" width="100%"></div>');
            }

        }
    });
}
</script>