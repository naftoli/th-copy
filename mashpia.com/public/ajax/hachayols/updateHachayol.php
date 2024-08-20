<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once("../../api/header/header.php");

$stmt = $MASHPIA_DB->prepare("UPDATE users SET hachayol = :val WHERE user_id = :user");

$data = json_decode(file_get_contents("php://input"));
foreach ($data->info as $user_id => $val) {
    $res = $stmt->execute([
        'val'   => $val,
        'user'  => $user_id
    ]);
    if (!$res) {
        echo json_encode([
            'success'   => false,
            'error'     => 'Error updating hachayol.'
        ]);
        break;
    }
}
echo json_encode(['success' => true]);