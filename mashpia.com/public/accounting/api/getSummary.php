<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';
require_once '../../chidon_shipping/class.chidonShipping.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

$sql = "SELECT * FROM registration_charges WHERE year = :year";
if ($_POST['school_id'][0] > 0) {
    $sql .= " AND school_id IN (" . implode(',', $_POST['school_id']) . ")";
}
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    ':year' => $_POST['year']
]);
$registration_charges = $stmt->fetchAll(PDO::FETCH_ASSOC);

$charges = [];
$summary = [];
$grand_total = 0;
foreach ($registration_charges as $charge) {
    $charges[$charge['type']][] = $charge;
    if (!isset($summary[$charge['type']])) {
        $summary[$charge['type']] = floatval($charge['amount']);
    } else {
        $summary[$charge['type']] += floatval($charge['amount']);
    }
    $grand_total += floatval($charge['amount']);
}

$headers = ['Charge Type', 'Total Amount'];
$data = [];
foreach ($summary as $type => $amount) {
    $row['Charge Type'] = ChidonShipping::getDescription($type) ? ChidonShipping::getDescription($type) : $type;
    $row['Total Amount'] = number_format($amount, 2);
    $data[] = $row;
}
$row = [];
$row['Charge Type'] = 'Grand Total';
$row['Total Amount'] = number_format($grand_total, 2);
$data[] = $row;

echo json_encode([
    'headers' => $headers,
    'data' => $data
]);