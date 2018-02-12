<?php
class RegistrationController extends Zend_Controller_Action
{
	private $arrInstitutionTypes = array(
		"School",
		"Camp"
	);

	public function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (isset($this->_user_session_data->user_id))
		{
			unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
			$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));
			
			/*
			// Load thie session array
			if (
				!$this->_user_session_data->user_id
				|| !$this->_user_session_data->permission_id
				|| !$this->_user_session_data->permission
				|| !$this->_user_session_data->institution_id
			)
				$this->_redirect('logout/index/' . $strParam);
			$this->objPermission = first($query->permissions__select(array(
				"user_id" => $this->_user_session_data->user_id,
				"permission_id" => $this->_user_session_data->permission_id,
				"permission" => $this->_user_session_data->permission,
				"institution_id" => $this->_user_session_data->institution_id
			)));
			if (!$this->objPermission)
				$this->_redirect('logout/index/' . $strParam);
			*/
		}
	}

	public function indexAction()
	{
		exit;
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}
	public function index2Action()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}
	public function shippingcostsAction()
	{
		$objUPS = new UPS();
		$objUPS->load();
		exit;
	}

	public function selectbrandAction()
	{
		$query = new QueryGen();
		$strTstyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->arrNetworks = $arrNetworks = array_stack('network_keyword', $query->networks__select(array(
			'_COLUMNS' => array('network_keyword'),
			'_ALL' => TRUE
		)));
		$this->view->arrPermissions = array_hash('template_style', $query->permissions__select(array(
			"user_id" => $this->_user_session_data->user_id,
			"template_style" => $arrNetworks,
			"permissions" => 'Institution Administrator'
		)));
	}

	public function loginAction()
	{
		$query = new QueryGen();
		$objUsers = new Users();
		$strTstyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrPost["email"] = trim($arrPost["email"]);
			if (empty($arrPost["email"]))
			{
				print json_encode(array(
					'error' => 'Please enter your email'
				));
			    exit;
			}
			else if (!preg_match("/^[^@]+@.+?\.[^\.]+$/", $arrPost["email"]))
			{
				print json_encode(array(
					'error' => 'Please enter a valid email address'
				));
			    exit;
			}
			else if (empty($arrPost["password"]))
			{
				print json_encode(array(
					'error' => 'Please enter your password'
				));
			    exit;
			}

			$intAuthenticate = $objUsers->Authenticate($arrPost["email"], $arrPost["password"]);
			if ($intAuthenticate == 1)
			{
				$objNewSession = new Zend_Session_Namespace('user_session_data');
				$objUser = first($query->users__select(array(
					"user_id" => $objNewSession->user_id
				)));
				unset($objUser->password);
				// Make sure that this user doesnt already have this program
				$arrNetworks = array_stack('network_keyword', $query->networks__select(array(
					'_COLUMNS' => array('network_keyword'),
					'_ALL' => TRUE
				)));
				$arrPermissions = array_hash('template_style', $query->permissions__select(array(
					"user_id" => $objNewSession->user_id,
					"template_style" => $arrNetworks,
					"permissions" => 'Institution Administrator'
				)));
				if (count($arrPermissions) == count($arrNetworks))
				{
					print json_encode(array(
						'error' => "It seems you're already registered to all brands available."
					));
					exit;
				}

				print json_encode(array(
					'success' => 'true',
					'objUser' => $objUser
				));
				exit;
			}
			else if ($intAuthenticate == 0)
			{
				print json_encode(array(
					'error' => 'Authentication error'
				));
				exit;
			}
			else if ($intAuthenticate == -1)
			{
				print json_encode(array(
					'error' => 'Invalid email address'
				));
				exit;
			}
			else if ($intAuthenticate == -3)
			{
				print json_encode(array(
					'error' => 'Invalid password'
				));
				exit;
			}
			else if ($intAuthenticate == -999)
			{
				print json_encode(array(
					'error' => 'Inactive user'
				));
				exit;
			}
			else
			{
				print json_encode(array(
					'error' => 'Authentication error'
				));
				exit;
			}
		}
	}

	public function administratorAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();

			$arrResult = $this->administratorValidate($arrPost);
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
			}
			else
			{
				print json_encode(array(
					"success" => "true"
				));
			}
			exit;
		}
	}

	private function administratorValidate($arrParams)
	{
		$query = new QueryGen();
		$objUsers = new Users();
		$arrResult = array();
		if (!isset($arrParams["administrator_first_name"]) || !strlen($arrParams["administrator_first_name"]))
		{
			$arrResult["error"]["administrator_first_name"] = "First name is a required field.";
		}
		if (!isset($arrParams["administrator_last_name"]) || !strlen($arrParams["administrator_last_name"]))
		{
			$arrResult["error"]["administrator_last_name"] = "Last name is a required field.";
		}
		if (!isset($arrParams["administrator_cell_phone"]) || !strlen($arrParams["administrator_cell_phone"]))
		{
			$arrResult["error"]["administrator_cell_phone"] = "Cell phone is a required field.";
		}
		if (!isset($arrParams["administrator_email"]) || !strlen($arrParams["administrator_email"]))
		{
			$arrResult["error"]["administrator_email"] = "Email is a required field.";
		}
		else if (!preg_match("/^.+?@.+?\.[a-z]{2,8}$/i", $arrParams["administrator_email"]))
		{
			$arrResult["error"]["administrator_email"] = "The email address provided appears to be invalid.";
		}
		/*
		else
		{
			$objUser = first($objUsers->_users_select(array(
				"email" => $arrParams["administrator_email"]
			)));
			$arrPermissions = first($query->permissions__select(array(
				'user_id' => $objUser->user_id,
				'template_style' => $this->_request->getParam("tstyle")
			)));
			if ($arrPermissions)
			{
				$arrResult["error"]["administrator_email"] = "This email already registered here.";
			}
		}
		*/
		return $arrResult;
	}

	public function institutionAction()
	{
		$objInstitutions = new Institutions();
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->arrInstitutionTypes = $this->arrInstitutionTypes;
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrResult = $this->institutionValidate($arrPost);
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
			}
			else
			{
				print json_encode(array(
					"success" => "true"
				));
			}
			exit;
		}
	}

	private function institutionValidate($arrParams)
	{
		$objInstitutions = new Institutions();
		$arrResult = array();
		if (!isset($arrParams["institution_name"]))
		{
			$arrResult["error"]["institution_name"] = "Institution name is required.";
		}
		if (strlen($arrParams["institution_name"]) < 4)
		{
			$arrResult["error"]["institution_name"] = "The institution name must be at least 4 characters long.";
		}
		else
		{
			$objInstitution = first($objInstitutions->_institutions_select(array(
				"name" => $arrParams["institution_name"]
			)));
			if ($objInstitution)
			{
				$arrResult["error"]["institution_name"] = "This institution name is already being used in our system, please choose another.";
			}
		}
		if (empty($arrParams["institution_email"]))
		{
			$arrResult["error"]["institution_email"] = "An institution email must be provided.";
		}
		if (!preg_match("/^.+?@.+?\.[a-z]{2,8}$/i", $arrParams["institution_email"]))
		{
			$arrResult["error"]["institution_email"] = "The email address provided appears to be invalid.";
		}
		if (empty($arrParams["institution_phone"]))
		{
			$arrResult["error"]["institution_phone"] = "An institution phone number must be provided.";
		}
		//institution_phone
		return $arrResult;
	}

	public function kioskaccessoriesAction()
	{
		global $arrAppDetails;
		$strStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		// redirect if specified in app details
		$arrUrlParams = $this->_request->getParams();
		unset($arrUrlParams['module']);
		unset($arrUrlParams['action']);
		unset($arrUrlParams['controller']);
		$strExtra = preg_replace('/=/', '/', http_build_query($arrUrlParams, '', '/'));
		$arrApp = $arrAppDetails[$strStyle];
		if (isset($arrApp['registration']['kioskaccessories']))
			$this->_redirect('registration/' . $arrApp['registration']['kioskaccessories'] . '/' . $strExtra);
	}
	public function kioskaccessorieschabadhebrewschoolAction()
	{
		$strStyle = $this->view->tstyle = $this->_request->getParam("tstyle");

	}

	public function paymentAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrResult = $this->paymentValidate($arrPost);
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
			}
			else
			{
				print json_encode(array(
					"success" => "true"
				));
			}
			exit;
		}
	}

	private function paymentValidate($arrParams)
	{
		$arrResult = array();
		if (!isset($arrParams["billing_first_name"]) || !strlen($arrParams["billing_first_name"]))
		{
			$arrResult["error"]["billing_first_name"] = "Billing first name is required.";
		}
		if (!isset($arrParams["billing_last_name"]) || !strlen($arrParams["billing_last_name"]))
		{
			$arrResult["error"]["billing_last_name"] = "Billing last name is required.";
		}
		if (!isset($arrParams["billing_address"]) || !strlen($arrParams["billing_address"]))
		{
			$arrResult["error"]["billing_address"] = "Billing address is required.";
		}
		if (!isset($arrParams["billing_postal"]) || !strlen($arrParams["billing_postal"]))
		{
			$arrResult["error"]["billing_postal"] = "Billing zip/postal is required.";
		}

		if (!isset($arrParams["shipping_address"]) || !strlen($arrParams["shipping_address"]))
		{
			$arrResult["error"]["shipping_address"] = "Shipping address is required.";
		}
		if (!isset($arrParams["shipping_city"]) || !strlen($arrParams["shipping_city"]))
		{
			$arrResult["error"]["shipping_city"] = "City is required.";
		}
		if (!isset($arrParams["shipping_postal"]) || !strlen($arrParams["shipping_postal"]))
		{
			$arrResult["error"]["shipping_postal"] = "Zip/Postal code is required.";
		}
		if (!isset($arrParams["shipping_state"]) || !strlen($arrParams["shipping_state"]))
		{
			$arrResult["error"]["shipping_state"] = "State is required.";
		}
		if (!isset($arrParams["shipping_country"]) || !strlen($arrParams["shipping_country"]))
		{
			$arrResult["error"]["shipping_country"] = "Country is required.";
		}

		if (!isset($arrParams["creditcard_number"]) || !strlen($arrParams["creditcard_number"]))
		{
			$arrResult["error"]["creditcard_number"] = "Credit card number is required.";
		}
		if (!isset($arrParams["creditcard_ccv"]) || !strlen($arrParams["creditcard_ccv"]))
		{
			$arrResult["error"]["creditcard_ccv"] = "CCV is required.";
		}
		return $arrResult;
	}

	public function confirmAction()
	{
		$strTemplateStyle = $this->view->tstyle = $this->_request->getPost("template_style");
		global $arrAppDetails;
		$arrDetails = $arrAppDetails[$strTemplateStyle];
		$query = new QueryGen();
		$objRegistration = new Registration();
		$objUsers = new Users();
		$objInstitutions = new Institutions();
		$objPermissions = new Permissions();
		$objCampaigns = new Campaigns();
		$objStore = new Store();

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$objUser = false;
			if (isset($arrPost["administrator_user_id"]) && $arrPost["administrator_user_id"])
			{
				$arrPost["administrator_user_id"] = intval($arrPost["administrator_user_id"]);
				$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

				if ($this->_user_session_data->user_id != $arrPost["administrator_user_id"])
				{
					print json_encode(array(
						"error" => "Sorry, it seems your session has died."
					));
					exit;
				}
				if (
					!$this->_user_session_data->user_id
					|| !$this->_user_session_data->permission_id
					|| !$this->_user_session_data->permission
					|| !$this->_user_session_data->institution_id
				) {
					print json_encode(array(
						"error" => "Sorry, it seems your session has died."
					));
					exit;
				}
				$objPermission = first($query->permissions__select(array(
					"user_id" => $this->_user_session_data->user_id,
					"permission_id" => $this->_user_session_data->permission_id,
					"permission" => $this->_user_session_data->permission,
					"institution_id" => $this->_user_session_data->institution_id
				)));
				if (!$objPermission)
				{
					print json_encode(array(
						"error" => text("Sorry, there was an error") . ": CR-C101-FDSVV2"
					));
					exit;
				}
				$objUser = first($query->users__select(array(
					"user_id" => $arrPost["administrator_user_id"]
				)));
			}
			if (!$objUser)
			{
				$arrResult = $this->administratorValidate($arrPost);
				if (isset($arrResult["error"]))
				{
					print json_encode($arrResult);
					exit;
				}
			}
			$arrResult = $this->institutionValidate($arrPost);
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
				exit;
			}
			$arrResult = $this->paymentValidate($arrPost);
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
				exit;
			}

			// Calculate and verify amount
			$intJSAmount = $arrPost['amount'];
			if ($intJSAmount < 100)
			{
				print json_encode(array(
					'error' => "Sorry, there was an error: CR-C104-1asd29",
					"success" => "false"
				));
				exit;
			}

			/*
			 * TODO VERIFY AMOUNT!
			 *
			 *
	kioskaccessories_campers: "25"
	kioskaccessories_scanner: "1"
	kioskaccessories_handbook: "1"
			 */

			$intAmount = $intJSAmount;
			$intCampers = $arrPost['kioskaccessories_campers'];
			// master: fake card = dsd74f70gfd7g9a036bkan5la9gmi4kxo2a
			if ($arrPost['creditcard_number'] == 'dsd74f70gfd7g9a036bkan5la9gmi4kxo2a')
			{
				$arrResponse = array(
					'testing' => 'true'
				);
			}
			else
			{
				//$intAmount = 1;
				$objAuthorizeNet = new AuthorizeNet();
				$arrResponse = $objAuthorizeNet->process_transaction(array(
					'card_num' => $arrPost['creditcard_number'],
					'exp_date' => $arrPost['creditcard_expiration_month'] . '/' . $arrPost['creditcard_expiration_year'],
					'amount' => $intAmount,
					'description' => 'From:' . $strTemplateStyle,
					'first_name' => $arrPost['billing_first_name'],
					'last_name' => $arrPost['billing_last_name'],
					'address' => $arrPost['billing_address'],
					'state' => $arrPost['billing_state'],
					'zip' => $arrPost['billing_postal']
				));
				if ($arrResponse["Response Code"] != '1')
				{
					print json_encode(array(
						'error' => $arrResponse['Response Reason Text'],
						"success" => "false"
					));
					exit;
				}
			}
			$arrPost['creditcard_number'] = substr($arrPost['creditcard_number'], 0, -8);
			$intAuthNetConfirmation = 0;
			//$intAuthNetConfirmation = $objRegistration->processAutherizeNet($arrPost);
			if (1)//if (preg_match("/^[0-9]+$/", $intAuthNetConfirmation))
			{
				// The credit card payment was approved, create all the records!
				$arrDataSource = explode(",", "q,w,e,r,t,y,u,i,o,p,a,s,d,f,g,h,j,k,l,z,x,c,v,b,n,m,1,2,3,4,5,6,7,8,9,0");
				$strTempPassword =
					$arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)];
				$intNewInstitution = $objInstitutions->_institutions_insert(array(
					"template_style" => $strTemplateStyle,
					"host_id" => 1,
					"network_id" => 2,
					"is_active" => 1,
					"name" => $arrPost["institution_name"],
					"image_id" => $arrPost["institution_image_id"],
					"phone" => $arrPost["institution_phone"],
					"email" => $arrPost["institution_email"],
					"website" => $arrPost["institution_website"],
					"created_by" => 7493
				));
				$query->admin_credits__insert(array(
					"institution_id" => $intNewInstitution,
					"user_id" => 0,
					"credit_title" => "student_registration",
					"credit_amount" => $intCampers,
					"credit_description" => "institution registration"
				));
				if (isset($arrPost["package_option"]) && $arrPost["package_option"] == "package_pricing_two")
				{
					$intNextSeasonStart = 1438387200;
					$intNextSeasonDuration = 6 * 30 * 24 * 60 * 60;
					$intNextSeasonStart += $intNextSeasonDuration;
					$query->admin_credits__insert(array(
						"user_id" => $this->_user_session_data->user_id,
						'start_epoch' => $intNextSeasonStart,
						'end_epoch' => $intNextSeasonStart + $intNextSeasonDuration,
						"credit_title" => "student_registration",
						"credit_amount" => $intCampers,
						"credit_description" => "2nd season credits"
					));
				}

				// Install Default ON prizes
				$arrHostPrizes = $objStore->_prizes_select(array(
					"installable_default_on" => 1,
					"is_active" => 1,
					"institution_id" => 1
				));
				foreach ($arrHostPrizes as $objHostPrizes)
				{
					$arrPrize = (array) $objHostPrizes;
					if (!$arrPrize)
					{
						print text("Sorry, there was an error") . ": CR-C101-9F9F9F";
						exit;
					}
					// Install prize
					$arrPrize["institution_id"] = $intNewInstitution;
					$arrPrize["template_prize_id"]	= $arrPrize["prize_id"];
					$arrPrize["prize_id"] = false;
					$arrPrize["created_by"] = 7493;
					$intPrizeAI = $query->prize__insert($arrPrize);
				}

				// Install Default ON campaigns
				$arrHostCampaigns = $objCampaigns->_campaigns_select(array(
					"campaign_type" => "Incremental",
					"institution_id" => 1,
					"is_active" => 1,
					"default_installed" => 1
				));
				foreach ($arrHostCampaigns as $objHostCampaign)
				{
					$arrCampaign = (array) $objHostCampaign;
					$arrCampaign["institution_id"] = $intNewInstitution;
					$arrCampaign["installed_campaign_id"]	= $arrCampaign["campaign_id"];
					$arrCampaign["campaign_id"] = false;
					$arrCampaign["created_by"] = 7493;
					$intCampaignAI = $query->campaigns__insert($arrCampaign);
				}
				$objUser = first($query->users__select(array(
					"email" => $arrPost["administrator_email"]
				)));
				$boolNewUser = FALSE;
				if (!$objUser)
				{
					$boolNewUser = TRUE;
					$intNewUser = $objUsers->_users_insert(array(
						"email" => $arrPost["administrator_email"],
						"password" => md5($strTempPassword),
						"first_name" => $arrPost["administrator_first_name"],
						"last_name" => $arrPost["administrator_last_name"],
						"address" => $arrPost["administrator_address"],
						"city" => $arrPost["administrator_city"],
						"postal" => $arrPost["administrator_postal"],
						"state" => $arrPost["administrator_state"],
						"country" => $arrPost["administrator_country"],
						"phone" => $arrPost["administrator_phone_number"],
						"cell" => $arrPost["administrator_cell_phone"],
						"is_active" => 1
					));
				}
				else
				{
					$intNewUser = $objUser->user_id;
				}
				$intNewPermission = $objPermissions->_permissions_insert(array(
					"user_id" => $intNewUser,
					"institution_id" => $intNewInstitution,
					"permission" => "Institution Administrator",
					"template_style" => $strTemplateStyle,
					"default_permission" => 1
				));

				$intPayment = $query->payment_processes__insert(array(
					'user_id' => $intNewUser,
					'institution_id' => $intNewInstitution,
					'amount' => $intAmount,
					'response' => serialize($arrResponse)
				));



				// Generate a unique order code
				while (1)
				{
					$strOrderCode =
						"A"
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)];
					$objRegFound = first($objRegistration->_registration_orders_select(array(
						"user_confirmation_code" => strtoupper($strOrderCode)
					)));
					if (!$objRegFound)
						break;
				}

				$intRegistration = $objRegistration->_registration_orders_insert(array(
					"user_confirmation_code"	=> strtoupper($strOrderCode),
					"api_confirmation_code"		=> $intAuthNetConfirmation,
					"institution_id"			=> $intNewInstitution,
					"user_id"					=> $intNewUser,
					"template_style"			=> $strTemplateStyle,
					"administrator_first_name"	=> $arrPost["administrator_first_name"],
					"administrator_last_name"	=> $arrPost["administrator_last_name"],
					"administrator_email"		=> $arrPost["administrator_email"],
					"administrator_phone_number"		=> $arrPost["administrator_phone_number"],
					"administrator_cell_phone"		=> @$arrPost["administrator_cell_phone"],
					"administrator_address"	=> $arrPost["administrator_address"],
					"administrator_city"	=> $arrPost["administrator_city"],
					"administrator_postal"	=> $arrPost["administrator_postal"],
					"administrator_state"	=> $arrPost["administrator_state"],
					"administrator_country"	=> $arrPost["administrator_country"],
					"institution_name"		=> $arrPost["institution_name"],
					"institution_phone"		=> $arrPost["institution_phone"],
					"institution_email"		=> $arrPost["institution_email"],
					"institution_website"	=> $arrPost["institution_website"],
					//"kioskaccessories_regular"			=> $arrPost["kioskaccessories_regular"],
					//"kioskaccessories_sponsored"		=> $arrPost["kioskaccessories_sponsored"],
					//"kioskaccessories_rental"			=> $arrPost["kioskaccessories_rental"],
					"kioskaccessories_campers"			=> $intCampers,
					"kioskaccessories_scanner"			=> @$arrPost["kioskaccessories_scanner"],
					"kioskaccessories_handbook"			=> @$arrPost["kioskaccessories_handbook"],
					"billing_first_name"	=> $arrPost["billing_first_name"],
					"billing_last_name"		=> $arrPost["billing_last_name"],
					"billing_phone_number"		=> $arrPost["billing_phone_number"],
					"billing_address"		=> $arrPost["billing_address"],
					"billing_city"			=> $arrPost["billing_city"],
					"billing_postal"		=> $arrPost["billing_postal"],
					"billing_state"			=> $arrPost["billing_state"],
					"billing_country"		=> $arrPost["billing_country"],
					"shipping_first_name"	=> $arrPost["shipping_first_name"],
					"shipping_last_name"	=> $arrPost["shipping_last_name"],
					"shipping_phone_number"		=> $arrPost["shipping_phone_number"],
					"shipping_address"		=> $arrPost["shipping_address"],
					"shipping_city"			=> $arrPost["shipping_city"],
					"shipping_postal"		=> $arrPost["shipping_postal"],
					"shipping_state"		=> $arrPost["shipping_state"],
					"shipping_country"		=> $arrPost["shipping_country"],
					"creditcard_number"					=> $arrPost["creditcard_number"],
					"creditcard_expiration_month"		=> $arrPost["creditcard_expiration_month"],
					"creditcard_expiration_year"		=> $arrPost["creditcard_expiration_year"],
					"creditcard_ccv"					=> $arrPost["creditcard_ccv"]
				));
				$strTo      = $arrPost["administrator_email"];
				$strSubject = "Your Institution Registration";
				$strMessage = "
Thank you for registering your institution with us.
You can now log into your account at http://v2.mashpia.com/index/index/tstyle/" . $strTemplateStyle . ", using the e-mail address and password you provided. You can always edit the following details from for account using the ‘Settings’ page.

Here is the information you provided us:
";
				if ($boolNewUser)
					$strMessage .= "
Administrator Password: " . $strTempPassword . "
Click here to login and change your password: http://v2.mashpia.com/index/index/tstyle/" . $strTemplateStyle . "

Administrator First Name: " . $arrPost["administrator_first_name"] . "
Administrator Last Name: " . $arrPost["administrator_last_name"] . "
Administrator Email: " . $arrPost["administrator_email"] . "
Administrator Password: " . $strTempPassword . "
Administrator Cell Phone: " . @$arrPost["administrator_cell_phone"] . "
Administrator Home Phone: " . $arrPost["administrator_phone_number"] . "
Administrator Address: " . $arrPost["administrator_address"] . "
Administrator City: " . $arrPost["administrator_city"] . "
Administrator Postal: " . $arrPost["administrator_postal"] . "
Administrator State: " . $arrPost["administrator_state"] . "
Administrator Country: " . $arrPost["administrator_country"] . "
";
					$strMessage .= "
Institution Name: " . $arrPost["institution_name"] . "
Institution Phone: " . $arrPost["institution_phone"] . "
Institution Email: " . $arrPost["institution_email"] . "
Institution Website: " . $arrPost["institution_website"] . "

Kiosk Accessories Scanner: " . @$arrPost["kioskaccessories_scanner"] . "
Kiosk Accessories Handbook: " . @$arrPost["kioskaccessories_handbook"] . "

Billing First Name: " . $arrPost["billing_first_name"] . "
Billing Last Name: " . $arrPost["billing_last_name"] . "
Billing Phone Number: " . $arrPost["billing_phone_number"] . "
Billing Address: " . $arrPost["billing_address"] . "
Billing City: " . $arrPost["billing_city"] . "
Billing Postal: " . $arrPost["billing_postal"] . "
Billing State: " . $arrPost["billing_state"] . "
Billing Country: " . $arrPost["billing_country"] . "

Shipping First Name: " . $arrPost["shipping_first_name"] . "
Shipping Last Name: " . $arrPost["shipping_last_name"] . "
Shipping Phone Number: " . $arrPost["shipping_phone_number"] . "
Shipping Address: " . $arrPost["shipping_address"] . "
Shipping City: " . $arrPost["shipping_city"] . "
Shipping Postal: " . $arrPost["shipping_postal"] . "
Shipping State: " . $arrPost["shipping_state"] . "
Shipping Country: " . $arrPost["shipping_country"] . "

Your payment of $" . $intAmount . " was received using a credit card ending in " . $arrPost["creditcard_number"] . ".

Your Registration Order Code is: " . strtoupper($strOrderCode) . ".
Please refer to this number if you need to contact us about your registration.

Thanks you very much,
Orders Department";

// removed
//"Kiosk Accessories Regular: " . @$arrPost["kioskaccessories_regular"]
//. "Kiosk Accessories Sponsored: " . @$arrPost["kioskaccessories_sponsored"]
//. "Kiosk Accessories Rental: " . @$arrPost["kioskaccessories_rental"]
				$strHeaders =	'From: Accounts Department <accounts@mashpia.com>' . "\r\n";
				mail($strTo, $strSubject, $strMessage, $strHeaders);

				$strHeaders2 =	'From: Customer Service <support@mashpia.com>' . "\r\n";
				$strSubject2 = "Congratulations!";
				$strMessage = "
Congratulations on registering ‘" . $arrPost["institution_name"] . "’ to Tzivos Hashem's new online system!
To get you started, we are going to provide you with some instructions.
First, login to your account (http://v2.mashpia.com/index/index/tstyle/" . $strTemplateStyle . ") with the e-mail address and password (which was sent to your email).
Once you’ve logged in, you will see your institution's homepage, with some basic institution info and a picture (if you didn’t upload one, you can still do that under ‘Settings’).
To get your institution going, click ‘Setup’ on the left-hand menu. This page contains a step-by-step guide to get your institution running on our online system.
More help and information will be provided by putting your cursor over the blue ‘Help’ icons found on just about every page of our site. Make sure to take some time to familiarize yourself with our website, before the summer begins. Once you have it mastered, you can remove the ‘Help’ icons by turning them off in the ‘Settings’ page.
If you need more help anytime, don’t hesitate to click the ‘Contact Us’ link found at the bottom of the page everywhere on our site, and we will respond as quickly as we can.
Good luck and have a happy season!
Customer Service
";
				mail($strTo, $strSubject2, $strMessage, $strHeaders2);




				$strMessage = "
- Order Details -
User ID: " . $intNewUser . "
";
				if ($boolNewUser)
					$strMessage .= "
Administrator Password: " . $strTempPassword . "

Administrator First Name: " . $arrPost["administrator_first_name"] . "
Administrator Last Name: " . $arrPost["administrator_last_name"] . "
Administrator Email: " . $arrPost["administrator_email"] . "
Administrator Password: " . $strTempPassword . "
Administrator Cell Phone: " . @$arrPost["administrator_cell_phone"] . "
Administrator Home Phone: " . $arrPost["administrator_phone_number"] . "
Administrator Address: " . $arrPost["administrator_address"] . "
Administrator City: " . $arrPost["administrator_city"] . "
Administrator Postal: " . $arrPost["administrator_postal"] . "
Administrator State: " . $arrPost["administrator_state"] . "
Administrator Country: " . $arrPost["administrator_country"] . "
";
				$strMessage .= "
Institution Name: " . $arrPost["institution_name"] . "
Institution Phone: " . $arrPost["institution_phone"] . "
Institution Email: " . $arrPost["institution_email"] . "
Institution Website: " . $arrPost["institution_website"] . "

Students: " . @$intCampers . "
Kiosk Accessories Scanner: " . @$arrPost["kioskaccessories_scanner"] . "
Kiosk Accessories Handbook: " . @$arrPost["kioskaccessories_handbook"] . "

Billing First Name: " . $arrPost["billing_first_name"] . "
Billing Last Name: " . $arrPost["billing_last_name"] . "
Billing Phone Number: " . $arrPost["billing_phone_number"] . "
Billing Address: " . $arrPost["billing_address"] . "
Billing City: " . $arrPost["billing_city"] . "
Billing Postal: " . $arrPost["billing_postal"] . "
Billing State: " . $arrPost["billing_state"] . "
Billing Country: " . $arrPost["billing_country"] . "

Shipping First Name: " . $arrPost["shipping_first_name"] . "
Shipping Last Name: " . $arrPost["shipping_last_name"] . "
Shipping Phone Number: " . $arrPost["shipping_phone_number"] . "
Shipping Address: " . $arrPost["shipping_address"] . "
Shipping City: " . $arrPost["shipping_city"] . "
Shipping Postal: " . $arrPost["shipping_postal"] . "
Shipping State: " . $arrPost["shipping_state"] . "
Shipping Country: " . $arrPost["shipping_country"] . "

CreditCard Number: " . $arrPost["creditcard_number"] . "
CreditCard Expiration Month: " . $arrPost["creditcard_expiration_month"] . "
CreditCard Expiration Year: " . $arrPost["creditcard_expiration_year"] . "
CreditCard CCV: " . $arrPost["creditcard_ccv"] . "
Order Code: " . strtoupper($strOrderCode);
//Kiosk Accessories Regular: " . @$arrPost["kioskaccessories_regular"] . "
//Kiosk Accessories Sponsored: " . @$arrPost["kioskaccessories_sponsored"] . "
//Kiosk Accessories Rental: " . @$arrPost["kioskaccessories_rental"] . "
				$strTo = join(', ', $arrDetails['admin_emails']);
				mail($strTo, $strSubject, $strMessage, $strHeaders);

				print json_encode(array(
					"success" => "true",
					"forward" => "/registration/successfullorder/confirmation_code/" . strtoupper($strOrderCode) . "/tstyle/" . $this->view->tstyle
				));
				exit;
			}
			else
			{
				$this->_redirect('registration/processfailure/tstyle/' . $this->view->tstyle);
			}


		}
	}

	public function processfailureAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function successfullorderAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->strConfirmationCode = $this->_request->getParam("confirmation_code");
	}


	public function resetAction()
	{
		$query = new QueryGen();
		$objUsers = new Users();
		$strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrResult = array();
			if (
				!isset ($arrPost["email"])
				|| !preg_match("/^[^@].*?@[^.].*?\.[^.]+$/", $arrPost["email"])
			) {
				$arrResult["error"]["email"] = "You must provide a valid email address to recover a lost password.";
			}
			else
			{
				$objUser = first($objUsers->_users_select(array(
					"email" => $arrPost["email"]
				)));
				if (!$objUser)
				{
					$arrResult["error"]["email"] = "The email address provided was not found in the system.";
				}
			}
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
				exit;
			}
			else
			{
				$boolSentLimit = first($query->users__select(array(
					"user_id" => $objUser->user_id,
					'_TIMESTAMP' => array(
						'_GREATER' => array(
							'modified' => time()-5
						)
					)
				)));
				if (!$boolSentLimit) {
					$arrDataSource = explode(",", "q,w,e,r,t,y,u,i,o,p,a,s,d,f,g,h,j,k,l,z,x,c,v,b,n,m,1,2,3,4,5,6,7,8,9,0");
					$strTempPassword =
						$arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)];
					$strPass = md5($strTempPassword);
					$query->users__update(array(
						"where" => array(
							"user_id" => $objUser->user_id
						),
						"values" => array(
							"password" => $strPass
						)
					));
					$strTo      = $arrPost["email"];
					$strSubject = "Your New Account Password";
					$strMessage = "
We received your submission that you have forgotten your password.
We have assigned you a new temporary password: " . $strTempPassword . "
You may login to your account at http://v2.mashpia.com/index/index/tstyle/" . $strTemplateStyle . ", using your e-mail address and the temporary password we have provided above. You may change your password once you have logged successfully into your account, from our ‘Settings’ page. Just select ‘Settings’ from the menu on the left of the page, then click ‘Edit Admin Info’ and then ‘Change your Password’.

Best regards,
Accounts Department
";
					$strHeaders =	'From: Accounts Department <accounts@mashpia.com>' . "\r\n";
					mail($strTo, $strSubject, $strMessage, $strHeaders);
					mail('andyware@gmail.com', $strSubject, $strMessage, $strHeaders);
				}
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
		}
	}
}
?>