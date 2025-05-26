<?php
/**
 * Debug script for CustomerProfile class
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the autoloader directly
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/vendor/autoload.php';
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/includes/authorize_constants.php';
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/public/classes/authorize/Auth.php';
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/public/classes/authorize/AuthorizeAPIRequest.php';
require_once '/Users/naftolir/Sites/tzivos-hashem/mashpia.com/public/classes/authorize/CustomerProfile.php';

use includes\authorize\AuthorizeConstants as Constants;
use classes\authorize\Auth;
use classes\authorize\CustomerProfile;

echo "<h2>Debugging CustomerProfile Class</h2>";

// Define a debug function
function debug_print($message, $data = null) {
    echo "<p><strong>$message</strong></p>";
    if ($data !== null) {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

// First, let's check the constants
debug_print("Constants from authorize_constants.php");
debug_print("Sandbox Login ID", Constants::GetMerchantLoginID(true));
debug_print("Sandbox Transaction Key (first 4 chars)", substr(Constants::GetMerchantTransactionKey(true), 0, 4));

// Now let's check the Auth class
debug_print("Auth class with sandbox=true");
$auth = new Auth(true);
debug_print("Auth merchantAuthentication", $auth->merchantAuthentication);

// Now let's try to load a customer profile directly with the SDK
debug_print("Testing direct SDK connection to get customer profile");

try {
    // Customer profile ID from previous successful tests
    $customer_profile_id = 931063847;
    
    // Create authentication object
    $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName(Constants::GetMerchantLoginID(true));
    $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey(true));
    
    debug_print("Using credentials", [
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
        
        // Check if there are payment profiles
        $paymentProfiles = $profile->getPaymentProfiles();
        if (!empty($paymentProfiles)) {
            debug_print("Found payment profiles", count($paymentProfiles));
            foreach ($paymentProfiles as $index => $paymentProfile) {
                debug_print("Payment Profile #" . ($index + 1), [
                    'customerPaymentProfileId' => $paymentProfile->getCustomerPaymentProfileId()
                ]);
            }
        } else {
            debug_print("No payment profiles found");
        }
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
    debug_print("Exception during SDK call", $e->getMessage());
}

// Now let's try with the CustomerProfile class
debug_print("Testing CustomerProfile class");

try {
    // Create a modified version of the CustomerProfile class for debugging
    class DebugCustomerProfile extends CustomerProfile {
        public function __construct($profileId=false, $loadFromAPI = true, $api=null, $sandbox = false) {
            debug_print("CustomerProfile constructor called with", [
                'profileId' => $profileId,
                'loadFromAPI' => $loadFromAPI ? "true" : "false",
                'api' => $api ? "API object" : "null",
                'sandbox' => $sandbox ? "true" : "false"
            ]);
            
            // Create an instance of the auth object for the user to authenticate api requests
            $this->auth = new Auth($sandbox);
            debug_print("Auth object created with sandbox", $sandbox ? "true" : "false");
            debug_print("Auth merchantAuthentication", $this->auth->merchantAuthentication);
            
            // set the api handler to the AuthorizeAPIRequest object passed in.
            if ($api) {
                $this->api = $api;
                debug_print("Using provided API object");
            } else {
                $this->api = new \classes\authorize\AuthorizeAPIRequest("POST", null, null, $sandbox);
                debug_print("Created new AuthorizeAPIRequest with sandbox", $sandbox ? "true" : "false");
            }
            
            // if we pass in a profile id on intialization load the data from the API
            if($profileId) {
                $this->customerProfileId = $profileId;
                debug_print("Set customerProfileId to", $profileId);
                
                if ($loadFromAPI) { // defaults to true but this allows us to override it.
                    debug_print("Loading data from API");
                    $this->load(); // load the data from the API, See below
                }
            }
        }
        
        // Override the load method to add debugging
        function load() {
            debug_print("load method called");
            
            // Generate the JSON to get the API data
            $api_array = $this->auth->createApiCall(
                "getCustomerProfileRequest",
                [
                    "customerProfileId" => $this->customerProfileId,
                    "unmaskExpirationDate" => true
                ]
            );
            
            debug_print("API request prepared", $api_array);
            
            // return it for now (eventually make the api call and get the data)
            $this->api->setPostData($api_array);
            debug_print("Post data set on API object");
            
            $api_data = $this->api->execute();
            debug_print("API execute returned", $api_data);
            
            //print_r($api_data);
            // if the API request was OK
            if($api_data['messages']['resultCode'] == Constants::RESPONSE_OK) {
                debug_print("API request was successful");
                
                // get the profile from the response
                $profile = $api_data['profile'];
                
                // and set all the internal variables to the API data.
                $this->paymentProfiles = isset( $profile['paymentProfiles'] ) ? $profile['paymentProfiles'] : [];
                
                $this->customerProfileId = $profile['customerProfileId'];
                $this->description = isset( $profile['description'] ) ? $profile['description'] : false;
                $this->email = isset( $profile['email'] ) ? $profile['email'] : '';
                // handle strict testing
                if(array_key_exists('merchantCustomerId', $profile )) {
                    $this->merchantCustomerId = $profile['merchantCustomerId'];
                }
                if(array_key_exists('shipToList', $profile )) {
                    $this->shipToList = $profile['shipToList'];
                }
                
                debug_print("Profile data loaded", [
                    'customerProfileId' => $this->customerProfileId,
                    'description' => $this->description,
                    'email' => $this->email,
                    'paymentProfiles' => count($this->paymentProfiles)
                ]);
            } else {
                debug_print("API request failed");
                $this->invalid = true;
                $this->error_return = $api_data;
                debug_print("Set invalid=true and error_return", $api_data);
            }
        }
    }
    
    // Create a customer profile object with debugging
    debug_print("Creating DebugCustomerProfile object");
    $customer_profile = new DebugCustomerProfile(931063847, true, null, true); // true for sandbox
    
    if ($customer_profile->invalid) {
        debug_print("Error loading customer profile", $customer_profile->error_return);
    } else {
        debug_print("Customer profile loaded successfully", [
            'customerProfileId' => $customer_profile->customerProfileId,
            'description' => $customer_profile->description,
            'paymentProfiles' => count($customer_profile->paymentProfiles)
        ]);
    }
    
} catch (Exception $e) {
    debug_print("Exception", $e->getMessage());
}
?>
