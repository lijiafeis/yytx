<?php
//网站域名
$site_url = trim($_POST['siteurl']);
if($independent){
	$username = trim($_POST['manager']);
	$password = trim($_POST['manager_pwd']);
}
//读取配置文件，并替换真实配置数据
$strConfig = file_get_contents(INSTALL_PATH . $config['dbSetFile']);
$strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
$strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
$strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
$strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
$strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
$strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
@file_put_contents(ROOT_PATH . $config['dbConfig'], $strConfig);

if($independent){
	//插入管理员
	$time = time();
	$ip = get_client_ip();
	$password = md5($password);
	$email = trim($_POST['manager_email']);
	$query = "INSERT INTO `{$dbPrefix}admin` (id,username, password, ip, register_time, phone, email, last_time) VALUES ('1','{$username}', '{$password}', '{$ip}', '0', '{$time}', '{$email}', '0')";
	if(mysqli_query($conn, $query)){
		return array('status'=>2,'info'=>'成功添加管理员<br />成功写入配置文件<br>安装完成...');
	}
}else{
	return array('status'=>2,'info'=>'成功写入配置文件<br>安装完成...');
}
return array('status'=>0,'info'=>'安装失败...');
