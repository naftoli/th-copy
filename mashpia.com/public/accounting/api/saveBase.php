<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$school_number = $_POST['school_number'];
$reg_type = $_POST['reg_type'];
$school_charge_date = $_POST['school_charge_date'];
$chayolei_fee = $_POST['chayolei_fee'];
$chidon_fee = $_POST['chidon_fee'];
$balance = $_POST['balance'];
$child_fee = $_POST['child_fee'];
$early_bird = $_POST['early_bird'];
$registration_notes = $_POST['registration_notes'];

$sql = "UPDATE schools SET 
        reg_type = :reg_type, 
        school_charge_date = :school_charge_date, 
        chayolei_fee = :chayolei_fee, 
        chidon_fee = :chidon_fee, 
        balance = :balance, 
        child_fee = :child_fee, 
        early_bird = :early_bird, 
        registration_notes = :registration_notes 
    WHERE school_number = :school_number";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':reg_type' => $reg_type,
    ':school_charge_date' => $school_charge_date,
    ':chayolei_fee' => $chayolei_fee,
    ':chidon_fee' => $chidon_fee,
    ':balance' => $balance,
    ':child_fee' => $child_fee == '' ? null : $child_fee,
    ':early_bird' => $early_bird,
    ':registration_notes' => $registration_notes,
    ':school_number' => $school_number
]);
// $stmt->debugDumpParams();

echo json_encode([
    'success' => $res
]);