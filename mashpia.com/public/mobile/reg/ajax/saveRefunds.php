<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';

$total = $_POST['total'];
$donation = $_POST['donation'];
$donation50 = implode(',', $_POST['donation50']);
$num_donation_50 = $_POST['num_donation_50'];
$num_children = $_POST['num_children'];
$admin = encrypt_decrypt('decrypt', $_COOKIE['admin']);

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO chidon_refunds 
    SET 
        year = :year, 
        admin_id = :admin, 
        donation = :donation, 
        refund = :total, 
        donation_50 = :donation50,
        num_donation_50 = :num50,
        num_children = :num_children
");
$res = $stmt->execute([
    ':year'     => 5780,
    ':admin'    => $admin,
    ':donation' => $donation,
    ':total'    => $total,
    ':donation50'=> $donation50,
    ':num50'    => $num_donation_50,
    ':num_children' => $num_children
]);
if ($res) {
    $stmt = $MASHPIA_DB->prepare("
        UPDATE admins SET already_refunded = 1 WHERE admin_id = :admin
    ");
    $stmt->execute([':admin' => $admin]);
    echo json_encode([
        'success'   => true
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'There was an error processing your request'
    ]);
}