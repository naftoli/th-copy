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
-- Table structure for table `achievement_cards_scanned`
--

DROP TABLE IF EXISTS `achievement_cards_scanned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievement_cards_scanned` (
  `achievement_card_id` int(11) NOT NULL DEFAULT 0,
  `institution_id` int(11) unsigned DEFAULT 0,
  `campaign_id` int(11) unsigned DEFAULT 0,
  `mission_id` int(11) unsigned DEFAULT 0,
  `task_id` int(11) unsigned DEFAULT 0,
  `class_id` int(10) unsigned DEFAULT 0,
  `card_serial` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `extra_serial` int(11) DEFAULT NULL,
  `card_points` int(5) unsigned DEFAULT 0,
  `card_type` enum('Institution Administrator','Teacher','MissionsApp') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `campaign_image_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `achievement` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `status` enum('scanned','not scanned','weblink') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'not scanned',
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`achievement_card_id`),
  KEY `institution` (`institution_id`),
  KEY `card` (`card_serial`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:36:26
