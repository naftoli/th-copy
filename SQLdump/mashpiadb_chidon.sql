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
-- Table structure for table `chidon`
--

DROP TABLE IF EXISTS `chidon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chidon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mqty` int(10) unsigned NOT NULL,
  `gqty` int(10) unsigned NOT NULL,
  `ggqty` int(10) unsigned NOT NULL,
  `paid` decimal(6,2) NOT NULL,
  `approval` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date_purchased` datetime NOT NULL,
  `shipped` datetime DEFAULT NULL,
  `method` enum('ship','jcm pickup','event pickup') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `address` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `city` varchar(35) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `state` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `zip` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `chidon_reg_id` int(10) unsigned DEFAULT NULL,
  `chidon_reg_id2` int(10) unsigned DEFAULT NULL,
  `vip_seats` tinyint(4) NOT NULL DEFAULT 0,
  `fr` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `prepared` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `m10` int(10) unsigned NOT NULL,
  `m18` int(10) unsigned NOT NULL,
  `m36` int(10) unsigned NOT NULL,
  `m50` int(10) unsigned NOT NULL,
  `m100` int(10) unsigned NOT NULL,
  `g10` int(10) unsigned NOT NULL,
  `g18` int(10) unsigned NOT NULL,
  `gg10` int(10) unsigned NOT NULL,
  `gg18` int(10) unsigned NOT NULL,
  `gg36` int(10) unsigned NOT NULL,
  `gg50` int(10) unsigned NOT NULL,
  `gg100` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=811 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:34:04
