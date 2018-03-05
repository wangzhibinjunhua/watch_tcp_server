-- MySQL dump 10.13  Distrib 5.5.49, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: watch
-- ------------------------------------------------------
-- Server version	5.5.49-0ubuntu0.14.04.1

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
-- Table structure for table `watch_app_user`
--

DROP TABLE IF EXISTS `watch_app_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_app_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL COMMENT '密码',
  `name` varchar(32) NOT NULL COMMENT '姓名',
  `avatar` varchar(255) NOT NULL COMMENT '图像',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `nickname` varchar(45) NOT NULL COMMENT '昵称',
  `vkey` varchar(255) NOT NULL COMMENT '短信验证码',
  `mobile` varchar(45) NOT NULL COMMENT '手机号',
  `app_sn` varchar(255) NOT NULL COMMENT 'app 代码',
  `sex` int(2) NOT NULL COMMENT '性别',
  `birthday` date NOT NULL COMMENT '出生日期',
  `cer_number` varchar(45) NOT NULL COMMENT '身份证号码',
  `status` int(2) NOT NULL COMMENT '帐号状态：0注册中，1正常，2锁定',
  `hid` varchar(45) NOT NULL COMMENT 'uuid',
  `token` varchar(60) DEFAULT NULL COMMENT '登录身份令牌',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_app_watch`
--

DROP TABLE IF EXISTS `watch_app_watch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_app_watch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `watch_imei` varchar(16) NOT NULL,
  `app_id` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=469 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_info`
--

DROP TABLE IF EXISTS `watch_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_info` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `imei` varchar(15) NOT NULL,
  `gps_lon` varchar(255) NOT NULL COMMENT 'gps定位数据',
  `gps_lat` varchar(255) NOT NULL COMMENT 'gps定位数据',
  `unix_time` int(11) NOT NULL,
  `watch_time` datetime NOT NULL COMMENT '手表上报时间',
  `system_time` datetime NOT NULL COMMENT '服务器系统时间',
  `location_lon` varchar(255) NOT NULL COMMENT '基站wifi定位数据',
  `location_lat` varchar(255) NOT NULL COMMENT '基站wifi定位数据',
  `location_content` varchar(255) NOT NULL,
  `location_type` varchar(4) NOT NULL COMMENT '定位类型,0--gps,1--wifi,2--基站',
  `ud_content` varchar(4096) NOT NULL COMMENT '手表上报ud原始数据',
  `battery` varchar(5) NOT NULL DEFAULT '' COMMENT '电量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102149 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_message`
--

DROP TABLE IF EXISTS `watch_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_message` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `flag` varchar(2) NOT NULL DEFAULT '0' COMMENT '0未读,1已读',
  `type` varchar(2) NOT NULL DEFAULT '0' COMMENT '0为音频文件,1为图片文件',
  `imei` varchar(16) NOT NULL,
  `user_id` varchar(16) NOT NULL,
  `stamp` varchar(16) NOT NULL,
  `file` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4313 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_sms`
--

DROP TABLE IF EXISTS `watch_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_sms` (
  `sms_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL COMMENT '短信内容',
  `mobile` varchar(45) NOT NULL COMMENT '接收手机号',
  `status` int(11) NOT NULL COMMENT '验证状态:0 未验证 1:验证完毕',
  `v_code` varchar(45) NOT NULL COMMENT '短信验证码',
  `type` int(11) NOT NULL COMMENT '短信类型:1 帐号注册验证码;2:密码找回验证码 3:其他',
  `unix_time` int(11) NOT NULL,
  `app_sn` varchar(255) NOT NULL COMMENT 'app类型:1:儿童手表;2:血压手表',
  `send_time` datetime NOT NULL,
  `send_status` int(11) NOT NULL COMMENT '发送状态 0:失败 1:成功',
  PRIMARY KEY (`sms_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-05 16:02:42
