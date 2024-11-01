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

$stmt = $MASHPIA_DB->prepare("INSERT IGNORE INTO rank_medals_shipped (user_id, rank_ord) VALUES (?, ?)");

$data = json_decode(file_get_contents('php://input'), true);
$info = $data['info'];
$total = $data['total'];

// make sure our total is same as info total
$rankTotal = 0;
foreach ($info as $user_id => $ranks) {
    $rankTotal += count($ranks);
}
if ($rankTotal != $total) {
    echo json_encode([
        'success'   => false,
        'error'     => 'Error: corrupted file. Total ranks count does not match.'
    ]);
    exit;
}

$MASHPIA_DB->beginTransaction();
$success = true;

$error = '';
$updated = 0;
foreach ($info as $user_id => $ranks) {
    foreach ($ranks as $rank_ord) {
        $res = $stmt->execute([$user_id, $rank_ord]);
        if (!$res) {
            $success = false;
            $error = 'Failed to update rank for user ' . $user_id . ' and rank ' . $rank_ord. '\nNo ranks were set as shipped.';
            break;
        }
        $updated++;
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode([
        'success'       => true,
        'ranks_count'   => $updated
    ]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success'   => false,
        'error'     => $error
    ]);
}