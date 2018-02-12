<?php

class supportTickets {

  public $settings;
  public $team;

  const TICKET_HISTORY_FILENAME = 'history-{ticket}-{date}.csv';
  const TICKET_EXPORT_FILENAME = 'ticket-stats-{date}.csv';

  public function exportTicketStats($dt, $dl) {
    global $msg_search26, $msg_search27, $msg_script4, $msg_script5;
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    $sepr = ',';
    $file = PATH . 'export/' . str_replace(array(
      '{date}'
    ), array(
      $dt->mswDateTimeDisplay(strtotime(date('Ymd H:i:s')), 'dmY-his')
    ), supportTickets::TICKET_EXPORT_FILENAME);
    if ($this->settings->disputes == 'no') {
      unset($msg_search26[15]);
    }
    $string = implode(',', $msg_search26) . mswDefineNewline();
    if (!empty($_POST['del'])) {
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "portal`.`email` AS `ticketMail`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           LEFT JOIN `" . DB_PREFIX . "levels`
           ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
            OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
           WHERE `" . DB_PREFIX . "tickets`.`id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
           " . (isset($_POST['orderbyexp']) ? mswSafeImportString($_POST['orderbyexp']) : 'ORDER BY `' . DB_PREFIX . 'tickets`.`id`') . "
           ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($T = mysqli_fetch_object($q)) {
        // Ticket No..
        $string .= mswCleanCSV(mswTicketNumber($T->ticketID), $sepr) . $sepr;
        // Created By..
        $string .= mswCleanCSV(mswCleanData($T->ticketName), $sepr) . $sepr;
        // Email..
        $string .= mswCleanCSV($T->ticketMail, $sepr) . $sepr;
        // Created On..
        $string .= mswCleanCSV($dt->mswDateTimeDisplay($T->ticketStamp, $this->settings->dateformat) . ' ' . $dt->mswDateTimeDisplay($T->ticketStamp, $this->settings->timeformat), $sepr) . $sepr;
        // First Reply On..
        $first = supportTickets::getLastReplyExportInfo($T->ticketID, 'first');
        $last  = supportTickets::getLastReplyExportInfo($T->ticketID, 'last');
        $string .= mswCleanCSV(($first[2] > 0 ? $dt->mswDateTimeDisplay($last[2], $this->settings->dateformat) . ' ' . $dt->mswDateTimeDisplay($last[2], $this->settings->timeformat) : ''), $sepr) . $sepr;
        // First Reply By..
        $string .= mswCleanCSV($first[0], $sepr) . $sepr;
        // Last Reply On..
        $string .= mswCleanCSV(($last[2] > 0 ? $dt->mswDateTimeDisplay($last[2], $this->settings->dateformat) . ' ' . $dt->mswDateTimeDisplay($last[2], $this->settings->timeformat) : ''), $sepr) . $sepr;
        // Last Reply By..
        $string .= mswCleanCSV($first[0], $sepr) . $sepr;
        // Agents Assigned..
        $assgd = supportTickets::assignedTeam($T->assignedto);
        $string .= mswCleanCSV($assgd, $sepr) . $sepr;
        // Subject..
        $string .= mswCleanCSV(mswCleanData($T->subject), $sepr) . $sepr;
        // Department..
        $string .= mswCleanCSV(mswCleanData($T->deptName), $sepr) . $sepr;
        // Ticket Status..
        $string .= mswCleanCSV(supportTickets::exportStatus($T, 'ticket'), $sepr) . $sepr;
        // Reply Status..
        $string .= mswCleanCSV(supportTickets::exportStatus($T, 'reply'), $sepr) . $sepr;
        // Priority..
        $string .= mswCleanCSV(mswCleanData($T->levelName), $sepr) . $sepr;
        // Via..
        $string .= mswCleanCSV((isset($msg_search27[$T->source]) ? $msg_search27[$T->source] : 'Undefined'), $sepr) . $sepr;
        // Is Dispute..
        if ($this->settings->disputes == 'yes') {
          $string .= mswCleanCSV(($T->isDisputed == 'yes' ? $msg_script4 : $msg_script5), $sepr) . $sepr;
        }
        // Total Replies..
        $string .= mswCleanCSV(mswRowCount('replies WHERE `ticketID` = \'' . $T->ticketID . '\''), $sepr) . $sepr;
        // Total History Actions..
        $string .= mswCleanCSV(mswRowCount('tickethistory WHERE `ticketID` = \'' . $T->ticketID . '\''), $sepr) . mswDefineNewline();
      }
      if (mysqli_num_rows($q) > 0) {
        // Save file to server and download..
        $dl->write($file, rtrim($string));
        if (file_exists($file)) {
          return $file;
        }
      }
    }
    return 'none';
  }

  public function exportStatus($t, $type) {
    global $msg_search28, $msg_search29, $msg_public_history8, $msg_viewticket14, $msg_viewticket15, $msg_viewticket16;
    if ($t->assignedto == 'waiting') {
      return $msg_public_history8;
    }
    switch ($type) {
      case 'ticket':
        switch ($t->ticketStatus) {
          case 'open':
            return $msg_viewticket14;
            break;
          case 'close':
            return $msg_viewticket15;
            break;
          case 'closed':
            return $msg_viewticket16;
            break;
        }
        break;
      case 'reply':
        switch ($t->replyStatus) {
          case 'start':
          case 'admin':
            return $msg_search28;
            break;
          case 'visitor':
            return $msg_search29;
            break;
        }
        break;
    }
  }

  public function getLastReplyExportInfo($id, $type) {
    switch ($type) {
      case 'first':
        $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ts`,`replyType`,`replyUser`,`disputeUser` FROM `" . DB_PREFIX . "replies`
             WHERE `ticketID` = '{$id}'
		         ORDER BY `id`
		         LIMIT 1
		         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
      case 'last':
        $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ts`,`replyType`,`replyUser`,`disputeUser` FROM `" . DB_PREFIX . "replies`
             WHERE `ticketID` = '{$id}'
		         ORDER BY `id` DESC
		         LIMIT 1
		         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
    }
    $R = mysqli_fetch_object($q);
    if (isset($R->ts)) {
      switch ($R->replyType) {
        case 'admin':
          $A    = mswGetTableData('users', 'id', $R->replyUser);
          $info = array(
            (isset($A->name) ? mswSafeDisplay($A->name) : 'N/A'),
            (isset($A->email) ? $A->email : 'N/A'),
            $R->ts
          );
          break;
        case 'visitor':
          if ($R->disputeUser > 0) {
            $U    = mswGetTableData('portal', 'id', $R->disputeUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSafeDisplay($U->name) : 'N/A'),
              (isset($U->email) ? $U->email : 'N/A'),
              $R->ts
            );
          } else {
            $U    = mswGetTableData('portal', 'id', $R->replyUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSafeDisplay($U->name) : 'N/A'),
              (isset($U->email) ? $U->email : 'N/A'),
              $R->ts
            );
          }
          break;
      }
      return $info;
    }
    return array(
      '',
      '',
      0
    );
  }

  public function attachList($id) {
    $s = '';
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "attachments`
	       WHERE `ticketID` = '{$id}'
	       ORDER BY `id`
	       ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($A = mysqli_fetch_object($q)) {
      $s .= $this->settings->scriptpath . '/' . $this->settings->afolder . '/?attachment=' . $A->id . mswDefineNewline();
    }
    return trim($s);
  }

  public function notSpam() {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
    `spamFlag` = 'no'
    WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
  }

  public function spamLearning($ident, $b8) {
    if (!empty($_POST['del'])) {
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `comments` FROM `" . DB_PREFIX . "tickets`
           WHERE `id`     IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
           AND `spamFlag`  = 'yes'
           AND `source`    = 'imap'
           ORDER BY `id`
           ");
      while ($T = mysqli_fetch_object($q)) {
        switch ($ident) {
          case 'spam':
            $b8->learn(htmlspecialchars($T->comments), b8::SPAM);
            break;
          case 'ham':
            $b8->learn(htmlspecialchars($T->comments), b8::HAM);
            break;
        }
      }
    }
  }

  public function searchDisputeUsers() {
    $f     = (isset($_GET['field']) && in_array($_GET['field'], array(
      'name',
      'email'
    )) ? $_GET['field'] : 'name');
    $ID    = (int) $_GET['id'];
    $acc   = array();
    $users = array();
    // Get all users currently in dispute..
    $qDU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `visitorID` FROM `" . DB_PREFIX . "disputes`
           WHERE `ticketID` = '{$ID}'
           ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($DU = mysqli_fetch_object($qDU)) {
      $users[] = $DU->visitorID;
    }
    // Get ID of person who started ticket
    $TK = mswGetTableData('tickets', 'id', $ID);
    if (isset($TK->visitorID)) {
      $users[] = $TK->visitorID;
    }
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email` FROM `" . DB_PREFIX . "portal`
         WHERE `" . $f . "` LIKE '%" . mswSafeImportString($_GET['term']) . "%'
		     " . (!empty($users) ? 'AND `id` NOT IN(' . mswSafeImportString(implode(',', $users)) . ')' : '') . "
         AND `enabled` = 'yes'
		     GROUP BY `email`
	       ORDER BY `name`,`email`
		     ");
    if (mysqli_num_rows($q) > 0) {
      while ($A = mysqli_fetch_object($q)) {
        $n          = array();
        $n['name']  = mswSafeDisplay($A->name);
        $n['email'] = mswCleanData($A->email);
        $acc[]      = $n;
      }
    }
    return $acc;
  }

  public function deleteTicketHistory($id, $ticket) {
    // All or single entry..
    if ($id == 'all') {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `ticketID` = '{$ticket}'") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    } else {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `id` = '{$id}'") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    if (mswRowCount('tickethistory') == 0) {
      @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "tickethistory`");
    }
  }

  public function historyLog($ticket, $action) {
    if ($this->settings->ticketHistory == 'yes') {
      mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "tickethistory` (
      `ts`,
      `ticketID`,
      `action`,
      `ip`
      ) VALUES (
      UNIX_TIMESTAMP(),
      '{$ticket}',
      '" . mswSafeImportString($action) . "',
      '" . mswIPAddresses() . "'
      )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function exportTicketHistory($dl, $dt) {
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    global $msg_viewticket113;
    $id   = (isset($_GET['param']) ? (int) $_GET['param'] : '0');
    $sepr = ',';
    $file = PATH . 'export/' . str_replace(array(
      '{ticket}',
      '{date}'
    ), array(
      mswTicketNumber($id),
      $dt->mswDateTimeDisplay(strtotime(date('Ymd H:i:s')), 'dmY-his')
    ), supportTickets::TICKET_HISTORY_FILENAME);
    $csv = array($msg_viewticket113);
    $qTH = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "tickethistory`
           WHERE `ticketID` = '{$id}'
           ORDER BY `ts` DESC
           ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($HIS = mysqli_fetch_object($qTH)) {
      $csv[] = mswCleanCSV($dt->mswDateTimeDisplay($HIS->ts, $this->settings->dateformat), $sepr) . $sepr . mswCleanCSV($dt->mswDateTimeDisplay($HIS->ts, $this->settings->timeformat), $sepr) . $sepr . mswCleanCSV($HIS->action, $sepr) . $sepr . mswCleanCSV($HIS->ip, $sepr);
    }
    if (mysqli_num_rows($qTH) > 0) {
      // Save file to server and download..
      $dl->write($file, implode(mswDefineNewline(), $csv));
      if (file_exists($file)) {
        return $file;
      }
    }
    return 'none';
  }

  public function searchBatchUpdate() {
    $cnt = 0;
    $bd  = array();
    $act = array();
    if (!empty($_POST['ticket'])) {
      if ($_POST['department'] != 'no-change' || $_POST['priority'] != 'no-change' || $_POST['status'] != 'no-change') {
        // Department update..
        if ((int) $_POST['department'] > 0) {
          $bd[] = '`department` = \'' . (int) $_POST['department'] . '\'';
          mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "attachments` SET
          `department`      = '{$_POST['department']}'
          WHERE `ticketID` IN(" . mswSafeImportString(implode(',', $_POST['ticket'])) . ")
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          $act[] = 'dept';
        }
        // Priority update..
        if ($_POST['priority'] != 'no-change') {
          $bd[]  = '`priority` = \'' . $_POST['priority'] . '\'';
          $act[] = 'priority';
        }
        // Status update..
        if (in_array($_POST['status'], array(
          'close',
          'open',
          'locked'
        ))) {
          $bd[]  = '`ticketStatus` = \'' . $_POST['status'] . '\'';
          $act[] = 'status';
        }
        // Is anything changing?
        if (!empty($bd)) {
          mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
          " . mswSafeImportString(implode(',', $bd)) . "
          WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['ticket'])) . ")
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
          // Update timestamp if something actually changed..
          if ($rows > 0) {
            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
            `lastrevision` = UNIX_TIMESTAMP()
            WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['ticket'])) . ")
            ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            return array(
              $rows,
              $act
            );
          }
        }
      }
    }
    return array(
      $cnt,
      array()
    );
  }

  public function assignedTeam($assigned) {
    if ($assigned == 'waiting' || $assigned == '') {
      return 'N/A';
    }
    $u = array();
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name` FROM `" . DB_PREFIX . "users` WHERE `id` IN({$assigned}) ORDER BY `name`") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($TM = mysqli_fetch_object($q)) {
      $u[] = mswCleanData($TM->name);
    }
    return implode(', ', $u);
  }

  public function ticketUserAssign($id, $users, $action) {
    $string = '';
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
    `assignedto`   = '{$users}'
    WHERE `id`     = '{$id}'
    ");
    // Write log if there are affected rows..
    if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) > 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision` = UNIX_TIMESTAMP()
      WHERE `id`     = '{$id}'
      ");
      supportTickets::historyLog($id, str_replace(array(
        '{admin}',
        '{users}'
      ), array(
        $this->team->name,
        supportTickets::assignedTeam($users)
      ), $action));
    }
  }

  public function updateNotes($id) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
    `ticketNotes`  = '" . (isset($_POST['notes']) ? mswSafeImportString($_POST['notes']) : '') . "'
    WHERE `id`     = '{$id}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    if ($rows > 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision` = UNIX_TIMESTAMP()
      WHERE `id`     = '{$id}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    return $rows;
  }

  public function addDisputeUser($ticket, $visitor, $priv) {
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "disputes` (
    `ticketID`,
    `visitorID`,
    `postPrivileges`
    ) VALUES (
    '{$ticket}',
    '{$visitor}',
    '{$priv}'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function updateDisputePrivileges($id, $tk, $type, $priv) {
    switch ($type) {
      case 'ticket':
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
        `disPostPriv` = '{$priv}'
        WHERE `id`    = '{$tk}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
      default:
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "disputes` SET
        `postPrivileges` = '{$priv}'
        WHERE `ticketID` = '{$tk}'
        AND `visitorID`  = '{$id}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
    }
  }

  public function removeDisputeUsersFromTicket($rem) {
    if (!empty($rem)) {
      $disID = implode(',', $rem);
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "disputes`
      WHERE `id` IN({$disID})
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('disputes') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "disputes`");
      }
    }
  }

  public function deleteAttachments($ids = array()) {
    if (!empty($ids)) {
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `fileName`,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
	         WHERE `id` IN(" . mswSafeImportString(implode(',', $ids)) . ")
		       ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($A = mysqli_fetch_object($q)) {
        supportTickets::deleteAttachmentData($A);
      }
      // Delete all attachment data..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "attachments` WHERE `id` IN(" . mswSafeImportString(implode(',', $ids)) . ")") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('attachments') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "attachments`");
      }
    }
    return $ids;
  }

  public function purgeTickets() {
    $t       = 0;
    $r       = 0;
    $a       = 0;
    $sql     = '';
    $tickets = array();
    if ((int) $_POST['days1'] > 0) {
      $days = (int) $_POST['days1'];
      // Departments..
      if (!empty($_POST['dept1'])) {
        $sql = "WHERE `department` IN(" . mswSafeImportString(implode(',', $_POST['dept1'])) . ")";
      }
      $sql .= ($sql ? ' AND ' : 'WHERE ') . 'DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`ts`))) >= ' . $days;
      // Get tickets applicable for deletion..
      $q_t = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "tickets` $sql AND `ticketStatus` != 'open' AND `assignedto` != 'waiting' AND `spamFlag` = 'no'") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($TK = mysqli_fetch_object($q_t)) {
        $tickets[] = $TK->id;
      }
      // Anything to delete..
      if (!empty($tickets)) {
        $_POST['ticket'] = $tickets;
        $ret             = supportTickets::deleteTickets((isset($_POST['clear']) ? 'yes' : 'no'), 'yes', true);
        return $ret;
      }
    }
    return array(
      $t,
      $r,
      $a
    );
  }

  public function purgeAttachments() {
    $count = 0;
    $sql   = '';
    if ((int) $_POST['days2'] > 0) {
      $days = (int) $_POST['days2'];
      // Departments..
      if (!empty($_POST['dept1'])) {
        $sql = "WHERE `department` IN(" . mswSafeImportString(implode(',', $_POST['dept2'])) . ")";
      }
      $sql .= ($sql ? ' AND ' : 'WHERE ') . 'DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`ts`))) >= ' . $days;
      // Delete attachment files..
      $qA = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `fileName`,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments` $sql") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($A = mysqli_fetch_object($qA)) {
        supportTickets::deleteAttachmentData($A);
      }
      // Delete all attachment data..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "attachments` $sql") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      if (mswRowCount('attachments') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "attachments`");
      }
    }
    return $count;
  }

  private function deleteAttachmentData($A) {
    $split  = explode('-', $A->addDate);
    $folder = '';
    // Check for newer folder structure..
    if (file_exists($this->settings->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $A->fileName)) {
      $folder = $split[0] . '/' . $split[1] . '/';
    }
    if (is_writeable($this->settings->attachpath) && file_exists($this->settings->attachpath . '/' . $folder . $A->fileName)) {
      @unlink($this->settings->attachpath . '/' . $folder . $A->fileName);
    }
  }

  public function deleteTickets($attachments = 'yes', $ticketData = 'yes', $purgeCounts = false) {
    $pcnt = array();
    $t    = 0;
    $r    = 0;
    $a    = 0;
    if (!empty($_POST['del'])) {
      $tIDs = implode(',', $_POST['del']);
      // Delete attachment files..
      if ($attachments == 'yes') {
        $qA = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
	            WHERE `ticketID` IN({$tIDs})
			        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        while ($A = mysqli_fetch_object($qA)) {
          supportTickets::deleteAttachmentData($A);
        }
        // Delete all attachment data..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "attachments` WHERE `ticketID` IN({$tIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $a = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      }
      if ($ticketData == 'yes') {
        // Delete all replies..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "replies` WHERE `ticketID` IN({$tIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $r = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        // Delete all tickets..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "tickets` WHERE `id` IN({$tIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $t = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        // Delete all custom data..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ticketfields` WHERE `ticketID` IN({$tIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Delete disputes..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "disputes` WHERE `ticketID` IN({$tIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Delete history..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `ticketID` IN({$tIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Truncate tables to start at 1..
        foreach (array(
          'tickets',
          'attachments',
          'replies',
          'cusfields',
          'ticketfields',
          'disputes',
          'tickethistory'
        ) AS $tables) {
          if (mswRowCount($tables) == 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . $tables . "`");
          }
        }
      }
      if ($purgeCounts) {
        return array(
          @number_format($t),
          @number_format($r),
          @number_format($a)
        );
      }
    }
    return array(
      0,
      0,
      0
    );
  }

  public function reOpenTicket() {
    $rows = 0;
    if (!empty($_POST['ticket'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision`  = UNIX_TIMESTAMP(),
      `ticketStatus`  = 'open'
      WHERE `id`     IN(" . mswSafeImportString(implode(',', $_POST['ticket'])) . ")
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    }
    return $rows;
  }

  public function addTicketReply() {
    $tID     = (int) $_POST['ticketID'];
    $array   = array(
      'no',
      $tID,
      ''
    );
    $mergeID = (isset($_POST['mergeid']) ? mswReverseTicketNumber($_POST['mergeid']) : '0');
    $newID   = ($mergeID > 0 ? $mergeID : $tID);
    // Are we merging this ticket..
    if ($mergeID > 0) {
      if (mswRowCount('tickets WHERE `id` = \'' . $mergeID . '\'') > 0) {
        // Get original ticket and convert it to a reply..
        $OTICKET = mswGetTableData('tickets', 'id', $tID);
        // Get new parent data for department..
        $MERGER  = mswGetTableData('tickets', 'id', $mergeID);
        // Account information..
        $PORTAL  = mswGetTableData('portal', 'id', $MERGER->visitorID);
        // Add original ticket as reply..
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "replies` (
        `ts`,
        `ticketID`,
        `comments`,
        `replyType`,
        `replyUser`,
        `isMerged`,
        `ipAddresses`
        ) VALUES (
        UNIX_TIMESTAMP(),
        '{$mergeID}',
        '" . mswSafeImportString($OTICKET->comments) . "',
        'visitor',
        '{$OTICKET->visitorID}',
        'yes',
        '{$OTICKET->ipAddresses}'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Now remove original ticket
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "tickets` WHERE `id` = '{$tID}'") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Move any replies attached to original ticket to new parent..
        // Update timestamp so they fall in line..
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "replies` SET
	      `ts`              = UNIX_TIMESTAMP(),
        `ticketID`        = '{$mergeID}',
        `isMerged`        = 'yes'
        WHERE `ticketID`  = '{$tID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Move attachments to new ticket id..
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "attachments` SET
        `ticketID`        = '{$mergeID}',
        `department`      = '{$MERGER->department}'
        WHERE `ticketID`  = '{$tID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Move custom field data to new ticket..
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "ticketfields` SET
        `ticketID`        = '{$mergeID}'
        WHERE `ticketID`  = '{$tID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Remove history for old ticket..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `ticketID` = '{$tID}'") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Move any dispute user data to new ticket..
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "disputes` SET
        `ticketID`        = '{$mergeID}'
        WHERE `ticketID`  = '{$tID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Overwrite array..
        $array = array(
          'yes',
          $mergeID,
          $OTICKET->subject
        );
      }
    }
    // Add new reply..
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "replies` (
    `ts`,
    `ticketID`,
    `comments`,
    `replyType`,
    `replyUser`,
    `isMerged`,
    `ipAddresses`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$newID}',
    '" . mswSafeImportString($_POST['comments']) . "',
    'admin',
    '{$this->team->id}',
    'no',
    '" . mswIPAddresses() . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $newReply = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // Custom field data..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $data = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $data = implode('#####', $v);
          }
        } else {
          $data = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($data != '' && $data != 'nothing-selected') {
          if (mswRowCount('ticketfields WHERE `ticketID`  = \'' . $newID . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'' . $newReply . '\'') > 0) {
            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "ticketfields` SET
            `fieldData`       = '" . mswSafeImportString($data) . "'
            WHERE `ticketID`  = '{$newID}'
            AND `fieldID`     = '{$k}'
            AND `replyID`     = '{$newReply}'
            ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            } else {
              mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ticketfields` (
            `fieldData`,`ticketID`,`fieldID`,`replyID`
            ) VALUES (
            '" . mswSafeImportString($data) . "','{$newID}','{$k}','{$newReply}'
            )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          }
        } else {
          mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ticketfields`
          WHERE `ticketID`  = '{$newID}'
          AND `fieldID`     = '{$k}'
          AND `replyID`     = '{$newReply}'
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          if (mswRowCount('ticketfields') == 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "ticketfields`");
          }
        }
      }
    }
    // Update ticket status..
    $status = (in_array($_POST['status'], array(
      'close',
      'open',
      'closed'
    )) ? $_POST['status'] : 'open');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
    `lastrevision`  = UNIX_TIMESTAMP(),
    `ticketStatus`  = '{$status}',
    `replyStatus`   = 'visitor'
    WHERE `id`      = '{$newID}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // If specified, add reply as standard response..
    if (isset($_POST['response']) && $_POST['response']) {
      // Add response..
      $dept = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
      mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "responses` (
      `ts`,
      `title`,
      `answer`,
      `departments`
      ) VALUES (
      UNIX_TIMESTAMP(),
      '" . mswSafeImportString($_POST['response']) . "',
      '" . mswSafeImportString($_POST['comments']) . "',
      '" . mswSafeImportString($dept) . "'
      )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Rebuild sequence..
      include_once(PATH . 'control/classes/class.responses.php');
      $MSSTR = new standardResponses();
      $MSSTR->rebuildSequence();
    }
    $array[] = $newReply;
    return $array;
  }

  public function deleteReply($RP, $TK, $ID) {
    $rows = 0;
    if (isset($RP->ticketID)) {
      if (isset($TK->id)) {
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "replies` WHERE `id` = '{$ID}'");
        $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        // Delete attachments..
        $q    = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
                WHERE `ticketID`  = '{$TK->id}'
                AND `replyID`     = '{$ID}'
                ORDER BY `id`
                ");
        while ($ATT = mysqli_fetch_object($q)) {
          supportTickets::deleteAttachmentData($ATT);
        }
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "attachments` WHERE `replyID` = '{$ID}'");
        // If all replies have been deleted. ticket should be set back to start..
        if (mswRowCount('replies WHERE `ticketID` = \'' . $TK->id . '\'') == 0) {
          mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
          `lastrevision` = UNIX_TIMESTAMP(),
          `replyStatus`  = 'start'
          WHERE `id`     = '{$TK->id}'
          ");
        }
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ticketfields` WHERE `replyID` = '{$ID}'");
        // Truncate tables to start at 1..
        foreach (array(
          'attachments',
          'replies',
          'ticketfields'
        ) AS $tables) {
          if (mswRowCount($tables) == 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . $tables . "`");
          }
        }
      }
    }
    return $rows;
  }

  public function updateTicketReply($action) {
    $_GET['id']        = (isset($_POST['replyID']) ? (int) $_POST['replyID'] : '0');
    $_POST['ticketID'] = (isset($_POST['ticketID']) ? (int) $_POST['ticketID'] : '0');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "replies` SET
    `comments`  = '" . mswSafeImportString($_POST['comments']) . "'
    WHERE `id`  = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Only write log if there are affected rows and something was changed..
    if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) > 0) {
      supportTickets::historyLog($_POST['ticketID'], str_replace(array(
        '{id}',
        '{user}'
      ), array(
        $_GET['id'],
        $this->team->name
      ), $action));
    }
    // Custom field data..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $data = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $data = implode('#####', $v);
          }
        } else {
          $data = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($data != '' && $data != 'nothing-selected') {
          if (mswRowCount('ticketfields WHERE `ticketID`  = \'' . $_POST['ticketID'] . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'' . $_GET['id'] . '\'') > 0) {
            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "ticketfields` SET
            `fieldData`       = '" . mswSafeImportString($data) . "'
            WHERE `ticketID`  = '{$_POST['ticketID']}'
            AND `fieldID`     = '{$k}'
            AND `replyID`     = '{$_GET['id']}'
            ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          } else {
            mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ticketfields` (
            `fieldData`,`ticketID`,`fieldID`,`replyID`
            ) VALUES (
            '" . mswSafeImportString($data) . "','{$_POST['ticketID']}','{$k}','{$_GET['id']}'
            )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          }
        } else {
          mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ticketfields`
          WHERE `ticketID`  = '{$_POST['ticketID']}'
          AND `fieldID`     = '{$k}'
          AND `replyID`     = '{$_GET['id']}'
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          if (mswRowCount('ticketfields') == 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "ticketfields`");
          }
        }
      }
    }
  }

  public function updateTicketDisputeStatus() {
    $status = (isset($_GET['odis']) ? 'yes' : 'no');
    if ((int) $_GET['id'] > 0) {
      $_GET['id'] = (int) $_GET['id'];
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE " . DB_PREFIX . "tickets SET
      `lastrevision` = UNIX_TIMESTAMP(),
      `isDisputed`   = '{$status}'
      WHERE `id`     = '{$_GET['id']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function batchReOpenTickets() {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
    `ticketStatus` = 'open',
    `lastrevision` = UNIX_TIMESTAMP()
    WHERE `id`    IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    return $rows;
  }

  public function updateTicketStatus() {
    $ID   = (int) $_GET['id'];
    $rows = 0;
    switch ($_GET['act']) {
      // Open/close/lock ticket..
      case 'open':
      case 'close':
      case 'lock':
      case 'reopen':
        if ($_GET['act'] == 'reopen') {
          $_GET['act'] = 'open';
        }
        $status = ($_GET['act'] == 'lock' ? 'closed' : $_GET['act']);
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
        `ticketStatus` = '{$status}'
        WHERE `id`     = '{$ID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        break;
      case 'ticket':
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
        `isDisputed`  = 'no',
	      `disPostPriv` = 'yes'
        WHERE `id`    = '{$ID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        // Remove users in this dispute..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "disputes`
        WHERE `ticketID` = '{$ID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        if (mswRowCount('disputes') == 0) {
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "disputes`");
        }
        break;
      // Convert to dispute..
      case 'dispute':
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
        `isDisputed` = 'yes'
        WHERE `id`   = '{$ID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        break;
    }
    // If something happened, update the timestamp..
    if ($rows > 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision` = UNIX_TIMESTAMP()
      WHERE `id`     = '{$ID}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    return $rows;
  }

  public function updateTicket() {
    $tickID = (isset($_POST['id']) ? (int) $_POST['id'] : '0');
    $deptID = (isset($_POST['dept']) ? (int) $_POST['dept'] : '0');
    if ($tickID == '0') {
      return 0;
    }
    $isAssg = mswRowCount('departments WHERE `id` = \'' . $deptID . '\' AND `manual_assign` = \'yes\'');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
    `lastrevision` = UNIX_TIMESTAMP(),
    `department`   = '{$deptID}',
    `assignedto`   = '" . ($isAssg > 0 && !empty($_POST['assigned']) ? implode(',',$_POST['assigned']) : '') . "',
    `subject`      = '" . mswSafeImportString($_POST['subject']) . "',
    `comments`     = '" . mswSafeImportString($_POST['comments']) . "',
    `priority`     = '" . mswSafeImportString($_POST['priority']) . "',
    `ticketStatus` = '" . (in_array($_POST['status'],array('open','close','closed')) ? $_POST['status'] : 'open') . "'
    WHERE `id`     = '{$tickID}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    // Custom field data..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $data = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $data = implode('#####', $v);
          }
        } else {
          $data = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($data != '' && $data != 'nothing-selected') {
          if (mswRowCount('ticketfields WHERE `ticketID`  = \'' . $tickID . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'0\'') > 0) {
            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "ticketfields` SET
            `fieldData`       = '" . mswSafeImportString($data) . "'
            WHERE `ticketID`  = '{$tickID}'
            AND `fieldID`     = '{$k}'
            AND `replyID`     = '0'
            ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            $rows = $rows + mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
          } else {
            mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ticketfields` (
            `fieldData`,`ticketID`,`fieldID`,`replyID`
            ) VALUES (
            '" . mswSafeImportString($data) . "','{$tickID}','{$k}','0'
            )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            $rows = $rows + mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
          }
        } else {
          mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ticketfields`
          WHERE `ticketID`  = '{$tickID}'
          AND `fieldID`     = '{$k}'
          AND `replyID`     = '0'
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          $rows = $rows + mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
          if (mswRowCount('ticketfields') == 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "ticketfields`");
          }
        }
      }
    }
    // Do we need to delete any attachments?
    if (!empty($_POST['attachment'])) {
      $aIDs = implode(',', $_POST['attachment']);
      $qA   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
	            WHERE `id` IN({$aIDs})
			        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($A = mysqli_fetch_object($qA)) {
        supportTickets::deleteAttachmentData($A);
      }
      // Delete all attachment data..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "attachments` WHERE `id` IN({$aIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    // If department was changed, update attachments..
    if ($deptID != $_POST['odeptid']) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "attachments` SET
      `department`      = '{$deptID}'
      WHERE `ticketID`  = '{$tickID}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Check assignment..If department has assign disabled, we need to clear assigned values from ticket..
      if ($isAssg > 0) {
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
        `assignedto` = ''
        WHERE `id`   = '{$tickID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    return $rows;
  }

  public function mergeSearch($access,$txt) {
    $vs  = (isset($_GET['visitor']) ? (int) $_GET['visitor'] : '0');
    $id  = (isset($_GET['id']) ? (int) $_GET['id'] : '0');
    $fl  = mswSQLDepartmentFilter($access, 'WHERE');
    $ar  = array();
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`subject` FROM `" . DB_PREFIX . "tickets`
           $fl
		       " . ($fl ? 'AND' : 'WHERE') . " `visitorID`    = '{$vs}'
           AND `id`          != '{$id}'
           AND `assignedto`  != 'waiting'
		       AND `ticketStatus` = 'open'
		       AND `isDisputed`   = 'no'
		       AND `spamFlag`     = 'no'
           ORDER BY `id`
           ");
    while ($TK = mysqli_fetch_object($q)) {
      $ar[] = array(
        'value' => $TK->id,
        'label' => '[#' . mswTicketNumber($TK->id) . '] ' . mswSafeDisplay($TK->subject),
        'ticket' => mswTicketNumber($TK->id),
        'txt' => $txt
      );
    }
    return $ar;
  }

}

?>