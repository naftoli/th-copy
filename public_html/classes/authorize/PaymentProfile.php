<?php

namespace classes\authorize;

// load the custom authorization tools for the Authorize.net API
require_once( dirname(__FILE__).'/Auth.php' );
require_once( dirname(__FILE__).'/AuthorizeAPIRequest.php' );

use classes\authorize;
use includes\authorize\AuthorizeConstants as Constants;

class PaymentProfile {
    // instance variables
    public $customerPaymentProfileId = "";
    public $customerProfileId = '';
    public $billTo = null;
    public $cardNumber = null;
    public $expirationDate = "";
    public $cardCode = "";
    public $cardType = "";
    public $default_card = false;
    public $live = true;
    
    private $api = null;
    
    /*
     * new PaymentProfile($api, $customerPaymentProfileId, $customerProfileId)
     *
     * Constructor for PaymentProfile instances
     *
     * $api -> instance of the API object it can call.
     * $customerPaymentProfileId => the profile id from the api
     * $customerProfileId => the profile id for the related customer from the api
     * 
     */
    function __construct($customerPaymentProfileId=false, $customerProfileId=false, $loadFromAPI=true, $api=null){
        // Create an instance of the auth object for the user to authenticate api requests
        $this->auth = new Auth();
        // set the internal api instance to equal the one that was passed in.
        if (!$api){$api = new AuthorizeAPIRequest();}
        $this->api = $api;
        // if we pass in a profile id on intialization load the data from the API        
        if(!!$customerPaymentProfileId && !!$customerProfileId) {
            $this->customerProfileId = $customerProfileId;
            $this->customerPaymentProfileId = $customerPaymentProfileId;
            if ($loadFromAPI) {$this->load();}
        }
    }
    
    /*
     * PaymentProfile::create(cardNumber, exparation, code, customerProfileId, [billToArray, default])
     *
     * cardnumber => the credit card number
     * exparation => exparation date on that credit card number
     * code => CVV for that credit card number
     * customerProfileId => The Authorize.net profile for the card to be added to
     * billToArray (optional) => an array of billing information, see tests for example (../../tests/authorize/customerProfile.php)
     * default (optional) => should this card be set as the default card on the account.
     *
     */
    public static function create($cardNumber, $exparation, $code, $customerProfileId, $billToArray = null, $default=false, $live = true, $api = null) {
        $auth = new Auth(); // create a new auth for the staic context
        if (!$api){$api = new AuthorizeAPIRequest();} // create a new $api object if not passed in
        // create the basic array
        $api_array = [
            "customerProfileId" => $customerProfileId,
            "paymentProfile" => []
        ];
        // if we get billing information add it to the request
        if ($billToArray) {
            $api_array['paymentProfile']['billto'] = $billToArray;
        }
        // The payment must be after the bill to in the JSON request or it will fail
        $api_array['paymentProfile']["payment"] = [
            "creditCard" => [
              "cardNumber" => $cardNumber,
              "expirationDate" => $exparation,
              "cardCode" => $code
            ]
        ];
        // add it's default status
        $api_array['paymentProfile']["defaultPaymentProfile"] = $default;
        // add the validation mode
        $api_array['validationMode'] = $auth->getValidationMode($live);
        // create an authenticated api call
        $api_array = $auth->createApiCall(
            "createCustomerPaymentProfileRequest",
            $api_array
        );
        // set the post data on the api request
        $api->setPostData($api_array);
        $api_data = $api->execute();
        
        // if it returns data that can be used to make an object. create an object and return it.
        if(array_key_exists("customerProfileId", $api_data) && array_key_exists("customerPaymentProfileId", $api_data)) {
            return new self($api_data["customerPaymentProfileId"], $api_data["customerProfileId"], true, $api); // pass the api in for performance
        } else { //otherwise return the json for now.
            return $api_data;
        }
    }
    
    /*
     * PaymentProfile::createBasicArray(cardNumber, exparation, code, billToArray, default)
     *
     * Creates a basic array for usage in other sections of API
     *
     * cardNumber => the credit card number
     * exparation => the exparation date
     * code => the CVV
     * billToArray (optional) => an array of the users address. Required for canadian payment processors.
     * default (optional) => is this the default card or not? (defaults to false)
     *
     */
    public static function createBasicArray($cardNumber, $expiration, $code, $billToArray = null, $default=false){
        $api_array = array(
                           "customerType" => "individual"
                           );
        // add the bill to array if provided
        if($billToArray){
            $api_array["billTo"] = $billToArray;
        }
        // The payment must be after the bill to in the JSON request or it will fail
        $api_array["payment"] = [
            "creditCard" => [
                "cardNumber" => $cardNumber,
                "expirationDate" => $expiration,
                "cardCode" => $code
            ]
        ];
        //set if default or not
        $api_array["defaultPaymentProfile"] = $default;
        
        return $api_array;
    }
    
    /*
     * paymentProfile.load()
     *
     * Loads the PaymentProfile from the API based on the public variables
     *
     * Takes no paramaters
     *
     */
    public function load() {
        // Generate the JSON to get the API data
        $api_array = $this->auth->createApiCall(
            "getCustomerPaymentProfileRequest",
            [
                "customerProfileId" => $this->customerProfileId,
                "customerPaymentProfileId" => $this->customerPaymentProfileId
            ]
        );
        // return it for now (eventually make the api call and get the data)
        $this->api->setPostData($api_array);
        $api_data = $this->api->execute();
        
        if ($api_data['messages']['message'][0]['code'] == 'I00001'){
            $cc = $api_data['paymentProfile']['payment']['creditCard'];
            $this->cardNumber = $cc['cardNumber'];
            $this->expirationDate = $cc["expirationDate"];
            $this->cardType = $cc['cardType'];
            if(array_key_exists('billTo', $api_data['paymentProfile'])){
                $this->billTo = $api_data['paymentProfile']['billTo'];
            }
        }
    }
    
    /*
     * paymentProfile.update()
     *
     * updates the payment profile based on the public variables
     *
     * takes no paramaters
     *
     * returns the response if errors have occured.
     *
     * So proper usage would be $errors = paymentProfile.update(); and then you can check for errors.
     *
     */
    
    public function update() {
        $api_array = [
            "customerProfileId" => $this->customerProfileId,
            "paymentProfile"=>[]
        ];
        // Only add the bill to if it was set
        if (!!$this->billTo){
            $api_array["paymentProfile"]["billTo"] = $this->billTo;
        }
        // If we have a new number set the card number in the update
        if ($this->cardNumber && $this->expirationDate) {
            $api_array["paymentProfile"]["payment"] = [
                "creditCard"=>[
                    "cardNumber"=>$this->cardNumber,
                    "expirationDate" => $this->expirationDate,
                ]
            ];
            if($this->cardCode) {
                $api_array["paymentProfile"]["payment"]["creditCard"]["cardCode"] = $this->cardCode;
            }
        }
        
        // set the state with its defaultness
        $api_array['paymentProfile']["defaultPaymentProfile"] = $this->default_card;
        // add the validation mode
        $api_array['validationMode'] = $this->auth->getValidationMode($this->live);
        //add the customer payment profile id
        $api_array['paymentProfile']["customerPaymentProfileId"] = $this->customerPaymentProfileId;
        // create authenticated api call
        
        $api_array = $this->auth->createApiCall("updateCustomerPaymentProfileRequest", $api_array);
        
        $this->api->setPostData($api_array);
        $api_data = $this->api->execute();
        
        if($api_data['messages']['resultCode'] != Constants::RESPONSE_OK || $api_data['messages']['message'][0]["code"] != "I00001"){
            return $api_data;
        } else {
            return null;
        }
    }
}

?>