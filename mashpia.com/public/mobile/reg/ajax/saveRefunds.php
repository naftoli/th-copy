<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';

$total = $_POST['total'];
$admin = encrypt_decrypt('decrypt', $_COOKIE['admin']);

// get parent cc info
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM admins WHERE admin_id = :id 
");
$res = $stmt->execute([
    ':id'   =>  $admin
]);
if ($res) {
    $profile = $stmt->fetch()['authorize_customer_profile_id'];
    if ($profile) {
        processRefund($profile);
    } else {
        echo json_encode([
            'success'   =>  false,
            'error'     =>  'You need to have a credit card profile with us in order to get your refund.\nPlease first setup your profile and then come back to this page.'
        ]);
        exit;
    }
}

// process refund
function processRefund($profile) {
    // include authorize functions

    updateDB();
}

// set flag in db to indicate parent received refund
function updateDB($trans_id) {
    global $MASHPIA_DB, $admin;
    $stmt = $MASHPIA_DB->prepare("
        UPDATE admins SET already_refunded = 1 WHERE admin_id = :id
    ");
    $res = $stmt->execute([
        ':id'   =>  $admin
    ]);
    if (!$res) {
        // send issue to naftoli
        $to = 'naftoli@tzivoshashem.org';
        $subject = 'Error in updating chidon refund status';
        $msg = 'There was an error updating Admin ID: ' . $admin . ' refund status. His transaction ID is: ' . $trans_id;
        $headers = 'From: admin@mashpia.com' . "\r\n" . 'Reply-To: admin@mashpia.com';
        @mail($to, $subject, $msg, $headers);
    }
}