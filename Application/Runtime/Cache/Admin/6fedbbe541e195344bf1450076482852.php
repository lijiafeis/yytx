<?php if (!defined('THINK_PATH')) exit();?> <!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no">
  <title>微信商城系统管理后台</title>
  <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="/Public/bootstrap/css/font-awesome.min.css">
  <link rel="stylesheet" href="/Public/admin/css/base.css">



<frameset rows="95,*" framespacing="0" border="0">
  <frame src="<?php echo U('top');?>" id="header-frame" name="header-frame" frameborder="no" scrolling="no">
  <frameset cols="180,  *" framespacing="0" border="0" id="frame-body">
    <frame src="<?php echo U('left');?>" id="menu-frame" name="menu-frame" frameborder="no" scrolling="no">
    <frame src="<?php echo U('Main/Index');?>" id="main-frame" name="main-frame" frameborder="no" scrolling="yes">
  </frameset>
</frameset><noframes></noframes>
  <frameset rows="0, 0" framespacing="0" border="0">
  <frame src="http://api.ecshop.com/record.php?mod=login&url=<?php echo ($shop_url); ?>" id="hidd-frame" name="hidd-frame" frameborder="no" scrolling="no">
  </frameset>
</head>