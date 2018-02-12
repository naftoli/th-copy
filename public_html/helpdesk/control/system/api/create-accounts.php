<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !defined('API_LOADER')) {
  $HEADERS->err403();
}

// Load mailer params..
include(PATH . 'control/mail-data.php');

// Ticket data array from API..
$added      = 0;
$ticketData = $MSAPI->account($read, array_keys($timezones));

// Loop data..
if (!empty($ticketData['accounts'])) {
  $countOfAccounts = count($ticketData['accounts']);
  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfAccounts . ' account(s) found in incoming data. Preparing to loop account(s)..');
  for ($i = 0; $i < $countOfAccounts; $i++) {
    $name     = trim($ticketData['accounts'][$i]['name']);
    $email    = trim($ticketData['accounts'][$i]['email']);
    $password = trim($ticketData['accounts'][$i]['password']);
    $timezone = trim($ticketData['accounts'][$i]['timezone']);
    $ip       = trim($ticketData['accounts'][$i]['ip']);
    $language = trim($ticketData['accounts'][$i]['language']);
    $notes    = trim($ticketData['accounts'][$i]['notes']);
    if ($name && $email) {
      // Does account exist?
      $LI_ACC = mswGetTableData('portal', 'email', mswSafeImportString($email));
      if (!isset($LI_ACC->id)) {
        // Create password if blank..
        if ($password == '') {
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Password was blank, so password auto created. Not shown for security.');
          $password = $MSACC->ms_generate();
        }
        // Load language email template..
        if (file_exists(PATH . 'content/language/' . $language . '/mail-templates/new-account.txt')) {
          $mailT = PATH . 'content/language/' . $language . '/mail-templates/new-account.txt';
        } else {
          $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-account.txt';
        }
        // Create account..
        $userID = $MSACC->add(array(
          'name' => $name,
          'email' => $email,
          'pass' => $password,
          'enabled' => 'yes',
          'verified' => 'yes',
          'timezone' => $timezone,
          'ip' => $ip,
          'notes' => $notes,
          'language' => $language
        ));
        if ($userID > 0) {
          ++$added;
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] New account created for name ' . $email . ' <' . $email . '>');
          $MSMAIL->addTag('{ACC_NAME}', $name);
          $MSMAIL->addTag('{ACC_EMAIL}', $email);
          $MSMAIL->addTag('{PASS}', $password);
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
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error, user could not be added to database: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        }
      } else {
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Account already exists for email ' . $email . ' (' . $LI_ACC->name . '). Account ignored.');
      }
    } else {
      $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: Name,Email are required, check data. Account ignored.');
    }
  }
  // We are done, so add response..
  if ($added > 0) {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $added . ' accounts(s) successfully created. API ops completed, finally show response');
    $MSAPI->response('OK', str_replace('{count}', $added, $msg_api3));
  } else {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No accounts created from incoming data. Check log file.');
    $MSAPI->response('ERROR', $msg_api4);
  }
  exit;
}

?>