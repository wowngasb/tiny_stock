/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : tiny_stock

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-09-05 00:28:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for stock_base
-- ----------------------------
DROP TABLE IF EXISTS `stock_base`;
CREATE TABLE `stock_base` (
  `id` bigint(20) unsigned NOT NULL COMMENT '主键 格式为 股票code 加 上日期',
  `lday` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日期 整型',
  `pchg` float NOT NULL DEFAULT '0' COMMENT '涨跌幅度 百分比',
  `yclose` int(10) NOT NULL DEFAULT '0' COMMENT '前收盘价',
  `open` int(10) NOT NULL DEFAULT '0' COMMENT '开盘价',
  `high` int(10) NOT NULL DEFAULT '0' COMMENT '最高',
  `low` int(10) NOT NULL DEFAULT '0' COMMENT '最低',
  `close` int(10) NOT NULL DEFAULT '0' COMMENT '收盘价',
  `vol` int(10) NOT NULL DEFAULT '0' COMMENT '成交量',
  `unused` int(10) NOT NULL DEFAULT '0' COMMENT 'unused',
  `amount` float NOT NULL DEFAULT '0' COMMENT '成交金额',
  `stock_code` varchar(16) NOT NULL DEFAULT '' COMMENT '股票代码',
  `minline_data` text NOT NULL COMMENT '每分钟数据 base64 gzencode 压缩',
  `has_minline` tinyint(4) NOT NULL DEFAULT '0' COMMENT '每分钟数据 是否存在',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  PRIMARY KEY (`id`),
  KEY `ix_code` (`stock_code`),
  KEY `ix_lday` (`lday`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='股票行情信息';
