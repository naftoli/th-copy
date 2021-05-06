<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';

$user_id = mysql_real_escape_string($_POST['user_id']);
$he_name = mysql_real_escape_string($_POST['he_name']);

$sql = "update chidon_user_prizes set he_name = '$he_name' where user_id = $user_id";
if (mysql_query($sql)) echo 1;
else echo 0;