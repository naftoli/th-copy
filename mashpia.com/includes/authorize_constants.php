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
	private static $MERCHANT_SANDBOX_LOGIN_ID = "6ZvKUVx425pQ";
	private static $MERCHANT_SANDBOX_TRANSACTION_KEY = "7kfzP7LuQA358N5y";
	// merchant account credentials production (move to ENV variables for security)
	private static $MERCHANT_LOGIN_ID = "4FW7gsD8Tr";
	private static $MERCHANT_TRANSACTION_KEY = "933Q86GEy6u8PcQP";
	
	private static $SANDBOX_API_URL = "https://apitest.authorize.net/xml/v1/request.api";
	//private static $API_URL = "https://apitest.authorize.net/xml/v1/request.api";
	private static $API_URL = "https://api.authorize.net/xml/v1/request.api";
	
	// get the login id (sandbox or production)
	static function GetMerchantLoginID() {
		// if (defined("AUTHORIZE_NET_SANDBOX")){
		// 	return self::$MERCHANT_SANDBOX_LOGIN_ID;
		// } else {
			return self::$MERCHANT_LOGIN_ID;
		//}
	}
	
	// get the transaction key (sandbox or production)
	static function GetMerchantTransactionKey() {
		// if (defined("AUTHORIZE_NET_SANDBOX")){
		// 	return self::$MERCHANT_SANDBOX_TRANSACTION_KEY;
		// } else {
			return self::$MERCHANT_TRANSACTION_KEY;
		//}
	}
	
	// Set to the test endpoint, Please change before deployment
	static function GetApiEndpoint() {
		// if (defined("AUTHORIZE_NET_SANDBOX")){
		// 	return self::$SANDBOX_API_URL;
		// } else {
			return self::$API_URL;
		// }
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