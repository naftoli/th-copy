-- MySQL dump 10.16  Distrib 10.1.34-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: pointsDB
-- ------------------------------------------------------
-- Server version	10.1.34-MariaDB

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
-- Table structure for table `achievement_cards`
--

DROP TABLE IF EXISTS `achievement_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievement_cards` (
  `achievement_card_id` int(11) NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) unsigned DEFAULT '0',
  `campaign_id` int(11) unsigned DEFAULT '0',
  `mission_id` int(11) unsigned DEFAULT '0',
  `task_id` int(11) unsigned DEFAULT '0',
  `class_id` int(10) unsigned DEFAULT '0',
  `card_serial` char(20) DEFAULT NULL,
  `extra_serial` int(11) DEFAULT NULL,
  `card_points` int(5) unsigned DEFAULT '0',
  `card_type` enum('Institution Administrator','Teacher','MissionsApp') DEFAULT NULL,
  `campaign_image_id` varchar(30) DEFAULT NULL,
  `achievement` varchar(255) DEFAULT '',
  `status` enum('scanned','not scanned','weblink') DEFAULT 'not scanned',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`achievement_card_id`),
  KEY `card_serial` (`card_serial`),
  KEY `institution_id` (`institution_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4125331 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_credits`
--

DROP TABLE IF EXISTS `admin_credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_credits` (
  `admin_credit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `credit_title` varchar(45) NOT NULL DEFAULT '',
  `credit_amount` int(11) NOT NULL,
  `credit_description` varchar(255) DEFAULT NULL,
  `start_epoch` int(11) DEFAULT NULL,
  `end_epoch` int(11) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`admin_credit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1097 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcement_classes`
--

DROP TABLE IF EXISTS `announcement_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_classes` (
  `announcement_class_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `announcement_id` int(11) unsigned NOT NULL,
  `class_id` int(11) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`announcement_class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcement_institutions`
--

DROP TABLE IF EXISTS `announcement_institutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_institutions` (
  `announcement_institution_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `announcement_id` int(11) unsigned NOT NULL,
  `status` enum('Requested','Approved','Denied') DEFAULT NULL,
  `institution_id` int(11) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`announcement_institution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcement_students`
--

DROP TABLE IF EXISTS `announcement_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_students` (
  `announcement_student_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `announcement_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`announcement_student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=366 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `announcement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_by` int(11) unsigned NOT NULL,
  `class_id` int(11) unsigned DEFAULT NULL,
  `institution_id` int(11) unsigned NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `status` enum('Saved','Publish Request','Published','Denied Request','Deleted') DEFAULT 'Saved',
  `reason` varchar(500) DEFAULT NULL,
  `headline` varchar(75) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime NOT NULL,
  PRIMARY KEY (`announcement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_keyword_types`
--

DROP TABLE IF EXISTS `app_keyword_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_keyword_types` (
  `app_keyword_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `keyword_type` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`app_keyword_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_keywords`
--

DROP TABLE IF EXISTS `app_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_keywords` (
  `app_keyword_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `primary_app_keyword_id` int(11) unsigned DEFAULT NULL,
  `app_keyword_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'School',
  `language_id` int(11) unsigned DEFAULT NULL,
  `app_name` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `institution_id` int(11) unsigned DEFAULT NULL,
  `content` varchar(50) CHARACTER SET utf8 DEFAULT '',
  `created_by` int(11) unsigned DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`app_keyword_id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_language_pref`
--

DROP TABLE IF EXISTS `app_language_pref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_language_pref` (
  `app_language_pref_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `section` varchar(30) NOT NULL DEFAULT '',
  `language_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`app_language_pref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_text`
--

DROP TABLE IF EXISTS `app_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_text` (
  `app_text_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `primary_app_text_id` int(11) unsigned NOT NULL DEFAULT '0',
  `institution_id` int(11) unsigned DEFAULT NULL,
  `language_id` int(11) unsigned DEFAULT NULL,
  `app_name` varchar(60) DEFAULT NULL,
  `permission` varchar(40) DEFAULT NULL,
  `priority` int(11) unsigned NOT NULL DEFAULT '0',
  `controller` varchar(30) DEFAULT NULL,
  `action` varchar(30) DEFAULT NULL,
  `content` varchar(300) DEFAULT '',
  `resource_id` int(11) DEFAULT NULL,
  `resource_name` varchar(30) DEFAULT NULL,
  `order_found` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`app_text_id`),
  KEY `content` (`content`(255)),
  KEY `controller` (`controller`),
  KEY `priority` (`priority`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=627732 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_text_languages`
--

DROP TABLE IF EXISTS `app_text_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_text_languages` (
  `app_text_language_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_text_language` varchar(60) NOT NULL DEFAULT '',
  `hierarchy` int(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`app_text_language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_card_orders`
--

DROP TABLE IF EXISTS `auth_card_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_card_orders` (
  `auth_card_order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date_completed` int(11) unsigned DEFAULT NULL,
  `institution_id` int(11) DEFAULT NULL,
  `user_ids` text NOT NULL,
  `confirmation_code` char(16) DEFAULT NULL,
  `creditcard_first_name` varchar(60) DEFAULT '',
  `creditcard_last_name` varchar(60) DEFAULT '',
  `creditcard_name` varchar(20) DEFAULT '',
  `creditcard_number` varchar(4) DEFAULT '',
  `creditcard_ccv` varchar(11) DEFAULT NULL,
  `creditcard_expiration_month` char(3) DEFAULT '',
  `creditcard_expiration_year` int(4) unsigned DEFAULT NULL,
  `shipping_address` varchar(400) DEFAULT NULL,
  `shipping_city` varchar(30) DEFAULT NULL,
  `shipping_state` varchar(30) DEFAULT NULL,
  `shipping_postal` varchar(30) DEFAULT NULL,
  `shipping_country` varchar(30) DEFAULT NULL,
  `order_processed_date` int(11) unsigned DEFAULT NULL,
  `price_per_unit` varchar(11) DEFAULT NULL,
  `quantity_purchased` int(11) unsigned DEFAULT NULL,
  `sub_total` decimal(6,2) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) unsigned NOT NULL,
  PRIMARY KEY (`auth_card_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_cards`
--

DROP TABLE IF EXISTS `auth_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_cards` (
  `auth_card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `rank_id` int(10) unsigned DEFAULT NULL,
  `institution_id` int(10) unsigned NOT NULL,
  `card_expires` int(10) unsigned DEFAULT NULL,
  `date_printed` int(11) unsigned DEFAULT NULL,
  `host_printed` int(11) DEFAULT NULL,
  `date_card_ordered` int(11) DEFAULT NULL,
  `auth_card_order_id` int(11) unsigned DEFAULT NULL,
  `date_card_redeemed` int(11) unsigned DEFAULT NULL,
  `card_status` enum('not printed','printed','ordered','host printed','redeemed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'not printed',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  `exp` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`auth_card_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9055 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `book_lines`
--

DROP TABLE IF EXISTS `book_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_lines` (
  `book_line_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` int(10) unsigned NOT NULL,
  `line_hierarchy` int(10) unsigned NOT NULL,
  `line_data` text NOT NULL,
  `line_number` varchar(10) DEFAULT NULL,
  `paragraphs` varchar(40) DEFAULT NULL,
  `pages` varchar(40) DEFAULT NULL,
  `chapters` varchar(40) DEFAULT NULL,
  `volumes` varchar(40) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`book_line_id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12278 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `books` (
  `book_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `book_name` varchar(40) NOT NULL,
  `line_numbers_enabled` tinyint(1) DEFAULT '1',
  `paragraphs_enabled` tinyint(1) DEFAULT '1',
  `pages_enabled` tinyint(1) DEFAULT '1',
  `chapters_enabled` tinyint(1) DEFAULT '1',
  `volumes_enabled` tinyint(1) DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`book_id`),
  KEY `institution_id` (`institution_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cached_data`
--

DROP TABLE IF EXISTS `cached_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cached_data` (
  `cached_data_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `institution_id` int(11) DEFAULT NULL,
  `label` enum('missionsapp marked','missionsapp medals earned') DEFAULT NULL,
  `value` int(9) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`cached_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `campaign_school_types`
--

DROP TABLE IF EXISTS `campaign_school_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_school_types` (
  `campaign_school_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `school_type` varchar(40) DEFAULT '',
  PRIMARY KEY (`campaign_school_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `campaign_types`
--

DROP TABLE IF EXISTS `campaign_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_types` (
  `campaign_type` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns` (
  `campaign_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `installed_campaign_id` int(10) unsigned DEFAULT '0',
  `default_installed` int(1) unsigned DEFAULT '0',
  `institution_id` int(10) unsigned DEFAULT NULL,
  `network_id` int(10) unsigned DEFAULT NULL,
  `campaign_name` varchar(18) NOT NULL DEFAULT '',
  `image_largemed` varchar(144) DEFAULT NULL,
  `image_smallmed` varchar(144) DEFAULT NULL,
  `image_achievement` varchar(144) DEFAULT NULL,
  `description` varchar(400) DEFAULT '',
  `commitments` varchar(400) DEFAULT '',
  `slogan` text,
  `campaign_type` varchar(22) DEFAULT NULL,
  `image_id` varchar(66) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `ladder` int(3) unsigned DEFAULT NULL,
  `points` tinyint(1) DEFAULT '1',
  `medals` tinyint(1) DEFAULT '1',
  `ranks` tinyint(1) DEFAULT '1',
  `is_editable` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`),
  KEY `institution_id` (`institution_id`),
  KEY `network_id` (`network_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3836 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ckids_mission_app`
--

DROP TABLE IF EXISTS `ckids_mission_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ckids_mission_app` (
  `task_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network_id` int(4) unsigned DEFAULT NULL,
  `holiday_name` varchar(255) DEFAULT NULL,
  `code` char(2) DEFAULT NULL,
  `description` tinytext,
  `access_level` int(1) unsigned DEFAULT NULL,
  `badge` varchar(255) DEFAULT NULL,
  `date_label` varchar(255) DEFAULT NULL,
  `start_date` int(11) unsigned DEFAULT NULL,
  `end_date` int(11) unsigned DEFAULT NULL,
  `activation_date` int(11) unsigned DEFAULT '0',
  `expiration_date` int(11) unsigned DEFAULT '0',
  `image_id` varchar(30) DEFAULT NULL,
  `pic_source` varchar(255) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`task_id`),
  KEY `network_id` (`network_id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ckids_mission_cards`
--

DROP TABLE IF EXISTS `ckids_mission_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ckids_mission_cards` (
  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_status` enum('not ordered','ordered','printed','sent','received') DEFAULT NULL,
  `printed` int(11) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1075 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ckids_mission_marking`
--

DROP TABLE IF EXISTS `ckids_mission_marking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ckids_mission_marking` (
  `marking_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network_id` int(10) unsigned DEFAULT '1',
  `task_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `modifed` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`marking_id`),
  KEY `network_id` (`network_id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10021 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ckids_mission_networks`
--

DROP TABLE IF EXISTS `ckids_mission_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ckids_mission_networks` (
  `network_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network_name` varchar(60) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`network_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) DEFAULT '',
  `institution_id` int(10) unsigned NOT NULL,
  `class_hierarchy` int(5) unsigned DEFAULT NULL,
  `grade` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `sub` varchar(45) DEFAULT NULL,
  `grade_id` int(11) unsigned DEFAULT NULL,
  `gender` enum('boys','girls','mixed') DEFAULT 'mixed',
  `is_active` tinyint(1) DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`class_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_settings`
--

DROP TABLE IF EXISTS `config_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_settings` (
  `config_setting_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) DEFAULT NULL,
  `class_id` int(10) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `set` varchar(80) DEFAULT NULL,
  `key` varchar(80) NOT NULL DEFAULT '',
  `val` varchar(10000) NOT NULL DEFAULT '0',
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`config_setting_id`),
  KEY `config_key` (`key`),
  KEY `set` (`set`)
) ENGINE=InnoDB AUTO_INCREMENT=1512 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_store`
--

DROP TABLE IF EXISTS `config_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_store` (
  `config_store_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `army_points` tinyint(1) DEFAULT '1',
  `base_points` tinyint(1) DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`config_store_id`),
  KEY `institution_id` (`institution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataset_parshos`
--

DROP TABLE IF EXISTS `dataset_parshos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dataset_parshos` (
  `dataset_parshos_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` char(4) DEFAULT NULL,
  PRIMARY KEY (`dataset_parshos_id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `error_reports`
--

DROP TABLE IF EXISTS `error_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `error_reports` (
  `error_report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `institution_id` int(11) unsigned DEFAULT NULL,
  `permission_id` int(11) unsigned DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  `code` varchar(28) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `message` text,
  `other` varchar(2000) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`error_report_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grades` (
  `grade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned DEFAULT NULL,
  `grade_name` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `grade_hierarchy` int(5) unsigned DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`grade_id`),
  KEY `institution_id` (`institution_id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_class_points`
--

DROP TABLE IF EXISTS `group_class_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_class_points` (
  `group_class_points_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `points` decimal(10,2) NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_class_points_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_classes`
--

DROP TABLE IF EXISTS `group_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_classes` (
  `group_classes_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_classes_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `group_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images` (
  `image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_category_id` int(11) unsigned NOT NULL,
  `photo` longblob NOT NULL,
  `photo_type` varchar(11) DEFAULT NULL,
  `image_name` varchar(11) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `imgs`
--

DROP TABLE IF EXISTS `imgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imgs` (
  `img_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `img_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `img_type` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `img_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(10) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`img_id`),
  KEY `permission_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22136 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions`
--

DROP TABLE IF EXISTS `institutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutions` (
  `institution_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_type` varchar(7) DEFAULT NULL COMMENT 'depreciated',
  `is_active` tinyint(1) unsigned DEFAULT '1',
  `reg_expires` int(10) unsigned DEFAULT '0',
  `name` varchar(60) NOT NULL DEFAULT '',
  `template_style` varchar(30) DEFAULT NULL,
  `network_id` int(10) unsigned NOT NULL,
  `host_id` int(10) unsigned NOT NULL,
  `host_info_id` int(11) DEFAULT NULL,
  `hebrew_name` varchar(40) DEFAULT '',
  `address` varchar(400) DEFAULT '',
  `city` varchar(30) DEFAULT NULL,
  `state` varchar(30) DEFAULT NULL,
  `country` varchar(30) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `postal` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `image_id` varchar(30) DEFAULT '0',
  `light_image_id` varchar(30) DEFAULT '0',
  `custom_fields` varchar(10000) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`institution_id`),
  KEY `host_id` (`host_id`),
  KEY `network_id` (`network_id`),
  KEY `created_by` (`created_by`),
  KEY `template_style` (`template_style`)
) ENGINE=InnoDB AUTO_INCREMENT=627 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `legacy_lookup`
--

DROP TABLE IF EXISTS `legacy_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legacy_lookup` (
  `legacy_lookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `legacy_id` int(11) DEFAULT NULL,
  `ims_id` int(11) DEFAULT NULL,
  `legacy_table` varchar(45) DEFAULT NULL,
  `ims_table` varchar(45) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`legacy_lookup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28155 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medals`
--

DROP TABLE IF EXISTS `medals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medals` (
  `medal_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `medal_hierarchy` int(10) unsigned NOT NULL,
  `medal_name` varchar(32) NOT NULL,
  `medal_value` int(10) unsigned NOT NULL,
  `medal_image_id` varchar(30) DEFAULT NULL,
  `medal_image_id_2` varchar(30) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`medal_id`),
  KEY `institution_id` (`institution_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missions` (
  `mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `installed_mission_id` int(10) unsigned DEFAULT NULL,
  `mission_name` varchar(50) NOT NULL DEFAULT '',
  `mission_type` varchar(255) DEFAULT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `book_id` int(10) unsigned DEFAULT NULL,
  `book_measurement` varchar(45) DEFAULT NULL,
  `institution_id` int(10) unsigned NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `points_up` int(11) DEFAULT NULL,
  `medal_up` varchar(255) DEFAULT NULL,
  `rank_up` varchar(255) DEFAULT NULL,
  `sequence` int(11) DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `percentage_required` int(11) DEFAULT '100',
  `default_velocity` decimal(10,2) DEFAULT '1.00',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mission_id`),
  KEY `mission_type` (`mission_type`),
  KEY `campaign_id` (`campaign_id`),
  KEY `institution_id` (`institution_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1963 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `network_alerts`
--

DROP TABLE IF EXISTS `network_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `network_alerts` (
  `network_alert_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network_id` int(11) unsigned DEFAULT NULL,
  `alert_location` enum('ID Cards','User Registration','Institution Registration') DEFAULT NULL,
  `alert_email` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`network_alert_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `networks`
--

DROP TABLE IF EXISTS `networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `networks` (
  `network_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) unsigned DEFAULT NULL,
  `network_name` varchar(99) DEFAULT NULL,
  `network_keyword` varchar(99) DEFAULT NULL,
  `network_email` varchar(255) DEFAULT NULL,
  `admin_user_id` int(11) unsigned DEFAULT NULL,
  `network_terminology` varchar(55) DEFAULT NULL,
  `image_id` varchar(55) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`network_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `institution_id` int(10) unsigned DEFAULT NULL,
  `prize_id` int(10) unsigned DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `item_id_ref` enum('prizes','packages') NOT NULL,
  `description` varchar(400) DEFAULT '',
  `currency` enum('CAD','USD') DEFAULT 'CAD',
  `total_price` decimal(15,2) DEFAULT NULL,
  `serial` varchar(45) DEFAULT NULL,
  `status` enum('Pending','Redeemed') NOT NULL DEFAULT 'Pending',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2429 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parshos`
--

DROP TABLE IF EXISTS `parshos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parshos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` char(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_processes`
--

DROP TABLE IF EXISTS `payment_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_processes` (
  `payment_process_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `institution_id` int(11) DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `response` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`payment_process_id`)
) ENGINE=InnoDB AUTO_INCREMENT=508 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_types`
--

DROP TABLE IF EXISTS `permission_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_types` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permission_type` varchar(255) NOT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_style` varchar(30) DEFAULT '',
  `registration_expiration` int(11) DEFAULT '0',
  `registration_date` int(11) unsigned DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `institution_id` int(10) unsigned NOT NULL,
  `permission` varchar(255) NOT NULL,
  `auth_hash` varchar(32) DEFAULT NULL,
  `default_permission` tinyint(1) unsigned DEFAULT '1',
  `registration_location` varchar(64) DEFAULT NULL,
  `email_notification` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  KEY `user_id` (`user_id`),
  KEY `institution_id` (`institution_id`),
  KEY `permission` (`permission`),
  KEY `created_by` (`created_by`),
  KEY `registration_expiration` (`registration_expiration`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prize_classes`
--

DROP TABLE IF EXISTS `prize_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prize_classes` (
  `prize_class_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prize_id` int(11) unsigned DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`prize_class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19555 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prize_school_types`
--

DROP TABLE IF EXISTS `prize_school_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prize_school_types` (
  `prize_school_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prize_id` int(11) unsigned DEFAULT NULL,
  `school_type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`prize_school_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prize_sizes`
--

DROP TABLE IF EXISTS `prize_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prize_sizes` (
  `prize_size_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prize_id` int(10) unsigned NOT NULL,
  `prize_size_hierarchy` int(8) unsigned NOT NULL,
  `prize_size` varchar(40) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`prize_size_id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prizes`
--

DROP TABLE IF EXISTS `prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prizes` (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_prize_id` int(10) unsigned DEFAULT '0',
  `parent_prize_id` int(10) unsigned DEFAULT '0',
  `legacy_add_on_id` int(11) unsigned DEFAULT NULL,
  `teacher_id` int(11) unsigned DEFAULT '0',
  `guardian_id` int(11) unsigned DEFAULT '0',
  `network_id` int(10) unsigned DEFAULT '0',
  `institution_id` int(10) unsigned DEFAULT NULL,
  `prize_name` varchar(80) NOT NULL DEFAULT '',
  `points` int(6) DEFAULT NULL,
  `prize_category` varchar(40) DEFAULT 'General Prize',
  `bar_code` varchar(40) DEFAULT '',
  `prize_description` varchar(2000) DEFAULT '',
  `image_id` varchar(20) DEFAULT '0',
  `add_on_restricted` tinyint(1) unsigned DEFAULT '0',
  `use_sub_prizes` tinyint(1) unsigned DEFAULT '0',
  `one_per_user` tinyint(1) unsigned DEFAULT '0',
  `prize_count` int(11) unsigned DEFAULT '0',
  `prize_type` enum('Template','School Installed','Installable') DEFAULT 'Template',
  `installable_default_on` tinyint(1) unsigned DEFAULT '0',
  `prize_price` decimal(15,2) unsigned DEFAULT NULL,
  `prize_discounted_price` decimal(15,2) unsigned DEFAULT NULL,
  `is_active` tinyint(1) unsigned DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`prize_id`),
  KEY `institution_id` (`institution_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=164747 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchase_details`
--

DROP TABLE IF EXISTS `purchase_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_details` (
  `purchase_detail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) DEFAULT NULL,
  `pack_item_id` int(11) DEFAULT NULL,
  `pack_item_type` enum('item','add-on','') DEFAULT NULL,
  `item_description` varchar(255) DEFAULT NULL,
  `item_name` varchar(128) DEFAULT NULL,
  `pack_item_price` float DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`purchase_detail_id`),
  KEY `purchase_id` (`purchase_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `purchase_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `institution_id` int(11) DEFAULT NULL,
  `payment_status` enum('Pending','Completed','Refused') DEFAULT 'Pending',
  `price` float NOT NULL,
  `credit` float DEFAULT NULL,
  `currency` enum('US','CDN') DEFAULT 'US',
  `is_active` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`purchase_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ranks`
--

DROP TABLE IF EXISTS `ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranks` (
  `rank_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) unsigned NOT NULL,
  `rank_title` varchar(80) NOT NULL DEFAULT '',
  `rank_points` int(11) unsigned NOT NULL,
  `rank_image` varchar(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modifed` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`rank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registration_orders`
--

DROP TABLE IF EXISTS `registration_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_orders` (
  `registration_orders_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_confirmation_code` varchar(16) NOT NULL DEFAULT '',
  `api_confirmation_code` varchar(30) DEFAULT '',
  `institution_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `template_style` varchar(30) NOT NULL,
  `administrator_first_name` varchar(30) DEFAULT NULL,
  `administrator_last_name` varchar(30) DEFAULT NULL,
  `administrator_email` varchar(100) DEFAULT NULL,
  `administrator_phone_number` varchar(30) DEFAULT NULL,
  `administrator_cell_phone` varchar(45) DEFAULT NULL,
  `administrator_address` varchar(45) DEFAULT NULL,
  `administrator_city` varchar(25) DEFAULT NULL,
  `administrator_postal` varchar(20) DEFAULT NULL,
  `administrator_state` varchar(25) DEFAULT NULL,
  `administrator_country` varchar(20) DEFAULT NULL,
  `institution_name` varchar(30) DEFAULT NULL,
  `institution_type` varchar(25) DEFAULT NULL,
  `institution_address` varchar(60) DEFAULT NULL,
  `institution_city` varchar(30) DEFAULT NULL,
  `institution_state` varchar(20) DEFAULT NULL,
  `institution_postal` varchar(20) DEFAULT NULL,
  `institution_country` varchar(20) DEFAULT NULL,
  `institution_phone` varchar(30) DEFAULT NULL,
  `institution_email` varchar(100) DEFAULT NULL,
  `institution_website` varchar(255) DEFAULT NULL,
  `kioskaccessories_regular` int(4) unsigned DEFAULT NULL,
  `kioskaccessories_campers` int(11) unsigned DEFAULT NULL,
  `kioskaccessories_sponsored` int(4) unsigned DEFAULT NULL,
  `kioskaccessories_rental` int(4) unsigned DEFAULT NULL,
  `kioskaccessories_scanner` int(6) unsigned DEFAULT NULL,
  `kioskaccessories_handbook` int(6) unsigned DEFAULT NULL,
  `billing_first_name` varchar(30) DEFAULT NULL,
  `billing_last_name` varchar(30) DEFAULT NULL,
  `billing_phone_number` varchar(30) DEFAULT NULL,
  `billing_address` varchar(60) DEFAULT NULL,
  `billing_city` varchar(30) DEFAULT NULL,
  `billing_postal` varchar(20) DEFAULT NULL,
  `billing_state` varchar(20) DEFAULT NULL,
  `billing_country` varchar(20) DEFAULT NULL,
  `shipping_first_name` varchar(30) DEFAULT NULL,
  `shipping_last_name` varchar(30) DEFAULT NULL,
  `shipping_phone_number` varchar(30) DEFAULT NULL,
  `shipping_address` varchar(60) DEFAULT NULL,
  `shipping_city` varchar(30) DEFAULT NULL,
  `shipping_postal` varchar(20) DEFAULT NULL,
  `shipping_state` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(20) DEFAULT NULL,
  `creditcard_name` varchar(45) DEFAULT NULL,
  `creditcard_number` varchar(4) DEFAULT NULL,
  `creditcard_expiration_month` varchar(2) DEFAULT NULL,
  `creditcard_expiration_year` varchar(4) DEFAULT NULL,
  `creditcard_ccv` varchar(8) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`registration_orders_id`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `relationships`
--

DROP TABLE IF EXISTS `relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relationships` (
  `relationship_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `relation_id` int(10) unsigned NOT NULL,
  `relationship` enum('Parent') COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`relationship_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3079 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rules`
--

DROP TABLE IF EXISTS `rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rules` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_type` enum('Allow','Deny') NOT NULL DEFAULT 'Deny',
  `rule_applies_to` varchar(255) NOT NULL,
  `rule` varchar(400) NOT NULL DEFAULT '',
  `user_id` int(11) DEFAULT NULL,
  `institution_id` int(11) NOT NULL,
  `campaign_id` int(10) DEFAULT NULL,
  `prize_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `institution_id` (`institution_id`),
  KEY `prize_id` (`prize_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=9238 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedules` (
  `scheduler_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mission_id` int(10) unsigned DEFAULT NULL,
  `task_id` int(10) unsigned DEFAULT NULL,
  `years` varchar(255) DEFAULT NULL,
  `weeks_in_year` varchar(255) DEFAULT NULL,
  `days_in_year` varchar(255) DEFAULT NULL,
  `months` varchar(255) DEFAULT NULL,
  `weeks_in_month` varchar(255) DEFAULT NULL,
  `days_in_month` varchar(255) DEFAULT NULL,
  `days_of_week` varchar(255) DEFAULT NULL,
  `start_time` varchar(5) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`scheduler_id`),
  KEY `task_id` (`task_id`),
  KEY `mission_id` (`mission_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduling_params`
--

DROP TABLE IF EXISTS `scheduling_params`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scheduling_params` (
  `scheduler_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mission_id` int(10) unsigned DEFAULT NULL,
  `task_id` int(10) unsigned DEFAULT NULL,
  `years` varchar(255) DEFAULT NULL,
  `weeks_in_year` varchar(255) DEFAULT NULL,
  `days_in_year` varchar(255) DEFAULT NULL,
  `months` varchar(255) DEFAULT NULL,
  `weeks_in_month` varchar(255) DEFAULT NULL,
  `days_in_month` varchar(255) DEFAULT NULL,
  `days_of_week` varchar(255) DEFAULT NULL,
  `frequency` enum('Yearly','Monthly','Weekly','Daily') DEFAULT NULL,
  `start_time` varchar(5) DEFAULT NULL,
  `expiration` int(8) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`scheduler_id`),
  KEY `task_id` (`task_id`),
  KEY `mission_id` (`mission_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scratch_cards`
--

DROP TABLE IF EXISTS `scratch_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scratch_cards` (
  `scratch_card_id` int(11) NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) unsigned DEFAULT '0',
  `card_serial` int(5) unsigned DEFAULT NULL,
  `card_control` int(6) unsigned DEFAULT NULL,
  `card_points` int(5) unsigned DEFAULT '0',
  `user_point_id` int(11) DEFAULT NULL,
  `status` enum('scanned','not scanned') DEFAULT 'not scanned',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`scratch_card_id`)
) ENGINE=InnoDB AUTO_INCREMENT=200104 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `slow_queries`
--

DROP TABLE IF EXISTS `slow_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slow_queries` (
  `slow_query_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `seconds` int(4) unsigned DEFAULT NULL,
  `data` text,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`slow_query_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17398 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_purchases`
--

DROP TABLE IF EXISTS `student_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_purchases` (
  `student_purchases_id` int(11) NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) unsigned DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `package_item_id` int(11) NOT NULL,
  `package_item_name` varchar(45) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`student_purchases_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_codes`
--

DROP TABLE IF EXISTS `task_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_codes` (
  `task_code_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL,
  `bar_code` char(16) DEFAULT NULL,
  `short_code` char(6) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`task_code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `installed_task_id` int(10) unsigned DEFAULT '0',
  `school_type` varchar(40) DEFAULT NULL,
  `task_name` varchar(60) NOT NULL DEFAULT '',
  `mission_id` int(10) unsigned DEFAULT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned DEFAULT '0',
  `institution_id` int(10) unsigned NOT NULL,
  `points` int(7) unsigned DEFAULT '0',
  `min_points` int(7) unsigned DEFAULT NULL,
  `max_points` int(7) DEFAULT NULL,
  `frequency` enum('One-Time','Recurring') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `sequence` int(11) unsigned DEFAULT '1',
  `velocity` decimal(10,2) DEFAULT NULL,
  `is_checkbox` tinyint(1) unsigned DEFAULT '0',
  `is_locked` tinyint(1) unsigned DEFAULT '0',
  `is_grid` tinyint(1) unsigned DEFAULT '1',
  `is_card` tinyint(1) unsigned DEFAULT '1',
  `is_required` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`task_id`),
  KEY `mission_id` (`mission_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `institution_id` (`institution_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=13068 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_scale`
--

DROP TABLE IF EXISTS `tasks_scale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks_scale` (
  `tasks_scale_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `grade` varchar(55) NOT NULL,
  `ladder` int(11) NOT NULL,
  `comment` varchar(400) DEFAULT '',
  `mission_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `institution_id` int(10) unsigned NOT NULL,
  `is_required` tinyint(1) DEFAULT '1',
  `percentage` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tasks_scale_id`),
  KEY `task_id` (`task_id`),
  KEY `mission_id` (`mission_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `institution_id` (`institution_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1525 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_missions_statuses`
--

DROP TABLE IF EXISTS `temp_missions_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_missions_statuses` (
  `phase` int(11) DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL,
  `institution_id` int(11) DEFAULT NULL,
  `missions` int(11) DEFAULT NULL,
  `status_msg` varchar(244) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_addons`
--

DROP TABLE IF EXISTS `user_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_addons` (
  `user_addon_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `prize_id` int(11) unsigned DEFAULT NULL,
  `expires` int(11) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_addon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_campaign_logs`
--

DROP TABLE IF EXISTS `user_campaign_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_campaign_logs` (
  `user_campaign_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `campaign_goal` decimal(8,2) unsigned DEFAULT NULL,
  `log_date` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_campaign_log_id`),
  KEY `institution_id` (`institution_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2175 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_campaign_progress`
--

DROP TABLE IF EXISTS `user_campaign_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_campaign_progress` (
  `user_campaign_progress_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `current_line` int(11) unsigned NOT NULL,
  `campaign_goal` int(10) unsigned DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_campaign_progress_id`),
  KEY `institution_id` (`institution_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2268 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_campaigns`
--

DROP TABLE IF EXISTS `user_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_campaigns` (
  `user_campaign_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `institution_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `mission_id` int(10) unsigned DEFAULT NULL,
  `mission_increment` int(11) unsigned DEFAULT NULL,
  `task_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `book_id` int(10) unsigned DEFAULT NULL,
  `task_increment` decimal(10,2) unsigned DEFAULT NULL,
  `status` enum('In Progress','Completed','Paused','Resumed','Enrollment','Unenrollment') DEFAULT NULL,
  `line_offset` int(6) unsigned DEFAULT NULL,
  `ladder` int(3) unsigned DEFAULT NULL,
  `ladder_velocity` decimal(10,2) unsigned DEFAULT NULL,
  `grade_hierarchy` int(3) unsigned DEFAULT NULL,
  `grade_velocity` int(10) unsigned DEFAULT NULL,
  `schedule_date` int(11) unsigned DEFAULT NULL,
  `input_value` varchar(40) DEFAULT '',
  `points_given` int(11) DEFAULT NULL COMMENT 'dont unsign',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_campaign_id`),
  KEY `user_id` (`user_id`),
  KEY `institution_id` (`institution_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `mission_id` (`mission_id`),
  KEY `task_id` (`task_id`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=573157 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_classes`
--

DROP TABLE IF EXISTS `user_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_classes` (
  `user_class_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `class_role` enum('Student','Teacher') DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_class_id`),
  KEY `class_id` (`class_id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_orders`
--

DROP TABLE IF EXISTS `user_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_orders` (
  `user_order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `confirmation_code` char(20) DEFAULT NULL,
  `api_confirmation_code` varchar(80) DEFAULT NULL,
  `user_registrations_list` text,
  `user_addons_list` text,
  `creditcard_first_name` varchar(11) DEFAULT NULL,
  `creditcard_last_name` varchar(11) DEFAULT NULL,
  `creditcard_number` int(4) unsigned DEFAULT NULL,
  `creditcard_ccv` varchar(11) DEFAULT NULL,
  `creditcard_name` varchar(11) DEFAULT NULL,
  `creditcard_expiration_month` varchar(11) DEFAULT NULL,
  `creditcard_expiration_year` varchar(11) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_points`
--

DROP TABLE IF EXISTS `user_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_points` (
  `user_point_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reversed_user_point_id` int(11) unsigned DEFAULT NULL,
  `prize_id` int(10) unsigned DEFAULT '0',
  `user_prize_id` int(10) unsigned DEFAULT NULL,
  `scratch_card_id` int(11) DEFAULT NULL,
  `achievement_card_id` int(10) unsigned DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `mission_id` int(10) unsigned DEFAULT NULL,
  `task_id` int(10) unsigned DEFAULT NULL,
  `institution_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `points` decimal(10,2) NOT NULL,
  `rule_id` int(10) unsigned DEFAULT NULL,
  `resource_name` varchar(45) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_point_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `institution_id` (`institution_id`),
  KEY `class_id` (`class_id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1098965 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_prizes`
--

DROP TABLE IF EXISTS `user_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_prizes` (
  `user_prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prize_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `institution_id` int(11) unsigned NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `prize_size` varchar(40) DEFAULT '',
  `serial` varchar(45) DEFAULT NULL,
  `status` enum('Checked Out','Printed','Redeemed') DEFAULT 'Checked Out',
  `is_reversed` tinyint(1) unsigned DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_prize_id`),
  KEY `prize_id` (`prize_id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=61038 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_user_id` int(10) unsigned DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `bar_code` varchar(20) DEFAULT NULL,
  `user_serial` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `hebrew_first_name` varchar(255) DEFAULT NULL,
  `hebrew_last_name` varchar(255) DEFAULT NULL,
  `user_start_date` varchar(45) DEFAULT NULL,
  `dob` varchar(10) DEFAULT NULL,
  `dob_he` varchar(45) DEFAULT '0000-00-00',
  `dob_he_offset` varchar(45) DEFAULT '0000-00-00',
  `gender` varchar(1) DEFAULT NULL,
  `address` varchar(255) DEFAULT '',
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `cell` varchar(255) DEFAULT NULL,
  `image_id` varchar(20) DEFAULT '0',
  `is_active` tinyint(1) unsigned DEFAULT '1',
  `is_deleted` tinyint(4) unsigned DEFAULT NULL,
  `custom_fields` mediumtext,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `created_by` (`created_by`),
  KEY `bar_code` (`bar_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7494 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_deleted`
--

DROP TABLE IF EXISTS `users_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_deleted` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_user_id` int(10) unsigned DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `bar_code` char(20) DEFAULT NULL,
  `user_serial` varchar(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `hebrew_first_name` varchar(255) DEFAULT NULL,
  `hebrew_last_name` varchar(255) DEFAULT NULL,
  `user_start_date` varchar(45) DEFAULT NULL,
  `dob` varchar(10) DEFAULT NULL,
  `dob_he` varchar(45) DEFAULT '0000-00-00',
  `dob_he_offset` varchar(45) DEFAULT '0000-00-00',
  `gender` varchar(1) DEFAULT NULL,
  `address` varchar(255) DEFAULT '',
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `image_id` varchar(20) DEFAULT '0',
  `is_active` tinyint(1) unsigned DEFAULT '1',
  `is_deleted` tinyint(4) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36205 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `velocity_grades`
--

DROP TABLE IF EXISTS `velocity_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `velocity_grades` (
  `velocity_grade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `grade_hierarchy` int(10) unsigned DEFAULT NULL,
  `velocity` decimal(10,2) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`velocity_grade_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `grade_id` (`grade_hierarchy`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `velocity_ladders`
--

DROP TABLE IF EXISTS `velocity_ladders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `velocity_ladders` (
  `velocity_ladder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `ladder` int(11) DEFAULT NULL,
  `velocity` decimal(11,2) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`velocity_ladder_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=264 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zend_log`
--

DROP TABLE IF EXISTS `zend_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zend_log` (
  `zend_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `level` varchar(6) DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`zend_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-11 13:40:58
