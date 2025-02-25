<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

// get admin 1264
$stmt = $MASHPIA_DB->prepare("select * from admins where admin_id = :admin");
$stmt->execute([':admin' => 1264]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) die('Admin not found');

// get the profile
require_once '../CustomerProfile.php';
use classes\authorize\CustomerProfile as Customer;
$customer_profile = new Customer($admin['authorize_customer_profile_id']);
$profiles = $customer_profile->paymentProfiles;

echo json_encode([
    'success'   => true,
    'cards'     => $profiles
]);