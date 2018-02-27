var page_load = '<div class="weui-loadmore load_more" id="bb" data-page="1" data-state="1" style="display: block;"><i class="weui-loading"></i><span class="weui-loadmore__tips">正在加载</span></div>';
var page_none = '<div class="weui-loadmore weui-loadmore_line"><span class="weui-loadmore__tips" style="background: #fff;">已加载完毕</span></div>';
$(function(){
	if($(document).scrollTop() == 0 && $(document).height()  ==  $(window).height()){
		/*首次打开页面加载*/
		get_page()
	}
	$(window).scroll(function() {
                //$(document).scrollTop() 获取垂直滚动的距离、$(document).scrollLeft() 这是获取水平滚动条的距离
                if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                	//$('body').append('下滑了')
                	get_page()
                }
            });
})
function get_page(p){
	var load_more = scroll_data.parentId.find('.load_more')
	if(load_more.length == 0){
		scroll_data.parentId.append(page_load)
		load_more = scroll_data.parentId.find('.load_more')
	}
	// 判断上一个请求是否执行完
             var state = load_more.data('state')
             if(state == 0){return}else{load_more.data('state',0)}
             load_more.show()
            if(p){
                 var page = p
            }else{
                 var page = load_more.data('page')
            }
            
             $.post(scroll_data.url+"p="+page,scroll_data.t_data,function(data){
             	load_more.hide().data('page',page*1+1).before(data)
                    	if(data == ''){
                    		load_more.before(page_none)
                    	}else{
                    		load_more.data('state',1)
                    	}
             })
}