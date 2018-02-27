
CREATE TABLE IF NOT EXISTS `wx_admin` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(18) NOT NULL DEFAULT '',
  `register_time` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `last_time` int(11) unsigned NOT NULL DEFAULT '0',
  `sign` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `wx_agent_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `order_sn` varchar(32) NOT NULL DEFAULT '',
  `type` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `bucha` int(1) unsigned NOT NULL DEFAULT '0',
  `total_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `time` int(11) unsigned zerofill NOT NULL DEFAULT '00000000000',
  `openid` varchar(28) NOT NULL DEFAULT '',
  `is_true` int(1) NOT NULL DEFAULT '0',
  `product` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `wx_app` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `app_name` varchar(32) NOT NULL DEFAULT '',
  `app_desc` varchar(255) NOT NULL DEFAULT '',
  `state` int(1) unsigned NOT NULL DEFAULT '0',
  `sign` varchar(32) NOT NULL DEFAULT '',
  `url` varchar(64) NOT NULL DEFAULT '',
  `web` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='应用表';


CREATE TABLE IF NOT EXISTS `wx_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `wx_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `wx_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

insert into `wx_auth_group`(`id`,`title`,`status`,`rules`) values
('1','超级管理员','1','15,16,17,18,19,20,10,11,12,13,14,7,8,9,1,21,22,4'),
('5','商城管理员','1','10,11,12,13,14,1');
insert into `wx_auth_group_access`(`uid`,`group_id`) values
('1','1');
insert into `wx_auth_rule`(`id`,`name`,`title`,`type`,`status`,`condition`,`code`) values
('1','Main','统计数据','1','1','1','16'),
('4','System','系统设置','1','1','','7'),
('7','AgentUsers','会员列表','1','1','','13'),
('8','AgentBroke','提现记录','1','1','','14'),
('9','Agentchongzhi','充值记录','1','1','','15'),
('10','ShopSetting','商城设置','1','1','','8'),
('11','ShopCategrey','分类管理','1','1','','9'),
('12','ShopType','属性设置','1','1','','10'),
('13','ShopGood','商品管理','1','1','','11'),
('14','ShopOrder','订单管理','1','1','','12'),
('15','BaseConfig','微信参数','1','1','','1'),
('16','BaseMenu','菜单设置','1','1','','2'),
('17','BaseSubscribe','关注回复','1','1','','3'),
('18','BaseText','文本回复','1','1','','4'),
('19','BaseNews','图文回复','1','1','','5'),
('20','BaseCustom','在线客服','1','1','','6'),
('21','Gongpai','公排复制','1','1','','17'),
('22','Agentsend_hongbao','发送红包','1','1','1','18');
insert into `wx_app`(`app_id`,`app_name`,`app_desc`,`state`,`sign`,`url`,`web`) values
('1','公排复制','平台会员进入后可以按照从左到右、自上向下方式进行公排，进而获得游戏红包收益','1','','gongpai/index','0'),
('2','群发消息','利用客服接口进行群发文字、图文等信息，突破发送条数限制，48小时互动粉丝均可收到','1','','sendmsg/index','0');



CREATE TABLE IF NOT EXISTS `wx_broke_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `desc` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `type` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '记录类型',
  `fee` decimal(6,2) unsigned DEFAULT '0.00',
  `state` int(1) unsigned NOT NULL DEFAULT '1',
  `order_sn` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='返佣金明细表';


CREATE TABLE IF NOT EXISTS `wx_chat` (
  `chat_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `to_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='聊天记录表';


CREATE TABLE IF NOT EXISTS `wx_chat_all` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `from_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会话列表';


CREATE TABLE IF NOT EXISTS `wx_config` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `wxname` varchar(255) NOT NULL DEFAULT '',
  `wxid` varchar(40) NOT NULL DEFAULT '',
  `weixin` varchar(30) NOT NULL DEFAULT '',
  `appid` varchar(255) NOT NULL DEFAULT '',
  `appsecret` varchar(255) NOT NULL DEFAULT '',
  `machid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `mkey` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_custom` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `switch` int(1) NOT NULL DEFAULT '0',
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_daijin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `del_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_daijin_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `daijin_id` int(11) unsigned NOT NULL DEFAULT '0',
  `total_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0',
  `state` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0，未使用\n1，已消费',
  `prepay_id` varchar(64) NOT NULL DEFAULT '',
  `pay_id` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代金券订单表';

CREATE TABLE IF NOT EXISTS `wx_daili_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_daili_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(32) NOT NULL DEFAULT '',
  `first_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `second_name` varchar(32) NOT NULL DEFAULT '',
  `second_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `third_name` varchar(32) NOT NULL DEFAULT '',
  `third_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `first_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `second_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `third_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `four_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `five_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `six_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `seven_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `eight_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `nine_hongbao` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `daili_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_fenhong` (
  `fenhong_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fenhong_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_fenxiao_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fenxiao_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `fenxiao_name` varchar(32) NOT NULL DEFAULT '',
  `fenxiao_exist` int(1) unsigned NOT NULL DEFAULT '1',
  `fenxiao_total` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `fenxiao_good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `self_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `self_per` int(3) unsigned NOT NULL DEFAULT '0',
  `fenxiao_level` int(1) unsigned NOT NULL DEFAULT '3',
  `min_tixian` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `tixian_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `first_per` int(2) unsigned NOT NULL DEFAULT '0',
  `second_per` int(2) unsigned NOT NULL DEFAULT '0',
  `third_per` int(2) unsigned NOT NULL DEFAULT '0',
  `four_per` int(2) unsigned NOT NULL DEFAULT '0',
  `five_per` int(2) unsigned NOT NULL DEFAULT '0',
  `six_per` int(2) unsigned NOT NULL DEFAULT '0',
  `seven_per` int(2) unsigned NOT NULL DEFAULT '0',
  `eight_per` int(2) unsigned NOT NULL DEFAULT '0',
  `nine_per` int(2) unsigned NOT NULL DEFAULT '0',
  `jifen_first_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_second_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_third_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_four_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_five_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_six_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_seven_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_eight_per` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_nine_per` int(1) unsigned NOT NULL DEFAULT '0',
  `chongzhi_exist` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0，允许所有会员充值\n1，允许分销商充值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_gongpai_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `children_id` int(11) unsigned NOT NULL DEFAULT '0',
  `level` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `aaa` (`user_id`,`children_id`,`level`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公排用户关系数据表';

CREATE TABLE IF NOT EXISTS `wx_gongpai_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gongpai_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `duodian_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `gongpai_number` int(2) unsigned NOT NULL DEFAULT '0',
  `gongpai_level` int(2) unsigned NOT NULL DEFAULT '0',
  `first_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `second_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `third_fee` decimal(5,2) NOT NULL DEFAULT '0.00',
  `four_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `five_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `six_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `seven_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `eight_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `nine_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `ten_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `eleven_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `twelve_fee` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `gongpai_again` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '值为1时，重复购买进入下一轮，值为2时自动进入下一轮，值为3禁止再次进入',
  `gongpai_exist` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '1，商城内任意商品\n2，指定商品\n3，购买指定代金券',
  `gongpai_good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ganen_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `ganen_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `daijin_id` int(11) unsigned NOT NULL DEFAULT '0',
  `jifen_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_fee` int(11) unsigned NOT NULL DEFAULT '0',
  `tojifen_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen_per` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_gongpai_user` (
  `dianwei_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `type` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0，未出局\n1，出局',
  `state` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0，未排满一层\n1，已排满一层',
  PRIMARY KEY (`dianwei_id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公排用户点位表';

CREATE TABLE IF NOT EXISTS `wx_good_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_goods_spec_info` (
  `good_spec_id` int(11) NOT NULL AUTO_INCREMENT,
  `good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `spec_info_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`good_spec_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_hbrecord` (
  `hb_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `dianwei_id` int(11) unsigned NOT NULL DEFAULT '0',
  `from_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `from_dianwei_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '1,排位得红包\n2,后台发红包',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `hongbao_fee` decimal(6,2) NOT NULL DEFAULT '0.00',
  `last_fee` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`hb_id`),
  KEY `aaa` (`user_id`,`dianwei_id`,`from_user_id`,`from_dianwei_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_jifen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `number` int(11) unsigned NOT NULL DEFAULT '0',
  `type` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '值1，关注积分\n值2，签到积分',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='积分信息明细表';

CREATE TABLE IF NOT EXISTS `wx_jifen_goods` (
  `good_id` int(11) NOT NULL AUTO_INCREMENT,
  `good_type` int(1) unsigned DEFAULT '0' COMMENT '1,实物产品\n2，虚拟产品\n3，卡券产品\n4，红包产品\n5，代金券产品',
  `good_name` varchar(54) NOT NULL DEFAULT '',
  `good_prcie` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '兑换积分数',
  `good_pic` varchar(255) NOT NULL COMMENT '商品缩略图',
  `good_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品库存',
  `good_sale_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已售出数量',
  `good_code` int(11) unsigned DEFAULT '0' COMMENT '商品排序ID，数值越大排名越靠前',
  `good_desc` text NOT NULL COMMENT '商品详细描述',
  `is_sale` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '商品上下架\n1，上架\n0，下架',
  PRIMARY KEY (`good_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分商品表';

CREATE TABLE IF NOT EXISTS `wx_jifen_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `subscribe_number` int(8) unsigned NOT NULL DEFAULT '0',
  `qiandao_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `qiandao_number` int(8) unsigned NOT NULL DEFAULT '0',
  `qiandao_week` int(3) unsigned NOT NULL DEFAULT '7',
  `more_number` int(8) unsigned NOT NULL DEFAULT '0',
  `subscribe_word` varchar(255) NOT NULL DEFAULT '',
  `buy_per` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_jifen_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `good_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品表关联ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户表关联ID',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '兑换时间戳',
  `state` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态\n0，未发货\n1，已发货\n2，已确认完成',
  `send_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发货时间戳',
  `over_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单完成时间戳',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分兑换订单表';

CREATE TABLE IF NOT EXISTS `wx_menu` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL DEFAULT '',
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(220) NOT NULL DEFAULT '',
  `is_show` int(1) NOT NULL DEFAULT '1',
  `type` varchar(26) DEFAULT '0',
  `code` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_news` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `click` int(11) unsigned NOT NULL DEFAULT '0',
  `share` int(11) unsigned NOT NULL DEFAULT '0',
  `read_num` int(11) unsigned NOT NULL DEFAULT '0',
  `zan` int(11) unsigned NOT NULL DEFAULT '0',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  `is_share` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许分享转发',
  `is_comment` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许评论',
  `read_type` int(1) unsigned DEFAULT '0' COMMENT '0，不允许所有人查看\n1，只允许分销会员查看\n2，允许所有会员查看\n3，允许所有人查看',
  `is_shang` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启赞赏',
  `pic_show` int(1) unsigned NOT NULL DEFAULT '1',
  `keyword_type` int(1) DEFAULT NULL COMMENT '1,完全匹配\n2，模糊匹配',
  `out_link` varchar(255) NOT NULL DEFAULT '',
  `location_link` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_news_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `time` int(11) NOT NULL,
  `is_show` int(1) unsigned NOT NULL DEFAULT '1',
  `zan_num` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_news_comment_zan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) unsigned NOT NULL DEFAULT '0',
  `new_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_news_read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_news_shang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `order_sn` varchar(32) NOT NULL DEFAULT '',
  `fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_news_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_news_zan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_qrcode` (
  `scene_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  `media_id` varchar(255) NOT NULL DEFAULT '',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `use_num` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`scene_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_qrset` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `font_size` int(3) unsigned NOT NULL DEFAULT '0',
  `font_x` smallint(5) unsigned NOT NULL DEFAULT '0',
  `font_y` smallint(5) unsigned NOT NULL DEFAULT '0',
  `head_size` smallint(5) unsigned NOT NULL DEFAULT '0',
  `head_x` smallint(5) unsigned NOT NULL DEFAULT '0',
  `head_y` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qr_size` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qr_x` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qr_y` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户疑难问题';

CREATE TABLE IF NOT EXISTS `wx_send_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL DEFAULT '',
  `text` varchar(500) NOT NULL DEFAULT '',
  `news_id` varchar(255) NOT NULL DEFAULT '',
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `success` int(11) unsigned NOT NULL DEFAULT '0',
  `fail` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `is_sure` int(1) unsigned NOT NULL DEFAULT '0',
  `media_id` varchar(255) NOT NULL DEFAULT '',
  `system_show` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_shop_ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '50',
  `click` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_shop_bannar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_shop_categrey` (
  `cate_id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `cate_name` varchar(255) NOT NULL DEFAULT '',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `is_show` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否在首页显示',
  `hidden` int(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏该分类',
  PRIMARY KEY (`cate_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_shop_daijin` (
  `daijin_id` int(11) NOT NULL AUTO_INCREMENT,
  `daijin_name` int(11) unsigned NOT NULL DEFAULT '0',
  `daijin_rule` int(1) NOT NULL DEFAULT '1',
  `daijin_line` int(11) unsigned NOT NULL DEFAULT '0',
  `date_switch` int(1) unsigned NOT NULL DEFAULT '0',
  `daijin_date` int(11) unsigned NOT NULL DEFAULT '0',
  `daijin_fee` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `daijin_number` int(11) unsigned NOT NULL DEFAULT '0',
  `sale_number` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`daijin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商城代金券';

CREATE TABLE IF NOT EXISTS `wx_shop_good_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_shop_goods` (
  `good_id` int(11) NOT NULL AUTO_INCREMENT,
  `good_name` varchar(255) NOT NULL DEFAULT '',
  `market_price` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `good_price` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `cate_pid` int(11) unsigned NOT NULL DEFAULT '0',
  `cate_gid` int(11) unsigned NOT NULL DEFAULT '0',
  `good_desc` text NOT NULL,
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  `best` int(1) unsigned NOT NULL DEFAULT '0',
  `hot` int(1) unsigned NOT NULL DEFAULT '0',
  `new` int(1) unsigned NOT NULL DEFAULT '0',
  `number` int(11) unsigned NOT NULL DEFAULT '0',
  `is_true` int(1) unsigned NOT NULL DEFAULT '1',
  `good_profit` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `first_per` int(3) unsigned NOT NULL DEFAULT '0',
  `second_per` int(3) unsigned NOT NULL DEFAULT '0',
  `third_per` int(3) unsigned NOT NULL DEFAULT '0',
  `type_id` int(11) unsigned NOT NULL DEFAULT '0',
  `good_jifen` int(11) unsigned NOT NULL DEFAULT '0',
  `jifen_profit` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`good_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_shop_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_sn` varchar(18) NOT NULL DEFAULT '',
  `total_fee` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单提交时间',
  `prepay_id` varchar(64) NOT NULL DEFAULT '',
  `serve_name` varchar(20) NOT NULL DEFAULT '',
  `serve_id` varchar(25) NOT NULL DEFAULT '',
  `is_true` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '付款状态',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '付款时间',
  `state` int(1) unsigned NOT NULL DEFAULT '0',
  `pay_type` int(1) unsigned NOT NULL DEFAULT '0',
  `gongpai_dianwei_id` int(11) unsigned NOT NULL DEFAULT '0',
  `serve_time` int(11) unsigned NOT NULL DEFAULT '0',
  `daijin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '代金券订单ID',
  PRIMARY KEY (`order_id`),
  KEY `aaa` (`pay_id`,`user_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单表';

CREATE TABLE IF NOT EXISTS `wx_shop_order_detail` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_sn` varchar(18) NOT NULL DEFAULT '',
  `good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `good_name` varchar(50) NOT NULL DEFAULT '',
  `good_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `good_profit` decimal(10,2) unsigned DEFAULT '0.00',
  `jifen_profit` int(11) unsigned NOT NULL DEFAULT '0',
  `good_num` int(11) unsigned NOT NULL DEFAULT '0',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT '',
  `good_jifen` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`),
  KEY `aaa` (`pay_id`,`user_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单详情表';

CREATE TABLE IF NOT EXISTS `wx_shop_order_temp` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `good_id` int(11) unsigned NOT NULL DEFAULT '0',
  `good_num` int(11) unsigned NOT NULL DEFAULT '1',
  `type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户购物车数据';

CREATE TABLE IF NOT EXISTS `wx_shop_spec` (
  `spec_id` int(11) NOT NULL AUTO_INCREMENT,
  `spec_name` varchar(32) NOT NULL DEFAULT '',
  `type_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL DEFAULT '',
  `type` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`spec_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_shop_spec_info` (
  `spec_info_id` int(11) NOT NULL AUTO_INCREMENT,
  `spec_id` int(11) unsigned NOT NULL DEFAULT '0',
  `spec_info_name` varchar(225) NOT NULL DEFAULT '',
  PRIMARY KEY (`spec_info_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_subscribe` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL DEFAULT '',
  `keyword` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_system` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `last_time` int(11) NOT NULL DEFAULT '0',
  `url_sure` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_text` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `createtime` int(11) NOT NULL DEFAULT '0',
  `click` int(11) unsigned NOT NULL DEFAULT '0',
  `keyword_type` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `wx_user_address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '',
  `telphone` varchar(32) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wx_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(28) NOT NULL DEFAULT '',
  `wxid` varchar(50) NOT NULL DEFAULT '',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `headimgurl` varchar(255) NOT NULL DEFAULT '',
  `sex` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `province` varchar(50) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(50) NOT NULL DEFAULT '',
  `subscribe` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `subscribe_time` int(11) unsigned NOT NULL DEFAULT '0',
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `level` int(11) unsigned NOT NULL DEFAULT '1',
  `agent` int(1) NOT NULL DEFAULT '0',
  `shop_income` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `shop_outcome` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `daijin` int(1) unsigned NOT NULL DEFAULT '0',
  `jifen` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


