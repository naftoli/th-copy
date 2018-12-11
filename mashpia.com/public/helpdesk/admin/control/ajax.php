<?php

if (!defined('PARENT') || !isset($_GET['ajax'])) {
  exit;
}

// For CSV files, read contents to get total lines.
// Set max to read at 5mb
define('CSV_COUNT_MAX_LINES', (1024 * 1024 * 5));

// Load classes not loaded by main system..
include(REL_PATH . 'control/classes/class.accounts.php');
include(PATH . 'control/classes/class.accounts.php');
include(REL_PATH . 'control/classes/class.upload.php');
$MSACC           = new accountSystem();
$MSPTL           = new accounts();
$MSPTL->settings = $SETTINGS;
$MSACC->settings = $SETTINGS;
$MSUPL           = new msUpload();

$json = array(
  'msg' => 'err',
  'info' => $msadminlang3_1[3],
  'sys' => $msadminlang3_1[2],
  'delconfirm' => 0
);

// Load mail params
include_once(REL_PATH . 'control/mail-data.php');

// Parse based on directive..
switch ($_GET['ajax']) {

  //=========================
  // Mailbox
  //=========================

  case 'mbmove':
  case 'mbread':
  case 'mbunread':
  case 'mbdel':
  case 'mbclear':
  case 'mbcompose':
  case 'mbreply':
  case 'mbfolders':
    include(PATH . 'control/classes/class.mailbox.php');
    $MSMB           = new mailBox();
    $MSMB->settings = $SETTINGS;
    $MSMB->datetime = $MSDT;
    switch($_GET['ajax']) {
      case 'mbmove':
        $MSMB->moveTo($_GET['param'], $MSTEAM->id);
        $json = array(
          'msg' => 'ok'
        );
        break;
      case 'mbread':
      case 'mbunread':
        $MSMB->mark($_GET['ajax'], $MSTEAM->id);
        $json = array(
          'msg' => 'ok'
        );
        break;
      case 'mbdel':
        if ($MSTEAM->mailDeletion == 'yes' || $MSTEAM->id == '1') {
          $rows = $MSMB->delete($MSTEAM->id);
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'mbclear':
        if ($MSTEAM->mailDeletion == 'yes' || $MSTEAM->id == '1') {
          $MSMB->emptyBin($MSTEAM->id);
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'mbcompose':
        if (isset($_POST['subject'],$_POST['message']) && $_POST['subject'] && $_POST['message'] && !empty($_POST['staff'])) {
          foreach ($_POST['staff'] AS $staffID) {
            $id = $MSMB->add(array(
              'staff' => $MSTEAM->id,
              'to' => $staffID,
              'subject' => $_POST['subject'],
              'message' => $_POST['message']
            ));
            // Proceed if added ok..
            // Are we sending notification to staff mailbox?
            if ($id > 0 && $MSTEAM->mailCopy == 'yes') {
              $USR = mswGetTableData('users', 'id', $staffID, '', '`name`,`email`,`email2`,`notify`');
              if (isset($USR->name) && $USR->notify == 'yes') {
                $MSMAIL->addTag('{NAME}', $USR->name);
                $MSMAIL->addTag('{SENDER}', $MSTEAM->name);
                // Send mail..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $USR->email,
                  'to_name' => $USR->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{user}'
                  ), array(
                    $SETTINGS->website,
                    $MSTEAM->name
                  ), $emailSubjects['mailbox-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => LANG_PATH . 'mailbox-notification.txt',
                  'language' => $SETTINGS->language,
                  'add-emails' => $USR->email2,
                  'alive' => 'yes'
                ));
              }
            }
          }
          $MSMAIL->smtpClose();
          $json = array(
            'msg' => 'ok'
          );
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msgadminlang3_1mailbox[5]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'mbreply':
        if (isset($_POST['message']) && $_POST['message'] && isset($_POST['msgID'])) {
          // Get other person in message..
          $MID = (int) $_POST['msgID'];
          $OT  = mswGetTableData('mailassoc', 'mailID', $MID, 'AND `staffID` != \'' . $MSTEAM->id . '\'');
          if (isset($OT->staffID)) {
            $id = $MSMB->reply(array(
              'staff' => $MSTEAM->id,
              'to' => $OT->staffID,
              'id' => $MID,
              'message' => $_POST['message']
            ));
            // Proceed if added ok..
            // Are we sending notification to staff mailbox?
            if ($id > 0 && $MSTEAM->mailCopy == 'yes') {
              $USR = mswGetTableData('users', 'id', $OT->staffID, '', '`name`,`email`,`email2`,`notify`');
              if (isset($USR->name) && $USR->notify == 'yes') {
                $MSMAIL->addTag('{NAME}', $USR->name);
                $MSMAIL->addTag('{SENDER}', $MSTEAM->name);
                $MSMAIL->addTag('{TOPIC}', $_POST['subject']);
                // Send mail..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $USR->email,
                  'to_name' => $USR->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{user}'
                  ), array(
                    $SETTINGS->website,
                    $MSTEAM->name
                  ), $emailSubjects['mailbox-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => LANG_PATH . 'mailbox-notification-reply.txt',
                  'language' => $SETTINGS->language,
                  'add-emails' => $USR->email2
                ));
              }
            }
            $json = array(
              'msg' => 'ok'
            );
          }
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msgadminlang3_1mailbox[6]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'mbfolders':
        $MSMB->folders($MSTEAM->id);
        $json = array(
          'msg' => 'ok'
        );
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'delconfirm' => (isset($rows) ? $rows : '0')
      );
    }
    break;

  //=========================
  // Tickets
  //=========================

  case 'ticket':
  case 'tickdel':
  case 'tickexp':
  case 'ticknotes':
  case 'tickaccept':
  case 'tickassign':
  case 'tickreply':
  case 'tickrepdel':
  case 'tickedit':
  case 'tickdept':
  case 'tickrepedit':
  case 'tickdispusers':
  case 'tickresponse':
  case 'tickdelhis':
  case 'tickhisexp':
  case 'tickattdel':
  case 'tickopen':
    $improws = 0;
    switch($_GET['ajax']) {
      case 'ticket':
        // Call the relevant classes..
        include_once(REL_PATH . 'control/classes/class.tickets.php');
        include_once(REL_PATH . 'control/classes/class.fields.php');
        $MSPTICKETS           = new tickets();
        $MSCFMAN              = new customFieldManager();
        $MSPTICKETS->settings = $SETTINGS;
        $MSPTICKETS->datetime = $MSDT;
        $MSPTICKETS->upload   = $MSUPL;
        if ($_POST['subject'] && $_POST['comments'] && $_POST['name'] && mswIsValidEmail($_POST['email'])) {
          // Check if account exists for email address..
          $PORTAL = mswGetTableData('portal', 'email', mswSafeImportString(strtolower($_POST['email'])));
          // Check language..
          if (isset($_PORTAL->id) && $PORTAL->language && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-add-ticket.txt')) {
            $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-add-ticket.txt';
            $pLang = $PORTAL->language;
          } else {
            $mailT = LANG_PATH . 'admin-add-ticket.txt';
          }
          $pass  = '';
          $ipAdr = (isset($PORTAL->ip) ? $PORTAL->ip : '');
          // If portal account doesn`t exist, we need to create it..
          if (!isset($PORTAL->id)) {
            $pass   = $MSACC->ms_generate();
            $mailT  = LANG_PATH . 'admin-add-ticket-new.txt';
            $userID = $MSACC->add(array(
              'name' => $_POST['name'],
              'email' => $_POST['email'],
              'pass' => $pass,
              'enabled' => 'yes',
              'verified' => 'yes',
              'timezone' => '',
              'ip' => '',
              'notes' => '',
              'language' => $SETTINGS->language
            ));
          }
          // Add ticket to database..
          if ((isset($userID) && $userID > 0) || isset($PORTAL->id)) {
            $ID = $MSPTICKETS->add(array(
              'dept' => (int) $_POST['dept'],
              'assigned' => (isset($_POST['waiting']) ? 'waiting' : (!empty($_POST['assigned']) ? implode(',', $_POST['assigned']) : '')),
              'visitor' => (isset($userID) ? $userID : $PORTAL->id),
              'subject' => $_POST['subject'],
              'quoteBody' => '',
              'comments' => $_POST['comments'],
              'priority' => $_POST['priority'],
              'replyStatus' => (isset($_POST['closed']) ? 'admin' : (isset($_POST['waiting']) ? 'start' : 'visitor')),
              'ticketStatus' => (isset($_POST['closed']) ? 'close' : 'open'),
              'ip' => $ipAdr,
              'notes' => $_POST['notes'],
              'disputed' => 'no'
            ));
            // Add attachments, history, send emails..
            if ($ID > 0) {
              // Attachments..
              $attString = array();
              if (!empty($_FILES['file']['tmp_name'])) {
                for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
                  $a_name = $_FILES['file']['name'][$i];
                  $a_temp = $_FILES['file']['tmp_name'][$i];
                  $a_size = $_FILES['file']['size'][$i];
                  $a_mime = $_FILES['file']['type'][$i];
                  if ($a_name && $a_temp && $a_size > 0) {
                    $atID  = $MSPTICKETS->addAttachment(array(
                      'temp' => $a_temp,
                      'name' => $a_name,
                      'size' => $a_size,
                      'mime' => $a_mime,
                      'tID' => $ID,
                      'rID' => 0,
                      'dept' => $_POST['dept'],
                      'incr' => $i
                    ));
                    $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID[0];
                  }
                }
              }
              // Log..
              $MSTICKET->historyLog($ID, str_replace(array(
                '{user}'
              ), array(
                $MSTEAM->name
              ), $msg_ticket_history['new-ticket-admin']));
              // Everything in the post array..
              foreach ($_POST AS $key => $value) {
                if (!is_array($value)) {
                  $MSMAIL->addTag('{' . strtoupper($key) . '}', $MSBB->cleaner($value));
                }
              }
              // Send notification to visitor if enabled..
              if (isset($_POST['accMail']) && !isset($_POST['closed'])) {
                // Tags..
                $MSMAIL->addTag('{NAME}', $_POST['name']);
                $MSMAIL->addTag('{TITLE}', $_POST['subject']);
                $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
                $MSMAIL->addTag('{EMAIL}', $_POST['email']);
                $MSMAIL->addTag('{PASSWORD}', $pass);
                $MSMAIL->addTag('{ID}', $ID);
                $MSMAIL->sendMSMail(array(
                  'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                  'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                  'to_email' => $_POST['email'],
                  'to_name' => $_POST['name'],
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
                  'template' => $mailT,
                  'language' => (isset($pLang) ? $pLang : $SETTINGS->language),
                  'alive' => 'yes'
                ));
                $MSMAIL->smtpClose();
              }
              // Send notification to support staff..
              // If ticket is waiting assignment, no emails are sent..
              if (isset($_POST['assignMail']) && !isset($_POST['waiting']) && !isset($_POST['closed'])) {
                // Are we notifying staff who are assigned to this ticket?
                $userList = array();
                if (!empty($_POST['assigned'])) {
                  $as = implode(',', $_POST['assigned']);
                  $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name`,`email`,`email2` FROM `" . DB_PREFIX . "users`
                        WHERE `id`    IN({$as})
                        AND `id`  NOT IN (1,{$MSTEAM->id})
                        AND `notify`   = 'yes'
                        ORDER BY `id`
                        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
                  while ($USR = mysqli_fetch_object($q)) {
                    $userList[$USR->id] = array(
                      $USR->name,
                      $USR->email,
                      $USR->email2
                    );
                  }
                  $mailT = LANG_PATH . 'admin-ticket-assign.txt';
                } else {
                  $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `" . DB_PREFIX . "users`.`id` AS `usrID`,`name`,`email`,`email2` FROM `" . DB_PREFIX . "userdepts`
                       LEFT JOIN `" . DB_PREFIX . "users`
                       ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                       WHERE `deptID`                        = '{$_POST['dept']}'
                       AND `" . DB_PREFIX . "users`.`id`    NOT IN (1,{$MSTEAM->id})
                       AND `notify`                          = 'yes'
                       GROUP BY `" . DB_PREFIX . "userdepts`.`userID`
                       ORDER BY `" . DB_PREFIX . "userdepts`.`userID`
                       ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
                  while ($USR = mysqli_fetch_object($q)) {
                    $userList[$USR->usrID] = array(
                      $USR->name,
                      $USR->email,
                      $USR->email2
                    );
                  }
                  $mailT = LANG_PATH . 'admin-add-ticket-staff-notify.txt';
                }
                // Tags..
                $MSMAIL->addTag('{TITLE}', $_POST['subject']);
                $MSMAIL->addTag('{TICKETS}', str_replace(array(
                  '{id}',
                  '{subject}'
                ), array(
                  mswTicketNumber($ID),
                  $_POST['subject']
                ), $msg_assign7));
                $MSMAIL->addTag('{TEAM_NAME}', $MSTEAM->name);
                $MSMAIL->addTag('{ASSIGNEE}', $MSTEAM->name);
                $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID));
                $MSMAIL->addTag('{ACC_NAME}', $_POST['name']);
                $MSMAIL->addTag('{ACC_EMAIL}', $_POST['email']);
                $MSMAIL->addTag('{SUBJECT}', $_POST['subject']);
                $MSMAIL->addTag('{DEPT}', $MSYS->department($_POST['dept'], $msg_script30));
                $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($_POST['priority']));
                $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
                $MSMAIL->addTag('{CUSTOM}', $MSCFMAN->email($ID, 0));
                $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswDefineNewline(), $attString) : 'N/A'));
                $MSMAIL->addTag('{ID}', $ID);
                // Anyone to send a message to..
                if (!empty($userList)) {
                  foreach ($userList AS $k => $v) {
                    $teamID = $k;
                    $name   = $v[0];
                    $email  = $v[1];
                    $email2 = $v[2];
                    $MSMAIL->addTag('{NAME}', $name);
                    $MSMAIL->sendMSMail(array(
                      'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                      'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                      'to_email' => $email,
                      'to_name' => $name,
                      'subject' => str_replace(array(
                        '{website}',
                        '{ticket}'
                      ), array(
                        $SETTINGS->website,
                        mswTicketNumber($ID)
                      ), $emailSubjects['new-ticket-team']),
                      'replyto' => array(
                        'name' => $SETTINGS->website,
                        'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                      ),
                      'template' => $mailT,
                      'language' => $SETTINGS->language,
                      'alive' => 'yes',
                      'add-emails' => $email2
                    ));
                  }
                  $MSMAIL->smtpClose();
                }
                // Send mail to global user if applicable and if the global user isn`t the one adding the ticket..
                // Applies to department level filtering only, not assigned..
                if (empty($_POST['assigned']) && $MSTEAM->id > 1) {
                  $GLOBAL = mswGetTableData('users', 'id', '1');
                  if (isset($GLOBAL->id) && $GLOBAL->notify == 'yes') {
                    $MSMAIL->addTag('{NAME}', $GLOBAL->name);
                    $MSMAIL->sendMSMail(array(
                      'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                      'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                      'to_email' => $GLOBAL->email,
                      'to_name' => $GLOBAL->name,
                      'subject' => str_replace(array(
                        '{website}',
                        '{ticket}'
                      ), array(
                        $SETTINGS->website,
                        mswTicketNumber($ID)
                      ), $emailSubjects['new-ticket-team']),
                      'replyto' => array(
                        'name' => $SETTINGS->website,
                        'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                      ),
                      'template' => $mailT,
                      'language' => $SETTINGS->language,
                      'add-emails' => $GLOBAL->email2
                    ));
                  }
                }
              }
              // Log for closed..
              if (isset($_POST['closed'])) {
                $MSTICKET->historyLog($ID, str_replace(array(
                  '{user}'
                ), array(
                  $MSTEAM->name
                ), $msg_ticket_history['new-ticket-admin-close']));
              }
              // Redirect to ticket..
              $json = array(
                'msg' => 'ok',
                'field' => 'redirect',
                'redirect' => 'index.php?p=edit-ticket&id=' . $ID
              );
              echo $JSON->encode($json);
              exit;
            }
          }
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1adminaddticket[0]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickdel':
        if (USER_DEL_PRIV == 'yes') {
          include(REL_PATH . 'control/lib/b8/call_b8.php');
          if ($B8_CFG->learning == 'yes') {
            $MSTICKET->spamLearning('spam', $MSB8);
          }
          $tick = $MSTICKET->deleteTickets();
          $rows = (!empty($_POST['del']) ? count($_POST['del']) : '0');
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tickexp':
        include_once(REL_PATH . 'control/classes/class.download.php');
        $MSDL = new msDownload();
        $file = $MSTICKET->exportTicketStats($MSDT, $MSDL);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => 'admin/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
      case 'ticknotes':
        $ID   = (isset($_GET['id']) ? (int) $_GET['id'] : '0');
        if ($ID > 0) {
          $rows = $MSTICKET->updateNotes($ID);
          // History log..
          if ($rows > 0) {
            $MSTICKET->historyLog($ID, str_replace(array(
              '{user}'
            ), array(
              $MSTEAM->name
            ), $msg_ticket_history['ticket-notes-edit']));
          }
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tickaccept':
        include(REL_PATH . 'control/lib/b8/call_b8.php');
        if ($B8_CFG->learning == 'yes') {
          $MSTICKET->spamLearning('ham', $MSB8);
        }
        $rows = $MSTICKET->notSpam();
        // If rows were affected, write log for each ticket and send relevant emails..
        if ($rows > 0) {
          foreach ($_POST['del'] AS $tID) {
            $replyToAddr = '';
            $MSTICKET->historyLog($tID, str_replace(array(
              '{user}'
            ), array(
              $MSTEAM->name
            ), $msg_ticket_history['ticket-spam-accept']));
            // Load data..
            $ST     = mswGetTableData('tickets', 'id', $tID);
            $PORTAL = mswGetTableData('portal', 'id', $ST->visitorID);
            // Mail tags..
            $MSMAIL->addTag('{ACC_NAME}', $PORTAL->name);
            $MSMAIL->addTag('{ACC_EMAIL}', $PORTAL->email);
            $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($ST->subject));
            $MSMAIL->addTag('{TICKET}', mswTicketNumber($tID));
            $MSMAIL->addTag('{DEPT}', $MSYS->department($ST->department, $msg_script30));
            $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($ST->priority));
            $MSMAIL->addTag('{STATUS}', $msg_showticket23);
            $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($ST->comments));
            $MSMAIL->addTag('{ATTACHMENTS}', $MSTICKET->attachList($tID));
            $MSMAIL->addTag('{ID}', $tID);
            $MSMAIL->addTag('{CUSTOM}', 'N/A');
            // Is this ticket going to be assigned?
            if ($ST->assignedto != 'waiting') {
              $qU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2` FROM `" . DB_PREFIX . "userdepts`
                    LEFT JOIN `" . DB_PREFIX . "departments`
                    ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                    LEFT JOIN `" . DB_PREFIX . "users`
                    ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                    WHERE `deptID`  = '{$ST->department}'
                    AND `userID`   != '1'
                    AND `notify`    = 'yes'
                    GROUP BY `email`
                    ORDER BY `" . DB_PREFIX . "users`.`name`
                    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
              while ($STAFF = mysqli_fetch_object($qU)) {
                $MSMAIL->addTag('{NAME}', $STAFF->teamName);
                $MSMAIL->sendMSMail(array(
                  'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                  'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                  'to_email' => $STAFF->email,
                  'to_name' => $STAFF->teamName,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($tID)
                  ), $emailSubjects['new-ticket']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => REL_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-staff.txt',
                  'language' => $SETTINGS->language,
                  'alive' => 'yes',
                  'add-emails' => $STAFF->email2
                ));
              }
              $MSMAIL->smtpClose();
            }
            // Send to admin if not admin logged in..
            if ($MSTEAM->id != '1') {
              $GLOBAL = mswGetTableData('users', 'id', 1, 'AND `notify` = \'yes\'', '`name`,`email`,`email2`');
              if (isset($GLOBAL->name)) {
                $MSMAIL->addTag('{NAME}', $GLOBAL->name);
                $MSMAIL->sendMSMail(array(
                  'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                  'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                  'to_email' => $GLOBAL->email,
                  'to_name' => $GLOBAL->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($tID)
                  ), $emailSubjects['new-ticket']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => REL_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-admin.txt',
                  'language' => $SETTINGS->language,
                  'alive' => 'yes',
                  'add-emails' => $GLOBAL->email2
                ));
              }
              $MSMAIL->smtpClose();
            }
            // Notify visitor..
            $IDEPT = mswGetTableData('imap', 'im_dept', $ST->department, '', '`im_email`');
            if (isset($IDEPT->im_email) && $IDEPT->im_email) {
              $replyToAddr = $IDEPT->im_email;
            }
            if (file_exists(REL_PATH . 'content/language/' . $PORTAL->language . '/mail-templates/new-ticket-visitor.txt')) {
              $mailT = REL_PATH . 'content/language/' . $PORTAL->language . '/mail-templates/new-ticket-visitor.txt';
              $pLang = $PORTAL->language;
            } else {
              $mailT = REL_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-visitor.txt';
            }
            $MSMAIL->addTag('{NAME}', $PORTAL->name);
            $MSMAIL->sendMSMail(array(
              'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
              'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
              'to_email' => $PORTAL->email,
              'to_name' => $PORTAL->name,
              'subject' => str_replace(array(
                '{website}',
                '{ticket}'
              ), array(
                $SETTINGS->website,
                mswTicketNumber($tID)
              ), $emailSubjects['new-ticket-vis']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($replyToAddr ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
              ),
              'template' => $mailT,
              'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
            ));
            $MSMAIL->smtpClose();
          }
        }
        $json = array(
          'msg' => 'ok'
        );
        break;
      case 'tickassign':
        if (!empty($_POST['users'])) {
          if (!empty($_POST['del'])) {
            $userNotify = array();
            $tickets    = array();
            $accepted   = array();
            foreach ($_POST['del'] AS $ID) {
              if (!empty($_POST['users'][$ID])) {
                // Ticket information..
                $SUPTICK = mswGetTableData('tickets', 'id', $ID);
                // Array of ticket subjects assigned to users..
                foreach ($_POST['users'][$ID] AS $userID) {
                  $tickets[$userID][] = str_replace(array(
                    '{id}',
                    '{subject}'
                  ), array(
                    mswTicketNumber($ID),
                    $SUPTICK->subject
                  ), $msg_assign7);
                  // Skip if it`s the logged in staff member
                  if ($userID != $MSTEAM->id) {
                    $userNotify[] = $userID;
                  }
                }
                // Update ticket..
                $MSTICKET->ticketUserAssign($ID, implode(',', $_POST['users'][$ID]), $msg_ticket_history['assign']);
                $accepted[] = $ID;
              }
            }
          } else {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1tickets[0]
            );
            echo $JSON->encode($json);
            exit;
          }
          // Email users..
          if (!empty($userNotify) && !empty($tickets) && isset($_POST['mail'])) {
            $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name`,`email`,`email2` FROM `" . DB_PREFIX . "users`
                 WHERE `id` IN(" . mswSafeImportString(implode(',', $userNotify)) . ")
                 GROUP BY `id`
                 ORDER BY `name`
                 ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            while ($USERS = mysqli_fetch_object($q)) {
              $MSMAIL->addTag('{ASSIGNEE}', $MSTEAM->name);
              $MSMAIL->addTag('{NAME}', $USERS->name);
              $MSMAIL->addTag('{TICKETS}', trim(implode(mswDefineNewline(), $tickets[$USERS->id])));
              // Send mail..
              $MSMAIL->sendMSMail(array(
                'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                'to_email' => $USERS->email,
                'to_name' => $USERS->name,
                'subject' => str_replace(array(
                  '{website}',
                  '{user}'
                ), array(
                  $SETTINGS->website,
                  $MSTEAM->name
                ), $emailSubjects['ticket-assign']),
                'replyto' => array(
                  'name' => $SETTINGS->website,
                  'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                ),
                'template' => LANG_PATH . 'admin-ticket-assign.txt',
                'language' => $SETTINGS->language,
                'add-emails' => $USERS->email2,
                'alive' => 'yes'
              ));
            }
            $MSMAIL->smtpClose();
          }
          $json = array(
            'msg' => 'ok',
            'accepted' => $accepted
          );
          echo $JSON->encode($json);
          exit;
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1tickets[0]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickreply':
        define('AJAX_TICK_REPLY', 1);
        if (isset($_POST['comments']) && trim($_POST['comments'])) {
          // Call the relevant classes..
          include_once(REL_PATH . 'control/classes/class.tickets.php');
          include_once(REL_PATH . 'control/classes/class.fields.php');
          $MSPTICKETS           = new tickets();
          $MSCFMAN              = new customFieldManager();
          $MSPTICKETS->settings = $SETTINGS;
          $MSPTICKETS->datetime = $MSDT;
          $MSPTICKETS->upload   = $MSUPL;
          include(PATH . 'control/system/tickets/ticket-reply.php');
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1adminviewticket[15]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickrepdel':
        if (USER_DEL_PRIV == 'yes') {
          $ID = (int) $_GET['param'];
          $RP = mswGetTableData('replies', 'id', $ID);
          $TK = mswGetTableData('tickets', 'id', $RP->ticketID);
          switch ($RP->replyType) {
            case 'admin':
              $NME = mswGetTableData('users', 'id', $RP->replyUser);
              break;
            default:
              $NME = mswGetTableData('portal', 'id', $RP->replyUser);
              break;
          }
          if (isset($TK->id)) {
            $rows = $MSTICKET->deleteReply($RP, $TK, $ID);
            // History log..
            if ($rows > 0) {
              $MSTICKET->historyLog($TK->id, str_replace(array(
                '{user}',
                '{id}',
                '{poster}'
              ), array(
                $MSTEAM->name,
                $ID,
                (isset($NME->name) ? $NME->name : 'N/A')
              ), $msg_ticket_history['reply-delete']));
            }
          }
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tickedit':
        if (USER_EDIT_T_PRIV == 'yes') {
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
          if (!empty($eFields)) {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => implode('<br>', $eFields)
            );
          } else {
            $trows = $MSTICKET->updateTicket();
            // Log if affected rows..
            if ($trows > 0) {
              $MSTICKET->historyLog($_POST['id'], str_replace(array(
                '{user}'
              ), array(
                $MSTEAM->name
              ), $msg_ticket_history['edit-ticket']));
            }
            $json = array(
              'msg' => 'ok'
            );
          }
        }
        break;
      case 'tickrepedit':
        if (USER_EDIT_R_PRIV == 'yes') {
          if ($_POST['comments'] == '') {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1createticket[4]
            );
          } else {
            $MSTICKET->updateTicketReply($msg_ticket_history['reply-edit']);
            $json = array(
              'msg' => 'ok'
            );
          }
        }
        break;
      case 'tickdept':
        $fields = '';
        $dept   = (isset($_GET['dp']) ? (int) $_GET['dp'] : '0');
        $tickID = (isset($_GET['id']) ? (int) $_GET['id'] : '0');
        $area   = (!isset($_GET['ar']) ? 'ticket' : (in_array($_GET['ar'], array(
          'ticket',
          'reply',
          'admin'
        )) ? $_GET['ar'] : 'ticket'));
        $isAssign = mswRowCount('departments WHERE `id` = \'' . $dept . '\' AND `manual_assign` = \'yes\'');
        // Custom fields..
        $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
              WHERE FIND_IN_SET('{$area}',`fieldLoc`)  > 0
              AND `enField`                            = 'yes'
              AND FIND_IN_SET('{$dept}',`departments`) > 0
              ORDER BY `orderBy`
              ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        if (mysqli_num_rows($qF) > 0) {
          while ($FIELDS = mysqli_fetch_object($qF)) {
            $html = '';
            if ($tickID > 0) {
              $TF   = mswGetTableData('ticketfields','ticketID',(int) $tickID,' AND `replyID` = \'0\' AND `fieldID` = \'' . $FIELDS->id . '\'');
              $html = (isset($TF->fieldData) ? $TF->fieldData : '');
            }
            switch ($FIELDS->fieldType) {
              case 'textarea':
                $fields .= $MSFM->buildTextArea(mswCleanData($FIELDS->fieldInstructions), $FIELDS->id, (++$tabIndex),$html);
                break;
              case 'input':
                $fields .= $MSFM->buildInputBox(mswCleanData($FIELDS->fieldInstructions), $FIELDS->id, (++$tabIndex),$html);
                break;
              case 'select':
                $fields .= $MSFM->buildSelect(mswCleanData($FIELDS->fieldInstructions), $FIELDS->id, $FIELDS->fieldOptions, (++$tabIndex),$html);
                break;
              case 'checkbox':
                $fields .= $MSFM->buildCheckBox(mswCleanData($FIELDS->fieldInstructions), $FIELDS->id, $FIELDS->fieldOptions,$html);
                break;
            }
          }
        }
        $json = array(
          'fields' => $fields,
          'assign' => ($isAssign > 0 ? 'yes' : 'no')
        );
        echo $JSON->encode($json);
        exit;
        break;
      case 'tickdispusers':
        $tickID = (isset($_POST['disputeID']) ? (int) $_POST['disputeID'] : '0');
        $TICKET = mswGetTableData('tickets', 'id', $tickID);
        $other  = array();
        $new    = array();
        $del    = array();
        if (isset($TICKET->visitorID)) {
          $USER = mswGetTableData('portal', 'id', $TICKET->visitorID);
          if (!empty($_POST['userID']) && $tickID > 0 && isset($USER->id)) {
            $msc = 0;
            // Anything to delete?
            if (!empty($_POST['duser'])) {
              $toGo = array();
              foreach ($_POST['duser'] AS $dduser) {
                $dduser = substr($dduser, 6);
                $D_USER = mswGetTableData('disputes', 'id', (int) $dduser);
                if (isset($D_USER->visitorID)) {
                  $D_PORTAL = mswGetTableData('portal', 'id', $D_USER->visitorID);
                  if (isset($D_PORTAL->id)) {
                    $del[] = mswCleanData($D_PORTAL->name);
                  }
                }
                $toGo[] = $dduser;
              }
              $MSTICKET->removeDisputeUsersFromTicket($toGo);
            }
            // Loop existing..
            foreach ($_POST['userID'] AS $k) {
              if (substr($k, 0, 2) == 't_') {
                $name   = $USER->name;
                $email  = $USER->email;
                $sbj    = $emailSubjects['dispute-notify'];
                $userID = $USER->id;
              } else {
                $PORTAL = mswGetTableData('portal', 'id', (int) $k);
                if (isset($PORTAL->id)) {
                  $name   = $PORTAL->name;
                  $email  = $PORTAL->email;
                  $sbj    = $emailSubjects['dispute'];
                  $pass   = '';
                  if ($PORTAL->language && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-dispute-user-current.txt')) {
                    $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-dispute-user-current.txt';
                    $pLang = $PORTAL->language;
                  } else {
                    $mailT = LANG_PATH . 'admin-dispute-user-current.txt';
                  }
                  $userID  = $PORTAL->id;
                  $other[] = $name;
                } else {
                  $name   = (isset($_POST['nm_' . $k]) ? mswCleanData($_POST['nm_' . $k]) : '');
                  $email  = (isset($_POST['em_' . $k]) && mswIsValidEmail($_POST['em_' . $k]) ? $_POST['em_' . $k] : '');
                  $sbj    = $emailSubjects['dispute'];
                  if ($name && $email) {
                    $pass   = $MSACC->ms_generate();
                    $mailT  = LANG_PATH . 'admin-dispute-user-new.txt';
                    $userID = $MSPTL->add(array(
                      'name' => $name,
                      'email' => $email,
                      'userPass' => $pass,
                      'enabled' => 'yes',
                      'timezone' => '',
                      'ip' => '',
                      'notes' => ''
                    ));
                    $PORTAL        = new stdclass();
                    $PORTAL->email = $email;
                    $other[]       = $name;
                  }
                }
              }
              if ($name && $email) {
                $send  = (!empty($_POST['notify']) && in_array($k, $_POST['notify']) ? 'yes' : 'no');
                $priv  = (!empty($_POST['priv']) && in_array($k, $_POST['priv']) ? 'yes' : 'no');
                // If this user isn`t in dispute already, add them..
                // Else, just update privileges..
                if (substr($k, 0, 2) != 't_') {
                  if (mswRowCount('disputes WHERE `ticketID` = \'' . $tickID . '\' AND `visitorID` = \'' . $userID . '\'') == 0) {
                    $MSTICKET->addDisputeUser($tickID, $userID, $priv);
                    $new[] = $name;
                  } else {
                    $MSTICKET->updateDisputePrivileges($userID, $tickID, 'user', $priv);
                  }
                } else {
                  $MSTICKET->updateDisputePrivileges($userID, $tickID, 'ticket', $priv);
                }
                // Send notification if enabled..
                if (substr($k, 0, 2) != 't_') {
                  if ($send == 'yes') {
                    $MSMAIL->addTag('{NAME}', $name);
                    $MSMAIL->addTag('{TITLE}', $TICKET->subject);
                    $MSMAIL->addTag('{EMAIL}', $email);
                    $MSMAIL->addTag('{PASSWORD}', $pass);
                    $MSMAIL->addTag('{ID}', $tickID);
                    $MSMAIL->addTag('{USER}', $USER->name);
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
                        mswTicketNumber($tickID)
                      ), $sbj),
                      'replyto' => array(
                        'name' => $SETTINGS->website,
                        'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                      ),
                      'template' => $mailT,
                      'language' => (isset($pLang) ? $pLang : $SETTINGS->language),
                      'alive' => 'yes'
                    ));
                    ++$msc;
                  }
                }
              }
            }
            // Send to ticket starter..
            $send    = (!empty($_POST['notify']) && in_array('t_' . $USER->id, $_POST['notify']) ? 'yes' : 'no');
            if ($send == 'yes' && !empty($other)) {
              $pLang = '';
               if ($USER->language && file_exists(LANG_BASE_PATH . $USER->language . '/mail-templates/admin-dispute-notification.txt')) {
                 $pLang = $USER->language;
               }
               $MSMAIL->addTag('{NAME}', $USER->name);
               $MSMAIL->addTag('{TITLE}', $TICKET->subject);
               $MSMAIL->addTag('{PEOPLE}', implode(mswDefineNewline(), $other));
               $MSMAIL->addTag('{ID}', $tickID);
               $MSMAIL->sendMSMail(array(
                 'from_email' => $SETTINGS->email,
                 'from_name' => $SETTINGS->website,
                 'to_email' => $USER->email,
                 'to_name' => $USER->name,
                 'subject' => str_replace(array(
                   '{website}',
                   '{ticket}'
                 ), array(
                   $SETTINGS->website,
                   mswTicketNumber($tickID)
                 ), $emailSubjects['dispute-notify-update']),
                 'replyto' => array(
                   'name' => $SETTINGS->website,
                   'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                 ),
                 'template' => LANG_PATH . 'admin-dispute-notification.txt',
                 'language' => ($pLang ? $pLang : $SETTINGS->language),
                 'alive' => 'yes'
               ));
               ++$msc;
            }
            // Anything delete?
            if (!empty($del)) {
              $MSTICKET->historyLog($tickID, str_replace(array(
                '{users}',
                '{admin}'
              ), array(
                implode(', ', $del),
                $MSTEAM->name
              ), $msg_ticket_history['dis-user-rem']));
            }
            // Add new users to ticket history log..
            if (!empty($new)) {
              $MSTICKET->historyLog($tickID, str_replace(array(
                '{users}',
                '{admin}'
              ), array(
                implode(', ', $new),
                $MSTEAM->name
              ), $msg_ticket_history['dis-user-add']));
            }
            // Close smtp..
            if ($msc > 0) {
              $MSMAIL->smtpClose();
            }
          }
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tickresponse':
        if (isset($_GET['id'])) {
          $SR   = mswGetTableData('responses', 'id', (int) $_GET['id']);
          $json = array(
            'msg' => 'ok',
            'response' => (isset($SR->answer) ? mswCleanData($SR->answer) : '')
          );
        }
        echo $JSON->encode($json);
        exit;
        break;
      case 'tickdelhis':
        if (USER_DEL_PRIV == 'yes' && isset($_GET['id']) && isset($_GET['t'])) {
          $ID = ($_GET['id'] != 'all' ? (int) $_GET['id'] : 'all');
          $TK = (int) $_GET['t'];
          $MSTICKET->deleteTicketHistory($ID, $TK);
          $json = array(
            'msg' => 'ok',
            'html' => $msg_viewticket111
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickhisexp':
        include(REL_PATH . 'control/classes/class.download.php');
        $MSDL = new msDownload();
        $file = $MSTICKET->exportTicketHistory($MSDL, $MSDT);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => 'admin/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
      case 'tickattdel':
        if (USER_DEL_PRIV == 'yes') {
          $ID  = (isset($_GET['param']) ? (int) $_GET['param'] : '0');
          $A   = mswGetTableData('attachments', 'id', $ID);
          if (isset($A->ticketID)) {
            $MSTICKET->deleteAttachments(array(
              $ID
            ));
            $cnt  = mswRowCount('attachments WHERE `ticketID` = \'' . $A->ticketID . '\' AND `replyID` = \'' . $A->replyID . '\'');
            $json = array(
              'msg' => 'ok',
              'cnt' => $cnt
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'tickopen':
        if (!empty($_POST['del'])) {
          $rows = $MSTICKET->batchReOpenTickets();
          // Write history entry..
          foreach ($_POST['del'] AS $t) {
            $MSTICKET->historyLog($t, str_replace(array(
              '{user}'
            ), array(
              $MSTEAM->name
            ), $msg_ticket_history['vis-ticket-open']));
          }
          $json = array(
            'msg' => 'ok'
          );
        } else {
          $json = array(
            'msg' => 'err',
            'info' => $msadminlang3_1[26],
            'sys' => $msadminlang3_1[2]
          );
        }
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'delconfirm' => (isset($rows) ? $rows : '0'),
        'importrows' => ($improws > 0 ? @number_format($improws) : '0')
      );
    }
    break;

  //=========================
  // FAQ
  //=========================

  case 'faqcat':
  case 'faqcatseq':
  case 'faqcatdel':
  case 'faqcatstate':
  case 'faq':
  case 'faqseq':
  case 'faqdel':
  case 'faqreset':
  case 'faqstate':
  case 'faqimport':
  case 'faqimport-upload':
  case 'faqattach':
  case 'faqattachseq':
  case 'faqattachdel':
  case 'faqattachstate':
    include_once(PATH . 'control/classes/class.faq.php');
    $FAQ           = new faqCentre();
    $FAQ->settings = $SETTINGS;
    $improws       = 0;
    switch($_GET['ajax']) {
      // Categories..
      case 'faqcat':
        if (isset($_POST['process'])) {
          if (trim($_POST['name'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswRowCount('categories') + 1) > RESTR_FAQ_CATS) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_FAQ_CATS,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $FAQ->addCategory();
          }
        }
        if (isset($_POST['update'])) {
          if (trim($_POST['name'])) {
            $FAQ->updateCategory();
          }
        }
        break;
      case 'faqcatseq':
        $FAQ->orderCatSequence();
        break;
      case 'faqcatdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $FAQ->deleteCategories();
        }
        break;
      case 'faqcatstate':
        $FAQ->enableDisableCats();
        break;
      // Questions..
      case 'faq':
        if (isset($_POST['process'])) {
          if (trim($_POST['question']) && trim($_POST['answer'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswRowCount('faq') + 1) > RESTR_FAQ_QUE) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_FAQ_QUE,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $FAQ->addQuestion();
          }
        }
        if (isset($_POST['update'])) {
          if (trim($_POST['question']) && trim($_POST['answer'])) {
            $FAQ->updateQuestion();
          }
        }
        break;
      case 'faqseq':
        $FAQ->orderQueSequence();
        break;
      case 'faqdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $FAQ->deleteQuestions();
        }
        break;
      case 'faqreset':
        $FAQ->resetCounts();
        break;
      case 'faqstate':
        $FAQ->enableDisableQuestions();
        break;
      case 'faqimport':
      case 'faqimport-upload':
        switch($_GET['ajax']) {
          case 'faqimport':
            $FAQ->batchImportQuestions();
            break;
          case 'faqimport-upload':
            $path = PATH . 'export/faqimport.csv';
            if (file_exists($path)) {
              @unlink($path);
            }
            if ($MSUPL->isUploaded($_FILES['file']['tmp_name'])) {
              $_SESSION['upload'] = array(
                'file' => $path
              );
              $MSUPL->moveFile($_FILES['file']['tmp_name'], $path);
              // Get count of lines to import..
              if (file_exists($path)) {
                if ($_FILES['file']['size'] < CSV_COUNT_MAX_LINES) {
                  $improws = count(file($path, FILE_SKIP_EMPTY_LINES));
                }
              } else {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : 'N/A'), $msadminlang3_1[7])
                );
                echo $JSON->encode($json);
                exit;
              }
              if (file_exists($_FILES['file']['tmp_name'])) {
                @unlink($_FILES['file']['tmp_name']);
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : 'N/A'), $msadminlang3_1[7])
              );
              echo $JSON->encode($json);
              exit;
            }
            break;
        }
        break;
      // Attachments..
      case 'faqattach':
        include_once(REL_PATH . 'control/classes/class.download.php');
        $MSDL = new msDownload();
        if (isset($_POST['process'])) {
          $arows = $FAQ->addAttachments($MSDL, $MSUPL);
          if ($arows == 0) {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1faq[15]
            );
            echo $JSON->encode($json);
            exit;
          }
          $json['msg'] = 'reload';
          echo $JSON->encode($json);
          exit;
        }
        if (isset($_POST['update'])) {
          $ret = $FAQ->updateAttachment($MSUPL);
          $json['msg'] = ($ret == 'no' ? 'ok' : 'reload');
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'faqattachseq':
        $FAQ->orderAttSequence();
        break;
      case 'faqattachdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $FAQ->deleteAttachments();
        }
        break;
      case 'faqattachstate':
        $FAQ->enableDisableAtt();
        break;
    }
    $json = array(
      'msg' => 'ok',
      'delconfirm' => (isset($rows) ? $rows : '0'),
      'importrows' => ($improws > 0 ? @number_format($improws) : '0')
    );
    break;

  //=========================
  // Imap Accounts
  //=========================

  case 'imap':
  case 'imdel':
  case 'imstate':
  case 'imfolders':
  case 'imspam':
    include_once(PATH . 'control/classes/class.imap.php');
    $MSIMAP = new imap();
    switch($_GET['ajax']) {
      case 'imap':
        if (isset($_POST['process'])) {
          if (trim($_POST['im_host'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswRowCount('imap') + 1) > RESTR_IMAP) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_IMAP,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $MSIMAP->addImapAccount();
          }
        }
        if (isset($_POST['update'])) {
          if (trim($_POST['im_host'])) {
            $MSIMAP->editImapAccount();
          }
        }
        break;
      case 'imdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSIMAP->deleteImapAccounts();
        }
        break;
      case 'imstate':
        $MSIMAP->enableDisable();
        break;
      case 'imspam':
        $MSIMAP->updateB8();
        break;
      case 'imfolders':
        $html   = '';
        $msg    = $msadminlang3_1[3];
        $action = 'err';
        if (function_exists('imap_open')) {
          $host = ($_POST['host'] ? mswCleanData($_POST['host']) : 'xx');
          $port = ($_POST['port'] ? mswCleanData($_POST['port']) : '1');
          $flag = ($_POST['flags'] ? mswCleanData($_POST['flags']) : '');
          $user = mswCleanData($_POST['user']);
          $pass = mswCleanData($_POST['pass']);
          $mbox = @imap_open('{' . $host . ':' . $port . '/imap' . $flag . '}', $user, $pass);
          if ($mbox) {
            $list = @imap_list($mbox, '{' . $host . '}', '*');
            if (is_array($list)) {
              sort($list);
              $html = '<option value="0">' . $msg_imap26 . '</option>';
              foreach ($list AS $box) {
                $box   = str_replace('{' . $host . '}', '', imap_utf7_decode($box));
                $html .= '<option value="' . $box . '">' . $box . '</option>';
              }
              $action = 'ok';
            } else {
              $msg = $msg_script_action2;
            }
            @imap_close($mbox);
            @imap_errors();
            @imap_alerts();
            if (imap_last_error()) {
              $msg = imap_last_error();
            }
          } else {
            // Mask errors to prevent callback failure..
            @imap_errors();
            @imap_alerts();
            if (imap_last_error()) {
              $msg = imap_last_error();
            } else {
              $msg = $msg_script_action2;
            }
          }
        } else {
          $msg = $msadminlang3_1[5];
        }
        echo $JSON->encode(array(
          'msg' => $action,
          'info' => $msg,
          'sys' => $msadminlang3_1[2],
          'html' => trim($html)
        ));
        exit;
        break;
    }
    $json = array(
      'msg' => 'ok',
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Standard Responses
  //=========================

  case 'response':
  case 'srseq':
  case 'srdel':
  case 'srstate':
  case 'srimport-upload':
  case 'srimport':
    include_once(PATH . 'control/classes/class.responses.php');
    $MSSTR           = new standardResponses();
    $MSSTR->settings = $SETTINGS;
    $improws         = 0;
    switch($_GET['ajax']) {
      case 'response':
        if (isset($_POST['process'])) {
          if (trim($_POST['title']) && trim($_POST['answer'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswRowCount('responses') + 1) > RESTR_RESPONSES) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_RESPONSES,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $MSSTR->addResponse();
          }
        }
        if (isset($_POST['update'])) {
          if (trim($_POST['title']) && trim($_POST['answer'])) {
            $MSSTR->updateResponse();
          }
        }
        break;
      case 'srimport':
      case 'srimport-upload':
        switch($_GET['ajax']) {
          case 'srimport':
            $MSSTR->batchImportSR();
            break;
          case 'srimport-upload':
            $path = PATH . 'export/srimport.csv';
            if (file_exists($path)) {
              @unlink($path);
            }
            if ($MSUPL->isUploaded($_FILES['file']['tmp_name'])) {
              $_SESSION['upload'] = array(
                'file' => $path
              );
              $MSUPL->moveFile($_FILES['file']['tmp_name'], $path);
              // Get count of lines to import..
              if (file_exists($path)) {
                if ($_FILES['file']['size'] < CSV_COUNT_MAX_LINES) {
                  $improws = count(file($path, FILE_SKIP_EMPTY_LINES));
                }
              } else {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : 'N/A'), $msadminlang3_1[7])
                );
                echo $JSON->encode($json);
                exit;
              }
              if (file_exists($_FILES['file']['tmp_name'])) {
                @unlink($_FILES['file']['tmp_name']);
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : 'N/A'), $msadminlang3_1[7])
              );
              echo $JSON->encode($json);
              exit;
            }
            break;
        }
        break;
      case 'srseq':
        $MSSTR->orderSequence();
        break;
      case 'srdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSSTR->deleteResponses();
        }
        break;
      case 'srstate':
        $MSSTR->enableDisable();
        break;
    }
    $json = array(
      'msg' => 'ok',
      'delconfirm' => (isset($rows) ? $rows : '0'),
      'importrows' => ($improws > 0 ? @number_format($improws) : '0')
    );
    break;

  //=========================
  // Custom Pages
  //=========================

  case 'pages':
  case 'pgseq':
  case 'pgdel':
  case 'pgstate':
    include_once(PATH . 'control/classes/class.pages.php');
    $MSPGS           = new csPages();
    $MSPGS->settings = $SETTINGS;
    $improws         = 0;
    switch($_GET['ajax']) {
      case 'pages':
        if (isset($_POST['process'])) {
          if (trim($_POST['title']) && trim($_POST['information'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswRowCount('pages') + 1) > RESTR_PAGES) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_PAGES,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $MSPGS->addPage();
          }
        }
        if (isset($_POST['update'])) {
          if (trim($_POST['title']) && trim($_POST['information'])) {
            $MSPGS->updatePage();
          }
        }
        break;
      case 'pgseq':
        $MSPGS->orderSequence();
        break;
      case 'pgdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSPGS->deletePages();
        }
        break;
      case 'pgstate':
        $MSPGS->enableDisable();
        break;
    }
    $json = array(
      'msg' => 'ok',
      'delconfirm' => (isset($rows) ? $rows : '0'),
      'importrows' => ($improws > 0 ? @number_format($improws) : '0')
    );
    break;

  //=========================
  // Accounts
  //=========================

  case 'accounts':
  case 'accdel':
  case 'accstate':
  case 'accimp-upload':
  case 'accimp':
  case 'accexp':
    // Include relevant classes..
    include_once(PATH . 'control/classes/class.accounts.php');
    $MSACC             = new accounts();
    $MSACC->settings   = $SETTINGS;
    $MSACC->timezones  = $timezones;
    $improws           = 0;
    switch($_GET['ajax']) {
      case 'accounts':
        if (isset($_POST['process'])) {
          if (trim($_POST['name']) && mswIsValidEmail($_POST['email'])) {
            if ($MSACC->check(strtolower($_POST['email'])) == 'exists') {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[1]
              );
            } else {
              if ($_POST['userPass'] == '') {
                $_POST['userPass']  = $MSACC->ms_generate();
              }
              $MSACC->add();
              // Send welcome email?
              if (isset($_POST['welcome'])) {
                // Message tags..
                $MSMAIL->addTag('{NAME}', $_POST['name']);
                $MSMAIL->addTag('{EMAIL}', $_POST['email']);
                $MSMAIL->addTag('{PASSWORD}', $_POST['userPass']);
                // Send..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $_POST['email'],
                  'to_name' => $_POST['name'],
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['add']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => LANG_PATH . 'admin-add-account.txt'
                ));
              }
              $json = array(
                'msg' => 'ok'
              );
            }
          } else {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[0]
            );
          }
        }
        if (isset($_POST['update'])) {
          if (trim($_POST['name']) && mswIsValidEmail($_POST['email'])) {
            if ($MSACC->check(strtolower($_POST['email'])) == 'exists') {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[1]
              );
            } else {
              $MSACC->update();
              // Anything to move?
              if (isset($_POST['dest_email']) && mswIsValidEmail($_POST['dest_email'])) {
                $MSACC->move($_POST['old_email'], $_POST['dest_email']);
              }
              $json = array(
                'msg' => 'ok'
              );
            }
          }
        }
        break;
      case 'accimp':
      case 'accimp-upload':
        switch($_GET['ajax']) {
          case 'accimp':
            $data = $MSACC->import();
            if (!empty($data) && isset($_POST['welcome'])) {
              foreach ($data AS $k => $v) {
                // Message tags..
                $MSMAIL->addTag('{NAME}', $v[0]);
                $MSMAIL->addTag('{EMAIL}', $v[1]);
                $MSMAIL->addTag('{PASSWORD}', $v[2]);
                // Send..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $v[1],
                  'to_name' => $v[0],
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['add']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => LANG_PATH . 'admin-add-account.txt',
                  'language' => $SETTINGS->language,
                  'alive' => 'yes'
                ));
              }
              $MSMAIL->smtpClose();
            }
            $json = array(
              'msg' => 'ok'
            );
            break;
          case 'accimp-upload':
            $path = PATH . 'export/accimport.csv';
            if (file_exists($path)) {
              @unlink($path);
            }
            if ($MSUPL->isUploaded($_FILES['file']['tmp_name'])) {
              $_SESSION['upload'] = array(
                'file' => $path
              );
              $MSUPL->moveFile($_FILES['file']['tmp_name'], $path);
              // Get count of lines to import..
              if (file_exists($path)) {
                if ($_FILES['file']['size'] < CSV_COUNT_MAX_LINES) {
                  $improws = count(file($path, FILE_SKIP_EMPTY_LINES));
                }
                $json = array(
                  'msg' => 'ok'
                );
              } else {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : 'N/A'), $msadminlang3_1[7])
                );
                echo $JSON->encode($json);
                exit;
              }
              if (file_exists($_FILES['file']['tmp_name'])) {
                @unlink($_FILES['file']['tmp_name']);
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : 'N/A'), $msadminlang3_1[7])
              );
              echo $JSON->encode($json);
              exit;
            }
            break;
        }
        break;
      case 'accexp':
        include(REL_PATH . 'control/classes/class.download.php');
        $MSDL = new msDownload();
        $file = $MSACC->export($msg_accounts37,$msadminlang3_1[9],$MSDL);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => 'admin/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
      case 'accdel':
        if (USER_DEL_PRIV == 'yes') {
          @ini_set('memory_limit', '100M');
          @set_time_limit(0);
          $rows = $MSACC->delete($MSTICKET);
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'accstate':
        $MSACC->enable();
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'delconfirm' => (isset($rows) ? $rows : '0'),
        'importrows' => ($improws > 0 ? @number_format($improws) : '0')
      );
    }
    break;

  //=========================
  // Support Team
  //=========================

  case 'team':
  case 'tmdel':
  case 'tmstate':
  case 'tmprofile':
    switch($_GET['ajax']) {
      case 'team':
        if (isset($_POST['process'])) {
          if (trim($_POST['name']) && mswIsValidEmail($_POST['email'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswRowCount('users') + 1) > RESTR_USERS) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_USERS,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            if ($MSUSERS->check(strtolower($_POST['email'])) == 'exists') {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[1]
              );
            } else {
              if ($_POST['accpass'] == '') {
                $_POST['accpass'] = $MSACC->ms_generate();
              }
              $MSUSERS->add();
              // Send mail..
              if (isset($_POST['welcome'])) {
                // Message tags..
                $MSMAIL->addTag('{NAME}', mswCleanData($_POST['name']));
                $MSMAIL->addTag('{EMAIL}', $_POST['email']);
                $MSMAIL->addTag('{PASSWORD}', $_POST['accpass']);
                // Send..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => mswCleanData($SETTINGS->website),
                  'to_email' => $_POST['email'],
                  'to_name' => $_POST['name'],
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['team-account']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => LANG_PATH . 'admin-new-team.txt',
                  'language' => $SETTINGS->language
                ));
              }
              $json = array(
                'msg' => 'ok'
              );
            }
          } else {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[0]
            );
          }
        }
        if (isset($_POST['update'])) {
          if (trim($_POST['name']) && mswIsValidEmail($_POST['email'])) {
            if ($MSUSERS->check(strtolower($_POST['email'])) == 'exists') {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[1]
              );
            } else {
              // Check edit for global user..
              if ($_POST['update'] == '1' && $MSTEAM->id != '1') {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => $msadminlang3_1[3]
                );
                echo $JSON->encode($json);
                exit;
              }
              $MSUSERS->update($MSTEAM->id);
              $json = array(
                'msg' => 'ok'
              );
            }
          }
        }
        break;
      case 'tmprofile':
        if (trim($_POST['name']) && mswIsValidEmail($_POST['email'])) {
          if ($MSUSERS->check(strtolower($_POST['email'])) == 'exists') {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[1]
            );
          } else {
            $urows = $MSUSERS->profile($MSTEAM);
            $json = array(
              'msg' => 'ok'
            );
          }
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1[0]
          );
        }
        break;
      case 'tmdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSUSERS->delete();
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tmstate':
        $MSUSERS->enable();
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'delconfirm' => (isset($rows) ? $rows : '0')
      );
    }
    break;

  //=========================
  // Custom Fields
  //=========================

  case 'fields':
  case 'fldseq':
  case 'flddel':
  case 'fldstate':
    include_once(PATH . 'control/classes/class.fields.php');
    $MSFIELDS = new fields();
    switch($_GET['ajax']) {
      case 'fields':
        if (isset($_POST['process'])) {
          if (isset($_POST['fieldInstructions']) && trim($_POST['fieldInstructions'])) {
            //if (LICENCE_VER == 'locked') {
            //  if ((mswRowCount('cusfields') + 1) > RESTR_FIELDS) {
            //    $json = array(
            //      'msg' => 'err',
            //      'info' => 'Free version restriction. Max allowed: ' . RESTR_FIELDS,
            //      'sys' => $msadminlang3_1[2]
            //    );
            //    echo $JSON->encode($json);
            //    exit;
            //  }
            //}
            $MSFIELDS->addCustomField();
          }
        }
        if (isset($_POST['update'])) {
          if (isset($_POST['fieldInstructions']) && trim($_POST['fieldInstructions'])) {
            $MSFIELDS->editCustomField();
          }
        }
        break;
      case 'fldseq':
        $MSFIELDS->orderSequence();
        break;
      case 'flddel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSFIELDS->deleteCustomFields();
        }
        break;
      case 'fldstate':
        $MSFIELDS->enableDisable();
        break;
    }
    $json = array(
      'msg' => 'ok',
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Priority levels
  //=========================

  case 'levels':
  case 'levseq':
  case 'levdel':
    include_once(PATH . 'control/classes/class.levels.php');
    $MSLVL = new levels();
    switch($_GET['ajax']) {
      case 'levels':
        if (isset($_POST['process'])) {
          if (isset($_POST['name']) && trim($_POST['name'])) {
            $MSLVL->addLevel();
          }
        }
        if (isset($_POST['update'])) {
          if (isset($_POST['name']) && trim($_POST['name'])) {
            $MSLVL->updateLevel();
          }
        }
        break;
      case 'levseq':
        $MSLVL->orderSequence();
        break;
      case 'levdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSLVL->deleteLevels();
        }
        break;
    }
    $json = array(
      'msg' => 'ok',
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Department
  //=========================

  case 'dept':
  case 'deptseq':
  case 'depdel':
    include_once(PATH . 'control/classes/class.departments.php');
    $MSDEPT = new departments();
    switch($_GET['ajax']) {
      case 'dept':
        if (isset($_POST['process'])) {
          if (isset($_POST['name']) && trim($_POST['name'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswRowCount('departments') + 1) > RESTR_DEPTS) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_DEPTS,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $MSDEPT->add($MSTEAM->id);
          }
        }
        if (isset($_POST['update'])) {
          if (isset($_POST['name']) && trim($_POST['name'])) {
            $MSDEPT->update();
          }
        }
        break;
      case 'deptseq':
        $MSDEPT->order();
        break;
      case 'depdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSDEPT->delete();
        }
        break;
    }
    $json = array(
      'msg' => 'ok',
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Settings / Tools
  //=========================

  case 'tlsettings':
  case 'tlpurge':
  case 'tlendis':
  case 'tlreset':
    switch ($_GET['ajax']) {
      case 'tlsettings':
        $MSSET->upload = $MSUPL;
        $MSSET->updateSettings();
        $json = array(
          'msg' => 'ok'
        );
        break;
      case 'tlpurge':
        if (isset($_POST['type'])) {
          switch($_POST['type']) {
            case 'tickets':
              if (USER_DEL_PRIV == 'yes' || $MSTEAM->id == '1') {
                if (isset($_POST['days1']) && (int) $_POST['days1'] > 0 && !empty($_POST['dept1'])) {
                  $counts = $MSTICKET->purgeTickets();
                  $json = array(
                    'msg' => 'ok-tools',
                    'report' => str_replace(array('{count1}', '{count2}', '{count3}'),array($counts[0], $counts[1], $counts[2]), $msg_tools8),
                    'sys' => $msadminlang3_1[18]
                  );
                }
              }
              break;
            case 'attachments':
              if (USER_DEL_PRIV == 'yes' || $MSTEAM->id == '1') {
                if (isset($_POST['days2']) && (int) $_POST['days2'] > 0 && !empty($_POST['dept2'])) {
                  $counts = $MSTICKET->purgeAttachments();
                  $json = array(
                    'msg' => 'ok-tools',
                    'report' => str_replace('{count}', $count, $msg_tools9),
                    'sys' => $msadminlang3_1[18]
                  );
                }
              }
              break;
            case 'accounts':
              if (USER_DEL_PRIV == 'yes' || $MSTEAM->id == '1') {
                if (isset($_POST['days3']) && (int) $_POST['days3'] > 0) {
                  $data  = $MSPTL->purgeAccounts();
                  $count = count($data);
                  if ($count > 0 && isset($_POST['mail'])) {
                    foreach ($data AS $k => $v) {
                      $pLang = $SETTINGS->language;
                      $mailT = LANG_BASE_PATH . $SETTINGS->language . '/mail-templates/account-deleted.txt';
                      if ($v['lang'] && file_exists(LANG_BASE_PATH . $v['lang'] . '/mail-templates/account-deleted.txt')) {
                        $mailT = LANG_BASE_PATH . $v['lang'] . '/mail-templates/account-deleted.txt';
                        $pLang = $v['lang'];
                      }
                      $MSMAIL->addTag('{NAME}', $v['name']);
                      $MSMAIL->sendMSMail(array(
                        'from_email' => $SETTINGS->email,
                        'from_name' => $SETTINGS->website,
                        'to_email' => $v['email'],
                        'to_name' => $v['name'],
                        'subject' => str_replace(array(
                          '{website}'
                        ), array(
                          $SETTINGS->website
                        ), $emailSubjects['acc-deletion']),
                        'replyto' => array(
                          'name' => $SETTINGS->website,
                          'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                        ),
                        'template' => $mailT,
                        'language' => $pLang,
                        'alive' => 'yes'
                      ));
                    }
                    $MSMAIL->smtpClose();
                  }
                }
                $json = array(
                  'msg' => 'ok-tools',
                  'report' => str_replace('{count}', $count, $msg_tools25),
                  'sys' => $msadminlang3_1[18]
                );
              }
              break;
          }
        }
        break;
      case 'tlendis':
        if (!empty($_POST['tbls']) && in_array($_POST['action'], array('enable','disable'))) {
          $MSSET->batchEnableDisable($batchEnDisFields);
          $json = array(
            'msg' => 'ok'
          );
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1[17]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tlreset':
        if ($MSTEAM->id == '1') {
          $cnt = array(
            0,
            0
          );
          // Account visitors..
          if (isset($_POST['visitors'])) {
            $qA = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email`,`language` FROM `" . DB_PREFIX . "portal`
                  " . (!isset($_POST['disabled']) ? 'WHERE `enabled` = \'yes\'' : '') . "
                  GROUP BY `email`
                  ORDER BY `name`
                  ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            while ($ACC = mysqli_fetch_object($qA)) {
              $pLang = '';
              if ($ACC->language && file_exists(LANG_BASE_PATH . $ACC->language . '/mail-templates/html-wrapper.html')) {
                $pLang = $ACC->language;
              }
              // New password..
              $newPass = $MSACC->ms_password($ACC->email, $MSACC->ms_generate());
              // Send email..
              if (isset($_POST['sendmail'])) {
                $MSMAIL->addTag('{NAME}', $ACC->name);
                $MSMAIL->addTag('{EMAIL}', $ACC->email);
                $MSMAIL->addTag('{PASS}', $newPass);
                $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $ACC->email,
                  'to_name' => $ACC->name,
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['reset']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $_POST['message'],
                  'language' => ($pLang ? $pLang : $SETTINGS->language),
                  'alive' => 'yes'
                ));
              }
            }
            $cnt[0] = mysqli_num_rows($qA);
          }
          // Support team..
          if (isset($_POST['team'])) {
            $qU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "users`
                  WHERE `id` > 1
                  " . (!isset($_POST['disabled']) ? 'AND `enabled` = \'yes\'' : '') . "
                  GROUP BY `email`
                  ORDER BY `name`
                  ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            while ($USR = mysqli_fetch_object($qU)) {
              // New password..
              $newPass = $MSUSERS->password($USR->id, $MSACC->ms_generate());
              // Send email..
              if (isset($_POST['sendmail'])) {
                $MSMAIL->addTag('{NAME}', $USR->name);
                $MSMAIL->addTag('{EMAIL}', $USR->email);
                $MSMAIL->addTag('{PASS}', $newPass);
                $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath . '/' . $SETTINGS->afolder);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $USR->email,
                  'to_name' => $USR->name,
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['reset']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $_POST['message'],
                  'language' => $SETTINGS->language,
                  'alive' => 'yes'
                ));
              }
            }
            $MSMAIL->smtpClose();
            $cnt[1] = mysqli_num_rows($qU);
          }
          $json = array(
            'msg' => 'ok-tools',
            'report' => str_replace(array('{count}', '{count2}'),array(@number_format($cnt[0]), @number_format($cnt[1])), $msg_tools18),
            'sys' => $msadminlang3_1[19]
          );
        }
        break;
    }
    break;

  //===========================
  // Entry Log
  //===========================

  case 'logdel':
  case 'logclr':
  case 'log':
    switch($_GET['ajax']) {
      case 'logdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSSET->deleteLogs();
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'logclr':
        if (USER_DEL_PRIV == 'yes') {
          $MSSET->clearLogFile();
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'log':
        include(REL_PATH . 'control/classes/class.download.php');
        $MSDL = new msDownload();
        $file = $MSSET->exportLogFile($MSDL);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => 'admin/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'delconfirm' => (isset($rows) ? $rows : '0')
      );
    }
    break;

  //===========================
  // Backup
  //===========================

  case 'backup':
    include(REL_PATH . 'control/classes/class.backup.php');
    if (!is_writeable(REL_PATH . 'backups') || !is_dir(REL_PATH . 'backups')) {
      $json = array(
        'msg' => 'err',
        'sys' => $msadminlang3_1[2],
        'info' => str_replace('{path}', REL_PATH . 'backups', $msadminlang3_1backup[0])
      );
    } else {
      $time     = date('H:i:s', $MSDT->mswTimeStamp());
      $download = (isset($_POST['download']) ? 'yes' : 'no');
      $compress = (isset($_POST['compress']) ? 'yes' : 'no');
      // Force download if off and no emails..
      if ($download == 'no' && $_POST['emails'] == '') {
        $download = 'yes';
      }
      // File path..
      if ($compress == 'yes') {
        $filepath = REL_PATH . 'backups/' . $msg_script33 . '-' . date('dMY', $MSDT->mswTimeStamp()) . '-' . date('His', $MSDT->mswTimeStamp()) . '.gz';
      } else {
        $filepath = REL_PATH . 'backups/' . $msg_script33 . '-' . date('dMY', $MSDT->mswTimeStamp()) . '-' . date('His', $MSDT->mswTimeStamp()) . '.sql';
      }
      // Save backup..
      $BACKUP           = new dbBackup($filepath, ($compress == 'yes' ? true : false));
      $BACKUP->settings = $SETTINGS;
      $BACKUP->doDump();
      // Copy email addresses if set..
      if (trim($_POST['emails']) && file_exists($filepath)) {
        // Update backup emails..
        $MSSET->updateBackupEmails();
        // Check how many emails we have..
        $emails = array();
        if (strpos($_POST['emails'], ',') !== false) {
          $emails = array_map('trim', explode(',', $_POST['emails']));
        } else {
          $emails[] = $_POST['emails'];
        }
        // Message tags..
        $MSMAIL->addTag('{HELPDESK}', mswCleanData($SETTINGS->website));
        $MSMAIL->addTag('{DATE_TIME}', $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->timeformat));
        $MSMAIL->addTag('{VERSION}', SCRIPT_VERSION);
        $MSMAIL->addTag('{FILE}', basename($filepath));
        $MSMAIL->addTag('{SCRIPT}', SCRIPT_NAME);
        $MSMAIL->addTag('{SIZE}', mswFileSizeConversion(@filesize($filepath)));
        // Send emails..
        foreach ($emails AS $recipient) {
          $MSMAIL->attachments[$filepath] = basename($filepath);
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $recipient,
            'to_name' => $recipient,
            'subject' => str_replace(array(
              '{website}',
              '{date}',
              '{time}'
            ), array(
              $SETTINGS->website,
              $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->dateformat),
              $time
            ), $emailSubjects['db-backup']),
            'replyto' => array(
              'name' => $SETTINGS->website,
              'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => LANG_PATH . 'backup.txt',
            'language' => $SETTINGS->language,
            'alive' => 'yes'
          ));
        }
        $MSMAIL->smtpClose();
      }
      // Download file if applicable..
      if ($download == 'yes' && file_exists($filepath)) {
        $json = array(
          'msg' => 'ok-dl',
          'file' => 'backups/' . basename($filepath),
          'type' => 'text/plain'
        );
      } else {
        // Clear file from server..
        if (file_exists($filepath)) {
          @unlink($filepath);
        }
        $json = array(
          'msg' => 'ok'
        );
      }
    }
    break;

  //===========================
  // Report
  //===========================

  case 'report':
    include(REL_PATH . 'control/classes/class.download.php');
    $MSDL = new msDownload();
    $file = $MSSET->exportReportCSV($MSDL);
    switch($file) {
      case 'err':
        $json = array(
          'msg' => 'err',
          'sys' => $msadminlang3_1[2],
          'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
        );
        break;
      case 'none':
        $json = array(
          'msg' => 'err',
          'sys' => $msadminlang3_1[2],
          'info' => $msadminlang3_1[8]
        );
        break;
      default:
        $json = array(
          'msg' => 'ok-dl',
          'file' => 'admin/export/' . basename($file),
          'type' => 'text/csv'
        );
        break;
    }
    break;

  //===========================
  // Password generator..
  //===========================

  case 'passgen':
    $pass = $MSACC->ms_generate();
    $json = array(
      'pass' => $pass
    );
    break;

  //=============================
  // Dispute account search..
  //=============================

  case 'dispute-users':
    $searched = $MSTICKET->searchDisputeUsers();
    if (empty($searched)) {
      $json = array(
        'text' => $msg_viewticket117
      );
    } else {
      $json = $searched;
    }
    break;

  //======================
  // Mail Test
  //======================

  case 'mailtest':
    include(REL_PATH . 'control/mail-data.php');
    $cnt    = 0;
    $others = '';
    if (isset($_POST['emails'])) {
      $list = array_map('trim', explode(',', $_POST['emails']));
      if (!empty($list)) {
        $cnt   = count($list);
        $first = $list[0];
        unset($list[0]);
        if (!empty($list)) {
          $others = implode(',', $list);
        }
        // Send test..
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $first,
          'to_name' => $SETTINGS->website,
          'subject' => str_replace(array(
            '{website}'
          ), array(
            $SETTINGS->website
          ), $emailSubjects['test-message']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => str_replace('{website}', $SETTINGS->website, $msg_script_action10),
          'language' => $SETTINGS->language,
          'add-emails' => $others
        ));
      }
    }
    $json = array(
      'msg' => str_replace('{count}', $cnt, $msg_script_action9)
    );
    break;

  //==================
  // Login
  //==================

  case 'login':
    if (isset($_POST['user'],$_POST['pass']) && $_POST['user'] && $_POST['pass']) {
      if (!mswIsValidEmail($_POST['user'])) {
        $json = array(
          'msg' => 'err',
          'info' => $msg_login6
        );
      } else {
        $USER = mswGetTableData('users', 'email', mswSafeImportString($_POST['user']), 'AND `accpass` = \'' . mswEncrypt(SECRET_KEY . $_POST['pass']) . '\'');
        // Fallback for md5..
        if (!isset($USER->email)) {
          $USER = mswGetTableData('users', 'email', mswSafeImportString($_POST['user']), 'AND `accpass` = \'' . md5(SECRET_KEY . $_POST['pass']) . '\'');
        }
        if (isset($USER->email)) {
          $json['msg'] = 'ok';
          // Update page access..
          if ($USER->id > 0) {
            $upa              = userAccessPages($USER->id);
            $USER->pageAccess = $upa;
          }
          // Add entry log..
          if ($USER->enableLog == 'yes') {
            $MSUSERS->log($USER);
          }
          // Set session..
          $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'] = $USER->email;
          $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key']  = $USER->accpass;
          // Set cookie..
          if (isset($_POST['cookie']) && COOKIE_NAME) {
            if ((COOKIE_SSL && mswDetectSSLConnection() == 'yes') || !COOKIE_SSL) {
              @setcookie(mswEncrypt(SECRET_KEY) . '_msc_mail', $USER->email, time() + 60 * 60 * 24 * COOKIE_EXPIRY_DAYS);
              @setcookie(mswEncrypt(SECRET_KEY) . '_msc_key', $USER->accpass, time() + 60 * 60 * 24 * COOKIE_EXPIRY_DAYS);
            }
          }
          if (isset($_SESSION[mswEncrypt(SECRET_KEY) . 'thisTicket'])) {
            $thisTicket = mswReverseTicketNumber($_SESSION[mswEncrypt(SECRET_KEY) . 'thisTicket']);
            $SUPTICK    = mswGetTableData('tickets', 'id', $thisTicket);
            unset($_SESSION[mswEncrypt(SECRET_KEY) . 'thisTicket']);
            $userAccess = explode('|', $USER->pageAccess);
            if ($SUPTICK->assignedto == 'waiting' && (in_array('assign', $userAccess) || $USER->id == 1)) {
              $json['redirect'] = 'index.php?p=assign';
            } elseif ($SUPTICK->assignedto == 'waiting' && !in_array('assign', $userAccess)) {
              $json['redirect'] = 'index.php';
            } else {
              $json['redirect'] = 'index.php?p=view-' . (isset($SUPTICK->isDisputed) && $SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&id=' . $thisTicket;
            }
          } else {
            // Do we have any unread messages?
            // If yes, do we redirect to mailbox?
            if ($USER->mailbox == 'yes' && $USER->mailScreen == 'yes') {
              if (mswRowCount('mailassoc WHERE `staffID` = \'' . $USER->id . '\' AND `folder` = \'inbox\' AND `status` = \'unread\'') > 0) {
                $json['redirect'] = 'index.php?p=mailbox';
              }
            }
            $json['redirect'] = 'index.php';
          }
        } else {
          $json = array(
            'msg' => 'err',
            'info' => $msg_login4
          );
        }
      }
    }
    break;

  //==================
  // Auto Path
  //==================

  case 'autopath':
    switch ($_GET['type']) {
      case 'http':
        $svr  = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $path = 'http://' . substr($svr, 0, strpos($svr, $SETTINGS->afolder)) . 'content/' . $msg_settings128;
        break;
      default:
        $spt  = PATH;
        $path = substr($spt, 0, strpos($spt, $SETTINGS->afolder)) . 'content' . (strpos($spt, ':') !== false ? '\\' : '/') . $msg_settings128;
        break;
    }
    $json = array(
      'path' => $path
    );
    break;

  //======================
  // File Download
  //======================

  case 'fdl':
    if (isset($_GET['infp']) && isset($_GET['infpt']) && file_exists(GLOBAL_PATH . $_GET['infp'])) {
      include(REL_PATH . 'control/classes/class.download.php');
      $MSDL = new msDownload();
      $MSDL->dl(GLOBAL_PATH . $_GET['infp'], $_GET['infpt']);
      exit;
    }
    break;

  //=======================
  // Search Accounts
  //=======================

  case 'search-accounts':
    $field = (isset($_POST['ffld']) ? $_POST['ffld'] : 'name');
    $value = (isset($_POST['fval']) ? $_POST['fval'] : '');
    $email = (isset($_POST['emal']) ? $_POST['emal'] : '');
    if (in_array($field, array('name','email','dest_email')) && $value) {
      $ret = $MSPTL->searchAccounts($field, $value, $email);
      if (!empty($ret)) {
        $json = array(
          'msg' => 'ok',
          'accounts' => $ret
        );
      } else {
        $json = array(
          'msg' => 'err',
          'info' => $msadminlang3_1[25],
          'sys' => $msadminlang3_1[2]
        );
      }
    }
    break;

  //=======================
  // Auto Complete
  //=======================

  case 'auto-users':
  case 'auto-response':
  case 'auto-merge':
  case 'auto-search-acc':
    switch($_GET['ajax']) {
      case 'auto-users':
        if (isset($_GET['term'])) {
          $arr = $MSPTL->autoSearch((in_array('accounts', $userAccess) || $MSTEAM->id == '1' ? 'yes' : 'no'));
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
      case 'auto-response':
        if (isset($_GET['term'])) {
          include_once(PATH . 'control/classes/class.responses.php');
          $MSSTR           = new standardResponses();
          $MSSTR->settings = $SETTINGS;
          $arr             = $MSSTR->autoSearch();
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
      case 'auto-merge':
        if (isset($_GET['term'])) {
          $arr = $MSTICKET->mergeSearch($ticketFilterAccess,$msadminlang3_1adminviewticket[16]);
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
      case 'auto-search-acc':
        if (isset($_GET['term'])) {
          $arr = $MSPTL->searchAccountsPages($_GET['term']);
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
    }
    exit;
    break;

  //=======================
  // Version Check
  //=======================

  case 'vc':
    $html = $MSSET->mswSoftwareVersionCheck();
    echo $JSON->encode(array(
      'html' => mswNL2BR($html)
    ));
    exit;
    break;

  //=======================
  // API Key
  //=======================

  case 'api-key':
    $length = (API_KEY_LENGTH > 100 ? 100 : API_KEY_LENGTH);
    $chars  = array_merge(range(1, 9), range('A', 'Z'), array(
      '-',
      '-',
      '-'
    ));
    shuffle($chars);
    $key = '';
    for ($i = 0; $i < $length; $i++) {
      shuffle($chars);
      $key .= $chars[rand(1, 9)];
    }
    echo $JSON->encode(array(
      'key' => trim($key)
    ));
    exit;
    break;

  //=======================
  // Password Reset
  //=======================

  case 'pass-reset':
    if (defined('PASS_RESET')) {
      if (empty($_POST['id'])) {
        $json = array(
          'msg' => 'err',
          'info' => $msadminlang3_1[23],
          'sys' => $msadminlang3_1[2],
          'delconfirm' => 0
        );
        echo $JSON->encode($json);
        exit;
      }
      $ret = $MSUSERS->reset($MSACC);
      if (!empty($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
          $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name`,`email`,`email2` FROM `" . DB_PREFIX . "users`
               WHERE `id` = '{$ret[$i]['id']}'
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          while ($USERS = mysqli_fetch_object($q)) {
            $MSMAIL->addTag('{NAME}', $USERS->name);
            $MSMAIL->addTag('{EMAIL}', $USERS->email);
            $MSMAIL->addTag('{PASS}', $ret[$i]['pass']);
            // Send mail..
            $MSMAIL->sendMSMail(array(
              'from_email' => $SETTINGS->email,
              'from_name' => $SETTINGS->website,
              'to_email' => $USERS->email,
              'to_name' => $USERS->name,
              'subject' => str_replace(array(
                '{website}',
                '{user}'
              ), array(
                $SETTINGS->website,
                $USERS->name
              ), $emailSubjects['reset']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
              ),
              'template' => LANG_PATH . 'admin-pass-reset.txt',
              'language' => $SETTINGS->language,
              'alive' => 'yes',
              'add-emails' => $USERS->email2
            ));
          }
          $MSMAIL->smtpClose();
        }
      }
      $json['msg'] = 'ok';
    }
    break;

  //---------------------
  // Unread Mailbox
  //---------------------

  case 'unread-mailbox':
    $json = array(
      'cnt' => (isset($MSTEAM->id) ? mswUnreadMailbox($MSTEAM->id) : '0')
    );
    break;

}

// If we are this far, stop and parse json response..
echo $JSON->encode($json);
exit;

?>