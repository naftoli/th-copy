<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/coupons/class.couponCode.php';

$year = 5781;
$admin = $_POST['admin'];
$amount = floatval($_POST['amount']);

$c = new CouponCode($MASHPIA_DB, $year);
$coupon = $c->getCouponCode(6); // six digit coupon code
$saved = $c->saveCode($amount, 'HQ', "credit for chidon 5781");
if ($saved) {
    echo json_encode([
        'success'   => true,
        'info'      => $coupon
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'There was an error creating the code.'
    ]);
}
