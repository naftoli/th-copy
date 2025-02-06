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

$stmt = $MASHPIA_DB->prepare("update :table set :field = :val where user_id = :user and year = :year");
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
        if (! $stmt->execute([
            ':table' => $table,
            ':field' => $field,
            ':val' => $val,
            ':user' => $user_id,
            ':year' => $year
        ])) {
            $success = false;
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