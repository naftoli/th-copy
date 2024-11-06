<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$stmtIns = $MASHPIA_DB->prepare("INSERT IGNORE INTO rank_books_shipped (user_id, book) VALUES (?, ?)");
$stmtDel = $MASHPIA_DB->prepare("DELETE FROM rank_books_shipped WHERE user_id = ? AND book = ?");

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user'];
$book = $data['book'];
$checked = $data['checked'];

if ($checked) {
    $res = $stmtIns->execute([$user_id, $book]);
} else {
    $res = $stmtDel->execute([$user_id, $book]);
}

echo json_encode([
    'success' => intval($res) ? 1 : 0
]);