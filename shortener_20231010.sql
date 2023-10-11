-- MySQL dump 10.13  Distrib 5.7.37, for Linux (x86_64)
--
-- Host: localhost    Database: shortener
-- ------------------------------------------------------
-- Server version	5.7.37-log

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
-- Table structure for table `tp_adsense`
--

DROP TABLE IF EXISTS `tp_adsense`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_adsense` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adsense_domain` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `adsense_txt` varchar(1000) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `adsense_code` text CHARACTER SET utf8 NOT NULL,
  `adsense_switch` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `note` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`itemid`),
  KEY `adsense_domain` (`adsense_domain`),
  KEY `adsense_switch` (`adsense_switch`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_api_token`
--

DROP TABLE IF EXISTS `tp_api_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_api_token` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `limit_time` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `token` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_black_url`
--

DROP TABLE IF EXISTS `tp_black_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_black_url` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pattern` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `black_url` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `price` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '9.9',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0',
  `notice` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `success` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `success` (`success`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_click_analysis`
--

DROP TABLE IF EXISTS `tp_click_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_click_analysis` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_time` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `all_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `pc_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `m_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `middle_page_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `short_url` int(10) unsigned NOT NULL DEFAULT '0',
  `ad_income` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `ad_views` int(10) unsigned NOT NULL DEFAULT '0',
  `ad_display` int(10) unsigned NOT NULL DEFAULT '0',
  `ad_hits` int(10) unsigned NOT NULL DEFAULT '0',
  `ad_cpc` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `ad_rpm` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `ad_ctr` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`itemid`),
  KEY `date_time` (`date_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_contact`
--

DROP TABLE IF EXISTS `tp_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_contact` (
  `itemid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `remote_ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `message` varchar(10000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_domain`
--

DROP TABLE IF EXISTS `tp_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_domain` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `http_prefix` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `domain_url` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `site_name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `index_title` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `index_keyword` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `index_description` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `baidu_tongji_id` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `google_tongji_id` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `display_ad` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_customize` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_checked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sitemap` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `template_num` int(3) unsigned NOT NULL DEFAULT '1',
  `roblox_index` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`itemid`),
  KEY `domain_url` (`domain_url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_http_referer`
--

DROP TABLE IF EXISTS `tp_http_referer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_http_referer` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `short_str` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `http_referer` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `user_agent` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `is_pc` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_spider` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user_language` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `remote_ip` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `short_str` (`short_str`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_order`
--

DROP TABLE IF EXISTS `tp_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `book_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `price` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `hash_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `payment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `payer_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0',
  `success` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `success` (`success`),
  KEY `hash_id` (`hash_id`),
  KEY `price` (`price`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_report_malicious_url`
--

DROP TABLE IF EXISTS `tp_report_malicious_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_report_malicious_url` (
  `itemid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `remote_ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment` varchar(10000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_shortener`
--

DROP TABLE IF EXISTS `tp_shortener`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_shortener` (
  `itemid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0',
  `access_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `short_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `short_url_7` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `short_url_8` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `url` varchar(10000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `short_from` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hits` bigint(20) unsigned NOT NULL DEFAULT '0',
  `remote_ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_access_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `middle_page` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_pc` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `accept_language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `allow_spider_jump` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_404` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `display_ad` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `redis_index` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `check_malicious_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `youtube_url_itemid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `short_url` (`short_url`),
  KEY `hits` (`hits`),
  KEY `check_malicious_status` (`check_malicious_status`),
  KEY `redis_index` (`redis_index`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_youtube_url`
--



DROP TABLE IF EXISTS `tp_youtube_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_youtube_url` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(2000) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `description` varchar(2000) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `keyword` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `youtube_url` varchar(2000) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `youtube_short_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `youtube_short_url` (`youtube_short_url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-10-10 21:00:07
