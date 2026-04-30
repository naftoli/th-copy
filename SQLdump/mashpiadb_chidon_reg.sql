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
-- Table structure for table `chidon_reg`
--

DROP TABLE IF EXISTS `chidon_reg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chidon_reg` (
  `chidon_reg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chidon_schools_id` int(10) unsigned NOT NULL,
  `grade` enum('4','5','6','7','8') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `type` enum('winner','parent','runnerUp','runnerUpP','contestant') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `hname` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `book` enum('1','2','3','4') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mark` tinyint(3) unsigned DEFAULT NULL,
  `mark1` tinyint(3) unsigned DEFAULT NULL,
  `mark2` tinyint(3) unsigned DEFAULT NULL,
  `mark3` tinyint(3) unsigned DEFAULT NULL,
  `fee` tinyint(3) unsigned DEFAULT NULL,
  `help` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `family` varchar(60) DEFAULT NULL,
  `address` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `arr_airport` varchar(60) DEFAULT NULL,
  `arr_number` varchar(60) DEFAULT NULL,
  `arr_time` varchar(45) DEFAULT NULL,
  `dep_airport` varchar(60) DEFAULT NULL,
  `dep_number` varchar(60) DEFAULT NULL,
  `dep_time` varchar(45) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `file` varchar(100) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid` tinyint(4) NOT NULL DEFAULT 0,
  `approval` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `parent_email` varchar(255) DEFAULT NULL,
  `parent_cell` varchar(25) DEFAULT NULL,
  `size` varchar(45) DEFAULT NULL,
  `transportation` tinyint(4) DEFAULT NULL,
  `bonus` tinyint(3) unsigned DEFAULT NULL,
  `parent_cell2` varchar(45) DEFAULT NULL,
  `allergies` varchar(255) DEFAULT NULL,
  `whatsapp` tinyint(3) DEFAULT 0,
  `walk_alone` tinyint(3) DEFAULT NULL,
  `shoe_size` varchar(45) DEFAULT NULL,
  `between_streets` varchar(255) DEFAULT NULL,
  `cert_printed` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `cert_conf_printed` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `plaque_created` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `entered` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `team` tinyint(1) unsigned DEFAULT NULL,
  `child_city_state` varchar(155) DEFAULT NULL,
  `bus_number` varchar(10) DEFAULT NULL,
  `walking_group` varchar(10) DEFAULT NULL,
  `emergency` varchar(45) DEFAULT NULL,
  `meeting_point` varchar(85) DEFAULT NULL,
  `hfname` varchar(100) DEFAULT NULL,
  `hlname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`chidon_reg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2277 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:33:53
