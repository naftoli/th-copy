<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$coupon = mysql_real_escape_string($_POST['coupon']);
$school_id = mysql_real_escape_string($_POST['school_id']);

if (! $coupon && $school_id) {
    echo json_encode([
        'success'   => false,
        'error'     => 'Error: Coupon code or School ID missing.'
    ]);
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/coupons/class.couponCode.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

    $year = GlobalSettings::getRegistrationYear($school_id);
    $couponCode = new CouponCode($MASHPIA_DB, $year, 'chayolei_base_reg');
    $discount = $couponCode->isValidCode($coupon);

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
}