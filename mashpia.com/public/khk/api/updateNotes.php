<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

// update notes
if (isset($_POST['user_serial']) && isset($_POST['notes'])) {
    $user_serial = $_POST['user_serial'];
    $notes = $_POST['notes'];
    $stmt = $MASHPIA_DB->prepare("
        UPDATE khk_info_5758 
        SET notes = :notes 
        WHERE user_serial = :user_serial
    ");
    $res = $stmt->execute([
        ':user_serial' => $user_serial,
        ':notes' => $notes
    ]);
    
    echo json_encode([
        'success' => $res,
        'error' => $res ? null : 'Notes not updated.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Missing user_serial or notes.'
    ]);
}        