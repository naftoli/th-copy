<?php

// Check var and parent load..
if (!defined('PARENT') || !isset($_GET['a']) || !defined('MS_PERMISSIONS') || $SETTINGS->kbase == 'no') {
  $HEADERS->err403();
}

// We load custom fields for this page..
$ms_js_css_loader['bbcode'] = 'yes';

define('PRINT_FRIENDLY', 1);

// Security check..
mswCheckDigit($_GET['a']);

$QUE = mswGetTableData('faq', 'id', (int) $_GET['a'], 'AND `enFaq` = \'yes\'', '*');

if (!isset($QUE->question)) {
  $HEADERS->err404();
}

// Is this private?
if ($QUE->private == 'yes' && !isset($LI_ACC->id)) {
  $HEADERS->err403();
}

// Variables..
$title = $QUE->question . ' - ' . $msg_adheader17;
$subt  = $msg_header8;
$cky   = array();

// Check for category/search params..
if (isset($_GET['c']) && (int) $_GET['c'] > 0) {
  $CAT = mswGetTableData('categories', 'id', (int) $_GET['c'], 'AND `enCat` = \'yes\'', '`id`,`name`,`subcat`');
  if (isset($CAT->name)) {
    if (isset($CAT->subcat) && $CAT->subcat > 0) {
      $SUB = mswGetTableData('categories', 'id', $CAT->subcat);
    }
  }
} else {
  // If category isn`t set in the url, is this question only in 1 category?
  $getCat = $FAQ->cat($QUE->id);
  if ($getCat > 0) {
    $CAT = mswGetTableData('categories', 'id', $getCat, 'AND `enCat` = \'yes\'', '`name`,`subcat`');
    if (isset($CAT->name)) {
      if (isset($CAT->subcat) && $CAT->subcat > 0) {
        $SUB = mswGetTableData('categories', 'id', $CAT->subcat);
      }
    }
  }
}

// Header..
include(PATH . 'control/header.php');

// Cookie set..
if (isset($_COOKIE[mswEncrypt(SECRET_KEY) . COOKIE_NAME])) {
  $cky = unserialize($_COOKIE[mswEncrypt(SECRET_KEY) . COOKIE_NAME]);
}

// Template initialisation..
$tpl = new Savant3();
$tpl->assign('CATEGORIES', $FAQ->menu(array(
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('TXT', array(
  $msg_kbase52,
  $msg_kbase54,
  $msg_pkbase18,
  $msg_pkbase,
  mswSafeDisplay($msadminlang3_1faq[4]),
  $msadminlang3_1faq[5],
  $msg_pkbase7,
  $msadminlang3_1faq[6],
  $msadminlang3_1faq[7],
  $msg_kbase51,
  $msg_header8,
  $msadminlang3_1faq[8],
  $msadminlang3_1faq[9],
  str_replace('{date}', $MSDT->mswDateTimeDisplay($QUE->ts, $SETTINGS->dateformat), $msg_pkbase11),
  $msadminlang3_1faq[11]
));
$tpl->assign('PARENT', (isset($CAT->id) ? (array) $CAT : array()));
$tpl->assign('SUB', (isset($SUB->id) ? (array) $SUB : array()));
$tpl->assign('SCH_TXT', $msg_header4);
$tpl->assign('ANSWER', (array) $QUE);
$tpl->assign('ANSWER_TXT', $MSPARSER->mswTxtParsingEngine($QUE->answer));
$tpl->assign('MSDT', $MSDT);
$tpl->assign('ATTACHMENTS', $FAQ->attachments());
$tpl->assign('FAQ_COOKIE_SET', (in_array($_GET['a'], $cky) ? 'yes' : 'no'));
$tpl->assign('STATS', $FAQ->stats($QUE->id));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/faq-question.tpl.php');

// Footer..
include(PATH . 'control/footer.php');

?>