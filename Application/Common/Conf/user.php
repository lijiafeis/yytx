<?php
/*************定义系统常量******************/
$app_color = F('wap_color','',DATA_ROOT);

return array(
	/*************回复模块常量******************/
	'SUBSCRIBE_ERROR'=>'您不是通过海报关注的，无法成为会员，请取消关注后扫描推广员宣传海报进行关注',
	'SUBSCRIBE_WORD1'=>"感谢您成为",
	'SUBSCRIBE_WORD2'=>"号会员，平台全体家人将竭诚为您服务！",//word1，word2中间为用户的id号
	'QR_NOTICE'=>'您还未成为代理，不能生成推广海报，前去购买代理吧！',
	'COPYRIGHT'=>'西瓜科技·微信第三方开发公司',
	/* 手机端色彩值 */
	'WAP_JIANBIAN_COLOR'=>$app_color['self'],
	
	'WAP_COLOR'=>$app_color['main'],
	
	/*************商城模块常量******************/
	'SHOP_ORDER_TYPE1'=>'待付款',
	'SHOP_ORDER_TYPE2'=>'已付款待发货',
	'SHOP_ORDER_TYPE3'=>'',
	'SHOP_ORDER_TYPE4'=>'',
	'SHOP_BROKE_TOP'=>'恭喜您，获得了一笔新的佣金，您可以在“会员中心”中查看详细佣金信息。',//商城佣金提醒头部文字
	
	
	
	/*************其它******************/
	'USER_CHONGZHI_NOTICE'=>'您好，您已经充值成功。',//用户充值成功模板头部文字
	'USER_HONGBAO_NOTICE'=>'您参与平台活动，有一笔新收入入账',//用户收到红包模板头部文字
	'PID_NOTIEC'=>'通过您的二维码关注了',//下级用户成功关注提醒
	
	'USER_AGENT_ONE'=>'您已成功升级成为',//会员成功升级为分销会员提示
	'USER_AGENT_TWO'=>'，请进入会员首页查看',//会员成功升级为分销会员提示
	'USER_NORMAL_NAME'=>'普通瓜农',//普通会员名称
	'USER_QR_NOTICE'=>'您目前是普通瓜农，无法生成二维码海报',//生成二维码失败时提示
	
	
);

