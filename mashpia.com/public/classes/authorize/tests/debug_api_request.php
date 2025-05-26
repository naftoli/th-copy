<?php
/**
 * Debug script for API requests
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

use classes\authorize\Auth;
use classes\authorize\AuthorizeAPIRequest;
use includes\authorize\AuthorizeConstants as Constants;

echo "<h2>Debugging API Request</h2>";

// Debug function
function debug_print($message, $data = null) {
    echo "<p><strong>$message</strong></p>";
    if ($data !== null) {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

// Set sandbox mode
$sandbox = true;

// Check constants
debug_print("API Constants");
debug_print("Sandbox Login ID", Constants::GetMerchantLoginID($sandbox));
debug_print("Sandbox Transaction Key (first 4 chars)", substr(Constants::GetMerchantTransactionKey($sandbox), 0, 4));
debug_print("Sandbox API URL", Constants::GetApiEndpoint($sandbox));

// Create Auth object
debug_print("Creating Auth object with sandbox=true");
$auth = new Auth($sandbox);
debug_print("Auth merchantAuthentication", $auth->merchantAuthentication);

// Create a simple API request to check authentication
debug_print("Creating API request to get merchant details");
$api_request = new AuthorizeAPIRequest("POST", null, null, $sandbox);

// Generate the API request
$api_array = $auth->createApiCall(
    "getMerchantDetailsRequest",
    []
);
debug_print("API request data", $api_array);

// Set the post data and execute
$api_request->setPostData($api_array);
debug_print("Executing API request");
$response = $api_request->execute();
debug_print("API response", $response);

// Try a direct SDK call to authenticate
debug_print("Trying direct SDK authentication");

try {
    // Create authentication object
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID($sandbox));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey($sandbox));
    
    debug_print("Using SDK credentials", [
        'name' => Constants::GetMerchantLoginID($sandbox),
        'transactionKey' => substr(Constants::GetMerchantTransactionKey($sandbox), 0, 4) . "..."
    ]);
    
    $refId = 'ref' . time();
    
    // Create a request to get merchant details
    $request = new \net\authorize\api\contract\v1\GetMerchantDetailsRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    
    debug_print("Executing GetMerchantDetailsRequest with SANDBOX endpoint");
    
    $controller = new \net\authorize\api\controller\GetMerchantDetailsController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        debug_print("Successfully authenticated with SDK");
        $merchantDetails = $response->getMerchant();
        debug_print("Merchant details", [
            'merchantName' => $merchantDetails->getMerchantName(),
            'gatewayId' => $merchantDetails->getGatewayId()
        ]);
    } else {
        debug_print("Failed to authenticate with SDK");
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
    debug_print("Exception during SDK call", $e->getMessage());
}
?>
