-- ----------------------------
-- Table structure for vj_cdk
-- ----------------------------
DROP TABLE IF EXISTS `vj_cdk`;
CREATE TABLE `vj_cdk` (
  `cdk_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `code` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'cdk',
  `time` bigint(16) NOT NULL COMMENT '卡密充值时长',
  `use_id` int(11) NOT NULL DEFAULT '0' COMMENT '使用会员ID,未使用默认0',
  `use_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '卡密使用时间',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `classify` varchar(255) NOT NULL DEFAULT 'default' COMMENT '卡密分类',
  PRIMARY KEY (`cdk_id`),
  UNIQUE KEY `code` (`code`),
  KEY `use_id` (`use_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='卡密充值表';



-- ----------------------------
-- Table structure for vj_count
-- ----------------------------
DROP TABLE IF EXISTS `vj_count`;
CREATE TABLE `vj_count` (
  `count_id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_num` int(11) NOT NULL DEFAULT '0',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`count_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据统计表';

-- ----------------------------
-- Records of vj_count
-- ----------------------------


-- ----------------------------
-- Table structure for vj_server
-- ----------------------------
DROP TABLE IF EXISTS `vj_server`;
CREATE TABLE `vj_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(100) NOT NULL COMMENT '服务器名称',
  `img_host` varchar(255) NOT NULL COMMENT '图片服务器',
  `video_host` varchar(255) NOT NULL COMMENT '视频服务器',
  `scheme` varchar(255) NOT NULL DEFAULT 'http://' COMMENT '协议',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `site_host` varchar(255) DEFAULT '',
  `use_video` tinyint(1) NOT NULL DEFAULT '1',
  `use_image` tinyint(1) NOT NULL DEFAULT '1',
  `hide` tinyint(1) NOT NULL DEFAULT '0' COMMENT '隐藏',
  `is_down` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否下载到本地',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of vj_server
-- ----------------------------
INSERT INTO `vj_server` VALUES ('1', 'cjl', '', 'www.yemalu.tw', 'http://', '2016-12-25 20:30:58', 'www.yemalu.tw', '1', '0', '1', '1', '0');
INSERT INTO `vj_server` VALUES ('2', 'avtb', 'www.111avtb.com', '', 'http://', '2016-12-25 20:30:58', 'www.111avtb.com', '0', '1', '0', '1', '19');
INSERT INTO `vj_server` VALUES ('3', 'qyule', 'www.qyl099.com', '', 'http://', '2017-05-02 08:41:02', 'www.qyl099.com', '0', '1', '0', '1', '18');
INSERT INTO `vj_server` VALUES ('4', '56pao', 'diaopic.993pao.com', '99gmgm.com', 'http://', '2017-07-19 10:27:58', 'diaopic.993pao.com', '0', '1', '0', '1', '0');
INSERT INTO `vj_server` VALUES ('5', '267hk', '', '', 'https://', '2017-07-20 04:24:25', 'www.9982s.com', '0', '0', '0', '1', '0');
INSERT INTO `vj_server` VALUES ('6', 'xvideo', '', '', 'https://', '2018-01-15 20:01:32', 'www.xvideos.com', '0', '0', '0', '1', '17');
INSERT INTO `vj_server` VALUES ('7', '52888', ' ', ' ', 'http://', '2018-01-15 22:32:06', '52888ppp.com', '0', '0', '0', '1', '20');

-- ----------------------------
-- Table structure for vj_type
-- ----------------------------
DROP TABLE IF EXISTS `vj_type`;
CREATE TABLE `vj_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(40) NOT NULL COMMENT '类别名称',
  `server_id` int(11) NOT NULL COMMENT '服务器id',
  `item` varchar(255) NOT NULL COMMENT '捕获参数',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示到首页',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of vj_type
-- ----------------------------
INSERT INTO `vj_type` VALUES ('2', '自拍', '1', 'c=1', '1', '2016-12-27 12:44:37');
INSERT INTO `vj_type` VALUES ('3', '欧洲', '1', 'c=2', '1', '2016-12-27 12:44:37');
INSERT INTO `vj_type` VALUES ('4', '日韩', '1', 'c=3', '1', '2016-12-27 12:44:37');
INSERT INTO `vj_type` VALUES ('5', '日本无码', '1', 'c=6', '1', '2016-12-27 12:44:37');
INSERT INTO `vj_type` VALUES ('6', '动漫', '1', 'c=4', '1', '2016-12-27 12:44:37');
INSERT INTO `vj_type` VALUES ('7', '会员专区', '1', 'c=5', '1', '2016-12-27 12:44:37');
INSERT INTO `vj_type` VALUES ('8', 'AVTB全部', '2', 'recent', '1', '2016-12-27 12:44:38');
INSERT INTO `vj_type` VALUES ('10', '卡通动漫', '3', 'cartoon', '1', '2017-05-02 10:23:21');
INSERT INTO `vj_type` VALUES ('11', '长视频', '3', 'changshipin', '1', '2017-05-02 10:23:40');
INSERT INTO `vj_type` VALUES ('12', '潮喷', '3', 'chaopen', '1', '2017-05-02 10:24:10');
INSERT INTO `vj_type` VALUES ('13', '大屌', '3', 'dadiao', '1', '2017-05-02 10:25:27');
INSERT INTO `vj_type` VALUES ('14', '肛交', '3', 'gangjiao', '1', '2017-05-02 10:26:18');
INSERT INTO `vj_type` VALUES ('15', '高清', '3', 'gaoqing', '1', '2017-05-02 10:26:37');
INSERT INTO `vj_type` VALUES ('16', 'GAY男同系列 ', '3', 'gay', '1', '2017-05-02 10:26:55');
INSERT INTO `vj_type` VALUES ('17', '国产', '3', 'guochan', '1', '2017-05-02 10:27:38');
INSERT INTO `vj_type` VALUES ('18', '巨乳', '3', 'juru', '1', '2017-05-02 10:28:00');
INSERT INTO `vj_type` VALUES ('19', '口爆颜射 ', '3', 'koubaoyanshe', '1', '2017-05-02 10:28:35');
INSERT INTO `vj_type` VALUES ('20', '美女', '3', 'meinv', '1', '2017-05-02 10:30:46');
INSERT INTO `vj_type` VALUES ('21', '蜘蛛', '3', 'nenmei', '1', '2017-05-02 10:31:12');
INSERT INTO `vj_type` VALUES ('22', '女同', '3', 'nvtong', '1', '2017-05-02 10:31:34');
INSERT INTO `vj_type` VALUES ('23', '欧美', '3', 'oumei', '1', '2017-05-02 10:32:23');
INSERT INTO `vj_type` VALUES ('24', '性party', '3', 'party', '1', '2017-05-02 10:32:42');
INSERT INTO `vj_type` VALUES ('25', '公众场所及户外', '3', 'public', '1', '2017-05-02 10:33:01');
INSERT INTO `vj_type` VALUES ('26', '器具自慰', '3', 'qijuziwei', '1', '2017-05-02 10:33:24');
INSERT INTO `vj_type` VALUES ('27', '群交', '3', 'qunjiao', '1', '2017-05-02 10:33:38');
INSERT INTO `vj_type` VALUES ('28', '强奸', '3', 'rapping', '1', '2017-05-02 10:33:54');
INSERT INTO `vj_type` VALUES ('29', '人妻熟女', '3', 'renqishunv', '1', '2017-05-02 10:34:13');
INSERT INTO `vj_type` VALUES ('30', '青娱早期热舞视频 ', '3', 'rewu', '1', '2017-05-02 10:34:26');
INSERT INTO `vj_type` VALUES ('31', '日本无码', '3', 'ribenwuma', '1', '2017-05-02 10:34:43');
INSERT INTO `vj_type` VALUES ('32', '性爱 ', '3', 'sex', '1', '2017-05-02 10:34:56');
INSERT INTO `vj_type` VALUES ('33', '丝袜', '3', 'siwa', '1', '2017-05-02 10:35:29');
INSERT INTO `vj_type` VALUES ('34', 'SM性虐', '3', 'smxingnue', '1', '2017-05-02 10:35:49');
INSERT INTO `vj_type` VALUES ('35', '素人', '3', 'suren', '1', '2017-05-02 10:36:08');
INSERT INTO `vj_type` VALUES ('36', '偷情与乱伦', '3', 'touqingyuluanlun', '1', '2017-05-02 10:36:22');
INSERT INTO `vj_type` VALUES ('37', 'VIP会员专区', '3', 'vip', '1', '2017-05-02 10:36:45');
INSERT INTO `vj_type` VALUES ('38', '小便 ', '3', 'xiaoban', '1', '2017-05-02 10:37:03');
INSERT INTO `vj_type` VALUES ('39', '校园', '3', 'xiaoyuan', '1', '2017-05-02 10:37:16');
INSERT INTO `vj_type` VALUES ('40', '亚洲 ', '3', 'yazhou', '1', '2017-05-02 10:37:35');
INSERT INTO `vj_type` VALUES ('41', '日本有码', '3', 'youma', '1', '2017-05-02 10:38:41');
INSERT INTO `vj_type` VALUES ('42', 'youtube', '3', 'youtube', '1', '2017-05-02 10:39:01');
INSERT INTO `vj_type` VALUES ('43', '制服', '3', 'zhifu', '1', '2017-05-02 10:39:19');
INSERT INTO `vj_type` VALUES ('44', '重口味', '3', 'zhongkouwei', '1', '2017-05-02 10:39:40');
INSERT INTO `vj_type` VALUES ('45', '中文字幕', '3', 'zhongwenzimu', '1', '2017-05-02 10:39:58');
INSERT INTO `vj_type` VALUES ('46', '主播 ', '3', 'zhubo', '1', '2017-05-02 10:40:19');
INSERT INTO `vj_type` VALUES ('47', '足脚', '3', 'zujiao', '1', '2017-05-02 10:40:55');
INSERT INTO `vj_type` VALUES ('48', '乱伦', '4', '27', '1', '2017-07-19 10:35:41');
INSERT INTO `vj_type` VALUES ('49', '人妻', '4', '28', '1', '2017-07-19 10:36:35');
INSERT INTO `vj_type` VALUES ('50', '偷拍', '4', '29', '1', '2017-07-19 10:36:47');
INSERT INTO `vj_type` VALUES ('51', '学生', '4', '34', '1', '2017-07-19 10:37:13');
INSERT INTO `vj_type` VALUES ('52', '巨乳', '4', '54', '1', '2017-07-19 10:37:41');
INSERT INTO `vj_type` VALUES ('53', '日韩', '4', '55', '1', '2017-07-19 10:38:02');
INSERT INTO `vj_type` VALUES ('54', '欧美', '4', '56', '1', '2017-07-19 10:38:23');
INSERT INTO `vj_type` VALUES ('55', '国产', '4', '57', '1', '2017-07-19 10:38:50');
INSERT INTO `vj_type` VALUES ('56', '动漫', '4', '58', '1', '2017-07-19 10:39:15');
INSERT INTO `vj_type` VALUES ('57', '国产精品', '5', '60', '1', '2017-07-20 04:59:35');
INSERT INTO `vj_type` VALUES ('58', '亚洲无码', '5', '110', '1', '2017-07-20 04:59:35');
INSERT INTO `vj_type` VALUES ('59', '欧美性爱', '5', '62', '1', '2017-07-20 04:59:35');
INSERT INTO `vj_type` VALUES ('60', 'VR虚拟现实', '5', '86', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('61', '成人动漫', '5', '101', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('62', '自拍图片', '5', '63', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('63', '情色小说', '5', '84', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('64', '自拍偷拍', '5', '89', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('65', '夫妻同房', '5', '87', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('66', '开放90后', '5', '93', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('67', '换妻游戏', '5', '90', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('68', '网红主播', '5', '91', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('69', '手机小视频', '5', '88', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('70', '明星艳照门', '5', '92', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('71', '经典三级', '5', '109', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('72', 'S级女优', '5', '100', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('73', '波多野结衣', '5', '94', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('74', '吉泽明步', '5', '95', '1', '2017-07-20 04:59:36');
INSERT INTO `vj_type` VALUES ('75', '苍井空', '5', '96', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('76', '宇都宮紫苑', '5', '128', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('77', '天海翼', '5', '98', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('78', '水菜麗', '5', '127', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('79', '泷泽萝拉', '5', '123', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('81', '熟女人妻', '5', '111', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('82', '美颜巨乳', '5', '112', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('83', '颜射吃精', '5', '113', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('84', '丝袜制服', '5', '114', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('85', '无码中字', '5', '115', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('86', '精彩短片', '5', '116', '1', '2017-07-20 04:59:37');
INSERT INTO `vj_type` VALUES ('87', '本月最佳', '6', 'month|0', '0', '2018-01-15 20:02:27');
INSERT INTO `vj_type` VALUES ('88', '上月最佳', '6', 'month|-1', '0', '2018-01-15 20:02:55');
INSERT INTO `vj_type` VALUES ('91', 'fc2素人', '7', 'fc2', '0', '2018-01-15 22:33:11');
INSERT INTO `vj_type` VALUES ('92', '内射', '7', 'cum-inside', '0', '2018-01-15 22:33:41');
INSERT INTO `vj_type` VALUES ('93', '大奶', '7', 'busty', '0', '2018-01-15 22:34:04');
INSERT INTO `vj_type` VALUES ('94', '学生', '7', 'student', '0', '2018-01-15 22:34:17');
INSERT INTO `vj_type` VALUES ('95', '本土', '7', 'native', '0', '2018-01-15 22:34:41');
INSERT INTO `vj_type` VALUES ('96', '美女', '7', 'beautiful', '0', '2018-01-15 22:34:56');
INSERT INTO `vj_type` VALUES ('97', '迷奸', '7', 'married', '0', '2018-01-15 22:36:33');
INSERT INTO `vj_type` VALUES ('98', '野外', '7', 'public', '0', '2018-01-15 22:36:53');

-- ----------------------------
-- Table structure for vj_ulogin_record
-- ----------------------------
DROP TABLE IF EXISTS `vj_ulogin_record`;
CREATE TABLE `vj_ulogin_record` (
  `record_id` bigint(16) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ua` varchar(512) NOT NULL COMMENT '登录浏览器UA',
  `client_ip` varchar(64) NOT NULL COMMENT '登录IP',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `user_id` int(11) NOT NULL COMMENT '使用会员ID',
  PRIMARY KEY (`record_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员登录记录表';


-- ----------------------------
-- Table structure for vj_user
-- ----------------------------
DROP TABLE IF EXISTS `vj_user`;
CREATE TABLE `vj_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `qq` varchar(30) NOT NULL DEFAULT '' COMMENT '绑定QQ号',
  `pwd` varchar(64) NOT NULL COMMENT '密码',
  `sid` varchar(64) NOT NULL COMMENT '登录SID',
  `last_login_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后一次登录时间',
  `expire_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '到期时间',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '会员状态: 0-正常,1-管理员锁定',
  `classify` varchar(255) NOT NULL DEFAULT 'default' COMMENT '会员分类',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `sid` (`sid`),
  KEY `sid_2` (`sid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='会员表';

-- ----------------------------
-- Records of vj_user
-- ----------------------------
# 账号1000 密码 1000.
INSERT INTO `vj_user` VALUES ('1000', '971626354', '9b91c59b464daedaf1945e662c951413a843b260', '04a4562c6f26f4d36ca4f53572986a65b99eee58', '2018-02-05 11:13:02', '2018-12-22 08:59:51', '2017-10-19 16:19:04', '0', 'default');

-- ----------------------------
-- Table structure for vj_video
-- ----------------------------
DROP TABLE IF EXISTS `vj_video`;
CREATE TABLE `vj_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `video_id` varchar(30) NOT NULL COMMENT 'av视频ID',
  `title` varchar(500) NOT NULL COMMENT '视频标题',
  `video_url` varchar(500) NOT NULL COMMENT '视频文件地址',
  `video_img` varchar(500) NOT NULL COMMENT '视频介绍图片',
  `video_time` varchar(50) NOT NULL COMMENT '视频时长',
  `type_id` int(11) unsigned NOT NULL COMMENT '分类id',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `local` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否本地文件: 1是，0否',
  PRIMARY KEY (`id`),
  UNIQUE KEY `video_id` (`video_id`) USING BTREE,
  KEY `type_id` (`type_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for vj_video_coll
-- ----------------------------
DROP TABLE IF EXISTS `vj_video_coll`;
CREATE TABLE `vj_video_coll` (
  `coll_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `video_id` varchar(100) NOT NULL COMMENT '关联视频表ID',
  `play_num` int(11) NOT NULL DEFAULT '0' COMMENT '播放量',
  `is_local_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否下载到本地删除过: 1-是;0-否',
  `down_status` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '下载任务状态: -1-不下载; 0-标记等待下载; 1-下载完成; 2-下载失败',
  `create_task_time` bigint(16) NOT NULL DEFAULT '0' COMMENT '创建下载任务时间戳',
  `down_msg` varchar(4096) NOT NULL DEFAULT '' COMMENT '下载错误信息',
  `digest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '精品视频: 1-是;0-不是',
  PRIMARY KEY (`coll_id`),
  UNIQUE KEY `video_id` (`video_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


DROP TRIGGER IF EXISTS `add_coll`;
DELIMITER ;;
CREATE TRIGGER `add_coll` AFTER INSERT ON `vj_video` FOR EACH ROW begin
	set @record = (select video_id from vj_video_coll where video_id=new.video_id);
	if @record is null THEN
		insert into vj_video_coll(video_id) value (new.video_id);
	end if;
end
;;
DELIMITER ;
