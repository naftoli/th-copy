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
-- Table structure for table `admins_bak`
--

DROP TABLE IF EXISTS `admins_bak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins_bak` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `auth` enum('','inactive','super','ckidssuper') NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(45) NOT NULL DEFAULT 'Rabbi',
  `title_fr` enum('','Rav','M.','Mme','Mlle') NOT NULL DEFAULT 'Rav',
  `first` varchar(128) NOT NULL,
  `last` varchar(128) NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'en',
  `admin_address1` varchar(255) NOT NULL,
  `admin_address2` varchar(255) NOT NULL,
  `admin_city` varchar(255) NOT NULL,
  `admin_state` varchar(255) NOT NULL,
  `admin_postal` varchar(255) NOT NULL,
  `admin_country` varchar(255) NOT NULL,
  `admin_phone_work` varchar(255) NOT NULL,
  `admin_phone_home` varchar(255) NOT NULL,
  `admin_phone_mobile` varchar(255) NOT NULL,
  `admin_phone_mobile2` varchar(45) DEFAULT NULL,
  `admin_email` varchar(255) DEFAULT NULL,
  `camp_id` int(10) unsigned DEFAULT NULL,
  `staff_type_id` int(10) unsigned DEFAULT NULL,
  `staff_photo_id` int(10) unsigned DEFAULT NULL,
  `reminders` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `is_parent` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `been_added` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `photo` varchar(255) NOT NULL,
  `is_shliach` tinyint(3) unsigned DEFAULT NULL,
  `ship_to` int(10) unsigned DEFAULT NULL,
  `num_hachayols` tinyint(3) unsigned DEFAULT NULL,
  `no_shipping` tinyint(4) DEFAULT 0,
  `father` varchar(100) DEFAULT NULL,
  `mother` varchar(100) DEFAULT NULL,
  `father_pic` varchar(100) DEFAULT NULL,
  `mother_pic` varchar(100) DEFAULT NULL,
  `wp_id` int(10) unsigned DEFAULT NULL,
  `chidon_whatsapp` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `achievement_cards` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `store` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `phone1` varchar(45) DEFAULT NULL,
  `phone2` varchar(45) DEFAULT NULL,
  `phone3` varchar(45) DEFAULT NULL,
  `phone4` varchar(45) DEFAULT NULL,
  `authorize_customer_profile_id` varchar(20) DEFAULT NULL,
  `chabad_org_shliach_id` int(10) unsigned DEFAULT NULL,
  `google_id` char(100) DEFAULT NULL,
  `beta` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `admin_email_UNIQUE` (`admin_email`),
  UNIQUE KEY `chabad_org_shliach_id_UNIQUE` (`chabad_org_shliach_id`),
  UNIQUE KEY `google_id_UNIQUE` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=192711 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:34:56
