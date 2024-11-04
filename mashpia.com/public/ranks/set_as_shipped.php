<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$stmt = $MASHPIA_DB->prepare("INSERT IGNORE INTO rank_books_shipped (user_id, book) VALUES (?, ?)");

$data = json_decode(file_get_contents('php://input'), true);
$info = $data['info'];
$total = $data['total'];

// make sure our total is same as info total
$bookTotal = count($info);
if ($bookTotal != $total) {
    echo json_encode([
        'success'   => false,
        'error'     => 'Error: corrupted file. Total books count does not match.'
    ]);
    exit;
}

$MASHPIA_DB->beginTransaction();
$success = true;

$error = '';
$updated = 0;
foreach ($info as $user_id => $book) {
    $res = $stmt->execute([$user_id, $book]);
    if (!$res) {
        $success = false;
        $error = 'Failed to update book #' . $book . ' for user ' . $user_id . '\nNo books were set as shipped.';
        break;
    }
    $updated++;
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode([
        'success'       => true,
        'books_count'   => $updated
    ]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success'   => false,
        'error'     => $error
    ]);
}