<?php
/**
 * Full integration test for Authorize.net
 * Tests the entire flow from creating a customer profile to creating a subscription
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

// Set sandbox mode
$sandbox = true;

echo "<h2>Full Authorize.net Integration Test</h2>";
debug_print("Running test with sandbox mode", $sandbox ? "enabled" : "disabled");

try {
    // Step 1: Create a test customer profile
    $customer_id = "test_" . time(); // Unique customer ID
    $email = "test_" . time() . "@example.com"; // Unique email
    $description = "Test Customer " . time();
    
    // Test credit card details (these are test values for Authorize.net sandbox)
    $card_number = "4111111111111111"; // Test Visa card
    $expiration = "2030-12"; // Future expiration date
    $cvv = "123"; // Test CVV
    
    // Billing information
    $billing_info = [
        "firstName" => "Test",
        "lastName" => "Customer",
        "company" => "Test Company",
        "address" => "123 Test St",
        "city" => "Test City",
        "state" => "NY",
        "zip" => "10001",
        "country" => "US",
        "phoneNumber" => "1234567890"
    ];
    
    // Create payment profile array
    $payment_profile = PaymentProfile::createBasicArray(
        $card_number,
        $expiration,
        $cvv,
        $billing_info,
        true // Set as default
    );
    
    debug_print("Creating new customer profile", [
        'customer_id' => $customer_id,
        'email' => $email,
        'description' => $description
    ]);
    
    // Create the customer profile
    $result = CustomerProfile::create(
        $customer_id,
        $email,
        $description,
        $payment_profile,
        false, // Not live validation
        null, // No API object
        $sandbox // Use sandbox
    );
    
    // Check if the result is a CustomerProfile object or an error
    if (is_object($result) && $result instanceof CustomerProfile) {
        debug_print("Successfully created customer profile", [
            'customerProfileId' => $result->customerProfileId,
            'description' => $result->description,
            'email' => $result->email
        ]);
        
        $customer_profile = $result;
        
        // Step 2: Get the payment profile ID
        if (empty($customer_profile->paymentProfiles)) {
            die("No payment profiles found for this customer");
        }
        
        $payment_profile_id = $customer_profile->paymentProfiles[0]['customerPaymentProfileId'];
        debug_print("Using payment profile ID", $payment_profile_id);
        
        // Step 3: Create an installment plan
        debug_print("Creating Installments object");
        $installments = new Installments($customer_profile, $payment_profile_id, true, $sandbox);
        
        // Set up installment details with unique parameters
        $total_amount = rand(50, 200) / 10; // Random amount between $5.00 and $20.00
        $num_installments = rand(2, 4); // Random number of installments between 2 and 4
        $start_date = date('Y-m-d', strtotime('+' . rand(1, 10) . ' day')); // Random start date
        
        debug_print("Setting up installment plan", [
            'total_amount' => $total_amount,
            'num_installments' => $num_installments,
            'start_date' => $start_date
        ]);
        
        // Step 4: Create the subscription
        debug_print("Creating subscription");
        $result = $installments->createSubscription($total_amount, $num_installments, $start_date);
        
        debug_print("Subscription creation result", $result);
        
        if (strpos($result, "Success") !== false) {
            debug_print("Subscription created successfully with ID", $installments->getSubscriptionId());
            echo "<h3>✅ Full integration test passed successfully!</h3>";
        } else {
            debug_print("Failed to create subscription");
            echo "<h3>❌ Failed to create subscription</h3>";
        }
    } else {
        debug_print("Failed to create customer profile", $result);
        echo "<h3>❌ Failed to create customer profile</h3>";
    }
    
} catch (Exception $e) {
    debug_print("Exception occurred", $e->getMessage());
    echo "<h3>❌ Test failed with exception</h3>";
}
?>
