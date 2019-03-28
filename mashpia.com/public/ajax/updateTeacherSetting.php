<?php
require '../db.php';
$admin = mysql_real_escape_string($_POST['admin']);
$setting = mysql_real_escape_string($_POST['setting']);
$type = mysql_real_escape_string($_POST['type']);

if ($type == 'achievement') {
    $sql = "update admins set achievement_cards = " . $setting . " where admin_id = " . $admin;
} else if ($type == 'store') {
    $sql = "update admins set store = " . $setting . " where admin_id = " . $admin;
}
if (mysql_query($sql)) {
    echo 0;
} else {
    echo mysql_error();
}