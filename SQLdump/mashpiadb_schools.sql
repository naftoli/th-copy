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
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `school_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_number` mediumint(8) unsigned NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `school_name_he` varchar(255) DEFAULT NULL,
  `nickname` varchar(155) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `school_initials` varchar(45) DEFAULT NULL,
  `inst_id` int(10) unsigned NOT NULL,
  `school_gender` enum('M','F','B') NOT NULL DEFAULT 'B',
  `school_era` smallint(5) unsigned DEFAULT NULL,
  `school_makeup_id` int(10) unsigned DEFAULT NULL,
  `school_settings` set('home_school') DEFAULT NULL,
  `package_id` int(10) unsigned DEFAULT NULL,
  `logo` varchar(255) DEFAULT 'logo.png',
  `logo_boys` varchar(255) DEFAULT 'logo.png',
  `logo_girls` varchar(255) DEFAULT 'logo_girls.png',
  `school_logo_id` int(10) unsigned DEFAULT NULL,
  `school_logo_kiosk_id` int(10) unsigned DEFAULT NULL,
  `school_no_logo` tinyint(1) NOT NULL DEFAULT 0,
  `school_file_id` int(10) unsigned DEFAULT NULL,
  `school_address1` varchar(255) DEFAULT NULL,
  `school_address2` varchar(255) DEFAULT NULL,
  `school_city` varchar(255) DEFAULT NULL,
  `school_state` varchar(255) DEFAULT NULL,
  `school_country` varchar(255) DEFAULT NULL,
  `school_postal` varchar(255) DEFAULT NULL,
  `school_phone` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `authorize_customer_profile_id` varchar(20) DEFAULT NULL,
  `authorize_payment_profile_id` varchar(20) DEFAULT NULL,
  `mosad_id` int(11) DEFAULT NULL,
  `kiosk_print` tinyint(1) unsigned DEFAULT 1,
  `shipping_method` enum('pickup','deliver') NOT NULL DEFAULT 'deliver',
  `yearly_prize_shipping_method` enum('pickup','deliver') DEFAULT 'deliver',
  `shipping_first` varchar(128) DEFAULT NULL,
  `shipping_last` varchar(128) DEFAULT NULL,
  `shipping_phone` varchar(255) DEFAULT NULL,
  `shipping_address1` varchar(255) DEFAULT NULL,
  `shipping_address2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(255) DEFAULT NULL,
  `shipping_state` varchar(255) DEFAULT NULL,
  `shipping_postal` varchar(255) DEFAULT NULL,
  `shipping_country` varchar(255) DEFAULT NULL,
  `shipping_requests` text DEFAULT NULL,
  `school_store` tinyint(1) NOT NULL DEFAULT 1,
  `camp_id` int(10) unsigned NOT NULL DEFAULT 0,
  `add_on_one` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `add_on_two` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `last_register_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cc_approval_number` varchar(50) DEFAULT NULL,
  `big_prizes_won` int(10) unsigned NOT NULL DEFAULT 0,
  `store_only` tinyint(1) DEFAULT NULL,
  `lang_id` int(10) unsigned NOT NULL DEFAULT 1,
  `he_name_principal` varchar(36) DEFAULT NULL,
  `he_name_p2` varchar(36) DEFAULT NULL,
  `conf_pushka_users` tinyint(4) unsigned NOT NULL DEFAULT 0,
  `chayolei` tinyint(4) unsigned NOT NULL DEFAULT 0,
  `chidon` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `hachayols` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `tanya` tinyint(4) unsigned NOT NULL DEFAULT 0,
  `tehillim` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `rewards` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `raffle_prizes` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `tanya_ord` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `tanya_cat_ord` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `school_type` tinyint(3) unsigned DEFAULT NULL,
  `col_show` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `tuition` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `principal` varchar(155) DEFAULT NULL,
  `principal_number` varchar(45) DEFAULT NULL,
  `principal_number_work` varchar(45) DEFAULT NULL,
  `principal_email` varchar(75) DEFAULT NULL,
  `principal_position` varchar(80) DEFAULT NULL,
  `principal_grades` varchar(65) DEFAULT NULL,
  `chidon_name` varchar(75) DEFAULT NULL,
  `chidon_number` varchar(45) DEFAULT NULL,
  `chidon_email` varchar(85) DEFAULT NULL,
  `chidon_also_bc` tinyint(3) unsigned DEFAULT 0,
  `reg_type` enum('1','2','3') NOT NULL DEFAULT '3',
  `chayolei_fee` decimal(6,2) unsigned NOT NULL DEFAULT 800.00,
  `tanya_fee` decimal(6,2) unsigned NOT NULL DEFAULT 0.00,
  `chidon_fee` decimal(6,2) unsigned NOT NULL DEFAULT 500.00,
  `rewards_fee` decimal(6,2) unsigned NOT NULL DEFAULT 0.00,
  `balance` decimal(8,2) NOT NULL DEFAULT 0.00,
  `early_bird` datetime NOT NULL DEFAULT '2018-09-07 00:00:00',
  `child_fee` int(11) unsigned DEFAULT NULL,
  `accounting_name` varchar(65) DEFAULT NULL,
  `accounting_number` varchar(45) DEFAULT NULL,
  `accounting_email` varchar(85) DEFAULT NULL,
  `test_school` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `shamai_ord` tinyint(3) unsigned DEFAULT NULL,
  `hachayol_name` varchar(65) DEFAULT NULL,
  `hachayol_extra` int(11) NOT NULL DEFAULT 0,
  `hachayol_extra_total` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `allow_parent_tasks` tinyint(3) DEFAULT 1,
  `print_parent_tasks` tinyint(3) DEFAULT 1,
  `pic_mission_type` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `store_reset` int(11) DEFAULT NULL,
  `lulav_shipping` tinyint(3) unsigned NOT NULL DEFAULT 15,
  `allow_lulav` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `chidon_hold_id` int(10) unsigned DEFAULT NULL,
  `chidon_hold_date` date DEFAULT NULL,
  `hq_notes` text DEFAULT NULL,
  `school_charge_date` datetime DEFAULT NULL,
  `chidon_posters_boys` int(11) unsigned DEFAULT NULL,
  `chidon_posters_girls` int(11) unsigned DEFAULT NULL,
  `medals_ranks` tinyint(1) NOT NULL DEFAULT 1,
  `registration_notes` varchar(255) DEFAULT NULL,
  `chidon_confirmed_5782` tinyint(3) unsigned DEFAULT 0,
  `sweaters_confirmed_5782` tinyint(3) unsigned DEFAULT 0,
  `res_address1` varchar(65) DEFAULT NULL,
  `res_address2` varchar(65) DEFAULT NULL,
  `res_city` varchar(65) DEFAULT NULL,
  `res_state` varchar(45) DEFAULT NULL,
  `res_postal` varchar(45) DEFAULT NULL,
  `res_country` varchar(65) DEFAULT NULL,
  `num_chidon_videos` tinyint(3) unsigned DEFAULT 0,
  `chidon_5783_updated_shipping` tinyint(3) unsigned DEFAULT 0,
  `prizes_updated_shipping` tinyint(3) unsigned DEFAULT 0,
  `one_time_prize_reset` int(10) unsigned DEFAULT 0,
  `shorthand` varchar(75) DEFAULT NULL,
  `show_report_card_1` tinyint(3) unsigned DEFAULT 0,
  `show_report_card_2` tinyint(3) unsigned DEFAULT 0,
  `show_report_card_3` tinyint(3) unsigned DEFAULT 0,
  `siddur_gift` tinyint(3) unsigned DEFAULT 0,
  `schoolscol` varchar(45) DEFAULT NULL,
  `chidon_first` varchar(45) DEFAULT NULL,
  `chidon_last` varchar(65) DEFAULT NULL,
  `open_reg_5786` tinyint(1) unsigned DEFAULT 0,
  PRIMARY KEY (`school_id`),
  UNIQUE KEY `school_name` (`inst_id`,`school_name`),
  UNIQUE KEY `school_number` (`school_number`),
  UNIQUE KEY `mosad_id_UNIQUE` (`mosad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=854 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 16:34:00
