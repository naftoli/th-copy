<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

define('HISTORY_VIEW', 1);

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($LI_ACC->id)) {
  header("Location:index.php?p=login");
  exit;
}

// Variables..
$pageNumbers = '';
$title       = (isset($_GET['qt']) ? $msg_portal17 : $msg_header11);
$dataCount   = $MSTICKET->ticketList(MS_PERMISSIONS, array(
  $limitvalue,
  $limit
), true);

// Pagination..
if ($dataCount > $limit) {
  define('PER_PAGE', $limit);
  $PTION       = new pagination(array($dataCount, $msg_script42, $page), $SETTINGS->scriptpath . '/?p=' . $_GET['p'] . '&amp;next=');
  $pageNumbers = $PTION->display();
}

include(PATH . 'control/header.php');

// Filters.
include(PATH . 'control/system/accounts/account-history-filters.php');

// Show..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_header11,
  $msg_header3,
  str_replace('{count}', $dataCount, $msg_public_history),
  $msg_header4,
  $msg_viewticket25,
  $msg_open36,
  $msg_open37,
  $msg_public_history2,
  $msg_public_history3,
  $msg_script45,
  $msg_search20,
  $msg_viewticket107,
  $msg_response6,
  $msg_public_history13,
  $msg_portal12
));
$tpl->assign('TICKETS', $MSTICKET->ticketList(MS_PERMISSIONS, array(
  $limitvalue,
  $limit
)));
$tpl->assign('PAGES', $pageNumbers);
$tpl->assign('IS_DISPUTED', 'no');
$tpl->assign('DD_ORDER', $orderBy);
$tpl->assign('DD_FILTERS', $filterBy);
$tpl->assign('DD_DEPT', $deptFilter);


// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-history.tpl.php');

include(PATH . 'control/footer.php');

?>