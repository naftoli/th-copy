-- MySQL dump 10.16  Distrib 10.1.34-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: tickets
-- ------------------------------------------------------
-- Server version	10.1.34-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `msp_attachments`
--

DROP TABLE IF EXISTS `msp_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_attachments` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` varchar(20) NOT NULL DEFAULT '',
  `replyID` int(11) NOT NULL DEFAULT '0',
  `department` int(5) NOT NULL DEFAULT '0',
  `fileName` varchar(250) NOT NULL DEFAULT '',
  `fileSize` varchar(20) NOT NULL DEFAULT '',
  `mimeType` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `repid_index` (`replyID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_ban`
--

DROP TABLE IF EXISTS `msp_ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_ban` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL DEFAULT '',
  `ip` varchar(250) NOT NULL DEFAULT '',
  `count` int(5) NOT NULL DEFAULT '0',
  `banstamp` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_categories`
--

DROP TABLE IF EXISTS `msp_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_categories` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `summary` varchar(250) NOT NULL DEFAULT '',
  `enCat` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `subcat` int(5) NOT NULL DEFAULT '0',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_cusfields`
--

DROP TABLE IF EXISTS `msp_cusfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_cusfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fieldInstructions` varchar(250) NOT NULL DEFAULT '',
  `fieldType` enum('textarea','input','select','checkbox') NOT NULL DEFAULT 'input',
  `fieldReq` enum('yes','no') NOT NULL DEFAULT 'no',
  `fieldOptions` text,
  `fieldLoc` varchar(25) NOT NULL DEFAULT '',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `repeatPref` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enField` enum('yes','no') NOT NULL DEFAULT 'yes',
  `departments` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_departments`
--

DROP TABLE IF EXISTS `msp_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_departments` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `showDept` enum('yes','no') NOT NULL DEFAULT 'no',
  `dept_subject` text,
  `dept_comments` text,
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `manual_assign` enum('yes','no') NOT NULL DEFAULT 'no',
  `days` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_disputes`
--

DROP TABLE IF EXISTS `msp_disputes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_disputes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticketID` int(15) NOT NULL DEFAULT '0',
  `visitorID` int(8) NOT NULL DEFAULT '0',
  `postPrivileges` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `vis_index` (`visitorID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_faq`
--

DROP TABLE IF EXISTS `msp_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_faq` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `question` text,
  `answer` text,
  `kviews` int(10) NOT NULL DEFAULT '0',
  `kuseful` int(10) NOT NULL DEFAULT '0',
  `knotuseful` int(10) NOT NULL DEFAULT '0',
  `enFaq` enum('yes','no') NOT NULL DEFAULT 'yes',
  `featured` enum('yes','no') NOT NULL DEFAULT 'no',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_faqassign`
--

DROP TABLE IF EXISTS `msp_faqassign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_faqassign` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `question` int(7) NOT NULL DEFAULT '0',
  `itemID` int(7) NOT NULL DEFAULT '0',
  `desc` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `que_index` (`question`),
  KEY `att_index` (`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_faqattach`
--

DROP TABLE IF EXISTS `msp_faqattach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_faqattach` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL DEFAULT '',
  `remote` varchar(250) NOT NULL DEFAULT '',
  `path` varchar(250) NOT NULL DEFAULT '',
  `size` varchar(30) NOT NULL DEFAULT '',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `enAtt` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mimeType` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_imap`
--

DROP TABLE IF EXISTS `msp_imap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_imap` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `im_piping` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_protocol` enum('pop3','imap') NOT NULL DEFAULT 'pop3',
  `im_host` varchar(100) NOT NULL DEFAULT '',
  `im_user` varchar(250) NOT NULL DEFAULT '',
  `im_pass` varchar(100) NOT NULL DEFAULT '',
  `im_port` int(5) NOT NULL DEFAULT '110',
  `im_name` varchar(50) NOT NULL DEFAULT '',
  `im_flags` varchar(250) NOT NULL DEFAULT '',
  `im_attach` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_move` varchar(50) NOT NULL DEFAULT '',
  `im_messages` int(3) NOT NULL DEFAULT '20',
  `im_ssl` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_priority` varchar(250) NOT NULL DEFAULT '',
  `im_dept` int(5) NOT NULL DEFAULT '0',
  `im_email` varchar(250) NOT NULL DEFAULT '',
  `im_spam` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_spam_purge` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_score` varchar(10) NOT NULL DEFAULT '1.0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_imap_b8`
--

DROP TABLE IF EXISTS `msp_imap_b8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_imap_b8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tokens` int(5) NOT NULL DEFAULT '0',
  `min_size` int(5) NOT NULL DEFAULT '0',
  `max_size` int(5) NOT NULL DEFAULT '0',
  `min_dev` varchar(5) NOT NULL DEFAULT '0.5',
  `x_constant` varchar(5) NOT NULL DEFAULT '0.5',
  `s_constant` varchar(5) NOT NULL DEFAULT '0.3',
  `learning` enum('yes','no') NOT NULL DEFAULT 'yes',
  `num_parse` enum('yes','no') NOT NULL DEFAULT 'no',
  `uri_parse` enum('yes','no') NOT NULL DEFAULT 'yes',
  `html_parse` enum('yes','no') NOT NULL DEFAULT 'yes',
  `multibyte` enum('yes','no') NOT NULL DEFAULT 'no',
  `encoder` varchar(50) NOT NULL DEFAULT 'utf-8',
  `skipFilters` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_imap_b8_filter`
--

DROP TABLE IF EXISTS `msp_imap_b8_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_imap_b8_filter` (
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `count_ham` int(10) unsigned NOT NULL DEFAULT '0',
  `count_spam` int(10) unsigned NOT NULL DEFAULT '0',
  `ts` int(30) NOT NULL DEFAULT '0',
  PRIMARY KEY (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_levels`
--

DROP TABLE IF EXISTS `msp_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_levels` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `display` enum('yes','no') NOT NULL DEFAULT 'no',
  `marker` varchar(100) NOT NULL DEFAULT '',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_log`
--

DROP TABLE IF EXISTS `msp_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `userID` int(5) NOT NULL DEFAULT '0',
  `ip` varchar(250) NOT NULL DEFAULT '',
  `type` enum('user','acc') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  KEY `useid_index` (`userID`)
) ENGINE=MyISAM AUTO_INCREMENT=138 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_mailassoc`
--

DROP TABLE IF EXISTS `msp_mailassoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_mailassoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staffID` int(8) NOT NULL DEFAULT '0',
  `mailID` int(8) NOT NULL DEFAULT '0',
  `folder` varchar(10) NOT NULL DEFAULT 'inbox',
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `lastUpdate` int(30) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`),
  KEY `mail_index` (`mailID`),
  KEY `status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_mailbox`
--

DROP TABLE IF EXISTS `msp_mailbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_mailbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `staffID` int(8) NOT NULL DEFAULT '0',
  `subject` varchar(250) NOT NULL DEFAULT '',
  `message` text,
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_mailfolders`
--

DROP TABLE IF EXISTS `msp_mailfolders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_mailfolders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staffID` int(8) NOT NULL DEFAULT '0',
  `folder` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_mailreplies`
--

DROP TABLE IF EXISTS `msp_mailreplies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_mailreplies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `mailID` int(8) NOT NULL DEFAULT '0',
  `staffID` int(8) NOT NULL DEFAULT '0',
  `message` text,
  PRIMARY KEY (`id`),
  KEY `mail_index` (`mailID`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_pages`
--

DROP TABLE IF EXISTS `msp_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_pages` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `title` text,
  `information` text,
  `enPage` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `secure` enum('yes','no') NOT NULL DEFAULT 'no',
  `accounts` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_portal`
--

DROP TABLE IF EXISTS `msp_portal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_portal` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `ts` int(30) NOT NULL DEFAULT '0',
  `email` varchar(250) NOT NULL DEFAULT '',
  `userPass` varchar(40) NOT NULL DEFAULT '',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `verified` enum('yes','no') NOT NULL DEFAULT 'no',
  `timezone` varchar(50) NOT NULL DEFAULT '0',
  `ip` text,
  `notes` text,
  `reason` text,
  `system1` varchar(250) NOT NULL DEFAULT '',
  `system2` varchar(250) NOT NULL DEFAULT '',
  `language` varchar(100) NOT NULL DEFAULT '',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `em_index` (`email`),
  KEY `nm_index` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4583 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_replies`
--

DROP TABLE IF EXISTS `msp_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` int(15) NOT NULL DEFAULT '0',
  `comments` text,
  `mailBodyFilter` varchar(30) NOT NULL DEFAULT '',
  `replyType` enum('none','visitor','admin') NOT NULL DEFAULT 'none',
  `replyUser` int(8) NOT NULL DEFAULT '0',
  `isMerged` enum('yes','no') NOT NULL DEFAULT 'no',
  `ipAddresses` text,
  `disputeUser` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `repuse_index` (`replyUser`),
  KEY `disuse_index` (`disputeUser`)
) ENGINE=MyISAM AUTO_INCREMENT=248 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_responses`
--

DROP TABLE IF EXISTS `msp_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_responses` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `title` text,
  `answer` text,
  `enResponse` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `departments` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_settings`
--

DROP TABLE IF EXISTS `msp_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_settings` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `website` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `replyto` varchar(250) NOT NULL DEFAULT '',
  `scriptpath` varchar(250) NOT NULL DEFAULT '',
  `attachpath` varchar(250) NOT NULL DEFAULT '',
  `attachhref` varchar(250) NOT NULL DEFAULT '',
  `attachpathfaq` varchar(250) NOT NULL DEFAULT '',
  `attachhreffaq` varchar(250) NOT NULL DEFAULT '',
  `language` varchar(250) DEFAULT 'english',
  `langSets` text,
  `dateformat` varchar(20) NOT NULL DEFAULT 'D j M Y, G:ia',
  `timeformat` varchar(15) NOT NULL DEFAULT 'H:iA',
  `timezone` varchar(50) NOT NULL DEFAULT 'Europe/London',
  `weekStart` enum('mon','sun') NOT NULL DEFAULT 'sun',
  `jsDateFormat` varchar(15) NOT NULL DEFAULT 'DD/MM/YYYY',
  `kbase` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enableVotes` enum('yes','no') NOT NULL DEFAULT 'yes',
  `multiplevotes` enum('yes','no') NOT NULL DEFAULT 'no',
  `popquestions` int(5) NOT NULL DEFAULT '0',
  `quePerPage` int(3) NOT NULL DEFAULT '10',
  `cookiedays` int(5) NOT NULL DEFAULT '0',
  `renamefaq` enum('yes','no') NOT NULL DEFAULT 'no',
  `attachment` enum('yes','no') NOT NULL DEFAULT 'no',
  `rename` enum('yes','no') NOT NULL DEFAULT 'no',
  `attachboxes` int(3) NOT NULL DEFAULT '2',
  `filetypes` text,
  `maxsize` int(15) NOT NULL DEFAULT '200',
  `enableBBCode` enum('yes','no') NOT NULL DEFAULT 'yes',
  `afolder` varchar(50) NOT NULL DEFAULT '',
  `autoClose` int(5) NOT NULL DEFAULT '0',
  `autoCloseMail` enum('yes','no') NOT NULL DEFAULT 'yes',
  `smtp_host` varchar(100) NOT NULL DEFAULT 'localhost',
  `smtp_user` varchar(100) NOT NULL DEFAULT '',
  `smtp_pass` varchar(100) NOT NULL DEFAULT '',
  `smtp_port` int(4) NOT NULL DEFAULT '25',
  `smtp_security` varchar(10) NOT NULL DEFAULT '',
  `smtp_debug` enum('yes','no') NOT NULL DEFAULT 'no',
  `prodKey` char(60) NOT NULL DEFAULT '',
  `publicFooter` text,
  `adminFooter` text,
  `encoderVersion` varchar(5) NOT NULL DEFAULT '',
  `softwareVersion` varchar(10) NOT NULL DEFAULT '',
  `apiKey` varchar(100) NOT NULL DEFAULT '',
  `apiLog` enum('yes','no') NOT NULL DEFAULT 'no',
  `apiHandlers` varchar(100) NOT NULL DEFAULT '',
  `recaptchaPublicKey` varchar(250) NOT NULL DEFAULT '',
  `recaptchaPrivateKey` varchar(250) NOT NULL DEFAULT '',
  `enCapLogin` enum('yes','no') NOT NULL DEFAULT 'yes',
  `sysstatus` enum('yes','no') NOT NULL DEFAULT 'yes',
  `autoenable` date NOT NULL DEFAULT '1970-01-01',
  `disputes` enum('yes','no') NOT NULL DEFAULT 'no',
  `offlineReason` text,
  `createPref` enum('yes','no') NOT NULL DEFAULT 'yes',
  `createAcc` enum('yes','no') NOT NULL DEFAULT 'yes',
  `loginLimit` int(5) NOT NULL DEFAULT '0',
  `banTime` int(5) NOT NULL DEFAULT '0',
  `ticketHistory` enum('yes','no') NOT NULL DEFAULT 'yes',
  `backupEmails` text,
  `closenotify` enum('yes','no') NOT NULL DEFAULT 'no',
  `minPassValue` int(3) NOT NULL DEFAULT '8',
  `accProfNotify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `newAccNotify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `recaptchaTheme` varchar(20) NOT NULL DEFAULT '',
  `recaptchaLang` varchar(10) NOT NULL DEFAULT 'en-GB',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  `defKeepLogs` varchar(100) NOT NULL DEFAULT '',
  `minTickDigits` int(2) NOT NULL DEFAULT '5',
  `enableMail` enum('yes','no') NOT NULL DEFAULT 'yes',
  `imap_debug` enum('yes','no') NOT NULL DEFAULT 'no',
  `imap_param` varchar(10) NOT NULL DEFAULT '',
  `imap_memory` varchar(3) NOT NULL DEFAULT '10',
  `imap_timeout` varchar(3) NOT NULL DEFAULT '120',
  `imap_attach` enum('yes','no') NOT NULL DEFAULT 'no',
  `imap_notify` enum('yes','no') NOT NULL DEFAULT 'no',
  `disputeAdminStop` enum('yes','no') NOT NULL DEFAULT 'no',
  `faqcounts` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_ticketfields`
--

DROP TABLE IF EXISTS `msp_ticketfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_ticketfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticketID` varchar(20) NOT NULL DEFAULT '',
  `fieldID` int(15) NOT NULL DEFAULT '0',
  `replyID` int(15) NOT NULL DEFAULT '0',
  `fieldData` text,
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `fldid_index` (`fieldID`),
  KEY `repid_index` (`replyID`)
) ENGINE=MyISAM AUTO_INCREMENT=1294 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_tickethistory`
--

DROP TABLE IF EXISTS `msp_tickethistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_tickethistory` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` int(11) NOT NULL DEFAULT '0',
  `action` text,
  `ip` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ticket_index` (`ticketID`)
) ENGINE=MyISAM AUTO_INCREMENT=548 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_tickets`
--

DROP TABLE IF EXISTS `msp_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `lastrevision` int(30) NOT NULL DEFAULT '0',
  `department` int(8) NOT NULL DEFAULT '0',
  `assignedto` varchar(200) NOT NULL DEFAULT '',
  `visitorID` int(8) NOT NULL DEFAULT '0',
  `subject` varchar(250) NOT NULL DEFAULT '',
  `mailBodyFilter` varchar(30) NOT NULL DEFAULT '',
  `comments` text,
  `priority` varchar(250) NOT NULL DEFAULT '',
  `replyStatus` enum('start','visitor','admin') NOT NULL DEFAULT 'start',
  `ticketStatus` enum('open','close','closed') NOT NULL DEFAULT 'open',
  `ipAddresses` varchar(250) NOT NULL DEFAULT '',
  `ticketNotes` text,
  `isDisputed` enum('yes','no') NOT NULL DEFAULT 'no',
  `disPostPriv` enum('yes','no') NOT NULL DEFAULT 'yes',
  `source` varchar(10) NOT NULL DEFAULT 'standard',
  `spamFlag` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `depid_index` (`department`),
  KEY `pry_index` (`priority`),
  KEY `isdis_index` (`isDisputed`),
  KEY `ts_index` (`ts`),
  KEY `vis_index` (`visitorID`)
) ENGINE=MyISAM AUTO_INCREMENT=189 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_userdepts`
--

DROP TABLE IF EXISTS `msp_userdepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_userdepts` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL DEFAULT '0',
  `deptID` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid_index` (`userID`),
  KEY `depid_index` (`deptID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_users`
--

DROP TABLE IF EXISTS `msp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `email2` text,
  `accpass` varchar(40) NOT NULL DEFAULT '',
  `signature` text,
  `notify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `pageAccess` text,
  `emailSigs` enum('yes','no') NOT NULL DEFAULT 'no',
  `notePadEnable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `delPriv` enum('yes','no') NOT NULL DEFAULT 'no',
  `nameFrom` varchar(250) NOT NULL DEFAULT '',
  `emailFrom` varchar(250) NOT NULL DEFAULT '',
  `assigned` enum('yes','no') NOT NULL DEFAULT 'no',
  `timezone` varchar(50) NOT NULL DEFAULT '0',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `notes` text,
  `ticketHistory` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailbox` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailFolders` int(3) NOT NULL DEFAULT '5',
  `mailDeletion` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailScreen` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailCopy` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailPurge` int(3) NOT NULL DEFAULT '0',
  `addpages` text,
  `mergeperms` enum('yes','no') NOT NULL DEFAULT 'yes',
  `digest` enum('yes','no') NOT NULL DEFAULT 'yes',
  `digestasg` enum('yes','no') NOT NULL DEFAULT 'no',
  `profile` enum('yes','no') NOT NULL DEFAULT 'yes',
  `helplink` enum('yes','no') NOT NULL DEFAULT 'no',
  `defDays` int(3) NOT NULL DEFAULT '45',
  `editperms` text,
  PRIMARY KEY (`id`),
  KEY `email_index` (`email`),
  KEY `nty_index` (`notify`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `msp_usersaccess`
--

DROP TABLE IF EXISTS `msp_usersaccess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msp_usersaccess` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `page` varchar(100) NOT NULL DEFAULT '',
  `userID` varchar(250) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_index` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-11 13:41:15
