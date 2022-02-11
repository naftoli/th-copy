<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../encrypt.php';
$admin = $_COOKIE['chidon_admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);

require_once __DIR__ . '/../../../classes/authorize/CustomerProfile.php';
use \classes\authorize\CustomerProfile as Customer;

$sql = "select * from admins where admin_id = " . $admin_id;
$result = mysql_query($sql);
$info = mysql_fetch_assoc($result);

$profile_id = $info['authorize_customer_profile_id'];
if ($profile_id) {
    $customer = new Customer($profile_id);
    $info['cards'] = $customer->paymentProfiles;
}

echo json_encode($info);