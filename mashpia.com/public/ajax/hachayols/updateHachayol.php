<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once("../../api/header/header.php");

$stmt = $MASHPIA_DB->prepare("UPDATE users SET hachayol = :val WHERE user_id = :user");

$data = json_decode(file_get_contents("php://input"));
echo "<pre>"; print_r($data); echo "</pre>"; exit;
$toAdd = $data->toAdd;
$toRemove = $data->toRemove;

$MASHPIA_DB->beginTransaction();
$success = true;

foreach ($toAdd as $user_id) {
    $res = $stmt->execute([
        'val' => 1,
        'user' => $user_id
    ]);
    if (!$res) {
        $success = false;
        break;
    }
}
foreach ($toRemove as $user_id) {
    $res = $stmt->execute([
        'val' => 0,
        'user' => $user_id
    ]);
    if (!$res) {
        $success = false;
        break;
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode(['success' => true]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success' => false,
        'error' => 'Error updating hachayol(s).'
    ]);
}