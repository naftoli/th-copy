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
-- Table structure for table `camps`
--

DROP TABLE IF EXISTS `camps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `camps` (
  `camp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `camp_type` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `camp_name` varchar(255) NOT NULL,
  `camp_name_he` varchar(255) NOT NULL,
  `inst_id` int(10) unsigned NOT NULL DEFAULT 8,
  `camp_settings` set('home_camp') NOT NULL,
  `package_id` int(10) unsigned DEFAULT NULL,
  `camp_gender` enum('M','F','B') NOT NULL,
  `camp_logo_id` int(10) unsigned DEFAULT NULL,
  `camp_logo_kiosk_id` int(10) unsigned DEFAULT NULL,
  `camp_no_logo` tinyint(1) NOT NULL DEFAULT 0,
  `camp_file_id` int(10) unsigned DEFAULT NULL,
  `camp_address1` varchar(255) NOT NULL,
  `camp_address2` varchar(255) NOT NULL,
  `camp_city` varchar(255) NOT NULL,
  `camp_state` varchar(255) NOT NULL,
  `camp_country` varchar(255) NOT NULL,
  `camp_postal` varchar(255) NOT NULL,
  `camp_phone` varchar(255) NOT NULL,
  `cc_number` varchar(19) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `cc_exp` varchar(5) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `cc_cvv` varchar(4) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `kiosk_print` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `camp_era` smallint(5) unsigned DEFAULT NULL,
  `shipping_method` enum('pickup','deliver') NOT NULL,
  `shipping_first` varchar(128) NOT NULL,
  `shipping_last` varchar(128) NOT NULL,
  `shipping_phone` varchar(255) NOT NULL,
  `shipping_address1` varchar(255) NOT NULL,
  `shipping_address2` varchar(255) NOT NULL,
  `shipping_city` varchar(255) NOT NULL,
  `shipping_state` varchar(255) NOT NULL,
  `shipping_postal` varchar(255) NOT NULL,
  `shipping_country` varchar(255) NOT NULL,
  `start_date` mediumint(8) NOT NULL DEFAULT 0,
  `end_date` mediumint(8) NOT NULL DEFAULT 0,
  `camp_type_id` int(10) unsigned NOT NULL,
  `session_one_start` mediumint(8) unsigned DEFAULT NULL,
  `session_one_end` mediumint(8) unsigned DEFAULT NULL,
  `session_two_start` mediumint(8) unsigned DEFAULT NULL,
  `session_two_end` mediumint(8) unsigned DEFAULT NULL,
  `camp_number` mediumint(8) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`camp_id`),
  UNIQUE KEY `camp_name` (`inst_id`,`camp_name`),
  UNIQUE KEY `camp_number` (`camp_number`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:33:59
