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
$data = $_POST['updates'];
$fields = ['admin_id', 'reg_fee', 'chidon_drive', 'subsidy', 'coupon', 'coupon_reason', 'paid', 'balance'];

$sql = "update th_chidon_zelda set ";
foreach ($fields as $field) {
    if ($field == 'coupon_reason') $sql .= $field . " = '" . $data[$field] . "', ";
    else $sql .= $field . " = " . $data[$field] . ", ";
}
$sql = substr($sql, 0, strlen($sql) - 2);
$sql .= " where th_chidon_id = $chidon_id";
if (mysql_query($sql)) echo 1;
else echo 0;