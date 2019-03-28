<?php

//------------------------------------------------------------------------------
// CLOSE TICKETS
// Run As Cron
//------------------------------------------------------------------------------

if (!defined('EMAIL_DIGEST')) {
  date_default_timezone_set('UTC');
  include(dirname(__file__) . '/control/classes/class.errors.php');
  if (ERR_HANDLER_ENABLED) {
    set_error_handler('msErrorhandler');
  }
  define('PATH', dirname(__file__) . '/');
  define('PARENT', 1);
  define('CRON_RUN', 1);
  include(PATH . 'control/system/init.php');
  include(PATH . 'control/mail-data.php');
}

$tCount = 0;

//----------------------------
// Close tickets
//----------------------------

if ((int) $SETTINGS->autoClose > 0) {
  $now = $MSDT->mswTimeStamp();
  $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `visitorID`,
         `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
         `" . DB_PREFIX . "portal`.`email` AS `ticketMail`,
         `" . DB_PREFIX . "portal`.`language` AS `ticketLang`
         FROM `" . DB_PREFIX . "tickets`
         LEFT JOIN `" . DB_PREFIX . "portal`
	       ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
		     WHERE `replyStatus`                   = 'visitor'
         AND `ticketStatus`                    = 'open'
		     AND `assignedto`                     != 'waiting'
		     AND `spamFlag`                        = 'no'
         AND DATE(FROM_UNIXTIME(`" . DB_PREFIX . "tickets`.`ts`)) <= DATE_SUB(DATE(UTC_TIMESTAMP),INTERVAL " . (int) $SETTINGS->autoClose . " DAY)
		     GROUP BY `visitorID`
	       ORDER BY `visitorID`
         ");
  if ($q && mysqli_num_rows($q) > 0) {
    while ($V = mysqli_fetch_object($q)) {
      $subjects = array();
      $q2       = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `subject`,`isDisputed`,`id`,`department`,`source`
	                FROM `" . DB_PREFIX . "tickets`
                  WHERE `replyStatus`            = 'visitor'
                  AND `ticketStatus`             = 'open'
                  AND `assignedto`              != 'waiting'
                  AND `visitorID`                = '{$V->visitorID}'
                  AND `spamFlag`                 = 'no'
                  AND DATE(FROM_UNIXTIME(`ts`)) <= DATE_SUB(DATE(UTC_TIMESTAMP),INTERVAL " . (int) $SETTINGS->autoClose . " DAY)
                  ORDER BY `" . DB_PREFIX . "tickets`.`id`
                  ");
      if ($q2 && mysqli_num_rows($q2) > 0) {
        while ($T = mysqli_fetch_object($q2)) {
          // Check and close ticket..
          // Last reply must be from admin..
          $qR = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ts`,`replyType` FROM `" . DB_PREFIX . "replies`
                WHERE `ticketID` = '{$T->id}'
				        ORDER BY `id` DESC
		            ");
          $RP = mysqli_fetch_object($qR);
          // Is this ticket waiting on visitor?
          if (isset($RP->ts) && $RP->replyType == 'admin') {
            // Check time of reply..
            $f = strtotime(date('Y-m-d', $RP->ts));
            $t = strtotime(date('Y-m-d', $now));
            $c = ceil(($t - $f) / 86400);
            // Close duration expired?
            if ($c >= (int) $SETTINGS->autoClose) {
              // Close ticket and write history note..
              $rows = $MSTICKET->openclose($T->id, 'close');
              // If affected rows, actioned ok..
              if ($rows > 0) {
                ++$tCount;
                $subjects[$V->visitorID][] = array(
                  $T->id,
                  $T->isDisputed,
                  $T->department,
                  $T->source,
                  str_replace(array(
                    '{ticket}',
                    '{subject}'
                  ), array(
                    mswTicketNumber($T->id),
                    $T->subject
                  ), $msg_script56)
                );
                // History if affected rows..
                $MSTICKET->historyLog($T->id, str_replace('{days}', (int) $SETTINGS->autoClose, $msg_ticket_history['ticket-auto-close']));
              }
            }
          }
        }
        // Group and send single email..
        if (!empty($subjects[$V->visitorID]) && $SETTINGS->autoCloseMail == 'yes') {
          $ticketData = array();
          foreach ($subjects[$V->visitorID] AS $values) {
            $ticket      = $values[0];
            $dispute     = $values[1];
            $dept        = $values[2];
            $source      = $values[3];
            $data        = $values[4];
            // Check if this ticket was originally opened by imap..
            // If it was, set the reply-to address as the imap address..
            // This is so any replies sent go back to the ticket..
            $replyToAddr = '';
            if ($source == 'imap') {
              $IMD = mswGetTableData('imap', 'im_dept', $dept);
              if (isset($IMD->im_email) && $IMD->im_email) {
                $replyToAddr = $IMD->im_email;
              }
            }
            // Is this a dispute?
            // If so, send notification to other users in dispute..
            if ($SETTINGS->disputes == 'yes' && $dispute == 'yes') {
              // Get all users in this dispute..
              $ticketDisputeUsers = $MSTICKET->disputeUsers($ticket);
              if (!empty($ticketDisputeUsers)) {
                $MSMAIL->addTag('{ID}', $ticket);
                $MSMAIL->addTag('{TICKET}', rtrim($data));
                $qDU = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email`,`language` FROM `" . DB_PREFIX . "portal`
                       WHERE `id` IN(" . mswSafeImportString(implode(',', $ticketDisputeUsers)) . ")
				               GROUP BY `email`
                       ORDER BY `name`
                       ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
                while ($D_USR = mysqli_fetch_object($qDU)) {
                  $pLang = '';
                  $temp  = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/auto-close-dispute.txt';
                  // Get correct language file..
                  if (isset($D_USR->language) && file_exists(PATH . 'content/language/' . $D_USR->language . '/mail-templates/auto-close-dispute.txt')) {
                    $pLang = $D_USR->language;
                    $temp  = PATH . 'content/language/' . $D_USR->language . '/mail-templates/auto-close-dispute.txt';
                  }
                  $MSMAIL->addTag('{NAME}', $D_USR->name);
                  $MSMAIL->sendMSMail(array(
                    'from_email' => $SETTINGS->email,
                    'from_name' => $SETTINGS->website,
                    'to_email' => $D_USR->email,
                    'to_name' => $D_USR->name,
                    'subject' => str_replace(array(
                      '{website}'
                      ), array(
                      $SETTINGS->website
                      ), $emailSubjects['auto-close']),
                      'replyto' => array(
                      'name' => $SETTINGS->website,
                      'email' => ($replyToAddr ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
                    ),
                    'template' => $temp,
                    'language' => ($pLang ? $pLang : $SETTINGS->language),
                    'alive' => 'yes'
                  ));
                }
              }
            }
            // Build ticket data..
            $ticketData[] = $data;
          }
          // Send notification to visitor about ticket closures..
          // This is a single email..
          if (!empty($ticketData)) {
            $MSMAIL->addTag('{NAME}', $V->ticketName);
            $MSMAIL->addTag('{TICKETS}', rtrim(implode(mswDefineNewline() . mswDefineNewline(), $ticketData)));
            $pLang = '';
            $temp  = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/auto-close-tickets.txt';
            // Get correct language file..
            if (isset($V->ticketLang) && file_exists(PATH . 'content/language/' . $V->ticketLang . '/mail-templates/auto-close-tickets.txt')) {
              $pLang = $V->ticketLang;
              $temp  = PATH . 'content/language/' . $V->ticketLang . '/mail-templates/auto-close-tickets.txt';
            }
            $MSMAIL->sendMSMail(array(
              'from_email' => $SETTINGS->email,
              'from_name' => $SETTINGS->website,
              'to_email' => $V->ticketMail,
              'to_name' => $V->ticketName,
              'subject' => str_replace(array(
                '{website}',
                '{count}'
              ), array(
                $SETTINGS->website,
                count($ticketData)
              ), $emailSubjects['auto-close-vis']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($replyToAddr ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
              ),
              'template' => $temp,
              'language' => ($pLang ? $pLang : $SETTINGS->language),
              'alive' => 'yes'
            ));
          }
        }
      }
    }
  }
}

// Message, but only if the email digest hasn`t run as well..
if (!defined('EMAIL_DIGEST')) {
  echo str_replace('{count}', $tCount, $msg_script40);
}

?>