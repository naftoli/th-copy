<?php
namespace classes\authorize;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// load the constants
require_once( dirname(__FILE__) . "/../../../includes/authorize_constants.php" );

use includes\authorize\AuthorizeConstants as Constants;

//debugging
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

class Auth {
    public $merchantAuthentication;
    
    function __construct() {
        $this->merchantAuthentication = [
            "merchantAuthentication" => [
               "name"=>Constants::GetMerchantLoginID(),
               "transactionKey"=>Constants::GetMerchantTransactionKey()
               ]
            ];
    }
    
    /*
     * auth.crateApiCall(command, array)
     *
     *  Merges authentication data with the rest of the api command.
     *
     * command => the string of the top level command to send to the api
     * array => the data being sent to the api
     *
     */
    
    function createApiCall($command, $array){
        return array(
            $command=>array_merge(
                $this->merchantAuthentication,
                $array
            )
        );
    }
    
    /*
     * Auth::getValidatonMode() // returns the validation mode for credit cards based on the passed in boolean.
     *
     * Live => Creates a transaction from 0 to $1 and voids it to validate card
     * Test => Validates card number with mathamatical check
     * 
    */
     
    
    function getValidationMode($live){
        return $live ? "liveMode" : "testMode";
    }
}

?>