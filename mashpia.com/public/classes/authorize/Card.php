<?php
namespace classes\authorize;

use classes\authorize;
use \Datetime;

// load the custom authorization tools for the Authorize.net API
require_once( dirname(__FILE__).'/Auth.php' ); // loads the constants class in this namespace as well
require_once( dirname(__FILE__).'/AuthorizeAPIRequest.php' );

use includes\authorize\AuthorizeConstants as Constants;

class Card {
    private $auth = null; // internal instance of the authentication class.
    private $api = null;

    public function __construct() {
        // Create an instance of the auth object for the user to authenticate api requests
        $this->auth = new Auth();
        $this->api = new AuthorizeAPIRequest();
    }

    public function charge($cc_info, $amount, $description = null, $authType = 'capture') {
        // set default refID if not provided.
        $date = new DateTime();
        $refId = $date->getTimeStamp();
        $authTypes = [
            'auth' => 'authOnlyTransaction',
            'capture' => 'authCaptureTransaction',
        ];
        
        // generate the bare minmum of a valid api
        $api_array = [
            "refId" => $refId,
            "transactionRequest" => [
                "transactionType" => $authTypes[$authType],
                "amount" => $amount,
                "payment" => [
                    "creditCard" => [
                        "cardNumber" => $cc_info['num'],
                        "expirationDate" => $cc_info['exp'],
                        "cardCode" => $cc_info['cvv']
                    ]
                ],
            ],
        ];

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

        if (isset($api_data['transactionResponse']['errors'])) {
            $error = $api_data['transactionResponse']['errors'][0];
            return "Error(" . $error['errorCode'] . "): " . $error['errorText'];
        } else if ($api_data['messages']['resultCode'] != Constants::RESPONSE_OK) {
            $status = $api_data['messages']['resultCode'];
            $code = $api_data['messages']['message'][0]['code'];
            $text = $api_data['messages']['message'][0]['text'];
            return "$status ($code): $text";
        } else {
            return $api_data;
        }
    }
}