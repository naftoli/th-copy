<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$chidon_id = $_POST['chidon_id'];
$field = $_POST['field'];
$value = $_POST['val'];

$sql = "update th_chidon_zelda set $field = '$value' where th_chidon_id = $chidon_id";
if (mysql_query($sql)) echo 1;
else echo 0;