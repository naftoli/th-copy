<?php
$admin_auth = ['user'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/db.php";

$stmt = $MASHPIA_DB->prepare("UPDATE users SET first_he = :first_he, last_he = :last_he WHERE user_id = :user_id");

$user_id = $_POST['user_id'];
$heName = $_POST['name'];

// first split he name into first / last
$nameInfo = explode(' ', $heName);
$end = count($nameInfo) - 1;
$last = $nameInfo[$end];
$first = '';
for ($i = 0; $i < $end; $i++) {
    $first .= $nameInfo[$i] . ' ';
}
$first = trim($first);

$res = $stmt->execute([
    'first_he' => $first,
    'last_he' => $last,
    'user_id' => $user_id
]);
echo intval($res);