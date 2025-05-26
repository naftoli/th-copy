<?php
/**
 * Script to check the credentials being used
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary files
// The vendor directory is at /mashpia.com/vendor, not /mashpia.com/public/vendor
$root_path = dirname(dirname(dirname(dirname(__DIR__))));
require_once $root_path . '/vendor/autoload.php';
require_once $root_path . '/includes/authorize_constants.php';
require_once dirname(__DIR__) . '/Auth.php';

use includes\authorize\AuthorizeConstants as Constants;
use classes\authorize\Auth;

echo "<h2>Checking Authorize.net Credentials</h2>";

// Check the constants directly
echo "<h3>Constants from authorize_constants.php:</h3>";
echo "Sandbox Login ID: " . Constants::GetMerchantLoginID(true) . "<br>";
echo "Sandbox Transaction Key: " . substr(Constants::GetMerchantTransactionKey(true), 0, 4) . "..." . "<br>";
echo "Production Login ID: " . Constants::GetMerchantLoginID(false) . "<br>";
echo "Production Transaction Key: " . substr(Constants::GetMerchantTransactionKey(false), 0, 4) . "..." . "<br>";

// Check the Auth class
echo "<h3>Auth class with sandbox=true:</h3>";
$auth = new Auth(true);
echo "Login ID: " . $auth->merchantAuthentication['merchantAuthentication']['name'] . "<br>";
echo "Transaction Key: " . substr($auth->merchantAuthentication['merchantAuthentication']['transactionKey'], 0, 4) . "..." . "<br>";

// Test with the SDK directly
echo "<h3>Testing SDK connection with sandbox credentials:</h3>";

try {
    // Create authentication object
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    echo "Using Login ID: " . Constants::GetMerchantLoginID(true) . "<br>";
    echo "Using Transaction Key: " . substr(Constants::GetMerchantTransactionKey(true), 0, 4) . "..." . "<br>";
    
    $refId = 'ref' . time();
    
    // Create a test API request - get merchant details
    $request = new \net\authorize\api\contract\v1\GetMerchantDetailsRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    
    $controller = new \net\authorize\api\controller\GetMerchantDetailsController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "<p style='color:green;'>✓ Sandbox API credentials are valid!</p>";
        echo "Merchant Name: " . $response->getMerchantName() . "<br>";
        echo "Gateway ID: " . $response->getGatewayId() . "<br>";
    } else {
        echo "<p style='color:red;'>✗ Sandbox API credentials are invalid!</p>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}

// Try with hardcoded credentials from our previous successful test
echo "<h3>Testing with hardcoded credentials that worked previously:</h3>";

try {
    // Create authentication object with hardcoded credentials
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName("3YFKr8d8SMhs");
    $merchantAuthentication->setTransactionKey("3QrA8qAcc47T8p7D");
    
    echo "Using Login ID: 3YFKr8d8SMhs<br>";
    echo "Using Transaction Key: 3QrA..." . "<br>";
    
    $refId = 'ref' . time();
    
    // Create a test API request - get merchant details
    $request = new \net\authorize\api\contract\v1\GetMerchantDetailsRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    
    $controller = new \net\authorize\api\controller\GetMerchantDetailsController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "<p style='color:green;'>✓ Hardcoded sandbox API credentials are valid!</p>";
        echo "Merchant Name: " . $response->getMerchantName() . "<br>";
        echo "Gateway ID: " . $response->getGatewayId() . "<br>";
    } else {
        echo "<p style='color:red;'>✗ Hardcoded sandbox API credentials are invalid!</p>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}
?>
