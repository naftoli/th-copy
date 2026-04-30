-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 50.28.66.228    Database: mashpiadb
-- ------------------------------------------------------
-- Server version	5.5.5-10.3.39-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `users_bak`
--

DROP TABLE IF EXISTS `users_bak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_bak` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_code` bigint(19) unsigned zerofill NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `first` varchar(128) NOT NULL,
  `last` varchar(128) NOT NULL,
  `first_he` varchar(128) NOT NULL,
  `last_he` varchar(128) NOT NULL,
  `lang` varchar(20) NOT NULL DEFAULT 'english',
  `school_type_id` int(10) unsigned NOT NULL,
  `school_id` int(10) DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `team_id` int(10) unsigned DEFAULT NULL,
  `user_serial` mediumint(8) unsigned NOT NULL,
  `fee_id` int(10) unsigned DEFAULT NULL,
  `user_address1` varchar(255) NOT NULL,
  `user_address2` varchar(255) NOT NULL,
  `user_city` varchar(255) NOT NULL,
  `user_state` varchar(255) NOT NULL,
  `user_postal` varchar(255) NOT NULL,
  `user_country` varchar(255) NOT NULL,
  `user_phone` varchar(255) NOT NULL,
  `gender` enum('M','F') DEFAULT NULL,
  `user_start_date` mediumint(8) unsigned DEFAULT NULL,
  `user_registered` timestamp NULL DEFAULT NULL,
  `camp_registered` timestamp NULL DEFAULT NULL,
  `user_registration_fee` decimal(6,2) unsigned DEFAULT NULL,
  `user_notes` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `dob_he_offset` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `dob_he` varchar(255) NOT NULL,
  `user_photo_id` int(10) unsigned DEFAULT NULL,
  `kiosk_edit` enum('','off','frozen') NOT NULL,
  `camp_id` int(10) DEFAULT NULL,
  `add_on_one` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `add_on_two` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `child_type_id` int(10) unsigned NOT NULL DEFAULT 0,
  `shirt_size` char(2) NOT NULL,
  `cc_ref` varchar(50) NOT NULL,
  `been_added` tinyint(1) NOT NULL DEFAULT 0,
  `image_added` tinyint(1) NOT NULL DEFAULT 0,
  `parent_marking` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `big_prizes_won` int(10) unsigned NOT NULL DEFAULT 0,
  `small_prizes_won` int(10) unsigned NOT NULL DEFAULT 0,
  `lang_id` int(10) unsigned NOT NULL DEFAULT 1,
  `pic_mission_type` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `he_name` varchar(36) DEFAULT NULL,
  `he_name_conf` tinyint(4) DEFAULT 0,
  `mobile_pic` varchar(255) DEFAULT NULL,
  `pushka` tinyint(4) DEFAULT 0,
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:33:28
