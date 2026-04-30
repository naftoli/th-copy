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
-- Table structure for table `th_chidon_staff`
--

DROP TABLE IF EXISTS `th_chidon_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `th_chidon_staff` (
  `staff_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chap_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(80) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `cell` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `email` varchar(85) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `username` varchar(35) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `walking_zone` int(10) DEFAULT NULL,
  `door_number` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `bus_code` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `year` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `gender` enum('boys','girls') DEFAULT NULL,
  `first_name` varchar(55) DEFAULT NULL,
  `last_name` varchar(55) DEFAULT NULL,
  `grade` enum('4','5','6','7','8') DEFAULT NULL,
  `team_id` int(10) unsigned DEFAULT NULL,
  `bowling_lane` varchar(10) DEFAULT NULL,
  `school_bus` varchar(45) DEFAULT NULL,
  `open_air_bus` varchar(45) DEFAULT NULL,
  `coach_bus` varchar(45) DEFAULT NULL,
  `sunday_pm_bus` varchar(45) DEFAULT NULL,
  `address` varchar(75) DEFAULT NULL,
  `sweater_size` varchar(45) DEFAULT NULL,
  `walking_group` varchar(45) DEFAULT NULL,
  `teams` varchar(45) DEFAULT NULL,
  `super_admin` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `reprint` tinyint(1) unsigned DEFAULT 0,
  `dob` date DEFAULT NULL,
  `acc_name` varchar(45) DEFAULT NULL,
  `acc_phone` varchar(45) DEFAULT NULL,
  `acc_address` varchar(45) DEFAULT NULL,
  `vehicle` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `position` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2067 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:34:15
