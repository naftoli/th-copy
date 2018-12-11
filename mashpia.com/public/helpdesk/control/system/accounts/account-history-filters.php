<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !defined('HISTORY_VIEW')) {
  $HEADERS->err403();
}

// Order by filter..
unset($msg_script44['name_desc']);
unset($msg_script44['name_asc']);
$dd_FilterKeys = array_keys($msg_script44);
$orderBy       = $msg_script44;

// Priority level filter..
$dd_levelPrKeys    = $MSYS->levels('', false, true);
$dd_ticketLevelSel = $MSYS->levels('', true, false, true);
$filterBy          = $dd_ticketLevelSel;

// Department filter..
$deptFilters = $MSYS->ticketDepartments('', true);
$dd_DeptKeys = array_keys($deptFilters);
$deptFilter  = $deptFilters;

// Validation checks..
if (isset($_GET['order']) && !in_array($_GET['order'], $dd_FilterKeys)) {
  $HEADERS->err403();
}
if (isset($_GET['filter']) && !in_array($_GET['filter'], $dd_levelPrKeys)) {
  $HEADERS->err403();
}
if (isset($_GET['dept']) && !in_array($_GET['dept'], $dd_DeptKeys)) {
  $HEADERS->err403();
}

?>