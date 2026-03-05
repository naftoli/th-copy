<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$serial = $_POST['serial'] ?? '';
$field = $_POST['field'] ?? '';

if (in_array($field, ['khk_photo', 'chidon_photo'])) {
    $table = 'th_chidon';
} else {
    $table = 'users';
}

$sql = "update $table set $field = null where year = :year and user_id = (select user_id from users where serial = :serial)";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':year' => $year,
    ':serial' => $serial
]);

echo json_encode(['success' => $res ? 1 : 0]);