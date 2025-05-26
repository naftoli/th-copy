<?php
/**
 * Debug script to check endpoint selection
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define debug mode
define('DEBUG_MODE', true);

// Include the autoloader directly
$mashpia_root = dirname(dirname(dirname(dirname(__DIR__))));
require_once $mashpia_root . '/vendor/autoload.php';
require_once $mashpia_root . '/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants as Constants;

echo "<h2>Debugging API Endpoint Selection</h2>";

// Debug function
function debug_print($message, $data = null) {
    echo "<p><strong>$message</strong></p>";
    if ($data !== null) {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

// Test the Constants class
debug_print("Testing Constants class");
debug_print("Sandbox URL from Constants::GetApiEndpoint(true)", Constants::GetApiEndpoint(true));
debug_print("Production URL from Constants::GetApiEndpoint(false)", Constants::GetApiEndpoint(false));

// Test direct SDK constants
debug_print("Testing SDK constants");
debug_print("SDK SANDBOX constant", \net\authorize\api\constants\ANetEnvironment::SANDBOX);
debug_print("SDK PRODUCTION constant", \net\authorize\api\constants\ANetEnvironment::PRODUCTION);

// Test the AuthorizeAPIRequest class
require_once $mashpia_root . '/public/classes/authorize/Auth.php';
require_once $mashpia_root . '/public/classes/authorize/AuthorizeAPIRequest.php';

use classes\authorize\Auth;
use classes\authorize\AuthorizeAPIRequest;

// Test with sandbox = true
debug_print("Testing AuthorizeAPIRequest with sandbox = true");
$api_request_sandbox = new AuthorizeAPIRequest("POST", null, null, true);
debug_print("API request created");

// Test with sandbox = false
debug_print("Testing AuthorizeAPIRequest with sandbox = false");
$api_request_production = new AuthorizeAPIRequest("POST", null, null, false);
debug_print("API request created");

// Test CustomerProfile class
require_once $mashpia_root . '/public/classes/authorize/CustomerProfile.php';
use classes\authorize\CustomerProfile;

// Test with sandbox = true
debug_print("Testing CustomerProfile with sandbox = true");
try {
    $customer_profile_id = 931063847;
    $customer_profile = new CustomerProfile($customer_profile_id, false, null, true);
    debug_print("CustomerProfile object created with sandbox = true");
    
    // Create a mock API call to see what endpoint is used
    $auth = new Auth(true);
    debug_print("Auth object created with sandbox = true");
    debug_print("Auth merchantAuthentication", $auth->merchantAuthentication);
    
    // Create a test API request
    $api_request = new AuthorizeAPIRequest("POST", null, null, true);
    
    // Generate a test API call
    $api_array = $auth->createApiCall(
        "getCustomerProfileRequest",
        [
            "customerProfileId" => $customer_profile_id,
            "unmaskExpirationDate" => true
        ]
    );
    
    debug_print("API request prepared", $api_array);
    
    // Set the post data but don't execute
    $api_request->setPostData($api_array);
    debug_print("Post data set on API object");
    
    // Now try with the SDK directly
    debug_print("Testing direct SDK call with sandbox = true");
    
    // Create authentication object
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    debug_print("Using SDK credentials", [
        'name' => Constants::GetMerchantLoginID(true),
        'transactionKey' => substr(Constants::GetMerchantTransactionKey(true), 0, 4) . "..."
    ]);
    
    $refId = 'ref' . time();
    
    // Create a request to get the customer profile
    $request = new \net\authorize\api\contract\v1\GetCustomerProfileRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setCustomerProfileId($customer_profile_id);
    
    debug_print("Executing GetCustomerProfileRequest with SANDBOX endpoint");
    
    $controller = new \net\authorize\api\controller\GetCustomerProfileController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        debug_print("Successfully retrieved customer profile with SDK");
        $profile = $response->getProfile();
        debug_print("Profile details", [
            'customerProfileId' => $profile->getCustomerProfileId(),
            'description' => $profile->getDescription(),
            'email' => $profile->getEmail()
        ]);
    } else {
        debug_print("Failed to retrieve customer profile with SDK");
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
    
} catch (Exception $e) {
    debug_print("Exception during CustomerProfile test", $e->getMessage());
}
?>
