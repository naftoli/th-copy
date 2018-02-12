-- --------------------------------------------------------
-- Database Schema:  Maian Support
-- --------------------------------------------------------

-- Dumping structure for table msp_attachments
CREATE TABLE IF NOT EXISTS `msp_attachments` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` varchar(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `replyID` int(11) NOT NULL DEFAULT '0',
  `department` int(5) NOT NULL DEFAULT '0',
  `fileName` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `fileSize` varchar(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `mimeType` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `repid_index` (`replyID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_ban
CREATE TABLE IF NOT EXISTS `msp_ban` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `ip` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `count` int(5) NOT NULL DEFAULT '0',
  `banstamp` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_categories
CREATE TABLE IF NOT EXISTS `msp_categories` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `summary` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `enCat` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `subcat` int(5) NOT NULL DEFAULT '0',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_cusfields
CREATE TABLE IF NOT EXISTS `msp_cusfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fieldInstructions` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `fieldType` enum('textarea','input','select','checkbox') NOT NULL DEFAULT 'input',
  `fieldReq` enum('yes','no') NOT NULL DEFAULT 'no',
  `fieldOptions` text default null COLLATE 'utf8_general_ci',
  `fieldLoc` varchar(25) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `repeatPref` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enField` enum('yes','no') NOT NULL DEFAULT 'yes',
  `departments` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_departments
CREATE TABLE IF NOT EXISTS `msp_departments` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `showDept` enum('yes','no') NOT NULL DEFAULT 'no',
  `dept_subject` text default null COLLATE 'utf8_general_ci',
  `dept_comments` text default null COLLATE 'utf8_general_ci',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `manual_assign` enum('yes','no') NOT NULL DEFAULT 'no',
  `days` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_disputes
CREATE TABLE IF NOT EXISTS `msp_disputes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticketID` int(15) NOT NULL DEFAULT '0',
  `visitorID` int(8) NOT NULL DEFAULT '0',
  `postPrivileges` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `vis_index` (`visitorID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_faq
CREATE TABLE IF NOT EXISTS `msp_faq` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `question` text default null COLLATE 'utf8_general_ci',
  `answer` text default null COLLATE 'utf8_general_ci',
  `kviews` int(10) NOT NULL DEFAULT '0',
  `kuseful` int(10) NOT NULL DEFAULT '0',
  `knotuseful` int(10) NOT NULL DEFAULT '0',
  `enFaq` enum('yes','no') NOT NULL DEFAULT 'yes',
  `featured` enum('yes','no') NOT NULL DEFAULT 'no',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_faqassign
CREATE TABLE IF NOT EXISTS `msp_faqassign` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `question` int(7) NOT NULL DEFAULT '0',
  `itemID` int(7) NOT NULL DEFAULT '0',
  `desc` varchar(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `que_index` (`question`),
  KEY `att_index` (`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_faqattach
CREATE TABLE IF NOT EXISTS `msp_faqattach` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `remote` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `path` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `size` varchar(30) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `enAtt` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mimeType` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_imap
CREATE TABLE IF NOT EXISTS `msp_imap` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `im_piping` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_protocol` enum('pop3','imap') NOT NULL DEFAULT 'pop3',
  `im_host` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_user` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_pass` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_port` int(5) NOT NULL DEFAULT '110',
  `im_name` varchar(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_flags` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_attach` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_move` varchar(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_messages` int(3) NOT NULL DEFAULT '20',
  `im_ssl` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_priority` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_dept` int(5) NOT NULL DEFAULT '0',
  `im_email` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `im_spam` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_spam_purge` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_score` varchar(10) NOT NULL DEFAULT '1.0' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_imap_b8
CREATE TABLE IF NOT EXISTS `msp_imap_b8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tokens` int(5) NOT NULL DEFAULT '0',
  `min_size` int(5) NOT NULL DEFAULT '0',
  `max_size` int(5) NOT NULL DEFAULT '0',
  `min_dev` varchar(5) NOT NULL DEFAULT '0.5' COLLATE 'utf8_general_ci',
  `x_constant` varchar(5) NOT NULL DEFAULT '0.5' COLLATE 'utf8_general_ci',
  `s_constant` varchar(5) NOT NULL DEFAULT '0.3' COLLATE 'utf8_general_ci',
  `learning` enum('yes','no') NOT NULL DEFAULT 'yes',
  `num_parse` enum('yes','no') NOT NULL DEFAULT 'no',
  `uri_parse` enum('yes','no') NOT NULL DEFAULT 'yes',
  `html_parse` enum('yes','no') NOT NULL DEFAULT 'yes',
  `multibyte` enum('yes','no') NOT NULL DEFAULT 'no',
  `encoder` varchar(50) NOT NULL DEFAULT 'utf-8' COLLATE 'utf8_general_ci',
  `skipFilters` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_imap_b8_filter
CREATE TABLE IF NOT EXISTS `msp_imap_b8_filter` (
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `count_ham` int(10) unsigned NOT NULL DEFAULT '0',
  `count_spam` int(10) unsigned NOT NULL DEFAULT '0',
  `ts` int(30) NOT NULL DEFAULT '0',
  PRIMARY KEY (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_levels
CREATE TABLE IF NOT EXISTS `msp_levels` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `display` enum('yes','no') NOT NULL DEFAULT 'no',
  `marker` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_log
CREATE TABLE IF NOT EXISTS `msp_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `userID` int(5) NOT NULL DEFAULT '0',
  `ip` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `type` enum('user','acc') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  KEY `useid_index` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_mailassoc
CREATE TABLE IF NOT EXISTS `msp_mailassoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staffID` int(8) NOT NULL DEFAULT '0',
  `mailID` int(8) NOT NULL DEFAULT '0',
  `folder` varchar(10) NOT NULL DEFAULT 'inbox' COLLATE 'utf8_general_ci',
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `lastUpdate` int(30) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`),
  KEY `mail_index` (`mailID`),
  KEY `status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_mailbox
CREATE TABLE IF NOT EXISTS `msp_mailbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `staffID` int(8) NOT NULL DEFAULT '0',
  `subject` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `message` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_mailfolders
CREATE TABLE IF NOT EXISTS `msp_mailfolders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staffID` int(8) NOT NULL DEFAULT '0',
  `folder` varchar(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_mailreplies
CREATE TABLE IF NOT EXISTS `msp_mailreplies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `mailID` int(8) NOT NULL DEFAULT '0',
  `staffID` int(8) NOT NULL DEFAULT '0',
  `message` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `mail_index` (`mailID`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_pages
CREATE TABLE IF NOT EXISTS `msp_pages` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `title` text default null COLLATE 'utf8_general_ci',
  `information` text default null COLLATE 'utf8_general_ci',
  `enPage` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `secure` enum('yes','no') NOT NULL DEFAULT 'no',
  `accounts` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_portal
CREATE TABLE IF NOT EXISTS `msp_portal` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `ts` int(30) NOT NULL DEFAULT '0',
  `email` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `userPass` varchar(40) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `verified` enum('yes','no') NOT NULL DEFAULT 'no',
  `timezone` varchar(50) NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
  `ip` text default null COLLATE 'utf8_general_ci',
  `notes` text default null COLLATE 'utf8_general_ci',
  `reason` text default null COLLATE 'utf8_general_ci',
  `system1` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `system2` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `language` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `em_index` (`email`),
  KEY `nm_index` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_replies
CREATE TABLE IF NOT EXISTS `msp_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` int(15) NOT NULL DEFAULT '0',
  `comments` text default null COLLATE 'utf8_general_ci',
  `mailBodyFilter` varchar(30) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `replyType` enum('none','visitor','admin') NOT NULL DEFAULT 'none',
  `replyUser` int(8) NOT NULL DEFAULT '0',
  `isMerged` enum('yes','no') NOT NULL DEFAULT 'no',
  `ipAddresses` text default null COLLATE 'utf8_general_ci',
  `disputeUser` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `repuse_index` (`replyUser`),
  KEY `disuse_index` (`disputeUser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_responses
CREATE TABLE IF NOT EXISTS `msp_responses` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `title` text default null COLLATE 'utf8_general_ci',
  `answer` text default null COLLATE 'utf8_general_ci',
  `enResponse` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `departments` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_settings
CREATE TABLE IF NOT EXISTS `msp_settings` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `website` varchar(150) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `email` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `replyto` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `scriptpath` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `attachpath` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `attachhref` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `attachpathfaq` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `attachhreffaq` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `language` varchar(250) DEFAULT 'english' COLLATE 'utf8_general_ci',
  `langSets` text default null,
  `dateformat` varchar(20) NOT NULL DEFAULT 'D j M Y, G:ia' COLLATE 'utf8_general_ci',
  `timeformat` varchar(15) NOT NULL DEFAULT 'H:iA' COLLATE 'utf8_general_ci',
  `timezone` varchar(50) NOT NULL DEFAULT 'Europe/London' COLLATE 'utf8_general_ci',
  `weekStart` enum('mon','sun') NOT NULL DEFAULT 'sun',
  `jsDateFormat` varchar(15) NOT NULL DEFAULT 'DD/MM/YYYY' COLLATE 'utf8_general_ci',
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
  `filetypes` text default null COLLATE 'utf8_general_ci',
  `maxsize` int(15) NOT NULL DEFAULT '200',
  `enableBBCode` enum('yes','no') NOT NULL DEFAULT 'yes',
  `afolder` varchar(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `autoClose` int(5) NOT NULL DEFAULT '0',
  `autoCloseMail` enum('yes','no') NOT NULL DEFAULT 'yes',
  `smtp_host` varchar(100) NOT NULL DEFAULT 'localhost' COLLATE 'utf8_general_ci',
  `smtp_user` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `smtp_pass` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `smtp_port` int(4) NOT NULL DEFAULT '25',
  `smtp_security` varchar(10) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `smtp_debug` enum('yes','no') NOT NULL DEFAULT 'no',
  `prodKey` char(60) NOT NULL DEFAULT '',
  `publicFooter` text default null COLLATE 'utf8_general_ci',
  `adminFooter` text default null COLLATE 'utf8_general_ci',
  `encoderVersion` varchar(5) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `softwareVersion` varchar(10) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `apiKey` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `apiLog` enum('yes','no') NOT NULL DEFAULT 'no',
  `apiHandlers` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `recaptchaPublicKey` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `recaptchaPrivateKey` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `enCapLogin` enum('yes','no') NOT NULL DEFAULT 'yes',
  `sysstatus` enum('yes','no') NOT NULL DEFAULT 'yes',
  `autoenable` date NOT NULL DEFAULT '1970-01-01',
  `disputes` enum('yes','no') NOT NULL DEFAULT 'no',
  `offlineReason` text default null COLLATE 'utf8_general_ci',
  `createPref` enum('yes','no') NOT NULL DEFAULT 'yes',
  `createAcc` enum('yes','no') NOT NULL DEFAULT 'yes',
  `loginLimit` int(5) NOT NULL DEFAULT '0',
  `banTime` int(5) NOT NULL DEFAULT '0',
  `ticketHistory` enum('yes','no') NOT NULL DEFAULT 'yes',
  `backupEmails` text default null COLLATE 'utf8_general_ci',
  `closenotify` enum('yes','no') NOT NULL DEFAULT 'no',
  `minPassValue` int(3) NOT NULL DEFAULT '8',
  `accProfNotify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `newAccNotify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `recaptchaTheme` varchar(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `recaptchaLang` varchar(10) NOT NULL DEFAULT 'en-GB' COLLATE 'utf8_general_ci',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  `defKeepLogs` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `minTickDigits` int(2) NOT NULL DEFAULT '5',
  `enableMail` enum('yes','no') NOT NULL DEFAULT 'yes',
  `imap_debug` enum('yes','no') NOT NULL DEFAULT 'no',
  `imap_param` varchar(10) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `imap_memory` varchar(3) NOT NULL DEFAULT '10' COLLATE 'utf8_general_ci',
  `imap_timeout` varchar(3) NOT NULL DEFAULT '120' COLLATE 'utf8_general_ci',
  `imap_attach` enum('yes','no') NOT NULL DEFAULT 'no',
  `imap_notify` enum('yes','no') NOT NULL DEFAULT 'no',
  `disputeAdminStop` enum('yes','no') NOT NULL DEFAULT 'no',
  `faqcounts` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_ticketfields
CREATE TABLE IF NOT EXISTS `msp_ticketfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticketID` varchar(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `fieldID` int(15) NOT NULL DEFAULT '0',
  `replyID` int(15) NOT NULL DEFAULT '0',
  `fieldData` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `fldid_index` (`fieldID`),
  KEY `repid_index` (`replyID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_tickethistory
CREATE TABLE IF NOT EXISTS `msp_tickethistory` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` int(11) NOT NULL DEFAULT '0',
  `action` text default null COLLATE 'utf8_general_ci',
  `ip` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `ticket_index` (`ticketID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_tickets
CREATE TABLE IF NOT EXISTS `msp_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `lastrevision` int(30) NOT NULL DEFAULT '0',
  `department` int(8) NOT NULL DEFAULT '0',
  `assignedto` varchar(200) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `visitorID` int(8) NOT NULL DEFAULT '0',
  `subject` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `mailBodyFilter` varchar(30) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `comments` text default null COLLATE 'utf8_general_ci',
  `priority` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `replyStatus` enum('start','visitor','admin') NOT NULL DEFAULT 'start',
  `ticketStatus` enum('open','close','closed') NOT NULL DEFAULT 'open',
  `ipAddresses` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `ticketNotes` text default null COLLATE 'utf8_general_ci',
  `isDisputed` enum('yes','no') NOT NULL DEFAULT 'no',
  `disPostPriv` enum('yes','no') NOT NULL DEFAULT 'yes',
  `source` varchar(10) NOT NULL DEFAULT 'standard' COLLATE 'utf8_general_ci',
  `spamFlag` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `depid_index` (`department`),
  KEY `pry_index` (`priority`),
  KEY `isdis_index` (`isDisputed`),
  KEY `ts_index` (`ts`),
  KEY `vis_index` (`visitorID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_userdepts
CREATE TABLE IF NOT EXISTS `msp_userdepts` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL DEFAULT '0',
  `deptID` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid_index` (`userID`),
  KEY `depid_index` (`deptID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_users
CREATE TABLE IF NOT EXISTS `msp_users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `email` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `email2` text default null COLLATE 'utf8_general_ci',
  `accpass` varchar(40) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `signature` text default null COLLATE 'utf8_general_ci',
  `notify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `pageAccess` text default null COLLATE 'utf8_general_ci',
  `emailSigs` enum('yes','no') NOT NULL DEFAULT 'no',
  `notePadEnable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `delPriv` enum('yes','no') NOT NULL DEFAULT 'no',
  `nameFrom` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `emailFrom` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `assigned` enum('yes','no') NOT NULL DEFAULT 'no',
  `timezone` varchar(50) NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `notes` text default null COLLATE 'utf8_general_ci',
  `ticketHistory` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailbox` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailFolders` int(3) NOT NULL DEFAULT '5',
  `mailDeletion` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailScreen` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailCopy` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailPurge` int(3) NOT NULL DEFAULT '0',
  `addpages` text default null COLLATE 'utf8_general_ci',
  `mergeperms` enum('yes','no') NOT NULL DEFAULT 'yes',
  `digest` enum('yes','no') NOT NULL DEFAULT 'yes',
  `digestasg` enum('yes','no') NOT NULL DEFAULT 'no',
  `profile` enum('yes','no') NOT NULL DEFAULT 'yes',
  `helplink` enum('yes','no') NOT NULL DEFAULT 'no',
  `defDays` int(3) NOT NULL DEFAULT '45',
  `editperms` text default null COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `email_index` (`email`),
  KEY `nty_index` (`notify`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Dumping structure for table msp_usersaccess
CREATE TABLE IF NOT EXISTS `msp_usersaccess` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `page` varchar(100) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `userID` varchar(250) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `type` varchar(32) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`),
  KEY `user_index` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

-- Data dumps
INSERT INTO `msp_departments` (`id`, `name`, `showDept`, `dept_subject`, `dept_comments`, `orderBy`, `manual_assign`) VALUES
(1, 'General Tickets', 'yes', '', '', 1, 'no'),
(2, 'Sales and Billing', 'yes', '', '', 2, 'no'),
(3, 'Technical Support', 'yes', '', '', 3, 'no');

INSERT INTO `msp_imap_b8` (`id`, `tokens`, `min_size`, `max_size`, `min_dev`, `x_constant`, `s_constant`, `learning`, `num_parse`, `uri_parse`,
`html_parse`, `multibyte`, `encoder`, `skipFilters`) VALUES (1, 15, 3, 30, '0.5', '0.5', '0.3', 'yes', 'no', 'yes', 'yes', 'yes', 'UTF-8', 'mailer-daemon');

INSERT INTO `msp_imap_b8_filter` (`token`, `count_ham`, `count_spam`, `ts`) VALUES ('b8*dbversion', 3, 0, 0);
INSERT INTO `msp_imap_b8_filter` (`token`, `count_ham`, `count_spam`, `ts`) VALUES ('b8*texts', 0, 0, 0);

INSERT INTO `msp_levels` (`id`, `name`, `display`, `marker`, `orderBy`) VALUES
(1, 'Low', 'yes', 'low', 1),
(2, 'Medium', 'yes', 'medium', 2),
(3, 'High', 'yes', 'high', 3);

INSERT INTO `msp_settings` (`id`, `website`, `email`, `replyto`, `scriptpath`, `attachpath`, `attachhref`, `attachpathfaq`, `attachhreffaq`, `language`,
`langSets`, `dateformat`, `timeformat`, `timezone`, `weekStart`, `jsDateFormat`, `kbase`, `enableVotes`, `multiplevotes`, `popquestions`, `quePerPage`,
`cookiedays`, `renamefaq`, `attachment`, `rename`, `attachboxes`, `filetypes`, `maxsize`, `enableBBCode`, `afolder`, `autoClose`, `autoCloseMail`,
`smtp_host`, `smtp_user`, `smtp_pass`, `smtp_port`, `smtp_security`, `smtp_debug`, `prodKey`, `publicFooter`, `adminFooter`, `encoderVersion`,
`softwareVersion`, `apiKey`, `apiLog`, `apiHandlers`, `recaptchaPublicKey`, `recaptchaPrivateKey`, `enCapLogin`, `sysstatus`, `autoenable`, `disputes`,
`offlineReason`, `createPref`, `createAcc`, `loginLimit`, `banTime`, `ticketHistory`, `backupEmails`, `closenotify`, `minPassValue`, `accProfNotify`,
`newAccNotify`, `recaptchaTheme`, `recaptchaLang`, `enableLog`, `defKeepLogs`, `minTickDigits`, `enableMail`, `imap_debug`, `imap_param`, `imap_memory`,
`imap_timeout`, `disputeAdminStop`) VALUES (
1, 'HelpDesk', '', '', '', '', '', '', '', 'english', 'a:1:{s:7:"english";s:12:"_default_set";}', 'd M Y', 'H:iA', 'Europe/London',
'sun', 'DD-MM-YYYY', 'yes', 'yes', 'yes', 10, 10, 360, 'no', 'yes', 'yes', 5, '.jpg|.zip|.gif|.rar|.png|.pdf', 1048576, 'yes', 'admin', 0, 'yes', '', '',
'', 587, '', 'no', '', '', '', '', '', '', 'yes', 'json,xml', '', '', 'yes', 'yes', '0000-00-00', 'no', '', 'no', 'yes', 5, 5, 'yes', '', 'no', 8,
'yes', 'yes', 'red', 'en', 'yes', 'a:2:{s:4:"user";s:2:"50";s:3:"acc";s:2:"50";}', 5, 'yes', 'yes', 'pipe', '0', '0', 'no');

INSERT INTO `msp_users` (`id`, `ts`, `name`, `email`, `email2`, `accpass`, `signature`, `notify`, `pageAccess`, `emailSigs`, `notePadEnable`, `delPriv`, `nameFrom`, `emailFrom`, `assigned`, `timezone`, `enabled`, `notes`, `ticketHistory`, `enableLog`, `mailbox`, `mailFolders`, `mailDeletion`, `mailScreen`, `mailCopy`, `mailPurge`, `addpages`, `mergeperms`, `digest`, `digestasg`, `profile`, `helplink`, `defDays`) VALUES
(1, UNIX_TIMESTAMP(), 'admin', 'admin@example.com', NULL, '', '', 'yes', '', 'no', 'yes', 'yes', '', '', 'no', '0', 'yes', NULL, 'yes', 'yes', 'yes', 5, 'yes', 'yes', 'yes', 0, NULL, 'yes', 'yes', 'no', 'yes', 'yes', 45);