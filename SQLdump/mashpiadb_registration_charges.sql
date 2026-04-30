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
-- Table structure for table `registration_charges`
--

DROP TABLE IF EXISTS `registration_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registration_charges` (
  `registration_charge_id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` int(11) unsigned NOT NULL DEFAULT 0,
  `school_id` int(11) unsigned NOT NULL DEFAULT 0,
  `admin_id` int(10) unsigned NOT NULL DEFAULT 0,
  `type` varchar(45) NOT NULL DEFAULT 'n/a',
  `amount` decimal(6,2) NOT NULL DEFAULT 0.00,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `year` int(4) unsigned NOT NULL DEFAULT 0,
  `discount` decimal(6,2) unsigned NOT NULL DEFAULT 0.00,
  `study_guide_shipped` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `book_shipped` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `refunded` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `refund_reason` varchar(255) DEFAULT '0',
  `keep_charge` tinyint(3) unsigned DEFAULT 0,
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`registration_charge_id`),
  KEY `year` (`year`),
  KEY `school` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=155026 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:35:08
