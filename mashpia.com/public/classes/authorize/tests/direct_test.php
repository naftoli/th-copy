<?php
/**
 * Direct test script using the SDK without relying on the Installments class
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the autoloader directly
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/vendor/autoload.php';
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants as Constants;

// Customer profile ID and payment profile ID from previous successful tests
$customer_profile_id = 931063847;
$payment_profile_id = 930354017;

echo "<h2>Direct SDK Test for Creating Subscription</h2>";

try {
    // Create authentication object for sandbox
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    echo "Using Login ID: " . Constants::GetMerchantLoginID(true) . "<br>";
    echo "Using Transaction Key: " . substr(Constants::GetMerchantTransactionKey(true), 0, 4) . "..." . "<br>";
    
    // Test parameters
    $amount = 100.00; // Total amount
    $num_installments = 2; // Number of installments
    $installment_amount = round($amount / $num_installments, 2);
    $start_date = date('Y-m-d'); // Start today for testing
    
    echo "<h3>Creating subscription with the following parameters:</h3>";
    echo "Amount: $" . $amount . "<br>";
    echo "Number of installments: " . $num_installments . "<br>";
    echo "Installment amount: $" . $installment_amount . "<br>";
    echo "Start date: " . $start_date . "<br>";
    
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
    $paymentSchedule->setStartDate(new \DateTime($start_date));
    $paymentSchedule->setTotalOccurrences($num_installments);
    
    $subscription->setPaymentSchedule($paymentSchedule);
    $subscription->setAmount($installment_amount);
    
    // Set the customer profile
    $profile = new \net\authorize\api\contract\v1\CustomerProfileIdType();
    $profile->setCustomerProfileId($customer_profile_id);
    $profile->setCustomerPaymentProfileId($payment_profile_id);
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
        $subscription_id = $response->getSubscriptionId();
        echo "<p style='color:green;'>✓ Successfully created subscription!</p>";
        echo "Subscription ID: " . $subscription_id . "<br>";
        
        // Get subscription info
        echo "<h3>Subscription Information:</h3>";
        $getRequest = new \net\authorize\api\contract\v1\ARBGetSubscriptionRequest();
        $getRequest->setMerchantAuthentication($merchantAuthentication);
        $getRequest->setRefId($refId);
        $getRequest->setSubscriptionId($subscription_id);
        $getRequest->setIncludeTransactions(true);
        
        $getController = new \net\authorize\api\controller\ARBGetSubscriptionController($request);
        $getResponse = $getController->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        
        if (($getResponse != null) && ($getResponse->getMessages()->getResultCode() == "Ok")) {
            // Displaying the details
            echo 'Subscription Name: ' . $getResponse->getSubscription()->getName() . "<br>";
            echo 'Subscription amount: ' . $getResponse->getSubscription()->getAmount() . "<br>";
            echo 'Subscription status: ' . $getResponse->getSubscription()->getStatus() . "<br>";
        } else {
            // Error
            echo "<p style='color:red;'>✗ Error getting subscription info</p>";
            if ($getResponse != null) {
                $errorMessages = $getResponse->getMessages()->getMessage();
                echo 'Response: ' . $errorMessages[0]->getCode() . ' ' . $errorMessages[0]->getText() . "<br>";
            } else {
                echo "Null response from API<br>";
            }
        }
        
        // Cancel the subscription after testing
        echo "<h3>Cancelling Subscription:</h3>";
        $cancelRequest = new \net\authorize\api\contract\v1\ARBCancelSubscriptionRequest();
        $cancelRequest->setMerchantAuthentication($merchantAuthentication);
        $cancelRequest->setRefId($refId);
        $cancelRequest->setSubscriptionId($subscription_id);
        
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
