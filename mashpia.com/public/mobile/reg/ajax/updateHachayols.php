<?php
$admin_auth = ['user'];
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$stmt = $MASHPIA_DB->prepare("update users set hachayol = :val where user_id = :user");
$users = $_POST['list'];

$MASHPIA_DB->beginTransaction();
foreach ($users as $user) {
    $res = $stmt->execute([
        'user'  => $user['user_id'],
        'val'   => $user['checked']
    ]);
    $stmt->debugDumpParams();
    if (!$res) {
//        $stmt->debugDumpParams();
        $MASHPIA_DB->rollBack();
        echo json_encode([
            'success'   => false,
            'error'     => 'There was an error updating you hachayol settings.'
        ]);
    }
}
// if we get here all is good
$MASHPIA_DB->commit();
echo json_encode([
    'success'   => true
]);
