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
-- Table structure for table `date_tasks`
--

DROP TABLE IF EXISTS `date_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `date_tasks` (
  `date_task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_tasks_mission_id` int(10) unsigned NOT NULL,
  `ord` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `mandatory_qty` smallint(5) unsigned NOT NULL DEFAULT 0,
  `optional_qty` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_bonus` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `label_id` int(10) unsigned DEFAULT NULL,
  `label_ord` smallint(5) unsigned NOT NULL,
  `quantity` smallint(5) unsigned DEFAULT NULL,
  `points` decimal(6,2) unsigned NOT NULL DEFAULT 0.00,
  `sequence_number` mediumint(2) DEFAULT NULL,
  `daily_task` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `needed` tinyint(1) unsigned NOT NULL,
  `focus_task` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `cat_ord` decimal(6,2) DEFAULT NULL,
  `cat` varchar(100) DEFAULT NULL,
  `default_on` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `task_id` decimal(6,2) NOT NULL,
  `short_name` varchar(100) NOT NULL,
  `medium_pic` varchar(45) DEFAULT NULL,
  `yd_cat` varchar(45) DEFAULT NULL,
  `yd_cat_num` int(10) unsigned DEFAULT NULL,
  `grid_id` int(10) unsigned DEFAULT NULL,
  `mission_marking` tinyint(1) unsigned DEFAULT NULL,
  `grid_marking` tinyint(1) unsigned DEFAULT NULL,
  `achievement_card` tinyint(1) unsigned DEFAULT NULL,
  `cat_ord_new` int(10) unsigned DEFAULT NULL,
  `streak_id` int(10) unsigned NOT NULL DEFAULT 0,
  `streak_show` tinyint(1) unsigned DEFAULT 0,
  `streak_duch_cat` varchar(95) DEFAULT NULL,
  `streak_duch_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`date_task_id`),
  KEY `short_name` (`short_name`),
  KEY `grid` (`grid_id`),
  KEY `mandatory` (`mandatory_qty`),
  KEY `date_tasks_mission_id` (`date_tasks_mission_id`),
  KEY `idx_dt_mission_daily` (`date_tasks_mission_id`,`daily_task`),
  KEY `idx_dt_mission_mark_sort` (`date_tasks_mission_id`,`mission_marking`,`label_ord`,`grid_id`),
  KEY `idx_dt_grid_mission` (`grid_id`,`date_tasks_mission_id`),
  KEY `idx_dt_cat` (`cat`,`date_tasks_mission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17690052 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:32:32
