-- MySQL dump 10.16  Distrib 10.1.19-MariaDB, for Win32 (AMD64)
--
-- Host: rm-bp1au0s3s83kldx7avo.mysql.rds.aliyuncs.com    Database: rm-bp1au0s3s83kldx7avo.mysql.rds.aliyuncs.com
-- ------------------------------------------------------
-- Server version	5.6.16-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `yichat_tpl`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `yichat_tpl` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `yichat_tpl`;

--
-- Table structure for table `admin_access_control`
--

DROP TABLE IF EXISTS `admin_access_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_access_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) NOT NULL COMMENT '管理员 uid',
  `access_type` varchar(32) NOT NULL COMMENT '权限类型 可选 menu菜单',
  `access_value` varchar(191) NOT NULL COMMENT '权限数值 同具体access_type 相对应',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_access_udx` (`uid`,`access_type`,`access_value`),
  KEY `ix_admin_access_control_state` (`state`),
  KEY `ix_admin_access_control_created_at` (`created_at`),
  KEY `ix_admin_access_control_updated_at` (`updated_at`),
  KEY `ix_admin_access_control_access_type` (`access_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台客户 旗下子账号 权限设置 访问控制表 每条记录为一项权限';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_classify`
--

DROP TABLE IF EXISTS `article_classify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_classify` (
  `classify_id` int(11) NOT NULL AUTO_INCREMENT,
  `classify_title` varchar(32) NOT NULL DEFAULT '' COMMENT '分类 标题',
  `classify_keywords` varchar(128) NOT NULL DEFAULT '' COMMENT '分类 关键字',
  `classify_description` varchar(128) NOT NULL DEFAULT '' COMMENT '分类 描述',
  `classify_img` varchar(128) NOT NULL DEFAULT '' COMMENT '分类图片',
  `rank` int(11) NOT NULL DEFAULT '0' COMMENT '分类排序依据',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`classify_id`),
  KEY `ix_article_classify_updated_at` (`updated_at`),
  KEY `ix_article_classify_state` (`state`),
  KEY `ix_article_classify_created_at` (`created_at`),
  KEY `ix_article_classify_rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页  文章分类';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_help_doc`
--

DROP TABLE IF EXISTS `article_help_doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_help_doc` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `img` varchar(128) NOT NULL DEFAULT '' COMMENT '图片',
  `q_desc` varchar(128) NOT NULL DEFAULT '' COMMENT '问题描述',
  `a_html` text NOT NULL COMMENT '答案内容 html',
  `rank` int(11) NOT NULL DEFAULT '0' COMMENT '文档排序依据',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`doc_id`),
  KEY `ix_article_help_doc_state` (`state`),
  KEY `ix_article_help_doc_updated_at` (`updated_at`),
  KEY `ix_article_help_doc_q_desc` (`q_desc`),
  KEY `ix_article_help_doc_created_at` (`created_at`),
  KEY `ix_article_help_doc_rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页 帮助文档列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_news`
--

DROP TABLE IF EXISTS `article_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_news` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `classify_id` int(11) NOT NULL COMMENT '文章所属分类id',
  `article_title` varchar(64) NOT NULL DEFAULT '' COMMENT '文章 标题',
  `article_keywords` varchar(128) NOT NULL DEFAULT '' COMMENT '文章 关键字',
  `article_description` varchar(128) NOT NULL DEFAULT '' COMMENT '文章 描述',
  `article_date` varchar(32) NOT NULL DEFAULT '' COMMENT '文章发布时间',
  `article_author` varchar(64) NOT NULL DEFAULT '' COMMENT '文章发布者',
  `article_from` varchar(64) NOT NULL DEFAULT '' COMMENT '文章来源',
  `view_count` bigint(20) NOT NULL DEFAULT '0' COMMENT '文章观看次数',
  `article_html` text NOT NULL COMMENT '文章内容 html',
  `article_text` text NOT NULL COMMENT '文章内容 text',
  `rank` int(11) NOT NULL DEFAULT '0' COMMENT '文章排序依据',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`article_id`),
  KEY `ix_article_news_rank` (`rank`),
  KEY `ix_article_news_updated_at` (`updated_at`),
  KEY `ix_article_news_article_from` (`article_from`),
  KEY `ix_article_news_created_at` (`created_at`),
  KEY `ix_article_news_view_count` (`view_count`),
  KEY `ix_article_news_classify_id` (`classify_id`),
  KEY `ix_article_news_state` (`state`),
  KEY `ix_article_news_article_date` (`article_date`),
  KEY `ix_article_news_article_title` (`article_title`),
  KEY `ix_article_news_article_author` (`article_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页 文章信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_base`
--

DROP TABLE IF EXISTS `chat_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_base` (
  `chat_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户 或者群创建者',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户 如果为群 该id为0',
  `last_msg_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后一条消息',
  `last_msg_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后一条消息 对应时间',
  `chat_type` varchar(16) NOT NULL DEFAULT '' COMMENT '会话类型  参见 ChatTypeEnum 枚举',
  `group_name` varchar(64) NOT NULL DEFAULT '' COMMENT '群名称',
  `group_avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '群头像',
  `group_note` varchar(255) NOT NULL DEFAULT '' COMMENT '群公告',
  `group_note_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '修改公告时间',
  `group_note_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '修改公告的用户',
  `invited_confirm` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否开启 群聊邀请确认',
  `all_silent` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否开启 全员禁言',
  `all_silent_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '设置全员禁言的用户',
  `all_silent_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '设置 全员禁言的时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`chat_id`),
  KEY `ix_chat_base_group_note_by` (`group_note_by`),
  KEY `ix_chat_base_invited_confirm` (`invited_confirm`),
  KEY `ix_chat_base_last_msg_id` (`last_msg_id`),
  KEY `ix_chat_base_all_silent` (`all_silent`),
  KEY `ix_chat_base_last_msg_at` (`last_msg_at`),
  KEY `ix_chat_base_all_silent_by` (`all_silent_by`),
  KEY `ix_chat_base_chat_type` (`chat_type`),
  KEY `ix_chat_base_uid` (`uid`),
  KEY `ix_chat_base_all_silent_at` (`all_silent_at`),
  KEY `ix_chat_base_group_name` (`group_name`),
  KEY `ix_chat_base_created_at` (`created_at`),
  KEY `ix_chat_base_updated_at` (`updated_at`),
  KEY `ix_chat_base_group_avatar` (`group_avatar`(191)),
  KEY `ix_chat_base_group_note` (`group_note`(191)),
  KEY `ix_chat_base_target_uid` (`target_uid`),
  KEY `ix_chat_base_group_note_at` (`group_note_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天会话';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_config`
--

DROP TABLE IF EXISTS `chat_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_config` (
  `chat_config_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户 或者群创建者',
  `chat_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应会话',
  `read_msg_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '已读过的最后一条消息',
  `first_msg_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '上次删除之后 第一条消息',
  `is_deleted` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否是已删除的聊天',
  `chat_nick` varchar(32) NOT NULL DEFAULT '' COMMENT '我在本群的昵称',
  `is_top` smallint(6) NOT NULL DEFAULT '0' COMMENT '置顶聊天',
  `is_not_disturb` smallint(6) NOT NULL DEFAULT '0' COMMENT '消息免打扰',
  `is_import` smallint(6) NOT NULL DEFAULT '0' COMMENT '强提醒',
  `is_show_nick` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示群成员昵称',
  `is_chat_save` smallint(6) NOT NULL DEFAULT '0' COMMENT '保存到通讯录',
  `chat_bg` varchar(255) NOT NULL DEFAULT '' COMMENT '聊天背景',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`chat_config_id`),
  UNIQUE KEY `uid_chat_id_udx` (`uid`,`chat_id`),
  KEY `ix_chat_config_created_at` (`created_at`),
  KEY `ix_chat_config_is_top` (`is_top`),
  KEY `ix_chat_config_is_not_disturb` (`is_not_disturb`),
  KEY `ix_chat_config_chat_id` (`chat_id`),
  KEY `ix_chat_config_is_chat_save` (`is_chat_save`),
  KEY `ix_chat_config_is_import` (`is_import`),
  KEY `ix_chat_config_is_show_nick` (`is_show_nick`),
  KEY `ix_chat_config_updated_at` (`updated_at`),
  KEY `ix_chat_config_read_msg_id` (`read_msg_id`),
  KEY `ix_chat_config_chat_bg` (`chat_bg`(191)),
  KEY `ix_chat_config_first_msg_id` (`first_msg_id`),
  KEY `ix_chat_config_is_deleted` (`is_deleted`),
  KEY `ix_chat_config_chat_nick` (`chat_nick`),
  KEY `ix_chat_config_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天会话个人配置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_group_relation`
--

DROP TABLE IF EXISTS `chat_group_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_group_relation` (
  `group_relation_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `chat_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应会话',
  `is_mgr` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否是管理员',
  `mgr_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '设置我为管理员的用户',
  `mgr_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '设置为管理员的时间',
  `is_gag` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否是管理员',
  `gag_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '设置我为管理员的用户',
  `gag_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '设置为管理员的时间',
  `gag_expiry` int(11) NOT NULL DEFAULT '0' COMMENT '禁言持续时间',
  `invited_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '被谁邀请 加入的群',
  `invited_ext` varchar(64) NOT NULL DEFAULT '' COMMENT '邀请的附加信息',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`group_relation_id`),
  UNIQUE KEY `uid_chat_id_udx` (`uid`,`chat_id`),
  KEY `ix_chat_group_relation_gag_by` (`gag_by`),
  KEY `ix_chat_group_relation_updated_at` (`updated_at`),
  KEY `ix_chat_group_relation_is_gag` (`is_gag`),
  KEY `ix_chat_group_relation_gag_at` (`gag_at`),
  KEY `ix_chat_group_relation_mgr_at` (`mgr_at`),
  KEY `ix_chat_group_relation_mgr_by` (`mgr_by`),
  KEY `ix_chat_group_relation_invited_ext` (`invited_ext`),
  KEY `ix_chat_group_relation_is_mgr` (`is_mgr`),
  KEY `ix_chat_group_relation_invited_by` (`invited_by`),
  KEY `ix_chat_group_relation_created_at` (`created_at`),
  KEY `ix_chat_group_relation_gag_expiry` (`gag_expiry`),
  KEY `ix_chat_group_relation_chat_id` (`chat_id`),
  KEY `ix_chat_group_relation_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天会话 群成员关联';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_msg`
--

DROP TABLE IF EXISTS `chat_msg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_msg` (
  `msg_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `chat_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应会话',
  `msg_content` text NOT NULL COMMENT '消息内容',
  `msg_length` int(11) NOT NULL DEFAULT '0' COMMENT '消息长度 如果是语音 或视频 就是时长(秒)',
  `msg_meta` varchar(255) NOT NULL DEFAULT '' COMMENT '动态 元数据 json字符串 用于扩展',
  `serial_number` varchar(64) NOT NULL DEFAULT '' COMMENT '设备序列号',
  `msg_cmd` varchar(32) NOT NULL DEFAULT '' COMMENT '消息类型 标注',
  `send_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发送时间',
  `msg_type` varchar(32) NOT NULL DEFAULT '' COMMENT '消息类型',
  `msg_slug` varchar(32) NOT NULL DEFAULT '' COMMENT '消息类型 标注',
  `is_at_all` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否@所有人',
  `at_uids` varchar(255) NOT NULL DEFAULT '' COMMENT '该消息@的用户,使用逗号分隔',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`msg_id`),
  KEY `ix_chat_msg_is_at_all` (`is_at_all`),
  KEY `ix_chat_msg_created_at` (`created_at`),
  KEY `ix_chat_msg_at_uids` (`at_uids`(191)),
  KEY `ix_chat_msg_state` (`state`),
  KEY `ix_chat_msg_serial_number` (`serial_number`),
  KEY `ix_chat_msg_msg_slug` (`msg_slug`),
  KEY `ix_chat_msg_msg_cmd` (`msg_cmd`),
  KEY `ix_chat_msg_send_at` (`send_at`),
  KEY `ix_chat_msg_uid` (`uid`),
  KEY `ix_chat_msg_msg_type` (`msg_type`),
  KEY `ix_chat_msg_updated_at` (`updated_at`),
  KEY `ix_chat_msg_chat_id` (`chat_id`),
  KEY `ix_chat_msg_msg_length` (`msg_length`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天会话个人配置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_comment`
--

DROP TABLE IF EXISTS `circle_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_comment` (
  `comment_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `content` varchar(512) NOT NULL DEFAULT '' COMMENT '文本内容',
  `track_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应朋友圈动态',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `reply_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应回复用户',
  `reply_comment_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应回复 评论',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`comment_id`),
  KEY `ix_circle_comment_uid` (`uid`),
  KEY `ix_circle_comment_reply_uid` (`reply_uid`),
  KEY `ix_circle_comment_reply_comment_id` (`reply_comment_id`),
  KEY `ix_circle_comment_state` (`state`),
  KEY `ix_circle_comment_updated_at` (`updated_at`),
  KEY `ix_circle_comment_created_at` (`created_at`),
  KEY `ix_circle_comment_track_id` (`track_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈动态 其他用户评论';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_liked`
--

DROP TABLE IF EXISTS `circle_liked`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_liked` (
  `liked_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `track_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应朋友圈动态',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`liked_id`),
  UNIQUE KEY `uid_track_id_udx` (`uid`,`track_id`),
  KEY `ix_circle_liked_created_at` (`created_at`),
  KEY `ix_circle_liked_updated_at` (`updated_at`),
  KEY `ix_circle_liked_uid` (`uid`),
  KEY `ix_circle_liked_track_id` (`track_id`),
  KEY `ix_circle_liked_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈动态 其他用户点赞';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_notify`
--

DROP TABLE IF EXISTS `circle_notify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_notify` (
  `notify_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应产生该消息的 用户',
  `to_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户  提醒该用户查看该消息',
  `notify_slug` varchar(32) NOT NULL DEFAULT '' COMMENT '朋友圈提醒 标注',
  `track_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应朋友圈动态 track_id',
  `liked_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应 点赞 liked_id',
  `comment_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应评论 comment_id',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`notify_id`),
  KEY `ix_circle_notify_state` (`state`),
  KEY `ix_circle_notify_uid` (`uid`),
  KEY `ix_circle_notify_created_at` (`created_at`),
  KEY `ix_circle_notify_to_uid` (`to_uid`),
  KEY `ix_circle_notify_updated_at` (`updated_at`),
  KEY `ix_circle_notify_notify_slug` (`notify_slug`),
  KEY `ix_circle_notify_track_id` (`track_id`),
  KEY `ix_circle_notify_liked_id` (`liked_id`),
  KEY `ix_circle_notify_comment_id` (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈提醒 跟我有关的消息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_photo`
--

DROP TABLE IF EXISTS `circle_photo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_photo` (
  `photo_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `track_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应朋友圈动态',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `photo_url` varchar(255) NOT NULL DEFAULT '' COMMENT '对应图片地址',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`photo_id`),
  KEY `ix_circle_photo_created_at` (`created_at`),
  KEY `ix_circle_photo_photo_url` (`photo_url`(191)),
  KEY `ix_circle_photo_track_id` (`track_id`),
  KEY `ix_circle_photo_updated_at` (`updated_at`),
  KEY `ix_circle_photo_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈动态对应图片';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_privilege`
--

DROP TABLE IF EXISTS `circle_privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_privilege` (
  `privilege_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `not_see_me` smallint(6) NOT NULL DEFAULT '0' COMMENT '不让他看我',
  `not_see_it` smallint(6) NOT NULL DEFAULT '0' COMMENT '不看他',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`privilege_id`),
  UNIQUE KEY `uid_target_uid_udx` (`uid`,`target_uid`),
  KEY `ix_circle_privilege_not_see_it` (`not_see_it`),
  KEY `ix_circle_privilege_created_at` (`created_at`),
  KEY `ix_circle_privilege_updated_at` (`updated_at`),
  KEY `ix_circle_privilege_uid` (`uid`),
  KEY `ix_circle_privilege_target_uid` (`target_uid`),
  KEY `ix_circle_privilege_not_see_me` (`not_see_me`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈权限设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_timeline`
--

DROP TABLE IF EXISTS `circle_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_timeline` (
  `timeline_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应产生该消息的 用户',
  `to_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户  提醒该用户查看该消息',
  `track_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应朋友圈动态 track_id',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '1 新的  2 看过 4 无法看  状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`timeline_id`),
  KEY `ix_circle_timeline_to_uid` (`to_uid`),
  KEY `ix_circle_timeline_track_id` (`track_id`),
  KEY `ix_circle_timeline_state` (`state`),
  KEY `ix_circle_timeline_created_at` (`created_at`),
  KEY `ix_circle_timeline_updated_at` (`updated_at`),
  KEY `ix_circle_timeline_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈时间线 跟我有关的朋友圈动态';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_track`
--

DROP TABLE IF EXISTS `circle_track`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_track` (
  `track_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `content` varchar(512) NOT NULL DEFAULT '' COMMENT '文本内容',
  `privacy` varchar(16) NOT NULL DEFAULT 'public' COMMENT '隐私类型  参见 CirclePrivacyEnum 枚举 注意这里面 只存有 whitelist, blacklist 表示白名单或黑名单  private 是通过设置 state 为 2 来实现的',
  `remind` varchar(16) NOT NULL DEFAULT 'none' COMMENT '提醒类型  参见 CircleRemindEnum 枚举',
  `location` varchar(64) NOT NULL DEFAULT '' COMMENT '位置信息 位置名称',
  `longitude` float NOT NULL DEFAULT '0' COMMENT '位置信息 经度',
  `latitude` float NOT NULL DEFAULT '0' COMMENT '位置信息 纬度',
  `forward_url` varchar(255) NOT NULL DEFAULT '' COMMENT '转发内容 url',
  `thumbnail` varchar(255) NOT NULL DEFAULT '' COMMENT '动态 缩略图',
  `forward_meta` varchar(255) NOT NULL DEFAULT '' COMMENT '动态 元数据 json字符串 用于扩展',
  `track_slug` varchar(32) NOT NULL DEFAULT '' COMMENT '朋友圈动态 标注',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举 为2表示为私密信息 只有自己可见',
  `expires_at` datetime NOT NULL DEFAULT '2099-12-31 23:59:59' COMMENT '动态 有效期',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`track_id`),
  KEY `ix_circle_track_latitude` (`latitude`),
  KEY `ix_circle_track_privacy` (`privacy`),
  KEY `ix_circle_track_expires_at` (`expires_at`),
  KEY `ix_circle_track_state` (`state`),
  KEY `ix_circle_track_remind` (`remind`),
  KEY `ix_circle_track_location` (`location`),
  KEY `ix_circle_track_updated_at` (`updated_at`),
  KEY `ix_circle_track_longitude` (`longitude`),
  KEY `ix_circle_track_uid` (`uid`),
  KEY `ix_circle_track_created_at` (`created_at`),
  KEY `ix_circle_track_track_slug` (`track_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈动态表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_track_privacy`
--

DROP TABLE IF EXISTS `circle_track_privacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_track_privacy` (
  `privacy_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `track_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应朋友圈动态',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `can_see_it` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否可以看',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`privacy_id`),
  UNIQUE KEY `track_id_target_uid_udx` (`track_id`,`target_uid`),
  KEY `ix_circle_track_privacy_created_at` (`created_at`),
  KEY `ix_circle_track_privacy_updated_at` (`updated_at`),
  KEY `ix_circle_track_privacy_target_uid` (`target_uid`),
  KEY `ix_circle_track_privacy_track_id` (`track_id`),
  KEY `ix_circle_track_privacy_can_see_it` (`can_see_it`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈动态隐私设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `circle_track_remind`
--

DROP TABLE IF EXISTS `circle_track_remind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `circle_track_remind` (
  `remind_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `track_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应朋友圈动态',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`remind_id`),
  UNIQUE KEY `track_id_target_uid_udx` (`track_id`,`target_uid`),
  KEY `ix_circle_track_remind_updated_at` (`updated_at`),
  KEY `ix_circle_track_remind_target_uid` (`target_uid`),
  KEY `ix_circle_track_remind_uid` (`uid`),
  KEY `ix_circle_track_remind_created_at` (`created_at`),
  KEY `ix_circle_track_remind_track_id` (`track_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='朋友圈动态提醒设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_applied`
--

DROP TABLE IF EXISTS `contact_applied`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_applied` (
  `applied_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `has_replay` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否有回复',
  `is_new_replay` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否新的回复',
  `is_new` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否新的用户申请',
  `is_expired` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否过期',
  `add_by` varchar(32) NOT NULL DEFAULT '' COMMENT '添加来源 目前支持 account group cellphone qrcode card other shake nearby',
  `add_ext` varchar(64) NOT NULL DEFAULT '' COMMENT '添加来源 的附加信息 如果 add_by 为 group 这里就是 group_id',
  `applied_text` varchar(255) NOT NULL DEFAULT '' COMMENT '申请附加消息',
  `last_text` varchar(255) NOT NULL DEFAULT '' COMMENT '最后一次 回复的消息 用于展示',
  `last_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后回复的时间',
  `replay_list` text NOT NULL COMMENT '回复列表 具体数据为 json 格式',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`applied_id`),
  KEY `ix_contact_applied_applied_text` (`applied_text`(191)),
  KEY `ix_contact_applied_has_replay` (`has_replay`),
  KEY `ix_contact_applied_add_by` (`add_by`),
  KEY `ix_contact_applied_is_new_replay` (`is_new_replay`),
  KEY `ix_contact_applied_created_at` (`created_at`),
  KEY `ix_contact_applied_is_new` (`is_new`),
  KEY `ix_contact_applied_updated_at` (`updated_at`),
  KEY `ix_contact_applied_is_expired` (`is_expired`),
  KEY `ix_contact_applied_uid` (`uid`),
  KEY `ix_contact_applied_last_text` (`last_text`(191)),
  KEY `ix_contact_applied_add_ext` (`add_ext`),
  KEY `ix_contact_applied_state` (`state`),
  KEY `ix_contact_applied_last_at` (`last_at`),
  KEY `ix_contact_applied_target_uid` (`target_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='添加联系人申请';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_base`
--

DROP TABLE IF EXISTS `contact_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_base` (
  `contact_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `is_star` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否星标',
  `add_by` varchar(32) NOT NULL DEFAULT '' COMMENT '添加来源 目前支持 account group cellphone qrcode card other shake nearby',
  `add_ext` varchar(64) NOT NULL DEFAULT '' COMMENT '添加来源 的附加信息 如果 add_by 为 group 这里就是 group_id',
  `is_initiator` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否是 添加联系人的发起方',
  `contact_type` varchar(16) NOT NULL DEFAULT 'none' COMMENT '联系人状态 参见 ContactTypeEnum',
  `applied_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应发送添加好友请求id',
  `mark_nick` varchar(32) NOT NULL DEFAULT '' COMMENT '备注名',
  `mark_cellphone` varchar(32) NOT NULL DEFAULT '' COMMENT '备注电话号码',
  `mark_note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注描述',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `uid_target_uid_udx` (`uid`,`target_uid`),
  KEY `ix_contact_base_mark_nick` (`mark_nick`),
  KEY `ix_contact_base_target_uid` (`target_uid`),
  KEY `ix_contact_base_applied_id` (`applied_id`),
  KEY `ix_contact_base_created_at` (`created_at`),
  KEY `ix_contact_base_is_star` (`is_star`),
  KEY `ix_contact_base_contact_type` (`contact_type`),
  KEY `ix_contact_base_add_by` (`add_by`),
  KEY `ix_contact_base_add_ext` (`add_ext`),
  KEY `ix_contact_base_mark_cellphone` (`mark_cellphone`),
  KEY `ix_contact_base_is_initiator` (`is_initiator`),
  KEY `ix_contact_base_mark_note` (`mark_note`(191)),
  KEY `ix_contact_base_uid` (`uid`),
  KEY `ix_contact_base_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='联系人表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_blacklist`
--

DROP TABLE IF EXISTS `contact_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_blacklist` (
  `blacklist_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`blacklist_id`),
  UNIQUE KEY `uid_target_uid_udx` (`uid`,`target_uid`),
  KEY `ix_contact_blacklist_target_uid` (`target_uid`),
  KEY `ix_contact_blacklist_uid` (`uid`),
  KEY `ix_contact_blacklist_updated_at` (`updated_at`),
  KEY `ix_contact_blacklist_created_at` (`created_at`),
  KEY `ix_contact_blacklist_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='联系人 黑名单';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_tag`
--

DROP TABLE IF EXISTS `contact_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_tag` (
  `tag_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `tag_name` varchar(32) NOT NULL DEFAULT '' COMMENT '标签名称',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`tag_id`),
  KEY `ix_contact_tag_created_at` (`created_at`),
  KEY `ix_contact_tag_uid` (`uid`),
  KEY `ix_contact_tag_updated_at` (`updated_at`),
  KEY `ix_contact_tag_tag_name` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='联系人 标签';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_tag_relation`
--

DROP TABLE IF EXISTS `contact_tag_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_tag_relation` (
  `tag_relation_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `tag_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应标签',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`tag_relation_id`),
  UNIQUE KEY `uid_target_uid_tag_id_udx` (`uid`,`target_uid`,`tag_id`),
  KEY `ix_contact_tag_relation_tag_id` (`tag_id`),
  KEY `ix_contact_tag_relation_created_at` (`created_at`),
  KEY `ix_contact_tag_relation_updated_at` (`updated_at`),
  KEY `ix_contact_tag_relation_uid` (`uid`),
  KEY `ix_contact_tag_relation_target_uid` (`target_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='联系人 标签关联';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrate_version`
--

DROP TABLE IF EXISTS `migrate_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrate_version` (
  `repository_id` varchar(171) NOT NULL,
  `repository_path` text,
  `version` int(11) DEFAULT NULL,
  PRIMARY KEY (`repository_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin_favorite`
--

DROP TABLE IF EXISTS `plugin_favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_favorite` (
  `favorite_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `from_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应来源用户',
  `from_chat_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应来源聊天 如果是群聊的话 显示群聊的名称',
  `content` varchar(512) NOT NULL DEFAULT '' COMMENT '文本内容',
  `content_url` varchar(255) NOT NULL DEFAULT '' COMMENT '收藏内容url 用于 图片 视频 文件 录音 链接 等内容的url',
  `thumbnail` varchar(255) NOT NULL DEFAULT '' COMMENT '收藏缩略图 用于 图片 视频 和 链接',
  `location` varchar(64) NOT NULL DEFAULT '' COMMENT '收藏位置  位置信息 位置名称',
  `longitude` float NOT NULL DEFAULT '0' COMMENT '收藏位置  位置信息 经度',
  `latitude` float NOT NULL DEFAULT '0' COMMENT '收藏位置   位置信息 纬度',
  `favorite_slug` varchar(32) NOT NULL DEFAULT '' COMMENT '我的收藏 类型标注 支持  text  文本 image  图片  forward  转发链接   location  位置  file 文件  video 视频  voice 录音  note 笔记',
  `favorite_meta` varchar(255) NOT NULL DEFAULT '' COMMENT '动态元数据 json字符串 用于扩展  目前用于收藏的链接',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`favorite_id`),
  KEY `ix_plugin_favorite_from_uid` (`from_uid`),
  KEY `ix_plugin_favorite_updated_at` (`updated_at`),
  KEY `ix_plugin_favorite_from_chat_id` (`from_chat_id`),
  KEY `ix_plugin_favorite_location` (`location`),
  KEY `ix_plugin_favorite_longitude` (`longitude`),
  KEY `ix_plugin_favorite_created_at` (`created_at`),
  KEY `ix_plugin_favorite_latitude` (`latitude`),
  KEY `ix_plugin_favorite_favorite_slug` (`favorite_slug`),
  KEY `ix_plugin_favorite_uid` (`uid`),
  KEY `ix_plugin_favorite_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='我的收藏夹';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin_shake_history`
--

DROP TABLE IF EXISTS `plugin_shake_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_shake_history` (
  `shake_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `distance` float NOT NULL DEFAULT '0' COMMENT '距离 单位 km',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`shake_id`),
  KEY `ix_plugin_shake_history_target_uid` (`target_uid`),
  KEY `ix_plugin_shake_history_distance` (`distance`),
  KEY `ix_plugin_shake_history_state` (`state`),
  KEY `ix_plugin_shake_history_created_at` (`created_at`),
  KEY `ix_plugin_shake_history_updated_at` (`updated_at`),
  KEY `ix_plugin_shake_history_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='摇一摇历史数据';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_api_record`
--

DROP TABLE IF EXISTS `site_api_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_api_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `api_class` varchar(128) NOT NULL DEFAULT '' COMMENT '请求类',
  `action` varchar(128) NOT NULL DEFAULT '' COMMENT '请求方法',
  `ret_length` int(11) NOT NULL DEFAULT '0' COMMENT '结果长度',
  `ret_used` int(11) NOT NULL DEFAULT '0' COMMENT '响应时间 单位毫秒',
  `ret_code` varchar(16) NOT NULL COMMENT '返回状态码',
  `op_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作者  uid',
  `op_ip` varchar(32) NOT NULL COMMENT '操作 IP',
  `op_location` varchar(32) NOT NULL COMMENT '操作 地域',
  `serial_number` varchar(64) NOT NULL DEFAULT '' COMMENT '设备序列号',
  `net_type` varchar(64) NOT NULL DEFAULT '' COMMENT '网络类型',
  `prom_channel` varchar(64) NOT NULL DEFAULT '' COMMENT '推广渠道',
  `user_token` varchar(64) NOT NULL DEFAULT '' COMMENT 'user token',
  `app_ver` varchar(64) NOT NULL DEFAULT '' COMMENT 'app ver',
  `accept_language` varchar(64) NOT NULL DEFAULT '' COMMENT 'Accept-Language',
  `error_class` varchar(32) NOT NULL DEFAULT '' COMMENT '出错类',
  `error_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '出错信息',
  `params_str` text NOT NULL COMMENT '参数 json',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`id`),
  KEY `ix_site_api_record_op_uid` (`op_uid`),
  KEY `ix_site_api_record_app_ver` (`app_ver`),
  KEY `ix_site_api_record_updated_at` (`updated_at`),
  KEY `ix_site_api_record_ret_code` (`ret_code`),
  KEY `ix_site_api_record_accept_language` (`accept_language`),
  KEY `ix_site_api_record_prom_channel` (`prom_channel`),
  KEY `ix_site_api_record_user_token` (`user_token`),
  KEY `ix_site_api_record_api_class` (`api_class`),
  KEY `ix_site_api_record_net_type` (`net_type`),
  KEY `ix_site_api_record_error_msg` (`error_msg`(191)),
  KEY `ix_site_api_record_ret_used` (`ret_used`),
  KEY `ix_site_api_record_serial_number` (`serial_number`),
  KEY `ix_site_api_record_op_location` (`op_location`),
  KEY `ix_site_api_record_action` (`action`),
  KEY `ix_site_api_record_created_at` (`created_at`),
  KEY `ix_site_api_record_op_ip` (`op_ip`),
  KEY `ix_site_api_record_error_class` (`error_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='API请求记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_content_config`
--

DROP TABLE IF EXISTS `site_content_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_content_config` (
  `content_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应用户',
  `chat_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应会话',
  `content_slug` varchar(32) NOT NULL COMMENT '分块内容 类型 slug',
  `content_key` varchar(32) NOT NULL COMMENT '分块内容 类型 key',
  `content_title` varchar(64) NOT NULL DEFAULT '' COMMENT '配置 标题 字符串',
  `content_doc` text NOT NULL COMMENT '配置 帮助信息 字符串',
  `content_text` text NOT NULL COMMENT '配置值 文本 字符串',
  `content_config` text NOT NULL COMMENT '配置值 json 字符串',
  `content_group` varchar(16) NOT NULL DEFAULT 'group' COMMENT '区块类型  item 为单个条目  list 为一组设置',
  `content_rank` int(11) NOT NULL DEFAULT '0' COMMENT '排序依据',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`content_id`),
  KEY `ix_site_content_config_created_at` (`created_at`),
  KEY `ix_site_content_config_state` (`state`),
  KEY `ix_site_content_config_uid` (`uid`),
  KEY `ix_site_content_config_updated_at` (`updated_at`),
  KEY `ix_site_content_config_chat_id` (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='块配置信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_op_record`
--

DROP TABLE IF EXISTS `site_op_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_op_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `op_type` smallint(6) NOT NULL DEFAULT '0' COMMENT '操作类型  0 未知  1 插入  2 更改 3 删除',
  `op_table` varchar(32) NOT NULL DEFAULT '' COMMENT '修改数据 表名',
  `op_prikey` varchar(32) NOT NULL COMMENT '操作的数据表  主键 名称',
  `op_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作者  uid',
  `op_prival` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作的 本条记录的 主键 id',
  `op_args` text NOT NULL COMMENT '本次操作的 参数',
  `op_diff` text NOT NULL COMMENT '操作前后 记录数值 差分',
  `op_ip` varchar(32) NOT NULL COMMENT '操作 IP',
  `op_location` varchar(32) NOT NULL COMMENT '操作 地域',
  `op_uri` varchar(255) NOT NULL DEFAULT '' COMMENT '操作 来源 url',
  `op_refer` varchar(255) NOT NULL DEFAULT '' COMMENT '操作 refer',
  `last_value` text NOT NULL COMMENT '上一次 记录的值  使用 json 序列化',
  `this_value` text NOT NULL COMMENT '更改之后本条记录',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`id`),
  KEY `ix_site_op_record_op_prikey` (`op_prikey`),
  KEY `ix_site_op_record_op_prival` (`op_prival`),
  KEY `ix_site_op_record_op_location` (`op_location`),
  KEY `ix_site_op_record_created_at` (`created_at`),
  KEY `ix_site_op_record_op_type` (`op_type`),
  KEY `ix_site_op_record_updated_at` (`updated_at`),
  KEY `ix_site_op_record_op_table` (`op_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据变更记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_sms_log`
--

DROP TABLE IF EXISTS `site_sms_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_sms_log` (
  `sms_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `send_type` varchar(32) NOT NULL DEFAULT '' COMMENT '发送短信类型',
  `cellphone` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号码',
  `send_res` varchar(255) NOT NULL DEFAULT '' COMMENT '出错信息',
  `sms_code` varchar(16) NOT NULL DEFAULT '' COMMENT '验证码',
  `sms_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '短信内容',
  `op_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作者  uid',
  `op_ip` varchar(32) NOT NULL COMMENT '操作 IP',
  `op_location` varchar(32) NOT NULL COMMENT '操作 地域',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`sms_id`),
  KEY `ix_site_sms_log_created_at` (`created_at`),
  KEY `ix_site_sms_log_op_location` (`op_location`),
  KEY `ix_site_sms_log_updated_at` (`updated_at`),
  KEY `ix_site_sms_log_cellphone` (`cellphone`),
  KEY `ix_site_sms_log_sms_code` (`sms_code`),
  KEY `ix_site_sms_log_sms_msg` (`sms_msg`(191)),
  KEY `ix_site_sms_log_op_uid` (`op_uid`),
  KEY `ix_site_sms_log_op_ip` (`op_ip`),
  KEY `ix_site_sms_log_send_type` (`send_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发送短信日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_app_config`
--

DROP TABLE IF EXISTS `user_app_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_app_config` (
  `uid` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '用户uid',
  `enable_notify_msg` smallint(6) NOT NULL DEFAULT '1' COMMENT '新消息提醒 -> 接收新消息通知',
  `enable_notify_invite` smallint(6) NOT NULL DEFAULT '1' COMMENT '新消息提醒 -> 接受语音和视频通话邀请通知',
  `enable_notify_details` smallint(6) NOT NULL DEFAULT '1' COMMENT '新消息提醒 -> 通知显示消息详情',
  `enable_invite_vibrating` smallint(6) NOT NULL DEFAULT '1' COMMENT '新消息提醒 -> 语音和视频通话邀请 -> 震动',
  `enable_invite_sound` smallint(6) NOT NULL DEFAULT '1' COMMENT '新消息提醒 -> 语音和视频通话邀请 -> 声音',
  `enable_msg_vibrating` smallint(6) NOT NULL DEFAULT '0' COMMENT '新消息提醒 -> 聊天界面中的新消息通知 -> 震动',
  `enable_msg_sound` smallint(6) NOT NULL DEFAULT '0' COMMENT '新消息提醒 -> 聊天界面中的新消息通知 -> 声音',
  `enable_not_disturb` smallint(6) NOT NULL DEFAULT '0' COMMENT '勿扰模式 -> 勿扰模式',
  `not_disturb_start` varchar(64) NOT NULL DEFAULT '23:00' COMMENT '勿扰模式 -> 开始时间',
  `not_disturb_end` varchar(64) NOT NULL DEFAULT '8:00' COMMENT '勿扰模式 -> 结束时间',
  `enable_msg_receiver` smallint(6) NOT NULL DEFAULT '0' COMMENT '聊天 -> 使用听筒播放语音',
  `enable_msg_enter` smallint(6) NOT NULL DEFAULT '0' COMMENT '聊天 -> 回车键发送消息',
  `enable_privacy_review` smallint(6) NOT NULL DEFAULT '1' COMMENT '隐私 -> 加我为好友时需要验证',
  `enable_privacy_contact` smallint(6) NOT NULL DEFAULT '0' COMMENT '隐私 -> 向我推荐通讯录好友',
  `enable_add_account` smallint(6) NOT NULL DEFAULT '0' COMMENT '隐私 -> 添加我的方式 -> 帐号',
  `enable_add_cellphone` smallint(6) NOT NULL DEFAULT '0' COMMENT '隐私 -> 添加我的方式 -> 手机号',
  `enable_add_group` smallint(6) NOT NULL DEFAULT '0' COMMENT '隐私 -> 添加我的方式 -> 群聊',
  `enable_add_qrcode` smallint(6) NOT NULL DEFAULT '0' COMMENT '隐私 -> 添加我的方式 -> 二维码',
  `enable_add_card` smallint(6) NOT NULL DEFAULT '0' COMMENT '隐私 -> 添加我的方式 -> 名片',
  `enable_circle_stranger10` smallint(6) NOT NULL DEFAULT '1' COMMENT '隐私 -> 允许陌生人查看十条朋友圈',
  `enum_circle_visible` varchar(64) NOT NULL DEFAULT 'all' COMMENT '隐私 -> 允许朋友查看朋友圈的范围(all 全部 half_year 最近半年 one_month 最近一个月 three_days 最近三天)',
  `enable_circle_notify` smallint(6) NOT NULL DEFAULT '1' COMMENT '隐私 -> 朋友圈更新提醒',
  `enable_landscape` smallint(6) NOT NULL DEFAULT '0' COMMENT '通用 -> 开启横屏模式',
  `enum_auto_download` varchar(64) NOT NULL DEFAULT 'never' COMMENT '通用 -> 自动下载安装包(never 从不 wifi 仅Wi-Fi网络)',
  `enum_language` varchar(64) NOT NULL DEFAULT 'system' COMMENT '通用 -> 多语言(system 跟随系统)',
  `num_font_size` smallint(6) NOT NULL DEFAULT '0' COMMENT '通用 -> 字体大小(一共8个级别 从 -1 到 6, 0 为 标准大小)',
  `enable_file_auto_download` smallint(6) NOT NULL DEFAULT '1' COMMENT '通用 -> 照片、视频和文件 -> 自动下载',
  `enable_file_save_photo` smallint(6) NOT NULL DEFAULT '1' COMMENT '通用 -> 照片、视频和文件 -> 照片',
  `enable_file_save_video` smallint(6) NOT NULL DEFAULT '1' COMMENT '通用 -> 照片、视频和文件 -> 视频',
  `enable_file_auto_play` smallint(6) NOT NULL DEFAULT '1' COMMENT '通用 -> 照片、视频和文件 -> 移动网络下视频自动播放',
  `default_chat_bg` varchar(255) NOT NULL DEFAULT '' COMMENT '默认聊天背景 如果聊天没设置聊天背景的话 将会使用这个背景图片',
  `default_circle_bg` varchar(255) NOT NULL DEFAULT '' COMMENT '默认朋友圈背景图  在个人朋友圈首页设置',
  `enable_nearby_geo` smallint(6) NOT NULL DEFAULT '0' COMMENT '附近的人 是否开启位置  退出之后自动关闭',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`uid`),
  KEY `ix_user_app_config_enum_language` (`enum_language`),
  KEY `ix_user_app_config_num_font_size` (`num_font_size`),
  KEY `ix_user_app_config_enable_file_auto_download` (`enable_file_auto_download`),
  KEY `ix_user_app_config_enable_file_auto_play` (`enable_file_auto_play`),
  KEY `ix_user_app_config_enable_file_save_photo` (`enable_file_save_photo`),
  KEY `ix_user_app_config_enable_file_save_video` (`enable_file_save_video`),
  KEY `ix_user_app_config_enable_notify_msg` (`enable_notify_msg`),
  KEY `ix_user_app_config_default_chat_bg` (`default_chat_bg`(191)),
  KEY `ix_user_app_config_enable_nearby_geo` (`enable_nearby_geo`),
  KEY `ix_user_app_config_created_at` (`created_at`),
  KEY `ix_user_app_config_enable_notify_invite` (`enable_notify_invite`),
  KEY `ix_user_app_config_updated_at` (`updated_at`),
  KEY `ix_user_app_config_enable_notify_details` (`enable_notify_details`),
  KEY `ix_user_app_config_enable_invite_vibrating` (`enable_invite_vibrating`),
  KEY `ix_user_app_config_enable_invite_sound` (`enable_invite_sound`),
  KEY `ix_user_app_config_enable_msg_vibrating` (`enable_msg_vibrating`),
  KEY `ix_user_app_config_enable_msg_sound` (`enable_msg_sound`),
  KEY `ix_user_app_config_enable_not_disturb` (`enable_not_disturb`),
  KEY `ix_user_app_config_not_disturb_start` (`not_disturb_start`),
  KEY `ix_user_app_config_not_disturb_end` (`not_disturb_end`),
  KEY `ix_user_app_config_enable_msg_receiver` (`enable_msg_receiver`),
  KEY `ix_user_app_config_enable_msg_enter` (`enable_msg_enter`),
  KEY `ix_user_app_config_enable_privacy_review` (`enable_privacy_review`),
  KEY `ix_user_app_config_enable_privacy_contact` (`enable_privacy_contact`),
  KEY `ix_user_app_config_enable_add_account` (`enable_add_account`),
  KEY `ix_user_app_config_enable_add_cellphone` (`enable_add_cellphone`),
  KEY `ix_user_app_config_enable_add_group` (`enable_add_group`),
  KEY `ix_user_app_config_enable_add_qrcode` (`enable_add_qrcode`),
  KEY `ix_user_app_config_enable_add_card` (`enable_add_card`),
  KEY `ix_user_app_config_enable_circle_stranger10` (`enable_circle_stranger10`),
  KEY `ix_user_app_config_enum_circle_visible` (`enum_circle_visible`),
  KEY `ix_user_app_config_enable_circle_notify` (`enable_circle_notify`),
  KEY `ix_user_app_config_enable_landscape` (`enable_landscape`),
  KEY `ix_user_app_config_enum_auto_download` (`enum_auto_download`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户app设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_base`
--

DROP TABLE IF EXISTS `user_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_base` (
  `uid` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid_hash` varchar(32) NOT NULL DEFAULT '' COMMENT '用户 uid_hash',
  `nick` varchar(32) NOT NULL DEFAULT '' COMMENT '用户标题, 用于显示',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像, 用于显示',
  `account` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `account_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上次修改账号 时间',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '用户登录密码, 存储加盐md5',
  `password_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上一次修改密码的时间',
  `login_ip` varchar(32) NOT NULL DEFAULT '' COMMENT '上次登录ip',
  `login_location` varchar(64) NOT NULL DEFAULT '' COMMENT '登录地域 根据ip获取',
  `login_count` bigint(20) NOT NULL DEFAULT '0' COMMENT '登录次数',
  `login_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上次登陆时间',
  `longitude` float NOT NULL DEFAULT '0' COMMENT '位置信息 经度',
  `latitude` float NOT NULL DEFAULT '0' COMMENT '位置信息 纬度',
  `pc_serial_number` varchar(64) NOT NULL DEFAULT '' COMMENT '上一次 PC 设备序列号',
  `mobile_serial_number` varchar(64) NOT NULL DEFAULT '' COMMENT '上一次 手机 设备序列号',
  `web_serial_number` varchar(64) NOT NULL DEFAULT '' COMMENT '上一次 web 设备序列号',
  `qr_code` varchar(64) NOT NULL DEFAULT '' COMMENT '用户当前的 二维码  必须匹配才有效',
  `signature` varchar(255) NOT NULL DEFAULT '' COMMENT '用户个性签名',
  `user_note` varchar(255) NOT NULL DEFAULT '' COMMENT '用户信息备注信息 后台可见',
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '邮箱地址',
  `cellphone` varchar(64) NOT NULL DEFAULT '' COMMENT '手机号码',
  `cellphone_area` varchar(64) NOT NULL DEFAULT '' COMMENT '手机号码 前缀区号',
  `cellphone_num` varchar(64) NOT NULL DEFAULT '' COMMENT '手机号码 具体号码',
  `location` varchar(64) NOT NULL DEFAULT '' COMMENT '用户区域 用户自己设置',
  `gender` varchar(16) NOT NULL DEFAULT '' COMMENT '用户性别  参见 GenderEnum 枚举',
  `gender_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上次修改性别 时间',
  `birthday` int(11) NOT NULL DEFAULT '0' COMMENT '用户生日 整数存储 格式 20190102',
  `openid` varchar(64) NOT NULL DEFAULT '' COMMENT 'oAuth openid',
  `api_key` varchar(64) NOT NULL DEFAULT '' COMMENT '用户 api 认证key',
  `agent_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '上级企业uid, 子公司 parent  业务员 sales  邀请码用户 invite 有此字段',
  `parent_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '上级子公司uid, 业务员 sales  邀请码用户 invite 有此字段',
  `sales_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '上级业务员uid, 邀请码用户 invite 有此字段',
  `invite_code` varchar(32) NOT NULL DEFAULT '' COMMENT '用户的 邀请码, 业务员 sales  邀请码用户 invite 有此字段',
  `invite_from` varchar(32) NOT NULL DEFAULT '' COMMENT '用户使用的 邀请码, 邀请码用户 invite 有此字段',
  `invite_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '被XX的邀请码邀请 uid, 邀请码用户 invite 有此字段',
  `main_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '绑定的主账号uid, 邀请码用户 invite 有此字段',
  `parent_num` int(11) NOT NULL DEFAULT '0' COMMENT '下属 子公司 数量, 企业 agent 有此字段',
  `sales_num` int(11) NOT NULL DEFAULT '0' COMMENT '下属 业务员 数量, 企业 agent 子公司 parent 有此字段',
  `invite_num` int(11) NOT NULL DEFAULT '0' COMMENT '下属 邀请码用户 数量, 企业 agent 子公司 parent 业务员 sales 有此字段 (邀请码用户 invite 也有此字段 为其推广的数量)',
  `invite_main_num` int(11) NOT NULL DEFAULT '0' COMMENT '下属 邀请码用户 并且绑定了主账号 数量, 企业 agent 子公司 parent 业务员 sales 有此字段 (邀请码用户 invite 也有此字段 为其推广的数量)',
  `cname_host` varchar(191) NOT NULL DEFAULT '' COMMENT '客户 切换域名 CNAME 域名 默认为空',
  `user_type` varchar(16) NOT NULL DEFAULT '' COMMENT '用户类型 UserTypeEnum',
  `user_slug` varchar(32) NOT NULL DEFAULT '' COMMENT '用户类型 标注',
  `register_from` varchar(32) NOT NULL DEFAULT '' COMMENT '用户注册 来源 只用于标注',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 参见 StateEnum 枚举',
  `admin_config` text NOT NULL COMMENT '后台用户 相关配置 json 文本存储 默认为空',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `ix_user_base_cellphone` (`cellphone`),
  UNIQUE KEY `ix_user_base_api_key` (`api_key`),
  UNIQUE KEY `ix_user_base_account` (`account`),
  KEY `ix_user_base_mobile_serial_number` (`mobile_serial_number`),
  KEY `ix_user_base_web_serial_number` (`web_serial_number`),
  KEY `ix_user_base_qr_code` (`qr_code`),
  KEY `ix_user_base_email` (`email`),
  KEY `ix_user_base_created_at` (`created_at`),
  KEY `ix_user_base_cellphone_area` (`cellphone_area`),
  KEY `ix_user_base_cellphone_num` (`cellphone_num`),
  KEY `ix_user_base_location` (`location`),
  KEY `ix_user_base_register_from` (`register_from`),
  KEY `ix_user_base_gender` (`gender`),
  KEY `ix_user_base_invite_num` (`invite_num`),
  KEY `ix_user_base_gender_last` (`gender_last`),
  KEY `ix_user_base_birthday` (`birthday`),
  KEY `ix_user_base_openid` (`openid`),
  KEY `ix_user_base_user_type` (`user_type`),
  KEY `ix_user_base_agent_id` (`agent_id`),
  KEY `ix_user_base_parent_id` (`parent_id`),
  KEY `ix_user_base_sales_id` (`sales_id`),
  KEY `ix_user_base_invite_code` (`invite_code`),
  KEY `ix_user_base_updated_at` (`updated_at`),
  KEY `ix_user_base_uid_hash` (`uid_hash`),
  KEY `ix_user_base_invite_by` (`invite_by`),
  KEY `ix_user_base_main_id` (`main_id`),
  KEY `ix_user_base_nick` (`nick`),
  KEY `ix_user_base_sales_num` (`sales_num`),
  KEY `ix_user_base_invite_main_num` (`invite_main_num`),
  KEY `ix_user_base_account_last` (`account_last`),
  KEY `ix_user_base_cname_host` (`cname_host`),
  KEY `ix_user_base_login_ip` (`login_ip`),
  KEY `ix_user_base_login_location` (`login_location`),
  KEY `ix_user_base_state` (`state`),
  KEY `ix_user_base_user_slug` (`user_slug`),
  KEY `ix_user_base_login_count` (`login_count`),
  KEY `ix_user_base_invite_from` (`invite_from`),
  KEY `ix_user_base_login_last` (`login_last`),
  KEY `ix_user_base_parent_num` (`parent_num`),
  KEY `ix_user_base_longitude` (`longitude`),
  KEY `ix_user_base_latitude` (`latitude`),
  KEY `ix_user_base_pc_serial_number` (`pc_serial_number`)
) ENGINE=InnoDB AUTO_INCREMENT=1700 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_contingency`
--

DROP TABLE IF EXISTS `user_contingency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_contingency` (
  `contingency_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `target_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应目标用户',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`contingency_id`),
  UNIQUE KEY `uid_target_uid_udx` (`uid`,`target_uid`),
  KEY `ix_user_contingency_target_uid` (`target_uid`),
  KEY `ix_user_contingency_created_at` (`created_at`),
  KEY `ix_user_contingency_updated_at` (`updated_at`),
  KEY `ix_user_contingency_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='应急联系人';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_device`
--

DROP TABLE IF EXISTS `user_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_device` (
  `device_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '对应当前用户',
  `serial_number` varchar(64) NOT NULL DEFAULT '' COMMENT '设备序列号',
  `device_name` varchar(64) NOT NULL DEFAULT '' COMMENT '设备默认名称',
  `alias_name` varchar(64) NOT NULL DEFAULT '' COMMENT '设备别名',
  `login_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上次登陆时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`device_id`),
  UNIQUE KEY `uid_serial_number_udx` (`uid`,`serial_number`),
  KEY `ix_user_device_uid` (`uid`),
  KEY `ix_user_device_created_at` (`created_at`),
  KEY `ix_user_device_alias_name` (`alias_name`),
  KEY `ix_user_device_updated_at` (`updated_at`),
  KEY `ix_user_device_serial_number` (`serial_number`),
  KEY `ix_user_device_login_last` (`login_last`),
  KEY `ix_user_device_device_name` (`device_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户设备';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_op_record`
--

DROP TABLE IF EXISTS `user_op_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_op_record` (
  `opid` bigint(20) NOT NULL AUTO_INCREMENT,
  `chat_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作相关  聊天  尽可能 尝试记录',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作相关  uid  尽可能 尝试记录',
  `op_type` varchar(16) NOT NULL DEFAULT '' COMMENT '操作类型   参见 OpTypeEnum 枚举',
  `op_desc` varchar(128) NOT NULL DEFAULT '' COMMENT '操作描述',
  `op_ref` varchar(255) NOT NULL DEFAULT '' COMMENT '操作 ref 引用',
  `op_url` varchar(255) NOT NULL DEFAULT '' COMMENT '操作 url',
  `op_args` text COMMENT '本次操作的 参数 json 字符串',
  `op_method` varchar(128) NOT NULL COMMENT '操作 执行的方法',
  `op_ip` varchar(20) NOT NULL COMMENT '操作者 ip',
  `op_location` varchar(32) NOT NULL COMMENT '操作者 地域',
  `op_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作者  uid',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`opid`),
  KEY `ix_user_op_record_op_method` (`op_method`),
  KEY `ix_user_op_record_op_location` (`op_location`),
  KEY `ix_user_op_record_op_uid` (`op_uid`),
  KEY `ix_user_op_record_chat_id` (`chat_id`),
  KEY `ix_user_op_record_created_at` (`created_at`),
  KEY `ix_user_op_record_updated_at` (`updated_at`),
  KEY `ix_user_op_record_uid` (`uid`),
  KEY `ix_user_op_record_op_type` (`op_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作记录';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-06-16 20:15:18
