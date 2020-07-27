<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$admin_id = $_POST['admin_id'];
$checked = $_POST['checked'];

$stmt = $MASHPIA_DB->prepare("
    UPDATE admins SET show_chidon_refund = :refund WHERE admin_id = :admin
");
$res = $stmt->execute([
    ':refund'   =>  $checked, 
    ':admin'    =>  $admin_id
]);

echo json_encode([
    'success'   =>  $res
]);