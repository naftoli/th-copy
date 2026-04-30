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
-- Table structure for table `auth_card_orders`
--

DROP TABLE IF EXISTS `auth_card_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_card_orders` (
  `auth_card_order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date_completed` int(11) unsigned DEFAULT NULL,
  `institution_id` int(11) DEFAULT NULL,
  `user_ids` text NOT NULL,
  `confirmation_code` char(16) DEFAULT NULL,
  `creditcard_first_name` varchar(60) DEFAULT '',
  `creditcard_last_name` varchar(60) DEFAULT '',
  `creditcard_name` varchar(20) DEFAULT '',
  `creditcard_number` varchar(4) DEFAULT '',
  `creditcard_ccv` varchar(11) DEFAULT NULL,
  `creditcard_expiration_month` char(3) DEFAULT '',
  `creditcard_expiration_year` int(4) unsigned DEFAULT NULL,
  `shipping_address` varchar(400) DEFAULT NULL,
  `shipping_city` varchar(30) DEFAULT NULL,
  `shipping_state` varchar(30) DEFAULT NULL,
  `shipping_postal` varchar(30) DEFAULT NULL,
  `shipping_country` varchar(30) DEFAULT NULL,
  `order_processed_date` int(11) unsigned DEFAULT NULL,
  `price_per_unit` varchar(11) DEFAULT NULL,
  `quantity_purchased` int(11) unsigned DEFAULT NULL,
  `sub_total` decimal(6,2) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) unsigned NOT NULL,
  PRIMARY KEY (`auth_card_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:36:39
