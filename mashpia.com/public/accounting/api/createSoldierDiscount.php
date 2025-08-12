<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_POST['user_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$created_by = $_POST['created_by'] ?? '';
$reason = $_POST['reason'] ?? '';
$year = $_POST['year'] ?? '';

// Validation
if (empty($user_id) || empty($amount) || empty($created_by) || empty($reason) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!is_numeric($amount) || floatval($amount) <= 0) {
    echo json_encode(['success' => false, 'message' => 'Amount must be a positive number']);
    exit;
}

if (!is_numeric($user_id) || intval($user_id) <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid student selected']);
    exit;
}

if (!is_numeric($year) || intval($year) < 2020) {
    echo json_encode(['success' => false, 'message' => 'Invalid year']);
    exit;
}

try {
    require_once '../../reports/registration/Discount.php';
    
    $discount = new StudentDiscount($year, $user_id, $amount, $reason, $created_by);
    $d = new DiscountManager($MASHPIA_DB);
    
    if ($d->createStudentDiscount($discount)) {
        echo json_encode(['success' => true, 'message' => 'Discount created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating discount']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
