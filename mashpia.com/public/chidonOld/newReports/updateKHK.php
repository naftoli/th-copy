<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$user_id = $_POST['user_id'];
$checked = $_POST['checked'];
$year = GlobalSettings::getChidonYear();
$sql = "update th_chidon set khk_override = " . mysql_real_escape_string($checked) . " where user_id = " . mysql_real_escape_string($user_id) . " and year = " . mysql_real_escape_string($year);
if (mysql_query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => mysql_error()
    ]);
}
