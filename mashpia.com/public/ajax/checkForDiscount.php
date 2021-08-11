<?php
header('Access-Control-Allow-Origin: *');  // CORS
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/reports/registration/Discount.php';

$year = GlobalSettings::getRegistrationYear();
$school_id = $_REQUEST['school_id'] ? $_REQUEST['school_id'] : 0;
$user_id = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : 0;
$discount = 0;

$row['amount'] = 0;
if ($school_id > 0) {
    $d = new DiscountManager($MASHPIA_DB);
    $row = $d->getDiscountsForSchoolYear($year, $school_id);
} else if ($user_id > 0) {
    $d = new DiscountManager($MASHPIA_DB);
    $row = $d->getDiscountForUserYear($year, $user_id);
}
if ($row['amount']) $discount = $row['amount'];
echo json_encode([
    'success' => true,
    'discount' => $discount
]);
