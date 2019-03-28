<?php

// Ticket creation..

$MSTICKET->upload = $MSUPL;

if (defined('AJAX_HANDLER')) {
  // Is user logged in?
  if (MS_PERMISSIONS != 'guest' && isset($LI_ACC->name) && !isset($_POST['name'])) {
    $_POST['name']  = mswCleanData($LI_ACC->name);
    $_POST['email'] = $LI_ACC->email;
  }
  if (isset($_POST['name']) && $_POST['name'] == '') {
    $eFields[] = $msadminlang3_1createticket[1];
  }
  if (isset($_POST['email']) && !mswIsValidEmail($_POST['email'])) {
   $eFields[] = $msg_main13;
  }
  if ((int) $_POST['dept'] == '0') {
    $eFields[] = $msadminlang3_1createticket[2];
  }
  if ($_POST['subject'] == '') {
    $eFields[] = $msadminlang3_1createticket[3];
  }
  if ($_POST['comments'] == '') {
    $eFields[] = $msadminlang3_1createticket[4];
  }
  if (!in_array($_POST['priority'], $levelPrKeys)) {
    $eFields[] = $msadminlang3_1createticket[5];
  }
  if (!isset($_SESSION['ggrcver']) && $SETTINGS->recaptchaPublicKey && $SETTINGS->recaptchaPrivateKey) {
    $ggres = (isset($_POST['g-recaptcha-response']) ? $GRECAP->verify() : 'fail');
    switch($ggres) {
      case 'nothing-supported':
        $eFields[] = $msadminlangpublic[4];
        break;
      case 'fail':
        $eFields[] = $msg_public_create11;
        break;
      case 'ok':
        $_SESSION['ggrcver'] = 'verified' . time();
        break;
    }
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
  $customCheckFields = $MSFIELDS->check('ticket', (int) $_POST['dept']);
  if (!empty($customCheckFields)) {
    $eFields[] = str_replace('{count}', count($customCheckFields), $msadminlang3_1createticket[8]);
  }
  // All ok?
  if (empty($eFields) && isset($_POST['dept']) && (int) $_POST['dept'] > 0) {
    $deptID = (int) $_POST['dept'];
    // Department preferences..
    $DP     = mswGetTableData('departments', 'id', $deptID, '', '`manual_assign`');
    // If not logged in, lets see if this account exists..
    if (!isset($LI_ACC->id)) {
      $LI_ACC = mswGetTableData('portal', 'email', mswSafeImportString($_POST['email']));
    }
    // Is person logged in or does person already have account?
    if (isset($LI_ACC->name)) {
      $name   = $LI_ACC->name;
      $email  = $LI_ACC->email;
      $pass   = '';
      $userID = $LI_ACC->id;
    } else {
      define('NEW_ACC_CREATION', 1);
      $name   = mswCleanData($_POST['name']);
      $email  = $_POST['email'];
      $pass   = $MSACC->ms_generate();
      $mailT  = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-account.txt';
      // Create account..
      $userID = $MSACC->add(array(
        'name' => $name,
        'email' => $email,
        'pass' => $pass,
        'enabled' => 'yes',
        'verified' => 'yes',
        'timezone' => $SETTINGS->timezone,
        'ip' => mswIPAddresses(),
        'notes' => '',
        'language' => $SETTINGS->language
      ));
      // Send email about new account..
      $MSMAIL->addTag('{ACC_NAME}', $name);
      $MSMAIL->addTag('{ACC_EMAIL}', $email);
      $MSMAIL->addTag('{PASS}', $pass);
      $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
      $MSMAIL->sendMSMail(array(
        'from_email' => $SETTINGS->email,
        'from_name' => $SETTINGS->website,
        'to_email' => $email,
        'to_name' => $name,
        'subject' => str_replace(array(
          '{website}'
        ), array(
          $SETTINGS->website
        ), $emailSubjects['new-account']),
        'replyto' => array(
          'name' => $SETTINGS->website,
          'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
        ),
        'template' => $mailT,
        'language' => $SETTINGS->language
      ));
      $MSMAIL->smtpClose();
    }
    // Add ticket to database..
    if ($userID > 0) {
      $ID = $MSTICKET->add(array(
        'dept' => $deptID,
        'assigned' => ($DP->manual_assign == 'yes' ? 'waiting' : ''),
        'visitor' => $userID,
        'subject' => $_POST['subject'],
        'quoteBody' => '',
        'comments' => $_POST['comments'],
        'priority' => $_POST['priority'],
        'replyStatus' => 'start',
        'ticketStatus' => 'open',
        'ip' => mswIPAddresses(),
        'notes' => '',
        'disputed' => 'no'
      ));
      // Proceed if ticket added ok..
      if ($ID > 0) {
        // Add attachments..
        if ($SETTINGS->attachment == 'yes' && !empty($ticketAttachments)) {
          for ($i = 0; $i < count($ticketAttachments); $i++) {
            $a_name = $ticketAttachments[$i]['name'];
            $a_temp = $ticketAttachments[$i]['temp'];
            $a_size = $ticketAttachments[$i]['size'];
            $a_mime = $ticketAttachments[$i]['type'];
            if ($a_name && $a_temp && $a_size > 0) {
              $atID = $MSTICKET->addAttachment(array(
                'temp' => $a_temp,
                'name' => $a_name,
                'size' => $a_size,
                'mime' => $a_mime,
                'tID' => $ID,
                'rID' => 0,
                'dept' => $deptID,
                'incr' => $i
              ));
              $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID[0];
            }
          }
        }
        // Mail tags..
        $MSMAIL->addTag('{ACC_NAME}', $name);
        $MSMAIL->addTag('{ACC_EMAIL}', $email);
        $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($_POST['subject']));
        $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID));
        $MSMAIL->addTag('{DEPT}', $MSYS->department($deptID, $msg_script30));
        $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($_POST['priority']));
        $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
        $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswDefineNewline(), $attString) : 'N/A'));
        $MSMAIL->addTag('{CUSTOM}', $MSFIELDS->email($ID, 0));
        $MSMAIL->addTag('{ID}', $ID);
        // Send message to support staff if manual assign is off for department..
        // This doesn`t include the global user..
        if ($DP->manual_assign == 'no') {
          $qU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2` FROM `" . DB_PREFIX . "userdepts`
                LEFT JOIN `" . DB_PREFIX . "departments`
                ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                LEFT JOIN `" . DB_PREFIX . "users`
                ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                WHERE `deptID`  = '{$deptID}'
                AND `userID`   != '1'
                AND `notify`    = 'yes'
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
                mswTicketNumber($ID)
              ), $emailSubjects['new-ticket']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
              ),
              'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-staff.txt',
              'language' => $SETTINGS->language,
              'alive' => 'yes',
              'add-emails' => $STAFF->email2
            ));
          }
        }
        // Now send to global user..
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
              mswTicketNumber($ID)
            ), $emailSubjects['new-ticket']),
            'replyto' => array(
              'name' => $SETTINGS->website,
              'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-admin.txt',
            'language' => $SETTINGS->language,
            'alive' => 'yes',
            'add-emails' => $GLOBAL->email2
          ));
        }
        // Send auto responder to person who opened ticket..
        if (!defined('NEW_ACC_CREATION') && file_exists(LANG_PATH . 'mail-templates/new-ticket-visitor.txt')) {
          $mailT = LANG_PATH . 'mail-templates/new-ticket-visitor.txt';
          $pLang = $LI_ACC->language;
        } else {
          $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-visitor.txt';
        }
        $MSMAIL->addTag('{NAME}', $name);
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $email,
          'to_name' => $name,
          'subject' => str_replace(array(
            '{website}',
            '{ticket}'
          ), array(
            $SETTINGS->website,
            mswTicketNumber($ID)
          ), $emailSubjects['new-ticket-vis']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => $mailT,
          'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
        ));
        // Close smtp
        $MSMAIL->smtpClose();
        // Write history log..
        $MSTICKET->historyLog($ID, str_replace(array(
          '{visitor}'
        ), array(
          $name
        ), $msg_ticket_history['new-ticket-visitor']));
        // All done, so set session vars and show thanks page..
        $_SESSION['create']          = array();
        $_SESSION['create']['id']    = $ID;
        $_SESSION['create']['email'] = $email;
        $_SESSION['create']['pass']  = $pass;
        $json = array(
          'status' => 'ok',
          'field' => 'redirect',
          'msg' => $SETTINGS->scriptpath . '/?p=tk-msg'
        );
      }
    }
  } else {
    $json = array(
      'status' => 'err',
      'msg' => implode('<br>', $eFields)
    );
  }
}

?>