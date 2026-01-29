<?php
/**
 * Test file for creating a new subscription with unique parameters
 * This avoids the duplicate subscription error
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define debug mode
define('DEBUG_MODE', true);

// Include the autoloader directly
$mashpia_root = dirname(dirname(dirname(dirname(__DIR__))));
require_once $mashpia_root . '/vendor/autoload.php';
require_once $mashpia_root . '/includes/authorize_constants.php';

// Include necessary files
require_once $mashpia_root . '/public/classes/authorize/Auth.php';
require_once $mashpia_root . '/public/classes/authorize/AuthorizeAPIRequest.php';
require_once $mashpia_root . '/public/classes/authorize/CustomerProfile.php';
require_once $mashpia_root . '/public/classes/authorize/Installments.php';
require_once $mashpia_root . '/public/classes/authorize/PaymentProfile.php';

use \classes\authorize\CustomerProfile;
use \classes\authorize\Installments;
use \classes\authorize\PaymentProfile;
use includes\authorize\AuthorizeConstants as Constants;

// Debug function
function debug_print($message, $data = null) {
    echo "<p><strong>$message</strong></p>";
    if ($data !== null) {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

// Set admin ID
$admin_id = 1264;

// Set sandbox mode
$sandbox = true;

// Set customer profile ID (from previous successful tests)
$customer_profile_id = 931063847;

debug_print("Starting test with sandbox mode", $sandbox ? "enabled" : "disabled");

try {
    // Step 1: Load the customer profile
    debug_print("Loading customer profile with ID", $customer_profile_id);
    $customer_profile = new CustomerProfile($customer_profile_id, true, null, $sandbox);
    
    if ($customer_profile->invalid) {
        debug_print("Error loading customer profile", $customer_profile->error_return);
        die("Cannot proceed without a valid customer profile");
    }
    
    debug_print("Successfully loaded customer profile", [
        'description' => $customer_profile->description,
        'email' => $customer_profile->email
    ]);
    
    // Step 2: Get payment profile ID
    if (empty($customer_profile->paymentProfiles)) {
        die("No payment profiles found for this customer");
    }
    
    $payment_profile_id = $customer_profile->paymentProfiles[0]['customerPaymentProfileId'];
    debug_print("Using payment profile ID", $payment_profile_id);
    
    // Step 3: Create an installment plan
    debug_print("Creating Installments object");
    $installments = new Installments($customer_profile, $payment_profile_id, true, $sandbox);
    
    // Set up installment details with unique parameters
    // Use a random amount and different number of installments to avoid duplicate error
    $total_amount = rand(50, 200) / 10; // Random amount between $5.00 and $20.00
    $num_installments = rand(2, 4); // Random number of installments between 2 and 4
    $start_date = date('Y-m-d', strtotime('+' . rand(1, 10) . ' day')); // Random start date
    
    debug_print("Setting up installment plan with unique parameters", [
        'total_amount' => $total_amount,
        'num_installments' => $num_installments,
        'start_date' => $start_date
    ]);
    
    // Step 4: Create the subscription
    debug_print("Creating subscription");
    $result = $installments->createSubscription($total_amount, $num_installments, $start_date, $admin_id);
    
    debug_print("Subscription creation result", $result);
    
    if (strpos($result, "Success") !== false) {
        debug_print("Subscription created successfully with ID", $installments->getSubscriptionId());
    } else {
        debug_print("Failed to create subscription");
    }
    
} catch (Exception $e) {
    debug_print("Exception occurred", $e->getMessage());
}
?>
