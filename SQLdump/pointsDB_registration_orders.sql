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
-- Table structure for table `registration_orders`
--

DROP TABLE IF EXISTS `registration_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registration_orders` (
  `registration_orders_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_confirmation_code` varchar(16) NOT NULL DEFAULT '',
  `api_confirmation_code` varchar(30) DEFAULT '',
  `institution_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `template_style` varchar(30) NOT NULL,
  `administrator_first_name` varchar(30) DEFAULT NULL,
  `administrator_last_name` varchar(30) DEFAULT NULL,
  `administrator_email` varchar(100) DEFAULT NULL,
  `administrator_phone_number` varchar(30) DEFAULT NULL,
  `administrator_cell_phone` varchar(45) DEFAULT NULL,
  `administrator_address` varchar(45) DEFAULT NULL,
  `administrator_city` varchar(25) DEFAULT NULL,
  `administrator_postal` varchar(20) DEFAULT NULL,
  `administrator_state` varchar(25) DEFAULT NULL,
  `administrator_country` varchar(20) DEFAULT NULL,
  `institution_name` varchar(30) DEFAULT NULL,
  `institution_type` varchar(25) DEFAULT NULL,
  `institution_address` varchar(60) DEFAULT NULL,
  `institution_city` varchar(30) DEFAULT NULL,
  `institution_state` varchar(20) DEFAULT NULL,
  `institution_postal` varchar(20) DEFAULT NULL,
  `institution_country` varchar(20) DEFAULT NULL,
  `institution_phone` varchar(30) DEFAULT NULL,
  `institution_email` varchar(100) DEFAULT NULL,
  `institution_website` varchar(255) DEFAULT NULL,
  `kioskaccessories_regular` int(4) unsigned DEFAULT NULL,
  `kioskaccessories_campers` int(11) unsigned DEFAULT NULL,
  `kioskaccessories_sponsored` int(4) unsigned DEFAULT NULL,
  `kioskaccessories_rental` int(4) unsigned DEFAULT NULL,
  `kioskaccessories_scanner` int(6) unsigned DEFAULT NULL,
  `kioskaccessories_handbook` int(6) unsigned DEFAULT NULL,
  `billing_first_name` varchar(30) DEFAULT NULL,
  `billing_last_name` varchar(30) DEFAULT NULL,
  `billing_phone_number` varchar(30) DEFAULT NULL,
  `billing_address` varchar(60) DEFAULT NULL,
  `billing_city` varchar(30) DEFAULT NULL,
  `billing_postal` varchar(20) DEFAULT NULL,
  `billing_state` varchar(20) DEFAULT NULL,
  `billing_country` varchar(20) DEFAULT NULL,
  `shipping_first_name` varchar(30) DEFAULT NULL,
  `shipping_last_name` varchar(30) DEFAULT NULL,
  `shipping_phone_number` varchar(30) DEFAULT NULL,
  `shipping_address` varchar(60) DEFAULT NULL,
  `shipping_city` varchar(30) DEFAULT NULL,
  `shipping_postal` varchar(20) DEFAULT NULL,
  `shipping_state` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(20) DEFAULT NULL,
  `creditcard_name` varchar(45) DEFAULT NULL,
  `creditcard_number` varchar(4) DEFAULT NULL,
  `creditcard_expiration_month` varchar(2) DEFAULT NULL,
  `creditcard_expiration_year` varchar(4) DEFAULT NULL,
  `creditcard_ccv` varchar(8) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`registration_orders_id`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:36:57
