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
-- Table structure for table `date_tasks_missions`
--

DROP TABLE IF EXISTS `date_tasks_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `date_tasks_missions` (
  `date_tasks_mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_type_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `track_id` int(10) unsigned NOT NULL,
  `mission_name` varchar(255) NOT NULL,
  `mission_number` decimal(8,1) unsigned DEFAULT NULL,
  `mission_group` enum('','shabbos','weekday') NOT NULL,
  `mission_description` varchar(255) DEFAULT NULL,
  `mission_value` decimal(5,1) unsigned NOT NULL DEFAULT 1.0,
  `start_date` mediumint(8) unsigned NOT NULL,
  `end_date` mediumint(8) unsigned NOT NULL,
  `missing` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `personal` tinyint(1) NOT NULL DEFAULT 0,
  `speed` double NOT NULL DEFAULT 0,
  `default_on` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `created_by_school` int(10) unsigned DEFAULT NULL,
  `created_by_parent` int(10) DEFAULT NULL,
  `lang_id` int(10) unsigned NOT NULL DEFAULT 1,
  `gender` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`date_tasks_mission_id`),
  KEY `idx_dtm_filters` (`subject_id`,`level`,`track_id`,`school_type_id`,`lang_id`,`start_date`),
  KEY `idx_dtm_dates_subject` (`start_date`,`end_date`,`subject_id`),
  KEY `idx_dtm_track_lookup` (`school_type_id`,`track_id`,`level`,`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5486465 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:34:26
