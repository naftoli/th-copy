<?php
/**
 * Debug script to check what credentials are being used
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary files
$class_dir = dirname(__DIR__);
require_once $class_dir . '/Auth.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants as Constants;

echo "<h2>Debugging Authorize.net Authentication</h2>";

// Display the constants directly
echo "<h3>Constants from authorize_constants.php:</h3>";
echo "Sandbox Login ID: " . Constants::GetMerchantLoginID(true) . "<br>";
echo "Sandbox Transaction Key: " . substr(Constants::GetMerchantTransactionKey(true), 0, 4) . "..." . "<br>";
echo "Production Login ID: " . Constants::GetMerchantLoginID(false) . "<br>";
echo "Production Transaction Key: " . substr(Constants::GetMerchantTransactionKey(false), 0, 4) . "..." . "<br>";
echo "Sandbox API Endpoint: " . Constants::GetApiEndpoint(true) . "<br>";
echo "Production API Endpoint: " . Constants::GetApiEndpoint(false) . "<br>";

// Test creating Auth objects with different parameters
echo "<h3>Auth Object with sandbox=true:</h3>";
$auth_sandbox = new \classes\authorize\Auth(true);
echo "Login ID: " . $auth_sandbox->merchantAuthentication['merchantAuthentication']['name'] . "<br>";
echo "Transaction Key: " . substr($auth_sandbox->merchantAuthentication['merchantAuthentication']['transactionKey'], 0, 4) . "..." . "<br>";

echo "<h3>Auth Object with sandbox=false:</h3>";
$auth_production = new \classes\authorize\Auth(false);
echo "Login ID: " . $auth_production->merchantAuthentication['merchantAuthentication']['name'] . "<br>";
echo "Transaction Key: " . substr($auth_production->merchantAuthentication['merchantAuthentication']['transactionKey'], 0, 4) . "..." . "<br>";

// Test creating a CustomerProfile object
echo "<h3>CustomerProfile Object with sandbox=true:</h3>";
require_once $class_dir . '/CustomerProfile.php';
$customer_profile_id = 931063847;

try {
    $customer_profile = new \classes\authorize\CustomerProfile($customer_profile_id, false, null, true);
    echo "CustomerProfile created with ID: $customer_profile_id<br>";
    echo "Using Auth object with Login ID: " . $customer_profile->auth->merchantAuthentication['merchantAuthentication']['name'] . "<br>";
    echo "Using Auth object with Transaction Key: " . substr($customer_profile->auth->merchantAuthentication['merchantAuthentication']['transactionKey'], 0, 4) . "..." . "<br>";
    
    // Now try to load it and see what happens
    echo "<h3>Loading CustomerProfile with sandbox=true:</h3>";
    
    // Generate the API request that would be made
    $api_array = $customer_profile->auth->createApiCall(
        "getCustomerProfileRequest",
        [
            "customerProfileId" => $customer_profile_id,
            "unmaskExpirationDate" => true
        ]
    );
    
    echo "API Request that would be made:<br>";
    echo "<pre>" . htmlspecialchars(json_encode($api_array, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Now actually load it
    $customer_profile->load();
    
    if ($customer_profile->invalid) {
        echo "<p style='color:red;'>Error loading profile: " . print_r($customer_profile->error_return, true) . "</p>";
    } else {
        echo "<p style='color:green;'>Successfully loaded profile!</p>";
        echo "Profile Description: " . $customer_profile->description . "<br>";
        echo "Email: " . $customer_profile->email . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}

// Test with the Authorize.net SDK directly
echo "<h3>Testing with Authorize.net SDK directly:</h3>";
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';

try {
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    echo "Using Login ID: " . Constants::GetMerchantLoginID(true) . "<br>";
    echo "Using Transaction Key: " . substr(Constants::GetMerchantTransactionKey(true), 0, 4) . "..." . "<br>";
    
    $refId = 'ref' . time();
    
    $request = new \net\authorize\api\contract\v1\GetCustomerProfileRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setCustomerProfileId($customer_profile_id);
    
    $controller = new \net\authorize\api\controller\GetCustomerProfileController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "<p style='color:green;'>SDK call successful!</p>";
        $profile = $response->getProfile();
        echo "Profile Description: " . $profile->getDescription() . "<br>";
        echo "Email: " . $profile->getEmail() . "<br>";
    } else {
        echo "<p style='color:red;'>SDK call failed!</p>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>SDK Exception: " . $e->getMessage() . "</p>";
}
?>
