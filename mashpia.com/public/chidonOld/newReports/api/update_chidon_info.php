<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$input = json_decode(file_get_contents('php://input'), true);
$table = "th_chidon";

$mapping = [
    'track_passed'  => 'th_chidon_info',
    'khk_eligible'  => 'users',
];

$success = true;
$MASHPIA_DB->beginTransaction();
foreach ($input as $user_id => $more) {
    foreach ($more as $field => $val) {
        if ($field == 'school_id_chidon') {
            $field = 'school_id';
        }
        if (array_key_exists($field, $mapping)) {
            $table = $mapping[$field];
        }
        
        $sql = "UPDATE " . $table . " SET " . $field . " = :val WHERE user_id = :user AND year = :year";
        
        if (!$stmt = $MASHPIA_DB->prepare($sql) || !$stmt->execute([
            ':val' => $val,
            ':user' => $user_id,
            ':year' => $year
        ])) {
            $success = false;
            // $stmt->debugDumpParams();
            break;
        }
    }
}$success = true;
$MASHPIA_DB->beginTransaction();
foreach ($input as $user_id => $more) {
    foreach ($more as $field => $val) {
        if ($field == 'school_id_chidon') {
            $field = 'school_id';
        }
        if (array_key_exists($field, $mapping)) {
            $table = $mapping[$field];
        }
        if (! $stmt->execute([
            ':field' => $field,
            ':val' => $val,
            ':user' => $user_id,
            ':year' => $year
        ])) {
            $success = false;
            $stmt->debugDumpParams();
            break;
        }
    }
}
if ($success) {
    $MASHPIA_DB->commit();
} else {
    $MASHPIA_DB->rollBack();
}

echo json_encode(['success' => $success]);