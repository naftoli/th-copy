-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 50.28.66.228    Database: pointsDB
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
-- Table structure for table `user_campaigns`
--

DROP TABLE IF EXISTS `user_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_campaign_id`),
  KEY `user_id` (`user_id`),
  KEY `institution_id` (`institution_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `mission_id` (`mission_id`),
  KEY `task_id` (`task_id`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=573157 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:36:24
