<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

$stmt = $MASHPIA_DB->prepare("
    update medal_marks 
    set date_shipped = now() 
    where user_id = :user
    and subject_id = :subject
    and medal_ord = :medal
");

$MASHPIA_DB->beginTransaction();
$success = true;

$info = $_POST['info'];
foreach ($info as $user_id => $more) {
    foreach ($more as $subject_id => $medals) {
        foreach ($medals as $medal_ord) {
            $res = $stmt->execute([
                'user' => $user_id,
                'subject' => $subject_id,
                'medal' => $medal_ord
            ]);
            if (!$res) {
                $success = false;
                break;
            }
        }
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode(['success' => true]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode(['success' => false]);
}