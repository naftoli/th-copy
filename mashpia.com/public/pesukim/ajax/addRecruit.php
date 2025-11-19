<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';

$MASHPIA_DB->beginTransaction();
$p = new Pesukim($_POST['user_id']);

try {
    $res = $p->addRecruit($_POST);
} catch (Exception $e) {
    $MASHPIA_DB->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

if ($res) {
    $MASHPIA_DB->commit();
    echo json_encode(['success' => $res]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode(['success' => false, 'error' => 'Failed to add recruit']);
}