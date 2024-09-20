<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        'success' => false,
        'error' => 'Access Denied'
    ]);
    exit;
}

$stmt = $MASHPIA_DB->prepare("INSERT INTO rank_medals_shipped (user_id, rank_ord) VALUES (?, ?)");
$info = json_decode($_POST['info'], true);
$total = 0;

