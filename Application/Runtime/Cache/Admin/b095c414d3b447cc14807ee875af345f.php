<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="/Public/iconfont/0727/iconfont.css">
<link rel="stylesheet" href="/Public/admin/css/base.css">
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="/Public/admin/js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
	$('.menu_title').click(function(){
		$(this).attr('data','1');
		$('.menu_title').each(function(){
			if($(this).attr('data') == 1){
				$(this).css('color','#fff');$(this).css('background','#44b549');$(this).find('i').css('color','#fff');
			}else{
				$(this).css('color','#555');$(this).css('background','#fff');$(this).find('i').css('color','#555');
			}
		});
		var obj = $(this).next('dd');
		if(obj.css("display") == 'block'){
			obj.css("display","none");$(this).attr('data','0');
		}else{
			$('dd').each(function(){
				$(this).css("display","none");
			});
			obj.css("display","block");
			$(this).attr('data','0');
		}
	});
	$('.menu dl dd ul li').click(function(){
		$('.menu dl dd ul li').each(function(){
			$(this).css('background','');
			$(this).css('color','');
		});
		$(this).css('background','#44b549');
		$(this).css('color','#fff');
	});
	
});
</script>
<style>
.menu_title:hover{background:#44b549;color:#fff;font-size:15px;}
.menu_title:hover i{color:#fff;}
.iconfont{font-weight:normal;}
</style>
<div class="left" oncontextmenu=self.event.returnValue=false onselectstart="return false">
  <div class="menu">
    <dl>
      <dt class="menu_title" data='0'><i class="icon iconfont add">&#xe668;</i>　微信设置</dt>
      <dd style="display:none;">
        <ul>
            <a href="<?php echo U('Base/config');?>" target="main-frame"><li>微信参数</li></a>           
            <!--<a href="<?php echo U('Base/menu');?>" target="main-frame"><li>菜单设置</li></a>           -->
            <!--<a href="<?php echo U('Base/subscribe');?>" target="main-frame"><li>关注回复</li></a>           -->
            <!--<a href="<?php echo U('Base/text');?>" target="main-frame"><li>文本回复</li></a>           -->
            <!--<a href="<?php echo U('Base/news');?>" target="main-frame"><li>图文回复</li></a>           -->
            <!--<a href="<?php echo U('Base/custom');?>" target="main-frame"><li>在线客服</li></a>           -->
        </ul>
      </dd>
    </dl>
	<dl>
      <dt class="menu_title" data='0'><i class="icon iconfont">&#xe65a;</i>　系统设置</dt>
      <dd  style="DISPLAY: none">
        <ul>
         
		  <a href="<?php echo U('System/qr');?>" target="main-frame"><li>海报二维码</li></a>
		  <a href="<?php echo U('Canshu/setPassword');?>" target="main-frame"><li>设置密码</li></a>

        </ul>
      </dd>
    </dl>
      <dl>
          <dt class="menu_title" data='0'><i class="icon iconfont">&#xe65a;</i>　我的代理</dt>
          <dd  style="DISPLAY: none">
              <ul>

                  <a href="<?php echo U('Agent/agent');?>" target="main-frame"><li>我的代理</li></a>
                  <!--<a href="<?php echo U('System/shouquan');?>" target="main-frame"><li>我的授权</li></a>-->

              </ul>
          </dd>
      </dl>
	<dl>
      <dt class="menu_title"><i class="icon iconfont">&#xe61c;</i>　商城管理</dt>
      <dd  style="DISPLAY: none">
        <ul>
          <a href="<?php echo U('Shop/setting');?>" target="main-frame"><li>商城设置</li></a>
          <a href="<?php echo U('Shop/categrey');?>" target="main-frame"><li>分类管理</li></a>
          <a href="<?php echo U('Shop/type');?>" target="main-frame"><li>属性管理</li></a>
          <a href="<?php echo U('Shop/good');?>" target="main-frame"><li>商品管理</li></a>
          <!--<a href="<?php echo U('Daijin/index');?>" target="main-frame"><li>代金券管理</li></a>-->
		  <a href="<?php echo U('Shop/order');?>" target="main-frame"><li>订单管理</li></a>
		  <a href="<?php echo U('Shop/pingjia');?>" target="main-frame"><li>商品评价</li></a>
        </ul>
      </dd>
    </dl>
	<dl>
      <dt class="menu_title"><i class="icon iconfont">&#xe617;</i>　会员管理</dt>
      <dd  style="DISPLAY: none">
        <ul>
          <a href="<?php echo U('Agent/users');?>" target="main-frame"><li>会员列表</li></a>                
          <!--<a href="<?php echo U('Agent/user_shop');?>" target="main-frame"><li>商户列表</li></a>                -->
        </ul>
      </dd>
    </dl>

	<dl>
      <dt class="menu_title"><i class="icon iconfont">&#xe618;</i>　资金管理</dt>
      <dd  style="DISPLAY: none">
        <ul>
          <a href="<?php echo U('ZiJin/tixianList');?>" target="main-frame"><li>提现记录</li></a>
          <a href="<?php echo U('ZiJin/tixianSq');?>" target="main-frame"><li>提现申请</li></a>
          <a href="<?php echo U('ZiJin/yongjinList');?>" target="main-frame"><li>佣金记录</li></a>
          <a href="<?php echo U('ZiJin/weifafang');?>" target="main-frame"><li>未发放记录</li></a>
          <!--<a href="<?php echo U('ZiJin/dailiList');?>" target="main-frame"><li>购买代理</li></a>-->
          <!-- <a href="<?php echo U('Agent/chongzhi');?>" target="main-frame"><li>充值记录</li></a>  -->
          <!-- <a href="<?php echo U('Agent/send_hongbao');?>" target="main-frame"><li>发送红包</li></a>              -->
        </ul>
      </dd>
    </dl>

      <dl>
          <dt class="menu_title" data='0'><i class="icon iconfont">&#xe65a;</i>　参数设置</dt>
          <dd  style="DISPLAY: none">
          <ul>
          <a href="<?php echo U('Canshu/tuijianjiang');?>" target="main-frame"><li>推荐奖</li></a>
          <a href="<?php echo U('Canshu/lingdaojiang');?>" target="main-frame"><li>领导奖</li></a>
          <a href="<?php echo U('Canshu/fenhongjiang');?>" target="main-frame"><li>分红奖</li></a>
          <a href="<?php echo U('Canshu/huikuijiang');?>" target="main-frame"><li>回馈奖励</li></a>
          <a href="<?php echo U('Canshu/setFen');?>" target="main-frame"><li>设置数量</li></a>
          </ul>
          </dd>
      </dl>
      <dl>
          <dt class="menu_title" data='0'><i class="icon iconfont">&#xe65a;</i>　商城统计</dt>
          <dd  style="DISPLAY: none">
              <ul>

                  <a href="<?php echo U('Main/Index');?>" target="main-frame"><li>商城统计</li></a>

              </ul>
          </dd>
      </dl>
      <dl>
          <dt class="menu_title"><i class="icon iconfont">&#xe61c;</i>　商城管理</dt>
          <dd  style="DISPLAY: none">
              <ul>
                  <a href="<?php echo U('Goods/good');?>" target="main-frame"><li>商品管理</li></a>
                  <a href="<?php echo U('Goods/order');?>" target="main-frame"><li>订单管理</li></a>
                  <a href="<?php echo U('Goods/gameOrder');?>" target="main-frame"><li>游戏订单</li></a>
                  <a href="<?php echo U('Goods/setGameScale');?>" target="main-frame"><li>游戏比例</li></a>
                  <a href="<?php echo U('Goods/gameRecord');?>" target="main-frame"><li>游戏记录</li></a>
                  <a href="<?php echo U('Goods/gameUserRefund');?>" target="main-frame"><li>用户退款</li></a>
              </ul>
          </dd>
      </dl>

  </div>
  <div style="font-size:12px;color:#999;text-align:center;">Created By 郑州西瓜科技</div>
</div>

	<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			  <h4 class="modal-title" id="myLargeModalLabel">系统通知</h4>
			</div>
			<div class="modal-body">
			  功能后期添加，请等待
			</div>
		  </div>
	  </div>
	</div>