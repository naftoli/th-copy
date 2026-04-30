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
-- Table structure for table `date_tasks_marks_old`
--

DROP TABLE IF EXISTS `date_tasks_marks_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `date_tasks_marks_old` (
  `date_task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `mark_date` mediumint(8) unsigned NOT NULL,
  `done_qty` smallint(5) unsigned NOT NULL DEFAULT 0,
  `mark_description` text DEFAULT NULL,
  `mark_points` decimal(8,2) unsigned NOT NULL DEFAULT 0.00,
  `mark_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `mark_inactive` tinyint(1) NOT NULL DEFAULT 0,
  `mission_start` int(10) unsigned DEFAULT NULL,
  `mission_end` int(10) unsigned DEFAULT NULL,
  `dt_grid_id` int(10) unsigned DEFAULT NULL,
  `noDuplicates` int(10) unsigned NOT NULL DEFAULT 0,
  `auction_only_points` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mechunach_id` int(10) unsigned NOT NULL DEFAULT 0,
  KEY `date_task_id` (`date_task_id`),
  KEY `mechunach` (`mechunach_id`),
  KEY `auction_points` (`auction_only_points`),
  KEY `idx_dtm_user_date` (`user_id`,`mark_date`),
  KEY `idx_dtm_user_task` (`user_id`,`date_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:30:37
