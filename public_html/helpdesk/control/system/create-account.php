<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Account verification..
if (isset($_GET['va'])) {
  $code    = $_GET['va'];
  $message = '';
  $flag    = '';
  if ($code == '' || !ctype_alnum($code) || $SETTINGS->createAcc == 'no') {
    $HEADERS->err403();
  }
  // Get account..
  $A = mswGetTableData('portal', 'system1', mswSafeImportString($code));
  if (!isset($A->id)) {
    $message = $msg_public_create8;
    $flag    = 'fail';
  } else {
    if ($A->verified == 'yes') {
      $message = $msg_public_create9;
      $flag    = 'exists';
    } else {
      // Load mail params
      include(PATH . 'control/mail-data.php');
      // Activate..
      $pass = $MSACC->ms_generate();
      $rows = $MSACC->activate(array(
        'id' => $A->id,
        'pass' => $pass
      ));
      if ($rows > 0) {
        $flag  = 'ok';
        $MSMAIL->addTag('{NAME}', $A->name);
        $MSMAIL->addTag('{EMAIL}', $A->email);
        $MSMAIL->addTag('{PASS}', $pass);
        $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $A->email,
          'to_name' => $A->name,
          'subject' => str_replace(array(
            '{website}'
          ), array(
            $SETTINGS->website
          ), $emailSubjects['acc-verified']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/account-verified.txt',
          'language' => $SETTINGS->language
        ));
        // Admin notification..
        $ADMIN = mswGetTableData('users', 'id', 1);
        if ($SETTINGS->newAccNotify == 'yes' && $ADMIN->notify == 'yes') {
          $MSMAIL->addTag('{IP}', mswIPAddresses());
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $SETTINGS->email,
            'to_name' => $SETTINGS->website,
            'subject' => str_replace(array(
              '{website}'
            ), array(
              $SETTINGS->website
            ), $emailSubjects['new-acc-notify']),
            'replyto' => array(
              'name' => $A->name,
              'email' => $A->email
            ),
            'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-account-notification.txt',
            'language' => $SETTINGS->language,
            'add-emails' => $ADMIN->email2
          ));
        }
      }
      $message = str_replace('{email}', $A->email, $msg_public_create10);
    }
  }
  // Show message..
  $title = $msg_public_create7;
  include(PATH . 'control/header.php');
  $tpl = new Savant3();
  $tpl->assign('TXT', array(
    $msg_public_create7,
    $msg_public_create,
    $message,
    $msadminlangpublic[5]
  ));
  $tpl->assign('FLAG', $flag);

  // Global vars..
  include(PATH . 'control/lib/global.php');

  // Load template..
  $tpl->display('content/' . MS_TEMPLATE_SET . '/account-verification-message.tpl.php');
  include(PATH . 'control/footer.php');
  exit;
}

$title = $msg_public_create;

// Is this option enabled?
if ($SETTINGS->createAcc == 'no') {
  $HEADERS->err403();
}

include(PATH . 'control/header.php');

// Show..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_public_create,
  $msg_public_create2,
  $msg_main3,
  $msg_public_create3,
  $msg_public_create,
  $msg_public_create4,
  $msg_public_ticket9,
  $msg_public_create5,
  $msg_public_create6,
  $msg_public_create11
));
$tpl->assign('RECAPTCHA', ($SETTINGS->recaptchaPublicKey && $SETTINGS->recaptchaPrivateKey ? $MSYS->recaptcha($SETTINGS) : ''));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-create.tpl.php');

include(PATH . 'control/footer.php');

?>