
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for vt_area
-- ----------------------------
DROP TABLE IF EXISTS `vt_area`;
CREATE TABLE `vt_area` (
  `areaid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地区ID',
  `areaname` varchar(50) NOT NULL DEFAULT '' COMMENT '地区名',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级地区ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `childs` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '直接子级数',
  `listorder` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  PRIMARY KEY (`areaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='地区数据';

-- ----------------------------
-- Table structure for vt_category
-- ----------------------------
DROP TABLE IF EXISTS `vt_category`;
CREATE TABLE `vt_category` (
  `catid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '类别标题',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '图标',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `listorder` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(默认1显示)',
  `sign` varchar(10) NOT NULL DEFAULT '' COMMENT '扩展标识',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '区分',
  PRIMARY KEY (`catid`),
  KEY `type` (`type`) USING BTREE,
  KEY `sign` (`sign`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公用类别';

-- ----------------------------
-- Records of vt_category
-- ----------------------------
INSERT INTO `vt_category` VALUES ('1', '首页', 'layui-icon-home', '0', '', '1', '0', '', '01');
INSERT INTO `vt_category` VALUES ('2', '首页', 'layui-icon-home', '0', '', '1', '0', '', '02');

-- ----------------------------
-- Table structure for vt_login_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_login_log`;
CREATE TABLE `vt_login_log` (
  `logid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '登录帐号',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `passsalt` varchar(8) NOT NULL DEFAULT '' COMMENT '秘钥',
  `admin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '登录类型0:后台1:会员',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `logintime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `message` varchar(255) NOT NULL DEFAULT '' COMMENT '状态信息',
  `agent` varchar(255) NOT NULL DEFAULT '' COMMENT '登录端设备信息',
  PRIMARY KEY (`logid`),
  KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='登录日志';

-- ----------------------------
-- Table structure for vt_manager
-- ----------------------------
DROP TABLE IF EXISTS `vt_manager`;
CREATE TABLE `vt_manager` (
  `userid` mediumint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '帐号',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `passsalt` varchar(8) NOT NULL COMMENT '秘钥',
  `roleid` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '角色ID',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `truename` varchar(30) NOT NULL DEFAULT '' COMMENT '姓名',
  `nickname` varchar(30) NOT NULL DEFAULT '' COMMENT '昵称',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别:1男2女',
  `face` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(30) NOT NULL DEFAULT '' COMMENT '邮箱',
  `areaid` varchar(30) NOT NULL DEFAULT '0' COMMENT '地区ID串',
  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '详细地址',
  `loginip` varchar(15) NOT NULL DEFAULT '' COMMENT '最近登录IP',
  `logins` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `logintime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最近登录时间',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `edittime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  `edit` varchar(30) NOT NULL DEFAULT '' COMMENT '操作帐号',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理用户';

-- ----------------------------
-- Records of vt_manager
-- ----------------------------
INSERT INTO `vt_manager` VALUES ('1', 'admin', '7e618aaa25356b3049e608a9a29790b1', 'Rt4UBXRZ', '1', '1', '超管员', '超管', '1', '', '15900000001', '26843818@qq.com', '20', '广州', '127.0.0.1', '1', '1671545877', '1553999489', '1671423568', 'system', '1');

-- ----------------------------
-- Table structure for vt_manager_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_manager_log`;
CREATE TABLE `vt_manager_log` (
  `logid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '用户',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `logtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`logid`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理日志';

-- ----------------------------
-- Table structure for vt_menus
-- ----------------------------
DROP TABLE IF EXISTS `vt_menus`;
CREATE TABLE `vt_menus` (
  `menuid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `catid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识名称',
  `menu_name` varchar(50) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `role_name` varchar(50) NOT NULL DEFAULT '' COMMENT '权限名称',
  `link_url` varchar(255) NOT NULL DEFAULT '' COMMENT '外链',
  `menu_url` varchar(255) NOT NULL DEFAULT '' COMMENT '控制路径',
  `role_url` varchar(255) NOT NULL DEFAULT '' COMMENT '权限路径多个逗号隔开',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `listorder` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `ismenu` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0菜单不显示',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1:后台菜单2:会员菜单',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`menuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜单权限';

-- ----------------------------
-- Records of vt_menus
-- ----------------------------
INSERT INTO `vt_menus` VALUES ('1', '1', '', '系统面板', '系统面板', '', '', 'index/index,index/json,index/clear,index/ip', 'layui-icon-home', '0', '1', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('2', '1', '', '系统管理', '系统管理', '', '', '', 'layui-icon-set', '0', '3', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('3', '1', '', '首页面板', '首页面板', '', 'index/main', 'index/main', '', '1', '1', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('4', '1', '', '插件管理', '插件管理', '', 'addon/index', '', '', '1', '3', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('5', '1', '', '插件列表', '插件列表', '', '', 'addon/index', '', '4', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('6', '1', '', '插件安装', '插件安装', '', '', 'addon/install', '', '4', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('7', '1', '', '插件配置', '插件配置', '', '', 'addon/set', '', '4', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('8', '1', '', '插件卸载', '插件卸载', '', '', 'addon/unstall', '', '4', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('9', '1', '', '配置管理', '配置管理', '', '', 'addon/setting', '', '4', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('10', '1', '', '配置更新', '配置更新', '', '', 'addon/setup', '', '4', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('11', '1', '', '设配置项', '设配置项', '', 'system.setting/build', '', '', '2', '1', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('12', '1', '', '管理配置', '管理配置', '', 'system.setting/index', '', '', '2', '2', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('13', '1', '', '后台菜单', '后台菜单', '', 'system.menus/index', '', '', '2', '3', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('14', '1', '', '用户角色', '用户角色', '', 'system.roles/index', '', '', '2', '4', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('15', '1', '', '用户管理', '用户管理', '', 'system.manager/index', '', '', '2', '5', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('16', '1', '', '上传管理', '上传管理', '', 'system.upload/image', '', '', '2', '6', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('17', '1', '', '文件管理', '文件管理', '', 'system.filemanage/index', '', '', '2', '6', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('18', '1', '', '日志管理', '登录日志', '', 'system.log/index', 'system.log/index', '', '2', '7', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('19', '1', '', '地区管理', '地区管理', '', 'system.area/index', '', '', '2', '8', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('20', '1', '', '短信管理', '短信管理', '', 'system.sms/index', '', '', '2', '9', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('21', '1', '', '数据维护', '数据维护', '', 'system.database/index', '', '', '2', '12', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('22', '1', '', '在线用户', '在线用户', '', 'system.online/index', '', '', '2', '13', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('23', '1', '', '数据字典', '数据字典', '', 'system.dict/index', '', '', '2', '15', '1', '1', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('24', '1', '', '配置项列表', '配置项列表', '', '', 'system.setting/build', '', '11', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('25', '1', '', '配置项添加', '配置项添加', '', '', 'system.setting/badd', '', '11', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('26', '1', '', '配置项编辑', '配置项编辑', '', '', 'system.setting/bedit', '', '11', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('27', '1', '', '配置项删除', '配置项删除', '', '', 'system.setting/bdel', '', '11', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('28', '1', '', '配置项导出', '配置项导出', '', '', 'system.setting/bout', '', '11', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('29', '1', '', '配置项导入', '配置项导入', '', '', 'system.setting/bup', '', '11', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('30', '1', '', '查看配置', '查看配置', '', '', 'system.setting/index', '', '12', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('31', '1', '', '修改配置', '修改配置', '', '', 'system.setting/edit', '', '12', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('32', '1', '', '查看菜单', '查看菜单', '', '', 'system.menus/index', '', '13', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('33', '1', '', '添加菜单', '添加菜单', '', '', 'system.menus/add', '', '13', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('34', '1', '', '添加菜单批量', '添加菜单批量', '', '', 'system.menus/adds', '', '13', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('35', '1', '', '编辑菜单', '编辑菜单', '', '', 'system.menus/edit', '', '13', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('36', '1', '', '删除菜单', '删除菜单', '', '', 'system.menus/del', '', '13', '5', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('37', '1', '', '菜单重构', '菜单重构', '', '', 'system.menus/reset', '', '13', '6', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('38', '1', '', '菜单类别', '菜单类别', '', '', 'system.menus/category', '', '13', '7', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('39', '1', '', '类别添加', '类别添加', '', '', 'system.menus/catadd', '', '13', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('40', '1', '', '类别编辑', '类别编辑', '', '', 'system.menus/catedit', '', '13', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('41', '1', '', '类别删除', '类别删除', '', '', 'system.menus/catdel', '', '13', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('42', '1', '', '菜单导出', '菜单导出', '', '', 'system.menus/out', '', '13', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('43', '1', '', '菜单导入', '菜单导入', '', '', 'system.menus/up', '', '13', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('44', '1', '', '菜单重构', '菜单重构', '', '', 'system.menus/reset', '', '13', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('45', '1', '', '角色列表', '角色列表', '', '', 'system.roles/index', '', '14', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('46', '1', '', '角色添加', '角色添加', '', '', 'system.roles/add', '', '14', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('47', '1', '', '角色编辑', '角色编辑', '', '', 'system.roles/edit', '', '14', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('48', '1', '', '角色删除', '角色删除', '', '', 'system.roles/del', '', '14', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('49', '1', '', '用户查看', '用户查看', '', '', 'system.manager/index', '', '15', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('50', '1', '', '用户添加', '用户添加', '', '', 'system.manager/add', '', '15', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('51', '1', '', '用户编辑', '用户编辑', '', '', 'system.manager/edit', '', '15', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('52', '1', '', '用户删除', '用户删除', '', '', 'system.manager/del', '', '15', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('53', '1', '', '修改密码', '修改密码', '', '', 'system.manager/changpwd', '', '15', '5', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('54', '1', '', '重置密码', '重置密码', '', '', 'system.manager/resetpwd', '', '15', '6', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('55', '1', '', '用户中心', '用户中心', '', '', 'system.manager/index/info', '', '15', '7', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('56', '1', '', '个人修改', '个人修改', '', '', 'system.manager/edits', '', '15', '8', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('57', '1', '', '机构添加', '机构添加', '', '', 'system.manager/oadd', '', '15', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('58', '1', '', '机构编辑', '机构编辑', '', '', 'system.manager/oedit', '', '15', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('59', '1', '', '机构删除', '机构删除', '', '', 'system.manager/odel', '', '15', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('60', '1', '', '上传入口', '上传入口', '', '', '', '', '16', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('61', '1', '', '弹出文件管理', '弹出文件管理', '', '', '', '', '16', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('62', '1', '', '百度编辑器', '百度编辑器', '', '', '', '', '16', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('63', '1', '', '上传图片', '上传图片', '', '', 'system.upload/upfile/image', '', '60', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('64', '1', '', '上传视频', '上传视频', '', '', 'system.upload/upfile/video', '', '60', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('65', '1', '', '上传文件', '上传文件', '', '', 'system.upload/upfile/file', '', '60', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('66', '1', '', '上传音频', '上传音频', '', '', 'system.upload/upfile/audio', '', '60', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('67', '1', '', '文件列表', '文件列表', '', '', 'system.upload/files', '', '61', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('68', '1', '', '文件移动', '文件移动', '', '', 'system.upload/files/move', '', '61', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('69', '1', '', '文件删除', '文件删除', '', '', 'system.upload/files/del', '', '61', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('70', '1', '', '分组添加', '分组添加', '', '', 'system.upload/group/add', '', '61', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('71', '1', '', '分组编辑', '分组编辑', '', '', 'system.upload/group/edit', '', '61', '5', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('72', '1', '', '分组删除', '分组删除', '', '', 'system.upload/group/del', '', '61', '6', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('73', '1', '', '接口配置', '接口配置', '', '', 'system.upload/ueditor/config', '', '62', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('74', '1', '', '上传图片', '上传图片', '', '', 'system.upload/ueditor/image', '', '62', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('75', '1', '', '上传视频', '上传视频', '', '', 'system.upload/ueditor/video', '', '62', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('76', '1', '', '上传附件', '上传附件', '', '', 'system.upload/ueditor/file', '', '62', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('77', '1', '', '图片列表', '图片列表', '', '', 'system.upload/ueditor/listimage', '', '62', '5', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('78', '1', '', '附件列表', '附件列表', '', '', 'system.upload/ueditor/listfile', '', '62', '6', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('79', '1', '', '文件列表', '文件列表', '', '', 'system.filemanage/index', '', '17', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('80', '1', '', '文件名编辑', '文件名编辑', '', '', 'system.filemanage/edit', '', '17', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('81', '1', '', '文件软删除', '文件软删除', '', '', 'system.filemanage/del', '', '17', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('82', '1', '', '文件恢复', '文件恢复', '', '', 'system.filemanage/reset', '', '17', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('83', '1', '', '文件清理', '文件清理', '', '', 'system.filemanage/clear', '', '17', '5', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('84', '1', '', '登录日志', '日志查看', '', '', 'system.log/login', '', '18', '1', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('85', '1', '', '登录日志清理', '日志删除', '', '', 'system.log/ldel', '', '18', '2', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('86', '1', '', '后台日志', '后台日志', '', '', 'system.log/manager', '', '18', '3', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('87', '1', '', '后台日志清理', '日志列表', '', '', 'system.log/mdel', '', '18', '4', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('88', '1', '', '访问日志', '访问日志', '', '', 'system.log/web', '', '18', '5', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('89', '1', '', '访问日志清理', '访问日志清理', '', '', 'system.log/wdel', '', '18', '6', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('90', '1', '', '地区列表', '地区列表', '', '', 'system.area/index', '', '19', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('91', '1', '', '地区添加', '地区添加', '', '', 'system.area/add', '', '19', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('92', '1', '', '地区编辑', '地区编辑', '', '', 'system.area/edit', '', '19', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('93', '1', '', '地区删除', '地区删除', '', '', 'system.area/del', '', '19', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('94', '1', '', '内置导入', '内置导入', '', '', 'system.area/import', '', '19', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('95', '1', '', '发送记录', '发送记录', '', '', 'system.sms/index', '', '20', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('96', '1', '', '发送短信', '发送短信', '', '', 'system.sms/send', '', '20', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('97', '1', '', '记录删除', '记录删除', '', '', 'system.sms/del', '', '20', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('98', '1', '', '数据列表', '数据列表', '', '', 'system.database/index', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('99', '1', '', '数据备份', '数据备份', '', '', 'system.database/backup', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('100', '1', '', '备份列表', '备份列表', '', '', 'system.database/imports', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('101', '1', '', '备份恢复', '备份恢复', '', '', 'system.database/import', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('102', '1', '', '备份删除', '备份删除', '', '', 'system.database/del', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('103', '1', '', '注释修改', '注释修改', '', '', 'system.database/edit', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('104', '1', '', '查看字典', '查看字典', '', '', 'system.database/dict', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('105', '1', '', '备份下载', '备份下载', '', '', 'system.database/download', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('106', '1', '', '数据表修复', '数据表修复', '', '', 'system.database/xiufu', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('107', '1', '', '数据表优化', '数据表优化', '', '', 'system.database/youhua', '', '21', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('108', '1', '', '用户列表', '用户列表', '', '', 'system.online/index', '', '22', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('109', '1', '', '字典列表', '字典列表', '', '', 'system.dict/index', '', '23', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('110', '1', '', '字典添加', '字典添加', '', '', 'system.dict/add', '', '23', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('111', '1', '', '字典编辑', '字典编辑', '', '', 'system.dict/edit', '', '23', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('112', '1', '', '字典删除', '字典删除', '', '', 'system.dict/del', '', '23', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('113', '1', '', '字典组添加', '字典组添加', '', '', 'system.dict/gadd', '', '23', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('114', '1', '', '字典组编辑', '字典组编辑', '', '', 'system.dict/gedit', '', '23', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('115', '1', '', '字典组删除', '字典组删除', '', '', 'system.dict/gdel', '', '23', '10', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('116', '1', '', '字典项列表', '字典项列表', '', '', 'system.dict/items', '', '23', '11', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('117', '1', '', '字典项添加', '字典项添加', '', '', 'system.dict/iadd', '', '23', '12', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('118', '1', '', '字典项批量', '字典项批量', '', '', 'system.dict/iadds', '', '23', '13', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('119', '1', '', '字典项编辑', '字典项编辑', '', '', 'system.dict/iedit', '', '23', '14', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('120', '1', '', '字典项删除', '字典项删除', '', '', 'system.dict/idel', '', '23', '15', '0', '0', '1', '1700289715');
INSERT INTO `vt_menus` VALUES ('121', '1', '', '字典项接口', '字典项接口', '', '', 'system.dict/json', '', '23', '16', '0', '0', '1', '1700289715');

-- ----------------------------
-- Table structure for vt_online
-- ----------------------------
DROP TABLE IF EXISTS `vt_online`;
CREATE TABLE `vt_online` (
  `uid` varchar(30) NOT NULL DEFAULT '' COMMENT '编号',
  `userid` varchar(20) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '会员帐号',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '所在路径',
  `ip` varchar(30) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `online` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否在线',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:后台1:会员',
  `etime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后时间',
  UNIQUE KEY `uid` (`uid`,`userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='在线用户';

-- ----------------------------
-- Table structure for vt_organ
-- ----------------------------
DROP TABLE IF EXISTS `vt_organ`;
CREATE TABLE `vt_organ` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '机构简称',
  `titles` varchar(200) NOT NULL DEFAULT '' COMMENT '机构全称',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `listorder` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组织机构';

-- ----------------------------
-- Records of vt_organ
-- ----------------------------
INSERT INTO `vt_organ` VALUES ('1', 'Veitool', 'Veitool总部', '0', '', '1', '');
INSERT INTO `vt_organ` VALUES ('2', '市场部', '市场部', '1', '1', '1', '');
INSERT INTO `vt_organ` VALUES ('3', '售前组', '售前组', '2', '1,2', '1', '');
INSERT INTO `vt_organ` VALUES ('4', '售后组', '售后组', '2', '1,2', '1', '');
INSERT INTO `vt_organ` VALUES ('5', '研发部', '研发部', '1', '1', '1', '');
INSERT INTO `vt_organ` VALUES ('6', '设计部', '设计部', '1', '1', '1', '');

-- ----------------------------
-- Table structure for vt_roles
-- ----------------------------
DROP TABLE IF EXISTS `vt_roles`;
CREATE TABLE `vt_roles` (
  `roleid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `role_name` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名称',
  `role_menuid` text NOT NULL COMMENT '权限菜单项ID串',
  `listorder` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0禁用',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理角色';

-- ----------------------------
-- Records of vt_roles
-- ----------------------------
INSERT INTO `vt_roles` VALUES ('1', '超级管理员', '', '1', '1', '1552297670');
INSERT INTO `vt_roles` VALUES ('2', '系统管理员', '', '2', '1', '1552297670');

-- ----------------------------
-- Table structure for vt_setting
-- ----------------------------
DROP TABLE IF EXISTS `vt_setting`;
CREATE TABLE `vt_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称',
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT '标题',
  `group` varchar(32) NOT NULL DEFAULT '' COMMENT '配置分组',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型',
  `value` text NOT NULL COMMENT '配置值',
  `options` text NOT NULL COMMENT '配置项',
  `tips` varchar(256) NOT NULL DEFAULT '' COMMENT '配置提示',
  `relation` varchar(100) NOT NULL DEFAULT '' COMMENT '关联',
  `private` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐私',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `edittime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `listorder` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `addon` varchar(30) NOT NULL DEFAULT '' COMMENT '插件标识',
  `state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0禁用,1启用',
  PRIMARY KEY (`id`),
  KEY `addon` (`addon`) USING BTREE,
  KEY `group` (`group`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统配置';

-- ----------------------------
-- Records of vt_setting
-- ----------------------------
INSERT INTO `vt_setting` VALUES ('1', 'sys_group', '配置分组', 'system', 'array', 'system:系统\nsms:短信\nupload:上传', '', '配置分组，每行为一组如：标识:组名', '', '0', '1475240646', '1475240646', '1', '', '1');
INSERT INTO `vt_setting` VALUES ('2', 'sys_type', '配置类型', 'system', 'array', 'text:单行文本\ntextarea:多行文本\nstatic:静态文本\npassword:密码\ncheckbox:复选框\nradio:单选按钮\nyear:年选择器\nmonth:年月选择器\ndate:日期选择器\ntime:时间选择器\ndatetime:日期+时间选择器\nswitch:开关\narray:数组\nkeyval:键值对\nselect:下拉框\ntags:标签\nimage:单张图片\nimages:多张图片\nnumber:数字\nupfile:文件上传\ncolorpicker:取色器\nueditor:百度编辑器\ncherrymd:CherryMarkdown\neditormd:Editor.md\ntinymce:TinyMCE编辑器', '', '配置类型，每行为一组如：标识:说明', '', '0', '1475240646', '1475240646', '2', '', '1');
INSERT INTO `vt_setting` VALUES ('3', 'sys_title', '面板名称', 'system', 'text', '后台管理', '', '后台管理面板显示的名称', '', '0', '1593860369', '0', '2', '', '1');
INSERT INTO `vt_setting` VALUES ('4', 'admin_captcha', '后台验证', 'system', 'switch', '0', '', '后台管理员登录是否开启图形验证码', '', '0', '1599057178', '0', '3', '', '1');
INSERT INTO `vt_setting` VALUES ('5', 'admin_log', '后台日志', 'system', 'switch', '0', '', '是否开启后台操作日志', '', '0', '1612773267', '0', '4', '', '1');
INSERT INTO `vt_setting` VALUES ('6', 'home_log', '前台日志', 'system', 'switch', '0', '', '是否开启前台访问日志', '', '0', '1658285258', '0', '5', '', '1');
INSERT INTO `vt_setting` VALUES ('7', 'online_on', '在线状态', 'system', 'radio', '1', '0:全部关闭\n1:后台开启\n2:会员开启\n3:全部开启', '控制是否开启用户在线状态记录', '', '0', '1653019956', '0', '10', '', '1');
INSERT INTO `vt_setting` VALUES ('8', 'ip_login', '异地登录', 'system', 'radio', '0', '0:全部允许\n1:后台允许\n2:前台允许\n3:全部禁止', '控制是否允许同帐号同时异地登录', '', '0', '1653044144', '0', '10', '', '1');
INSERT INTO `vt_setting` VALUES ('9', 'sys_filter', '过滤字符', 'system', 'textarea', '', '', '多个用以英文逗号,隔开', '', '0', '1694527459', '0', '10', '', '1');
INSERT INTO `vt_setting` VALUES ('10', 'sms_state', '短信开关', 'sms', 'switch', '1', '', '', '', '0', '1593356677', '0', '1', '', '1');
INSERT INTO `vt_setting` VALUES ('11', 'sms_type', '发送方式', 'sms', 'radio', 'qiniu', 'qiniu:七牛短信\nsmsbao:短信宝', '发送短信的方式', 'sm', '0', '1633231217', '0', '1', '', '1');
INSERT INTO `vt_setting` VALUES ('12', 'sms_user', '接口ID/KEY', 'sms', 'text', '', '', '七牛 access_key', 'sm_qiniu', '1', '1593356855', '0', '2', '', '1');
INSERT INTO `vt_setting` VALUES ('13', 'sms_pass', '短信秘钥', 'sms', 'text', '', '', '七牛 secret_key', 'sm_qiniu', '1', '1593356912', '0', '3', '', '1');
INSERT INTO `vt_setting` VALUES ('14', 'sms_temp', '默认短信模板', 'sms', 'text', '', '', '七牛短信模板号', 'sm_qiniu', '0', '1593356951', '0', '4', '', '1');
INSERT INTO `vt_setting` VALUES ('15', 'sms_baouser', '短信宝帐号', 'sms', 'text', '', '', '短信宝接口帐号', 'sm_smsbao', '0', '1633231494', '0', '10', '', '1');
INSERT INTO `vt_setting` VALUES ('16', 'sms_baopass', '短信宝接口密码', 'sms', 'text', '', '', '短信宝接口密码', 'sm_smsbao', '1', '1633231533', '0', '10', '', '1');
INSERT INTO `vt_setting` VALUES ('17', 'sms_times', '发送时间间隔', 'sms', 'number', '', '', '发送短信的时间间隔，单位秒', '', '0', '1609054359', '0', '11', '', '1');
INSERT INTO `vt_setting` VALUES ('18', 'upload_image_type', '可传图片类型', 'upload', 'tags', 'jpg,png,gif,jpeg', '', '本地允许上传的图片类型', '', '0', '1592229542', '0', '1', '', '1');
INSERT INTO `vt_setting` VALUES ('19', 'upload_file_type', '可传文件类型', 'upload', 'tags', 'rar,zip,pdf,docx,doc,xlsx,xls', '', '本地允许上传的文件类型', '', '0', '1592798598', '0', '2', '', '1');
INSERT INTO `vt_setting` VALUES ('20', 'upload_video_type', '可传视频类型', 'upload', 'tags', 'mp4,flv,wmv,avi,mov,mpeg', '', '本地允许上传的视频类型', '', '0', '1592798848', '0', '3', '', '1');
INSERT INTO `vt_setting` VALUES ('21', 'upload_audio_type', '可传音频类型', 'upload', 'tags', 'mp3', '', '本地允许上传的音频类型', '', '0', '1592798923', '0', '4', '', '1');
INSERT INTO `vt_setting` VALUES ('22', 'upload_image_size', '上传图片大小上限', 'upload', 'number', '2', '', '允许上传图片大小上限（Mb）', '', '0', '1592879705', '0', '5', '', '1');
INSERT INTO `vt_setting` VALUES ('23', 'upload_file_size', '上传文件大小上限', 'upload', 'number', '10', '', '允许上传文件大小上限（Mb）', '', '0', '1592879775', '0', '6', '', '1');
INSERT INTO `vt_setting` VALUES ('24', 'upload_video_size', '上传视频大小上限', 'upload', 'number', '20', '', '允许上传视频大小上限（Mb）', '', '0', '1592879855', '0', '7', '', '1');
INSERT INTO `vt_setting` VALUES ('25', 'upload_audio_size', '上传音频大小上限', 'upload', 'number', '20', '', '允许上传音频大小上限（Mb）', '', '0', '1592879953', '0', '8', '', '1');
INSERT INTO `vt_setting` VALUES ('26', 'upload_engine', '上传方式', 'upload', 'radio', 'local', 'local:本地\nqiniu:七牛云存储\naliyun:阿里云OSS\nqcloud:腾讯云COS', '上传文件所保存的位置', 'up', '0', '1592125741', '0', '9', '', '1');
INSERT INTO `vt_setting` VALUES ('27', 'qiniu_bucket', '空间名称 Bucket', 'upload', 'text', '', '', '七牛云存储 Bucket', 'up_qiniu', '0', '1592126223', '0', '10', '', '1');
INSERT INTO `vt_setting` VALUES ('28', 'access_key', 'ACCESS_KEY AK', 'upload', 'text', '', '', '七牛云存储 ACCESS_KEY', 'up_qiniu', '1', '1592126291', '0', '11', '', '1');
INSERT INTO `vt_setting` VALUES ('29', 'qiniu_secret_key', 'SECRET_KEY SK', 'upload', 'text', '', '', '七牛云存储 SECRET_KEY', 'up_qiniu', '1', '1592126338', '0', '12', '', '1');
INSERT INTO `vt_setting` VALUES ('30', 'qiniu_domain', '空间域名 Domain', 'upload', 'text', '', '', '七牛云存储 请补全http:// 或 https://，例如：http://v.abc.com', 'up_qiniu', '0', '1592126400', '0', '13', '', '1');
INSERT INTO `vt_setting` VALUES ('31', 'aliyun_bucket', '空间名称 Bucket', 'upload', 'text', '', '', '阿里云OSS Bucket', 'up_aliyun', '0', '1592126223', '0', '14', '', '1');
INSERT INTO `vt_setting` VALUES ('32', 'access_key_id', 'AccessKeyId', 'upload', 'text', '', '', '阿里云OSS AccessKeyId', 'up_aliyun', '1', '1592126291', '0', '15', '', '1');
INSERT INTO `vt_setting` VALUES ('33', 'access_key_secret', 'AccessKeySecret', 'upload', 'text', '', '', '阿里云OSS AccessKeySecret', 'up_aliyun', '1', '1592126338', '0', '16', '', '1');
INSERT INTO `vt_setting` VALUES ('34', 'aliyun_domain', '空间域名 Domain', 'upload', 'text', '', '', '阿里云OSS 请补全http:// 或 https://，例如：http://v.abc.com', 'up_aliyun', '0', '1592126400', '0', '17', '', '1');
INSERT INTO `vt_setting` VALUES ('35', 'qcloud_bucket', '空间名称 Bucket', 'upload', 'text', '', '', '腾讯云COS Bucket', 'up_qcloud', '0', '1592126223', '0', '18', '', '1');
INSERT INTO `vt_setting` VALUES ('36', 'region', '所属地域 Region', 'upload', 'text', '', '', '腾讯云COS Region', 'up_qcloud', '0', '1592126291', '0', '19', '', '1');
INSERT INTO `vt_setting` VALUES ('37', 'secret_id', 'SecretId', 'upload', 'text', '', '', '腾讯云COS SecretId', 'up_qcloud', '1', '1592126338', '0', '20', '', '1');
INSERT INTO `vt_setting` VALUES ('38', 'qcloud_secret_key', 'SecretKey', 'upload', 'text', '', '', '腾讯云COS SecretKey', 'up_qcloud', '1', '1592126400', '0', '21', '', '1');
INSERT INTO `vt_setting` VALUES ('39', 'qcloud_domain', '空间域名 Domain', 'upload', 'text', '', '', '腾讯云COS 请补全http:// 或 https://，例如：http://v.abc.com', 'up_qcloud', '0', '1592126400', '0', '22', '', '1');

-- ----------------------------
-- Table structure for vt_sms
-- ----------------------------
DROP TABLE IF EXISTS `vt_sms`;
CREATE TABLE `vt_sms` (
  `itemid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '手机号',
  `message` text NOT NULL COMMENT '短信内容',
  `word` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '短信字数',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '操作者',
  `sendtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `code` varchar(200) NOT NULL DEFAULT '' COMMENT '错误提示',
  PRIMARY KEY (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信记录';

-- ----------------------------
-- Table structure for vt_upload_file
-- ----------------------------
DROP TABLE IF EXISTS `vt_upload_file`;
CREATE TABLE `vt_upload_file` (
  `fileid` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `storage` varchar(20) NOT NULL DEFAULT '' COMMENT '存储方式',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件分组id',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '所属会员账户',
  `admin` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '帐号平台1:后台2:会员',
  `fileurl` varchar(255) NOT NULL DEFAULT '' COMMENT '存储路径',
  `filename` varchar(200) NOT NULL DEFAULT '' COMMENT '文件名称',
  `filesize` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '文件大小',
  `filetype` varchar(20) NOT NULL DEFAULT '' COMMENT '文件类型',
  `fileext` varchar(20) NOT NULL DEFAULT '' COMMENT '文件扩展名',
  `isdel` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '软删除',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`fileid`),
  KEY `groupid` (`groupid`) USING BTREE,
  KEY `isdel` (`isdel`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `admin` (`admin`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='上传文件';

-- ----------------------------
-- Table structure for vt_upload_group
-- ----------------------------
DROP TABLE IF EXISTS `vt_upload_group`;
CREATE TABLE `vt_upload_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `grouptype` varchar(10) NOT NULL DEFAULT '' COMMENT '文件类型',
  `groupname` varchar(30) NOT NULL DEFAULT '' COMMENT '分类名称',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类排序',
  `isdel` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `edittime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='上传分组';

-- ----------------------------
-- Records of vt_upload_group
-- ----------------------------
INSERT INTO `vt_upload_group` VALUES ('1', 'image', '系统配置', '1', '0', '1592707159', '1647692564');

-- ----------------------------
-- Table structure for vt_web_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_web_log`;
CREATE TABLE `vt_web_log` (
  `logid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '会员帐号',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '访问地址',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `logtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访问时间',
  `agent` varchar(255) NOT NULL DEFAULT '' COMMENT '设备信息',
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='访问日志';

-- ----------------------------
-- Table structure for vt_dict
-- ----------------------------
DROP TABLE IF EXISTS `vt_dict`;
CREATE TABLE `vt_dict` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '项名',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '项值',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '编辑',
  `state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0禁用,1启用',
  PRIMARY KEY (`id`),
  KEY `parentid` (`parentid`) USING BTREE,
  KEY `groupid` (`groupid`) USING BTREE,
  KEY `state` (`state`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='字典项目';

-- ----------------------------
-- Records of vt_dict
-- ----------------------------
INSERT INTO `vt_dict` VALUES ('1', '支付宝支付', '支付宝支付', '4', '0', '', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('2', '微信支付', '微信支付', '4', '0', '', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('3', '银联支付', '银联支付', '4', '0', '', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('4', '企业支付', '企业支付', '4', '1', '1', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('5', '个体支付', '个体支付', '4', '1', '1', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('6', '个', '个', '5', '0', '', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('7', '件', '件', '5', '0', '', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('8', '部', '部', '5', '0', '', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('9', '套', '套', '5', '0', '', '100', '1699363736', 'admin', '1');
INSERT INTO `vt_dict` VALUES ('10', '箱', '箱', '5', '0', '', '100', '1699363736', 'admin', '1');

-- ----------------------------
-- Table structure for vt_dict_group
-- ----------------------------
DROP TABLE IF EXISTS `vt_dict_group`;
CREATE TABLE `vt_dict_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `code` varchar(30) NOT NULL DEFAULT '' COMMENT '字典编码',
  `sql` varchar(500) NOT NULL DEFAULT '' COMMENT 'SQL查表语句',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '字典类型0:类型1:列表2:树形n其他',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '为字典类时的父级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '编辑',
  `note` varchar(200) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='字典分组';

-- ----------------------------
-- Records of vt_dict_group
-- ----------------------------
INSERT INTO `vt_dict_group` VALUES ('1', '字典类型', '', '', '0', '0', '', '0', '', '');
INSERT INTO `vt_dict_group` VALUES ('2', '列表', '', '', '0', '1', '1', '0', '', '');
INSERT INTO `vt_dict_group` VALUES ('3', '树形', '', '', '0', '1', '1', '0', '', '');
INSERT INTO `vt_dict_group` VALUES ('4', '支付类型', 'PAY_TYPE', '', '3', '0', '', '1699363736', 'admin', '');
INSERT INTO `vt_dict_group` VALUES ('5', '常用单位', 'DAN_TYPE', '', '2', '0', '', '1699365171', 'admin', '');
INSERT INTO `vt_dict_group` VALUES ('6', '组织机构', 'ORGAN', 'select id,title as name,id as value,parentid as pid,arrparentid as pids from vt_organ', '3', '0', '', '1700296237', 'admin', 'SQL调用其他表演示');