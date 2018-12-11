<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

$ms_js_css_loader['bbcode'] = 'yes';

$CPAGE = mswGetTableData('pages', 'id', (int) $_GET['pg'], 'AND `enPage` = \'yes\'', '*');

if (!isset($CPAGE->id)) {
  $HEADERS->err403();
}

if ($CPAGE->secure == 'yes' && !isset($LI_ACC->id)) {
  $HEADERS->err403();
}

if ($CPAGE->secure == 'yes' && isset($LI_ACC->id)) {
  $chop = explode(',', $CPAGE->accounts);
  if (!in_array('all', $chop) && !in_array($LI_ACC->id, $chop)) {
    $HEADERS->err403();
  }
}

$title = mswCleanData($CPAGE->title);

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('CATEGORIES', $FAQ->menu(array(
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('TXT', array(
  $msg_adheader11,
  $msg_pkbase7
));
$tpl->assign('CPAGE', $CPAGE);
$tpl->assign('CPAGE_TXT', $MSPARSER->mswTxtParsingEngine($CPAGE->information));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/custom-page.tpl.php');

include(PATH . 'control/footer.php');

?>