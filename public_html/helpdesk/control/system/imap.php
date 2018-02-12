<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Initialise vars..
$pipes      = array(
  0,
  0,
  0,
  0,
  0,
  0,
  0
);
$pipeID     = (isset($_GET[$SETTINGS->imap_param]) ? (int) $_GET[$SETTINGS->imap_param] : (defined('IMAP_CRON_ID') ? IMAP_CRON_ID : ''));
$time_start = $MSDT->microtimeFloat();
$imSpamDec  = 5;

// Check imap..
if (!function_exists('imap_open')) {
  die('PHP <a href="http://php.net/manual/en/book.imap.php">imap functions</a> not found! Your server must be compiled
      with imap support for this function to run. Refer to installation instructions on PHP website or try uncommenting the following value in
	  your PHP.ini file and then reboot your server:<br><br><b>extension=php_imap.dll</b><br><br>For Cpanel and WHM setups, use EasyApache to recompile
	  with imap support.');
}

// Memory/timeouts..
if ($SETTINGS->imap_memory > 0) {
  @ini_set('memory_limit', $SETTINGS->imap_memory . 'M');
}
if ($SETTINGS->imap_timeout > 0) {
  @set_time_limit($SETTINGS->imap_timeout);
}

// Legacy check..
if ((int) $pipeID == 0 || $pipeID == 'yes' || $pipeID == '') {
  $pipeID = '1';
}

// Get imap data..
$IMDT = mswGetTableData('imap', 'id', $pipeID);

if (!isset($IMDT->id)) {
  die($pipeID . ' is an invalid imap id. Check url');
}

if ($IMDT->im_piping == 'no') {
  die('Imap account not active. Please enable in settings');
}

// Department info..
$DP = mswGetTableData('departments', 'id', $IMDT->im_dept, '', '`manual_assign`');

// Check department..
if (!isset($DP->manual_assign)) {
  die($pipeID . ' has not been assigned to any department. Update in set');
}

// Is debug enabled?
if ($SETTINGS->imap_debug == 'yes') {
  if (!is_dir(PATH . 'logs') || !is_writeable(PATH . 'logs')) {
    die('Imap debug enabled in settings, but "logs" folder either doesn`t exist or isn`t writeable. Please update.');
  }
}

// Load download class for mime types..
include(PATH . 'control/classes/class.download.php');
$DL = new msDownload();

// Load mailer params..
include(PATH . 'control/mail-data.php');

// Read mailbox and run..
include(PATH . 'control/classes/class.upload.php');
$MSUPL            = new msUpload();
$MSIMAP           = new imapRoutine($IMDT);
$MSIMAP->settings = $SETTINGS;
$MSIMAP->datetime = $MSDT;
$MSIMAP->upload   = $MSUPL;
$mailbox          = $MSIMAP->connectToMailBox();

// Load spam b8 filter if enabled..
if ($IMDT->im_spam == 'yes') {
  include(PATH . 'control/lib/b8/call_b8.php');
  if (isset($b8_err)) {
    $MSIMAP->log('B8 spam filter fatal error: ' . $b8_err);
  } else {
    if (class_exists('b8')) {
      $MSIMAP->log('B8 spam filter successfully loaded');
      define('B8_LOADED', 1);
    }
  }
}

// Log..
$MSIMAP->log('Imap account found, preparing to connect to mailbox..');
if ($mailbox) {
  $count = imap_num_msg($mailbox);
  $loop  = ($count > $IMDT->im_messages ? $IMDT->im_messages : $count);
  if ($count > 0) {
    $MSIMAP->log('Connection successful: Looping ' . $loop . ' message(s) of ' . $count . ' in reverse order (oldest first)');
  } else {
    $MSIMAP->log('Connection successful: No messages in mailbox folder: ' . $IMDT->im_name);
  }
  // Process messages in reverse order so last message is latest..
  for ($i = $loop; $i > 0; $i--) {
    // Vars initialisation for loop..
    $isSpam     = 'no';
    $spamBypass = 'no';
    $replyID    = 0;
    $attString  = array();
    $aCount     = 0;
    $message    = $MSIMAP->readMailBox($mailbox, $i);
    $MSIMAP->log('Data from mailbox: {nl}{nl}' . print_r($message, true));
    $mailSubject = array();
    $mailTemps   = array();
    $skipMessage = 'no';
    $filters     = array(
      'txt' => 'no',
      'matches' => array()
    );
    // Are skip filters enabled?
    if ($IMDT->im_spam == 'yes') {
      if ($B8_CFG->skipFilters) {
        $MSIMAP->log('Skip filters found. Checking name,email,subject and comments for matches..');
        $filters     = $MSIMAP->filters(array(
          'name' => $message['from'],
          'email' => $message['email'],
          'subject' => $MSIMAP->decodeString($message['subject']),
          'comments' => $MSBB->cleaner($MSIMAP->decodeString($message['body']))
        ), $B8_CFG->skipFilters);
        $skipMessage = $filters['txt'];
        switch ($skipMessage) {
          case 'no':
            $MSIMAP->log('No matches found, all fields have passed the skip filter check.');
            break;
        }
      }
    }
    // If name and/or email contain mailer daemon, we do nothing else..
    if ($skipMessage == 'no') {
      $name    = $message['from'];
      $email   = $message['email'];
      $subject = $MSIMAP->decodeString($message['subject']);
      $MSIMAP->log('Decoding subject:{nl}{nl}' . $subject);
      $priority = $IMDT->im_priority;
      // For comments, decode body if required and remove any bb tags..
      // BB tags should not be present in standard emails..
      $comments = $MSBB->cleaner($MSIMAP->decodeString($message['body']));
      $MSIMAP->log('Parsing comments:{nl}{nl}' . $comments);
      // Is the spam filter enabled for this account?
      if ($IMDT->im_spam == 'yes' && defined('B8_LOADED') && isset($B8_CFG->id)) {
        $MSIMAP->log('Spam - Filter enabled for imap account (' . $IMDT->id . '/' . $IMDT->im_host . ').');
        $MSIMAP->log('Spam - Accepted probability score: ' . $B8_CFG->min_dev . ' or less');
        // Check the probability for this message.
        $spamScore = @number_format($MSB8->classify(htmlentities($comments)), $imSpamDec);
        $MSIMAP->log('Spam - Checking spam probability: ' . $spamScore);
        // Accept or reject..
        if ($spamScore > $B8_CFG->min_dev) {
          $MSIMAP->log('Spam - Message to be rejected as spam');
          // Are we accepting this message?
          if ($IMDT->im_spam_purge == 'yes') {
            if ($IMDT->im_score > 0) {
              if ($spamScore >= $IMDT->im_score) {
                $MSIMAP->log('Spam - Message will be purged and ignored. "Delete Spam Messages Immediately" enabled for imap account (' . $IMDT->id . '/' . $IMDT->im_host . ') and score greater than or equal to ' . $IMDT->im_score);
                $spamBypass = 'yes';
              } else {
                $MSIMAP->log('Spam - Message accepted and will be viewable on "Spam Tickets" screen. "Delete Spam Messages Immediately" enabled for imap account (' . $IMDT->id . '/' . $IMDT->im_host . ') but score less than ' . $IMDT->im_score);
              }
            } else {
              $MSIMAP->log('Spam - Message will be purged and ignored. "Delete Spam Messages Immediately" enabled for imap account (' . $IMDT->id . '/' . $IMDT->im_host . ')');
              $spamBypass = 'yes';
            }
          } else {
            $MSIMAP->log('Spam - Message accepted and will be viewable on "Spam Tickets" screen.');
            $isSpam = 'yes';
          }
        } else {
          $MSIMAP->log('Spam - Message passed spam filters and will be allowed');
        }
        // Is learning enabled?
        if ($B8_CFG->learning == 'yes') {
          switch ($isSpam) {
            case 'yes':
              $MSB8->learn($comments, b8::SPAM);
              break;
            case 'no':
              $MSB8->learn($comments, b8::HAM);
              break;
          }
          $spamScore2 = $MSB8->classify(htmlentities($comments));
          $MSIMAP->log('Spam - Learning enabled. Probability after learning: ' . @number_format($spamScore2, $imSpamDec));
        }
      }
      // Get account info..
      $LI_ACC = mswGetTableData('portal', 'email', mswSafeImportString($email));
      // Ignore blank emails..
      if (($message['body'] != '' || !isset($LI_ACC->id)) && $spamBypass == 'no') {
        if ($isSpam == 'yes') {
          $pipes[5] = (++$pipes[5]);
          $MSIMAP->log('Due to ticket being flagged as spam, all emails will be disabled except to admin.');
        } else {
          $pipes[0] = (++$pipes[0]);
        }
        // Is this a brand new message or a reply..
        if ($message['ticketID'][0] == 'no') {
          $MSIMAP->log('Preparing to add new ticket..');
          // Is this first ticket from user email..
          if (isset($LI_ACC->id)) {
            $name   = $LI_ACC->name;
            $email  = $LI_ACC->email;
            $pass   = '';
            $userID = $LI_ACC->id;
            $MSIMAP->log('Account does exist for ' . $email);
          } else {
            $MSIMAP->log('New account to be created for email ' . $email);
            $pass = $MSACC->ms_generate();
            if (defined('IMAP_CRON_LANG') && file_exists(PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/new-account.txt')) {
              $mailT = PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/new-account.txt';
            } else {
              $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-account.txt';
            }
            // Create account..
            $userID = $MSACC->add(array(
              'name' => $name,
              'email' => $email,
              'pass' => $pass,
              'enabled' => 'yes',
              'verified' => 'yes',
              'timezone' => '',
              'ip' => '',
              'notes' => '',
              'language' => (defined('IMAP_CRON_LANG') ? IMAP_CRON_LANG : $SETTINGS->language)
            ));
            // Send email about new account..
            if ($userID > 0) {
              $MSIMAP->log('Account created successfully. ID: ' . $userID);
              // Send notification if enabled..
              if ($SETTINGS->imap_notify == 'yes') {
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
                  'language' => $SETTINGS->language,
                  'alive' => 'yes'
                ));
                $MSIMAP->log('Email sent to ' . $name . ' <' . $email . '>');
              } else {
                $MSIMAP->log('Account creation email NOT sent as this option is disabled in setting. Settings & Tools > Settings > General > Imap Settings');
              }
            } else {
              $MSIMAP->log('Fatal error, account could not be created: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            }
          }
          // Create ticket..
          $ID = $MSTICKET->add(array(
            'dept' => $IMDT->im_dept,
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
            'source' => 'imap',
            'spam' => $isSpam
          ));
          // History entry..
          $MSTICKET->historyLog($ID, str_replace(array(
            '{visitor}'
          ), array(
            $name
          ), $msg_ticket_history['new-ticket-visitor-imap']));
          // Proceed if ticket added ok..
          if ($ID > 0) {
            $MSIMAP->log('New ticket added. ID: ' . $ID);
            $mailSubject['staff'] = str_replace(array(
              '{website}',
              '{ticket}'
            ), array(
              $SETTINGS->website,
              mswTicketNumber($ID)
            ), $emailSubjects['new-ticket']);
            $mailSubject['vis']   = '[#' . mswTicketNumber($ID) . '] ' . $msg_public_ticket12;
            $mailTemps['staff']   = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-staff.txt';
            $mailTemps['admin']   = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-admin.txt';
            $mailTemps['vis']     = 'new-ticket-visitor.txt';
            if ($isSpam == 'no') {
              if ($DP->manual_assign == 'no') {
                $pipes[1] = (++$pipes[1]);
              } else {
                $pipes[4] = (++$pipes[4]);
              }
            }
            // Ticket info..
            $T = mswGetTableData('tickets', 'id', $ID);
          } else {
            $MSIMAP->log('Fatal error, ticket could not be created: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
          }
        } else {
          // Add reply..check permissions allow reply..
          $ID = $message['ticketID'][1];
          $MSIMAP->log('Check permissions before accepting reply. Ticket cannot be closed..');
          $T = mswGetTableData('tickets', 'id', $ID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `ticketStatus` != \'closed\' AND `spamFlag` = \'no\'');
          if (isset($T->id)) {
            $replyID = $MSTICKET->reply(array(
              'ticket' => $ID,
              'visitor' => $LI_ACC->id,
              'quoteBody' => '',
              'comments' => $comments,
              'repType' => 'visitor',
              'ip' => $LI_ACC->ip,
              'disID' => 0,
              'assigned' => $T->assignedto
            ));
            // History entry..
            $MSTICKET->historyLog($replyID, str_replace(array(
              '{visitor}',
              '{id}'
            ), array(
              $name,
              $replyID
            ), $msg_ticket_history['vis-reply-add-imap']));
            // Proceed if ok..
            if ($replyID > 0) {
              $MSIMAP->log('Reply successfully added. ID: ' . $replyID);
              $mailSubject['staff'] = str_replace(array(
                '{website}',
                '{ticket}'
              ), array(
                $SETTINGS->website,
                mswTicketNumber($ID)
              ), $emailSubjects['reply-notify']);
              $mailSubject['vis']   = '';
              $mailTemps['staff']   = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/ticket-reply.txt';
              $mailTemps['admin']   = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/ticket-reply.txt';
              $mailTemps['vis']     = '';
              $pipes[2]             = (++$pipes[2]);
            } else {
              $MSIMAP->log('Fatal error, reply could not added: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            }
          } else {
            $MSIMAP->log('Permission denied. Ticket is closed or was flagged as spam and hasn`t been accepted. If so, reply is not accepted');
          }
        }
        // Attachments..
        $MSIMAP->log('Check for attachments..');
        if ($IMDT->im_attach == 'yes' && isset($T->id)) {
          $attachments = $MSIMAP->readAttachments($mailbox, $i);
          $MSIMAP->log(count($attachments) . ' attachment(s) found');
          if (!empty($attachments) && LICENCE_VER == 'locked' && count($attachments) > RESTR_ATTACH) {
            $countOfBoxes = RESTR_ATTACH;
          }
          if (!empty($attachments)) {
            $restrictions = array(
              'Rename' => ucfirst($SETTINGS->rename),
              'FileTypes' => ($SETTINGS->filetypes ? $SETTINGS->filetypes : 'No Restrictions (Not recommended)'),
              'MaxSize' => ($SETTINGS->maxsize > 0 ? mswFileSizeConversion($SETTINGS->maxsize) : 'No Limits')
            );
            $MSIMAP->log('Restrictions Imposed: {nl}{nl}' . print_r($restrictions, true));
            $MSIMAP->log('Preparing to loop and check attachment(s)');
            for ($j = 0; $j < (isset($countOfBoxes) ? $countOfBoxes : count($attachments)); $j++) {
              ++$aCount;
              $MSIMAP->log('Check Attachment: ' . $attachments[$aCount]['file']);
              // Check for valid file type..
              if ($MSTICKET->type($attachments[$aCount]['file'])) {
                $n      = ($SETTINGS->rename == 'yes' ? $MSTICKET->rename($attachments[$aCount]['file'], $ID, $replyID, ($j + 1)) : $attachments[$aCount]['file']);
                // At this point we must upload the file to get file size..
                $folder = $MSIMAP->uploadEmailAttachment($n, $attachments[$aCount]['attachment']);
                // If file upload now exists, check file size..
                if ($folder && file_exists($SETTINGS->attachpath . '/' . $folder . $n)) {
                  $fSize = filesize($SETTINGS->attachpath . '/' . $folder . $n);
                  if ($fSize > 0) {
                    if (!$MSTICKET->size($fSize)) {
                      $MSIMAP->log('Size (' . mswFileSizeConversion($fSize) . ') too big and attachment ignored/deleted');
                      @unlink($SETTINGS->attachpath . '/' . $folder . $n);
                    } else {
                      // Try and determine mime type..
                      $mime = $DL->mime($attachments[$aCount]['file'], '');
                      $MSIMAP->log('Mime type determined as ' . $mime);
                      // Add attachment data to database..
                      $atID = $MSIMAP->addAttachmentToDB($ID, $replyID, $n, $fSize, $mime);
                      if ($atID > 0) {
                        $pipes[3]    = (++$pipes[3]);
                        $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID;
                        $MSIMAP->log('Attachment (' . basename($n) . ') accepted. ID: ' . $atID . ' @ ' . mswFileSizeConversion($fSize));
                      } else {
                        $MSIMAP->log('Fatal error, attachment could not be added: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
                      }
                    }
                  } else {
                    $MSIMAP->log('File size 0 bytes, ignored.');
                  }
                }
              } else {
                $MSIMAP->log('Type (' . strrchr(strtolower($attachments[$aCount]['file']), '.') . ') invalid and attachment ignored.');
              }
            }
          }
        } else {
          $MSIMAP->log('Attachments not enabled and ignored');
        }
        // Write log entry..
        if (isset($T->id)) {
          // If spam not detected, normal ticket..
          if ($isSpam == 'no') {
            // Mail tags and send emails..
            $MSMAIL->addTag('{ACC_NAME}', $name);
            $MSMAIL->addTag('{ACC_EMAIL}', $email);
            $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($subject));
            $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID));
            $MSMAIL->addTag('{DEPT}', $MSYS->department($IMDT->im_dept, $msg_script30));
            $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($priority));
            $MSMAIL->addTag('{STATUS}', $msg_showticket23);
            // Convert quoted-printable string to an 8 bit string..
            // Helps make message cleaner..
            if (function_exists('quoted_printable_decode')) {
              $comments = quoted_printable_decode($comments);
            }
            $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($comments));
            $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswDefineNewline(), $attString) : 'N/A'));
            $MSMAIL->addTag('{ID}', $ID);
            $MSMAIL->addTag('{CUSTOM}', 'N/A');
            // Send message to staff.
            // If new ticket, is manual assign off?
            $staffSend = 'no';
            $MSIMAP->log('Preparing to send emails to staff..');
            if (isset($mailTemps['staff'])) {
              if ($DP->manual_assign == 'no' && $replyID == 0) {
                $qU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2` FROM `" . DB_PREFIX . "userdepts`
                      LEFT JOIN `" . DB_PREFIX . "departments`
                      ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                      LEFT JOIN `" . DB_PREFIX . "users`
                      ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                      WHERE `deptID`  = '{$IMDT->im_dept}'
                      AND `userID`   != '1'
                      AND `notify`    = 'yes'
                      GROUP BY `email`
				      ORDER BY `" . DB_PREFIX . "users`.`name`
                      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
                $staffSend = 'yes';
              } else {
                // If reply, is ticket assigned..
                if ($replyID > 0) {
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
                  $staffSend = 'yes';
                }
              }
              // Any sending to do??
              if ($staffSend == 'yes') {
                while ($STAFF = mysqli_fetch_object($qU)) {
                  $MSMAIL->addTag('{NAME}', $STAFF->teamName);
                  $MSMAIL->sendMSMail(array(
                    'from_email' => $SETTINGS->email,
                    'from_name' => $SETTINGS->website,
                    'to_email' => $STAFF->email,
                    'to_name' => $STAFF->teamName,
                    'subject' => $mailSubject['staff'],
                    'replyto' => array(
                      'name' => $SETTINGS->website,
                      'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                    ),
                    'template' => $mailTemps['staff'],
                    'language' => $SETTINGS->language,
                    'alive' => 'yes',
                    'add-emails' => $STAFF->email2
                  ));
                  $MSIMAP->log('Email sent to ' . $STAFF->teamName . ' <' . $STAFF->email . '>');
                }
              }
            }
            // Anything to send to admin?
            $MSIMAP->log('Preparing to send to global admin staff member..');
            if (isset($mailTemps['admin'])) {
              $GLOBAL = mswGetTableData('users', 'id', 1, 'AND `notify` = \'yes\'', '`name`,`email`,`email2`');
              if (isset($GLOBAL->name)) {
                $MSMAIL->addTag('{NAME}', $GLOBAL->name);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $GLOBAL->email,
                  'to_name' => $GLOBAL->name,
                  'subject' => $mailSubject['staff'],
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $mailTemps['admin'],
                  'language' => $SETTINGS->language,
                  'alive' => 'yes',
                  'add-emails' => $GLOBAL->email2
                ));
                $MSIMAP->log('Email sent to ' . $GLOBAL->name . ' <' . $GLOBAL->email . '>');
              } else {
                $MSIMAP->log('Not sent, notifications are disabled. Enable in settings');
              }
            }
            // Anything to send to visitor?
            $MSIMAP->log('Preparing to send new ticket confirmation to visitor..');
            if (isset($mailTemps['vis'], $mailSubject['vis']) && $mailSubject['vis'] && $mailTemps['vis'] && $replyID == 0) {
              if (isset($LI_ACC->language) && file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'])) {
                $mailT = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'];
                $pLang = $LI_ACC->language;
              } else {
                if (defined('IMAP_CRON_LANG') && file_exists(PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'])) {
                  $mailT = PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'];
                  $pLang = IMAP_CRON_LANG;
                } else {
                  $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailTemps['vis'];
                }
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
                  'email' => ($IMDT->im_email ? $IMDT->im_email : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
                ),
                'template' => $mailT,
                'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
              ));
              $MSIMAP->log('Email sent to ' . $name . ' <' . $email . '>');
            }
          } else {
            // Spam notification..
            // Mail tags and send emails..
            $MSMAIL->addTag('{ACC_NAME}', $name);
            $MSMAIL->addTag('{ACC_EMAIL}', $email);
            $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($subject));
            $MSMAIL->addTag('{DEPT}', $MSYS->department($IMDT->im_dept, $msg_script30));
            $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($priority));
            $MSMAIL->addTag('{STATUS}', $msg_showticket23);
            // Convert quoted-printable string to an 8 bit string..
            // Helps make message cleaner..
            if (function_exists('quoted_printable_decode')) {
              $comments = quoted_printable_decode($comments);
            }
            $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($comments));
            $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswDefineNewline(), $attString) : 'N/A'));
            $MSMAIL->addTag('{CUSTOM}', 'N/A');
            // Anything to send to admin?
            $MSIMAP->log('Preparing to send spam notification to global admin staff member..');
            if (isset($mailTemps['admin'])) {
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
                  ), $emailSubjects['spam-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/spam-notification.txt',
                  'language' => $SETTINGS->language,
                  'add-emails' => $GLOBAL->email2
                ));
                $MSIMAP->log('Email sent to ' . $GLOBAL->name . ' <' . $GLOBAL->email . '>');
              } else {
                $MSIMAP->log('Not sent, notifications are disabled. Enable in settings');
              }
            }
          }
        }
      } else {
        $MSIMAP->log('Blank message, ignore');
      }
      // If spam filter is enabled, and message is spam, are we just deleting?
      if ($spamBypass == 'yes' && $IMDT->im_spam == 'yes' && defined('B8_LOADED')) {
        $pipes[6] = (++$pipes[6]);
        $MSIMAP->flagMessage($mailbox, $i);
      } else {
        // Are we moving message..
        if ($IMDT->im_protocol == 'imap') {
          if ($IMDT->im_move) {
            $MSIMAP->log('Move option enabled, moving ticket to ' . $IMDT->im_move);
            $MSIMAP->moveMail($mailbox, $i);
          } else {
            $MSIMAP->log('Message flagged for deletion after loop has finished');
            $MSIMAP->flagMessage($mailbox, $i);
          }
        }
      }
    } else {
      if (!empty($filters['matches'])) {
        $MSIMAP->log('Message will be deleted because skip filter matches were found. Admin > Imap Spam Filter > Skip Filters. Details to follow.');
        $MSIMAP->log(implode('{nl}', $filters['matches']));
        $MSIMAP->flagMessage($mailbox, $i);
      }
    }
  }
  // Close mailbox..closes mailbox and removes messages marked for deletion..
  $MSIMAP->closeMailbox($mailbox);
  if ($count > 0) {
    if ($IMDT->im_move) {
      $MSIMAP->log('Mailbox closed');
    } else {
      $MSIMAP->log('Mailbox closed and tickets purged');
    }
  }

  // Time calculations..
  $memory   = (function_exists('memory_get_usage') ? round(memory_get_usage() / 1048576, 2) . 'MB' : 'Unknown');
  $peak     = (function_exists('memory_get_peak_usage') ? round(memory_get_peak_usage() / 1048576, 2) . 'MB' : 'Unknown');
  $duration = round($MSDT->microtimeFloat() - $time_start, 2) . ' seconds';

  // Is cron output required..
  $done = str_replace(array(
    '{datetime}',
    '{count}',
    '{count2}',
    '{count3}',
    '{count4}',
    '{count_msg}',
    '{memory}',
    '{peak}',
    '{duration}',
    '{count5}',
    '{count6}'
  ), array(
    $MSDT->mswDateTimeDisplay(0, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay(0, $SETTINGS->timeformat),
    @number_format($pipes[1]),
    @number_format($pipes[2]),
    @number_format($pipes[3]),
    @number_format($pipes[4]),
    @number_format($pipes[0]),
    $memory,
    $peak,
    $duration,
    @number_format($pipes[5]),
    @number_format($pipes[6])
  ), $msg_piping8);
  echo $done;
  $MSIMAP->log('Operation completed. Information: {nl}{nl}' . str_replace('<br>', mswDefineNewline(), $done));
} else {
  $MSIMAP->log('Fatal error, could not connect to mailbox');
}

?>