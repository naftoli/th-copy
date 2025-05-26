<?php
/**
 * Test file for checking if a specific customer profile exists in sandbox
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants as Constants;

// The profile ID to check
$profileIdToCheck = 931063847;

echo "<h2>Checking Customer Profile ID: $profileIdToCheck</h2>";

// Set up the API request
$merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
$merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
$merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));

$refId = 'ref' . time();

// Create a request to get the customer profile
$request = new \net\authorize\api\contract\v1\GetCustomerProfileRequest();
$request->setMerchantAuthentication($merchantAuthentication);
$request->setRefId($refId);
$request->setCustomerProfileId($profileIdToCheck);

$controller = new \net\authorize\api\controller\GetCustomerProfileController($request);

try {
    // Execute the API call to the sandbox environment
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "<p style='color:green;'>✓ Profile exists in SANDBOX environment!</p>";
        $profile = $response->getProfile();
        echo "Profile Description: " . $profile->getDescription() . "<br>";
        echo "Email: " . $profile->getEmail() . "<br>";
        
        // Check if there are payment profiles
        if (isset($profile->getPaymentProfiles()[0])) {
            echo "<p>Payment profile exists</p>";
            echo "Payment Profile ID: " . $profile->getPaymentProfiles()[0]->getCustomerPaymentProfileId() . "<br>";
        } else {
            echo "<p style='color:orange;'>⚠ No payment profiles found for this customer</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ Profile NOT found in SANDBOX environment</p>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}

// Now check if it exists in the PRODUCTION environment
echo "<h2>Checking the same profile in PRODUCTION environment</h2>";

// Set up the API request for production
$merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
$merchantAuthentication->setName(Constants::GetMerchantLoginID(false));
$merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(false));

$request = new \net\authorize\api\contract\v1\GetCustomerProfileRequest();
$request->setMerchantAuthentication($merchantAuthentication);
$request->setRefId($refId);
$request->setCustomerProfileId($profileIdToCheck);

$controller = new \net\authorize\api\controller\GetCustomerProfileController($request);

try {
    // Execute the API call to the production environment
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "<p style='color:green;'>✓ Profile exists in PRODUCTION environment!</p>";
        $profile = $response->getProfile();
        echo "Profile Description: " . $profile->getDescription() . "<br>";
        echo "Email: " . $profile->getEmail() . "<br>";
        
        // Check if there are payment profiles
        if (isset($profile->getPaymentProfiles()[0])) {
            echo "<p>Payment profile exists</p>";
            echo "Payment Profile ID: " . $profile->getPaymentProfiles()[0]->getCustomerPaymentProfileId() . "<br>";
        } else {
            echo "<p style='color:orange;'>⚠ No payment profiles found for this customer</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ Profile NOT found in PRODUCTION environment</p>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error: " . $errorMessages[0]->getCode() . " - " . $errorMessages[0]->getText() . "<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . $e->getMessage() . "</p>";
}
?>
