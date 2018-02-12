<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !defined('API_LOADER')) {
  $HEADERS->err403();
}

// Load download class for mime types..
include(PATH . 'control/classes/class.download.php');
$DL = new msDownload();

// Load mailer params..
include(PATH . 'control/mail-data.php');

// Ticket data array from API..
$added      = 0;
$ticketData = $MSAPI->ticket($read, $levelPrKeys);

// Loop data..
if (!empty($ticketData['tickets'])) {
  $countOfTickets = count($ticketData['tickets']);
  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfTickets . ' ticket(s) found in incoming data. Preparing to loop ticket(s)..');
  for ($i=0; $i < $countOfTickets; $i++) {
    $name      = trim($ticketData['tickets'][$i]['name']);
    $email     = trim($ticketData['tickets'][$i]['email']);
    $deptID    = trim($ticketData['tickets'][$i]['dept']);
    $subject   = trim($ticketData['tickets'][$i]['subject']);
    $comments  = trim($ticketData['tickets'][$i]['comments']);
    $priority  = trim($ticketData['tickets'][$i]['priority']);
    $language  = trim($ticketData['tickets'][$i]['language']);
    $attString = array();
    $pLang     = $language;
    // Add ticket..
    if ($name && $email && $deptID > 0 && $subject && $comments && $priority) {
      $DP = mswGetTableData('departments', 'id', $deptID, '', '`manual_assign`');
      if (isset($DP->manual_assign)) {
        // Does account exist?
        $LI_ACC = mswGetTableData('portal', 'email', mswSafeImportString($email));
        if (isset($LI_ACC->id)) {
          $name   = $LI_ACC->name;
          $email  = $LI_ACC->email;
          $pass   = '';
          $userID = $LI_ACC->id;
          if (file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/new-ticket-visitor.txt')) {
            $mailR = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/new-ticket-visitor.txt';
            $pLang = $LI_ACC->language;
          } else {
            $mailR = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-visitor.txt';
          }
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Account does exist for ' . $email);
        } else {
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] New account to be created for email ' . $email);
          $pass   = $MSACC->ms_generate();
          $mailT  = PATH . 'content/language/' . $language . '/mail-templates/new-account.txt';
          $mailR  = PATH . 'content/language/' . $language . '/mail-templates/new-ticket-visitor.txt';
          // Create account..
          $userID = $MSACC->add(array(
            'name' => $name,
            'email' => $email,
            'pass' => $pass,
            'enabled' => 'yes',
            'verified' => 'yes',
            'timezone' => $SETTINGS->timezone,
            'ip' => '',
            'notes' => '',
            'language' => $language
          ));
          // Send email about new account..
          if ($userID > 0) {
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Account created successfully. ID: ' . $userID);
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
              'language' => $language,
              'alive' => 'yes'
            ));
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $name . ' <' . $email . '>');
          } else {
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error, account could not be created: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
          }
        }
        // Create ticket..
        if ($userID > 0) {
          $ID = $MSTICKET->add(array(
            'dept' => $deptID,
            'assigned' => ($DP->manual_assign == 'yes' ? 'waiting' : ''),
            'visitor' => $userID,
            'subject' => $subject,
            'quoteBody' => '',
            'comments' => $comments,
            'priority' => $priority,
            'replyStatus' => 'start',
            'ticketStatus' => 'open',
            'ip' => '',
            'notes' => '',
            'disputed' => 'no',
            'source' => 'api'
          ));
          // Proceed if ticket added ok..
          if ($ID > 0) {
            ++$added;
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] New ticket added. ID: ' . $ID);
            // Add custom fields..
            if (!empty($ticketData['tickets'][$i]['fields'])) {
              $countOfFields = count($ticketData['tickets'][$i]['fields']);
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfFields . ' custom field(s) found in incoming data. Preparing to add field(s)..');
              foreach ($ticketData['tickets'][$i]['fields'] AS $fKey => $fVal) {
                $fieldID = substr($fKey, 1);
                if ((int) $fieldID > 0 && mswRowCount('cusfields WHERE `id` = \'' . (int) $fieldID . '\'') > 0) {
                  $MSAPI->insertField($ID, $fieldID, $fVal);
                  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Field (' . $fKey . ') accepted.');
                } else {
                  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Field (' . $fKey . ') ignored, field ID ' . $fieldID . ' invalid or not found.');
                }
              }
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No custom field data found.');
            }
            // Add attachments..
            if (!empty($ticketData['tickets'][$i]['attachments'])) {
              $countOfAttachments = count($ticketData['tickets'][$i]['attachments']);
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfAttachments . ' attachment(s) found in incoming data. Preparing to add attachment(s)..');
              for ($a = 0; $a < $countOfAttachments; $a++) {
                $ext    = (isset($ticketData['tickets'][$i]['attachments'][$a]['ext']) ? $ticketData['tickets'][$i]['attachments'][$a]['ext'] : '');
                $file   = (isset($ticketData['tickets'][$i]['attachments'][$a]['data']) ? $ticketData['tickets'][$i]['attachments'][$a]['data'] : '');
                $fnme   = (isset($ticketData['tickets'][$i]['attachments'][$a]['name']) ? $ticketData['tickets'][$i]['attachments'][$a]['name'] : '');
                if ($ext && $file) {
                  // The API always renames incoming files as no file name is required..
                  $n      = ($fnme ? $fnme : $MSTICKET->rename($ID . '.' . $ext, $ID, 0, ($a + 1)));
                  // At this point we must upload the file to get file size..
                  // Replace any spaces in data with + symbol to maintain incoming data modified by urldecode..
                  $folder = $MSAPI->uploadEmailAttachment($n, strtr($file, ' ', '+'));
                  if ($folder && file_exists($SETTINGS->attachpath . '/' . $folder . $n)) {
                    $fSize = filesize($SETTINGS->attachpath . '/' . $folder . $n);
                    if ($fSize > 0) {
                      if (!$MSTICKET->size($fSize)) {
                        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Size (' . mswFileSizeConversion($fSize) . ') too big and attachment ignored/deleted');
                        @unlink($SETTINGS->attachpath . '/' . $folder . $n);
                      } else {
                        // Try and determine mime type..
                        $mime = $DL->mime($SETTINGS->attachpath . '/' . $folder . $n, '');
                        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Mime type determined as ' . $mime);
                        // Add attachment data to database..
                        $atID = $MSAPI->addAttachmentToDB($ID, 0, $n, $fSize, $deptID, $mime);
                        if ($atID > 0) {
                          $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID;
                          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Attachment (' . basename($n) . ') accepted. ID: ' . $atID . ' @ ' . mswFileSizeConversion($fSize));
                        } else {
                          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error, attachment could not be added: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
                        }
                      }
                    } else {
                      $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] File size of attachment 0 bytes. Ignored. Maybe permissions or error reading file.');
                    }
                  } else {
                    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] File attachment could not be saved. Either folder (' . $folder . ') doesn`t exist, has invalid permissions or could not be created.');
                  }
                } else {
                  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] File attachment ignored, no incoming data or extension value. Check source code.');
                }
              }
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No attachments found.');
            }
            // Write log entry..
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Writing to history log if enabled.');
            $MSTICKET->historyLog($ID, str_replace(array(
              '{visitor}'
            ), array(
              $name
            ), $msg_ticket_history['new-ticket-visitor-api']));
            // Send emails..
            $MSMAIL->addTag('{ACC_NAME}', $name);
            $MSMAIL->addTag('{ACC_EMAIL}', $email);
            $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($subject));
            $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID));
            $MSMAIL->addTag('{DEPT}', $MSYS->department($deptID, $msg_script30));
            $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($priority));
            $MSMAIL->addTag('{STATUS}', $msg_showticket23);
            $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($comments));
            $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswDefineNewline(), $attString) : 'N/A'));
            $MSMAIL->addTag('{ID}', $ID);
            $MSMAIL->addTag('{CUSTOM}', $MSFIELDS->email($ID, 0));
            // Send message to support staff if manual assign is off for department..
            // This doesn`t include the global user..
            if ($DP->manual_assign == 'no') {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to send emails to staff..');
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
                $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $STAFF->teamName . ' <' . $STAFF->email . '>');
              }
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No emails sent to staff as ticket is awaiting assignment');
            }
            // Now send to global user..
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to send to global admin staff member..');
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
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $GLOBAL->name . ' <' . $GLOBAL->email . '>');
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Not sent, notifications are disabled. Enable in settings');
            }
            // Send email to visitor..
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to send new ticket confirmation to visitor..');
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
              'template' => $mailR,
              'language' => ($pLang ? $pLang : $SETTINGS->language)
            ));
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $name . ' <' . $email . '>');
          } else {
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: Ticket could not be created: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
          }
        } else {
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: User ID not found.');
        }
      } else {
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: Department not found for ID ' . $deptID . '. Ticket ignored.');
      }
    } else {
      $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: Name,Email,Dept,Subject,Comments & Priority are required, check data. Ticket ignored.');
    }
  }
  // We are done, so add response..
  if ($added > 0) {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $added . ' ticket(s) successfully created. API ops completed, finally show response');
    $MSAPI->response('OK', str_replace('{count}', $added, $msg_api));
  } else {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No tickets created from incoming data. Check log file.');
    $MSAPI->response('ERROR', $msg_api2);
  }
  exit;
}

?>