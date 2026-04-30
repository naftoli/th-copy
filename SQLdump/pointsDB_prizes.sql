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
-- Table structure for table `prizes`
--

DROP TABLE IF EXISTS `prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prizes` (
  `prize_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_prize_id` int(10) unsigned DEFAULT 0,
  `parent_prize_id` int(10) unsigned DEFAULT 0,
  `legacy_add_on_id` int(11) unsigned DEFAULT NULL,
  `teacher_id` int(11) unsigned DEFAULT 0,
  `guardian_id` int(11) unsigned DEFAULT 0,
  `network_id` int(10) unsigned DEFAULT 0,
  `institution_id` int(10) unsigned DEFAULT NULL,
  `prize_name` varchar(50) NOT NULL DEFAULT '',
  `points` int(6) DEFAULT NULL,
  `prize_category` varchar(40) DEFAULT 'General Prize',
  `bar_code` varchar(40) DEFAULT '',
  `prize_description` varchar(500) DEFAULT '',
  `image_id` varchar(40) DEFAULT 'default.png',
  `add_on_restricted` tinyint(1) unsigned DEFAULT 0,
  `use_sub_prizes` tinyint(1) unsigned DEFAULT 0,
  `one_per_user` tinyint(1) unsigned DEFAULT 0,
  `prize_count` int(11) unsigned DEFAULT 0,
  `prize_type` enum('Template','School Installed','Installable') DEFAULT 'Template',
  `installable_default_on` tinyint(1) unsigned DEFAULT 0,
  `prize_price` decimal(15,2) unsigned DEFAULT NULL,
  `prize_discounted_price` decimal(15,2) unsigned DEFAULT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `created` datetime DEFAULT current_timestamp(),
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(10) unsigned DEFAULT NULL,
  `teacher_edit` tinyint(1) NOT NULL DEFAULT 0,
  `num_per_user` tinyint(3) unsigned DEFAULT 0,
  `discount_amount` smallint(5) unsigned DEFAULT NULL,
  `discount_type` enum('points','percent') DEFAULT NULL,
  PRIMARY KEY (`prize_id`),
  KEY `institution_id` (`institution_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=173882 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:36:06
