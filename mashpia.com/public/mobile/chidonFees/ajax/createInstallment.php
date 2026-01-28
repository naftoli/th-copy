<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/Installments.php';

use \classes\authorize\CustomerProfile as Customer;
use \classes\authorize\Installments as Installments;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['admin']) || !isset($data['amount']) || !isset($data['num_installments']) || !isset($data['card_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameters'
    ]);
    exit;
}

$admin = mysql_real_escape_string($data['admin']);
$admin_id = encrypt_decrypt('decrypt', $admin);
$amount = floatval($data['amount']);
$num_installments = intval($data['num_installments']);
$card_id = intval($data['card_id']);
$start_date = isset($data['start_date']) ? $data['start_date'] : date('Y-m-d');

if (!$admin_id || $amount <= 0 || $num_installments <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid parameters'
    ]);
    exit;
}

$year = GlobalSettings::getChidonYear();

// Check if there's already an active installment for this year
// $stmt = $MASHPIA_DB->prepare("
//     SELECT * FROM th_chidon_installments
//     WHERE admin_id = :admin_id 
//     AND year = :year
// ");
// $stmt->execute([
//     ':admin_id' => $admin_id,
//     ':year' => $year
// ]);
// if ($stmt->fetch()) {
//     echo json_encode([
//         'success' => false,
//         'error' => 'You already have an active installment plan for this year'
//     ]);
//     exit;
// }

// Get admin info to get customer profile
$sql = "SELECT * FROM admins WHERE admin_id = " . $admin_id;
$result = mysql_query($sql);
$admin_info = mysql_fetch_assoc($result);

if (!$admin_info || !$admin_info['authorize_customer_profile_id']) {
    echo json_encode([
        'success' => false,
        'error' => 'No payment profile found. Please add a credit card first.'
    ]);
    exit;
}

$MASHPIA_DB->beginTransaction();
try {
    $profile_id = $admin_info['authorize_customer_profile_id'];
    $customer = new Customer($profile_id);

    // First, save to registration_charges with trans_id = 0 (will update later with subscription_id)
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO registration_charges 
        (trans_id, user_id, school_id, admin_id, type, amount, date, year) 
        VALUES (0, :user_id, :school_id, :admin_id, 'RRFAM', :amount, NOW(), :year)
    ");
    $res = $stmt->execute([
        ':user_id' => 0,
        ':school_id' => 0,
        ':admin_id' => $admin_id,
        ':amount' => $amount,
        ':year' => $year
    ]);
    if (!$res) {
        throw new Exception('Failed to save registration charge to database');
    }
    $registration_charge_id = $MASHPIA_DB->lastInsertId();

    // Now create installment subscription
    $installments = new Installments($customer, $card_id, true);
    $response = $installments->createSubscription($amount, $num_installments, $start_date);

    if (strpos($response, 'Success') !== false) {
        // Subscription created successfully
        $subscription_id = $installments->getSubscriptionId();
        
        // Update registration_charges with subscription_id as trans_id
        $stmt = $MASHPIA_DB->prepare("
            UPDATE registration_charges 
            SET trans_id = :subscription_id
            WHERE registration_charge_id = :registration_charge_id
        ");
        $res = $stmt->execute([
            ':subscription_id' => $subscription_id,
            ':registration_charge_id' => $registration_charge_id
        ]);
        if (!$res) {
            throw new Exception('Failed to update registration charge with subscription_id');
        }
        
        // Save to th_chidon_installments table using the Installments class method
        $saved = $installments->saveToDb($MASHPIA_DB, $admin_id, $year);
        if (!$saved) {
            throw new Exception('Failed to save installment plan to database');
        } else {
            // update coupon codes used to 1
            $stmt = $MASHPIA_DB->prepare("
                UPDATE coupon_codes 
                SET used = 1, date_redeemed = NOW()
                WHERE used = 0 and year = :year and serial_num in (
                    SELECT user_serial FROM users WHERE user_id in (
                        SELECT id FROM admin_auths WHERE admin_id = :admin_id and auth = 'user'
                    )
                )
            ");
            $res = $stmt->execute([
                ':admin_id' => $admin_id,
                ':year' => $year
            ]);
            if (!$res) {
                throw new Exception('Failed to update coupon codes');
            }
        }
    } else {
        throw new Exception('Failed to create installment plan');
    }
} catch (Exception $e) {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}

$MASHPIA_DB->commit();
echo json_encode([
    'success' => true,
    'message' => 'Installment plan created successfully',
    'subscription_id' => $subscription_id
]);
exit;