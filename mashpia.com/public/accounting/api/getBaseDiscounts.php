<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

$year = $_POST['year'];
$school_id = $_POST['school_id'];

require_once '../../reports/registration/Discount.php';
$d = new DiscountManager($MASHPIA_DB);
$info = [];
if ($school_id[0] > 0) {
    foreach ($school_id as $id) {
        $discounts = $d->getDiscountsForSchoolYear($year, $id);
        $info = array_merge($info, $discounts);
    }
} else {
    $discounts = $d->getDiscountsForYear($year);
    $info = array_merge($info, $discounts);
}

$data = [];
$headers = ['Year', 'School', 'Amount', 'Reason', 'Created By', 'Created', 'Used'];
foreach ($info as $discount) {
    $row = [];
    $row['Year'] = $discount['year'];
    $row['School'] = $discount['school_name'];
    $row['Amount'] = $discount['amount'];
    $row['Reason'] = $discount['reason'];
    $row['Created By'] = $discount['created_by'];
    $row['Created'] = $discount['created'];
    $row['Used'] = $discount['used'];
    $data[] = $row;
}

echo json_encode([
    'headers' => $headers,
    'data' => $data
]);