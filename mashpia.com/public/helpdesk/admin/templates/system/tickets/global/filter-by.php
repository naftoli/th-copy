<?php

//============================
// TICKET FILTER BY OPTIONS
//============================

if (!defined('PARENT')) { exit; }

$filterBy  = '';

if (isset($_GET['priority']) && in_array($_GET['priority'],$levelPrKeys)) {
  $filterBy  .= "AND `priority` = '{$_GET['priority']}'";
}
if (isset($_GET['status'])) {
  switch ($_GET['status']) {
    case 'visitor':
      $filterBy  .= "AND `replyStatus` = 'visitor'";
      break;
    case 'admin':
      $filterBy  .= "AND `replyStatus` IN('admin','start')";
      break;
    case 'start':
      $filterBy  .= "AND `replyStatus` IN('start')";
      break;
    case 'adminonly':
      $filterBy  .= "AND `replyStatus` IN('admin')";
      break;
  }
}
if (isset($_GET['dept'])) {
  if (substr($_GET['dept'], 0, 1) == 'u' && ($MSTEAM->id == '1' || in_array('assign', $userAccess))) {
    $filterBy .= "AND FIND_IN_SET('" . (int) substr($_GET['dept'], 1) . "', `assignedto`) > 0";
  } else {
    $mswDeptFilterAccess  = '';
    $filterBy .= "AND `department` = '" . (int) $_GET['dept'] . "'";
  }
}

?>