<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../coupons/class.couponCode.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$c = new CouponCode( $MASHPIA_DB, $year );
$code = $_POST['code'];
if ( $value = $c->isValidCode( $code ) ) {
    echo json_encode([
        'success'   => true,
        'value'     => $value
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => "No such code found in our system or it was already used."
    ]);
}
