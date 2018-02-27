<?php
return array(
	//'配置项'=>'配置值'
	'URL_CASE_INSENSITIVE' =>true,
	'URL_MODEL'             =>  2,
	'TMPL_FILE_DEPR'        =>  '_', //模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符
	'DEFAULT_MODULE'        =>  'Login',
	'DEFAULT_CONTROLLER'    =>  'User', // 默认控制器名称
	'DEFAULT_ACTION'        =>  'index', // 默认操作名称
	'TMPL_ACTION_ERROR'     =>  'dispatch_jump', // 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS'   =>  'dispatch_jump', // 默认成功跳转对应的模板文件
	//'TMPL_EXCEPTION_FILE' => APP_PATH.'/Public/exception.tpl',
	//'ERROR_PAGE' => '/Public/exception.tpl',
	'LOAD_EXT_CONFIG' => 'user,db',  
);