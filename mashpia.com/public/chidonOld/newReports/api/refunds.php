<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$info = [];
$sql = "select * from family_prepaid_balances where year = :year and used > 0";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':year' => $year
]);
if ($res) {
    $info = $stmt->fetchAll();
}
echo json_encode($info);