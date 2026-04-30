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
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `class_grade` enum('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12') NOT NULL,
  `class_grade_fr` enum('Gan_1','Gan_2','Gan_3','Grand_Gan','CP','CE1','CE2','CM1','CM2','6eme','5eme','4eme','3eme','2nde','1ere','Term') DEFAULT NULL,
  `class_sub` varchar(255) NOT NULL,
  `class_teacher` varchar(255) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `cell` varchar(255) DEFAULT NULL,
  `default_level` tinyint(3) unsigned NOT NULL,
  `gender_view` enum('self','all') NOT NULL DEFAULT 'all',
  `class_era` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Hebrew Year when class was active (0 for current)',
  `teacher_gender` enum('M','F') DEFAULT NULL,
  `teacher_hname` varchar(36) DEFAULT NULL,
  `class_gender` enum('m','f') DEFAULT NULL,
  `whatsapp` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `allow_parent_tasks` tinyint(3) DEFAULT 1,
  `print_parent_tasks` tinyint(3) DEFAULT 1,
  `pic_mission_type` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `confirmed` tinyint(3) NOT NULL DEFAULT 0,
  `miles_per_soldier` int(11) NOT NULL DEFAULT 100,
  `miles_balance` int(11) NOT NULL DEFAULT 0,
  `hachayols` tinyint(1) NOT NULL DEFAULT 1,
  `medals_ranks` tinyint(1) NOT NULL DEFAULT 1,
  `updated` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `school_id` (`school_id`,`class_id`),
  UNIQUE KEY `class_grade` (`school_id`,`class_era`,`class_grade`,`class_grade_fr`,`class_sub`),
  KEY `class_era` (`class_era`)
) ENGINE=InnoDB AUTO_INCREMENT=7682 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:34:54
