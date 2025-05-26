<?php
/**
 * Test script for creating a subscription with an existing customer profile
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

echo "<h2>Subscription Test with Existing Customer Profile</h2>";
debug_print("Running with sandbox mode", $sandbox ? "enabled" : "disabled");

// Customer profile ID from the previous test
$customer_profile_id = 523541047;

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
    
    // Step 3: Create an installment plan with detailed debugging
    debug_print("Creating Installments object");
    $installments = new Installments($customer_profile, $payment_profile_id, true, $sandbox);
    
    // Set up installment details
    $total_amount = rand(50, 200) / 10; // Random amount between $5.00 and $20.00
    $num_installments = rand(2, 4); // Random number of installments between 2 and 4
    $start_date = date('Y-m-d', strtotime('+' . rand(1, 10) . ' day')); // Random start date
    
    debug_print("Setting up installment plan", [
        'total_amount' => $total_amount,
        'num_installments' => $num_installments,
        'start_date' => $start_date
    ]);
    
    // Step 4: Create the subscription with detailed debugging
    debug_print("Creating subscription");
    
    // Add detailed debugging for the createSubscription method
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID($sandbox));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey($sandbox));
    
    debug_print("Using SDK credentials", [
        'name' => Constants::GetMerchantLoginID($sandbox),
        'transactionKey' => substr(Constants::GetMerchantTransactionKey($sandbox), 0, 4) . "..."
    ]);
    
    // Set the transaction's refId
    $refId = 'ref' . time();
    
    // Subscription Type Info
    $subscription = new \net\authorize\api\contract\v1\ARBSubscriptionType();
    $subscription->setName("Subscription for " . $customer_profile->description);
    
    $interval = new \net\authorize\api\contract\v1\PaymentScheduleType\IntervalAType();
    $interval->setLength(1);
    $interval->setUnit("months");
    
    $paymentSchedule = new \net\authorize\api\contract\v1\PaymentScheduleType();
    $paymentSchedule->setInterval($interval);
    $paymentSchedule->setStartDate(new \DateTime($start_date));
    $paymentSchedule->setTotalOccurrences($num_installments);
    
    $subscription->setPaymentSchedule($paymentSchedule);
    $subscription->setAmount($total_amount / $num_installments);
    
    // Set up customer profile
    $profile = new \net\authorize\api\contract\v1\CustomerProfileIdType();
    $profile->setCustomerProfileId($customer_profile_id);
    $profile->setCustomerPaymentProfileId($payment_profile_id);
    $subscription->setProfile($profile);
    
    // Create the request
    $request = new \net\authorize\api\contract\v1\ARBCreateSubscriptionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setSubscription($subscription);
    
    debug_print("Executing ARBCreateSubscriptionRequest with SANDBOX endpoint");
    
    $controller = new \net\authorize\api\controller\ARBCreateSubscriptionController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        debug_print("Successfully created subscription with SDK");
        $subscriptionId = $response->getSubscriptionId();
        debug_print("Subscription ID", $subscriptionId);
        echo "<h3>✅ Subscription test passed successfully!</h3>";
    } else {
        debug_print("Failed to create subscription with SDK");
        if ($response != null) {
            $errorMessages = $response->getMessages()->getMessage();
            debug_print("Error", [
                'code' => $errorMessages[0]->getCode(),
                'text' => $errorMessages[0]->getText()
            ]);
        } else {
            debug_print("Null response from API");
        }
        echo "<h3>❌ Failed to create subscription</h3>";
    }
    
} catch (Exception $e) {
    debug_print("Exception occurred", $e->getMessage());
    echo "<h3>❌ Test failed with exception</h3>";
}
?>
