-- MySQL dump 10.16  Distrib 10.1.34-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: mashpiadb
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
-- Table structure for table `ID_cards`
--

DROP TABLE IF EXISTS `ID_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ID_cards` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `printed` datetime NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `type` enum('temporary','permanent') NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievement_tasks`
--

DROP TABLE IF EXISTS `achievement_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievement_tasks` (
  `achievement_task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` int(10) unsigned NOT NULL,
  `task` varchar(100) NOT NULL,
  `points` int(10) unsigned NOT NULL,
  `base` int(10) unsigned DEFAULT '1',
  `platoon` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`achievement_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=560 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `add_on_option_grades`
--

DROP TABLE IF EXISTS `add_on_option_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_on_option_grades` (
  `add_on_option_id` int(10) unsigned NOT NULL,
  `grade` varchar(10) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `add_on_options`
--

DROP TABLE IF EXISTS `add_on_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_on_options` (
  `add_on_option_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`add_on_option_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_auths`
--

DROP TABLE IF EXISTS `admin_auths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_auths` (
  `admin_id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user','camp') COLLATE utf8_unicode_ci NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned DEFAULT NULL,
  `position` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`admin_id`,`auth`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_sponsors`
--

DROP TABLE IF EXISTS `admin_sponsors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_sponsors` (
  `admin_sponsor_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `amount` decimal(6,2) DEFAULT NULL,
  `is_regular` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `year` int(4) unsigned NOT NULL,
  PRIMARY KEY (`admin_sponsor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=516 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `auth` enum('','inactive','super','ckidssuper') COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` enum('','Rabbi','Mr.','Mrs.','Ms.') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Rabbi',
  `title_fr` enum('','Rav','M.','Mme','Mlle') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Rav',
  `first` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `last` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `admin_address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_postal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_phone_work` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_phone_home` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_phone_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_phone_mobile2` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `camp_id` int(10) unsigned DEFAULT NULL,
  `staff_type_id` int(10) unsigned DEFAULT NULL,
  `staff_photo_id` int(10) unsigned DEFAULT NULL,
  `reminders` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_parent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `been_added` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `photo` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `is_shliach` tinyint(3) unsigned DEFAULT NULL,
  `ship_to` int(10) unsigned DEFAULT NULL,
  `num_hachayols` tinyint(3) unsigned DEFAULT NULL,
  `no_shipping` tinyint(4) DEFAULT '0',
  `father` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mother` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `father_pic` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mother_pic` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wp_id` int(10) unsigned DEFAULT NULL,
  `chidon_whatsapp` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `achievement_cards` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `store` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `phone1` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone3` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone4` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authorize_customer_profile_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `admin_email_UNIQUE` (`admin_email`)
) ENGINE=InnoDB AUTO_INCREMENT=179284 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `all_donations`
--

DROP TABLE IF EXISTS `all_donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `all_donations` (
  `donation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(80) CHARACTER SET latin1 NOT NULL,
  `amount` decimal(8,2) unsigned NOT NULL,
  `response` varchar(255) CHARACTER SET latin1 NOT NULL,
  `phone` varchar(60) CHARACTER SET latin1 DEFAULT NULL,
  `name` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `address` varchar(150) CHARACTER SET latin1 DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`donation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `army_cards`
--

DROP TABLE IF EXISTS `army_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `army_cards` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`card_id`),
  KEY `template_id` (`template_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `army_templates`
--

DROP TABLE IF EXISTS `army_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `army_templates` (
  `template_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned DEFAULT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `left_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `right_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `series` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`template_id`),
  KEY `school_id` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `attachment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email_id` int(10) unsigned NOT NULL,
  `src` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`attachment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auction_70`
--

DROP TABLE IF EXISTS `auction_70`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auction_70` (
  `user_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `balance` int(10) unsigned NOT NULL,
  `points_used` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auction_prizes`
--

DROP TABLE IF EXISTS `auction_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auction_prizes` (
  `auction_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `available` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`auction_id`,`prize_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auction_user_prizes`
--

DROP TABLE IF EXISTS `auction_user_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auction_user_prizes` (
  `auction_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  `system_awarded` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `won` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`auction_id`,`prize_id`,`user_id`),
  KEY `user_id` (`user_id`,`auction_id`),
  KEY `auction_id` (`auction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auction_winners`
--

DROP TABLE IF EXISTS `auction_winners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auction_winners` (
  `auction_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  `display_order` smallint(5) unsigned DEFAULT NULL,
  `shipped` tinyint(1) NOT NULL DEFAULT '0',
  `shipment_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`auction_id`,`user_id`,`prize_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auction_winners_deleted`
--

DROP TABLE IF EXISTS `auction_winners_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auction_winners_deleted` (
  `auction_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`auction_id`,`user_id`,`prize_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auction_winners_test`
--

DROP TABLE IF EXISTS `auction_winners_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auction_winners_test` (
  `auction_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`auction_id`,`user_id`,`prize_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auctions`
--

DROP TABLE IF EXISTS `auctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auctions` (
  `auction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auction_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  `auction_points_start_date` mediumint(8) unsigned DEFAULT NULL,
  `auction_date` mediumint(8) unsigned NOT NULL,
  `auction_points_trigger_date` mediumint(8) unsigned DEFAULT NULL,
  `auction_run_date` mediumint(8) unsigned DEFAULT NULL,
  `auction_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `max_prize_points` int(10) unsigned DEFAULT NULL,
  `auction_ran` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `kiosk_auction` tinyint(1) NOT NULL DEFAULT '0',
  `show_mobile` datetime DEFAULT NULL,
  PRIMARY KEY (`auction_id`),
  UNIQUE KEY `auction_date` (`auction_date`,`school_id`),
  KEY `school_id` (`school_id`,`auction_ran`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `base_cards`
--

DROP TABLE IF EXISTS `base_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_cards` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`card_id`),
  KEY `template_id` (`template_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `base_templates`
--

DROP TABLE IF EXISTS `base_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_templates` (
  `template_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned DEFAULT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `left_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `right_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `series` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`template_id`),
  KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `birthdays`
--

DROP TABLE IF EXISTS `birthdays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `birthdays` (
  `user_id` int(10) unsigned DEFAULT NULL,
  `date_tasks_mission_id` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `birthday` (`user_id`,`date_tasks_mission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bp_class_summary`
--

DROP TABLE IF EXISTS `bp_class_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bp_class_summary` (
  `campaign_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `num_lines` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bp_points`
--

DROP TABLE IF EXISTS `bp_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bp_points` (
  `points_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bp_type_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `points` decimal(4,2) unsigned NOT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `ref` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`points_id`),
  UNIQUE KEY `points` (`bp_type_id`,`user_id`,`type`,`ref`)
) ENGINE=InnoDB AUTO_INCREMENT=1516 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bp_school_summary`
--

DROP TABLE IF EXISTS `bp_school_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bp_school_summary` (
  `campaign_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `num_lines` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bp_types`
--

DROP TABLE IF EXISTS `bp_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bp_types` (
  `bp_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`bp_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bp_user_summary`
--

DROP TABLE IF EXISTS `bp_user_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bp_user_summary` (
  `campaign_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `num_lines` int(10) unsigned NOT NULL,
  `child_count` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`campaign_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camp_campaigns`
--

DROP TABLE IF EXISTS `camp_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camp_campaigns` (
  `camp_campaign_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `camp_id` int(10) unsigned NOT NULL,
  `campaign_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(10) unsigned NOT NULL,
  `group_task` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`camp_campaign_id`),
  UNIQUE KEY `camp_id` (`camp_id`,`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=463 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camp_card_codes`
--

DROP TABLE IF EXISTS `camp_card_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camp_card_codes` (
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `camp_id` int(10) unsigned NOT NULL DEFAULT '0',
  `task_id` int(10) unsigned NOT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `left_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `right_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `expiration_date` date NOT NULL,
  PRIMARY KEY (`code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camp_group_points`
--

DROP TABLE IF EXISTS `camp_group_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camp_group_points` (
  `camp_group_points_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned DEFAULT NULL,
  `points` int(10) unsigned NOT NULL,
  `points_date` date NOT NULL,
  PRIMARY KEY (`camp_group_points_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camp_missions`
--

DROP TABLE IF EXISTS `camp_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camp_missions` (
  `camp_mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence` tinyint(3) unsigned NOT NULL,
  `mission_id` int(10) unsigned NOT NULL,
  `camp_campaign_id` int(10) unsigned NOT NULL,
  `mission_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(10) unsigned NOT NULL,
  PRIMARY KEY (`camp_mission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1221 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camp_tasks`
--

DROP TABLE IF EXISTS `camp_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camp_tasks` (
  `camp_task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned DEFAULT NULL,
  `camp_mission_id` int(10) unsigned NOT NULL,
  `camp_type_id` int(10) unsigned NOT NULL,
  `level_id` int(10) unsigned DEFAULT '0',
  `period_id` int(10) unsigned NOT NULL,
  `task_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(10) unsigned NOT NULL,
  PRIMARY KEY (`camp_task_id`),
  UNIQUE KEY `camp_mission_id` (`camp_mission_id`,`task_name`),
  UNIQUE KEY `task_id` (`task_id`,`camp_mission_id`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2827 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camp_types`
--

DROP TABLE IF EXISTS `camp_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camp_types` (
  `camp_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `camp_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`camp_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `camps`
--

DROP TABLE IF EXISTS `camps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `camps` (
  `camp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `camp_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `camp_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `camp_name_he` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inst_id` int(10) unsigned NOT NULL DEFAULT '8',
  `camp_settings` set('home_camp') COLLATE utf8_unicode_ci NOT NULL,
  `package_id` int(10) unsigned DEFAULT NULL,
  `camp_gender` enum('M','F','B') COLLATE utf8_unicode_ci NOT NULL,
  `camp_logo_id` int(10) unsigned DEFAULT NULL,
  `camp_logo_kiosk_id` int(10) unsigned DEFAULT NULL,
  `camp_no_logo` tinyint(1) NOT NULL DEFAULT '0',
  `camp_file_id` int(10) unsigned DEFAULT NULL,
  `camp_address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `camp_address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `camp_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `camp_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `camp_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `camp_postal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `camp_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cc_number` varchar(19) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `cc_exp` varchar(5) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `cc_cvv` varchar(4) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `kiosk_print` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `camp_era` smallint(5) unsigned DEFAULT NULL,
  `shipping_method` enum('pickup','deliver') COLLATE utf8_unicode_ci NOT NULL,
  `shipping_first` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_last` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_postal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` mediumint(8) NOT NULL DEFAULT '0',
  `end_date` mediumint(8) NOT NULL DEFAULT '0',
  `camp_type_id` int(10) unsigned NOT NULL,
  `session_one_start` mediumint(8) unsigned DEFAULT NULL,
  `session_one_end` mediumint(8) unsigned DEFAULT NULL,
  `session_two_start` mediumint(8) unsigned DEFAULT NULL,
  `session_two_end` mediumint(8) unsigned DEFAULT NULL,
  `camp_number` mediumint(8) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`camp_id`),
  UNIQUE KEY `camp_name` (`inst_id`,`camp_name`),
  UNIQUE KEY `camp_number` (`camp_number`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cart_purchases`
--

DROP TABLE IF EXISTS `cart_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_purchases` (
  `cart_purchase_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `prize_shipped` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prize_points` int(10) unsigned NOT NULL,
  `prize_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `prize_quantity` smallint(5) unsigned DEFAULT '1',
  PRIMARY KEY (`cart_purchase_id`),
  KEY `user_id` (`user_id`,`prize_shipped`,`prize_date`),
  KEY `prize_shipped` (`prize_shipped`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cd_purchases`
--

DROP TABLE IF EXISTS `cd_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cd_purchases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `approval` varchar(255) NOT NULL,
  `date_purchased` datetime NOT NULL,
  `shipped` datetime DEFAULT NULL,
  `method` enum('ship','pickup') NOT NULL,
  `address` varchar(55) DEFAULT NULL,
  `city` varchar(35) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chain_items`
--

DROP TABLE IF EXISTS `chain_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chain_items` (
  `chain_item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `floor` tinyint(4) NOT NULL,
  `room` tinyint(4) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `mandatory_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `optional_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `label_id` int(10) unsigned DEFAULT NULL,
  `quantity` smallint(5) unsigned DEFAULT NULL,
  `points` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`chain_item_id`),
  UNIQUE KEY `chain_id` (`school_type_id`,`subject_id`,`level`,`track_id`,`floor`,`room`),
  KEY `label_id` (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chain_marks`
--

DROP TABLE IF EXISTS `chain_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chain_marks` (
  `chain_item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `skipped_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `override_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mark_description` text COLLATE utf8_unicode_ci NOT NULL,
  `mark_points` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`mark_date`,`chain_item_id`),
  KEY `chain_item_id` (`chain_item_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chain_missions`
--

DROP TABLE IF EXISTS `chain_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chain_missions` (
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `floor` tinyint(3) unsigned NOT NULL,
  `mission_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mission_description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`school_type_id`,`subject_id`,`level`,`track_id`,`floor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy`
--

DROP TABLE IF EXISTS `charidy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy` (
  `charidy_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  `fname` varchar(55) COLLATE utf8_bin DEFAULT NULL,
  `lname` varchar(55) COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(85) COLLATE utf8_bin DEFAULT NULL,
  `address` varchar(75) COLLATE utf8_bin DEFAULT NULL,
  `city` varchar(65) COLLATE utf8_bin DEFAULT NULL,
  `zip` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `state` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `country` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `donation` int(10) unsigned DEFAULT NULL,
  `with_matching` int(10) unsigned DEFAULT NULL,
  `solicited_by` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `donation_date` varchar(65) COLLATE utf8_bin DEFAULT NULL,
  `donor_comment` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `phone` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `parent_admin_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`charidy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_callers`
--

DROP TABLE IF EXISTS `charidy_callers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_callers` (
  `charidy_caller_id` int(11) NOT NULL AUTO_INCREMENT,
  `first` varchar(100) NOT NULL,
  `last` varchar(100) NOT NULL,
  PRIMARY KEY (`charidy_caller_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_donations`
--

DROP TABLE IF EXISTS `charidy_donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_donations` (
  `donation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donor_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `donation_date` datetime DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `child_only_donation` tinyint(3) unsigned DEFAULT '0',
  `dedication_name` varchar(85) DEFAULT NULL,
  `dedication_text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`donation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17657 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_donors`
--

DROP TABLE IF EXISTS `charidy_donors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_donors` (
  `donor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_admin_id` int(10) unsigned DEFAULT NULL,
  `first_name` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `last_name` varchar(65) CHARACTER SET latin1 DEFAULT NULL,
  `address` varchar(85) CHARACTER SET latin1 DEFAULT NULL,
  `city` varchar(55) CHARACTER SET latin1 DEFAULT NULL,
  `state` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `zip` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `country` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `phone` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(85) CHARACTER SET latin1 DEFAULT NULL,
  `needs_call` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`donor_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10872 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_donors_callers`
--

DROP TABLE IF EXISTS `charidy_donors_callers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_donors_callers` (
  `donor_id` int(11) NOT NULL,
  `charidy_caller_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  UNIQUE KEY `noDuplicates` (`donor_id`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_donors_extra_info`
--

DROP TABLE IF EXISTS `charidy_donors_extra_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_donors_extra_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donor_id` int(10) unsigned DEFAULT NULL,
  `parent_admin_id` int(10) unsigned DEFAULT NULL,
  `type` enum('email','phone') DEFAULT NULL,
  `info` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_final_list`
--

DROP TABLE IF EXISTS `charidy_final_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_final_list` (
  `cfl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donation_5776` int(10) unsigned DEFAULT NULL,
  `name` varchar(85) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `rank_5776` tinyint(3) unsigned DEFAULT NULL,
  `projected_5777` int(10) unsigned DEFAULT NULL,
  `rank_5777` tinyint(3) unsigned DEFAULT NULL,
  `email` varchar(85) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `charidy_id` int(10) unsigned DEFAULT NULL,
  `mailing_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`cfl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=747 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_school_staff`
--

DROP TABLE IF EXISTS `charidy_school_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_school_staff` (
  `css_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned DEFAULT NULL,
  `school_name` varchar(155) CHARACTER SET latin1 DEFAULT NULL,
  `staff_name` varchar(85) CHARACTER SET latin1 DEFAULT NULL,
  `staff_type` enum('principal','bc','teacher') CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(75) CHARACTER SET latin1 DEFAULT NULL,
  `office_number` varchar(65) CHARACTER SET latin1 DEFAULT NULL,
  `cell_number` varchar(65) CHARACTER SET latin1 DEFAULT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  `grade` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`css_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2826 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_temp_data`
--

DROP TABLE IF EXISTS `charidy_temp_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_temp_data` (
  `id` int(10) unsigned NOT NULL,
  `data` text CHARACTER SET latin1,
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `charidy_temp_donations`
--

DROP TABLE IF EXISTS `charidy_temp_donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charidy_temp_donations` (
  `donation_id` int(10) unsigned NOT NULL,
  `name` varchar(85) COLLATE utf8_bin DEFAULT NULL,
  `address1` varchar(85) COLLATE utf8_bin DEFAULT NULL,
  `address2` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `city` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `state` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `zip` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `country` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `phone` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `with_matching` decimal(6,2) DEFAULT NULL,
  `donation_amount` decimal(6,2) DEFAULT NULL,
  `donation_date` datetime DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(85) COLLATE utf8_bin DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `relation_id` int(10) unsigned DEFAULT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`donation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `check_ips`
--

DROP TABLE IF EXISTS `check_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `check_ips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=475 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chidon`
--

DROP TABLE IF EXISTS `chidon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chidon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) CHARACTER SET latin1 NOT NULL,
  `email` varchar(60) CHARACTER SET latin1 NOT NULL,
  `phone` varchar(20) CHARACTER SET latin1 NOT NULL,
  `mqty` int(10) unsigned NOT NULL,
  `gqty` int(10) unsigned NOT NULL,
  `ggqty` int(10) unsigned NOT NULL,
  `paid` decimal(6,2) NOT NULL,
  `approval` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date_purchased` datetime NOT NULL,
  `shipped` datetime DEFAULT NULL,
  `method` enum('ship','jcm pickup','event pickup') CHARACTER SET latin1 NOT NULL,
  `address` varchar(55) CHARACTER SET latin1 DEFAULT NULL,
  `city` varchar(35) CHARACTER SET latin1 DEFAULT NULL,
  `state` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `zip` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `chidon_reg_id` int(10) unsigned DEFAULT NULL,
  `chidon_reg_id2` int(10) unsigned DEFAULT NULL,
  `vip_seats` tinyint(4) NOT NULL DEFAULT '0',
  `fr` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `prepared` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `m10` int(10) unsigned NOT NULL,
  `m18` int(10) unsigned NOT NULL,
  `m36` int(10) unsigned NOT NULL,
  `m50` int(10) unsigned NOT NULL,
  `m100` int(10) unsigned NOT NULL,
  `g10` int(10) unsigned NOT NULL,
  `g18` int(10) unsigned NOT NULL,
  `gg10` int(10) unsigned NOT NULL,
  `gg18` int(10) unsigned NOT NULL,
  `gg36` int(10) unsigned NOT NULL,
  `gg50` int(10) unsigned NOT NULL,
  `gg100` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=811 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chidon_new`
--

DROP TABLE IF EXISTS `chidon_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chidon_new` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school` varchar(60) NOT NULL,
  `location` varchar(60) DEFAULT NULL,
  `type` enum('boys','girls','mixed') NOT NULL,
  `grades` varchar(60) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chidon_reg`
--

DROP TABLE IF EXISTS `chidon_reg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chidon_reg` (
  `chidon_reg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chidon_schools_id` int(10) unsigned NOT NULL,
  `grade` enum('4','5','6','7','8') CHARACTER SET latin1 NOT NULL,
  `type` enum('winner','parent','runnerUp','runnerUpP','contestant') CHARACTER SET latin1 NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `hname` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `book` enum('1','2','3','4') CHARACTER SET latin1 NOT NULL,
  `mark` tinyint(3) unsigned DEFAULT NULL,
  `mark1` tinyint(3) unsigned DEFAULT NULL,
  `mark2` tinyint(3) unsigned DEFAULT NULL,
  `mark3` tinyint(3) unsigned DEFAULT NULL,
  `fee` tinyint(3) unsigned DEFAULT NULL,
  `help` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `family` varchar(60) DEFAULT NULL,
  `address` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `arr_airport` varchar(60) DEFAULT NULL,
  `arr_number` varchar(60) DEFAULT NULL,
  `arr_time` varchar(45) DEFAULT NULL,
  `dep_airport` varchar(60) DEFAULT NULL,
  `dep_number` varchar(60) DEFAULT NULL,
  `dep_time` varchar(45) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `file` varchar(100) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid` tinyint(4) NOT NULL DEFAULT '0',
  `approval` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `parent_email` varchar(255) DEFAULT NULL,
  `parent_cell` varchar(25) DEFAULT NULL,
  `size` varchar(45) DEFAULT NULL,
  `transportation` tinyint(4) DEFAULT NULL,
  `bonus` tinyint(3) unsigned DEFAULT NULL,
  `parent_cell2` varchar(45) DEFAULT NULL,
  `allergies` varchar(255) DEFAULT NULL,
  `whatsapp` tinyint(3) DEFAULT '0',
  `walk_alone` tinyint(3) DEFAULT NULL,
  `shoe_size` varchar(45) DEFAULT NULL,
  `between_streets` varchar(255) DEFAULT NULL,
  `cert_printed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cert_conf_printed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `plaque_created` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `entered` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `team` tinyint(1) unsigned DEFAULT NULL,
  `child_city_state` varchar(155) DEFAULT NULL,
  `bus_number` varchar(10) DEFAULT NULL,
  `walking_group` varchar(10) DEFAULT NULL,
  `emergency` varchar(45) DEFAULT NULL,
  `meeting_point` varchar(85) DEFAULT NULL,
  `hfname` varchar(100) DEFAULT NULL,
  `hlname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`chidon_reg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2277 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chidon_schools`
--

DROP TABLE IF EXISTS `chidon_schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chidon_schools` (
  `chidon_schools_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `school_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `gender` varchar(45) NOT NULL,
  `chaperone_name` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `chaperone_phone` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `charged` smallint(5) unsigned NOT NULL,
  `approval` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `year` int(11) NOT NULL,
  `sweater` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `s_size` enum('s','m','l','xl') DEFAULT NULL,
  `full_program` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `chaperone_name2` varchar(45) DEFAULT NULL,
  `chaperone_phone2` varchar(30) DEFAULT NULL,
  `chaperone_name3` varchar(45) DEFAULT NULL,
  `chaperone_phone3` varchar(30) DEFAULT NULL,
  `chaperone_name4` varchar(45) DEFAULT NULL,
  `chaperone_phone4` varchar(30) DEFAULT NULL,
  `city_state` varchar(155) DEFAULT NULL,
  PRIMARY KEY (`chidon_schools_id`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `child_types`
--

DROP TABLE IF EXISTS `child_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `child_types` (
  `child_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `child_type_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`child_type_id`),
  UNIQUE KEY `child_type_name` (`child_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `children_missions`
--

DROP TABLE IF EXISTS `children_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `children_missions` (
  `mission_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mission_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `class_missions`
--

DROP TABLE IF EXISTS `class_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_missions` (
  `class_id` int(10) unsigned NOT NULL,
  `mission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`class_id`,`mission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `class_task_exceptions`
--

DROP TABLE IF EXISTS `class_task_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_task_exceptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `date_task_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Key` (`class_id`,`date_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44193696 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `class_task_info`
--

DROP TABLE IF EXISTS `class_task_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_task_info` (
  `date_task_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `mandatory` tinyint(1) NOT NULL,
  PRIMARY KEY (`date_task_id`,`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `class_tasks`
--

DROP TABLE IF EXISTS `class_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_tasks` (
  `class_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`class_id`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `class_grade` enum('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12') COLLATE utf8_unicode_ci NOT NULL,
  `class_grade_fr` enum('Gan_1','Gan_2','Gan_3','Grand_Gan','CP','CE1','CE2','CM1','CM2','6eme','5eme','4eme','3eme','2nde','1ere','Term') COLLATE utf8_unicode_ci DEFAULT NULL,
  `class_sub` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `class_teacher` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cell` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_level` tinyint(3) unsigned NOT NULL,
  `gender_view` enum('self','all') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'all',
  `class_era` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Hebrew Year when class was active (0 for current)',
  `teacher_gender` enum('M','F') COLLATE utf8_unicode_ci DEFAULT NULL,
  `teacher_hname` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class_gender` enum('m','f') COLLATE utf8_unicode_ci DEFAULT NULL,
  `whatsapp` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `allow_parent_tasks` tinyint(3) DEFAULT '1',
  `print_parent_tasks` tinyint(3) DEFAULT '1',
  `confirmed` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `school_id` (`school_id`,`class_id`),
  UNIQUE KEY `class_grade` (`school_id`,`class_era`,`class_grade`,`class_grade_fr`,`class_sub`),
  KEY `class_era` (`class_era`)
) ENGINE=InnoDB AUTO_INCREMENT=6493 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communicate_files`
--

DROP TABLE IF EXISTS `communicate_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communicate_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_UNIQUE` (`file`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `date_tasks`
--

DROP TABLE IF EXISTS `date_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_tasks` (
  `date_task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_tasks_mission_id` int(10) unsigned NOT NULL,
  `ord` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `mandatory_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `optional_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_bonus` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `label_id` int(10) unsigned DEFAULT NULL,
  `label_ord` smallint(5) unsigned NOT NULL,
  `quantity` smallint(5) unsigned DEFAULT NULL,
  `points` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `sequence_number` mediumint(2) DEFAULT NULL,
  `daily_task` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `needed` tinyint(1) unsigned NOT NULL,
  `focus_task` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cat_ord` decimal(6,2) DEFAULT NULL,
  `cat` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_on` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `task_id` decimal(6,2) NOT NULL,
  `short_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `medium_pic` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `yd_cat` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `yd_cat_num` int(10) unsigned DEFAULT NULL,
  `grid_id` int(10) unsigned DEFAULT NULL,
  `mission_marking` tinyint(1) unsigned DEFAULT NULL,
  `grid_marking` tinyint(1) unsigned DEFAULT NULL,
  `achievement_card` tinyint(1) unsigned DEFAULT NULL,
  `cat_ord_new` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`date_task_id`),
  KEY `date_tasks_mission_id` (`date_tasks_mission_id`,`ord`,`name`),
  KEY `label_id` (`label_id`,`date_tasks_mission_id`),
  KEY `short_name` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7854431 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `date_tasks_marks`
--

DROP TABLE IF EXISTS `date_tasks_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_tasks_marks` (
  `date_task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mark_description` text COLLATE utf8_unicode_ci NOT NULL,
  `mark_points` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `mark_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `mark_inactive` tinyint(1) NOT NULL DEFAULT '0',
  `mission_start` int(10) unsigned DEFAULT NULL,
  `mission_end` int(10) unsigned DEFAULT NULL,
  `dt_grid_id` int(10) unsigned DEFAULT NULL,
  `noDuplicates` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `date_task_id` (`date_task_id`),
  KEY `user_id` (`user_id`),
  KEY `grid_id` (`dt_grid_id`),
  KEY `mark_date` (`mark_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `date_tasks_mission_marks`
--

DROP TABLE IF EXISTS `date_tasks_mission_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_tasks_mission_marks` (
  `user_id` int(10) unsigned NOT NULL,
  `date_tasks_mission_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `mission_value` decimal(7,1) unsigned NOT NULL DEFAULT '1.0',
  `mission_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `mark_override` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `missions_updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`date_tasks_mission_id`),
  KEY `subject_id` (`user_id`,`subject_id`,`mark_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `date_tasks_missions`
--

DROP TABLE IF EXISTS `date_tasks_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_tasks_missions` (
  `date_tasks_mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `mission_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mission_number` decimal(8,1) unsigned DEFAULT NULL,
  `mission_group` enum('','shabbos','weekday') COLLATE utf8_unicode_ci NOT NULL,
  `mission_description` text COLLATE utf8_unicode_ci NOT NULL,
  `mission_value` decimal(5,1) unsigned NOT NULL DEFAULT '1.0',
  `start_date` mediumint(8) unsigned NOT NULL,
  `end_date` mediumint(8) unsigned NOT NULL,
  `missing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `personal` tinyint(1) NOT NULL DEFAULT '0',
  `speed` double NOT NULL DEFAULT '0',
  `default_on` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_by_school` int(10) unsigned DEFAULT NULL,
  `created_by_parent` int(10) DEFAULT NULL,
  `lang_id` int(10) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`date_tasks_mission_id`),
  KEY `date` (`school_type_id`,`subject_id`,`level`,`track_id`,`start_date`,`end_date`),
  KEY `track_id` (`subject_id`,`track_id`),
  KEY `level` (`subject_id`,`level`),
  KEY `subject_id` (`subject_id`,`start_date`),
  KEY `subject_id_2` (`subject_id`,`mission_number`,`date_tasks_mission_id`),
  KEY `subject_id_3` (`subject_id`,`mission_number`,`start_date`,`end_date`,`date_tasks_mission_id`),
  KEY `subject_id_4` (`subject_id`,`start_date`,`end_date`,`mission_number`,`date_tasks_mission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1801907 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `default_divisions`
--

DROP TABLE IF EXISTS `default_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `default_divisions` (
  `division_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `default_group_types`
--

DROP TABLE IF EXISTS `default_group_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `default_group_types` (
  `gt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_type_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`gt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_chapters`
--

DROP TABLE IF EXISTS `demo_tanya_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_chapters` (
  `name` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `line` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_goals`
--

DROP TABLE IF EXISTS `demo_tanya_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_goals` (
  `id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `chapter` varchar(2) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_lines`
--

DROP TABLE IF EXISTS `demo_tanya_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_lines` (
  `Line` smallint(5) unsigned NOT NULL,
  `Page` smallint(5) unsigned NOT NULL,
  `Perek` char(4) COLLATE utf8_unicode_ci NOT NULL,
  `Text` varchar(4096) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Line`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_medals`
--

DROP TABLE IF EXISTS `demo_tanya_medals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_medals` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `medal_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `values` int(11) NOT NULL,
  PRIMARY KEY (`medal_ord`),
  UNIQUE KEY `medal_name` (`medal_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_missions`
--

DROP TABLE IF EXISTS `demo_tanya_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_missions` (
  `mission_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'foreign',
  `mission_number` int(11) NOT NULL COMMENT 'miss named, task_number',
  `tested` tinyint(1) NOT NULL DEFAULT '0',
  `tested_date` int(11) NOT NULL,
  `ladder` int(11) NOT NULL,
  `real` decimal(4,2) NOT NULL COMMENT 'The weekly line number value; defined by ladder',
  `sum` decimal(4,2) NOT NULL COMMENT 'The lines for this week after remainder from previous mission is subtracted',
  `virtual_sum` int(11) NOT NULL COMMENT 'The number of lines completed up till this point',
  `mission_date` int(11) NOT NULL,
  `date_created` int(11) NOT NULL,
  PRIMARY KEY (`mission_id`),
  UNIQUE KEY `user_id_2` (`user_id`,`mission_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_requests`
--

DROP TABLE IF EXISTS `demo_tanya_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `line_goal` int(11) NOT NULL,
  `to_ladder` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_tasks`
--

DROP TABLE IF EXISTS `demo_tanya_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_tasks` (
  `user_id` int(11) NOT NULL,
  `mission` int(11) NOT NULL,
  `foreign_mission_id` int(11) NOT NULL DEFAULT '0',
  `line_number` int(6) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `user_id_2` (`user_id`,`mission`,`line_number`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo_tanya_users`
--

DROP TABLE IF EXISTS `demo_tanya_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo_tanya_users` (
  `id` int(11) NOT NULL,
  `lines_before_enrollment` int(6) NOT NULL,
  `lines_after_enrollment` int(6) NOT NULL,
  `desired_chapter_goal` int(6) NOT NULL COMMENT 'to be renamed to line_goal',
  `ladder` int(2) NOT NULL DEFAULT '1',
  `enrolled` tinyint(1) NOT NULL DEFAULT '0',
  `enrolled_date` int(11) NOT NULL,
  `date_created` int(11) NOT NULL COMMENT 'record creation date',
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='this table is user only for testing the tanya demo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `divisions` (
  `division_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_type_id` int(10) unsigned NOT NULL,
  `division_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`division_id`),
  UNIQUE KEY `group` (`division_id`,`division_name`),
  KEY `group_type_id` (`group_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1922 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `donations`
--

DROP TABLE IF EXISTS `donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donations` (
  `donation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(30) CHARACTER SET latin1 NOT NULL,
  `last_name` varchar(30) CHARACTER SET latin1 NOT NULL,
  `address` varchar(60) CHARACTER SET latin1 DEFAULT NULL,
  `city` varchar(40) CHARACTER SET latin1 DEFAULT NULL,
  `state` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `zip` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `country` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(60) CHARACTER SET latin1 NOT NULL,
  `phone` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `response` varchar(60) CHARACTER SET latin1 NOT NULL,
  `reason` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `dedication` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `family` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`donation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dummy`
--

DROP TABLE IF EXISTS `dummy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dummy` (
  `xx` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqadminlog`
--

DROP TABLE IF EXISTS `faqadminlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqadminlog` (
  `id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `usr` int(11) NOT NULL,
  `text` text NOT NULL,
  `ip` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqattachment`
--

DROP TABLE IF EXISTS `faqattachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqattachment` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `record_lang` varchar(5) NOT NULL,
  `real_hash` char(32) NOT NULL,
  `virtual_hash` char(32) NOT NULL,
  `password_hash` char(40) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL,
  `encrypted` tinyint(4) NOT NULL DEFAULT '0',
  `mime_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqattachment_file`
--

DROP TABLE IF EXISTS `faqattachment_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqattachment_file` (
  `virtual_hash` char(32) NOT NULL,
  `contents` blob NOT NULL,
  PRIMARY KEY (`virtual_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqcaptcha`
--

DROP TABLE IF EXISTS `faqcaptcha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqcaptcha` (
  `id` varchar(6) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `language` varchar(5) NOT NULL,
  `ip` varchar(64) NOT NULL,
  `captcha_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqcategories`
--

DROP TABLE IF EXISTS `faqcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqcategories` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqcategory_group`
--

DROP TABLE IF EXISTS `faqcategory_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqcategory_group` (
  `category_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqcategory_user`
--

DROP TABLE IF EXISTS `faqcategory_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqcategory_user` (
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqcategoryrelations`
--

DROP TABLE IF EXISTS `faqcategoryrelations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqcategoryrelations` (
  `category_id` int(11) NOT NULL,
  `category_lang` varchar(5) NOT NULL DEFAULT '',
  `record_id` int(11) NOT NULL,
  `record_lang` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`category_id`,`category_lang`,`record_id`,`record_lang`),
  KEY `idx_records` (`record_id`,`record_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqchanges`
--

DROP TABLE IF EXISTS `faqchanges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqchanges` (
  `id` int(11) NOT NULL,
  `beitrag` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `revision_id` int(11) NOT NULL DEFAULT '0',
  `usr` int(11) NOT NULL,
  `datum` int(11) NOT NULL,
  `what` text,
  PRIMARY KEY (`id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqcomments`
--

DROP TABLE IF EXISTS `faqcomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqcomments` (
  `id_comment` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `usr` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `datum` int(15) NOT NULL,
  `helped` text,
  PRIMARY KEY (`id_comment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqconfig`
--

DROP TABLE IF EXISTS `faqconfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqconfig` (
  `config_name` varchar(255) NOT NULL DEFAULT '',
  `config_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqdata`
--

DROP TABLE IF EXISTS `faqdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqdata` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `solution_id` int(11) NOT NULL,
  `revision_id` int(11) NOT NULL DEFAULT '0',
  `active` char(3) NOT NULL,
  `sticky` int(11) NOT NULL,
  `keywords` text,
  `thema` text NOT NULL,
  `content` longtext,
  `author` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` enum('y','n') NOT NULL DEFAULT 'y',
  `datum` varchar(15) NOT NULL,
  `links_state` varchar(7) DEFAULT NULL,
  `links_check_date` int(11) NOT NULL DEFAULT '0',
  `date_start` varchar(14) NOT NULL DEFAULT '00000000000000',
  `date_end` varchar(14) NOT NULL DEFAULT '99991231235959',
  PRIMARY KEY (`id`,`lang`),
  FULLTEXT KEY `keywords` (`keywords`,`thema`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqdata_group`
--

DROP TABLE IF EXISTS `faqdata_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqdata_group` (
  `record_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`record_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqdata_revisions`
--

DROP TABLE IF EXISTS `faqdata_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqdata_revisions` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `solution_id` int(11) NOT NULL,
  `revision_id` int(11) NOT NULL DEFAULT '0',
  `active` char(3) NOT NULL,
  `sticky` int(11) NOT NULL,
  `keywords` text,
  `thema` text NOT NULL,
  `content` longtext,
  `author` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` char(1) DEFAULT 'y',
  `datum` varchar(15) NOT NULL,
  `links_state` varchar(7) DEFAULT NULL,
  `links_check_date` int(11) NOT NULL DEFAULT '0',
  `date_start` varchar(14) NOT NULL DEFAULT '00000000000000',
  `date_end` varchar(14) NOT NULL DEFAULT '99991231235959',
  PRIMARY KEY (`id`,`lang`,`solution_id`,`revision_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqdata_tags`
--

DROP TABLE IF EXISTS `faqdata_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqdata_tags` (
  `record_id` int(11) NOT NULL,
  `tagging_id` int(11) NOT NULL,
  PRIMARY KEY (`record_id`,`tagging_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqdata_user`
--

DROP TABLE IF EXISTS `faqdata_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqdata_user` (
  `record_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`record_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqglossary`
--

DROP TABLE IF EXISTS `faqglossary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqglossary` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `item` varchar(255) NOT NULL,
  `definition` text NOT NULL,
  PRIMARY KEY (`id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqgroup`
--

DROP TABLE IF EXISTS `faqgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqgroup` (
  `group_id` int(11) NOT NULL,
  `name` varchar(25) DEFAULT NULL,
  `description` text,
  `auto_join` int(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqgroup_right`
--

DROP TABLE IF EXISTS `faqgroup_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqgroup_right` (
  `group_id` int(11) NOT NULL,
  `right_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqlinkverifyrules`
--

DROP TABLE IF EXISTS `faqlinkverifyrules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqlinkverifyrules` (
  `id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(6) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `reason` varchar(255) NOT NULL DEFAULT '',
  `enabled` enum('y','n') NOT NULL DEFAULT 'y',
  `locked` enum('y','n') NOT NULL DEFAULT 'n',
  `owner` varchar(255) NOT NULL DEFAULT '',
  `dtInsertDate` varchar(15) NOT NULL DEFAULT '',
  `dtUpdateDate` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqnews`
--

DROP TABLE IF EXISTS `faqnews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqnews` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `header` varchar(255) NOT NULL,
  `artikel` text NOT NULL,
  `datum` varchar(14) NOT NULL,
  `author_name` varchar(255) DEFAULT NULL,
  `author_email` varchar(255) DEFAULT NULL,
  `active` char(1) DEFAULT 'y',
  `comment` char(1) DEFAULT 'n',
  `date_start` varchar(14) NOT NULL DEFAULT '00000000000000',
  `date_end` varchar(14) NOT NULL DEFAULT '99991231235959',
  `link` varchar(255) DEFAULT NULL,
  `linktitel` varchar(255) DEFAULT NULL,
  `target` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqquestions`
--

DROP TABLE IF EXISTS `faqquestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqquestions` (
  `id` int(11) unsigned NOT NULL,
  `ask_username` varchar(100) NOT NULL,
  `ask_usermail` varchar(100) NOT NULL,
  `ask_rubrik` varchar(100) NOT NULL,
  `ask_content` text NOT NULL,
  `ask_date` varchar(20) NOT NULL,
  `is_visible` char(1) DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqright`
--

DROP TABLE IF EXISTS `faqright`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqright` (
  `right_id` int(11) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `for_users` int(1) DEFAULT '1',
  `for_groups` int(1) DEFAULT '1',
  PRIMARY KEY (`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqsearches`
--

DROP TABLE IF EXISTS `faqsearches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqsearches` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `searchterm` varchar(255) NOT NULL,
  `searchdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqsessions`
--

DROP TABLE IF EXISTS `faqsessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqsessions` (
  `sid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqstopwords`
--

DROP TABLE IF EXISTS `faqstopwords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqstopwords` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `stopword` varchar(64) NOT NULL,
  PRIMARY KEY (`id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqtags`
--

DROP TABLE IF EXISTS `faqtags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqtags` (
  `tagging_id` int(11) NOT NULL,
  `tagging_name` varchar(255) NOT NULL,
  PRIMARY KEY (`tagging_id`,`tagging_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faquser`
--

DROP TABLE IF EXISTS `faquser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faquser` (
  `user_id` int(11) NOT NULL,
  `login` varchar(25) NOT NULL,
  `session_id` varchar(150) DEFAULT NULL,
  `session_timestamp` int(11) unsigned DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `account_status` varchar(50) DEFAULT NULL,
  `last_login` varchar(14) DEFAULT NULL,
  `auth_source` varchar(100) DEFAULT NULL,
  `member_since` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faquser_group`
--

DROP TABLE IF EXISTS `faquser_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faquser_group` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faquser_right`
--

DROP TABLE IF EXISTS `faquser_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faquser_right` (
  `user_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faquserdata`
--

DROP TABLE IF EXISTS `faquserdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faquserdata` (
  `user_id` int(11) NOT NULL,
  `last_modified` timestamp NULL DEFAULT NULL,
  `display_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faquserlogin`
--

DROP TABLE IF EXISTS `faquserlogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faquserlogin` (
  `login` varchar(25) NOT NULL,
  `pass` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqvisits`
--

DROP TABLE IF EXISTS `faqvisits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqvisits` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `visits` int(11) NOT NULL,
  `last_visit` int(15) NOT NULL,
  PRIMARY KEY (`id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `faqvoting`
--

DROP TABLE IF EXISTS `faqvoting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqvoting` (
  `id` int(11) unsigned NOT NULL,
  `artikel` int(11) unsigned NOT NULL,
  `vote` int(11) unsigned NOT NULL,
  `usr` int(11) unsigned NOT NULL,
  `datum` varchar(20) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_types`
--

DROP TABLE IF EXISTS `file_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_types` (
  `file_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`file_type_id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `file_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `file_content_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `file_last_mod` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `file_data` longblob,
  `file_path` char(11) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `thumb` varchar(45) DEFAULT NULL,
  KEY `file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `frequencies`
--

DROP TABLE IF EXISTS `frequencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frequencies` (
  `frequency_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `frequency_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `frequency_period_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `monday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tuesday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `wednesday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `thursday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `friday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `shabbos` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sunday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sequence_number` smallint(2) DEFAULT NULL,
  PRIMARY KEY (`frequency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `frequency_periods`
--

DROP TABLE IF EXISTS `frequency_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frequency_periods` (
  `frequency_period_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `frequency_period_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`frequency_period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_campaigns`
--

DROP TABLE IF EXISTS `global_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_campaigns` (
  `campaign_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `campaign_name_fr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(10) unsigned NOT NULL,
  `camp_type_id` int(10) unsigned DEFAULT '0',
  `group_task` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_missions`
--

DROP TABLE IF EXISTS `global_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_missions` (
  `mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence` tinyint(3) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `mission_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mission_name_fr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mission_id`),
  UNIQUE KEY `campaign` (`mission_id`,`mission_name`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_prizes`
--

DROP TABLE IF EXISTS `global_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_prizes` (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prize_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prize_description` text COLLATE utf8_unicode_ci NOT NULL,
  `prize_points` int(10) unsigned NOT NULL,
  `prize_image_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`prize_id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_settings`
--

DROP TABLE IF EXISTS `global_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_settings` (
  `global_settings_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(65) DEFAULT NULL,
  `val` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`global_settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_tasks`
--

DROP TABLE IF EXISTS `global_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_tasks` (
  `task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mission_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `camp_type_id` int(10) unsigned NOT NULL,
  `level_id` int(10) unsigned NOT NULL,
  `period_id` int(10) unsigned NOT NULL,
  `task_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(10) unsigned NOT NULL,
  PRIMARY KEY (`task_id`),
  UNIQUE KEY `mission` (`task_id`,`task_name`),
  KEY `period_id` (`period_id`),
  KEY `mission_id` (`mission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `goals`
--

DROP TABLE IF EXISTS `goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goals` (
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `goal_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `goal_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`school_type_id`,`subject_id`,`level`,`track_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_roles`
--

DROP TABLE IF EXISTS `group_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_roles` (
  `group_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`group_role_id`),
  KEY `admin_id` (`admin_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_task_dates`
--

DROP TABLE IF EXISTS `group_task_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_task_dates` (
  `group_task_date_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_task_id` int(10) unsigned NOT NULL,
  `camp_task_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `task_date` mediumint(8) unsigned NOT NULL,
  `completed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_task_date_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_tasks`
--

DROP TABLE IF EXISTS `group_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_tasks` (
  `group_task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_type_id` int(10) unsigned DEFAULT NULL,
  `division_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL,
  `camp_task_id` int(10) unsigned NOT NULL,
  `period_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_task` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_task_id`),
  UNIQUE KEY `group_task` (`group_id`,`camp_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_type_roles`
--

DROP TABLE IF EXISTS `group_type_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_type_roles` (
  `group_type_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `group_type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`group_type_role_id`),
  KEY `admin_id` (`admin_id`),
  KEY `group_type_id` (`group_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_types`
--

DROP TABLE IF EXISTS `group_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_types` (
  `group_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `camp_id` int(10) unsigned NOT NULL,
  `group_type_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `has_divisions` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `logo_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`group_type_id`),
  UNIQUE KEY `group` (`group_type_id`,`group_type_name`),
  KEY `camp_id` (`camp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=728 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(10) unsigned NOT NULL,
  `group_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group` (`division_id`,`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1658 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hachayol_shipping`
--

DROP TABLE IF EXISTS `hachayol_shipping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hachayol_shipping` (
  `school_id` int(10) unsigned DEFAULT NULL,
  `parsha_id` int(10) unsigned DEFAULT NULL,
  `qty` int(10) DEFAULT NULL,
  `shipment_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `haggada_purchases`
--

DROP TABLE IF EXISTS `haggada_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `haggada_purchases` (
  `purchase_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `paid` decimal(6,2) NOT NULL,
  `cc_auth` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`purchase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hakhel_donations`
--

DROP TABLE IF EXISTS `hakhel_donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hakhel_donations` (
  `donation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(80) NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `amount` decimal(8,2) unsigned NOT NULL,
  `response` varchar(255) NOT NULL,
  `phone` varchar(60) DEFAULT NULL,
  `name` varchar(80) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`donation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `he_dob`
--

DROP TABLE IF EXISTS `he_dob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `he_dob` (
  `user_id` int(10) unsigned NOT NULL,
  `he_mm` smallint(5) unsigned NOT NULL,
  `he_dd` smallint(5) unsigned NOT NULL,
  `he_yy` smallint(5) unsigned NOT NULL,
  `born_in_leap` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `wp_synced` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `images_students`
--

DROP TABLE IF EXISTS `images_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images_students` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `photo_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `photo` longblob
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions`
--

DROP TABLE IF EXISTS `institutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutions` (
  `inst_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inst_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inst_logo_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`inst_id`),
  UNIQUE KEY `inst_name` (`inst_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitations` (
  `invitation_id` bigint(19) unsigned zerofill NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user') COLLATE utf8_unicode_ci NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `admin_id` int(10) unsigned NOT NULL COMMENT 'inviter',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `invitation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`invitation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `school_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_price` decimal(8,2) NOT NULL,
  `item_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_ref_type` enum('school_packages','school_package_fees','payment','charge','credit','note') COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_ref_id` int(10) unsigned DEFAULT NULL,
  `item_description` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `item_cc_ref` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`school_id`,`item_id`),
  KEY `item_date` (`item_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_age` enum('all','young','old') DEFAULT NULL,
  `description` varchar(50) NOT NULL,
  `first_time_only` tinyint(1) NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kapitels`
--

DROP TABLE IF EXISTS `kapitels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kapitels` (
  `kapitel` int(10) unsigned NOT NULL,
  `posuk` int(10) unsigned NOT NULL,
  `words` int(10) unsigned NOT NULL,
  UNIQUE KEY `kapitel` (`kapitel`,`posuk`,`words`),
  CONSTRAINT `kapitels_ibfk_1` FOREIGN KEY (`kapitel`) REFERENCES `tehillim` (`kapitel`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiosk_types`
--

DROP TABLE IF EXISTS `kiosk_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiosk_types` (
  `kiosk_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kiosk_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(8,2) unsigned NOT NULL,
  `non_ded_price` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY (`kiosk_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labels`
--

DROP TABLE IF EXISTS `labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labels` (
  `label_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label_name_fr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label_image_id` int(10) unsigned DEFAULT NULL,
  `frequency_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`label_id`),
  UNIQUE KEY `label_name` (`label_name`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `lang_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language` varchar(30) NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `levels`
--

DROP TABLE IF EXISTS `levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `levels` (
  `level_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `line_campaigns`
--

DROP TABLE IF EXISTS `line_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `line_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('Tanya','Mishna','Tehillim') NOT NULL,
  `campaign_date` date NOT NULL,
  `start_date` int(10) unsigned DEFAULT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lines_learned`
--

DROP TABLE IF EXISTS `lines_learned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lines_learned` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `lines_learned` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `mission_sheet_amount` int(10) unsigned DEFAULT NULL,
  `noDuplicates` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lines` (`campaign_id`,`lines_learned`,`user_id`,`noDuplicates`)
) ENGINE=InnoDB AUTO_INCREMENT=16557 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lines_pledged`
--

DROP TABLE IF EXISTS `lines_pledged`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lines_pledged` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `lines_pledged` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48315 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `magazines`
--

DROP TABLE IF EXISTS `magazines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `magazines` (
  `mag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mandatory_cats`
--

DROP TABLE IF EXISTS `mandatory_cats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mandatory_cats` (
  `subject_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `cat` varchar(150) NOT NULL,
  `cat_ord` decimal(6,2) DEFAULT NULL,
  KEY `subject` (`subject_id`),
  KEY `year` (`year`),
  KEY `lang` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `maos_chitim`
--

DROP TABLE IF EXISTS `maos_chitim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maos_chitim` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  `pledged` decimal(8,2) DEFAULT NULL,
  `raised` decimal(8,2) DEFAULT NULL,
  `year` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `maos_chitim_cards`
--

DROP TABLE IF EXISTS `maos_chitim_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maos_chitim_cards` (
  `number` int(10) unsigned NOT NULL,
  `value` decimal(4,2) NOT NULL,
  PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `maos_chitim_student_pledges`
--

DROP TABLE IF EXISTS `maos_chitim_student_pledges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maos_chitim_student_pledges` (
  `pledge_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `amount` decimal(4,2) NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pledge_id`)
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marks`
--

DROP TABLE IF EXISTS `marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marks` (
  `task_id` int(10) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `mark_description` text COLLATE utf8_unicode_ci NOT NULL,
  `mark_level` tinyint(3) unsigned NOT NULL,
  `mark_track_id` int(10) unsigned DEFAULT NULL,
  `mark_points` decimal(6,2) unsigned NOT NULL,
  `mark_quantity` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`,`mark_date`,`task_id`),
  KEY `task_id` (`task_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medal_marks`
--

DROP TABLE IF EXISTS `medal_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medal_marks` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_awarded` mediumint(8) unsigned NOT NULL,
  `date_shipped` timestamp NULL DEFAULT NULL,
  `date_received` timestamp NULL DEFAULT NULL,
  `medals_updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prof_medals_updater` tinyint(4) NOT NULL DEFAULT '0',
  `new_system_updated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`medal_ord`,`subject_id`,`user_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`medal_ord`,`user_id`),
  UNIQUE KEY `user_id` (`user_id`,`subject_id`,`medal_ord`),
  KEY `date_awarded` (`date_awarded`),
  KEY `date_received` (`date_received`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medal_marks_2018`
--

DROP TABLE IF EXISTS `medal_marks_2018`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medal_marks_2018` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_awarded` mediumint(8) unsigned NOT NULL,
  `date_shipped` timestamp NULL DEFAULT NULL,
  `date_received` timestamp NULL DEFAULT NULL,
  `medals_updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prof_medals_updater` tinyint(4) NOT NULL DEFAULT '0',
  `new_system_updated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`medal_ord`,`subject_id`,`user_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`medal_ord`,`user_id`),
  UNIQUE KEY `user_id` (`user_id`,`subject_id`,`medal_ord`),
  KEY `date_awarded` (`date_awarded`),
  KEY `date_received` (`date_received`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medal_marks_bk`
--

DROP TABLE IF EXISTS `medal_marks_bk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medal_marks_bk` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_awarded` mediumint(8) unsigned NOT NULL,
  `date_shipped` timestamp NULL DEFAULT NULL,
  `date_received` timestamp NULL DEFAULT NULL,
  `medals_updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prof_medals_updater` tinyint(4) NOT NULL DEFAULT '0',
  `new_system_updated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`medal_ord`,`subject_id`,`user_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`medal_ord`,`user_id`),
  UNIQUE KEY `user_id` (`user_id`,`subject_id`,`medal_ord`),
  KEY `date_awarded` (`date_awarded`),
  KEY `date_received` (`date_received`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medals`
--

DROP TABLE IF EXISTS `medals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medals` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `medal_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `medal_name_fr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `medal_on_image_id` int(10) unsigned DEFAULT NULL,
  `medal_off_image_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`medal_ord`),
  UNIQUE KEY `medal_name` (`medal_name`),
  UNIQUE KEY `medal_name_fr` (`medal_name_fr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medals_inventory`
--

DROP TABLE IF EXISTS `medals_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medals_inventory` (
  `medal_inventory_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` int(10) unsigned NOT NULL,
  `medal_ord` int(10) unsigned NOT NULL,
  `medal_type` enum('number_on_back','picture_on_back') NOT NULL DEFAULT 'picture_on_back',
  `in_stock` int(10) NOT NULL,
  PRIMARY KEY (`medal_inventory_id`),
  UNIQUE KEY `Index 2` (`subject_id`,`medal_ord`,`medal_type`)
) ENGINE=InnoDB AUTO_INCREMENT=464 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medals_inventory_details`
--

DROP TABLE IF EXISTS `medals_inventory_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medals_inventory_details` (
  `medal_inventory_details_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `medal_inventory_id` int(10) unsigned NOT NULL,
  `type` enum('initial_entry','add_to_stock','remove_from_stock','earned','shipped') NOT NULL DEFAULT 'initial_entry',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`medal_inventory_details_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medals_subjects`
--

DROP TABLE IF EXISTS `medals_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medals_subjects` (
  `subject_id` int(10) unsigned NOT NULL,
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `medal_on_image_id` int(10) unsigned DEFAULT NULL,
  `medal_off_image_id` int(10) unsigned DEFAULT NULL,
  `medal_photo_id` int(10) unsigned DEFAULT NULL,
  `missions_required` decimal(7,1) unsigned NOT NULL,
  `profile_photo_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`subject_id`,`medal_ord`),
  UNIQUE KEY `medal_ord` (`medal_ord`,`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `medals_subjects_totals`
--

DROP TABLE IF EXISTS `medals_subjects_totals`;
/*!50001 DROP VIEW IF EXISTS `medals_subjects_totals`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `medals_subjects_totals` (
  `subject_id` tinyint NOT NULL,
  `medal_ord` tinyint NOT NULL,
  `medal_on_image_id` tinyint NOT NULL,
  `medal_off_image_id` tinyint NOT NULL,
  `profile_photo_id` tinyint NOT NULL,
  `missions_required` tinyint NOT NULL,
  `missions_required_total` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `member_group_divisions`
--

DROP TABLE IF EXISTS `member_group_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_group_divisions` (
  `member_group_division_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `camp_group_division_id` int(10) unsigned NOT NULL,
  `start_date` mediumint(8) unsigned NOT NULL,
  `end_date` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`member_group_division_id`),
  UNIQUE KEY `member_group_division` (`user_id`,`camp_group_division_id`),
  KEY `camp_group_division_id` (`camp_group_division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_groups`
--

DROP TABLE IF EXISTS `member_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_groups` (
  `member_group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `camp_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `group_type_id` int(10) unsigned NOT NULL,
  `division_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `start_date` mediumint(8) unsigned NOT NULL,
  `end_date` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`member_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_points`
--

DROP TABLE IF EXISTS `member_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_points` (
  `member_points_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `points` int(10) unsigned NOT NULL,
  `points_date` date NOT NULL,
  PRIMARY KEY (`member_points_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_roles`
--

DROP TABLE IF EXISTS `member_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_roles` (
  `member_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`member_role_id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_tasks`
--

DROP TABLE IF EXISTS `member_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_tasks` (
  `member_task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `camp_task_id` int(10) unsigned NOT NULL,
  `task_date` mediumint(8) unsigned NOT NULL,
  `completed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`member_task_id`),
  UNIQUE KEY `campaign` (`user_id`,`camp_task_id`,`task_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_type_roles`
--

DROP TABLE IF EXISTS `member_type_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_type_roles` (
  `member_type_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `group_type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`member_type_role_id`),
  KEY `user_id` (`user_id`),
  KEY `group_type_id` (`group_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mesechtos`
--

DROP TABLE IF EXISTS `mesechtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesechtos` (
  `mesechto_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seder_id` int(10) unsigned NOT NULL,
  `mesechto` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`mesechto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mesechtos_learned`
--

DROP TABLE IF EXISTS `mesechtos_learned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesechtos_learned` (
  `mesechto_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mesechto_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mesechtos_summary`
--

DROP TABLE IF EXISTS `mesechtos_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesechtos_summary` (
  `mesechto_id` int(10) unsigned NOT NULL,
  `total_perokim` int(10) unsigned NOT NULL,
  `total_mishnos` int(10) unsigned NOT NULL,
  `total_lines` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mesechto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `message_type` enum('report1','report2','base_mission','th_to_soldier','hakhel_directives_1','hakhel_directives_2') COLLATE utf8_unicode_ci NOT NULL,
  `message_text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`message_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mishna_assigned`
--

DROP TABLE IF EXISTS `mishna_assigned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mishna_assigned` (
  `user_mishna_settings_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seder_id` int(10) unsigned NOT NULL,
  `mesechto_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_mishna_settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=594 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mishna_at_once`
--

DROP TABLE IF EXISTS `mishna_at_once`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mishna_at_once` (
  `user_id` int(10) unsigned NOT NULL,
  `mesechto_id` int(10) unsigned NOT NULL,
  `perek` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`mesechto_id`,`perek`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mishna_learned`
--

DROP TABLE IF EXISTS `mishna_learned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mishna_learned` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_learned` datetime DEFAULT NULL,
  `date_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mesechto_id` int(10) unsigned NOT NULL,
  `perek` int(10) unsigned NOT NULL,
  `mishna` int(10) unsigned NOT NULL,
  `lines_learned` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mishna` (`mesechto_id`,`perek`,`mishna`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2581 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mishna_ppl`
--

DROP TABLE IF EXISTS `mishna_ppl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mishna_ppl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `points` decimal(3,2) unsigned NOT NULL,
  `m_points` decimal(3,2) unsigned DEFAULT NULL,
  `p_points` decimal(3,2) unsigned DEFAULT NULL,
  `s_points` decimal(3,2) unsigned DEFAULT NULL,
  `shas_points` decimal(3,2) unsigned DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=270 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mishnos`
--

DROP TABLE IF EXISTS `mishnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mishnos` (
  `mishna_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seder_id` int(10) unsigned NOT NULL,
  `mesechto_id` int(10) unsigned NOT NULL,
  `perek` int(10) unsigned NOT NULL,
  `mishna` int(10) unsigned NOT NULL,
  `num_lines` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mishna_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4161 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `missing_medals`
--

DROP TABLE IF EXISTS `missing_medals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missing_medals` (
  `mm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `subject` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `medal` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`mm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missions` (
  `mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence` tinyint(3) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `mission_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mobile_logins`
--

DROP TABLE IF EXISTS `mobile_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_logins` (
  `login_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `cur_points` decimal(8,2) DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4707 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `new_medals_temp`
--

DROP TABLE IF EXISTS `new_medals_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new_medals_temp` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_awarded` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `new_ranks_temp`
--

DROP TABLE IF EXISTS `new_ranks_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new_ranks_temp` (
  `rank_ord` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `date_promoted` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newly_joined`
--

DROP TABLE IF EXISTS `newly_joined`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newly_joined` (
  `user_id` int(10) unsigned NOT NULL,
  `joined` int(10) unsigned NOT NULL,
  `shipped` date DEFAULT NULL,
  `received` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newly_registered`
--

DROP TABLE IF EXISTS `newly_registered`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newly_registered` (
  `user_id` int(10) unsigned NOT NULL,
  `reg_year` int(10) unsigned NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cards_shipped` date DEFAULT NULL,
  `cards_received` date DEFAULT NULL,
  `stickers_shipped` date DEFAULT NULL,
  `stickers_received` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oldresources`
--

DROP TABLE IF EXISTS `oldresources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oldresources` (
  `resource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `month` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ord`
--

DROP TABLE IF EXISTS `ord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ord` (
  `num` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`num`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `package_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `child_type_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB AUTO_INCREMENT=570 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periods`
--

DROP TABLE IF EXISTS `periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periods` (
  `period_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `period_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `monday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tuesday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `wednesday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `thursday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `friday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `shabbos` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sunday` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perl_cds`
--

DROP TABLE IF EXISTS `perl_cds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perl_cds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `cds_purchased` varchar(45) DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `download_code` varchar(10) NOT NULL,
  `auth` varchar(255) DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `downloaded` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perokim_learned`
--

DROP TABLE IF EXISTS `perokim_learned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perokim_learned` (
  `mesechto_id` int(10) unsigned NOT NULL,
  `perek` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mesechto_id`,`perek`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perokim_summary`
--

DROP TABLE IF EXISTS `perokim_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perokim_summary` (
  `mesechto_id` int(10) unsigned NOT NULL,
  `perek` int(10) unsigned NOT NULL,
  `total_mishnos` int(10) unsigned NOT NULL,
  `total_lines` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mesechto_id`,`perek`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `platoon_transitions`
--

DROP TABLE IF EXISTS `platoon_transitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platoon_transitions` (
  `platoon_transitions_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  `from_school_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `from_class_id` int(10) unsigned DEFAULT NULL,
  `year` int(10) unsigned NOT NULL,
  `deployed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`platoon_transitions_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `points`
--

DROP TABLE IF EXISTS `points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `points` (
  `user_id` int(10) unsigned NOT NULL,
  `award_date` mediumint(8) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `award_points` decimal(8,2) unsigned NOT NULL,
  `award_left_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `award_right_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `award_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `award_series` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`,`subject_id`,`award_date`),
  KEY `award_date` (`user_id`,`award_date`),
  KEY `award_series` (`user_id`,`subject_id`,`award_series`,`award_date`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `points_codes`
--

DROP TABLE IF EXISTS `points_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `points_codes` (
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `left_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `right_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `series` tinyint(3) unsigned DEFAULT NULL,
  `expiration_date` date NOT NULL,
  PRIMARY KEY (`code_id`),
  KEY `idx_points_codes` (`code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `points_codes_templates`
--

DROP TABLE IF EXISTS `points_codes_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `points_codes_templates` (
  `points_codes_template_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned DEFAULT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `left_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `right_circle` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `series` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`points_codes_template_id`),
  KEY `school_id` (`school_id`,`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poster_orders`
--

DROP TABLE IF EXISTS `poster_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poster_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `qty` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=376 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posters`
--

DROP TABLE IF EXISTS `posters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `posters` int(10) unsigned NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prizes`
--

DROP TABLE IF EXISTS `prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prizes` (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) CHARACTER SET utf8 NOT NULL,
  `picture` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(102) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_of_prize` enum('weekly','monthly','yearly') COLLATE utf8_unicode_ci NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prizes_auction_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`prize_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prizes_auction`
--

DROP TABLE IF EXISTS `prizes_auction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prizes_auction` (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned DEFAULT NULL,
  `prize_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prize_number` mediumint(8) unsigned DEFAULT NULL,
  `prize_description` text COLLATE utf8_unicode_ci NOT NULL,
  `prize_points` int(10) unsigned NOT NULL,
  `prize_ratio` smallint(5) unsigned NOT NULL,
  `prize_image_id` int(10) unsigned DEFAULT NULL,
  `min_grade` enum('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12') COLLATE utf8_unicode_ci DEFAULT NULL,
  `min_grade_fr` enum('Gan_1','Gan_2','Gan_3','Grand_Gan','CP','CE1','CE2','CM1','CM2','6eme','5eme','4eme','3eme','2nde','1ere','Term') COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_grade` enum('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12') COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_grade_fr` enum('Gan_1','Gan_2','Gan_3','Grand_Gan','CP','CE1','CE2','CM1','CM2','6eme','5eme','4eme','3eme','2nde','1ere','Term') COLLATE utf8_unicode_ci DEFAULT NULL,
  `in_stock` int(11) NOT NULL DEFAULT '0',
  `gender` enum('M','F','B') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'B',
  `ord` int(4) unsigned DEFAULT NULL,
  `archived` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`prize_id`),
  UNIQUE KEY `prize_number` (`prize_number`),
  UNIQUE KEY `prize_name` (`prize_name`,`school_id`),
  KEY `prize_points` (`prize_points`,`prize_name`),
  KEY `school_id` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=446 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prizes_camp`
--

DROP TABLE IF EXISTS `prizes_camp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prizes_camp` (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `global_prize_id` int(10) unsigned NOT NULL DEFAULT '0',
  `camp_id` int(10) unsigned DEFAULT NULL,
  `prize_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prize_description` text COLLATE utf8_unicode_ci NOT NULL,
  `prize_points` int(10) unsigned NOT NULL,
  `prize_available` smallint(5) unsigned DEFAULT '0',
  `prize_image_id` int(10) unsigned DEFAULT NULL,
  `installed` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`prize_id`),
  UNIQUE KEY `prize_name` (`prize_name`,`camp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=497 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prizes_store`
--

DROP TABLE IF EXISTS `prizes_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prizes_store` (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned DEFAULT NULL,
  `prize_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prize_description` text COLLATE utf8_unicode_ci NOT NULL,
  `prize_points` int(10) unsigned NOT NULL,
  `prize_available` smallint(5) unsigned DEFAULT NULL,
  `prize_image_id` int(10) unsigned DEFAULT NULL,
  `prize_current` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`prize_id`),
  UNIQUE KEY `prize_name` (`prize_name`,`school_id`),
  KEY `prize_points` (`prize_points`,`prize_name`),
  KEY `school_id` (`school_id`,`prize_available`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raffle_eligibility`
--

DROP TABLE IF EXISTS `raffle_eligibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raffle_eligibility` (
  `raffle_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `eligible` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `raffle_id` (`raffle_id`,`user_id`,`eligible`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raffle_prizes`
--

DROP TABLE IF EXISTS `raffle_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raffle_prizes` (
  `raffle_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  UNIQUE KEY `raffle_id` (`raffle_id`,`prize_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raffle_winners`
--

DROP TABLE IF EXISTS `raffle_winners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raffle_winners` (
  `raffle_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `shipped` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `raffle_id` (`raffle_id`,`prize_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raffles`
--

DROP TABLE IF EXISTS `raffles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raffles` (
  `raffle_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `run_date` datetime NOT NULL,
  `start_date` int(8) NOT NULL,
  `end_date` int(8) NOT NULL,
  `type` enum('weekly','monthly','yearly') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_ran` datetime DEFAULT NULL,
  `show_on_mobile` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`raffle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raffles_monthly`
--

DROP TABLE IF EXISTS `raffles_monthly`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raffles_monthly` (
  `raffle_id` int(10) unsigned DEFAULT NULL,
  `prize_id` int(10) unsigned DEFAULT NULL,
  `school_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rank_marks`
--

DROP TABLE IF EXISTS `rank_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rank_marks` (
  `rank_ord` tinyint(3) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_promoted` mediumint(8) unsigned NOT NULL,
  `date_printed` timestamp NULL DEFAULT NULL,
  `date_book_shipped` timestamp NULL DEFAULT NULL,
  `date_book_received` timestamp NULL DEFAULT NULL,
  `date_card_shipped` timestamp NULL DEFAULT NULL,
  `date_card_received` timestamp NULL DEFAULT NULL,
  `ranks_updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rank_ord`,`user_id`),
  UNIQUE KEY `user_id` (`user_id`,`rank_ord`),
  KEY `date_promoted` (`date_promoted`),
  KEY `date_printed` (`date_printed`),
  KEY `date_received` (`date_book_received`),
  KEY `date_card_received` (`date_card_received`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ranks`
--

DROP TABLE IF EXISTS `ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranks` (
  `rank_ord` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `rank_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rank_name_fr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `medals_required` tinyint(3) unsigned NOT NULL,
  `rank_image_id` int(10) unsigned DEFAULT NULL,
  `rank_image_id_fr` int(10) unsigned DEFAULT NULL,
  `rank_color` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `rank_color_fr` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `rank_background_image_id` int(10) unsigned DEFAULT NULL,
  `prof_rank_image_id` int(10) unsigned DEFAULT NULL,
  `prof_rank_image_id_fr` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rank_ord`),
  UNIQUE KEY `rank_name` (`rank_name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registration`
--

DROP TABLE IF EXISTS `registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration` (
  `reg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `approval` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `year` int(10) unsigned NOT NULL,
  `whatsapp` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tutorial` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `chavrusaEn` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `chavrusaHe` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `library` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `birthday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mishmor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_option` tinyint(4) DEFAULT NULL,
  `ship_dest` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `users` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extra_shipping` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`reg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1572 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registration_brochures`
--

DROP TABLE IF EXISTS `registration_brochures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_brochures` (
  `school_id` int(10) unsigned NOT NULL DEFAULT '0',
  `year` int(11) DEFAULT NULL,
  `brochures` int(11) DEFAULT NULL,
  PRIMARY KEY (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registration_charges`
--

DROP TABLE IF EXISTS `registration_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_charges` (
  `registration_charge_id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `type` varchar(45) NOT NULL DEFAULT 'n/a',
  `amount` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `year` int(4) DEFAULT NULL,
  PRIMARY KEY (`registration_charge_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `report_marks`
--

DROP TABLE IF EXISTS `report_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_marks` (
  `report_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user','end_user') COLLATE utf8_unicode_ci NOT NULL,
  `print_date` timestamp NULL DEFAULT NULL,
  `process_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`report_id`,`auth`,`id`),
  UNIQUE KEY `id` (`auth`,`id`,`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `report_subjects`
--

DROP TABLE IF EXISTS `report_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_subjects` (
  `report_type` enum('mission_cover_sheet') COLLATE utf8_unicode_ci NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `image_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`report_type`,`subject_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='For reports of more than one subject at a time';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `report_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `report_type` enum('','WWTC','Hakhel','Auction','mission_cover_sheet') COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start_date` mediumint(8) unsigned NOT NULL,
  `end_date` mediumint(8) unsigned NOT NULL,
  `visibility` enum('all','process','none') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'all',
  PRIMARY KEY (`report_id`),
  KEY `creation_date` (`creation_date`),
  KEY `report_type` (`report_type`,`visibility`,`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1160 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `reservation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `family` varchar(155) NOT NULL,
  `email` varchar(75) NOT NULL,
  `phone` varchar(45) NOT NULL,
  `boys` int(10) unsigned NOT NULL DEFAULT '0',
  `girls` int(10) unsigned NOT NULL DEFAULT '0',
  `auth_code` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(45) NOT NULL,
  `res_number` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`reservation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_categories`
--

DROP TABLE IF EXISTS `resource_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_categories` (
  `resource_id` int(10) unsigned NOT NULL,
  `category_type` enum('category','sub_category') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'category',
  `category_type_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`resource_id`,`category_type`,`category_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_tags`
--

DROP TABLE IF EXISTS `resource_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_tags` (
  `resource_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`resource_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources` (
  `resource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `file_type_id` int(10) unsigned NOT NULL,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`resource_id`),
  KEY `file_type_id` (`file_type_id`),
  CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`file_type_id`) REFERENCES `file_types` (`file_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_auth` enum('school','class','team','user','director','head counselor','counselor') COLLATE utf8_unicode_ci NOT NULL,
  `role_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_auth` (`role_auth`,`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_accessories`
--

DROP TABLE IF EXISTS `school_accessories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_accessories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `scanners` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_add_on_grades`
--

DROP TABLE IF EXISTS `school_add_on_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_add_on_grades` (
  `school_add_on_grade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `add_on_number` tinyint(1) NOT NULL,
  `grade` char(6) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`school_add_on_grade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_add_ons`
--

DROP TABLE IF EXISTS `school_add_ons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_add_ons` (
  `school_add_on_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `add_on` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` decimal(5,2) unsigned NOT NULL,
  `price` decimal(5,2) unsigned NOT NULL,
  `year` int(4) unsigned NOT NULL,
  `needs_size` tinyint(1) NOT NULL,
  `img_url` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`school_add_on_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_child_types`
--

DROP TABLE IF EXISTS `school_child_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_child_types` (
  `school_child_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `child_type_id` int(10) unsigned NOT NULL,
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`school_child_type_id`),
  UNIQUE KEY `school_child_type_id` (`school_id`,`child_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=214 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_child_types2`
--

DROP TABLE IF EXISTS `school_child_types2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_child_types2` (
  `school_child_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `child_type_id` int(10) unsigned NOT NULL,
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`school_child_type_id`),
  UNIQUE KEY `school_child_type_id` (`school_id`,`child_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=691 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_kiosks`
--

DROP TABLE IF EXISTS `school_kiosks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_kiosks` (
  `school_kiosk_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `kiosk_type_id` int(10) unsigned NOT NULL,
  `with_dedication` tinyint(1) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`school_kiosk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_makeups`
--

DROP TABLE IF EXISTS `school_makeups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_makeups` (
  `school_makeup_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_makeup_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`school_makeup_id`),
  UNIQUE KEY `school_makeup_name` (`school_makeup_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_missions`
--

DROP TABLE IF EXISTS `school_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_missions` (
  `school_id` int(10) unsigned NOT NULL,
  `mission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`school_id`,`mission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_package_fees`
--

DROP TABLE IF EXISTS `school_package_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_package_fees` (
  `package_id` int(10) unsigned NOT NULL,
  `fee_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fee_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fee_each` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY (`fee_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_packages`
--

DROP TABLE IF EXISTS `school_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_packages` (
  `package_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_ord` tinyint(3) unsigned NOT NULL,
  `package_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `package_description` text COLLATE utf8_unicode_ci NOT NULL,
  `fee` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_parents`
--

DROP TABLE IF EXISTS `school_parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_parents` (
  `school_id` int(11) NOT NULL,
  `admin_id` varchar(45) NOT NULL,
  KEY `main` (`school_id`,`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_reg_infos`
--

DROP TABLE IF EXISTS `school_reg_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_reg_infos` (
  `school_reg_info_id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `fee` int(11) NOT NULL DEFAULT '770',
  `balance` int(11) NOT NULL DEFAULT '0',
  `reg_deadline` datetime DEFAULT NULL,
  `early_bird` datetime NOT NULL,
  PRIMARY KEY (`school_reg_info_id`),
  UNIQUE KEY `noDuplicates` (`school_id`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_registrations`
--

DROP TABLE IF EXISTS `school_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_registrations` (
  `school_registration_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `year` int(10) unsigned NOT NULL,
  `amount_paid` decimal(6,2) DEFAULT NULL,
  `date_paid` timestamp NULL DEFAULT NULL,
  `child_fee` int(10) unsigned NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `fee` int(11) NOT NULL DEFAULT '770',
  `balance` int(11) NOT NULL DEFAULT '0',
  `early_bird` datetime NOT NULL,
  PRIMARY KEY (`school_registration_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_subjects`
--

DROP TABLE IF EXISTS `school_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_subjects` (
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`school_id`,`subject_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_subjects_new`
--

DROP TABLE IF EXISTS `school_subjects_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_subjects_new` (
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`school_id`,`subject_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_task_exceptions`
--

DROP TABLE IF EXISTS `school_task_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_task_exceptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `date_task_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Key` (`school_id`,`date_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1886453 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_task_info`
--

DROP TABLE IF EXISTS `school_task_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_task_info` (
  `date_task_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `mandatory` tinyint(1) NOT NULL,
  PRIMARY KEY (`date_task_id`,`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_tasks`
--

DROP TABLE IF EXISTS `school_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_tasks` (
  `school_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`school_id`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_type_subjects`
--

DROP TABLE IF EXISTS `school_type_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_type_subjects` (
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`school_type_id`,`subject_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`school_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_types`
--

DROP TABLE IF EXISTS `school_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_types` (
  `school_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_type_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `school_type_setting` enum('managed','managed_personal','self_managed','personal_only') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`school_type_id`),
  UNIQUE KEY `school_type_name` (`school_type_name`),
  KEY `school_type_setting` (`school_type_setting`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `school_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `school_name_he` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inst_id` int(10) unsigned NOT NULL,
  `school_makeup_id` int(10) unsigned DEFAULT NULL,
  `school_settings` set('home_school') COLLATE utf8_unicode_ci DEFAULT NULL,
  `package_id` int(10) unsigned DEFAULT NULL,
  `school_gender` enum('M','F','B') COLLATE utf8_unicode_ci NOT NULL,
  `school_number` mediumint(8) unsigned NOT NULL,
  `school_logo_id` int(10) unsigned DEFAULT NULL,
  `school_logo_kiosk_id` int(10) unsigned DEFAULT NULL,
  `school_no_logo` tinyint(1) NOT NULL DEFAULT '0',
  `school_file_id` int(10) unsigned DEFAULT NULL,
  `school_address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school_address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school_state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school_country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school_postal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school_phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_first` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_last` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_address` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_state` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_zip` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_number` varchar(19) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `cc_exp` varchar(5) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `cc_cvv` varchar(4) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `authorize_customer_profile_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authorize_payment_profile_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kiosk_print` tinyint(1) unsigned DEFAULT '1',
  `school_era` smallint(5) unsigned DEFAULT NULL,
  `shipping_method` enum('pickup','deliver') COLLATE utf8_unicode_ci DEFAULT NULL,
  `yearly_prize_shipping_method` enum('pickup','deliver') COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_first` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_last` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_postal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school_store` tinyint(1) NOT NULL DEFAULT '0',
  `camp_id` int(10) unsigned NOT NULL DEFAULT '0',
  `add_on_one` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `add_on_two` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `last_register_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cc_approval_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `big_prizes_won` int(10) unsigned NOT NULL DEFAULT '0',
  `notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `store_only` tinyint(1) DEFAULT NULL,
  `lang_id` int(10) unsigned NOT NULL DEFAULT '1',
  `logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo_2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `he_name_principal` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `he_name_p2` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conf_pushka_users` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `chayolei` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `tanya` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `tehillim` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chidon` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ckids` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tanya_ord` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tanya_cat_ord` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `school_type` tinyint(3) unsigned DEFAULT NULL,
  `school_initials` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nickname` varchar(155) COLLATE utf8_unicode_ci DEFAULT NULL,
  `principal` varchar(155) COLLATE utf8_unicode_ci DEFAULT NULL,
  `col_show` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `tuition` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `principal_number` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `principal_number_work` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `principal_email` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `principal_position` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chidon_name` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chidon_number` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chidon_email` varchar(85) COLLATE utf8_unicode_ci DEFAULT NULL,
  `num_students` int(11) unsigned DEFAULT NULL,
  `reg_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `shipping_requests` text COLLATE utf8_unicode_ci,
  `accounting_name` varchar(65) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accounting_number` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accounting_email` varchar(85) COLLATE utf8_unicode_ci DEFAULT NULL,
  `test_school` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `shamai_ord` tinyint(3) unsigned DEFAULT NULL,
  `hachayol_name` varchar(65) COLLATE utf8_unicode_ci DEFAULT NULL,
  `allow_parent_tasks` tinyint(3) DEFAULT '1',
  `print_parent_tasks` tinyint(3) DEFAULT '1',
  `store_start_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`school_id`),
  UNIQUE KEY `school_name` (`inst_id`,`school_name`),
  UNIQUE KEY `school_number` (`school_number`)
) ENGINE=InnoDB AUTO_INCREMENT=524 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sedorim`
--

DROP TABLE IF EXISTS `sedorim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sedorim` (
  `seder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seder` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`seder_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipment_details`
--

DROP TABLE IF EXISTS `shipment_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipment_details` (
  `shipment_id` int(10) unsigned DEFAULT NULL,
  `type` enum('gift','hachayol','prize','medal','rank') DEFAULT NULL,
  `item_type` varchar(20) DEFAULT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `item_ord` tinyint(3) DEFAULT NULL,
  `item_extra_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipments` (
  `shipment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `date_shipped` datetime DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `delivered` tinyint(4) DEFAULT '0',
  `archived` tinyint(4) DEFAULT '0',
  `description` text,
  `status` enum('planned','in transit','delivered','archived') NOT NULL DEFAULT 'planned',
  PRIMARY KEY (`shipment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_addresses`
--

DROP TABLE IF EXISTS `shipping_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_addresses` (
  `address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `address_line_1` varchar(100) CHARACTER SET latin1 NOT NULL,
  `address_line_2` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `city` varchar(100) CHARACTER SET latin1 NOT NULL,
  `state` varchar(45) CHARACTER SET latin1 NOT NULL,
  `zip` varchar(45) CHARACTER SET latin1 NOT NULL,
  `country` varchar(45) CHARACTER SET latin1 DEFAULT 'USA',
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_rates`
--

DROP TABLE IF EXISTS `shipping_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_rates` (
  `type` tinyint(4) NOT NULL,
  `zone` tinyint(4) NOT NULL,
  `child_count` int(11) NOT NULL,
  `rate` int(11) NOT NULL,
  UNIQUE KEY `noDuplicates` (`type`,`zone`,`child_count`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_report`
--

DROP TABLE IF EXISTS `shipping_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `printed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `siddur_purchases`
--

DROP TABLE IF EXISTS `siddur_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siddur_purchases` (
  `purchase_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(11) unsigned NOT NULL,
  `admin_id` int(11) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `paid` decimal(6,2) NOT NULL,
  `cc_auth` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`purchase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sm`
--

DROP TABLE IF EXISTS `sm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sm` (
  `month` varchar(10) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `task` enum('Kapitelach','Minutes') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('army','school','class') NOT NULL,
  `type_id` int(10) unsigned NOT NULL,
  `quota` int(10) unsigned NOT NULL,
  `accomplished` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_divisions`
--

DROP TABLE IF EXISTS `staff_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_divisions` (
  `staff_division_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `division_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`staff_division_id`),
  UNIQUE KEY `employee_divisions` (`admin_id`,`division_id`),
  KEY `division_id` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_group_types`
--

DROP TABLE IF EXISTS `staff_group_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_group_types` (
  `staff_group_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `group_type_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`staff_group_type_id`),
  UNIQUE KEY `employee_group_types` (`admin_id`,`group_type_id`),
  KEY `group_type_id` (`group_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_groups`
--

DROP TABLE IF EXISTS `staff_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_groups` (
  `staff_group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`staff_group_id`),
  UNIQUE KEY `employee_groups` (`admin_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_info`
--

DROP TABLE IF EXISTS `staff_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_info` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `staff_name` varchar(60) NOT NULL,
  `staff_position` varchar(80) NOT NULL,
  `staff_email` varchar(80) NOT NULL,
  `staff_number` varchar(15) NOT NULL,
  `staff_work_number` varchar(45) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_permissions`
--

DROP TABLE IF EXISTS `staff_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_permissions` (
  `staff_permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `write` tinyint(1) NOT NULL DEFAULT '0',
  `delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`staff_permission_id`),
  UNIQUE KEY `staff_group` (`admin_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_roles`
--

DROP TABLE IF EXISTS `staff_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_roles` (
  `staff_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `allow_read` tinyint(1) NOT NULL DEFAULT '0',
  `allow_write` tinyint(1) NOT NULL DEFAULT '0',
  `allow_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`staff_role_id`),
  UNIQUE KEY `employee_role` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_types`
--

DROP TABLE IF EXISTS `staff_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_types` (
  `staff_types_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`staff_types_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `store_purchases`
--

DROP TABLE IF EXISTS `store_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_purchases` (
  `store_purchase_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `prize_shipped` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prize_points` int(10) unsigned NOT NULL,
  `prize_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `prize_quantity` int(10) unsigned NOT NULL DEFAULT '1',
  `voucher_id` bigint(17) unsigned NOT NULL DEFAULT '0',
  `scan_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`store_purchase_id`),
  KEY `user_id` (`user_id`,`prize_shipped`,`prize_date`),
  KEY `prize_shipped` (`prize_shipped`)
) ENGINE=InnoDB AUTO_INCREMENT=930 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stories`
--

DROP TABLE IF EXISTS `stories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stories` (
  `story_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `story` text NOT NULL,
  `lesson` text NOT NULL,
  `entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`story_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `student_images`
--

DROP TABLE IF EXISTS `student_images`;
/*!50001 DROP VIEW IF EXISTS `student_images`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `student_images` (
  `user_id` tinyint NOT NULL,
  `photo_type` tinyint NOT NULL,
  `photo` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sub_categories` (
  `sub_category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `sub_category` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sub_category_id`),
  UNIQUE KEY `sub_category` (`sub_category`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `sub_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `subject_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject_name_fr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inst_id` int(10) unsigned NOT NULL,
  `subject_type` enum('','goal_hist','WWTC','Hakhel','school_points','home_points','Tanya','achievement') COLLATE utf8_unicode_ci NOT NULL,
  `subject_ord` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subject_image_id` int(10) unsigned DEFAULT NULL,
  `subject_gold_image_id` int(10) unsigned DEFAULT NULL,
  `subject_black_image_id` int(10) unsigned DEFAULT NULL,
  `subject_slogan` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject_description_fr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject_commitments` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject_commitments_fr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject_details` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_name` (`inst_id`,`subject_name`),
  UNIQUE KEY `inst_id` (`inst_id`,`subject_ord`,`subject_name`),
  KEY `subject_type` (`subject_type`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `table1`
--

DROP TABLE IF EXISTS `table1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table1` (
  `key1` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `field1` int(10) unsigned NOT NULL,
  PRIMARY KEY (`key1`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `table2`
--

DROP TABLE IF EXISTS `table2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table2` (
  `key2` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key1` int(10) unsigned NOT NULL,
  `field2` int(10) unsigned NOT NULL,
  PRIMARY KEY (`key2`),
  KEY `key1` (`key1`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tanya_goals`
--

DROP TABLE IF EXISTS `tanya_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanya_goals` (
  `track` tinyint(3) unsigned NOT NULL,
  `lines_goal` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`track`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tanya_lines`
--

DROP TABLE IF EXISTS `tanya_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanya_lines` (
  `line` smallint(5) unsigned NOT NULL,
  `page` smallint(5) unsigned NOT NULL,
  `perek` char(4) COLLATE utf8_unicode_ci NOT NULL,
  `text` varchar(4096) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`line`),
  UNIQUE KEY `page` (`page`,`line`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tanya_medal_cards`
--

DROP TABLE IF EXISTS `tanya_medal_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanya_medal_cards` (
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `medal_stage` tinyint(3) unsigned NOT NULL,
  `expiration_date` date NOT NULL,
  PRIMARY KEY (`code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tanya_totals`
--

DROP TABLE IF EXISTS `tanya_totals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanya_totals` (
  `school_id` int(10) unsigned NOT NULL,
  `total_tanya` int(10) unsigned NOT NULL,
  `total_mishna` int(10) unsigned NOT NULL,
  PRIMARY KEY (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tanya_users`
--

DROP TABLE IF EXISTS `tanya_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanya_users` (
  `user_id` int(10) unsigned NOT NULL,
  `track` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `year` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `lines_done` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lines_offset` smallint(5) unsigned NOT NULL DEFAULT '0',
  `medal_ord` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
  `tanya_start_date` mediumint(8) unsigned NOT NULL,
  `length_days` smallint(5) unsigned NOT NULL,
  `length_days_offset` smallint(5) unsigned NOT NULL,
  `pledges` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `collected` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `tanya_lines` int(11) NOT NULL,
  `mishna_lines` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tanya_users_old`
--

DROP TABLE IF EXISTS `tanya_users_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanya_users_old` (
  `user_id` int(10) unsigned NOT NULL,
  `track` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `year` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `lines_done` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lines_offset` smallint(5) unsigned NOT NULL DEFAULT '0',
  `medal_ord` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
  `tanya_start_date` mediumint(8) unsigned NOT NULL,
  `length_days` smallint(5) unsigned NOT NULL,
  `length_days_offset` smallint(5) unsigned NOT NULL,
  `pledges` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `collected` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `tanya_lines` int(11) NOT NULL,
  `mishna_lines` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_active`
--

DROP TABLE IF EXISTS `task_active`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_active` (
  `task_id` int(10) unsigned NOT NULL,
  `school_type_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `points` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `quantity` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`task_id`,`school_type_id`,`track_id`,`level`),
  UNIQUE KEY `school_type_id` (`school_type_id`,`level`,`track_id`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_mark_dates`
--

DROP TABLE IF EXISTS `task_mark_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_mark_dates` (
  `task_mark_date_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_mark_date_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_marks`
--

DROP TABLE IF EXISTS `task_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_marks` (
  `task_mark_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_prize_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date_task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mark_description` text COLLATE utf8_unicode_ci NOT NULL,
  `mark_points` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `mark_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `mark_inactive` tinyint(1) NOT NULL DEFAULT '0',
  `test` enum('a','b') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`task_mark_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `task_id` int(10) NOT NULL AUTO_INCREMENT,
  `subject_id` int(10) NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `task_type` int(10) NOT NULL,
  `quantity_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rep_type` enum('daily','weekly','monthly_date','monthly_week','yearly') COLLATE utf8_unicode_ci NOT NULL,
  `start_date` mediumint(8) NOT NULL,
  `end_date` mediumint(8) NOT NULL,
  `every` smallint(5) NOT NULL,
  `rep_param1` tinyint(3) NOT NULL,
  `rep_param2` tinyint(3) NOT NULL,
  PRIMARY KEY (`task_id`),
  UNIQUE KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `team_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `team_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`team_id`),
  UNIQUE KEY `team_name` (`school_id`,`team_name`),
  UNIQUE KEY `school_id` (`school_id`,`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tehillim`
--

DROP TABLE IF EXISTS `tehillim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tehillim` (
  `kapitel` int(10) unsigned NOT NULL,
  `posukim` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kapitel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tehillim_backups`
--

DROP TABLE IF EXISTS `tehillim_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tehillim_backups` (
  `backupID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` int(10) unsigned NOT NULL,
  `done_qty` int(10) unsigned NOT NULL,
  `mark_description` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` int(11) NOT NULL,
  `grid_id` int(11) NOT NULL,
  `sm_date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`backupID`),
  UNIQUE KEY `MAIN` (`date_task_id`,`user_id`),
  KEY `GRID` (`grid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=153778 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tehillim_ladders`
--

DROP TABLE IF EXISTS `tehillim_ladders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tehillim_ladders` (
  `ladder` int(10) unsigned NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `month` int(10) unsigned NOT NULL,
  `kapitelach` varchar(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `minutes` int(10) unsigned NOT NULL,
  `speed` float(2,1) NOT NULL,
  `qty` int(11) NOT NULL,
  PRIMARY KEY (`ladder`,`age`,`month`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terms` (
  `term_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `camp_id` int(10) unsigned DEFAULT NULL,
  `term_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `term_days` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`term_id`),
  UNIQUE KEY `term` (`camp_id`,`term_name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_20_reg`
--

DROP TABLE IF EXISTS `th_20_reg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_20_reg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `camp` varchar(100) CHARACTER SET latin1 NOT NULL,
  `number_staff` int(10) unsigned NOT NULL,
  `number_campers` int(10) unsigned NOT NULL,
  `ccauth` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon`
--

DROP TABLE IF EXISTS `th_chidon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon` (
  `th_chidon_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `history` varchar(255) DEFAULT NULL,
  `size` varchar(35) DEFAULT NULL,
  `reg_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `test1a` int(10) unsigned DEFAULT NULL,
  `test1b` int(10) unsigned DEFAULT NULL,
  `test2a` int(10) unsigned DEFAULT NULL,
  `test2b` int(10) unsigned DEFAULT NULL,
  `test3a` int(10) unsigned DEFAULT NULL,
  `test3b` int(10) unsigned DEFAULT NULL,
  `contestant` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `school_rep` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `paid` decimal(6,2) DEFAULT NULL,
  `date_paid` datetime DEFAULT NULL,
  `paid_by` int(10) unsigned DEFAULT NULL,
  `grade` enum('4','5','6','7','8') DEFAULT NULL,
  `book` enum('1','2','3','4','5') DEFAULT NULL,
  `host` varchar(105) DEFAULT NULL,
  `host_address1` varchar(45) DEFAULT NULL,
  `host_address2` varchar(105) DEFAULT NULL,
  `between_streets` varchar(155) DEFAULT NULL,
  `host_number` varchar(20) DEFAULT NULL,
  `allergies` varchar(255) DEFAULT NULL,
  `sandwich` enum('tuna','egg','cc','plain','cheese') DEFAULT NULL,
  `walk_day` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `walk_night` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `walking_zone` varchar(10) DEFAULT NULL,
  `approval` varchar(255) DEFAULT NULL,
  `shoe_size` varchar(30) DEFAULT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `team_id` int(10) unsigned DEFAULT NULL,
  `bunk_id` int(10) unsigned DEFAULT NULL,
  `test_table` int(10) unsigned DEFAULT NULL,
  `bowling_lane` varchar(10) DEFAULT NULL,
  `coach_bus` varchar(10) DEFAULT NULL,
  `dropoff_bus` int(10) unsigned DEFAULT NULL,
  `dropoff_seat` int(10) unsigned DEFAULT NULL,
  `school_bus` varchar(10) DEFAULT NULL,
  `double_decker` varchar(10) DEFAULT NULL,
  `seat_type` enum('medal','plaque','round one','round two') DEFAULT NULL,
  `seat_number` int(10) unsigned DEFAULT NULL,
  `cert_number` varchar(10) DEFAULT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `test_lang` enum('en','yi') NOT NULL DEFAULT 'en',
  `can_enroll` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `confirmed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `allow_edit` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `workshop_number` varchar(10) DEFAULT '0',
  `lane` int(10) DEFAULT NULL,
  PRIMARY KEY (`th_chidon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5174 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_attendance_lookup`
--

DROP TABLE IF EXISTS `th_chidon_attendance_lookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_attendance_lookup` (
  `att_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `att_time_id` int(10) unsigned NOT NULL,
  `bus_id` int(10) unsigned DEFAULT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `test_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`att_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_attendance_marks`
--

DROP TABLE IF EXISTS `th_chidon_attendance_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_attendance_marks` (
  `att_mark_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `att_time_id` int(10) unsigned NOT NULL,
  `th_chidon_id` int(10) unsigned NOT NULL,
  `marked` tinyint(1) DEFAULT '0',
  `marked_by` int(10) unsigned NOT NULL,
  `marked_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`att_mark_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2573 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_attendance_school_doors`
--

DROP TABLE IF EXISTS `th_chidon_attendance_school_doors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_attendance_school_doors` (
  `door_number` int(11) NOT NULL,
  `school_id` varchar(45) NOT NULL,
  UNIQUE KEY `no_duplicates` (`door_number`,`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_attendance_times`
--

DROP TABLE IF EXISTS `th_chidon_attendance_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_attendance_times` (
  `att_time_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `day_of_week` varchar(15) DEFAULT NULL,
  `att_time` datetime NOT NULL,
  `att_type` varchar(35) NOT NULL,
  `description` varchar(100) NOT NULL,
  `gender` enum('B','M','F') NOT NULL DEFAULT 'B',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`att_time_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_bunks`
--

DROP TABLE IF EXISTS `th_chidon_bunks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_bunks` (
  `bunk_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bunk_name` varchar(65) DEFAULT NULL,
  `counselor` varchar(65) DEFAULT NULL,
  `c_number` varchar(45) DEFAULT NULL,
  `c_coach_bus` varchar(10) DEFAULT NULL,
  `c_dropoff` int(10) unsigned DEFAULT NULL,
  `c_school_bus` varchar(10) DEFAULT NULL,
  `c_double_decker` varchar(10) DEFAULT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  `grade` tinyint(3) unsigned DEFAULT NULL,
  `walking_zone` int(10) DEFAULT NULL,
  `host_name` varchar(100) DEFAULT NULL,
  `host_address1` varchar(45) DEFAULT NULL,
  `host_address2` varchar(100) DEFAULT NULL,
  `host_between_streets` varchar(255) DEFAULT NULL,
  `chidon_type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`bunk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_buses`
--

DROP TABLE IF EXISTS `th_chidon_buses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_buses` (
  `bus_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bus_code` varchar(10) NOT NULL,
  `bus_type` varchar(20) DEFAULT NULL,
  `staff_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`bus_id`),
  UNIQUE KEY `bus_code` (`bus_code`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_chap_payments`
--

DROP TABLE IF EXISTS `th_chidon_chap_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_chap_payments` (
  `th_chidon_chap_payment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `paid` decimal(6,2) unsigned DEFAULT NULL,
  `approval` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_paid` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `school_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`th_chidon_chap_payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_chaps`
--

DROP TABLE IF EXISTS `th_chidon_chaps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_chaps` (
  `th_chidon_chap_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `name` varchar(105) CHARACTER SET latin1 DEFAULT NULL,
  `phone` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(105) CHARACTER SET latin1 DEFAULT NULL,
  `full_program` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sweater` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sweater_size` enum('s','m','l','xl','xxl') CHARACTER SET latin1 DEFAULT NULL,
  `ticket` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `year` int(10) unsigned DEFAULT NULL,
  `show_id_cards` tinyint(3) unsigned DEFAULT '0',
  `first_name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(65) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `acc_name` varchar(85) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acc_address` varchar(95) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acc_phone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vehicle` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `acc_cross_st` varchar(85) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chidon_type` enum('boys','girls') COLLATE utf8_unicode_ci DEFAULT NULL,
  `walking_zone` int(10) DEFAULT NULL,
  PRIMARY KEY (`th_chidon_chap_id`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_locations`
--

DROP TABLE IF EXISTS `th_chidon_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_locations` (
  `location_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(30) NOT NULL,
  `staff_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_schools`
--

DROP TABLE IF EXISTS `th_chidon_schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_schools` (
  `th_chidon_schools_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `registered` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`th_chidon_schools_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_sponsors`
--

DROP TABLE IF EXISTS `th_chidon_sponsors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_sponsors` (
  `th_chidon_sponsor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `num_trips` tinyint(1) unsigned NOT NULL,
  `amount` decimal(6,2) NOT NULL,
  `approval` varchar(255) NOT NULL,
  `sponsor` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`th_chidon_sponsor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_staff`
--

DROP TABLE IF EXISTS `th_chidon_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_staff` (
  `staff_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `cell` varchar(20) DEFAULT NULL,
  `email` varchar(85) DEFAULT NULL,
  `username` varchar(35) NOT NULL,
  `password` varchar(255) NOT NULL,
  `walking_zone` int(10) DEFAULT NULL,
  `door_number` varchar(10) DEFAULT NULL,
  `bus_code` varchar(10) DEFAULT NULL,
  `chap_chidon_type` enum('','boys','girls') DEFAULT NULL,
  `year` varchar(4) NOT NULL,
  `chidon_type` enum('boys','girls') DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_teams`
--

DROP TABLE IF EXISTS `th_chidon_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_teams` (
  `team_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_tests`
--

DROP TABLE IF EXISTS `th_chidon_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_tests` (
  `test_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(30) NOT NULL,
  `staff_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`test_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_chidon_walking_groups`
--

DROP TABLE IF EXISTS `th_chidon_walking_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_chidon_walking_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(30) NOT NULL,
  `staff_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `th_donor_list`
--

DROP TABLE IF EXISTS `th_donor_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `th_donor_list` (
  `th_donor_list_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `assigned` varchar(45) NOT NULL,
  `pledged` decimal(10,2) DEFAULT NULL,
  `donated` decimal(10,2) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`th_donor_list_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thumbs`
--

DROP TABLE IF EXISTS `thumbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thumbs` (
  `file_id` int(10) unsigned NOT NULL,
  `thumb` varchar(45) NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `todo_categories`
--

DROP TABLE IF EXISTS `todo_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todo_categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` int(10) unsigned DEFAULT NULL,
  `category_id_old` int(10) unsigned NOT NULL,
  `category_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `todo_list`
--

DROP TABLE IF EXISTS `todo_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todo_list` (
  `todo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recip` enum('school','class','team','user','end_user') COLLATE utf8_unicode_ci NOT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  `recip_id` int(10) unsigned DEFAULT NULL,
  `todo_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `todo_priority` enum('Urgent','High','Medium','Low') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Medium',
  `category_id` int(10) unsigned NOT NULL,
  `todo_due_date` mediumint(8) unsigned DEFAULT NULL,
  `todo_file_id` int(10) unsigned DEFAULT NULL,
  `todo_url` varchar(2048) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `visibility` enum('all','none') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'all',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`todo_id`),
  KEY `sub_id` (`category_id`),
  KEY `recip` (`recip`,`school_id`,`recip_id`)
) ENGINE=InnoDB AUTO_INCREMENT=634 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `todo_list_marks`
--

DROP TABLE IF EXISTS `todo_list_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todo_list_marks` (
  `todo_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user','end_user') COLLATE utf8_unicode_ci NOT NULL,
  `mark_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`todo_id`,`auth`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tracking_numbers`
--

DROP TABLE IF EXISTS `tracking_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracking_numbers` (
  `tracking_number_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shipment_id` int(10) unsigned DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `provider` enum('UPS','USPS','Amazon') DEFAULT NULL,
  PRIMARY KEY (`tracking_number_id`)
) ENGINE=InnoDB AUTO_INCREMENT=331 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tracks`
--

DROP TABLE IF EXISTS `tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracks` (
  `track_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `track_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`track_id`),
  UNIQUE KEY `track_name` (`track_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `trans_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `school_id` int(10) DEFAULT NULL,
  `trans_date` timestamp NULL DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `response` text COLLATE utf8_unicode_ci NOT NULL,
  `reg_amount` int(10) unsigned DEFAULT NULL,
  `ship_amount` int(10) unsigned DEFAULT NULL,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `users_registered` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`trans_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13967 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `translations_text`
--

DROP TABLE IF EXISTS `translations_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations_text` (
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `text_transl` text COLLATE utf8_unicode_ci NOT NULL,
  KEY `lang` (`lang`,`text`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `translations_varchar`
--

DROP TABLE IF EXISTS `translations_varchar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations_varchar` (
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `text_transl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`lang`,`text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_add_ons`
--

DROP TABLE IF EXISTS `user_add_ons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_add_ons` (
  `user_add_on_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `school_add_on_id` int(10) unsigned NOT NULL,
  `size` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shipped` date DEFAULT NULL,
  `received` date DEFAULT NULL,
  PRIMARY KEY (`user_add_on_id`),
  UNIQUE KEY `user_id` (`user_id`,`school_add_on_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5963 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_codes`
--

DROP TABLE IF EXISTS `user_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_codes` (
  `user_id` int(10) unsigned NOT NULL,
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `code_id_prefix` tinyint(1) unsigned NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `grant_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`admin_id`,`code_id`,`code_id_prefix`),
  KEY `admin_id` (`admin_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_images`
--

DROP TABLE IF EXISTS `user_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_images` (
  `file_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `file_content_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_last_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `file_data` longblob,
  `file_path` char(11) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_mission_entries`
--

DROP TABLE IF EXISTS `user_mission_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_mission_entries` (
  `user_id` int(10) unsigned NOT NULL,
  `code_id` bigint(19) unsigned DEFAULT NULL,
  `entry_id` int(10) unsigned NOT NULL,
  `entry_type` enum('date_tasks_missions','chain_missions') COLLATE utf8_unicode_ci NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`entry_type`,`entry_id`),
  UNIQUE KEY `code_id` (`code_id`),
  KEY `subject_id` (`subject_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_missions`
--

DROP TABLE IF EXISTS `user_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_missions` (
  `user_id` int(10) unsigned NOT NULL,
  `mission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`mission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_registration`
--

DROP TABLE IF EXISTS `user_registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_registration` (
  `user_reg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid` decimal(4,2) unsigned DEFAULT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_reg_id`),
  UNIQUE KEY `user` (`user_id`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=12162 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_sefer_hamitzvos`
--

DROP TABLE IF EXISTS `user_sefer_hamitzvos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sefer_hamitzvos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `mission` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1720 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_store_withdraw`
--

DROP TABLE IF EXISTS `user_store_withdraw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_store_withdraw` (
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `print_date` timestamp NULL DEFAULT NULL,
  `scan_date` timestamp NULL DEFAULT NULL,
  `points` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`code_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_task_exceptions`
--

DROP TABLE IF EXISTS `user_task_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_task_exceptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date_task_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Key` (`user_id`,`date_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31003686 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_task_info`
--

DROP TABLE IF EXISTS `user_task_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_task_info` (
  `date_task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mandatory` tinyint(1) NOT NULL,
  PRIMARY KEY (`date_task_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_tasks`
--

DROP TABLE IF EXISTS `user_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tasks` (
  `user_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_tracks`
--

DROP TABLE IF EXISTS `user_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tracks` (
  `user_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `track_id` int(10) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `enrolled` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`user_id`,`subject_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_withdraw`
--

DROP TABLE IF EXISTS `user_withdraw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_withdraw` (
  `user_id` int(10) unsigned NOT NULL,
  `code_id` bigint(9) unsigned zerofill NOT NULL,
  `print_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `scan_date` timestamp NULL DEFAULT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `jul_print_date` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`code_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_yearly_gift`
--

DROP TABLE IF EXISTS `user_yearly_gift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_yearly_gift` (
  `user_id` int(11) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `marked` tinyint(4) DEFAULT '0',
  UNIQUE KEY `noDuplicates` (`user_id`,`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_yearly_raffle`
--

DROP TABLE IF EXISTS `user_yearly_raffle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_yearly_raffle` (
  `user_id` int(10) unsigned NOT NULL,
  `days` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  UNIQUE KEY `noDuplicates` (`user_id`,`year`),
  CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_code` bigint(19) unsigned zerofill NOT NULL,
  `username` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `first` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `last` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `first_he` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `last_he` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `lang` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'english',
  `school_type_id` int(10) unsigned NOT NULL,
  `school_id` int(10) DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `team_id` int(10) unsigned DEFAULT NULL,
  `user_serial` mediumint(8) unsigned NOT NULL,
  `fee_id` int(10) unsigned DEFAULT NULL,
  `user_address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_postal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gender` enum('M','F') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_start_date` mediumint(8) unsigned DEFAULT NULL,
  `user_registered` timestamp NULL DEFAULT NULL,
  `camp_registered` timestamp NULL DEFAULT NULL,
  `user_registration_fee` decimal(6,2) unsigned DEFAULT NULL,
  `user_notes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `dob_he_offset` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dob_he` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_photo_id` int(10) unsigned DEFAULT NULL,
  `kiosk_edit` enum('','off','frozen') COLLATE utf8_unicode_ci NOT NULL,
  `camp_id` int(10) DEFAULT NULL,
  `add_on_one` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `add_on_two` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `child_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `shirt_size` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `cc_ref` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `been_added` tinyint(1) NOT NULL DEFAULT '0',
  `image_added` tinyint(1) NOT NULL DEFAULT '0',
  `parent_marking` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `big_prizes_won` int(10) unsigned NOT NULL DEFAULT '0',
  `small_prizes_won` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '1',
  `pic_mission_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `he_name` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `he_name_conf` tinyint(4) DEFAULT '0',
  `mobile_pic` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pushka` tinyint(4) DEFAULT '0',
  `chayolei` tinyint(3) NOT NULL DEFAULT '1',
  `yan` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `chidon` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `allow_parent_tasks` tinyint(3) DEFAULT '1',
  `print_parent_tasks` tinyint(3) DEFAULT '0',
  `reg_paid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_code` (`user_code`),
  UNIQUE KEY `user_serial` (`user_serial`),
  KEY `team_id` (`school_id`,`team_id`),
  KEY `school_type_id` (`school_type_id`),
  KEY `user_start_date` (`user_start_date`),
  KEY `school_id` (`school_id`,`user_registered`),
  KEY `class_id` (`class_id`,`school_id`,`user_registered`),
  KEY `cc_ref` (`cc_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=55510 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_bak`
--

DROP TABLE IF EXISTS `users_bak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_bak` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_code` bigint(19) unsigned zerofill NOT NULL,
  `username` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `first` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `last` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `first_he` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `last_he` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `lang` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'english',
  `school_type_id` int(10) unsigned NOT NULL,
  `school_id` int(10) DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `team_id` int(10) unsigned DEFAULT NULL,
  `user_serial` mediumint(8) unsigned NOT NULL,
  `fee_id` int(10) unsigned DEFAULT NULL,
  `user_address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_postal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gender` enum('M','F') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_start_date` mediumint(8) unsigned DEFAULT NULL,
  `user_registered` timestamp NULL DEFAULT NULL,
  `camp_registered` timestamp NULL DEFAULT NULL,
  `user_registration_fee` decimal(6,2) unsigned DEFAULT NULL,
  `user_notes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `dob_he_offset` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dob_he` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_photo_id` int(10) unsigned DEFAULT NULL,
  `kiosk_edit` enum('','off','frozen') COLLATE utf8_unicode_ci NOT NULL,
  `camp_id` int(10) DEFAULT NULL,
  `add_on_one` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `add_on_two` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `child_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `shirt_size` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `cc_ref` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `been_added` tinyint(1) NOT NULL DEFAULT '0',
  `image_added` tinyint(1) NOT NULL DEFAULT '0',
  `parent_marking` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `big_prizes_won` int(10) unsigned NOT NULL DEFAULT '0',
  `small_prizes_won` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '1',
  `pic_mission_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `he_name` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `he_name_conf` tinyint(4) DEFAULT '0',
  `mobile_pic` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pushka` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_code` (`user_code`),
  UNIQUE KEY `user_serial` (`user_serial`),
  KEY `team_id` (`school_id`,`team_id`),
  KEY `school_type_id` (`school_type_id`),
  KEY `user_start_date` (`user_start_date`),
  KEY `school_id` (`school_id`,`user_registered`),
  KEY `class_id` (`class_id`,`school_id`,`user_registered`),
  KEY `cc_ref` (`cc_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=50694 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `weekly_emails`
--

DROP TABLE IF EXISTS `weekly_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weekly_emails` (
  `email_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `subject` varchar(100) NOT NULL,
  `email` text NOT NULL,
  PRIMARY KEY (`email_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `yearly_prize_shipping`
--

DROP TABLE IF EXISTS `yearly_prize_shipping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yearly_prize_shipping` (
  `id` int(10) unsigned NOT NULL,
  `type` varchar(10) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `shipped` tinyint(3) unsigned DEFAULT NULL,
  `distributed` tinyint(4) DEFAULT '0',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_id_type_index` (`id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `medals_subjects_totals`
--

/*!50001 DROP TABLE IF EXISTS `medals_subjects_totals`*/;
/*!50001 DROP VIEW IF EXISTS `medals_subjects_totals`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mashpia`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `medals_subjects_totals` AS select `medals_subjects`.`subject_id` AS `subject_id`,`medals_subjects`.`medal_ord` AS `medal_ord`,`medals_subjects`.`medal_on_image_id` AS `medal_on_image_id`,`medals_subjects`.`medal_off_image_id` AS `medal_off_image_id`,`medals_subjects`.`profile_photo_id` AS `profile_photo_id`,`medals_subjects`.`missions_required` AS `missions_required`,(select sum(`medals_total`.`missions_required`) AS `SUM(missions_required)` from `medals_subjects` `medals_total` where ((`medals_total`.`subject_id` = `medals_subjects`.`subject_id`) and (`medals_total`.`medal_ord` <= `medals_subjects`.`medal_ord`))) AS `missions_required_total` from `medals_subjects` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `student_images`
--

/*!50001 DROP TABLE IF EXISTS `student_images`*/;
/*!50001 DROP VIEW IF EXISTS `student_images`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mashpia`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `student_images` AS select `u`.`user_id` AS `user_id`,`f`.`file_content_type` AS `photo_type`,`f`.`file_data` AS `photo` from (`users` `u` join `files` `f` on((`u`.`user_photo_id` = `f`.`file_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-11 13:40:35
