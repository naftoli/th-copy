<?php

class systemSettings {

  public $datetime;
  public $settings;
  public $upload;

  const ENTRY_LOG_FILENAME = 'log-{date}.csv';
  const REPORT_LOG_FILENAME = 'reports-{date}.csv';

  public function batchEnableDisable($fields) {
    $opt = ($_POST['action'] == 'enable' ? 'yes' : 'no');
    foreach (array_keys($fields) AS $k) {
      if (in_array($k, $_POST['tbls'])) {
        switch ($k) {
          case 'users':
            $tbl   = 'users';
            $field = 'enabled';
            break;
          case 'portal':
            $tbl   = 'portal';
            $field = 'enabled';
            break;
          case 'fields':
            $tbl   = 'cusfields';
            $field = 'enField';
            break;
          case 'responses':
            $tbl   = 'responses';
            $field = 'enResponse';
            break;
          case 'imap':
            $tbl   = 'imap';
            $field = 'im_piping';
            break;
          case 'faq-cat':
            $tbl   = 'categories';
            $field = 'enCat';
            break;
          case 'faq-que':
            $tbl   = 'faq';
            $field = 'enFaq';
            break;
        }
        // For users, we skip ID 1..
        if ($k == 'users') {
          mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . $tbl . "` SET
	        `" . $field . "` = '{$opt}'
		      WHERE `id`  != '1'
	        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        } else {
          mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . $tbl . "` SET
	        `" . $field . "` = '{$opt}'
	        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        }
      }
    }
  }

  public function exportReportCSV($dl) {
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    global $msg_reports7, $msg_reports8, $msg_reports9, $msg_reports10, $msg_reports11, $msg_script21;
    $sep  = ',';
    $file = PATH . 'export/' . str_replace('{date}', date('dmY-his'), systemSettings::REPORT_LOG_FILENAME);
    if ($this->settings->disputes == 'yes') {
      $data = $msg_reports7 . $sep . $msg_reports8 . $sep . $msg_reports9 . $sep . $msg_reports10 . $sep . $msg_reports11 . mswDefineNewline();
    } else {
      $data = $msg_reports7 . $sep . $msg_reports8 . $sep . $msg_reports9 . mswDefineNewline();
    }
    $from  = (isset($_POST['from']) && $this->datetime->mswDatePickerFormat($_POST['from']) != '0000-00-00' ? $_POST['from'] : $this->datetime->mswConvertMySQLDate(date('Y-m-d', strtotime('-6 months', $this->datetime->mswTimeStamp()))));
    $to    = (isset($_POST['to']) && $this->datetime->mswDatePickerFormat($_POST['to']) != '0000-00-00' ? $_POST['to'] : $this->datetime->mswConvertMySQLDate(date('Y-m-d', $this->datetime->mswTimeStamp())));
    $view  = (isset($_POST['view']) && in_array($_POST['view'], array(
      'month',
      'day'
    )) ? $_POST['view'] : 'month');
    $dept  = (isset($_POST['dept']) ? $_POST['dept'] : '0');
    // Get data..
    $where = 'WHERE DATE(FROM_UNIXTIME(`ts`)) BETWEEN \'' . $this->datetime->mswDatePickerFormat($from) . '\' AND \'' . $this->datetime->mswDatePickerFormat($to) . '\'';
    if (substr($dept, 0, 1) == 'u') {
      $where .= mswDefineNewline() . 'AND FIND_IN_SET(\'' . substr($dept, 1) . '\',`assignedto`) > 0';
    } else {
      if ($dept > 0) {
        $where .= mswDefineNewline() . 'AND `department` = \'' . $dept . '\'';
      }
    }
    $where .= mswDefineNewline() . 'AND `assignedto` != \'waiting\'';
    switch ($view) {
      case 'month':
        $qRE = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,MONTH(FROM_UNIXTIME(`ts`)) AS `m`,YEAR(FROM_UNIXTIME(`ts`)) AS `y` FROM " . DB_PREFIX . "tickets
               $where
		           AND `spamFlag` = 'no'
               GROUP BY MONTH(FROM_UNIXTIME(`ts`)),YEAR(FROM_UNIXTIME(`ts`))
               ORDER BY 2
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
      case 'day':
        $qRE = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `d` FROM " . DB_PREFIX . "tickets
               $where
               AND `spamFlag` = 'no'
               GROUP BY DATE(FROM_UNIXTIME(`ts`))
               ORDER BY 2
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
    }
    while ($REP = mysqli_fetch_object($qRE)) {
      switch ($view) {
        case 'month':
          // Open tickets..
          $C1 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                $where
                AND `ticketStatus`             = 'open'
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                "));
          // Closed tickets..
          $C2 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                $where
                AND `ticketStatus`             = 'close'
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                "));
          if ($this->settings->disputes == 'yes') {
            // Open disputes..
            $C3 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                  $where
                  AND `ticketStatus`             = 'open'
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                  AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                  "));
            // Closed disputes..
            $C4 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                  $where
                  AND `ticketStatus`             = 'close'
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                  AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                  "));
          }
          break;
        case 'day':
          // Open tickets..
          $C1 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                $where
                AND `ticketStatus`             = 'open'
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                "));
          // Closed tickets..
          $C2 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                $where
                AND `ticketStatus`             = 'close'
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                "));
          if ($this->settings->disputes == 'yes') {
            // Open disputes..
            $C3 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                  $where
                  AND `ticketStatus`             = 'open'
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                  "));
            // Closed disputes..
            $C4 = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM " . DB_PREFIX . "tickets
                  $where
                  AND `ticketStatus`             = 'close'
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                  "));
          }
          break;
      }
      $cnt1 = (isset($C1->c) ? $C1->c : '0');
      $cnt2 = (isset($C2->c) ? $C2->c : '0');
      $cnt3 = (isset($C3->c) ? $C3->c : '0');
      $cnt4 = (isset($C4->c) ? $C4->c : '0');
      if ($this->settings->disputes == 'yes') {
        $data .= ($view == 'day' ? date($this->settings->dateformat, strtotime($REP->d)) : $msg_script21[($REP->m - 1)] . ' ' . $REP->y) . $sep;
        $data .= number_format($cnt1) . $sep;
        $data .= number_format($cnt2) . $sep;
        $data .= number_format($cnt3) . $sep;
        $data .= number_format($cnt4) . mswDefineNewline();
      } else {
        $data .= ($view == 'day' ? date($this->settings->dateformat, strtotime($REP->d)) : $msg_script21[($REP->m - 1)] . ' ' . $REP->y) . $sep;
        $data .= number_format($cnt1) . $sep;
        $data .= number_format($cnt2) . mswDefineNewline();
      }
    }
    if ($data) {
      // Save file to server and download..
      $dl->write($file, rtrim($data));
      return $file;
    }
    return 'none';
  }

  public function exportLogFile($dl) {
    global $msg_log15, $msg_log14;
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    } else {
      $file  = PATH . 'export/' . str_replace('{date}', date('dmY-his'), systemSettings::ENTRY_LOG_FILENAME);
      $data  = '';
      $sepr  = ',';
      $from  = (isset($_POST['from']) && $this->datetime->mswDatePickerFormat($_POST['from']) != '0000-00-00' ? $_POST['from'] : '');
      $to    = (isset($_POST['to']) && $this->datetime->mswDatePickerFormat($_POST['to']) != '0000-00-00' ? $_POST['to'] : '');
      $keys  = '';
      $where = array();
      if (isset($_POST['keys']) && $_POST['keys']) {
        $chop  = explode(' ', $_POST['keys']);
        $words = '';
        for ($i = 0; $i < count($chop); $i++) {
          $words .= ($i ? 'OR ' : 'WHERE (') . "`" . DB_PREFIX . "portal`.`name` LIKE '%" . mswSafeImportString($chop[$i]) . "%' OR `" . DB_PREFIX . "users`.`name` LIKE '%" . mswSafeImportString($chop[$i]) . "%' ";
        }
        if ($words) {
          $where[] = $words . ')';
        }
      }
      if ($from && $to) {
        $where[] = (!empty($where) ? 'AND ' : 'WHERE ') . 'DATE(FROM_UNIXTIME(`' . DB_PREFIX . 'log`.`ts`)) BETWEEN \'' . $this->datetime->mswDatePickerFormat($from) . '\' AND \'' . $this->datetime->mswDatePickerFormat($to) . '\'';
      }
      $q_log = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
               `" . DB_PREFIX . "log`.`ts` AS `lts`,
               `" . DB_PREFIX . "log`.`userID` AS `personID`,
               `" . DB_PREFIX . "portal`.`name` AS `portalName`,
               `" . DB_PREFIX . "log`.`ip` AS `entryLogIP`,
               `" . DB_PREFIX . "users`.`name` AS `userName`
               FROM `" . DB_PREFIX . "log`
               LEFT JOIN `" . DB_PREFIX . "users`
               ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "users`.`id`
               LEFT JOIN `" . DB_PREFIX . "portal`
               ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "portal`.`id`
               " . (!empty($where) ? mswSafeImportString(implode(mswDefineNewline(), $where)) : '') . "
               ORDER BY `" . DB_PREFIX . "log`.`id` DESC
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($LOG = mysqli_fetch_object($q_log)) {
        $data .= mswCleanCSV(($LOG->type == 'acc' ? $LOG->portalName : $LOG->userName), $sepr) . $sepr . ($LOG->type == 'user' ? $msg_log15 : $msg_log14) . $sepr . mswCleanCSV($LOG->entryLogIP, $sepr) . $sepr . mswCleanCSV($this->datetime->mswDateTimeDisplay($LOG->lts, $this->settings->dateformat), $sepr) . $sepr . mswCleanCSV($this->datetime->mswDateTimeDisplay($LOG->lts, $this->settings->timeformat), $sepr) . mswDefineNewline();
      }
      // Save file to server and download..
      $dl->write($file, rtrim($data));
      if (file_exists($file)) {
        return $file;
      }
      return 'none';
    }
  }

  public function clearLogFile() {
    mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "log`") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function deleteLogs() {
    if (!empty($_POST['del'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "log`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    }
  }

  public function updateBackupEmails() {
    $_POST = mswMultiDimensionalArrayMap('mswSafeImportString', $_POST);
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "settings` SET
    `backupEmails` = '{$_POST['emails']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function updateSettings() {
    $_POST                     = mswMultiDimensionalArrayMap('mswSafeImportString', $_POST);
    // Defaults if not set..
    $_POST['attachment']       = (isset($_POST['attachment']) ? 'yes' : 'no');
    $_POST['rename']           = (isset($_POST['rename']) ? 'yes' : 'no');
    $_POST['weekStart']        = (isset($_POST['weekStart']) && in_array($_POST['weekStart'], array(
      'sun',
      'mon'
    )) ? $_POST['weekStart'] : 'sun');
    $_POST['enSpamSum']        = (isset($_POST['enSpamSum']) && in_array($_POST['enSpamSum'], array(
      'yes',
      'no'
    )) ? $_POST['enSpamSum'] : 'yes');
    $_POST['enableBBCode']     = (isset($_POST['enableBBCode']) ? 'yes' : 'no');
    $_POST['disputes']         = (isset($_POST['disputes']) ? 'yes' : 'no');
    $_POST['multiplevotes']    = (isset($_POST['multiplevotes']) ? 'yes' : 'no');
    $_POST['enableVotes']      = (isset($_POST['enableVotes']) ? 'yes' : 'no');
    $_POST['enCapLogin']       = (isset($_POST['enCapLogin']) ? 'yes' : 'no');
    $_POST['sysstatus']        = (isset($_POST['sysstatus']) ? 'yes' : 'no');
    $_POST['autoenable']       = ($_POST['autoenable'] ? $this->datetime->mswDatePickerFormat($_POST['autoenable']) : '1970-01-01');
    $_POST['kbase']            = (isset($_POST['kbase']) ? 'yes' : 'no');
    $_POST['scriptpath']       = systemSettings::filterInstallationPath($_POST['scriptpath']);
    $_POST['attachpath']       = systemSettings::filterInstallationPath($_POST['attachpath']);
    $_POST['attachhref']       = systemSettings::filterInstallationPath($_POST['attachhref']);
    $_POST['attachpathfaq']    = systemSettings::filterInstallationPath($_POST['attachpathfaq']);
    $_POST['attachhreffaq']    = systemSettings::filterInstallationPath($_POST['attachhreffaq']);
    $_POST['imap_param']       = ($_POST['imap_param'] ? $_POST['imap_param'] : 'pipe');
    $_POST['renamefaq']        = (isset($_POST['renamefaq']) ? 'yes' : 'no');
    $_POST['smtp_debug']       = (isset($_POST['smtp_debug']) ? 'yes' : 'no');
    $_POST['createPref']       = (isset($_POST['createPref']) ? 'yes' : 'no');
    $_POST['createAcc']        = (isset($_POST['createAcc']) ? 'yes' : 'no');
    $_POST['ticketHistory']    = (isset($_POST['ticketHistory']) ? 'yes' : 'no');
    $_POST['closenotify']      = (isset($_POST['closenotify']) ? 'yes' : 'no');
    $_POST['accProfNotify']    = (isset($_POST['accProfNotify']) ? 'yes' : 'no');
    $_POST['newAccNotify']     = (isset($_POST['newAccNotify']) ? 'yes' : 'no');
    $_POST['enableLog']        = (isset($_POST['enableLog']) ? 'yes' : 'no');
    $_POST['enableMail']       = (isset($_POST['enableMail']) ? 'yes' : 'no');
    $_POST['imap_debug']       = (isset($_POST['imap_debug']) ? 'yes' : 'no');
    $_POST['imap_attach']      = (isset($_POST['imap_attach']) ? 'yes' : 'no');
    $_POST['imap_notify']      = (isset($_POST['imap_notify']) ? 'yes' : 'no');
    $_POST['apiLog']           = (isset($_POST['apiLog']) ? 'yes' : 'no');
    $_POST['disputeAdminStop'] = (isset($_POST['disputeAdminStop']) ? 'yes' : 'no');
    $_POST['faqcounts']        = (isset($_POST['faqcounts']) ? 'yes' : 'no');
    // Enforce digits..
    $_POST['maxsize']          = (isset($_POST['maxsize']) ? (int) $_POST['maxsize'] : '0');
    // Check max size against server limit..
    if ($_POST['maxsize'] > $this->upload->getMaxSize()) {
      $_POST['maxsize'] = $this->upload->getMaxSize();
    }
    $_POST['popquestions']     = (isset($_POST['popquestions']) ? (int) $_POST['popquestions'] : '10');
    $_POST['quePerPage']       = (isset($_POST['quePerPage']) ? (int) $_POST['quePerPage'] : '10');
    $_POST['cookiedays']       = (isset($_POST['cookiedays']) ? (int) $_POST['cookiedays'] : '60');
    $_POST['attachboxes']      = (isset($_POST['attachboxes']) ? (int) $_POST['attachboxes'] : '1');
    $_POST['autoClose']        = (isset($_POST['autoClose']) ? (int) $_POST['autoClose'] : '0');
    $_POST['smtp_port']        = (isset($_POST['smtp_port']) ? (int) $_POST['smtp_port'] : '25');
    $_POST['loginLimit']       = (isset($_POST['loginLimit']) ? (int) $_POST['loginLimit'] : '0');
    $_POST['banTime']          = (isset($_POST['banTime']) ? (int) $_POST['banTime'] : '25');
    $_POST['minPassValue']     = (isset($_POST['minPassValue']) ? (int) $_POST['minPassValue'] : '8');
    $_POST['minTickDigits']    = (isset($_POST['minTickDigits']) ? (int) $_POST['minTickDigits'] : '5');
    $_POST['imap_timeout']     = (isset($_POST['imap_timeout']) ? (int) $_POST['imap_timeout'] : '0');
    $_POST['imap_memory']      = (isset($_POST['imap_memory']) ? (int) $_POST['imap_memory'] : '0');
    // Restrictions..
    if (LICENCE_VER == 'locked') {
      $_POST['attachboxes']  = RESTR_ATTACH;
      $_POST['adminFooter']  = 'To add your own footer code, go to &quot;Settings &amp; Tools > Other Options > Edit Footers&quot;';
      $_POST['publicFooter'] = 'To add your own footer code, go to &quot;Settings &amp; Tools > Other Options > Edit Footers&quot;';
    }
    // Serialized data..
    $langSets = (!empty($_POST['templateSet']) ? serialize($_POST['templateSet']) : '');
    if ($_POST['defKeepLogs']['user'] == '') {
      $_POST['defKeepLogs']['user'] = '0';
    }
    if ($_POST['defKeepLogs']['acc'] == '') {
      $_POST['defKeepLogs']['acc'] = '0';
    }
    $defLog   = (!empty($_POST['defKeepLogs']) ? serialize($_POST['defKeepLogs']) : '');
    $handlers = (!empty($_POST['apiHandlers']) ? mswSafeImportString(implode(',', $_POST['apiHandlers'])) : '');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE IGNORE `" . DB_PREFIX . "settings` SET
    `website`              = '{$_POST['website']}',
    `email`                = '{$_POST['email']}',
    `replyto`              = '{$_POST['replyto']}',
    `scriptpath`           = '{$_POST['scriptpath']}',
    `attachpath`           = '{$_POST['attachpath']}',
    `attachhref`           = '{$_POST['attachhref']}',
    `attachpathfaq`        = '{$_POST['attachpathfaq']}',
    `attachhreffaq`        = '{$_POST['attachhreffaq']}',
    `language`             = '{$_POST['language']}',
    `langSets`             = '" . mswSafeImportString($langSets) . "',
    `dateformat`           = '{$_POST['dateformat']}',
    `timeformat`           = '{$_POST['timeformat']}',
    `timezone`             = '{$_POST['timezone']}',
    `weekStart`            = '{$_POST['weekStart']}',
    `jsDateFormat`         = '{$_POST['jsDateFormat']}',
    `kbase`                = '{$_POST['kbase']}',
    `enableVotes`          = '{$_POST['enableVotes']}',
    `multiplevotes`        = '{$_POST['multiplevotes']}',
    `popquestions`         = '{$_POST['popquestions']}',
    `quePerPage`           = '{$_POST['quePerPage']}',
    `cookiedays`           = '{$_POST['cookiedays']}',
    `renamefaq`            = '{$_POST['renamefaq']}',
    `attachment`           = '{$_POST['attachment']}',
    `rename`               = '{$_POST['rename']}',
    `attachboxes`          = '{$_POST['attachboxes']}',
    `filetypes`            = '{$_POST['filetypes']}',
    `maxsize`              = '{$_POST['maxsize']}',
    `enableBBCode`         = '{$_POST['enableBBCode']}',
    `afolder`              = '{$_POST['afolder']}',
    `autoClose`            = '{$_POST['autoClose']}',
    `smtp_host`            = '{$_POST['smtp_host']}',
    `smtp_user`            = '{$_POST['smtp_user']}',
    `smtp_pass`            = '{$_POST['smtp_pass']}',
    `smtp_port`            = '{$_POST['smtp_port']}',
    `smtp_security`        = '{$_POST['smtp_security']}',
    `smtp_debug`           = '{$_POST['smtp_debug']}',
    `adminFooter`          = '{$_POST['adminFooter']}',
    `publicFooter`         = '{$_POST['publicFooter']}',
    `apiKey`               = '{$_POST['apiKey']}',
    `apiLog`               = '{$_POST['apiLog']}',
    `apiHandlers`          = '{$handlers}',
    `recaptchaPrivateKey`  = '{$_POST['recaptchaPrivateKey']}',
    `recaptchaPublicKey`   = '{$_POST['recaptchaPublicKey']}',
    `enCapLogin`           = '{$_POST['enCapLogin']}',
    `sysstatus`            = '{$_POST['sysstatus']}',
    `autoenable`           = '{$_POST['autoenable']}',
    `disputes`             = '{$_POST['disputes']}',
    `offlineReason`        = '{$_POST['offlineReason']}',
    `createPref`           = '{$_POST['createPref']}',
    `createAcc`            = '{$_POST['createAcc']}',
    `loginLimit`           = '{$_POST['loginLimit']}',
    `banTime`              = '{$_POST['banTime']}',
    `ticketHistory`        = '{$_POST['ticketHistory']}',
    `closenotify`          = '{$_POST['closenotify']}',
    `accProfNotify`        = '{$_POST['accProfNotify']}',
    `minPassValue`         = '{$_POST['minPassValue']}',
    `newAccNotify`         = '{$_POST['newAccNotify']}',
    `recaptchaLang`        = '{$_POST['recaptchaLang']}',
    `recaptchaTheme`       = '{$_POST['recaptchaTheme']}',
    `enableLog`            = '{$_POST['enableLog']}',
    `defKeepLogs`          = '" . mswSafeImportString($defLog) . "',
    `minTickDigits`        = '{$_POST['minTickDigits']}',
    `enableMail`           = '{$_POST['enableMail']}',
    `imap_debug`           = '{$_POST['imap_debug']}',
    `imap_param`           = '{$_POST['imap_param']}',
    `imap_memory`          = '{$_POST['imap_memory']}',
    `imap_timeout`         = '{$_POST['imap_timeout']}',
    `imap_attach`          = '{$_POST['imap_attach']}',
    `imap_notify`          = '{$_POST['imap_notify']}',
    `disputeAdminStop`     = '{$_POST['disputeAdminStop']}',
    `faqcounts`            = '{$_POST['faqcounts']}'
    WHERE `id`             = '1'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  private function filterInstallationPath($path) {
    if (substr($path, -1) == '/') {
      $path = substr_replace($path, '', -1);
    }
    return $path;
  }

  // Check for new version..
  public function mswSoftwareVersionCheck() {
    $url = 'https://www.maianscriptworld.co.uk/version-check.php?id=' . SCRIPT_ID;
    $str = '';
    if (function_exists('curl_init')) {
      $ch = @curl_init();
      @curl_setopt($ch, CURLOPT_URL, $url);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $result = @curl_exec($ch);
      @curl_close($ch);
      if ($result) {
        if ($result != $this->settings->softwareVersion) {
          $str = 'Installed Version: ' . $this->settings->softwareVersion . mswDefineNewline();
          $str .= 'Current Version: ' . $result . mswDefineNewline() . mswDefineNewline();
          $str .= '<i class="fa fa-warning fa-fw"></i> Your version is out of date.' . mswDefineNewline() . mswDefineNewline();
          $str .= 'Download new version at:' . mswDefineNewline();
          $str .= '<a href="https://www.' . SCRIPT_URL . '/download.html" onclick="window.open(this);return false">www.' . SCRIPT_URL . '</a>';
        } else {
          $str = 'Current Version: ' . $this->settings->softwareVersion . mswDefineNewline() . mswDefineNewline() . '<i class="fa fa-check fa-fw"></i> You are currently using the latest version';
        }
      }
    } else {
      if (@ini_get('allow_url_fopen') == '1') {
        $result = @file_get_contents($url);
        if ($result) {
          if ($result != $this->settings->softwareVersion) {
            $str = 'Installed Version: ' . $this->settings->softwareVersion . mswDefineNewline();
            $str .= 'Current Version: ' . $result . mswDefineNewline() . mswDefineNewline();
            $str .= '<i class="fa fa-warning fa-fw"></i> Your version is out of date.' . mswDefineNewline() . mswDefineNewline();
            $str .= 'Download new version at:' . mswDefineNewline();
            $str .= '<a href="https://www.' . SCRIPT_URL . '/download.html" onclick="window.open(this);return false">www.' . SCRIPT_URL . '</a>';
          } else {
            $str = 'Current Version: ' . $this->settings->softwareVersion . mswDefineNewline() . mswDefineNewline() . '<i class="fa fa-check fa-fw"></i> You are currently using the latest version';
          }
        }
      }
    }
    // Nothing?
    if ($str == '') {
      $str = 'Server check functions not available.' . mswDefineNewline() . mswDefineNewline();
      $str .= 'Please visit <a href="https://www.' . SCRIPT_URL . '/download.html" onclick="window.open(this);return false">www.' . SCRIPT_URL . '</a> to check for updates';
    }
    return $str;
  }

}

?>