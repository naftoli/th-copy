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
$info = file_get_contents('php://input');
$info = json_decode($info, true);

$total = 0;
$success = true;
$MASHPIA_DB->beginTransaction();
foreach ($info['info'] as $user_id => $rank_ords) {
    foreach ($rank_ords as $rank_ord) {
        $res = $stmt->execute([$user_id, $rank_ord]);
        if (!$res) {
            $success = false;
            break;
        }
        $total++;
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode([
        'success' => true,
        'total' => $total
    ]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success' => false,
        'error' => 'Failed to insert all records'
    ]);
}