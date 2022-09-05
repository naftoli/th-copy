<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once("../../api/header/header.php");

$data = json_decode(file_get_contents("php://input"));
$user_ids = $data->info;

$stmt = $MASHPIA_DB->prepare("update users set hachayol = :val where user_id = :user");

$success = true;
foreach ($user_ids as $id => $val) {
    $res = $stmt->execute([
        'val'   => $val,
        'user'  => $id
    ]);
    if (!$res) {
        echo json_encode([
            'success'   => false,
            'error'     => 'Error updating hachayol.'
        ]);
        break;
    }
}
if ($success) echo json_encode(['success' => $success]);
