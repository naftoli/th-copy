<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$stmt = $MASHPIA_DB->prepare("
    update medal_marks 
    set date_shipped = now() 
    where user_id = :user
    and subject_id = :subject
    and medal_ord = :medal
");

$data = json_decode(file_get_contents('php://input'), true);
$info = $data['info'];
$total = $data['total'];

// make sure our total is same as info total
$medals = 0;
foreach ($info as $user_id => $more) {
    foreach ($more as $subject_id => $medal_ord) {
        $medals += count($medal_ord);
    }
}
if ($medals != $total) {
    echo json_encode([
        'success'   => false,
        'error'     => 'Error: corrupted file. Total medals count does not match.'
    ]);
    exit;
}

$MASHPIA_DB->beginTransaction();
$success = true;

$error = '';
$updated = 0;
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
                $error = 'Failed to update medal for user ' . $user_id . ' and medal ' . $medal_ord. '\nNo medals were set as shipped.';
                break;
            }
            $updated++;
        }
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode([
        'success'       => true,
        'medals_count'  => $updated
    ]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success'   => false,
        'error'     => $error
    ]);
}