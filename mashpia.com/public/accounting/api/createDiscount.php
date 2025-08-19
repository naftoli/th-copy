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

$discount_type = $_POST['discount_type'] ?? '';
$year = $_POST['year'] ?? '';
$amount = $_POST['amount'] ?? '';
$created_by = $_POST['created_by'] ?? '';
$reason = $_POST['reason'] ?? '';

// Validation
if (empty($discount_type) || empty($year) || empty($amount) || empty($created_by) || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!is_numeric($amount) || floatval($amount) <= 0) {
    echo json_encode(['success' => false, 'message' => 'Amount must be a positive number']);
    exit;
}

if (!is_numeric($year) || intval($year) < 5780) {
    echo json_encode(['success' => false, 'message' => 'Invalid year']);
    exit;
}

try {
    require_once '../../reports/registration/Discount.php';
    
    if ($discount_type === 'base') {
        // Base/School discount
        $school_id = $_POST['school_id'] ?? '';
        
        if (empty($school_id) || !is_numeric($school_id) || intval($school_id) <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid school selected']);
            exit;
        }

        $discount = new SchoolDiscount($year, $school_id, $amount, $reason, $created_by);
    } else if ($discount_type === 'soldier') {
        // Student discount
        $user_id = $_POST['user_id'] ?? '';
        
        if (empty($user_id) || !is_numeric($user_id) || intval($user_id) <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid student selected']);
            exit;
        }

        $discount = new StudentDiscount($year, $user_id, $amount, $reason, $created_by);
    }

    $dm = new DiscountManager($MASHPIA_DB);
    if ($dm->createDiscount($discount)) {
        echo json_encode(['success' => true, 'message' => 'Discount created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating discount']);
    }    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
