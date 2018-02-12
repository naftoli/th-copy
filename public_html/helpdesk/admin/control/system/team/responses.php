<?php

if (!defined('PARENT') || !isset($_GET['id'])) {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Lets check someone isn`t trying to view the admin user..
if ($_GET['id'] == '1' && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$U = mswGetTableData('users', 'id', (int) $_GET['id']);
checkIsValid($U);

$title = $msg_user87 . ' (' . mswSafeDisplay($U->name) . ')';

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/team/responses.php');
include(PATH . 'templates/footer.php');

?>