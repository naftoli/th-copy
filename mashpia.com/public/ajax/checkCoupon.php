<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/coupons/class.couponCode.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getRegistrationYear();
$couponCode = new CouponCode($MASHPIA_DB, $year, 'chayolei_base_reg');
$discount = $couponCode->isValidCode($_POST['coupon']);

if ($discount) {
    echo json_encode([
        'success'   => true,
        'discount'  => $discount
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'No such coupon found in the system.'
    ]);
}