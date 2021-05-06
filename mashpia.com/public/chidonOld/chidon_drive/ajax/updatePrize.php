<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';

$field = mysql_real_escape_string($_POST['field']);
$user_id = mysql_real_escape_string($_POST['user_id']);
$value = mysql_real_escape_string($_POST['val']);

$sql = "update chidon_user_prizes set $field = $value where user_id = $user_id";
if (mysql_query($sql)) echo 1;
else echo 0;