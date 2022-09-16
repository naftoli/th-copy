<?php
$admin_auth = 'school';
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$field = mysql_real_escape_string($_POST['field']);
$user_id = mysql_real_escape_string($_POST['user_id']);
$checked = mysql_real_escape_string($_POST['value']);
$type = 'chidon';
if ($field == 'book_shipped') $type = 'yahadus';

$sql = "update registration_charges set $field = $checked where user_id = $user_id and year = $year and type = '$type'";
if (mysql_query($sql)) echo 1;
else echo 0;