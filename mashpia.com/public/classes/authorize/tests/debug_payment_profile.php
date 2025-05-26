<?php
/**
 * Debug script for payment profile
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
require_once $mashpia_root . '/public/classes/authorize/PaymentProfile.php';

use \classes\authorize\CustomerProfile;
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

echo "<h2>Debug Payment Profile</h2>";
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
    
    // Step 2: Check payment profiles
    if (empty($customer_profile->paymentProfiles)) {
        debug_print("No payment profiles found for this customer");
    } else {
        debug_print("Found payment profiles", count($customer_profile->paymentProfiles));
        foreach ($customer_profile->paymentProfiles as $index => $profile) {
            debug_print("Payment Profile #" . ($index + 1), $profile);
        }
    }
    
    // Step 3: Try to load the payment profile directly
    if (!empty($customer_profile->paymentProfiles)) {
        $payment_profile_id = $customer_profile->paymentProfiles[0]['customerPaymentProfileId'];
        debug_print("Loading payment profile with ID", $payment_profile_id);
        
        $payment_profile = new PaymentProfile($payment_profile_id, $customer_profile_id, true, null, $sandbox);
        
        if ($payment_profile->invalid) {
            debug_print("Error loading payment profile", "Payment profile is invalid");
        } else {
            debug_print("Successfully loaded payment profile", [
                'customerPaymentProfileId' => $payment_profile->customerPaymentProfileId,
                'cardNumber' => $payment_profile->cardNumber,
                'expirationDate' => $payment_profile->expirationDate,
                'cardType' => $payment_profile->cardType
            ]);
        }
    }
    
    // Step 4: Try to create a direct SDK call to verify the payment profile
    debug_print("Testing direct SDK call to verify payment profile");
    
    // Create authentication object
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID($sandbox));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey($sandbox));
    
    debug_print("Using SDK credentials", [
        'name' => Constants::GetMerchantLoginID($sandbox),
        'transactionKey' => substr(Constants::GetMerchantTransactionKey($sandbox), 0, 4) . "..."
    ]);
    
    $refId = 'ref' . time();
    
    // Create a request to get the customer payment profile
    $request = new \net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setCustomerProfileId($customer_profile_id);
    
    if (!empty($customer_profile->paymentProfiles)) {
        $payment_profile_id = $customer_profile->paymentProfiles[0]['customerPaymentProfileId'];
        $request->setCustomerPaymentProfileId($payment_profile_id);
        
        debug_print("Executing GetCustomerPaymentProfileRequest with SANDBOX endpoint");
        
        $controller = new \net\authorize\api\controller\GetCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            debug_print("Successfully retrieved payment profile with SDK");
            $paymentProfile = $response->getPaymentProfile();
            debug_print("Payment profile details", [
                'customerPaymentProfileId' => $paymentProfile->getCustomerPaymentProfileId(),
                'payment' => $paymentProfile->getPayment() ? "Payment object exists" : "No payment object",
                'billTo' => $paymentProfile->getBillTo() ? "BillTo object exists" : "No BillTo object"
            ]);
            
            // Check if there's a valid payment method
            $payment = $paymentProfile->getPayment();
            if ($payment) {
                $creditCard = $payment->getCreditCard();
                if ($creditCard) {
                    debug_print("Credit card details", [
                        'cardNumber' => $creditCard->getCardNumber(),
                        'expirationDate' => $creditCard->getExpirationDate()
                    ]);
                } else {
                    debug_print("No credit card found in payment profile");
                }
            } else {
                debug_print("No payment method found in payment profile");
            }
        } else {
            debug_print("Failed to retrieve payment profile with SDK");
            if ($response != null) {
                $errorMessages = $response->getMessages()->getMessage();
                debug_print("Error", [
                    'code' => $errorMessages[0]->getCode(),
                    'text' => $errorMessages[0]->getText()
                ]);
            } else {
                debug_print("Null response from API");
            }
        }
    }
    
} catch (Exception $e) {
    debug_print("Exception occurred", $e->getMessage());
}
?>
