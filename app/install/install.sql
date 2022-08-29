/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : sademo

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 19/08/2022 11:57:39
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for __PREFIX__admin
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin`;
CREATE TABLE `__PREFIX__admin`  (
  `id` mediumint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分组id',
  `department_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门id',
  `jobs_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '岗位id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '帐号',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户昵称',
  `pwd` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `sex` tinyint(1) NOT NULL DEFAULT 1 COMMENT '性别',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户标签',
  `face` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '/static/images/user_default.jpg' COMMENT '头像',
  `mood` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '每日心情',
  `email` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱',
  `area` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '区号',
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '手机',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '简介',
  `count` smallint(6) NULL DEFAULT NULL COMMENT '登录次数',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户地址',
  `login_ip` bigint(12) NULL DEFAULT NULL COMMENT '登录IP',
  `login_time` int(11) NULL DEFAULT NULL COMMENT '最后登录时间',
  `create_ip` bigint(12) NULL DEFAULT NULL COMMENT '注册IP',
  `status` int(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '用户状态',
  `banned` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '封号原因',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '注册时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `pwd`(`pwd`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '后台管理员表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin
-- ----------------------------
INSERT INTO `__PREFIX__admin` VALUES (1, '1', '2', '3', 'admin', 'meystack', '13682bec405cf4b9002e6e8306312ce6', 1, 'a:3:{i:0;s:12:\"测试效果\";i:1;s:15:\"隔壁帅小伙\";i:2;s:9:\"技术宅\";}', '/upload/avatars/f8e34ec67a2a0233_100x100.jpg', '海阔天空，有容乃大', 'admin@swiftadmin.net', '0310', '15188888888', '高级管理人员', 254, '河北省邯郸市', 2130706433, 1660635302, 3232254977, 1, NULL, 1596682835, 1660880928, NULL);
INSERT INTO `__PREFIX__admin` VALUES (2, '2', '4', '5,6', 'ceshi', '测试用户', '13682bec405cf4b9002e6e8306312ce6', 1, 'a:3:{i:0;s:6:\"呵呵\";i:1;s:5:\"Think\";i:2;s:12:\"铁血柔肠\";}', '/upload/avatars/a0b923820dcc509a_100x100.png', 'PHP是全世界最好的语言', 'baimei@your.com', '0310', '15188888888', '我原本以为吕布已经天下无敌了，没想到还有比吕布勇猛的，这谁的部将？', 50, '河北省邯郸市廉颇大道110号指挥中心', 2130706433, 1660637434, 3232254977, 1, '违规', 1609836672, 1660637434, NULL);

-- ----------------------------
-- Table structure for __PREFIX__admin_access
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_access`;
CREATE TABLE `__PREFIX__admin_access`  (
  `admin_id` mediumint(8) UNSIGNED NOT NULL COMMENT '用户ID',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '管理员分组',
  `rules` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '自定义权限',
  `cates` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '栏目权限',
  PRIMARY KEY (`admin_id`) USING BTREE,
  INDEX `uid`(`admin_id`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '组规则表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_access
-- ----------------------------
INSERT INTO `__PREFIX__admin_access` VALUES (1, '1', NULL, NULL);
INSERT INTO `__PREFIX__admin_access` VALUES (2, '2', '5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54', '');

-- ----------------------------
-- Table structure for __PREFIX__admin_group
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_group`;
CREATE TABLE `__PREFIX__admin_group`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NOT NULL COMMENT '父组id',
  `jobid` int(11) NULL DEFAULT NULL COMMENT '体系id',
  `title` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分组名称',
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '标识',
  `type` int(11) NULL DEFAULT NULL COMMENT '分组类型',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `rules` varchar(2048) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '规则字符串',
  `cates` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '栏目权限',
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '颜色',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户组表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_group
-- ----------------------------
INSERT INTO `__PREFIX__admin_group` VALUES (1, 0, 1, '超级管理员', 'admin', 1, 1, '网站超级管理员组的', NULL, NULL, 'layui-bg-blue', 1607832158, NULL);
INSERT INTO `__PREFIX__admin_group` VALUES (2, 1, 2, '网站编辑', 'editor', 1, 1, '负责公司软文的编写', NULL, NULL, 'layui-bg-cyan', 1607832158, NULL);

-- ----------------------------
-- Table structure for __PREFIX__admin_rules
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_rules`;
CREATE TABLE `__PREFIX__admin_rules`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '父栏目id',
  `title` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '菜单标题',
  `router` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '路由地址',
  `alias` varchar(110) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限标识',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '菜单，按钮，接口，系统',
  `note` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注信息',
  `condition` char(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '正则表达式',
  `sort` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '排序',
  `icon` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
  `auth` tinyint(4) NULL DEFAULT 1 COMMENT '状态',
  `status` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '状态码',
  `isSystem` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '系统级,只可手动操作',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '添加时间',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 113 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '菜单权限表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_rules
-- ----------------------------
INSERT INTO `__PREFIX__admin_rules` VALUES (1, 0, 'Dashboard', 'Dashboard', 'dashboard', 0, '', '', 1, 'layui-icon-home', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (2, 1, '控制台', '/index/console', 'index:console', 0, '', '', 2, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (3, 1, '分析页', '/index/analysis', 'index:analysis', 0, '', '', 3, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (4, 1, '监控页', '/index/monitor', 'index:monitor', 0, '', '', 4, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (5, 0, '系统管理', 'System', 'system', 0, '', '', 5, 'layui-icon-set-fill', 1, 1, 0, 1657367099, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (6, 5, '基本设置', '/index/basecfg', 'index:basecfg', 0, '', '', 6, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (7, 6, '修改配置', '/index/baseset', 'index:baseset', 2, '', '', 7, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (8, 6, 'FTP接口', '/index/testftp', 'index:testftp', 2, '', '', 8, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (9, 6, '邮件接口', '/index/testemail', 'index:testemail', 2, '', '', 9, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (10, 6, '缓存接口', '/index/testcache', 'index:testcache', 2, '', '', 10, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (11, 5, '用户管理', '/system/Admin/index', 'system:Admin:index', 0, '', '', 11, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (12, 11, '查看', '/system/Admin/index', 'system:Admin:index', 1, '', '', 12, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (13, 11, '添加', '/system/Admin/add', 'system:Admin:add', 1, '', '', 13, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (14, 11, '编辑', '/system/Admin/edit', 'system:Admin:edit', 1, '', '', 14, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (15, 11, '删除', '/system/Admin/del', 'system:Admin:del', 1, '', '', 15, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (16, 11, '状态', '/system/Admin/status', 'system:Admin:status', 2, '', '', 16, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (17, 11, '编辑权限', '/system/Admin/editRules', 'system:Admin:editRules', 2, '', '', 17, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (18, 11, '编辑栏目', '/system/Admin/editCates', 'system:Admin:editCates', 2, '', '', 18, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (19, 11, '系统模板', '/system/Admin/theme', 'system:Admin:theme', 2, '', '', 19, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (20, 11, '短消息', '/system/Admin/message', 'system:Admin:message', 2, '', '', 20, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (21, 11, '个人中心', '/system/Admin/center', 'system:Admin:center', 2, '', '', 21, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (22, 11, '修改资料', '/system/Admin/modify', 'system:Admin:modify', 2, '', '', 22, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (23, 11, '修改密码', '/system/Admin/pwd', 'system:Admin:pwd', 2, '', '', 23, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (24, 11, '系统语言', '/system/Admin/language', 'system:Admin:language', 2, '', '', 24, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (25, 11, '清理缓存', '/system/Admin/clear', 'system:Admin:clear', 2, '', '', 25, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (26, 11, '数据接口', '/system/Admin/getPermissions', 'system:Admin:getPermissions', 3, '', '', 26, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (27, 5, '用户中心', '/system/Admin/center', 'system:Admin:center', 0, '', '', 27, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (28, 27, '系统模板', '/system/Admin/theme', 'system:Admin:theme', 2, '', '', 28, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (29, 27, '短消息', '/system/Admin/message', 'system:Admin:message', 2, '', '', 29, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (30, 27, '修改资料', '/system/Admin/modify', 'system:Admin:modify', 2, '', '', 30, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (31, 27, '修改密码', '/system/Admin/pwd', 'system:Admin:pwd', 2, '', '', 31, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (32, 27, '系统语言', '/system/Admin/language', 'system:Admin:language', 2, '', '', 32, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (33, 27, '清理缓存', '/system/Admin/clear', 'system:Admin:clear', 2, '', '', 33, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (34, 5, '角色管理', '/system/AdminGroup/index', 'system:AdminGroup:index', 0, '', '', 34, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (35, 34, '查看', '/system/AdminGroup/index', 'system:AdminGroup:index', 1, '', '', 35, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (36, 34, '添加', '/system/AdminGroup/add', 'system:AdminGroup:add', 1, '', '', 36, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (37, 34, '编辑', '/system/AdminGroup/edit', 'system:AdminGroup:edit', 1, '', '', 37, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (38, 34, '删除', '/system/AdminGroup/del', 'system:AdminGroup:del', 1, '', '', 38, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (39, 34, '状态', '/system/AdminGroup/status', 'system:AdminGroup:status', 2, '', '', 39, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (40, 34, '编辑权限', '/system/AdminGroup/editRules', 'system:AdminGroup:editRules', 2, '', '', 40, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (41, 34, '编辑栏目', '/system/AdminGroup/editCates', 'system:AdminGroup:editCates', 2, '', '', 41, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (42, 5, '菜单管理', '/system/AdminRules/index', 'system:AdminRules:index', 0, '', '', 42, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (43, 42, '查询', '/system/AdminRules/index', 'system:AdminRules:index', 1, '', '', 43, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (44, 42, '添加', '/system/AdminRules/add', 'system:AdminRules:add', 1, '', '', 44, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (45, 42, '编辑', '/system/AdminRules/edit', 'system:AdminRules:edit', 1, '', '', 45, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (46, 42, '删除', '/system/AdminRules/del', 'system:AdminRules:del', 1, '', '', 46, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (47, 42, '状态', '/system/AdminRules/status', 'system:AdminRules:status', 2, '', '', 47, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (48, 5, '操作日志', '/system/SystemLog/index', 'system:SystemLog:index', 0, '', '', 48, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (49, 48, '查询', '/system/SystemLog/index', 'system:SystemLog:index', 1, '', '', 49, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (50, 5, '登录日志', '/system/LoginLog/index', 'system:LoginLog:index', 0, '', '', 50, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (51, 50, '添加', '/system/LoginLog/add', 'system:LoginLog:add', 1, '', '', 51, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (52, 50, '编辑', '/system/LoginLog/edit', 'system:LoginLog:edit', 1, '', '', 52, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (53, 50, '删除', '/system/LoginLog/del', 'system:LoginLog:del', 1, '', '', 53, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (54, 50, '状态', '/system/LoginLog/status', 'system:LoginLog:status', 1, '', '', 54, NULL, 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (55, 0, '高级管理', 'Management', 'management', 0, '', '', 55, 'layui-icon-engine', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (56, 55, '公司管理', '/system/Company/index', 'system:Company:index', 0, '', '', 56, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (57, 56, '查看', '/system/Company/index', 'system:Company:index', 1, '', '', 57, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (58, 56, '添加', '/system/Company/add', 'system:Company:add', 1, '', '', 58, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (59, 56, '编辑', '/system/Company/edit', 'system:Company:edit', 1, '', '', 59, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (60, 56, '删除', '/system/Company/del', 'system:Company:del', 1, '', '', 60, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (61, 56, '状态', '/system/Company/status', 'system:Company:status', 2, '', '', 61, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (62, 55, '部门管理', '/system/Department/index', 'system:Department:index', 0, '', '', 62, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (63, 62, '查看', '/system/Department/index', 'system:Department:index', 1, '', '', 63, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (64, 62, '添加', '/system/Department/add', 'system:Department:add', 1, '', '', 64, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (65, 62, '编辑', '/system/Department/edit', 'system:Department:edit', 1, '', '', 65, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (66, 62, '删除', '/system/Department/del', 'system:Department:del', 1, '', '', 66, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (67, 62, '状态', '/system/Department/status', 'system:Department:status', 2, '', '', 67, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (68, 55, '岗位管理', '/system/Jobs/index', 'system:Jobs:index', 0, '', '', 68, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (69, 68, '查看', '/system/Jobs/index', 'system:Jobs:index', 1, '', '', 69, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (70, 68, '添加', '/system/Jobs/add', 'system:Jobs:add', 1, '', '', 70, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (71, 68, '编辑', '/system/Jobs/edit', 'system:Jobs:edit', 1, '', '', 71, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (72, 68, '删除', '/system/Jobs/del', 'system:Jobs:del', 1, '', '', 72, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (73, 68, '状态', '/system/Jobs/status', 'system:Jobs:status', 2, '', '', 73, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (74, 55, '字典设置', '/system/Dictionary/index', 'system:Dictionary:index', 0, '', '', 74, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (75, 74, '查看', '/system/Dictionary/index', 'system:Dictionary:index', 1, '', '', 75, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (76, 74, '添加', '/system/Dictionary/add', 'system:Dictionary:add', 1, '', '', 76, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (77, 74, '编辑', '/system/Dictionary/edit', 'system:Dictionary:edit', 1, '', '', 77, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (78, 74, '删除', '/system/Dictionary/del', 'system:Dictionary:del', 1, '', '', 78, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (79, 74, '状态', '/system/Dictionary/status', 'system:Dictionary:status', 2, '', '', 79, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (80, 55, '附件管理', '/system/Attachment/index', 'system:Attachment:index', 0, '', '', 80, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (81, 80, '查看', '/system/Attachment/index', 'system:Attachment:index', 1, '', '', 81, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (82, 80, '编辑', '/system/Attachment/edit', 'system:Attachment:edit', 1, '', '', 82, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (83, 80, '删除', '/system/Attachment/del', 'system:Attachment:del', 1, '', '', 83, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (84, 80, '附件上传', '/Ajax/upload', 'Ajax:upload', 2, '', '', 84, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (85, 0, '插件应用', 'Plugin', 'Plugin', 0, '', '', 85, 'layui-icon-component', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (86, 85, '插件管理', '/system/Plugin/index', 'system:Plugin:index', 0, '', '', 86, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (87, 86, '查看', '/system/Plugin/index', 'system:Plugin:index', 1, '', '', 87, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (88, 86, '安装', '/system/Plugin/install', 'system:Plugin:install', 1, '', '', 88, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (89, 86, '卸载', '/system/Plugin/uninstall', 'system:Plugin:uninstall', 1, '', '', 89, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (90, 86, '配置', '/system/Plugin/config', 'system:Plugin:config', 1, '', '', 90, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (91, 86, '状态', '/system/Plugin/status', 'system:Plugin:status', 2, '', '', 91, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (92, 86, '升级', '/system/Plugin/upgrade', 'system:Plugin:upgrade', 2, '', '', 92, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (93, 86, '数据表', '/system/Plugin/tables', 'system:Plugin:tables', 2, '', '', 93, '', 0, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (94, 85, '占位菜单', '#', '', 0, '', '', 94, '', 1, 0, 1, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (95, 94, '查看', '#', '', 1, '', '', 95, '', 1, 0, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (96, 94, '安装', '#', '', 1, '', '', 96, '', 1, 0, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (97, 94, '卸载', '#', '', 1, '', '', 97, '', 1, 0, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (98, 94, '预留1', '#', '', 1, '', '', 98, '', 1, 0, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (99, 94, '预留2', '#', '', 2, '', '', 99, '', 1, 0, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (100, 0, '会员管理', 'User', 'User', 0, '', '', 100, 'layui-icon-user', 1, 1, 0, 1659447410, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (101, 100, '会员管理', '/system/User/index', 'system:User:index', 0, '', '', 101, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (102, 101, '查看', '/system/User/index', 'system:User:index', 1, '', '', 102, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (103, 101, '添加', '/system/User/add', 'system:User:add', 1, '', '', 103, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (104, 101, '编辑', '/system/User/edit', 'system:User:edit', 1, '', '', 104, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (105, 101, '删除', '/system/User/del', 'system:User:del', 1, '', '', 105, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (106, 101, '状态', '/system/User/status', 'system:User:status', 2, '', '', 106, '', 1, 1, 0, 1657002180, 1657002180, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (107, 100, '会员组管理', '/system/UserGroup/index', 'system:UserGroup:index', 0, '', '', 119, '', 1, 1, 0, 1657002181, 1657002181, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (108, 107, '查看', '/system/UserGroup/index', 'system:UserGroup:index', 1, '', '', 120, '', 1, 1, 0, 1657002181, 1657002181, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (109, 107, '添加', '/system/UserGroup/add', 'system:UserGroup:add', 1, '', '', 121, '', 1, 1, 0, 1657002181, 1657002181, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (110, 107, '编辑', '/system/UserGroup/edit', 'system:UserGroup:edit', 1, '', '', 122, '', 1, 1, 0, 1657002181, 1657002181, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (111, 107, '删除', '/system/UserGroup/del', 'system:UserGroup:del', 1, '', '', 123, '', 1, 1, 0, 1657002181, 1657002181, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (112, 107, '状态', '/system/UserGroup/status', 'system:UserGroup:status', 2, '', '', 124, '', 1, 1, 0, 1657002181, 1657002181, NULL);

-- ----------------------------
-- Table structure for __PREFIX__attachment
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__attachment`;
CREATE TABLE `__PREFIX__attachment`  (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '类别',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '物理路径',
  `extension` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件后缀',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件名称',
  `filesize` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
  `mimetype` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'mime类型',
  `sha1` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件 sha1编码',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建日期',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `extension`(`extension`) USING BTREE,
  INDEX `filename`(`filename`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '附件表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__attachment
-- ----------------------------

-- ----------------------------
-- Table structure for __PREFIX__company
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__company`;
CREATE TABLE `__PREFIX__company`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公司名称',
  `alias` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公司标识',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公司地址',
  `postcode` int(11) NULL DEFAULT NULL COMMENT '邮编',
  `contact` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系人',
  `mobile` bigint(20) NULL DEFAULT NULL COMMENT '手机号',
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `email` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `blicense` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '营业执照代码',
  `longitude` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '地图经度',
  `latitude` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '地图纬度',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '公司信息表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__company
-- ----------------------------
INSERT INTO `__PREFIX__company` VALUES (1, '北京总部技术公司', 'bj', '北京市东城区长安街880号', 10000, '权栈', 15100000001, '010-10000', 'coolsec@foxmail.com', '91130403XXA0AJ7XXM', '01', '02', 1613711884,NULL);
INSERT INTO `__PREFIX__company` VALUES (2, '河北分公司', 'hb', '河北省邯郸市丛台区公园路880号', 56000, '权栈', 12345678901, '0310-12345678', 'coolsec@foxmail.com', 'code', NULL, NULL, 1613787702,NULL);

-- ----------------------------
-- Table structure for __PREFIX__config
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__config`;
CREATE TABLE `__PREFIX__config`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '字段',
  `system` int(1) UNSIGNED NULL DEFAULT 0 COMMENT '系统',
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '配置组',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字段类型',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字段值',
  `tips` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '提示信息',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 91 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统配置表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__config
-- ----------------------------
INSERT INTO `__PREFIX__config` VALUES (1, 'site_name', 1, 'site', 'string', '基于PHP MYSQL的极速后台开发框架', '网站名称');
INSERT INTO `__PREFIX__config` VALUES (2, 'site_url', 1, 'site', 'string', 'www.swiftadmin.net', '网站URL');
INSERT INTO `__PREFIX__config` VALUES (3, 'site_logo', 1, 'site', 'string', '/static/images/logo.png', '网站logo');
INSERT INTO `__PREFIX__config` VALUES (4, 'site_http', 1, 'site', 'string', 'http://www.swiftadmin.net', 'HTTP地址');
INSERT INTO `__PREFIX__config` VALUES (5, 'site_state', 1, 'site', 'string', '0', '是否开启手机版');
INSERT INTO `__PREFIX__config` VALUES (6, 'site_type', 1, 'site', 'string', '0', '手机版类型');
INSERT INTO `__PREFIX__config` VALUES (7, 'site_mobile', 1, 'site', 'string', 'https://m.swiftadmin.net', '手机版地址');
INSERT INTO `__PREFIX__config` VALUES (8, 'site_icp', 1, 'site', 'string', '京ICP备13000001号', '备案号');
INSERT INTO `__PREFIX__config` VALUES (9, 'site_email', 1, 'site', 'string', 'admin@swiftadmin.net', '站长邮箱');
INSERT INTO `__PREFIX__config` VALUES (10, 'site_keyword', 1, 'site', 'string', '网站关键字', '网站关键字');
INSERT INTO `__PREFIX__config` VALUES (11, 'site_description', 1, 'site', 'string', '网站描述', '网站描述');
INSERT INTO `__PREFIX__config` VALUES (12, 'site_total', 1, 'site', 'string', '统计代码：', '统计代码');
INSERT INTO `__PREFIX__config` VALUES (13, 'site_copyright', 1, 'site', 'string', '版权信息：', '版权信息');
INSERT INTO `__PREFIX__config` VALUES (14, 'site_clearLink', 1, 'site', 'string', '1', '清理非本站链接');
INSERT INTO `__PREFIX__config` VALUES (15, 'site_status', 1, 'site', 'string', '0', '运营状态');
INSERT INTO `__PREFIX__config` VALUES (16, 'site_notice', 1, 'site', 'string', '<p>您要访问的网站出现了问题！</p>', '关闭通知');
INSERT INTO `__PREFIX__config` VALUES (17, 'auth_key', 0, NULL, 'string', '38nfCIlkqNMI2', '授权码');
INSERT INTO `__PREFIX__config` VALUES (18, 'auth_code', 0, NULL, 'string', 'wMRkfKO4Lr37HTJQ', '加密KEY');
INSERT INTO `__PREFIX__config` VALUES (19, 'system_logs', 0, NULL, 'string', '0', '后台日志');
INSERT INTO `__PREFIX__config` VALUES (20, 'system_exception', 0, NULL, 'string', '1', '异常日志');
INSERT INTO `__PREFIX__config` VALUES (21, 'cache_status', 0, 'cache', 'string', '1', '缓存状态');
INSERT INTO `__PREFIX__config` VALUES (22, 'cache_type', 0, 'cache', 'string', 'redis', '缓存类型');
INSERT INTO `__PREFIX__config` VALUES (23, 'cache_time', 0, 'cache', 'string', '6000', '缓存时间');
INSERT INTO `__PREFIX__config` VALUES (24, 'cache_host', 0, 'cache', 'string', '127.0.0.1', '服务器IP');
INSERT INTO `__PREFIX__config` VALUES (25, 'cache_port', 0, 'cache', 'string', '6379', '端口');
INSERT INTO `__PREFIX__config` VALUES (26, 'cache_select', 0, 'cache', 'string', '1', '缓存数据库');
INSERT INTO `__PREFIX__config` VALUES (27, 'cache_user', 0, 'cache', 'string', '', '用户名');
INSERT INTO `__PREFIX__config` VALUES (28, 'cache_pass', 0, 'cache', 'string', '', '密码');
INSERT INTO `__PREFIX__config` VALUES (29, 'upload_path', 0, 'upload', 'string', 'upload', '上传路径');
INSERT INTO `__PREFIX__config` VALUES (30, 'upload_style', 0, 'upload', 'string', 'Y-m-d', '文件夹格式');
INSERT INTO `__PREFIX__config` VALUES (31, 'upload_class', 0, 'upload', 'array', '{\"images\":\".bmp.jpg.jpeg.png.gif.svg\",\"video\":\".flv.swf.mkv.avi.rm.rmvb.mpeg.mpg.ogg.ogv.mov.wmv.mp4.webm.mp3.wav.mid\",\"document\":\".txt.doc.xls.ppt.docx.xlsx.pptx\",\"files\":\".exe.dll.sys.so.dmg.iso.zip.rar.7z.sql.pem.pdf.psd\"}', '文件分类');
INSERT INTO `__PREFIX__config` VALUES (32, 'upload_ftp', 0, 'upload', 'string', '0', 'FTP上传');
INSERT INTO `__PREFIX__config` VALUES (33, 'upload_del', 0, 'upload', 'string', '0', '上传后删除');
INSERT INTO `__PREFIX__config` VALUES (34, 'upload_ftp_host', 0, 'upload', 'string', '127.0.0.1', 'FTP服务器');
INSERT INTO `__PREFIX__config` VALUES (35, 'upload_ftp_port', 0, 'upload', 'string', '26655', 'FTP端口');
INSERT INTO `__PREFIX__config` VALUES (36, 'upload_ftp_user', 0, 'upload', 'string', '123123', 'FTP用户名');
INSERT INTO `__PREFIX__config` VALUES (37, 'upload_ftp_pass', 0, 'upload', 'string', '5BGMMATwC7mtGp4m', 'FTP密码');
INSERT INTO `__PREFIX__config` VALUES (38, 'upload_http_prefix', 0, 'upload', 'string', '', '图片CDN地址');
INSERT INTO `__PREFIX__config` VALUES (39, 'upload_chunk_size', 0, 'upload', 'string', '2097152', '文件分片大小 字节');
INSERT INTO `__PREFIX__config` VALUES (40, 'upload_thumb', 0, 'upload', 'string', '0', '是否开启缩略图');
INSERT INTO `__PREFIX__config` VALUES (41, 'upload_thumb_w', 0, 'upload', 'string', '120', '宽度');
INSERT INTO `__PREFIX__config` VALUES (42, 'upload_thumb_h', 0, 'upload', 'string', '140', '高度');
INSERT INTO `__PREFIX__config` VALUES (43, 'upload_water', 0, 'upload', 'string', '0', '是否水印');
INSERT INTO `__PREFIX__config` VALUES (44, 'upload_water_type', 0, 'upload', 'string', '1', '水印类型');
INSERT INTO `__PREFIX__config` VALUES (45, 'upload_water_font', 0, 'upload', 'string', 'www.swiftadmin.net', '水印文字');
INSERT INTO `__PREFIX__config` VALUES (46, 'upload_water_size', 0, 'upload', 'string', '20', '字体大小');
INSERT INTO `__PREFIX__config` VALUES (47, 'upload_water_color', 0, 'upload', 'string', '#0fbeea', '字体颜色');
INSERT INTO `__PREFIX__config` VALUES (48, 'upload_water_pct', 0, 'upload', 'string', '47', '透明度');
INSERT INTO `__PREFIX__config` VALUES (49, 'upload_water_img', 0, 'upload', 'string', '/', '图片水印地址');
INSERT INTO `__PREFIX__config` VALUES (50, 'upload_water_pos', 0, 'upload', 'string', '9', '水印位置');
INSERT INTO `__PREFIX__config` VALUES (51, 'play', 0, NULL, 'array', '{\"play_width\":\"960\",\"play_height\":\"450\",\"play_show\":\"0\",\"play_second\":\"10\",\"play_area\":\"大陆,香港,中国台湾,美国,韩国,日本,泰国,印度,英国,法国,俄罗斯,新加坡,其它\",\"play_year\":\"2022,2021,2020,2019,2018,2017,2016,2015,2014,2013,2012,2011,2010,2009,2008,2007,2006,2005,2004,2003,2002,2001,2000,1999\",\"play_version\":\"高清版,剧场版,抢先版,OVA,TV,影院版\",\"play_language\":\"国语,英语,粤语,韩语,日语,法语,德语,泰语,俄语,其它\",\"play_week\":\"周一,周二,周三,周四,周五,周六,周日\",\"play_playad\":\"http:\\/\\/www.swiftadmin.net\\/api\\/show.html\",\"play_down\":\"http:\\/\\/www.swiftadmin.net\\/api\\/show.html\",\"play_downgorup\":\"http:\\/\\/down.swiftadmin.net\\/\"}', '播放器数据');
INSERT INTO `__PREFIX__config` VALUES (52, 'cloud_status', 0, NULL, 'string', '1', '是否开启OSS上传');
INSERT INTO `__PREFIX__config` VALUES (53, 'cloud_type', 0, NULL, 'string', 'aliyun_oss', 'OSS上传类型');
INSERT INTO `__PREFIX__config` VALUES (54, 'aliyun_oss', 0, NULL, 'array', '{\"accessId\":\"LTAI5tRl3a8LJu61vC\",\"accessSecret\":\"knwIiD8rINVl3a8LJu61l3a8LJu6\",\"bucket\":\"bucket\",\"endpoint\":\"oss-cn-beijing.aliyuncs.com\",\"url\":\"http:\\/\\/oss-cn-beijing.aliyuncs.com\"}', '阿里云OSS');
INSERT INTO `__PREFIX__config` VALUES (55, 'qcloud_oss', 0, NULL, 'array', '{\"app_id\":\"1252296528\",\"secret_id\":\"LTAI5333kuER9w3xNnVMe1vC\",\"secret_key\":\"kFStrmkXjHjw9sankaJdocxsSScjRt9A\",\"bucket\":\"testpack\",\"region\":\"ap-beijing\",\"url\":\"\"}', '腾讯云OSS');
INSERT INTO `__PREFIX__config` VALUES (56, 'email', 0, NULL, 'array', '{\"smtp_debug\":\"0\",\"smtp_host\":\"smtp.163.com\",\"smtp_port\":\"587\",\"smtp_name\":\"管理员\",\"smtp_user\":\"domain@163.com\",\"smtp_pass\":\"KNWSGPUYBMFATCIZ\",\"smtp_test\":\"yourname@foxmail.com\"}', '邮箱配置');
INSERT INTO `__PREFIX__config` VALUES (57, 'qq', 0, NULL, 'array', '{\"app_id\":\"\",\"app_key\":\"\",\"callback\":\"\"}', 'QQ登录');
INSERT INTO `__PREFIX__config` VALUES (58, 'weixin', 0, NULL, 'array', '{\"app_id\":\"\",\"app_key\":\"\",\"callback\":\"\"}', '微信登录');
INSERT INTO `__PREFIX__config` VALUES (59, 'gitee', 0, NULL, 'array', '{\"app_id\":\"\",\"app_key\":\"\",\"callback\":\"\"}', '码云登录');
INSERT INTO `__PREFIX__config` VALUES (60, 'weibo', 0, NULL, 'array', '{\"app_id\":\"\",\"app_key\":\"\",\"callback\":\"\"}', '微博登录');
INSERT INTO `__PREFIX__config` VALUES (61, 'alipay', 0, NULL, 'array', '{\"mode\":\"0\",\"app_id\":\"202100213462****\",\"app_public_cert_path\":\"appCertPublicKey_20210021346*****.crt\",\"app_secret_cert\":\"7eUBvZLxn8XwZPuCA==\",\"return_url\":\"https:\\/\\/www.swiftadmin.net\\/\",\"notify_url\":\"https:\\/\\/www.swiftadmin.net\\/\",\"alipay_public_cert_path\":\"alipayCertPublicKey_RSA2.crt\",\"alipay_root_cert_path\":\"alipayRootCert.crt\"}', '支付宝');
INSERT INTO `__PREFIX__config` VALUES (62, 'wechat', 0, NULL, 'array', '{\"mode\":\"0\",\"mch_id\":\"16138*****\",\"mch_secret_key\":\"GgnohjtLdR******rprA6duxQ8k0AuVA\",\"mp_app_id\":\"wxd2bf0834be*****\",\"mini_app_id\":\"\",\"notify_url\":\"https:\\/\\/www.swiftadmin.net\\/\",\"mch_secret_cert\":\"apiclient_key.pem\",\"mch_public_cert_path\":\"apiclient_cert.pem\"}', '微信支付');
INSERT INTO `__PREFIX__config` VALUES (63, 'smstype', 0, NULL, 'string', 'tensms', '短信类型');
INSERT INTO `__PREFIX__config` VALUES (64, 'alisms', 0, NULL, 'array', '{\"app_id\":\"cn-hangzhou\",\"app_sign\":\"河北邯郸市有限公司\",\"access_id\":\"kFStrmkXjHjw9sankaJdoIXXSScjRt9A\",\"access_secret\":\"kFStrmkXjHjw9sankaJdoIXXSScjRt9A\"}', '阿里云短信');
INSERT INTO `__PREFIX__config` VALUES (65, 'tensms', 0, NULL, 'array', '{\"app_id\":\"1400660771\",\"app_sign\":\"河北邯郸市有限公司\",\"secret_id\":\"AKIDsa322o8C0basdTAajbDXaMr63j\",\"secret_key\":\"QaT5QUHn1zg6F6qxq7RUGlyuZx3tS66W\"}', '腾讯云短信');
INSERT INTO `__PREFIX__config` VALUES (66, 'mpwechat', 0, NULL, 'array', '{\"app_id\":\"wx11\",\"secret\":\"3d969476ca2\",\"token\":\"M1qheYRCvSRutsreGp6PS\",\"aes_key\":\"wxd2bf0834\"}', '微信公众号');
INSERT INTO `__PREFIX__config` VALUES (67, 'user_status', 0, 'user', 'string', '1', '注册状态');
INSERT INTO `__PREFIX__config` VALUES (68, 'user_register', 0, 'user', 'string', 'mobile', '注册方式');
INSERT INTO `__PREFIX__config` VALUES (69, 'user_document', 0, 'user', 'string', '1', '用户投稿');
INSERT INTO `__PREFIX__config` VALUES (70, 'user_sensitive', 0, 'user', 'string', '1', '开启违禁词检测');
INSERT INTO `__PREFIX__config` VALUES (71, 'user_document_integra', 0, 'user', 'string', '1', '投稿获得积分');
INSERT INTO `__PREFIX__config` VALUES (72, 'user_valitime', 0, 'user', 'string', '10', '激活码有效期');
INSERT INTO `__PREFIX__config` VALUES (73, 'user_register_second', 0, 'user', 'string', '10', '每日注册');
INSERT INTO `__PREFIX__config` VALUES (74, 'user_login_integra', 0, 'user', 'string', '1', '登录获得积分');
INSERT INTO `__PREFIX__config` VALUES (75, 'user_spread_integra', 0, 'user', 'string', '1', '推广获得积分');
INSERT INTO `__PREFIX__config` VALUES (76, 'user_search_interval', 0, 'user', 'string', '1', '用户搜索间隔');
INSERT INTO `__PREFIX__config` VALUES (77, 'user_reg_notallow', 0, 'user', 'string', 'www,bbs,ftp,mail,user,users,admin,administrator', '禁止注册');
INSERT INTO `__PREFIX__config` VALUES (78, 'user_form_status', 0, 'user', 'string', '1', '评论开关');
INSERT INTO `__PREFIX__config` VALUES (79, 'user_form_check', 0, 'user', 'string', '0', '评论审核');
INSERT INTO `__PREFIX__config` VALUES (80, 'user_isLogin', 0, 'user', 'string', '1', '游客评论');
INSERT INTO `__PREFIX__config` VALUES (81, 'user_anonymous', 0, 'user', 'string', '0', '匿名评论');
INSERT INTO `__PREFIX__config` VALUES (82, 'user_form_second', 0, 'user', 'string', '10', '最大注册');
INSERT INTO `__PREFIX__config` VALUES (83, 'user_replace', 0, 'user', 'string', '她妈|它妈|他妈|你妈|去死|贱人', '过滤字符');
INSERT INTO `__PREFIX__config` VALUES (84, 'sitemap', 0, NULL, 'array', '', '地图配置');
INSERT INTO `__PREFIX__config` VALUES (85, 'rewrite', 0, NULL, 'string', '', 'URL配置');
INSERT INTO `__PREFIX__config` VALUES (86, 'database', 0, NULL, 'string', '', '数据库维护');
INSERT INTO `__PREFIX__config` VALUES (87, 'variable', 0, NULL, 'array', '{\"test\":\"我是值2\",\"ceshi\":\"我是测试变量的值\"}', '自定义变量');
INSERT INTO `__PREFIX__config` VALUES (88, 'param', 0, NULL, 'string', '', '测试代码');
INSERT INTO `__PREFIX__config` VALUES (89, 'full_status', 0, NULL, 'string', '0', '全文检索');
INSERT INTO `__PREFIX__config` VALUES (90, 'editor', 0, NULL, 'string', 'lay-editor', '编辑器选项');

-- ----------------------------
-- Table structure for __PREFIX__department
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__department`;
CREATE TABLE `__PREFIX__department`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NULL DEFAULT 0 COMMENT '上级ID',
  `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '部门名称',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门区域',
  `head` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '负责人',
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '手机号',
  `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门简介',
  `sort` tinyint(4) NULL DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '部门管理表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__department
-- ----------------------------
INSERT INTO `__PREFIX__department` VALUES (1, 0, '北京总部', '北京市昌平区体育馆南300米', '秦老板', '1510000001', 'coolsec@foxmail.com', '总部，主要负责广告的营销，策划！', 1, 1, 1611213045, NULL);
INSERT INTO `__PREFIX__department` VALUES (2, 1, '河北分公司', '河北省邯郸市丛台区政府路', '刘备', '15100020003', 'liubei@qq.com', '', 2, 1, 1611227478, NULL);
INSERT INTO `__PREFIX__department` VALUES (3, 2, '市场部', '一楼', '大乔', '15100010003', 'xiaoqiao@foxmail.com', '', 3, 1, 1611228586, NULL);
INSERT INTO `__PREFIX__department` VALUES (4, 2, '开发部', '二楼2', '赵云', '15100010003', 'zhaoyun@shijiazhuang.com', '', 4, 1, 1611228626, NULL);
INSERT INTO `__PREFIX__department` VALUES (5, 2, '营销部', '二楼', '许攸', '15100010003', 'xuyou@henan.com', '', 5, 1, 1611228674, NULL);

-- ----------------------------
-- Table structure for __PREFIX__dictionary
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__dictionary`;
CREATE TABLE `__PREFIX__dictionary`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '字典分类id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字典名称',
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字典值',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序号',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注信息',
  `isSystem` tinyint(3) UNSIGNED NULL DEFAULT NULL COMMENT '系统级,只可手动操作',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `create_time` int(11) NULL DEFAULT 0 COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `value`(`value`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '字典数据表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__dictionary
-- ----------------------------
INSERT INTO `__PREFIX__dictionary` VALUES (1, 0, '内容属性', 'content', 1, '', 1, 1659839499, 1637738903, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (2, 1, '头条', '1', 2, '', 1, 1638093403, 1638093403, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (3, 1, '推荐', '2', 3, '', 1, 1657367329, 1638093425, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (4, 1, '幻灯', '3', 4, '', 1, 1657438818, 1638093430, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (5, 1, '滚动', '4', 5, '', 1, 1638093435, 1638093435, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (6, 1, '图文', '5', 6, '', 1, 1638093456, 1638093456, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (7, 1, '跳转', '6', 7, '', 1, 1638093435, 1638093435, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (8, 0, '友链类型', 'friendlink', 8, '', 1, 1638093456, 1638093456, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (9, 8, '资源', '1', 9, '', 1, 1638093430, 1638093430, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (10, 8, '社区', '2', 10, '', 1, 1638093435, 1638093435, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (11, 8, '合作伙伴', '3', 11, '', 1, 1659450310, 1638093456, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (12, 8, '关于我们', '4', 12, '', 1, 1638093461, 1638093461, NULL);

-- ----------------------------
-- Table structure for __PREFIX__jobs
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__jobs`;
CREATE TABLE `__PREFIX__jobs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位名称',
  `alias` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位标识',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '岗位描述',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT NULL COMMENT '岗位状态',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '岗位管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__jobs
-- ----------------------------
INSERT INTO `__PREFIX__jobs` VALUES (1, '董事长', 'ceo', '日常划水~', 1, 1, 1611234206, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (2, '人力资源', 'hr', '招聘人员，员工考核，绩效奖励！', 2, 1, 1611234288, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (3, '首席技术岗', 'cto', '主要职责是设计公司的未来，其更多的工作应该是前瞻性的，也就是制定下一代产品的策略和进行研究工作，属于技术战略的重要执行者。CTO还是高级市场人员，他可以从技术角度非常有效地帮助公司推广理念，其中包括公司对技术趋势所持的看法。因此，在大型用户会议上CTO会阐述产品下一代的走向和功能，这也是重要的市场策略。', 3, 1, 1611274959, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (4, '首席运营官', 'coo', '又常称为运营官或营运总监)是公司团体里负责监督管理每日活动的高阶官员。COO是企业组织中最高层的成员之一，监测每日的公司运作，并直接报告给首席执行官。在某些公司中COO会同时兼任总裁，但通常COO还是以兼任常务或资深副总裁的情况居多。', 4, 1, 1611274981, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (5, '首席财务官', 'cof', '企业治理结构发展到一个新阶段的必然产物。没有首席财务官的治理结构不是现代意义上完善的治理结构。从这一层面上看，中国构造治理结构也应设立CFO之类的职位。当然，从本质上讲，CFO在现代治理结构中的真正含义，不是其名称的改变、官位的授予，而是其职责权限的取得，在管理中作用的真正发挥。', 5, 1, 1611275010, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (6, '普通员工', 'pop', '一线员工', 6, 1, 1611275128, NULL);

-- ----------------------------
-- Table structure for __PREFIX__login_log
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__login_log`;
CREATE TABLE `__PREFIX__login_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '访问ID',
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '账号',
  `nickname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '用户昵称',
  `user_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用户 IP',
  `user_agent` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '浏览器 UA',
  `user_os` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '操作系统',
  `user_browser` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '浏览器',
  `status` int(1) NULL DEFAULT 0 COMMENT '登录状态',
  `error` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '错误信息',
  `update_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_ip`(`user_ip`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户登录记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__login_log
-- ----------------------------

-- ----------------------------
-- Table structure for __PREFIX__system_log
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__system_log`;
CREATE TABLE `__PREFIX__system_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户名/或系统',
  `module` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '模块名',
  `controller` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '控制器',
  `action` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '方法名',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '访问地址',
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '错误文件地址',
  `line` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '错误代码行号',
  `code` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '状态码',
  `error` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '异常消息',
  `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '请求参数',
  `ip` bigint(20) NOT NULL COMMENT 'IP地址',
  `method` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '访问方式',
  `type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '日志类型',
  `status` int(11) NULL DEFAULT 1 COMMENT '执行状态',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type`) USING BTREE,
  INDEX `module`(`module`) USING BTREE,
  INDEX `action`(`action`) USING BTREE,
  INDEX `ip`(`ip`) USING BTREE,
  INDEX `method`(`method`) USING BTREE,
  INDEX `create_time`(`create_time`) USING BTREE,
  INDEX `line`(`line`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__system_log
-- ----------------------------

-- ----------------------------
-- Table structure for __PREFIX__user
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user`;
CREATE TABLE `__PREFIX__user`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `group_id` smallint(5) UNSIGNED NOT NULL DEFAULT 1 COMMENT '组id',
  `nickname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户昵称',
  `pwd` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '密码',
  `salt` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '密码盐',
  `qq` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'QQ',
  `wechat` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '微信号',
  `avatar` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '头像',
  `heart` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '这个人很懒，什么都没有留下～ ' COMMENT '用户心情',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'emain',
  `mobile` bigint(20) NULL DEFAULT NULL COMMENT '手机号',
  `card` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '身份证号',
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '家庭住址',
  `modify_name` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '修改次数',
  `score` mediumint(9) UNSIGNED NULL DEFAULT 0 COMMENT '积分',
  `question` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '密保问题',
  `answer` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '答案',
  `gender` int(1) UNSIGNED NULL DEFAULT 1 COMMENT '性别',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `app_id` int(11) NULL DEFAULT NULL COMMENT '用户appid',
  `app_secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户appsecret',
  `hits` mediumint(8) UNSIGNED NULL DEFAULT NULL COMMENT '点击量',
  `hits_day` mediumint(8) UNSIGNED NULL DEFAULT NULL COMMENT '日点击',
  `hits_week` mediumint(8) UNSIGNED NULL DEFAULT NULL COMMENT '周点击',
  `hits_month` mediumint(8) UNSIGNED NULL DEFAULT NULL COMMENT '月点击',
  `hits_lasttime` int(11) NULL DEFAULT NULL COMMENT '点击时间',
  `valicode` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '激活码',
  `invite_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '邀请人',
  `login_ip` bigint(20) NULL DEFAULT NULL COMMENT '登录ip',
  `login_time` int(11) NULL DEFAULT NULL COMMENT '登录时间',
  `login_count` smallint(6) NULL DEFAULT 1 COMMENT '登录次数',
  `url` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '获取用户地址 占位',
  `create_ip` bigint(20) NULL DEFAULT NULL COMMENT '注册IP',
  `create_time` int(11) NOT NULL COMMENT '注册时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `group_id`(`group_id`, `status`) USING BTREE,
  INDEX `create_time`(`create_time`) USING BTREE,
  INDEX `login_time`(`login_time`) USING BTREE,
  INDEX `invite_id`(`invite_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '会员管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__user
-- ----------------------------
INSERT INTO `__PREFIX__user` VALUES (1, 1, 'admin', '513bd12b00b512d0b879962b777b5560', 'wdONQC', NULL, NULL, '', '这个人很懒，什么都没有留下～ ', 'test@swiftadmin.net', NULL, NULL, '河北省邯郸市中华区人民东路023号', 0, 0, '你家的宠物叫啥？', '23', 1, 1, 10001, 'lLtSvJGyFQCVuTdjRIhqza', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2130706433, 1660805456, 129, NULL, 1861775580, 1657332918, NULL);

-- ----------------------------
-- Table structure for __PREFIX__user_group
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_group`;
CREATE TABLE `__PREFIX__user_group`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '会员组名',
  `alias` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '会员标识',
  `score` int(11) NULL DEFAULT NULL COMMENT '会员组积分',
  `pay` int(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否可购买',
  `price` decimal(10, 2) UNSIGNED NULL DEFAULT 0.00 COMMENT '购买价格',
  `upgrade` int(1) NULL DEFAULT NULL COMMENT '是否自动升级',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '会员组状态',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '会员组说明',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '会员组管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__user_group
-- ----------------------------
INSERT INTO `__PREFIX__user_group` VALUES (1, '初级会员', 'v1', 10, 0, 0.00, 1, 1, '新注册会员', 1649039829, NULL);
INSERT INTO `__PREFIX__user_group` VALUES (2, '中级会员', 'v2', 100, 0, 0.00, 1, 1, '活跃会员', 1649039829, NULL);
INSERT INTO `__PREFIX__user_group` VALUES (3, '高级会员', 'v3', 500, 0, 0.00, 1, 1, '高级会员', 1649039829, NULL);
INSERT INTO `__PREFIX__user_group` VALUES (4, '超级会员', 'v4', 2000, 1, 0.00, 1, 1, '超神会员', 1649039829, NULL);

-- ----------------------------
-- Table structure for __PREFIX__user_third
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_third`;
CREATE TABLE `__PREFIX__user_third`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录类型',
  `apptype` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '应用类型',
  `unionid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '第三方UNIONID',
  `openid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '第三方OPENID',
  `nickname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '第三方会员昵称',
  `access_token` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'AccessToken',
  `refresh_token` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `expires_in` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '有效期',
  `create_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `logintime` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '登录时间',
  `expiretime` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`, `user_id`) USING BTREE,
  INDEX `user_id`(`user_id`, `type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '第三方登录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__user_third
-- ----------------------------

-- ----------------------------
-- Table structure for __PREFIX__user_validate
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_validate`;
CREATE TABLE `__PREFIX__user_validate`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '手机号',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '验证码',
  `event` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '事务类型',
  `status` int(11) NULL DEFAULT 0 COMMENT '验证码状态',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户验证码表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__user_validate
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
