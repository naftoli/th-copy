<?php

// Check var and parent load..
if (!defined('PARENT') || !isset($_GET['q']) || !defined('MS_PERMISSIONS') || $SETTINGS->kbase == 'no') {
  $HEADERS->err403();
}

// Load the skip words array..
include(PATH . 'control/skipwords.php');

// Variables..
$limitvalue  = $page * $SETTINGS->quePerPage - ($SETTINGS->quePerPage);
$pageNumbers = '';
$html        = '';
$title       = $msg_pkbase;
$dataCount   = 0;

// Build search query..
$SQL = '';
if ($_GET['q']) {
  $chop = array_map('trim', explode(' ', $_GET['q']));
  if (!empty($chop)) {
    foreach ($chop AS $word) {
      if (!in_array($word, $searchSkipWords)) {
        $SQL .= (!$SQL ? 'WHERE (' : 'OR (') . "`question` LIKE '%" . mswCleanData(mswSafeImportString($word)) . "%' OR `answer` LIKE '%" . mswCleanData(mswSafeImportString($word)) . "%')";
      }
    }
  }
  // Are we searching for anything..
  if ($SQL) {
    $html = $FAQ->questions(array(
      'id' => 0,
      'limit' => $limitvalue,
      'search' => array($SQL, 'no'),
      'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
    ));
    $dataCount = $FAQ->questions(array(
      'id' => 0,
      'limit' => $limitvalue,
      'search' => array($SQL, 'yes'),
      'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
    ));
  }
}

// Check for category/search params..
if (isset($_GET['c']) && (int) $_GET['c'] > 0) {
  $CAT = mswGetTableData('categories', 'id', (int) $_GET['c'], 'AND `enCat` = \'yes\'', '`id`,`name`,`subcat`');
  if (isset($CAT->name)) {
    if (isset($CAT->subcat) && $CAT->subcat > 0) {
      $SUB = mswGetTableData('categories', 'id', $CAT->subcat);
    }
  }
}

// Pagination..
if ($dataCount > $SETTINGS->quePerPage) {
  define('PER_PAGE', $SETTINGS->quePerPage);
  $PTION       = new pagination(array($dataCount, $msg_script42, $page, 'q'), $SETTINGS->scriptpath . '/?q=' . urlencode($_GET['q']) . '&amp;next=');
  $pageNumbers = $PTION->display();
}

// Header..
include(PATH . 'control/header.php');

// Template initialisation..
$tpl = new Savant3();
$tpl->assign('CATEGORIES', $FAQ->menu(array(
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('TXT', array(
  $msg_header8,
  $msg_pkbase,
  $msg_header4,
  $msg_kbase53,
  str_replace('{count}', @number_format($dataCount), $msadminlang3_1faq[12]),
  $msadminlang3_1faq[5],
  $msg_pkbase7,
  $msadminlang3_1faq[13]
));
$tpl->assign('PARENT', (isset($CAT->id) ? (array) $CAT : array()));
$tpl->assign('SUB', (isset($SUB->id) ? (array) $SUB : array()));
$tpl->assign('SCH_TXT', $msg_header4);
$tpl->assign('FAQ', $html);
$tpl->assign('RESULTS', $dataCount);
$tpl->assign('MSDT', $MSDT);
$tpl->assign('PAGES', $pageNumbers);

// Global vars..
include(PATH . 'control/lib/global.php');

// Global vars..
$tpl->display('content/' . MS_TEMPLATE_SET . '/faq-search.tpl.php');

// Footer..
include(PATH . 'control/footer.php');

?>