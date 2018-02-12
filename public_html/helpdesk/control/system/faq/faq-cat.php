<?php

// Check var and parent load..
if (!defined('PARENT') || !defined('MS_PERMISSIONS') || $SETTINGS->kbase == 'no') {
  $HEADERS->err403();
}

// Download attachment..
if (isset($_GET['fattachment'])) {
  include(PATH . 'control/classes/class.download.php');
  $D = new msDownload();
  $D->faqAttachment((int) $_GET['fattachment'], $SETTINGS);
  exit;
}

// Check var and parent load..
if (!isset($_GET['c']) || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Security check..
mswCheckDigit($_GET['c']);

// Load category..
$CAT = mswGetTableData('categories', 'id', (int) $_GET['c'], 'AND `enCat` = \'yes\'');

// 404 if not found..
if (!isset($CAT->name)) {
  $HEADERS->err404();
}

// Is this private?
if ($CAT->private == 'yes' && !isset($LI_ACC->id)) {
  $HEADERS->err403();
}

// Variables..
$limitvalue  = $page * $SETTINGS->quePerPage - ($SETTINGS->quePerPage);
$pageNumbers = '';
$title       = $CAT->name . ' - ' . $msg_adheader17;
$dataCount   = mswRowCount('faqassign LEFT JOIN `' . DB_PREFIX . 'faq` ON `' . DB_PREFIX . 'faq`.`id` = `' . DB_PREFIX . 'faqassign`.`question`
	             WHERE `itemID` = \'' . (int) $_GET['c'] . '\' AND `desc` = \'category\' AND `' . DB_PREFIX . 'faq`.`enFaq` = \'yes\'');

// Check if sub category..
if ($CAT->subcat > 0) {
  $SUB = mswGetTableData('categories', 'id', $CAT->subcat);
  if (isset($SUB->name)) {
    // Is parent category private?
    if ($SUB->private == 'yes' && !isset($LI_ACC->id)) {
      $HEADERS->err403();
    }
    $title = mswCleanData($CAT->name) . ' (' . mswCleanData($SUB->name) . ') - ' . $msg_adheader17;
  }
}

// Pagination..
if ($dataCount > $SETTINGS->quePerPage) {
  define('PER_PAGE', $SETTINGS->quePerPage);
  $PTION       = new pagination(array($dataCount, $msg_script42, $page, 'c'), $SETTINGS->scriptpath . '/?c=' . (int) $_GET['c'] . '&amp;next=');
  $pageNumbers = $PTION->display();
}

// Header..
include(PATH . 'control/header.php');

// Template initialisation..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_header8,
  $msg_header4,
  $msg_pkbase,
  mswSafeDisplay($msadminlang3_1faq[4]),
  $msadminlang3_1faq[5],
  $msg_pkbase7,
  $msadminlang3_1faq[6],
  $msadminlang3_1faq[7]
));
$tpl->assign('SCH_TXT', $msg_header4);
$tpl->assign('CATEGORIES', $FAQ->menu(array(
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('FAQ', $FAQ->questions(array(
  'id' => $CAT->id,
  'limit' => $limitvalue,
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('PARENT', (array) $CAT);
$tpl->assign('SUB', (isset($SUB->id) ? (array) $SUB : array()));
$tpl->assign('MSDT', $MSDT);
$tpl->assign('PAGES', $pageNumbers);
$tpl->assign('COUNT', $dataCount);

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/faq-cat.tpl.php');

// Footer..
include(PATH . 'control/footer.php');

?>