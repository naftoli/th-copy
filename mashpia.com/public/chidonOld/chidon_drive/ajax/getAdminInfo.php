<?php
require_ONCE __DIR__ . '/../../../db.php';
require_ONCE __DIR__ . '/../encrypt.php';
$admin = $_COOKIE['chidon_admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);

require __DIR__ . '/../classes/authorize/CustomerProfile.php';
use \classes\authorize\CustomerProfile as Customer;

$sql = "select * from admins where admin_id = " . $admin_id;
$result = mysql_query($sql);
$info = mysql_fetch_assoc($result);

$profile_id = $info['authorize_customer_profile_id'];
if ($profile_id) {
    $customer = new Customer($profile_id);
//    echo "<pre>"; print_r($customer); echo "</pre>";
    $info['cards'] = $customer->paymentProfiles;
}

echo json_encode($info);