<?php
class AuthorizeNet
{
	function process_transaction($arrParams)
	{
		if (!isset($arrParams['card_num']))
		{
			print "Sorry, there was an error: MAB-PT101-gsd15db";
			exit;
		}
		if (!isset($arrParams['exp_date']))
		{
			print "Sorry, there was an error: MAB-PT102-sfsd33";
			exit;
		}
		if (!isset($arrParams['amount']))
		{
			print "Sorry, there was an error: MAB-PT103-df7jfd";
			exit;
		}
		if (!isset($arrParams['first_name']))
		{
			print "Sorry, there was an error: MAB-PT104-99d9dd";
			exit;
		}
		if (!isset($arrParams['last_name']))
		{
			print "Sorry, there was an error: MAB-PT105-2f3dsd";
			exit;
		}
		/*if (!isset($arrParams['address']))
		{
			print "Sorry, there was an error: MAB-PT106-8h6urt";
			exit;
		}
		if (!isset($arrParams['state']))
		{
			print "Sorry, there was an error: MAB-PT107-f2dsdf";
			exit;
		}
		if (!isset($arrParams['zip']))
		{
			print "Sorry, there was an error: MAB-PT108-89fdg7";
			exit;
		}*/
		if (!isset($arrParams['description']))
			$arrParams['description'] = '';
        $strURL = "https://secure.authorize.net/gateway/transact.dll";
        $arrPost = array(
			"x_login"				=> "4FW7gsD8Tr",
			"x_tran_key"			=> "6f7z4c79NMLU4293",
			"x_version"             => "3.1",
			"x_delim_data"          => "TRUE",
			"x_delim_char"          => "|",
			"x_relay_response"      => "FALSE",
			"x_type"                => "AUTH_CAPTURE",
			"x_method"              => "CC",
			"x_card_num"            => $arrParams['card_num'],
			"x_exp_date"            => $arrParams['exp_date'],
			"x_amount"              => $arrParams['amount'],
			"x_description"         => $arrParams['description'],
			"x_first_name"          => $arrParams['first_name'],
			"x_last_name"           => $arrParams['last_name'],
			"x_address"             => @$arrParams['address'],
			"x_state"               => @$arrParams['state'],
			"x_zip"                 => @$arrParams['zip']
        );
        $strPost = "";
        foreach($arrPost as $key => $value)
        {
			$strPost .= "$key=" . urlencode( $value ) . "&";
		}
        $strPost = rtrim( $strPost, "& " );
        $objRequest = curl_init($strURL); // initiate curl object
		curl_setopt($objRequest, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($objRequest, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($objRequest, CURLOPT_POSTFIELDS, $strPost); // use HTTP POST to send form data
		curl_setopt($objRequest, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		$strResponse = curl_exec($objRequest); // execute curl post and store results in $post_response
        curl_close ($objRequest); // close curl object
        $arrResponse = explode($arrPost["x_delim_char"],$strResponse);
		$arrColumns = array(
			"Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text",
			"Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description",
			"Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name",
			"Cardholder Last Name", "Company", "Billing Address", "City", "State",
			"Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name",
			"Ship to Company", "Ship to Address", "Ship to City", "Ship to State",
			"Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount",
			"Tax Exempt Flag", "PO Number", "MD5 Hash",
			"Card Code (CVV2/CVC2/CID) Response Code",
			"Cardholder Authentication Verification Value (CAVV) Response Code"
		);
		$arrRows = array();
		foreach ($arrResponse as $intKey => $mixedValue)
		{
			if (!isset($arrColumns[$intKey]))
				continue;
			$arrRows[$arrColumns[$intKey]] = $mixedValue;
		}
		$arrRows['response_code_interpreted'] = 'error';
        switch ($arrResponse[0]) {
			case 1:
				$arrRows['response_code_interpreted'] = "Approved";
				break;
			case 2:
				$arrRows['response_code_interpreted'] = "Declined";
				break;
			case 3:
				$arrRows['response_code_interpreted'] = "Error";
				break;
			case 4:
				$arrRows['response_code_interpreted'] = "Held for Review";
				break;
		}
		return $arrRows;
	}
}
?>