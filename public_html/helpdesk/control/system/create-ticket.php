<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

define('TICKET_CREATION', 1);
$ms_js_css_loader['textarea'] = 'yes';

include(PATH . 'control/classes/class.upload.php');
$MSUPL  = new msUpload();

// Upload dropzone..
$mSize = 0;
if ($SETTINGS->attachment == 'yes' && $SETTINGS->attachboxes > 0) {
  $ms_js_css_loader['uploader'] = 'yes';
  $mSize  = $MSUPL->getMaxSize();
  $aMax   = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes);
  $mswUploadDropzone = array(
    'ajax' => 'create-ticket',
    'multiple' => ($SETTINGS->attachboxes > 1 && $aMax > 1 ? 'true' : 'false'),
    'max-files' => $aMax,
    'max-size' => ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? $mSize : $SETTINGS->maxsize) : $mSize),
    'allowed' => ($SETTINGS->filetypes ? str_replace(array('|','.'),array(',',''),strtolower($SETTINGS->filetypes)) : '*'),
    'drag' => 'false',
    'txt' => str_replace("'", "\'", $msadminlang3_1uploads[5]),
    'div' => 'two'
  );
}

// Check log in..
if ($SETTINGS->createPref == 'yes' && MS_PERMISSIONS == 'guest') {
  $_SESSION['redirectPage'] = 'open';
  header("Location:index.php?p=login");
  exit;
}

// Load account globals..
include(PATH . 'control/system/accounts/account-global.php');

// Reset captcha if we are logged in..
if ($SETTINGS->enCapLogin == 'yes' && MS_PERMISSIONS != 'guest' && isset($LI_ACC->name)) {
  $SETTINGS->recaptchaPublicKey  = '';
  $SETTINGS->recaptchaPrivateKey = '';
}

$title = $msg_main2;

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_main2,
  $msg_main17,
  $msg_newticket3,
  $msg_newticket4,
  $msg_newticket15,
  $msg_newticket6,
  $msg_newticket8,
  $msg_newticket5,
  $msg_viewticket78,
  $msg_newticket37,
  $msg_newticket38,
  $attachRestrictions,
  $msg_main2,
  $msg_newticket43,
  $msg_viewticket101,
  $msg_public_ticket4,
  $msg_public_ticket5,
  $msg_public_ticket9,
  $msg_public_ticket10,
  $bb_code_buttons,
  $msg_public_create11,
  $msg_header3,
  $msg_add3,
  $msg_add2,
  $msg_add,
  str_replace(
    array('{max}','{files}','{types}'),
    array(
      ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? mswFileSizeConversion($mSize) : mswFileSizeConversion($SETTINGS->maxsize)) : mswFileSizeConversion($mSize)),
      (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes),
      ($SETTINGS->filetypes ? str_replace(array('|','.'),array(', ',''), $SETTINGS->filetypes) : $msadminlang3_1uploads[4])
    ),
    $msadminlang3_1uploads[3]
  ),
  $msadminlang3_1createticket[0]
));
$tpl->assign('RECAPTCHA', ($SETTINGS->recaptchaPublicKey && $SETTINGS->recaptchaPrivateKey ? $MSYS->recaptcha() : ''));
$tpl->assign('DEPARTMENTS', $MSYS->ticketDepartments());
$tpl->assign('PRIORITY_LEVELS', $ticketLevelSel);
$tpl->assign('CUS_FIELDS_COUNT', mswRowCount('cusfields WHERE `enField` = \'yes\''));
$tpl->assign('LOGGED_IN', (MS_PERMISSIONS != 'guest' && isset($LI_ACC->name) ? 'yes' : 'no'));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-create-ticket.tpl.php');

include(PATH . 'control/footer.php');

?>