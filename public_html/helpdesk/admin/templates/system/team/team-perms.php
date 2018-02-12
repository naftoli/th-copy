<?php

// SUPPORT TEAM PERMISSION GLOBALS

if (!defined('PARENT') || !isset($MSTEAM->id) || !isset($userAccess)) {
  die('Permission denied');
}

$userDeptAccess      = mswGetDepartmentAccess($MSTEAM->id);
$mswDeptFilterAccess = mswDeptFilterAccess($MSTEAM, $userDeptAccess, 'department');
$ticketFilterAccess  = mswDeptFilterAccess($MSTEAM, $userDeptAccess, 'tickets');
$ePerms              = ($MSTEAM->editperms ? unserialize($MSTEAM->editperms) : array());

define('USER_DEL_PRIV', ($MSTEAM->id == '1' ? 'yes' : $MSTEAM->delPriv));
define('USER_EDIT_T_PRIV', ($MSTEAM->id == '1' || in_array('ticket', $ePerms) ? 'yes' : 'no'));
define('USER_EDIT_R_PRIV', ($MSTEAM->id == '1' || in_array('reply', $ePerms) ? 'yes' : 'no'));

?>