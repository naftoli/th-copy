<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

$admin_id = $_POST['admin_id'];
$checked = intval($_POST['checked']);

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$stmt = $MASHPIA_DB->prepare("
    UPDATE admins SET show_chidon_refund = :checked WHERE admin_id = :admin
");
$res = $stmt->execute([
    ':checked'  =>  $checked,
    ':admin'    =>  $admin_id
]);

echo json_encode([
    'success'   =>  $res
]);