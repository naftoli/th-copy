<?php
/**
 * Debug script to test direct SDK connection
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants as Constants;

echo "<h2>Testing Direct SDK Connection to Authorize.net</h2>";

// Display the constants directly
echo "<h3>Credentials being used:</h3>";
echo "Sandbox Login ID: " . Constants::GetMerchantLoginID(true) . "<br>";
echo "Sandbox Transaction Key: " . substr(Constants::GetMerchantTransactionKey(true), 0, 4) . "..." . "<br>";
echo "Sandbox API Endpoint: " . Constants::GetApiEndpoint(true) . "<br>";

// Test with the Authorize.net SDK directly
echo "<h3>Testing customer profile retrieval with SDK:</h3>";

$customer_profile_id = 931063847;
echo "Trying to retrieve customer profile ID: $customer_profile_id<br>";

try {
    // Create authentication object
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    $refId = 'ref' . time();
    
    // Create the API request
    $request = new \net\authorize\api\contract\v1\GetCustomerProfileRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setCustomerProfileId($customer_profile_id);
    
    // Execute the request against the sandbox environment
    $controller = new \net\authorize\api\controller\GetCustomerProfileController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "<p style='color:green;'>✓ Successfully retrieved customer profile!</p>";
        $profile = $response->getProfile();
        echo "Profile Description: " . $profile->getDescription() . "<br>";
        echo "Email: " . $profile->getEmail() . "<br>";
        
        // Check for payment profiles
        $paymentProfiles = $profile->getPaymentProfiles();
        if (!empty($paymentProfiles)) {
            echo "<p>Found " . count($paymentProfiles) . " payment profile(s):</p>";
            foreach ($paymentProfiles as $index => $paymentProfile) {
                echo "Payment Profile #" . ($index + 1) . " ID: " . $paymentProfile->getCustomerPaymentProfileId() . "<br>";
            }
        } else {
            echo "<p style='color:orange;'>⚠ No payment profiles found for this customer</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ Failed to retrieve customer profile</p>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}

// Now test creating a subscription directly with the SDK
echo "<h3>Testing subscription creation with SDK:</h3>";

try {
    // Create authentication object
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    $refId = 'ref' . time();
    
    // Create a subscription
    $subscription = new \net\authorize\api\contract\v1\ARBSubscriptionType();
    $subscription->setName("Test Subscription " . time());
    
    // Set the payment schedule
    $interval = new \net\authorize\api\contract\v1\PaymentScheduleType\IntervalAType();
    $interval->setLength(1);
    $interval->setUnit("months");
    
    $paymentSchedule = new \net\authorize\api\contract\v1\PaymentScheduleType();
    $paymentSchedule->setInterval($interval);
    $paymentSchedule->setStartDate(new \DateTime(date('Y-m-d')));
    $paymentSchedule->setTotalOccurrences(2);
    
    $subscription->setPaymentSchedule($paymentSchedule);
    $subscription->setAmount(50.00);
    
    // Set the customer profile
    $profile = new \net\authorize\api\contract\v1\CustomerProfileIdType();
    $profile->setCustomerProfileId($customer_profile_id);
    
    // Get the payment profile ID from the first test
    if (isset($paymentProfiles) && !empty($paymentProfiles)) {
        $paymentProfileId = $paymentProfiles[0]->getCustomerPaymentProfileId();
        $profile->setCustomerPaymentProfileId($paymentProfileId);
        echo "Using payment profile ID: $paymentProfileId<br>";
    } else {
        echo "<p style='color:red;'>No payment profile available to create subscription</p>";
        exit;
    }
    
    $subscription->setProfile($profile);
    
    // Create the request
    $request = new \net\authorize\api\contract\v1\ARBCreateSubscriptionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setSubscription($subscription);
    
    // Execute the request
    $controller = new \net\authorize\api\controller\ARBCreateSubscriptionController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "<p style='color:green;'>✓ Successfully created subscription!</p>";
        echo "Subscription ID: " . $response->getSubscriptionId() . "<br>";
        
        // Now cancel the subscription we just created
        echo "<h4>Cancelling test subscription:</h4>";
        $cancelRequest = new \net\authorize\api\contract\v1\ARBCancelSubscriptionRequest();
        $cancelRequest->setMerchantAuthentication($merchantAuthentication);
        $cancelRequest->setRefId($refId);
        $cancelRequest->setSubscriptionId($response->getSubscriptionId());
        
        $cancelController = new \net\authorize\api\controller\ARBCancelSubscriptionController($cancelRequest);
        $cancelResponse = $cancelController->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        
        if (($cancelResponse != null) && ($cancelResponse->getMessages()->getResultCode() == "Ok")) {
            echo "<p style='color:green;'>✓ Successfully cancelled subscription</p>";
        } else {
            echo "<p style='color:red;'>✗ Failed to cancel subscription</p>";
            $errorMessages = $cancelResponse->getMessages()->getMessage();
            echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
        }
    } else {
        echo "<p style='color:red;'>✗ Failed to create subscription</p>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}
?>
