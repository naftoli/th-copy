-- MySQL dump 10.11
--
-- Host: localhost    Database: mashpia
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny2-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES UTF8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_auths`
--

DROP TABLE IF EXISTS `admin_auths`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `admin_auths` (
  `admin_id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user') collate utf8_unicode_ci NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`admin_id`,`auth`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `admins` (
  `admin_id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(64) collate utf8_unicode_ci NOT NULL,
  `auth` enum('','inactive','super') collate utf8_unicode_ci NOT NULL,
  `password` varchar(64) character set utf8 collate utf8_bin NOT NULL,
  `title` enum('','Rabbi','Mr.','Mrs.','Ms.') collate utf8_unicode_ci NOT NULL,
  `first` varchar(128) collate utf8_unicode_ci NOT NULL,
  `last` varchar(128) collate utf8_unicode_ci NOT NULL,
  `lang` char(2) collate utf8_unicode_ci NOT NULL,
  `admin_address1` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_address2` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_city` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_state` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_postal` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_country` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_phone_work` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_phone_home` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_phone_mobile` varchar(255) collate utf8_unicode_ci NOT NULL,
  `admin_email` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auction_prizes`
--

DROP TABLE IF EXISTS `auction_prizes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auction_prizes` (
  `auction_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `available` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`auction_id`,`prize_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auction_user_prizes`
--

DROP TABLE IF EXISTS `auction_user_prizes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auction_user_prizes` (
  `auction_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`auction_id`,`prize_id`,`user_id`),
  KEY `user_id` (`user_id`,`auction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auction_user_prizes_bak`
--

DROP TABLE IF EXISTS `auction_user_prizes_bak`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auction_user_prizes_bak` (
  `auction_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auction_winners`
--

DROP TABLE IF EXISTS `auction_winners`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auction_winners` (
  `auction_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`auction_id`,`user_id`,`prize_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auctions`
--

DROP TABLE IF EXISTS `auctions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auctions` (
  `auction_id` int(10) unsigned NOT NULL auto_increment,
  `school_id` int(10) unsigned default NULL,
  `auction_date` mediumint(8) unsigned NOT NULL,
  `auction_run_date` mediumint(8) unsigned default NULL,
  `auction_message` varchar(255) collate utf8_unicode_ci NOT NULL,
  `max_prize_points` int(10) unsigned default NULL,
  `auction_ran` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`auction_id`),
  UNIQUE KEY `auction_date` (`auction_date`,`school_id`),
  KEY `school_id` (`school_id`,`auction_ran`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chain_items`
--

DROP TABLE IF EXISTS `chain_items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `chain_items` (
  `chain_item_id` int(10) unsigned NOT NULL auto_increment,
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `floor` tinyint(4) NOT NULL,
  `room` tinyint(4) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `mandatory_qty` smallint(5) unsigned NOT NULL default '0',
  `optional_qty` smallint(5) unsigned NOT NULL default '0',
  `label_id` int(10) unsigned default NULL,
  `quantity` smallint(5) unsigned default NULL,
  `points` decimal(6,2) unsigned NOT NULL default '0.00',
  PRIMARY KEY  (`chain_item_id`),
  UNIQUE KEY `chain_id` (`school_type_id`,`subject_id`,`level`,`track_id`,`floor`,`room`),
  KEY `label_id` (`label_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chain_marks`
--

DROP TABLE IF EXISTS `chain_marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `chain_marks` (
  `chain_item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL default '0',
  `skipped_qty` smallint(5) unsigned NOT NULL default '0',
  `override_qty` smallint(5) unsigned NOT NULL default '0',
  `mark_description` text collate utf8_unicode_ci NOT NULL,
  `mark_points` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`,`mark_date`,`chain_item_id`),
  KEY `chain_item_id` (`chain_item_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chain_missions`
--

DROP TABLE IF EXISTS `chain_missions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `chain_missions` (
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `floor` tinyint(3) unsigned NOT NULL,
  `mission_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `mission_description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`school_type_id`,`subject_id`,`level`,`track_id`,`floor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `classes` (
  `class_id` int(10) unsigned NOT NULL auto_increment,
  `school_id` int(10) unsigned NOT NULL,
  `class_grade` enum('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12') collate utf8_unicode_ci NOT NULL,
  `class_sub` varchar(255) collate utf8_unicode_ci NOT NULL,
  `class_teacher` varchar(255) collate utf8_unicode_ci NOT NULL,
  `default_level` tinyint(3) unsigned NOT NULL,
  `class_era` smallint(5) unsigned NOT NULL default '0' COMMENT 'Hebrew Year when class was active (0 for current)',
  PRIMARY KEY  (`class_id`),
  UNIQUE KEY `school_id` (`school_id`,`class_id`),
  UNIQUE KEY `class_grade` (`school_id`,`class_era`,`class_grade`,`class_sub`),
  KEY `class_era` (`class_era`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks`
--

DROP TABLE IF EXISTS `date_tasks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks` (
  `date_task_id` int(10) unsigned NOT NULL auto_increment,
  `date_tasks_mission_id` int(10) unsigned NOT NULL,
  `ord` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `mandatory_qty` smallint(5) unsigned NOT NULL default '0',
  `optional_qty` smallint(5) unsigned NOT NULL default '0',
  `is_bonus` tinyint(3) unsigned NOT NULL default '0',
  `label_id` int(10) unsigned default NULL,
  `quantity` smallint(5) unsigned default NULL,
  `points` decimal(6,2) unsigned NOT NULL default '0.00',
  PRIMARY KEY  (`date_task_id`),
  KEY `date_tasks_mission_id` (`date_tasks_mission_id`,`ord`,`name`),
  KEY `label_id` (`label_id`,`date_tasks_mission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks_dates`
--

DROP TABLE IF EXISTS `date_tasks_dates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks_dates` (
  `date_task_id` int(10) unsigned NOT NULL,
  `nominal_date` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`date_task_id`,`nominal_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks_marks`
--

DROP TABLE IF EXISTS `date_tasks_marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks_marks` (
  `date_task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL default '0',
  `mark_description` text collate utf8_unicode_ci NOT NULL,
  `mark_points` decimal(8,2) unsigned NOT NULL default '0.00',
  `mark_quantity` int(10) unsigned NOT NULL default '0',
  `mark_inactive` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`date_task_id`),
  UNIQUE KEY `date_task_id` (`date_task_id`,`user_id`),
  KEY `mark_date` (`mark_date`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks_marks_bak`
--

DROP TABLE IF EXISTS `date_tasks_marks_bak`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks_marks_bak` (
  `date_task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL default '0',
  `mark_description` text collate utf8_unicode_ci NOT NULL,
  `mark_points` decimal(8,2) unsigned NOT NULL default '0.00',
  `mark_quantity` int(10) unsigned NOT NULL default '0',
  `mark_inactive` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`mark_date`,`date_task_id`),
  KEY `date_task_id` (`date_task_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks_mission_codes`
--

DROP TABLE IF EXISTS `date_tasks_mission_codes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks_mission_codes` (
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `mission_number` decimal(3,1) unsigned NOT NULL,
  `include_bonus_task` tinyint(3) unsigned NOT NULL default '0',
  `expiration_date` date NOT NULL,
  PRIMARY KEY  (`code_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks_mission_marks`
--

DROP TABLE IF EXISTS `date_tasks_mission_marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks_mission_marks` (
  `user_id` int(10) unsigned NOT NULL,
  `date_tasks_mission_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `mission_value` decimal(7,1) unsigned NOT NULL,
  `mission_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`,`date_tasks_mission_id`),
  KEY `subject_id` (`user_id`,`subject_id`,`mark_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks_missions`
--

DROP TABLE IF EXISTS `date_tasks_missions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks_missions` (
  `date_tasks_mission_id` int(10) unsigned NOT NULL auto_increment,
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `mission_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `mission_number` decimal(3,1) unsigned default NULL,
  `mission_description` text collate utf8_unicode_ci NOT NULL,
  `mission_value` decimal(5,1) unsigned NOT NULL default '1.0',
  `start_date` mediumint(8) unsigned NOT NULL,
  `end_date` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`date_tasks_mission_id`),
  UNIQUE KEY `mission_number` (`mission_number`,`school_type_id`,`subject_id`,`level`,`track_id`),
  KEY `date` (`school_type_id`,`subject_id`,`level`,`track_id`,`start_date`,`end_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `date_tasks_missions_printed`
--

DROP TABLE IF EXISTS `date_tasks_missions_printed`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `date_tasks_missions_printed` (
  `user_id` int(10) unsigned NOT NULL,
  `date_tasks_mission_id` int(10) unsigned NOT NULL,
  `date_printed` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`date_tasks_mission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `files` (
  `file_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `file_content_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `file_last_mod` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `file_data` longblob NOT NULL,
  PRIMARY KEY  (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `goals`
--

DROP TABLE IF EXISTS `goals`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `goals` (
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `goal_start` varchar(255) collate utf8_unicode_ci NOT NULL,
  `goal_end` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`school_type_id`,`subject_id`,`level`,`track_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `institutions`
--

DROP TABLE IF EXISTS `institutions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `institutions` (
  `inst_id` int(10) unsigned NOT NULL auto_increment,
  `inst_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `inst_logo_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`inst_id`),
  UNIQUE KEY `inst_name` (`inst_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `invitations` (
  `invitation_id` bigint(19) unsigned zerofill NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user') collate utf8_unicode_ci NOT NULL,
  `role_id` int(11) default NULL,
  `admin_id` int(10) unsigned NOT NULL COMMENT 'inviter',
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `invitation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`invitation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `invoice_items` (
  `school_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL auto_increment,
  `item_price` decimal(8,2) NOT NULL,
  `item_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `item_ref_type` enum('school_packages','school_package_fees','payment','charge','credit','note') collate utf8_unicode_ci default NULL,
  `item_ref_id` int(10) unsigned default NULL,
  `item_description` varchar(512) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`school_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `labels`
--

DROP TABLE IF EXISTS `labels`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `labels` (
  `label_id` int(10) unsigned NOT NULL auto_increment,
  `label_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `label_description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `label_image_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`label_id`),
  UNIQUE KEY `label_name` (`label_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `marks`
--

DROP TABLE IF EXISTS `marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `marks` (
  `task_id` int(10) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `mark_description` text collate utf8_unicode_ci NOT NULL,
  `mark_level` tinyint(3) unsigned NOT NULL,
  `mark_track_id` int(10) unsigned default NULL,
  `mark_points` decimal(6,2) unsigned NOT NULL,
  `mark_quantity` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`user_id`,`mark_date`,`task_id`),
  KEY `task_id` (`task_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `medal_marks`
--

DROP TABLE IF EXISTS `medal_marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `medal_marks` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_awarded` mediumint(8) unsigned NOT NULL,
  `date_received` timestamp NULL default NULL,
  PRIMARY KEY  (`medal_ord`,`subject_id`,`user_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`medal_ord`,`user_id`),
  UNIQUE KEY `user_id` (`user_id`,`subject_id`,`medal_ord`),
  KEY `date_awarded` (`date_awarded`),
  KEY `date_received` (`date_received`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `medals`
--

DROP TABLE IF EXISTS `medals`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `medals` (
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `medal_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `medal_on_image_id` int(10) unsigned default NULL,
  `medal_off_image_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`medal_ord`),
  UNIQUE KEY `medal_name` (`medal_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `medals_subjects`
--

DROP TABLE IF EXISTS `medals_subjects`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `medals_subjects` (
  `subject_id` int(10) unsigned NOT NULL,
  `medal_ord` tinyint(3) unsigned NOT NULL,
  `medal_on_image_id` int(10) unsigned default NULL,
  `medal_off_image_id` int(10) unsigned default NULL,
  `medal_photo_id` int(10) unsigned default NULL,
  `missions_required` decimal(7,1) unsigned NOT NULL,
  `profile_photo_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`subject_id`,`medal_ord`),
  UNIQUE KEY `medal_ord` (`medal_ord`,`subject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `medals_subjects_totals`
--

DROP TABLE IF EXISTS `medals_subjects_totals`;
/*!50001 DROP VIEW IF EXISTS `medals_subjects_totals`*/;
/*!50001 CREATE TABLE `medals_subjects_totals` (
  `subject_id` int(10) unsigned,
  `medal_ord` tinyint(3) unsigned,
  `medal_on_image_id` int(10) unsigned,
  `medal_off_image_id` int(10) unsigned,
  `profile_photo_id` int(10) unsigned,
  `missions_required` decimal(7,1) unsigned,
  `missions_required_total` decimal(29,1)
) */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `messages` (
  `message_type` enum('report1','report2','base_mission','th_to_soldier','hakhel_directives_1','hakhel_directives_2') collate utf8_unicode_ci NOT NULL,
  `message_text` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`message_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `mission_active`
--

DROP TABLE IF EXISTS `mission_active`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `mission_active` (
  `mission_id` int(10) unsigned NOT NULL,
  `school_type_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`mission_id`,`school_type_id`,`level`,`track_id`),
  UNIQUE KEY `school_type_id` (`school_type_id`,`level`,`track_id`,`mission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `mission_tasks`
--

DROP TABLE IF EXISTS `mission_tasks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `mission_tasks` (
  `mission_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  `reps` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mission_id`,`task_id`),
  UNIQUE KEY `task_id` (`task_id`,`mission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `missions` (
  `mission_id` int(10) unsigned NOT NULL auto_increment,
  `mission_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `mission_points` decimal(8,2) unsigned NOT NULL default '0.00',
  `subject_id` int(10) unsigned NOT NULL,
  `start_month` tinyint(2) unsigned NOT NULL,
  `start_day` tinyint(2) unsigned NOT NULL,
  `end_month` tinyint(2) unsigned NOT NULL,
  `end_day` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY  (`mission_id`),
  UNIQUE KEY `mission_name` (`subject_id`,`mission_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ord`
--

DROP TABLE IF EXISTS `ord`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ord` (
  `num` smallint(5) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `points`
--

DROP TABLE IF EXISTS `points`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `points` (
  `user_id` int(10) unsigned NOT NULL,
  `award_date` mediumint(8) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `award_points` decimal(8,2) unsigned NOT NULL,
  `award_left_circle` char(1) collate utf8_unicode_ci NOT NULL,
  `award_right_circle` char(1) collate utf8_unicode_ci NOT NULL,
  `award_description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `award_series` tinyint(3) unsigned default NULL,
  PRIMARY KEY  (`user_id`,`subject_id`,`award_date`),
  KEY `award_date` (`user_id`,`award_date`),
  KEY `award_series` (`user_id`,`subject_id`,`award_series`,`award_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `points_codes`
--

DROP TABLE IF EXISTS `points_codes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `points_codes` (
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `left_circle` char(1) collate utf8_unicode_ci NOT NULL,
  `right_circle` char(1) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `series` tinyint(3) unsigned default NULL,
  `expiration_date` date NOT NULL,
  PRIMARY KEY  (`code_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `points_codes_templates`
--

DROP TABLE IF EXISTS `points_codes_templates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `points_codes_templates` (
  `points_codes_template_id` int(10) unsigned NOT NULL auto_increment,
  `school_id` int(10) unsigned default NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  `left_circle` char(1) collate utf8_unicode_ci NOT NULL,
  `right_circle` char(1) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `series` tinyint(3) unsigned default NULL,
  PRIMARY KEY  (`points_codes_template_id`),
  KEY `school_id` (`school_id`,`subject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `prizes_auction`
--

DROP TABLE IF EXISTS `prizes_auction`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `prizes_auction` (
  `prize_id` int(10) unsigned NOT NULL auto_increment,
  `school_id` int(10) unsigned default NULL,
  `prize_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `prize_number` mediumint(8) unsigned default NULL,
  `prize_description` text collate utf8_unicode_ci NOT NULL,
  `prize_points` int(10) unsigned NOT NULL,
  `prize_ratio` smallint(5) unsigned NOT NULL,
  `prize_image_id` int(10) unsigned default NULL,
  `min_grade` enum('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12') collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`prize_id`),
  UNIQUE KEY `prize_number` (`prize_number`),
  UNIQUE KEY `prize_name` (`prize_name`,`school_id`),
  KEY `prize_points` (`prize_points`,`prize_name`),
  KEY `school_id` (`school_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `prizes_store`
--

DROP TABLE IF EXISTS `prizes_store`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `prizes_store` (
  `prize_id` int(10) unsigned NOT NULL auto_increment,
  `school_id` int(10) unsigned default NULL,
  `prize_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `prize_description` text collate utf8_unicode_ci NOT NULL,
  `prize_points` int(10) unsigned NOT NULL,
  `prize_available` smallint(5) unsigned default NULL,
  `prize_image_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`prize_id`),
  UNIQUE KEY `prize_name` (`prize_name`,`school_id`),
  KEY `prize_points` (`prize_points`,`prize_name`),
  KEY `school_id` (`school_id`,`prize_available`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `rank_marks`
--

DROP TABLE IF EXISTS `rank_marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `rank_marks` (
  `rank_ord` tinyint(3) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_promoted` mediumint(8) unsigned NOT NULL,
  `date_printed` timestamp NULL default NULL,
  `date_book_received` timestamp NULL default NULL,
  `date_card_received` timestamp NULL default NULL,
  PRIMARY KEY  (`rank_ord`,`user_id`),
  UNIQUE KEY `user_id` (`user_id`,`rank_ord`),
  KEY `date_promoted` (`date_promoted`),
  KEY `date_printed` (`date_printed`),
  KEY `date_received` (`date_book_received`),
  KEY `date_card_received` (`date_card_received`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ranks`
--

DROP TABLE IF EXISTS `ranks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ranks` (
  `rank_ord` tinyint(3) unsigned NOT NULL auto_increment,
  `rank_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `medals_required` tinyint(3) unsigned NOT NULL,
  `rank_image_id` int(10) unsigned default NULL,
  `rank_color` varchar(32) character set ascii collate ascii_bin NOT NULL,
  `rank_background_image_id` int(10) unsigned default NULL,
  `prof_rank_image_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`rank_ord`),
  UNIQUE KEY `rank_name` (`rank_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `report_marks`
--

DROP TABLE IF EXISTS `report_marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `report_marks` (
  `report_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user','end_user') collate utf8_unicode_ci NOT NULL,
  `print_date` timestamp NULL default NULL,
  `process_date` timestamp NULL default NULL,
  PRIMARY KEY  (`report_id`,`auth`,`id`),
  UNIQUE KEY `id` (`auth`,`id`,`report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `report_subjects`
--

DROP TABLE IF EXISTS `report_subjects`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `report_subjects` (
  `report_type` enum('mission_cover_sheet') collate utf8_unicode_ci NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `image_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`report_type`,`subject_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`report_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='For reports of more than one subject at a time';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `reports` (
  `report_id` int(10) unsigned NOT NULL auto_increment,
  `report_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `report_type` enum('','WWTC','Hakhel','Auction','mission_cover_sheet') collate utf8_unicode_ci NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `start_date` mediumint(8) unsigned NOT NULL,
  `end_date` mediumint(8) unsigned NOT NULL,
  `visibility` enum('all','process','none') collate utf8_unicode_ci NOT NULL default 'all',
  PRIMARY KEY  (`report_id`),
  KEY `creation_date` (`creation_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `roles` (
  `role_id` int(10) unsigned NOT NULL auto_increment,
  `role_auth` enum('school','class','team','user') collate utf8_unicode_ci NOT NULL,
  `role_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`role_id`),
  UNIQUE KEY `role_auth` (`role_auth`,`role_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `school_makeups`
--

DROP TABLE IF EXISTS `school_makeups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `school_makeups` (
  `school_makeup_id` int(10) unsigned NOT NULL auto_increment,
  `school_makeup_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`school_makeup_id`),
  UNIQUE KEY `school_makeup_name` (`school_makeup_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `school_package_fees`
--

DROP TABLE IF EXISTS `school_package_fees`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `school_package_fees` (
  `package_id` int(10) unsigned NOT NULL,
  `fee_id` int(10) unsigned NOT NULL auto_increment,
  `fee_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `fee_each` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY  (`fee_id`),
  KEY `package_id` (`package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `school_packages`
--

DROP TABLE IF EXISTS `school_packages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `school_packages` (
  `package_id` int(10) unsigned NOT NULL auto_increment,
  `package_ord` tinyint(3) unsigned NOT NULL,
  `package_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `package_description` text collate utf8_unicode_ci NOT NULL,
  `fee` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY  (`package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `school_subjects`
--

DROP TABLE IF EXISTS `school_subjects`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `school_subjects` (
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`school_id`,`subject_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`school_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `school_type_subjects`
--

DROP TABLE IF EXISTS `school_type_subjects`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `school_type_subjects` (
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`school_type_id`,`subject_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`school_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `school_types`
--

DROP TABLE IF EXISTS `school_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `school_types` (
  `school_type_id` int(10) unsigned NOT NULL auto_increment,
  `school_type_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_type_setting` enum('managed','managed_personal','self_managed','personal_only') collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`school_type_id`),
  UNIQUE KEY `school_type_name` (`school_type_name`),
  KEY `school_type_setting` (`school_type_setting`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `schools` (
  `school_id` int(10) unsigned NOT NULL auto_increment,
  `school_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_name_he` varchar(255) collate utf8_unicode_ci NOT NULL,
  `inst_id` int(10) unsigned NOT NULL,
  `school_makeup_id` int(10) unsigned NOT NULL,
  `school_settings` set('home_school') collate utf8_unicode_ci NOT NULL,
  `package_id` int(10) unsigned default NULL,
  `school_gender` enum('M','F','B') collate utf8_unicode_ci NOT NULL,
  `school_number` mediumint(8) unsigned NOT NULL,
  `school_logo_id` int(10) unsigned default NULL,
  `school_logo_kiosk_id` int(10) unsigned default NULL,
  `school_no_logo` tinyint(1) NOT NULL default '0',
  `school_file_id` int(10) unsigned default NULL,
  `school_address1` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_address2` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_city` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_state` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_country` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_postal` varchar(255) collate utf8_unicode_ci NOT NULL,
  `school_phone` varchar(255) collate utf8_unicode_ci NOT NULL,
  `cc_number` varchar(19) character set ascii collate ascii_bin NOT NULL,
  `cc_exp` varchar(5) character set ascii collate ascii_bin NOT NULL,
  `cc_cvv` varchar(4) character set ascii collate ascii_bin NOT NULL,
  `kiosk_print` tinyint(1) unsigned NOT NULL default '1',
  `school_era` smallint(5) unsigned default NULL,
  `shipping_method` enum('pickup','deliver') collate utf8_unicode_ci NOT NULL,
  `shipping_first` varchar(128) collate utf8_unicode_ci NOT NULL,
  `shipping_last` varchar(128) collate utf8_unicode_ci NOT NULL,
  `shipping_phone` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shipping_address1` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shipping_address2` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shipping_city` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shipping_state` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shipping_postal` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shipping_country` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`school_id`),
  UNIQUE KEY `school_name` (`inst_id`,`school_name`),
  UNIQUE KEY `school_number` (`school_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `store_purchases`
--

DROP TABLE IF EXISTS `store_purchases`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `store_purchases` (
  `store_purchase_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `prize_id` int(10) unsigned NOT NULL,
  `prize_shipped` tinyint(1) unsigned NOT NULL default '0',
  `prize_points` int(10) unsigned NOT NULL,
  `prize_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`store_purchase_id`),
  KEY `user_id` (`user_id`,`prize_shipped`,`prize_date`),
  KEY `prize_shipped` (`prize_shipped`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subjects` (
  `subject_id` int(10) unsigned NOT NULL auto_increment,
  `subject_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `inst_id` int(10) unsigned NOT NULL,
  `subject_type` enum('','goal_hist','WWTC','Hakhel','school_points','home_points','Tanya') collate utf8_unicode_ci NOT NULL,
  `subject_image_id` int(10) unsigned default NULL,
  `prof_image_id` int(10) unsigned default NULL,
  `black_image_id` int(10) unsigned default NULL,
  `slogan` tinytext collate utf8_unicode_ci,
  `description` tinytext collate utf8_unicode_ci,
  `commitments` tinytext collate utf8_unicode_ci,
  PRIMARY KEY  (`subject_id`),
  UNIQUE KEY `subject_name` (`inst_id`,`subject_name`),
  KEY `subject_type` (`subject_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tanya_goals`
--

DROP TABLE IF EXISTS `tanya_goals`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tanya_goals` (
  `track` tinyint(3) unsigned NOT NULL,
  `lines_goal` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`track`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tanya_lines`
--

DROP TABLE IF EXISTS `tanya_lines`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tanya_lines` (
  `line` smallint(5) unsigned NOT NULL,
  `page` smallint(5) unsigned NOT NULL,
  `perek` char(4) collate utf8_unicode_ci NOT NULL,
  `text` varchar(4096) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`line`),
  UNIQUE KEY `page` (`page`,`line`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tanya_medal_cards`
--

DROP TABLE IF EXISTS `tanya_medal_cards`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tanya_medal_cards` (
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `medal_stage` tinyint(3) unsigned NOT NULL,
  `expiration_date` date NOT NULL,
  PRIMARY KEY  (`code_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tanya_users`
--

DROP TABLE IF EXISTS `tanya_users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tanya_users` (
  `user_id` int(10) unsigned NOT NULL,
  `track` tinyint(3) unsigned NOT NULL default '1',
  `year` tinyint(3) unsigned NOT NULL default '1',
  `lines_done` smallint(5) unsigned NOT NULL default '0',
  `lines_offset` smallint(5) unsigned NOT NULL default '0',
  `medal_ord` decimal(4,2) unsigned NOT NULL default '0.00',
  `tanya_start_date` mediumint(8) unsigned NOT NULL,
  `length_days` smallint(5) unsigned NOT NULL,
  `length_days_offset` smallint(5) unsigned NOT NULL,
  `pledges` decimal(8,2) unsigned NOT NULL default '0.00',
  `collected` decimal(8,2) unsigned NOT NULL default '0.00',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tanya_users2`
--

DROP TABLE IF EXISTS `tanya_users2`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tanya_users2` (
  `user_id` int(10) unsigned NOT NULL,
  `track` tinyint(3) unsigned NOT NULL default '1',
  `year` tinyint(3) unsigned NOT NULL default '1',
  `lines_done` smallint(5) unsigned NOT NULL default '0',
  `lines_offset` smallint(5) unsigned NOT NULL default '0',
  `medal_ord` decimal(4,2) unsigned NOT NULL default '0.00',
  `tanya_start_date` mediumint(8) unsigned NOT NULL,
  `length_days` smallint(5) unsigned NOT NULL,
  `length_days_offset` smallint(5) unsigned NOT NULL,
  `pledges` decimal(8,2) NOT NULL default '0.00',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `task_active`
--

DROP TABLE IF EXISTS `task_active`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `task_active` (
  `task_id` int(10) unsigned NOT NULL,
  `school_type_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `points` decimal(6,2) unsigned NOT NULL default '0.00',
  `description` text collate utf8_unicode_ci NOT NULL,
  `quantity` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`task_id`,`school_type_id`,`track_id`,`level`),
  UNIQUE KEY `school_type_id` (`school_type_id`,`level`,`track_id`,`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tasks` (
  `task_id` int(10) unsigned NOT NULL auto_increment,
  `subject_id` int(10) unsigned NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `task_type` enum('','goal_hist') collate utf8_unicode_ci NOT NULL,
  `quantity_name` varchar(255) collate utf8_unicode_ci default NULL,
  `rep_type` enum('daily','weekly','monthly_date','monthly_week','yearly') collate utf8_unicode_ci NOT NULL,
  `start_date` mediumint(8) unsigned NOT NULL COMMENT 'Date as a Chronological Julian Day',
  `end_date` mediumint(8) unsigned default NULL COMMENT 'Date as a Chronological Julian Day',
  `every` smallint(5) unsigned NOT NULL,
  `rep_param1` tinyint(3) unsigned default NULL COMMENT 'Meaning varies depending on repeat',
  `rep_param2` tinyint(3) unsigned default NULL COMMENT 'Meaning varies depending on repeat',
  PRIMARY KEY  (`task_id`),
  KEY `task_type` (`task_type`),
  KEY `subject_date` (`subject_id`,`start_date`,`end_date`),
  KEY `start_date` (`start_date`,`end_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `teams` (
  `team_id` int(10) unsigned NOT NULL auto_increment,
  `school_id` int(10) unsigned NOT NULL,
  `team_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`team_id`),
  UNIQUE KEY `team_name` (`school_id`,`team_name`),
  UNIQUE KEY `school_id` (`school_id`,`team_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `todo_categories`
--

DROP TABLE IF EXISTS `todo_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `todo_categories` (
  `category_id` int(10) unsigned NOT NULL auto_increment,
  `subject_id` int(10) unsigned default NULL,
  `category_id_old` int(10) unsigned NOT NULL,
  `category_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`category_id`),
  UNIQUE KEY `subject_id` (`subject_id`,`category_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `todo_categories_old`
--

DROP TABLE IF EXISTS `todo_categories_old`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `todo_categories_old` (
  `category_id` int(10) unsigned NOT NULL auto_increment,
  `category_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `todo_list`
--

DROP TABLE IF EXISTS `todo_list`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `todo_list` (
  `todo_id` int(10) unsigned NOT NULL auto_increment,
  `recip` enum('school','class','team','user','end_user') collate utf8_unicode_ci NOT NULL,
  `school_id` int(10) unsigned default NULL,
  `recip_id` int(10) unsigned default NULL,
  `todo_text` varchar(255) collate utf8_unicode_ci NOT NULL,
  `todo_priority` enum('Urgent','High','Medium','Low') collate utf8_unicode_ci NOT NULL default 'Medium',
  `category_id` int(10) unsigned NOT NULL,
  `todo_due_date` mediumint(8) unsigned default NULL,
  `todo_file_id` int(10) unsigned default NULL,
  `todo_url` varchar(2048) character set ascii collate ascii_bin NOT NULL,
  `visibility` enum('all','none') collate utf8_unicode_ci NOT NULL default 'all',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`todo_id`),
  KEY `sub_id` (`category_id`),
  KEY `recip` (`recip`,`school_id`,`recip_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `todo_list_marks`
--

DROP TABLE IF EXISTS `todo_list_marks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `todo_list_marks` (
  `todo_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `auth` enum('school','class','team','user','end_user') collate utf8_unicode_ci NOT NULL,
  `mark_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`todo_id`,`auth`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tracks`
--

DROP TABLE IF EXISTS `tracks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tracks` (
  `track_id` int(10) unsigned NOT NULL auto_increment,
  `track_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`track_id`),
  UNIQUE KEY `track_name` (`track_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `translations_text`
--

DROP TABLE IF EXISTS `translations_text`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `translations_text` (
  `text` text collate utf8_unicode_ci NOT NULL,
  `lang` char(2) collate utf8_unicode_ci NOT NULL,
  `text_transl` text collate utf8_unicode_ci NOT NULL,
  KEY `lang` (`lang`,`text`(331))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `translations_varchar`
--

DROP TABLE IF EXISTS `translations_varchar`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `translations_varchar` (
  `text` varchar(255) collate utf8_unicode_ci NOT NULL,
  `lang` char(2) collate utf8_unicode_ci NOT NULL,
  `text_transl` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`lang`,`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_bak`
--

DROP TABLE IF EXISTS `user_bak`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_bak` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `user_code` bigint(19) unsigned zerofill NOT NULL,
  `username` varchar(64) collate utf8_unicode_ci NOT NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `auth` enum('','super','admin') collate utf8_unicode_ci NOT NULL,
  `password` varchar(64) collate utf8_unicode_ci NOT NULL,
  `first` varchar(128) collate utf8_unicode_ci NOT NULL,
  `last` varchar(128) collate utf8_unicode_ci NOT NULL,
  `first_he` varchar(128) collate utf8_unicode_ci NOT NULL,
  `last_he` varchar(128) collate utf8_unicode_ci NOT NULL,
  `lang` char(2) collate utf8_unicode_ci NOT NULL,
  `school_type_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned default NULL,
  `team_id` int(10) unsigned default NULL,
  `user_serial` mediumint(8) unsigned default NULL,
  `user_address1` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_address2` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_city` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_state` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_postal` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_country` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_phone` varchar(255) collate utf8_unicode_ci NOT NULL,
  `gender` enum('M','F') collate utf8_unicode_ci default NULL,
  `user_start_date` mediumint(8) unsigned default NULL,
  `dob` date default NULL,
  `dob_he_offset` tinyint(1) unsigned default NULL,
  `dob_he` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_photo_id` int(10) unsigned default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_codes`
--

DROP TABLE IF EXISTS `user_codes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_codes` (
  `user_id` int(10) unsigned NOT NULL,
  `code_id` bigint(19) unsigned zerofill NOT NULL,
  `code_id_prefix` tinyint(1) unsigned NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `grant_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`admin_id`,`code_id`,`code_id_prefix`),
  KEY `admin_id` (`admin_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_mission_entries`
--

DROP TABLE IF EXISTS `user_mission_entries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_mission_entries` (
  `user_id` int(10) unsigned NOT NULL,
  `code_id` bigint(19) unsigned default NULL,
  `entry_id` int(10) unsigned NOT NULL,
  `entry_type` enum('date_tasks_missions','chain_missions') collate utf8_unicode_ci NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`,`entry_type`,`entry_id`),
  UNIQUE KEY `code_id` (`code_id`),
  KEY `subject_id` (`subject_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_tasks`
--

DROP TABLE IF EXISTS `user_tasks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_tasks` (
  `task_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `rep_type` enum('daily','weekly','monthly_date','monthly_week','yearly') collate utf8_unicode_ci NOT NULL,
  `start_date` mediumint(8) unsigned NOT NULL COMMENT 'Date as a Chronological Julian Day',
  `end_date` mediumint(8) unsigned default NULL COMMENT 'Date as a Chronological Julian Day',
  `every` smallint(5) unsigned NOT NULL,
  `rep_param1` tinyint(3) unsigned default NULL COMMENT 'Meaning varies depending on repeat',
  `rep_param2` tinyint(3) unsigned default NULL COMMENT 'Meaning varies depending on repeat',
  `range` smallint(5) unsigned NOT NULL default '0' COMMENT 'extra days after the offical date where the task shows up/can be done',
  `reps` smallint(5) unsigned NOT NULL default '1' COMMENT 'how many times the task must be done to satisfy the range',
  `levels` set('3','4','5','6','7','8','9','10','11','12','13','14') collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`task_id`),
  KEY `level` (`user_id`,`subject_id`,`start_date`,`end_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_tracks`
--

DROP TABLE IF EXISTS `user_tracks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_tracks` (
  `user_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `track_id` int(10) unsigned default NULL,
  `level` tinyint(3) unsigned default NULL,
  `enrolled` tinyint(1) unsigned default '0',
  PRIMARY KEY  (`user_id`,`subject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_withdraw`
--

DROP TABLE IF EXISTS `user_withdraw`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_withdraw` (
  `user_id` int(10) unsigned NOT NULL,
  `code_id` bigint(17) unsigned zerofill NOT NULL,
  `print_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `scan_date` timestamp NULL default NULL,
  `points` decimal(8,2) unsigned NOT NULL,
  PRIMARY KEY  (`code_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `user_code` bigint(19) unsigned zerofill NOT NULL,
  `username` varchar(64) collate utf8_unicode_ci NOT NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `password` varchar(64) character set utf8 collate utf8_bin NOT NULL,
  `first` varchar(128) collate utf8_unicode_ci NOT NULL,
  `last` varchar(128) collate utf8_unicode_ci NOT NULL,
  `first_he` varchar(128) collate utf8_unicode_ci NOT NULL,
  `last_he` varchar(128) collate utf8_unicode_ci NOT NULL,
  `lang` char(2) collate utf8_unicode_ci NOT NULL,
  `school_type_id` int(10) unsigned NOT NULL,
  `school_id` int(10) default NULL,
  `class_id` int(10) unsigned default NULL,
  `team_id` int(10) unsigned default NULL,
  `user_serial` mediumint(8) unsigned NOT NULL,
  `fee_id` int(10) unsigned default NULL,
  `user_address1` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_address2` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_city` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_state` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_postal` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_country` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_phone` varchar(255) collate utf8_unicode_ci NOT NULL,
  `gender` enum('M','F') collate utf8_unicode_ci default NULL,
  `user_start_date` mediumint(8) unsigned default NULL,
  `user_registered` timestamp NULL default NULL,
  `user_registration_fee` decimal(6,2) unsigned default NULL,
  `user_notes` varchar(255) collate utf8_unicode_ci NOT NULL,
  `dob` date default NULL,
  `dob_he_offset` tinyint(1) unsigned default NULL,
  `dob_he` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_photo_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `user_code` (`user_code`),
  UNIQUE KEY `user_serial` (`user_serial`),
  KEY `team_id` (`school_id`,`team_id`),
  KEY `school_type_id` (`school_type_id`),
  KEY `user_start_date` (`user_start_date`),
  KEY `school_id` (`school_id`,`user_registered`),
  KEY `class_id` (`class_id`,`school_id`,`user_registered`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `medals_subjects_totals`
--

/*!50001 DROP TABLE `medals_subjects_totals`*/;
/*!50001 DROP VIEW IF EXISTS `medals_subjects_totals`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mashpia`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `medals_subjects_totals` AS select `medals_subjects`.`subject_id` AS `subject_id`,`medals_subjects`.`medal_ord` AS `medal_ord`,`medals_subjects`.`medal_on_image_id` AS `medal_on_image_id`,`medals_subjects`.`medal_off_image_id` AS `medal_off_image_id`,`medals_subjects`.`profile_photo_id` AS `profile_photo_id`,`medals_subjects`.`missions_required` AS `missions_required`,(select sum(`medals_total`.`missions_required`) AS `SUM(missions_required)` from `medals_subjects` `medals_total` where ((`medals_total`.`subject_id` = `medals_subjects`.`subject_id`) and (`medals_total`.`medal_ord` <= `medals_subjects`.`medal_ord`))) AS `missions_required_total` from `medals_subjects` */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-11-17 20:26:48
