<?php


// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.9','<'))  die('require PHP > 5.3.0 !');
$data = ['update','delete','select','drop','show','script'];
$post = file_get_contents("php://input");
if($post){
    foreach ($data as $value) {
        if(stripos($post,$value) ){
           
            die();
        }
    }
}elseif($_POST){
    $post = '';
    foreach($_POST as $k=>$v){
        $post .= $v;
    }
    foreach ($data as $value) {
        if(stripos($post,$value) ){
           
            die();
        }
    }
}
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',false);
header("content-type:text/html;charset=utf8");
// 定义应用目录
define('APP_PATH','./Application/');
define('DATA_ROOT','./data/');
require './Xigua/Xigua.php';
