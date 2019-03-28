<?php

// Ticket reply..

$MSTICKET->upload = $MSUPL;

if (defined('AJAX_HANDLER') && isset($LI_ACC->id)) {
  $tType = (isset($_POST['ticketType']) && in_array($_POST['ticketType'], array('ticket','dispute')) ? $_POST['ticketType'] : 'ticket');
  $tkID  = (isset($_POST['ticketID']) ? (int) $_POST['ticketID'] : '0');
  if ($tkID > 0) {
    switch($tType) {
      case 'ticket':
        $T = mswGetTableData('tickets', 'id', $tkID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\'');
        break;
      case 'dispute':
        $T = mswGetTableData('tickets', 'id', $tkID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\'');
        if (!isset($T->id)) {
          // Check if this user is in the dispute list...
          $PRIV = mswGetTableData('disputes', 'visitorID', $LI_ACC->id, 'AND `ticketID` = \'' . $tkID . '\'');
          // If privileges allow viewing of dispute, requery without email..
          if (isset($PRIV->id)) {
            $T = mswGetTableData('tickets', 'id', $tkID);
          }
        }
        break;
    }
    // If ticket ok, proceed..
    if (isset($T->id) && $T->assignedto != 'waiting') {
      if ($_POST['comments'] == '') {
        $eFields[] = $msadminlang3_1createticket[4];
      }
      // Attachments..
      if ($SETTINGS->attachment == 'yes' && !empty($_FILES['file']['tmp_name'])) {
        $attCnt  = count($_FILES['file']['tmp_name']);
        // Check limit..
        if (LICENCE_VER == 'locked' && $attCnt > RESTR_ATTACH) {
          $countOfBoxes = RESTR_ATTACH;
        }
        $attachE = array();
        for ($i = 0; $i < (isset($countOfBoxes) ? $countOfBoxes : $attCnt); $i++) {
          if ($SETTINGS->attachboxes > 1) {
            $fname = $_FILES['file']['name'][$i];
            $ftemp = $_FILES['file']['tmp_name'][$i];
            $fsize = $_FILES['file']['size'][$i];
            $fmime = $_FILES['file']['type'][$i];
          } else {
            $fname = $_FILES['file']['name'];
            $ftemp = $_FILES['file']['tmp_name'];
            $fsize = $_FILES['file']['size'];
            $fmime = $_FILES['file']['type'];
          }
          if ($fname && $ftemp && $fsize > 0) {
            if (!$MSTICKET->size($fsize)) {
              $attachE[] = str_replace(array('{file}', '{max}'),array(mswSafeDisplay($fname),mswFileSizeConversion($SETTINGS->maxsize)),$msadminlang3_1createticket[6]);
            } else {
              if (!$MSTICKET->type($fname)) {
                $attachE[] = str_replace(array('{file}', '{allowed}'),array(mswSafeDisplay($fname),str_replace(array('|','.'),array(', ',''), $SETTINGS->filetypes)),$msadminlang3_1createticket[6]);
              } else {
                $ticketAttachments[$i]['ext']  = (strpos($fname, '.') !== false ? strrchr(strtolower($fname), '.') : '');
                $ticketAttachments[$i]['temp'] = $ftemp;
                $ticketAttachments[$i]['size'] = $fsize;
                $ticketAttachments[$i]['name'] = $fname;
                $ticketAttachments[$i]['type'] = $fmime;
              }
            }
          }
        }
        // If error, clear all attachment temp files..
        if (!empty($attachE)) {
          for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
            @unlink($_FILES['file']['tmp_name'][$i]);
          }
          $ticketAttachments = array();
          $eFields[]         = implode('<br>', $attachE);
        }
      }
      // Check required custom fields..
      $customCheckFields = $MSFIELDS->check('reply', $T->department);
      if (!empty($customCheckFields)) {
        $eFields[] = str_replace('{count}', count($customCheckFields), $msadminlang3_1createticket[8]);
      }
      // All ok?
      if (empty($eFields)) {
        // Add reply..
        $replyID = $MSTICKET->reply(array(
          'ticket' => $T->id,
          'visitor' => $LI_ACC->id,
          'quoteBody' => '',
          'comments' => $_POST['comments'],
          'repType' => 'visitor',
          'ip' => mswIPAddresses(),
          'disID' => (isset($PRIV->id) ? $LI_ACC->id : '0')
        ));
        // Proceed if ok..
        if ($replyID > 0) {
          // Add attachments..
          if ($SETTINGS->attachment == 'yes' && !empty($ticketAttachments)) {
            for ($i = 0; $i < count($ticketAttachments); $i++) {
              $a_name = $ticketAttachments[$i]['name'];
              $a_temp = $ticketAttachments[$i]['temp'];
              $a_size = $ticketAttachments[$i]['size'];
              $a_mime = $ticketAttachments[$i]['type'];
              if ($a_name && $a_temp && $a_size > 0) {
                $atID        = $MSTICKET->addAttachment(array(
                  'temp' => $a_temp,
                  'name' => $a_name,
                  'size' => $a_size,
                  'mime' => $a_mime,
                  'tID' => $T->id,
                  'rID' => $replyID,
                  'dept' => $T->department,
                  'incr' => $i
                ));
                $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID[0];
              }
            }
          }
          // History log..
          $MSTICKET->historyLog($T->id, str_replace(array(
            '{visitor}',
            '{id}'
          ), array(
            mswSafeDisplay($LI_ACC->name),
            $replyID
          ), $msg_ticket_history['vis-reply-add']));
          // Dispute ticket or standard operations..
          switch ($T->isDisputed) {
            case 'no':
              // Was ticket closed..
              if (isset($_POST['close'])) {
                $closeRrows = $MSTICKET->openclose($T->id, 'close');
                // History if affected rows..
                if ($closeRrows > 0) {
                  $MSTICKET->historyLog($T->id, str_replace('{user}', mswSafeDisplay($LI_ACC->name), $msg_ticket_history['vis-ticket-close']));
                  // Should we switch emails off?
                  if ($SETTINGS->closenotify == 'yes') {
                    define('EMAILS_OFF', 1);
                  }
                }
              }
              break;
            default:
              break;
          }
          // Mail tags..
          if (!defined('EMAILS_OFF')) {
            $MSMAIL->addTag('{ACC_NAME}', $LI_ACC->name);
            $MSMAIL->addTag('{TICKET}', mswTicketNumber($T->id));
            $MSMAIL->addTag('{SUBJECT}', $T->subject);
            $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
            $MSMAIL->addTag('{DEPT}', $MSYS->department($T->department, $msg_script30));
            $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($T->priority));
            $MSMAIL->addTag('{STATUS}', (isset($closeRrows) && $closeRrows > 0 ? $msg_showticket24 : $msg_showticket23));
            $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswDefineNewline(), $attString) : 'N/A'));
            $MSMAIL->addTag('{CUSTOM}', $MSFIELDS->email($T->id, $replyID));
            $MSMAIL->addTag('{ID}', $T->id);
            // Send message to support staff..
            if ($T->assignedto && $T->assignedto != 'waiting') {
              $sqlClause = 'WHERE `userID` IN(' . $T->assignedto . ') AND `notify` = \'yes\'';
            } else {
              $sqlClause = 'WHERE `deptID` = \'' . $T->department . '\' AND `userID` != \'1\' AND `notify` = \'yes\'';
            }
            $qU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2` FROM `" . DB_PREFIX . "userdepts`
                  LEFT JOIN `" . DB_PREFIX . "departments`
                  ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                  LEFT JOIN `" . DB_PREFIX . "users`
                  ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                  $sqlClause
                  GROUP BY `email`
                  ORDER BY `" . DB_PREFIX . "users`.`name`
                  ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            while ($STAFF = mysqli_fetch_object($qU)) {
              $MSMAIL->addTag('{NAME}', $STAFF->teamName);
              $MSMAIL->sendMSMail(array(
                'from_email' => $SETTINGS->email,
                'from_name' => $SETTINGS->website,
                'to_email' => $STAFF->email,
                'to_name' => $STAFF->teamName,
                'subject' => str_replace(array(
                  '{website}',
                  '{ticket}'
                ), array(
                  $SETTINGS->website,
                  mswTicketNumber($T->id)
                ), $emailSubjects['reply-notify']),
                'replyto' => array(
                  'name' => $SETTINGS->website,
                  'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                ),
                'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/ticket-reply.txt',
                'language' => $SETTINGS->language,
                'alive' => 'yes',
                'add-emails' => $STAFF->email2
              ));
            }
            // Now send to global user if ticket assign is off..
            if ($T->assignedto == '') {
              $GLOBAL = mswGetTableData('users', 'id', 1, 'AND `notify` = \'yes\'', '`name`,`email`,`email2`');
              if (isset($GLOBAL->name)) {
                $MSMAIL->addTag('{NAME}', $GLOBAL->name);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $GLOBAL->email,
                  'to_name' => $GLOBAL->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($T->id)
                  ), $emailSubjects['reply-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/ticket-reply.txt',
                  'language' => $SETTINGS->language,
                  'alive' => 'yes',
                  'add-emails' => $GLOBAL->email2
                ));
              }
            }
          }
          // If this ticket is a dispute, send notification to relevant users..
          if ($T->isDisputed == 'yes') {
            // Check if this ticket was originally opened by imap..
            // If it was, set the reply-to address as the imap address..
            // This is so any replies sent go back to the ticket..
            if ($T->source == 'imap') {
              $IMD = mswGetTableData('imap', 'im_dept', $T->department);
              if (isset($IMD->im_email) && $IMD->im_email) {
                $replyToAddr = $IMD->im_email;
              }
            }
            // Get all users in this dispute..
            $ticketDisputeUsers = $MSTICKET->disputeUsers($T->id);
            // Add original ticket starter to the mix..
            array_push($ticketDisputeUsers, $T->visitorID);
            // Send, but skip person currently logged in..
            if (!empty($ticketDisputeUsers)) {
              $qDU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email`,`language` FROM `" . DB_PREFIX . "portal`
                     WHERE `id` IN(" . mswSafeImportString(implode(',', $ticketDisputeUsers)) . ")
                     AND `id`   != '{$LI_ACC->id}'
                     GROUP BY `email`
                     ORDER BY `name`
                     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
              while ($D_USR = mysqli_fetch_object($qDU)) {
                $pLang = '';
                $temp  = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/dispute-reply.txt';
                // Get correct language file..
                if (isset($D_USR->language) && file_exists(PATH . 'content/language/' . $D_USR->language . '/mail-templates/dispute-reply.txt')) {
                  $pLang = $D_USR->language;
                  $temp  = PATH . 'content/language/' . $D_USR->language . '/mail-templates/dispute-reply.txt';
                }
                $MSMAIL->addTag('{USER}', $LI_ACC->name);
                $MSMAIL->addTag('{NAME}', $D_USR->name);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $D_USR->email,
                  'to_name' => $D_USR->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($T->id)
                  ), $emailSubjects['dispute-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => (isset($replyToAddr) ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
                  ),
                  'template' => $temp,
                  'language' => ($pLang ? $pLang : $SETTINGS->language),
                  'alive' => 'yes'
                ));
              }
            }
          }
          $MSMAIL->smtpClose();
          // Finish with message..
          $json = array(
            'status' => 'reload'
          );
        }
      } else {
        $json = array(
          'status' => 'err',
          'msg' => implode('<br>', $eFields)
        );
      }
    }
  }
}

?>