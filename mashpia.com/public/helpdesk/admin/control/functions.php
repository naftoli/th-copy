<?php

function loadIPAddresses($ip) {
  $t = '&nbsp;';
  if ($ip) {
    if (strpos($ip, ',') !== false) {
      $t = array();
      foreach (explode(',', $ip) AS $ipp) {
        $ipt = trim($ipp);
        if ($ipt) {
          $t[] = '<a href="' . str_replace('{ip}', $ipt, IP_LOOKUP) . '" onclick="window.open(this);return false">' . $ipt . '</a>';
        }
      }
      return implode(', ', $t);
    } else {
      return '<a href="' . str_replace('{ip}', $ip, IP_LOOKUP) . '" onclick="window.open(this);return false">' . $ip . '</a>';
    }
  }
  return $t;
}

function mswUnreadMailbox($id) {
  return mswRowCount('mailassoc WHERE `staffID` = \'' . $id . '\' AND `folder` = \'inbox\' AND `status` = \'unread\'');
}

function mswClearExportFiles() {
  if (is_dir(PATH . 'export')) {
    $dir = opendir(PATH . 'export');
    while (false!==($read=readdir($dir))) {
      if (substr($read, -4) == '.csv') {
        @unlink(PATH . 'export/' . $read);
      }
    }
    closedir($dir);
  }
}

function getTicketLink($t) {
  global $msg_adheader5,$msg_adheader6,$msg_adheader28,$msg_adheader29,$msg_adheader63,$msg_adheader32;
  if ($t->ticketStatus == 'open' && $t->isDisputed == 'no' && $t->assignedto != 'waiting' && $t->spamFlag == 'no') {
    return array('open', $msg_adheader5);
  }
  if ($t->ticketStatus != 'open' && $t->isDisputed == 'no' && $t->assignedto != 'waiting' && $t->spamFlag == 'no') {
    return array('close', $msg_adheader6);
  }
  if ($t->ticketStatus == 'open' && $t->isDisputed == 'yes' && $t->assignedto != 'waiting' && $t->spamFlag == 'no') {
    return array('disputes', $msg_adheader28);
  }
  if ($t->ticketStatus != 'open' && $t->isDisputed == 'yes' && $t->assignedto != 'waiting' && $t->spamFlag == 'no') {
    return array('cdisputes', $msg_adheader29);
  }
  if ($t->source == 'imap' && $t->spamFlag == 'yes') {
    return array('spam', $msg_adheader63);
  }
  if ($t->replyStatus == 'start' && $t->isDisputed == 'no' && $t->assignedto == 'waiting' && $t->spamFlag == 'no') {
    return array('assign', $msg_adheader32);
  }
  return array('','');
}

function helpPageLoader($page) {
  switch ($page) {
    case 'view-dispute':
      if (isset($_GET['disputeUsers'])) {
        return 'view-dispute-users';
      }
      break;
  }
  return $page;
}

function mswUserPageAccess($t) {
  $a = explode('|', $t->pageAccess);
  if ($t->addpages) {
    $b = explode(',', $t->addpages);
    return array_merge($a, $b);
  }
  return $a;
}

function mswDeptPerms($user, $dept, $arr) {
  return ($user == '1' || in_array($dept, $arr) ? 'ok' : 'fail');
}

function mswSQLDepartmentFilter($code, $query = 'AND') {
  return ($code ? $query . ' ' . $code : '');
}

function userAccessPages($id) {
  $p = array();
  $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `page` FROM `" . DB_PREFIX . "usersaccess`
       WHERE `userID`  = '{$id}'
       AND `type`      = 'pages'
       ORDER BY `page`
       ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  while ($AP = mysqli_fetch_object($q)) {
    $p[] = $AP->page;
  }
  if (!empty($p)) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "users` SET
    `pageAccess`  = '" . implode('|', $p) . "'
    WHERE `id`    = '{$id}'
	  ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    return implode('|', $p);
  }
  return '';
}

function mswDeptFilterAccess($MSTEAM, $userDeptAccess, $table) {
  $f = '';
  if ($MSTEAM->id != '1') {
    switch ($MSTEAM->assigned) {
      // Can view assigned tickets ONLY..
      case 'yes':
        switch ($table) {
          case 'department':
            $f = '`id` > 0 AND `manual_assign` = \'yes\'';
            break;
          case 'tickets':
            $f = 'FIND_IN_SET(\'' . $MSTEAM->id . '\',`assignedto`) > 0';
            break;
        }
        break;
      // Can view tickets by department..
      case 'no':
        switch ($table) {
          case 'department':
            if (!empty($userDeptAccess)) {
              $f = '`id` IN(' . implode(',', $userDeptAccess) . ')';
            } else {
              $f = '`id` = \'0\'';
            }
            break;
          case 'tickets':
            if (!empty($userDeptAccess)) {
              $f = '(`department` IN(' . implode(',', $userDeptAccess) . ') OR FIND_IN_SET(\'' . $MSTEAM->id . '\',`assignedto`) > 0)';
            } else {
              $f = '`department` = \'0\'';
            }
            break;
        }
        break;
    }
  }
  return $f;
}

function mswCallBackUrls($cmd) {
  if (isset($_GET['attachment'])) {
    $cmd = 'view-ticket';
  }
  if (isset($_GET['response'])) {
    $cmd = 'view-ticket';
  }
  if (isset($_GET['fattachment'])) {
    $cmd = 'attachman';
  }
  if (isset($_GET['p']) && $_GET['p'] == 'cp') {
    $cmd = 'team-profile';
  }
  if (isset($_GET['ajax'])) {
    $cmd = 'ajax-handler';
  }
  return $cmd;
}

// Field display information..
function mswFieldDisplayInformation($loc) {
  global $msg_customfields40, $msg_customfields41, $msg_customfields42;
  $chop = explode(',', $loc);
  $dis  = array();
  if (in_array('ticket', $chop)) {
    $dis[] = $msg_customfields40;
  }
  if (in_array('reply', $chop)) {
    $dis[] = $msg_customfields41;
  }
  if (in_array('admin', $chop)) {
    $dis[] = $msg_customfields42;
  }
  return implode(', ', $dis);
}

// Clear settings footers..
function mswClearSettingsFooters() {
  mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "settings` SET
  `adminFooter`   = '',
  `publicFooter`  = ''
  ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
}

// Log in checker..
function mswIsLoggedIn($t) {
  if ((isset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail']) && isset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key']) && mswIsValidEmail($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'])) || (isset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail']) && isset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_key']) && mswIsValidEmail($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail']))) {
    if (!isset($t->name)) {
      header("Location: index.php?p=login");
      exit;
    }
  } else {
    header("Location: index.php?p=login");
    exit;
  }
}

// Cleans CSV..adds quotes if data contains delimiter..
function mswCleanCSV($data, $del) {
  if (strpos($data, $del) !== FALSE) {
    return '"' . mswCleanData($data) . '"';
  } else {
    return mswCleanData($data);
  }
}

// Get page access for user..
function mswGetUserPageAccess($id) {
  $q     = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `pageAccess`,`addpages` FROM `" . DB_PREFIX . "users` WHERE `id` = '{$id}'") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  $U     = mysqli_fetch_object($q);
  $pages = explode('|', $U->pageAccess);
  // Additional page rules..
  if ($U->addpages) {
    $add = array_map('trim', explode(',', $U->addpages));
    return array_merge($add, $pages);
  }
  return $pages;
}

// Get department access for user..
function mswGetDepartmentAccess($id) {
  $dept = array();
  $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `deptID` FROM `" . DB_PREFIX . "userdepts` WHERE `userID` = '{$id}'") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  while ($row = mysqli_fetch_object($q)) {
    $dept[] = $row->deptID;
  }
  // Are there any tickets assigned to this user NOT in the department array..?
  // If there are, add department to allowed array..
  $q2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `department` FROM `" . DB_PREFIX . "tickets`
        WHERE `department` NOT IN(" . implode(',', (!empty($dept) ? $dept : array(
    '0'
  ))) . ")
        AND FIND_IN_SET('{$id}',`assignedto`) > 0
        GROUP BY `department`
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  while ($DP = mysqli_fetch_object($q2)) {
    $dept[] = $DP->department;
  }
  if (!empty($dept)) {
    sort($dept);
  }
  return $dept;
}

// Standard response department..
function mswSrCat($depts) {
  $dep = array();
  if (empty($depts)) {
    $depts = array(0);
  }
  $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name` FROM `" . DB_PREFIX . "departments`
         WHERE `id` IN({$depts})
         ORDER BY `name`
	     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  while ($DP = mysqli_fetch_object($q)) {
    $dept[] = mswCleanData($DP->name);
  }
  return (!empty($dept) ? implode(', ', $dept) : '');
}

// FAQ Cat..
function mswFaqCategories($id, $action = 'show') {
  $cat   = array();
  $catID = array();
  $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `" . DB_PREFIX . "categories`.`name`,`" . DB_PREFIX . "categories`.`id` AS `catID` FROM `" . DB_PREFIX . "categories`
           LEFT JOIN `" . DB_PREFIX . "faqassign`
	       ON `" . DB_PREFIX . "faqassign`.`itemID`    = `" . DB_PREFIX . "categories`.`id`
           WHERE `" . DB_PREFIX . "faqassign`.`desc`   = 'category'
	       AND `" . DB_PREFIX . "faqassign`.`question` = '{$id}'
           ORDER BY `" . DB_PREFIX . "categories`.`name`
	       ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  while ($CT = mysqli_fetch_object($q)) {
    $cat[]   = mswCleanData($CT->name);
    $catID[] = $CT->catID;
  }
  // We just want IDs if action is get..
  if ($action == 'get') {
    return $catID;
  }
  return (!empty($cat) ? implode(', ', $cat) : '');
}

?>