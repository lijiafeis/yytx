function xigua_upload(xigua){
	//获取指定文件夹内文件数量，并返回文件文件地址
	//alert(xigua.nums);exit;
	$.ajax({
		url:xigua.a_url,
		type:'post',
		dataType:'json',
		data:{address:xigua.pic_address,num:xigua.nums},
		success:function(json){
			//page(json.num,json.pic,xigua.nums);
			//加载
			laypage({
				cont: xigua.pageid, //容器。值支持id名、原生dom对象，jquery对象,
				pages: json.num, //总页数
				skin: 'yahei', //皮肤
				first: 1, //将首页显示为数字1,。若不显示，设置false即可
				last: 11, //将尾页显示为总页数。若不显示，设置false即可
				prev: '<', //若不显示，设置false即可
				next: '>', //若不显示，设置false即可
				jump: function(obj){
				  // alert(obj.curr);exit;
				  getpicdata(json.pic,obj.curr,xigua.nums,xigua.cid);
				}
			});
		},
		error:function(){
			layer.msg('请求远程图片发生错误！');
		}
	});
}
function select(obj){
	if($(obj).find('.select').css('display') == 'block'){$('.enter').attr('data','');$('.enter').button('loading');$(obj).find('.select').css('display','none');exit;}
	$('.select').each(function(){
		$(this).css('display','none');
	});
	$(obj).children().css('display','block');
	$('.enter').attr('data',$(obj).find('img').attr('src'));$('.enter').button('reset');
}


function getpicdata(data,curr,nums,cid){
	var str = '', last = curr*nums - 1;
	last = last >= data.length ? (data.length-1) : last;
	for(var i = (curr*nums - nums); i <= last; i++){
		str += '<div class="col-xs-3 text-center" onclick="select(this)"><div class="select" style=""><i class="icon-ok-sign icon-3x"></i> </div><img src="/'+ data[i] +'" width="100px" height="100px"></div>';
	}
	$("#"+cid).html(str);
}

