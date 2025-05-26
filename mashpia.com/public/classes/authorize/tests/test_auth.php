<?php
/**
 * Test file for verifying Authorize.net API credentials
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once dirname(__DIR__) . '/Auth.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants as Constants;

echo "<h2>Testing Authorize.net API Credentials</h2>";

// Test sandbox credentials
echo "<h3>Sandbox Credentials:</h3>";
echo "Login ID: " . Constants::GetMerchantLoginID(true) . "<br>";
echo "Transaction Key: [Hidden for security]<br>";

// Test production credentials
echo "<h3>Production Credentials:</h3>";
echo "Login ID: " . Constants::GetMerchantLoginID() . "<br>";
echo "Transaction Key: [Hidden for security]<br>";

// Test API connection using the SDK
echo "<h3>Testing API Connection:</h3>";

// Load the Authorize.net SDK
$autoload_path = dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
    echo "SDK loaded successfully<br>";
} else {
    die("Error: Could not find Authorize.net SDK at $autoload_path");
}

// Test sandbox connection
try {
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    $refId = 'ref' . time();
    
    // Create a test API request
    $request = new \net\authorize\api\contract\v1\GetMerchantDetailsRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    
    $controller = new \net\authorize\api\controller\GetMerchantDetailsController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "Sandbox API credentials are valid!<br>";
        echo "Merchant Name: " . $response->getMerchantName() . "<br>";
        echo "Gateway ID: " . $response->getGatewayId() . "<br>";
    } else {
        echo "Sandbox API credentials are invalid!<br>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "Exception when testing sandbox credentials: " . $e->getMessage() . "<br>";
}
