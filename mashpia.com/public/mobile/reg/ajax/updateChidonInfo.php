<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("update th_chidon set confirmed_info = :val where user_id = :user and year = :year");
$res = $stmt->execute([
    'user'  => $_POST['user_id'],
    'val'   => $_POST['confirmed'],
    'year'  => $year
]);
echo intval($res);