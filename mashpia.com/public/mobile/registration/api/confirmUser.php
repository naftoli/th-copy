<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$info = json_decode(file_get_contents('php://input'), true);
$user_id = $info['user_id'];

// update th_chidon table to confirm user
$stmt = $MASHPIA_DB->prepare("
        UPDATE th_chidon SET confirmed_info = 1 WHERE user_id = :user_id AND year = :year 
    ");
$res = $stmt->execute([
    ':year'     => $year,
    ':user_id'  => $user_id
]);
echo $res ? 1 : 0;