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

$MASHPIA_DB->startTransaction();
$success = true;

foreach ($info as $user_id => $rank_ords) {
    $total += count($rank_ords);
    foreach ($rank_ords as $rank_ord) {
        $result = $stmt->execute([$user_id, $rank_ord]);
        if (!$result) {
            $success = false;
            break 2;
        }
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode([
        'success' => true,
        'total' => $total
    ]);
} else {
    $MASHPIA_DB->rollback();
    echo json_encode([
        'success' => false,
        'error' => 'Error setting rank medals as shipped.'
    ]);
}
