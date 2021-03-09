<?php
ini_set('display_errors', 1);

require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../../coupons/class.couponCode.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

$c = new CouponCode($MASHPIA_DB, $year);
$code = $c->checkForCode($admin_id);
if ($code) echo json_encode([
    'success'   =>  true,
    'code'      =>  $code
]);
else echo json_encode([
    'success'   =>  false
]);
