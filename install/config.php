<?php
return array(
	/* ------系统------ */
	//系统名称
	'name'=>'西瓜科技应用定制',
	//系统版本
	'version'=>'1.0',
	//系统powered
	'powered'=>'Powered by 郑州西瓜科技',
	//系统脚部信息
	'footerInfo'=>'Copyright &copy; 2016 郑州西瓜科技',
	/* ------站点------ */
	//数据库文件
	'sqlFileName'=>array('data.sql'),
	//数据库配置文件
	'dbConfig'=>'application/common/conf/db.php',
	//数据库名
	'dbName' => 'ectouch_db',
	//数据库表前缀
	'dbPrefix' => 'ecs_',
	//站点名称
	'siteName' => '',
	//站点关键字
	'siteKeywords' => '西瓜科技',
	//站点描述
	'siteDescription' => '西瓜科技',
	//需要读写权限的目录
	'dirAccess' => array(
		'/',
		'/data',
		'/application',
		'/application/runtime/',
		'/uploads',
		'/public',
	),
	/* ------写入数据库完成后处理的文件------ */
	'handleFile' => 'main.php',
	/* ------生成数据库配置文件的模板------ */
	'dbSetFile'=> 'config.ini',
	/* ------安装验证/生成文件;非云平台安装有效------ */
	'installFile' => '../data/install.lock',
	'alreadyInstallInfo' => '你已经安装过该系统，如果想重新安装，请先删除站点data目录下的 install.lock 文件，然后再尝试安装！',
);