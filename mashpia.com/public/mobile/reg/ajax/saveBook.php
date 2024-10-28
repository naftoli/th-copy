<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";

$year = GlobalSettings::getChidonRegYear();
$user_id = $_POST['user_id'];
$book = $_POST['book'];

$stmt = $MASHPIA_DB->prepare("UPDATE th_chidon SET book = :book WHERE user_id = :user_id AND year = :year");
$res = $stmt->execute([
    'book' => $book,
    'user_id' => $user_id,
    'year' => $year
]);

echo intval($res);