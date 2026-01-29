<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/Installments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/PaymentProfile.php';

use \classes\authorize\CustomerProfile as Customer;
use \classes\authorize\Installments as Installments;
use \classes\authorize\PaymentProfile as PaymentProfile;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['admin']) || !isset($data['amount']) || !isset($data['num_installments'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameters'
    ]);
    exit;
}

$admin = $data['admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);
$amount = floatval($data['amount']);
$num_installments = intval($data['num_installments']);
$card_id = isset($data['card_id']) ? intval($data['card_id']) : 0;
$start_date = isset($data['start_date']) ? $data['start_date'] : date('Y-m-d');

// New card data (when user chooses "Enter a new card" on the index page)
$new_card = isset($data['new_card']) && is_array($data['new_card']) ? $data['new_card'] : null;

if (!$admin_id || $amount <= 0 || $num_installments <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid parameters'
    ]);
    exit;
}

// Must have either an existing card_id or valid new card data
if ($card_id <= 0 && empty($new_card)) {
    echo json_encode([
        'success' => false,
        'error' => 'Please select a card on file or enter a new card.'
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

// Same pattern as processPayment5783.php: use Admin model and createPaymentProfile for new cards
$admin_model = \Admin::find('first', ['admin_id' => $admin_id]);
if (!$admin_model) {
    echo json_encode([
        'success' => false,
        'error' => 'Account not found.'
    ]);
    exit;
}

$profile_id = $admin_model->authorize_customer_profile_id;
$used_new_card = false;

// If user entered a new card, add it via Admin::createPaymentProfile (same as processPayment5783)
if ($card_id <= 0 && $new_card) {
    // Build payment_info in same format as processPayment5783.php addNewCard()
    $cc_number = isset($new_card['number']) ? preg_replace('/\D/', '', $new_card['number']) : '';
    $cc_exp = isset($new_card['exp']) ? trim($new_card['exp']) : '';
    $cc_cvv = isset($new_card['cvv']) ? trim($new_card['cvv']) : '';
    $zip = isset($new_card['zip']) ? trim($new_card['zip']) : '';

    if (strlen($cc_number) < 13 || strlen($cc_cvv) < 3 || !$zip) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid new card data. Please provide card number, expiration, security code, and zip.'
        ]);
        exit;
    }

    // Convert MMYY to YYYY-MM (same format as Chidon checkout: exp_yy + '-' + exp_mm)
    if (strlen($cc_exp) === 4) {
        $mm = substr($cc_exp, 0, 2);
        $yy = substr($cc_exp, 2, 2);
        $cc_exp = '20' . $yy . '-' . $mm;
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Expiration must be MMYY (e.g. 1225).'
        ]);
        exit;
    }

    $payment_info = [
        'cc-number'   => $cc_number,
        'cc-exp'      => $cc_exp,
        'x_card_code' => $cc_cvv,
        'zip'         => $zip
    ];

    $payment_profile = $admin_model->createPaymentProfile($payment_info);

    if (is_string($payment_profile)) {
        echo json_encode([
            'success' => false,
            'error' => $payment_profile
        ]);
        exit;
    }
    if (!($payment_profile instanceof PaymentProfile)) {
        echo json_encode([
            'success' => false,
            'error' => 'Could not add card to your account. Please check the card details and try again.'
        ]);
        exit;
    }

    $card_id = (int) $payment_profile->customerPaymentProfileId;
    $profile_id = $admin_model->authorize_customer_profile_id; // may have been set if profile was just created
    $used_new_card = true;
}

// When using an existing card, we need a customer profile on file
if ($card_id > 0 && !$profile_id) {
    echo json_encode([
        'success' => false,
        'error' => 'No payment profile found. Please add a credit card first.'
    ]);
    exit;
}

$MASHPIA_DB->beginTransaction();
try {
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

    // Now create installment subscription (skip updateBilling when we just added the card)
    $installments = new Installments($customer, $card_id, !$used_new_card);
    $response = $installments->createSubscription($amount, $num_installments, $start_date, $admin_id);

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
        throw new Exception('Failed to create installment subscription: ' . $response);
    }
    
    // If we get here, everything succeeded
    $MASHPIA_DB->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Installment plan created successfully',
        'subscription_id' => $subscription_id
    ]);

    // send email confirmation
    require_once $_SERVER['DOCUMENT_ROOT'] . '/emails/sendEmail.php';
    $from = 'chidon';
    $to = $admin_model->admin_email;
    $subject = 'Installment Plan Created';
    $message = 'Your installment plan for $' . $amount . ' starting on ' . $start_date . ' has been created successfully.<br /><br /> Your subscription ID is: ' . $subscription_id . '<br /><br /> Thank you.';
    $error = sendEmail($from, $to, $subject, $message);
    if ($error) {
        error_log('Error sending email: ' . $error);
    }
    exit; 
} catch (Exception $e) {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}