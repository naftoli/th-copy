<?php
namespace includes\authorize;

/*
 *
 * Autnorize.net constants
 *
 * This file defines the constants used by the Authorize.net API calls.
 *
 */


class AuthorizeConstants
{
	//merchant credentials - sandbox
	// private static $MERCHANT_SANDBOX_LOGIN_ID = "6ZvKUVx425pQ";
	// private static $MERCHANT_SANDBOX_TRANSACTION_KEY = "7kfzP7LuQA358N5y";
	private static $MERCHANT_SANDBOX_LOGIN_ID = "3YFKr8d8SMhs";
	private static $MERCHANT_SANDBOX_TRANSACTION_KEY = "3QrA8qAcc47T8p7D";
	private static $SANDBOX_API_URL = "https://apitest.authorize.net/xml/v1/request.api";

	// merchant account credentials production (move to ENV variables for security)
	private static $MERCHANT_LOGIN_ID = "4FW7gsD8Tr";
	private static $MERCHANT_TRANSACTION_KEY = "933Q86GEy6u8PcQP";
	private static $API_URL = "https://api.authorize.net/xml/v1/request.api";
	
	// get the login id (sandbox or production)
	static function GetMerchantLoginID($sandbox = false) {
		return $sandbox ? self::$MERCHANT_SANDBOX_LOGIN_ID : self::$MERCHANT_LOGIN_ID;
	}
	
	// get the transaction key (sandbox or production)
	static function GetMerchantTransactionKey($sandbox = false) {
		return $sandbox ? self::$MERCHANT_SANDBOX_TRANSACTION_KEY : self::$MERCHANT_TRANSACTION_KEY;
	}
	
	// get the environment (sandbox or production)
	static function GetApiEndpoint($sandbox = false) {
		return $sandbox ? self::$SANDBOX_API_URL : self::$API_URL;
	}
	
	const RESPONSE_OK = "Ok";
    
    // SAMPLE CONSTANTS FROM DEMO. UPDATE WITH VALID TOKENS
	
	//Recurring Billing
	//const SUBSCRIPTION_ID_GET = "2930242";
	//Transaction Reporting
	//const TRANS_ID = "2238968786";
	//const SAMPLE_AMOUNT = "2.23";
}

?>