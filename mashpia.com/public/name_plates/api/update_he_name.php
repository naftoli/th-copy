<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

// update he name
if (isset($_POST['user_id']) && isset($_POST['he_name'])) {
    $user_id = $_POST['user_id'];
    $he_name = $_POST['he_name'];
    $arrName = explode(' ', $he_name);
    $num = count($arrName);
    $first = '';
    $last = '';
    for ($i = 0; $i < $num; $i++) {
        if ($i == ($num-1)) {
            $last = $arrName[$i];
        } else {
            $first .= $arrName[$i] . ' ';
        }
    }
    $first = trim($first);
    $last = trim($last);
    
    $stmt = $MASHPIA_DB->prepare("
        UPDATE users
        SET first_he = :first_he, last_he = :last_he
        WHERE user_id = :user_id
    ");
    $res = $stmt->execute([
        ':user_id' => $user_id,
        ':first_he' => $first,
        ':last_he' => $last
    ]);
    
    echo json_encode([
        'success' => $res,
        'error' => $res ? null : 'He name not updated.'
    ]);
}