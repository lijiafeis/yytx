<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="renderer" content="webkit">
	<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no">
	<title>系统管理后台</title>
	<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="/Public/admin/css/base.css">
	<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
	<script src="/Public/admin/js/bootstrap.min.js"></script>
	<script src="/Public/js/Chart.min.js"></script>
</head>
<body>
<style>
	.view{padding:30px 0;background:#13cbae5;margin:10px 20px;color:#fff;text-align:center;}
	.view:hover{background:#133afd9;}
	.number{font-size:30px;}
</style>
<div class="well">
	<div class="col-sm-12 alert-success" style="font-size:16px;padding:10px 20px;margin-bottom:10px;">平台营收</div>
	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($shop_all_fee); ?></em><div class="title">总营业额</div>
			</div>
		</div>
	</div>
	<div class="col-sm-3">
		<div class="view" style="background:#5cb85c">
			<div class="inner">
				<em class="number"><?php echo ($zonge["a"]); ?></em><div class="title">当天营业额</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#f0ad4e">
			<div class="inner">
				￥<em class="number"><?php echo ($user_all_broke); ?></em><div class="title">当天订单数</div>
			</div>
		</div>
	</div>



	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($user_all_tixian); ?></em><div class="title">总订单数</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($tixian); ?></em><div class="title">总提现额</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($currentTx); ?></em><div class="title">当天提现额</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($qb_money); ?></em><div class="title">钱包余额</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($xinjinmoney); ?></em><div class="title">当天新进钱包余额</div>
			</div>
		</div>
	</div>
	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($zhifubao); ?></em><div class="title">支付宝</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($weixin); ?></em><div class="title">微信</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($shoudong); ?></em><div class="title">手动确认</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($good_zonge); ?></em><div class="title">商城总金额</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($good_zhifubao); ?></em><div class="title">支付宝</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($good_weixin); ?></em><div class="title">微信</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($good_type1); ?></em><div class="title">未发货</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($good_type2); ?></em><div class="title">未收货</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($good_type3); ?></em><div class="title">已完成</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				￥<em class="number"><?php echo ($good_price); ?></em><div class="title">当天营业额</div>
			</div>
		</div>
	</div>

</div>
</body></html>