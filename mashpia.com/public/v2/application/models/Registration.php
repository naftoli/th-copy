<?php
class Registration
{
	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		$this->_tools = new ToolsModels();
   	}

	public function processAutherizeNet($arrParams)
	{
		if (!isset($arrParams["creditcard_number"]))
		{
			print "Sorry, there was an error MPP-PAN101-FF3FEE";
			exit;
		}
		if (!isset($arrParams["creditcard_name"]))
		{
			print "Sorry, there was an error MPP-PAN102-FEWW22";
			exit;
		}
		if (!isset($arrParams["creditcard_ccv"]))
		{
			print "Sorry, there was an error MPP-PAN103-FFDSRG";
			exit;
		}
		if (!isset($arrParams["creditcard_expiration_month"]))
		{
			print "Sorry, there was an error MPP-PAN104-DF0DGG";
			exit;
		}
		if (!isset($arrParams["creditcard_expiration_year"]))
		{
			print "Sorry, there was an error MPP-PAN105-DF3F3F";
			exit;
		}
		if (!isset($arrParams["billing_first_name"]))
		{
			print "Sorry, there was an error MPP-PAN106-DFCVDS";
			exit;
		}
		if (!isset($arrParams["billing_last_name"]))
		{
			print "Sorry, there was an error MPP-PAN107-DFEDXS";
			exit;
		}

		// http://developer.authorize.net


		/*

		$post_url = "https://secure.authorize.net/gateway/transact.dll";
		$submit 		= mysql_real_escape_string($_GET['submit']);
		$school_id 		= mysql_real_escape_string($_GET['school_id']);
		$amount 		= mysql_real_escape_string($_GET['cc_amount']);
		$description 	= mysql_real_escape_string($_GET['cc_description']);
		$card_num 		= mysql_real_escape_string($_GET['ccnum']);
		$exp_date 		= mysql_real_escape_string($_GET['ccexp']);
		$first_name 	= mysql_real_escape_string($_GET['cc_first_name']);
		$last_name 		= mysql_real_escape_string($_GET['cc_last_name']);
		$email 			= mysql_real_escape_string($_GET['email']);
		$address 		= mysql_real_escape_string($_GET['cc_address']);
		$state 			= mysql_real_escape_string($_GET['cc_state']);
		$zip 			= mysql_real_escape_string($_GET['cc_zip']);

		$post_values = array(
		// the API Login ID and Transaction Key must be replaced with valid values
			"x_login"            => "4FW7gsD8Tr",
			"x_tran_key"         => "6f7z4c79NMLU4293",
			"x_version"             => "3.1",
			"x_delim_data"          => "TRUE",
			"x_delim_char"          => "|",
			"x_relay_response"      => "FALSE",
			"x_type"                => "AUTH_CAPTURE",
			"x_method"              => "CC",
			"x_card_num"            => $card_num ,
			"x_exp_date"            => $exp_date ,
			"x_amount"              => $amount,
			"x_description"         => $description,
			"x_first_name"          => $first_name,
			"x_last_name"           => $last_name,
			"x_address"             => $address,
			"x_state"               => $state,
			"x_zip"                 => $zip
		);


		*/

		return 43412313;
	}

	public function _registration_orders_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"registration_order_id"		=> @$arrParams["registration_order_id"],
			"user_confirmation_code"	=> @$arrParams["user_confirmation_code"],
			"api_confirmation_code"		=> @$arrParams["api_confirmation_code"],
			"institution_id"			=> @$arrParams["institution_id"],
			"user_id"					=> @$arrParams["user_id"],
			"template_style"			=> @$arrParams["template_style"],
			"administrator_first_name"	=> @$arrParams["administrator_first_name"],
			"administrator_last_name"	=> @$arrParams["administrator_last_name"],
			"administrator_email"		=> @$arrParams["administrator_email"],
			"administrator_phone_number"	=> @$arrParams["administrator_phone_number"],
			"administrator_address"	=> @$arrParams["administrator_address"],
			"administrator_city"	=> @$arrParams["administrator_city"],
			"administrator_postal"	=> @$arrParams["administrator_postal"],
			"administrator_state"	=> @$arrParams["administrator_state"],
			"administrator_country"	=> @$arrParams["administrator_country"],
			"administrator_phone"	=> @$arrParams["administrator_phone"],
			"institution_name"		=> @$arrParams["institution_name"],
			"institution_address"	=> @$arrParams["institution_address"],
			"institution_city"		=> @$arrParams["institution_city"],
			"institution_state"		=> @$arrParams["institution_state"],
			"institution_postal"	=> @$arrParams["institution_postal"],
			"institution_country"	=> @$arrParams["institution_country"],
			"institution_phone"		=> @$arrParams["institution_phone"],
			"institution_email"		=> @$arrParams["institution_email"],
			"institution_website"	=> @$arrParams["institution_website"],
			"kioskaccessories_regular"			=> @$arrParams["kioskaccessories_regular"],
			"kioskaccessories_sponsored"		=> @$arrParams["kioskaccessories_sponsored"],
			"kioskaccessories_rental"			=> @$arrParams["kioskaccessories_rental"],
			"kioskaccessories_scanner"			=> @$arrParams["kioskaccessories_scanner"],
			"kioskaccessories_handbook"			=> @$arrParams["kioskaccessories_handbook"],
			"billing_first_name"	=> @$arrParams["billing_first_name"],
			"billing_last_name"		=> @$arrParams["billing_last_name"],
			"billing_phone_number"		=> @$arrParams["billing_phone_number"],
			"billing_address"		=> @$arrParams["billing_address"],
			"billing_city"			=> @$arrParams["billing_city"],
			"billing_postal"		=> @$arrParams["billing_postal"],
			"billing_state"			=> @$arrParams["billing_state"],
			"billing_country"		=> @$arrParams["billing_country"],
			"shipping_first_name"	=> @$arrParams["shipping_first_name"],
			"shipping_last_name"	=> @$arrParams["shipping_last_name"],
			"shipping_phone_number"	=> @$arrParams["shipping_phone_number"],
			"shipping_address"		=> @$arrParams["shipping_address"],
			"shipping_city"			=> @$arrParams["shipping_city"],
			"shipping_postal"		=> @$arrParams["shipping_postal"],
			"shipping_state"		=> @$arrParams["shipping_state"],
			"shipping_country"		=> @$arrParams["shipping_country"],
			"creditcard_name"				=> @$arrParams["creditcard_name"],
			"creditcard_number"				=> @$arrParams["creditcard_number"],
			"creditcard_expiration_month"				=> @$arrParams["creditcard_expiration_month"],
			"creditcard_expiration_year"				=> @$arrParams["creditcard_expiration_year"],
			"creditcard_ccv"					=> @$arrParams["creditcard_ccv"],
			"created"				=> date("Y-m-d H:i:S")
		);

		$strSql = "
			SELECT
				*
			FROM
				registration_orders
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
					|| $Value
				)
			) {
				if (!is_int($Value))
				{
					$Value = '"' . $Value . '"';
				}
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _registration_orders_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id ? $this->_user_session_data->user_id : 0;
		}

		$arrFeilds = array (
			"user_confirmation_code"	=> @$arrParams["user_confirmation_code"],
			"api_confirmation_code"		=> @$arrParams["api_confirmation_code"],
			"institution_id"			=> @$arrParams["institution_id"],
			"user_id"					=> @$arrParams["user_id"],
			"template_style"			=> @$arrParams["template_style"],
			"administrator_first_name"	=> @$arrParams["administrator_first_name"],
			"administrator_last_name"	=> @$arrParams["administrator_last_name"],
			"administrator_email"		=> @$arrParams["administrator_email"],
			"administrator_phone_number"	=> @$arrParams["administrator_phone_number"],
			"administrator_cell_phone"	=> @$arrParams["administrator_cell_phone"],
			"administrator_address"	=> @$arrParams["administrator_address"],
			"administrator_city"	=> @$arrParams["administrator_city"],
			"administrator_postal"	=> @$arrParams["administrator_postal"],
			"administrator_state"	=> @$arrParams["administrator_state"],
			"administrator_country"	=> @$arrParams["administrator_country"],
			"institution_name"		=> @$arrParams["institution_name"],
			"institution_address"	=> @$arrParams["institution_address"],
			"institution_city"		=> @$arrParams["institution_city"],
			"institution_state"		=> @$arrParams["institution_state"],
			"institution_postal"	=> @$arrParams["institution_postal"],
			"institution_country"	=> @$arrParams["institution_country"],
			"institution_phone"		=> @$arrParams["institution_phone"],
			"institution_email"		=> @$arrParams["institution_email"],
			"institution_website"	=> @$arrParams["institution_website"],
			"kioskaccessories_regular"			=> @$arrParams["kioskaccessories_regular"],
			"kioskaccessories_sponsored"		=> @$arrParams["kioskaccessories_sponsored"],
			"kioskaccessories_rental"			=> @$arrParams["kioskaccessories_rental"],
			"kioskaccessories_scanner"			=> @$arrParams["kioskaccessories_scanner"],
			"kioskaccessories_handbook"			=> @$arrParams["kioskaccessories_handbook"],
			"billing_first_name"	=> @$arrParams["billing_first_name"],
			"billing_last_name"		=> @$arrParams["billing_last_name"],
			"billing_phone_number"		=> @$arrParams["billing_phone_number"],
			"billing_address"		=> @$arrParams["billing_address"],
			"billing_city"			=> @$arrParams["billing_city"],
			"billing_postal"		=> @$arrParams["billing_postal"],
			"billing_state"			=> @$arrParams["billing_state"],
			"billing_country"		=> @$arrParams["billing_country"],
			"shipping_first_name"	=> @$arrParams["shipping_first_name"],
			"shipping_last_name"	=> @$arrParams["shipping_last_name"],
			"shipping_address"		=> @$arrParams["shipping_address"],
			"shipping_city"			=> @$arrParams["shipping_city"],
			"shipping_postal"		=> @$arrParams["shipping_postal"],
			"shipping_state"		=> @$arrParams["shipping_state"],
			"shipping_country"		=> @$arrParams["shipping_country"],
			"creditcard_name"				=> @$arrParams["creditcard_name"],
			"creditcard_number"				=> @$arrParams["creditcard_number"],
			"creditcard_expiration_month"				=> @$arrParams["creditcard_expiration_month"],
			"creditcard_expiration_year"				=> @$arrParams["creditcard_expiration_year"],
			"creditcard_ccv"					=> @$arrParams["creditcard_ccv"],
			"created"				=> date("Y-m-d H:i:S")
		);

		// Execute
		$boolResult = $this->_db->insert("registration_orders", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

}
?>