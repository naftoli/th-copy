<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// make sure only super admin can use
if ($admin_user['auth'] != 'super') {
    echo "no permission";
    exit;
}

$stmtUpdate = $MASHPIA_DB->prepare("
    UPDATE th_chidon_marks 
    SET answered_correctly = :answered 
    WHERE th_chidon_mark_id = :id 
");

$success = true;
$MASHPIA_DB->beginTransaction();

$stmt = $MASHPIA_DB->query("
    SELECT * FROM mashpia_backup2.th_chidon_marks m 
    JOIN th_chidon tc using (th_chidon_id) 
    WHERE tc.year = 5785
");
$marks = $stmt->fetchAll();
foreach ($marks as $mark) {
    $res = $stmtUpdate->execute([
        'answered'  => $mark['answered_correctly'],
        'id'        => $mark['th_chidon_mark_id']
    ]);
    if (!$res) {
        $success = false;
        break;
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo "success";
} else {
    $MASHPIA_DB->rollBack();
    $stmtUpdate->debugDumpParams();
    echo "failed";
}