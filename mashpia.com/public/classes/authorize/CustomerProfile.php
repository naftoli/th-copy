<?php
namespace classes\authorize;

use classes\authorize;
use \Datetime;

// load the custom authorization tools for the Authorize.net API
require_once( dirname(__FILE__).'/Auth.php' ); // loads the constants class in this namespace as well
require_once( dirname(__FILE__).'/AuthorizeAPIRequest.php' );

use includes\authorize\AuthorizeConstants as Constants;

class CustomerProfile {
    
    private $auth = null; // internal instance of the authentication class.
    private $api = null;
    
    public $customerProfileId = ""; // id from API
    public $merchantCustomerId = ""; // id that we gave it
    public $email = ""; // email address from user
    public $description = ""; // a description of the user (school name?)
    
    // Array of payment profiles and shipping addresses.
    public $paymentProfiles = array();
    public $shipToList = array();
    
    public $invalid = false;
    public $error_return = null;
    
    /*
     * Customer Profile Constructor
     *
     * new CustomerProfile([$profileId[, $loadFromAPI [,$api]]])
     * 
     * $api
     *  API object to make requests against (\classes\authorize\AuthorizeAPIRequest). Will create a new one by default
     *
     * Optional:
     *  Takes profile ID and auto loads all the details
     *  Takes a boolean (defaults to true) that determines if the items details will be loaded.
     *
     */
    
    function __construct($profileId=false, $loadFromAPI = true, $api=null){
        // Create an instance of the auth object for the user to authenticate api requests
        $this->auth = new Auth();
        
        // set the api handler to the AuthorizeAPIRequest object passed in.
        if ($api){
            $this->api = $api;
        } else {
            $this->api = new AuthorizeAPIRequest();
        }
        
        // if we pass in a profile id on intialization load the data from the API
        if($profileId) {
            $this->customerProfileId = $profileId;
            if ($loadFromAPI) { // defaults to true but this allows us to override it.
                $this->load(); // load the data from the API, See below
            }
        }
    }
    
    /*
     * ConsumerProfile::create(customerId, email, description, paymentProfile, [$live])
     *
     * Create a new instance of a ConsumerProfile via the api
     *
     * customerId => an id for us to use to track the customer. Required,
     * email => the email address, must be unique as we can query the api with this
     * description => just some nice descriptive text
     * paymentProfile => the initial payment profile for the user. Use PaymentProfile:createBasicArray for this
     * live (optional) => is the validation live or mathamatical
    */
    
    // Create a new CustomerProfile with data
    // (Idealy return a new instance as well)
    public static function create($customerId, $email, $description, $paymentProfile, $live = false, $api=null) {

        $auth = new Auth(); // create a new instance of auth in the static context
        
        // create an instance of the api if it was not passed in.
        if (!$api){
            $api = new AuthorizeAPIRequest();
        }

        // create an authorized api call with the required data
        // see here for documentation: http://developer.authorize.net/api/reference/index.html#customer-profiles-create-customer-profile
        $api_array = [
            "profile"=>[
                "merchantCustomerId"=>$customerId
            ],
            "validationMode"=>$auth->getValidationMode($live)
        ];
        
        if (!!$description) {
            $api_array["profile"]["description"] = $description;
        }
        
        if(!!$email) {
             $api_array["profile"]["email"] = $email;
        }

        $paymentProfile['defaultPaymentProfile'] = true;
        $api_array["profile"]["paymentProfiles"] = $paymentProfile;

        $api_array = $auth->createApiCall("createCustomerProfileRequest", $api_array);

        // set the data and execute the request
        $api->setPostData($api_array);

        $api_data = $api->execute();

        // If it is sucessfull (the API call);
        if ($api_data && $api_data['messages']['resultCode'] == Constants::RESPONSE_OK || array_key_exists("customerProfileId", $api_data)) {
            // If we recive the correct code in the first message
            if ($api_data["messages"]["message"][0]["code"] === "I00001") {
                // return a new instance of the object
                return new self($api_data["customerProfileId"], true, $api); // create the base object
            } else { // not the code we expected
                return $api_data; // what on earth should we return if it is ok but the status is incorrect and there is no id given?
            }
        } else if ($api_data && $api_data['messages']['message'][0]['code'] === "E00039") {
            preg_match('/ID [0-9]*/', $api_data['messages']['message'][0]['text'], $matches);
            
            $customerProfileId = explode(" ", $matches[0])[1];
            // create payment profile
            $paymentProfile = $paymentProfile["payment"]["creditCard"];
            PaymentProfile::create(
                $paymentProfile["cardNumber"],  $paymentProfile["expirationDate"], 
                $paymentProfile["cardCode"],    $customerProfileId 
            );
            return new self($customerProfileId, true, $api);
        } else { // return an error message
            $error_msg = $api_data['messages']['resultCode'] .
                    "(" .  $api_data['messages']['message'][0]['code'] . "): " .
                    $api_data['messages']['message'][0]['text'];
            return ["message" => $error_msg, "result" => $api_data];
        }
    }
    
    /*
     * customerProfile.load()
     *
     * Loads the data from the api and sets the public variables
     *
     * Takes no paramaters
     */
    
    function load() {
        // Generate the JSON to get the API data
        $api_array = $this->auth->createApiCall(
            "getCustomerProfileRequest",
            [
                "customerProfileId" => $this->customerProfileId
            ]
        );
        // return it for now (eventually make the api call and get the data)
        $this->api->setPostData($api_array);
        $api_data = $this->api->execute();
        //print_r($api_data);
        // if the API request was OK
        if($api_data['messages']['resultCode'] == Constants::RESPONSE_OK) {
            
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
        } else {
            $this->invalid = true;
            $this->error_return = $api_data;
        }
        
    }
    
    /*
     * paymentProfile.update()
     *
     * Updates the payment profile with the provider.
     *
     * Takes no paramaters (uses the public ones.)
     *
     */
    
    public function update(){
        // generate the json to persist the objects data to the api
        $api_array = ["profile" => []];
        // only update the feilds which are set on the object
        if ($this->merchantCustomerId) { $api_array["profile"]["merchantCustomerId"] = $this->merchantCustomerId; }
        if ($this->description){ $api_array["profile"]["description"] = $this->description; }
        if ($this->email){ $api_array["profile"]["email"] = $this->email; }
        
        // It seems this has to be at the end
        $api_array["profile"]["customerProfileId"] = $this->customerProfileId;
        
        $api_array = $this->auth->createApiCall("updateCustomerProfileRequest",$api_array);
        // set the array to the post data and submit to the server
        $this->api->setPostData($api_array);
        $api_data = $this->api->execute();
        
        if($api_data['messages']['resultCode'] == Constants::RESPONSE_OK) {
            return $api_data['messages']["message"][0]["text"];
        } else {
            $this->invalid = true;
            // return an error message
            return $api_data['messages']['resultCode'] .
                    "(" .  $api_data['messages']['message'][0]['code'] . "): " .
                    $api_data['messages']['message'][0]['text'];
        }
    }
    
    // Should we allow deletion of customerProfiles?
    
    
    /*
     * customerProfile.chargeCard($amount, $paymentProfileId, $refId)
     *
     * Creates and executes an api request to charge the customers default card
     *
     * $amount -> the amount to charge the card for
     * $paymentProfileId -> The specific card to charge, api will attempt to use default if available
     * $refId -> the refrence ID for the transaction (defaults to unix timestamp, optional)
     *
     */
    
    public function chargeCard($amount, $paymentProfileId = null, $refId = null, $invoice_number = null, $description = null, $authType = "authCaptureTransaction") {
        // set default refID if not provided.
        if (!$refId){
            $date = new DateTime();
            $refId = $date->getTimeStamp();
        }
        
        // generate the bare minmum of a valid api
        $api_array = [
            "refId" => $refId,
            "transactionRequest" => [
                "transactionType" => $authType,
                "amount" => $amount,
                "profile" => [
                    "customerProfileId" => $this->customerProfileId
                ]
            ]
        ];
        
        // add the paymentProfile if provided
        if (!!$paymentProfileId) {
            $api_array['transactionRequest']['profile']['paymentProfile'] = ['paymentProfileId' => $paymentProfileId];
        }
        
        // if (!!$invoice_number) {
        //     $api_array['transactionRequest']['order']['invoiceNumber'] = $invoice_number;
        // }
        
        if (!!$description) {
            $api_array['transactionRequest']['order']['description'] = $description;
        }
        
        // create a authorized api request
        $api_array = $this->auth->createApiCall(
            "createTransactionRequest", $api_array
        );
        
        //set the data to the api post and execute it
        $this->api->setPostData($api_array);
        $api_data = $this->api->execute();
        // print the refId for now
        //echo "refId: " . $api_data['refId'] . "\n";
        // handle errors:
        if ($api_data['messages']['resultCode'] == Constants::RESPONSE_OK) {
            //print_r($api_data['transactionResponse']); // TODO save data in transactions table?
            if ($api_data['transactionResponse']['responseCode'] != "1") {
                
                $errors = $api_data['transactionResponse']['errors'][0];
                return "Error(". $errors["errorCode"] . "): " . $errors["errorText"];

            } else {
                return $api_data;
            }
        } else {
            $status = $api_data['messages']['resultCode'];
            $code = $api_data['messages']['message'][0]['code'];
            $text = $api_data['messages']['message'][0]['text'];
            
            return "$status ($code): $text";
        }
    }
}
?>