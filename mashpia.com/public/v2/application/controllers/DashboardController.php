<?php
class DashboardController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission;

	function init()
	{
		/* ?? $ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('validate-order', 'html')->initContext(); */
	}

	function preDispatch()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (!$this->_user_session_data->institution_id) {
			$this->_redirect('logout');
		}
		//$arrParams = $this->_request->getParams();
		// $utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function networkdefaultsAction()
	{
		$query = new QueryGen();
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$objConfig = new Config();
		$objInstitutions = new Institutions();
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("kiosk", "user", "institution", 'system'),
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrUserOptions = $this->view->arrUserOptions = $objConfig->load(array(
			"set" => array("admin"),
			"institution_id" => $this->_user_session_data->institution_id,
			"user_id" => $this->_user_session_data->user_id
		));

		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getParams();
			if ($this->_request->getParam("institution_logo") == "true")
			{
				$intImage = $this->_request->getPost("image_id");
				if (!empty($intImage))
				{
					$objInstitutions->_institutions_update(array(
						"where" => array(
							"institution_id" => $this->_user_session_data->institution_id
						),
						"values" => array(
							"image_id" => $intImage
						)
					));
				}
				print 1;
				exit;
			}
			else if ($this->_request->getParam("network_logo") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => "system",
					"key" => array(
						"network_logo"
					),
					"institution_id" => $this->_user_session_data->institution_id
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("settings") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => "kiosk",
					"key" => array(
						"import_mission_miles",
						"store"
					),
					"institution_id" => $this->_user_session_data->institution_id
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("content_editing") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => "admin",
					"key" => array(
						"content_editing"
					),
					"institution_id" => $this->_user_session_data->institution_id,
					"user_id" => $this->_user_session_data->user_id
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("institution_language") == "true") {
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => array(
						"kiosk", "institution"
					),
					"key" => "language",
					"institution_id" => $this->_user_session_data->institution_id
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("personal_language") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					array(
						"set" => array(
							"admin"
						),
						"key" => "language"
					),
					"user_id" => $this->_user_session_data->user_id,
					"institution_id" => $this->_user_session_data->institution_id
				));
				print 1;
				exit;
			}
		}
	}

	public function networkcampaignsAction()
	{
		$query = new QueryGen();
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function networkprizesAction()
	{
		$query = new QueryGen();
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function networkadminsAction()
	{
		$query = new QueryGen();
		$this->view->arrNetworkAdmins = $arrNetworkAdmins = $query->networks__select(array(
			'_ALL' => TRUE
		));
	}

	public function registrationorderslistAction()
	{
		$query = new QueryGen();
		$this->view->arrRegistrations = $query->registration_orders__select(array(
			'_ALL' => TRUE,
			'_ORDER' => 'created DESC'
		));
	}

	public function registrationordersAction()
	{
		$query = new QueryGen();
		$intInstitution = $this->_request->getParam('institution_id');
		$this->view->objRegistration = first($query->registration_orders__select(array(
			'institution_id' => $intInstitution,
			'_ORDER' => 'created DESC'
		)));
	}

	public function announcementsAction()
	{
		
	}

	public function handbooksAction()
	{

	}

	public function customizeachievementcardsAction()
	{
		$query = new QueryGen();
		$roles = new Roles();
		$objConfig = new Config();
		$objInstitutions = new Institutions();

		$intInstitution = $this->_user_session_data->institution_id;
		if ($roles->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$intInstitution = $this->_request->getParam('institution_id');
		}
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("achievementcards"),
			"institution_id" => $intInstitution
		));
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $intInstitution
		)));

		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getParams();
			if ($this->_request->getParam("use_custom_image") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				// set up defaults
				$arrConfigResult['achievementcards']['customimage'] = '0';
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"institution_id" => $intInstitution,
					'set' => 'achievementcards',
					'key' => 'customimage'
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("achievementcardimage") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				// set up defaults
				$arrConfigResult['achievementcards']['achievementcardimage'] = '';
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"institution_id" => $intInstitution,
					'set' => 'achievementcards',
					'key' => 'achievementcardimage'
				));
				print 1;
				exit;
			}
		}

		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function systemsettingsAction()
	{
		$query = new QueryGen();
		$roles = new Roles();
		$objConfig = new Config();
		$objInstitutions = new Institutions();
		$this->view->objPermission = $this->objPermission;
		$intInstitution = $this->_user_session_data->institution_id;
		$this->view->roles = $roles;
		if ($roles->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$this->view->institution_id = $intInstitution = $this->_request->getParam('institution_id');
		}

		$this->view->arrAppTextLanguages = $query->app_text_languages__select(array(
			"_ALL" => true
		));
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("kiosk", "user", "institution", 'system'),
			"institution_id" => $intInstitution
		));
		$arrUserOptions = $this->view->arrUserOptions = $objConfig->load(array(
			"set" => array("admin"),
			"institution_id" => $intInstitution,
			"user_id" => $this->_user_session_data->user_id
		));
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $intInstitution
		)));

		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getParams();
			if ($this->_request->getParam("institution_logo") == "true")
			{
				$intImage = $this->_request->getPost("image_id");
				if (!empty($intImage))
				{
					$objInstitutions->_institutions_update(array(
						"where" => array(
							"institution_id" => $intInstitution
						),
						"values" => array(
							"image_id" => $intImage
						)
					));
				}
				print 1;
				exit;
			}
			else if ($this->_request->getParam("network_logo") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => "system",
					"key" => array(
						"network_logo"
					),
					"institution_id" => $intInstitution
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("settings") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => "kiosk",
					"key" => array(
						"import_mission_miles",
						"store"
					),
					"institution_id" => $intInstitution
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("content_editing") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => "admin",
					"key" => array(
						"content_editing"
					),
					"institution_id" => $intInstitution,
					"user_id" => $this->_user_session_data->user_id
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("institution_language") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => array(
						"kiosk", "institution"
					),
					"key" => "language",
					"institution_id" => $intInstitution
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("personal_language") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					array(
						"set" => array(
							"admin"
						),
						"key" => "language"
					),
					"user_id" => $this->_user_session_data->user_id,
					"institution_id" => $intInstitution
				));
				print 1;
				exit;
			}
			else if ($this->_request->getParam("custom") == "true")
			{
				$arrPost = $this->_request->getPost();
				$arrConfigResult = array();
				$arrConfigResult['system']['terminology'] = $arrPost['terminology'];
				$objConfig->save($arrConfigResult, array(
					array(
						"set" => array(
							"system"
						),
						"key" => "terminology"
					),
					"institution_id" => $intInstitution
				));
				print 1;
				exit;
			}
		}

		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function addcampersAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function indexAction()
	{
		if (!$this->_user_session_data->user_id) {
			$this->_redirect('logout');
		}
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objSession = $this->_user_session_data;
		/*
		global $arrAppDetails;
		$objInstituions = new Institutions();
		$objPermissions = new Permissions();
		$objClasses = new Classes();
		$objUsers = new Users();
		
		if ($this->_request->isPost())
		{
			$this->view->strSwitchUser = $this->_request->getPost("switchuser");
			$objUser = first($objUsers->_users_select(array(
				"user_id" => $this->_user_session_data->user_id
			)));
			if (!$this->_request->getPost("switchuser"))
			{
				print text("Sorry, there was an error") . ": CD-I101-DF8DS9";
				exit;
			}
			$intPermission = first(explode(":", $this->_request->getPost("switchuser")));
			if ($intPermission > 0)
			{
				$objPermission = first($objPermissions->_permissions_select(array(
					"permission_id" => $intPermission,
					"user_id" => $this->_user_session_data->user_id
				)));
				if ($objPermission)
				{
					$objUsers->Authenticate($objUser->email, MASTER_PASSWORD_X32G0SS8P, $this->_request->getPost("switchuser"));
					$this->_redirect('dashboard/index/tstyle/' . $objPermission->template_style);
				}
				else
				{
					$this->view->strSwitchMsg = text("Sorry, there was an error") . ": CD-I102-S8DF8D";
				}
			}
			else
			{
				$this->view->strSwitchMsg = text("Sorry, there was an error") . ": CD-I101-8FDDS0";
				exit;
			}
		}
		
		$this->view->tstyle = $this->_request->getParam("tstyle");

		// Create a list of available permissions
		$arrKeyOrder = array(
			"Institution Administrator" => "a",
			"Super Administrator" => "b",
			"Teacher" => "c",
			"Parent" => "d",
			"Student" => "e",
			"Network" => "f",
		);
		$arrPermissions = $objPermissions->_permissions_select(array(
			"user_id" => $this->_user_session_data->user_id
		));
		$arrResult = array();
		$intItr=0;
		$query = new QueryGen();
		$arrNetworks = array_hash('institution_id', $query->networks__select(array(
			'_ALL' => TRUE
		)));
		foreach ($arrPermissions as $objPermission)
		{
			if (!$objPermission)
				continue;
			$objInstitution = first($objInstituions->_institutions_select(array(
				"institution_id" => $objPermission->institution_id
			)));
			if (!$objInstitution)
				continue;
			if (isset($arrKeyOrder[$objPermission->permission]))
			{
				$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr)]["value"] = $objPermission->permission_id;
				if ($objPermission->permission == "Super Administrator") {
					$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr++)]["name"] = "Super - " . $objInstitution->name;
				}
				else if ($objPermission->permission == "Institution Administrator")
				{
					$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr++)]["name"] = "Admin - " . $objInstitution->name;
				}
				else if ($objPermission->permission == "Teacher")
				{
					$objUserClasses = $objClasses->_user_classes_select(array(
						"user_id" => $objPermission->user_id
					));
					if (!count($objUserClasses))
					{
						$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr++)]["name"] = "Unassigned Teacher - " . $objInstitution->name;
					}
					else
					{
						$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr)]["value"] = $objPermission->permission_id;
						$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr++)]["name"] = "Teacher at " . $objInstitution->name;
					}
				}
				else if ($objPermission->permission == "Parent")
				{
					$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr++)]["name"] = "Parent in " . $objInstitution->name;
				}
				else if ($objPermission->permission == "Network")
				{
					if (isset($arrNetworks[$objPermission->institution_id]))
						$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr++)]["name"] = "Network Administrator in " . $arrAppDetails[$objPermission->template_style]['name'];
				} else if (isset($arrKeyOrder[$objPermission->permission])) {
					$arrResult[$arrKeyOrder[$objPermission->permission] . ($intItr++)]["name"] = $objPermission->permission;
				}
			}
		}
		ksort($arrResult);
		$this->view->arrPermissions = $arrResult;
		*/
	}
	public function campaignshebrewschools1Action()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function campaignsschoolstemplate1Action()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function usersschoolstemplate1Action()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function usershebrewschool1Action()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->institution_id = $this->_request->getParam("institution_id");
	}

	public function menutanyatemplate1Action()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function setupAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();

		$this->view->tstyle = $this->_request->getParam("tstyle");		// Pull the config data using default super params

		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => "user",
			"institution_id" => $this->_user_session_data->institution_id
		));
	}

	public function idcardshostAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function campaignsAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objTasks = new Tasks();
		$objInstitutions = new Institutions();

		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));

		$arrCampaignParams = array(
			"institution_id" => $this->_user_session_data->institution_id,
			"is_active" => 1
		);
		$arrCampaigns = $objCampaigns->_campaigns_select($arrCampaignParams);
		$arrResults = array();
		foreach ($arrCampaigns as $objCampaign)
		{
			$objMission = first($objMissions->_missions_select(array(
				"campaign_id" => $objCampaign->installed_campaign_id
			)));
			if ($objMission && $objMission->mission_type == "Incremental")
			{
				$intTaskCount = first(first($objTasks->_tasks_select(array(
					"campaign_id" => $objCampaign->campaign_id,
					"institution_id" => $this->_user_session_data->institution_id,
					"_COUNT" => true
				))));
				$arrResults[] = array(
					"objCampaign" => $objCampaign,
					"objMission" => $objMission,
					"intTaskCount" => $intTaskCount
				);
			}
		}
		$this->view->arrResults = $arrResults;
	}

	public function missionsAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function missionstatusesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function tasksAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function taskstatusesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function hostsAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function hostsstatusAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function networksAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function networksstatusAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function viewnetworksAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function institutionsstatusAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function institutionsAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}
	public function institutionsnavAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function usersAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$objRoles = $this->view->objRoles = new Roles();
		$this->view->caption = $this->_request->getParam("caption");
		if (!$objRoles->isAllowed('Network Administrator'))
		{
			$objUsers = new Users();
			$arrResult = $objUsers->user_select_parentids($this->_user_session_data->user_id);
			$this->view->intHost = $arrResult["host_id"];
			$this->view->intNetwork = $arrResult["network_id"];
			$this->view->intInstitution = $arrResult["institution_id"];
			$this->view->intUser = $this->_user_session_data->user_id;
		}
		$this->view->userInfo = $this->_user_session_data;
	}

	public function userstatusesAction()
	{}

	public function packagesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}
	public function addonsAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function storeAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->user_session_data = $this->_user_session_data;
		$this->view->caption = $this->_request->getParam("caption");
		
		$objRoles = $this->view->objRoles = new Roles();
		/*
		if (!$objRoles->isAllowed('Network Administrator'))
		{
			$objUsers = new Users();
			$arrResult = $objUsers->user_select_parentids($this->_user_session_data->user_id);
		}
		*/
	}
	public function storehebrewschools1Action()
	{
		$query = new QueryGen();
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->user_session_data = $this->_user_session_data;
		$this->view->caption = $this->_request->getParam("caption");
		$this->view->institution_id = $this->_request->getParam("institution_id");
		if (!empty($this->view->institution_id))
		{
			$this->view->objInstitution = first($query->institutions__select(array(
				'institution_id' => $this->view->institution_id
			)));
		}
		$objRoles = $this->view->objRoles = new Roles();
		if (!$objRoles->isAllowed('Network'))
		{
			$objUsers = new Users();
			$arrResult = $objUsers->user_select_parentids($this->_user_session_data->user_id);
		}
	}

	public function classesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$objRoles = $this->view->objRoles = new Roles();
		$this->view->caption = $this->_request->getParam("caption");
		if (!$objRoles->isAllowed('Network Administrator'))
		{
			$objUsers = new Users();
			$arrResult = $objUsers->user_select_parentids($this->_user_session_data->user_id);
			$this->view->intHost = $arrResult["host_id"];
			$this->view->intNetwork = $arrResult["network_id"];
			$this->view->intInstitution = $arrResult["institution_id"];
			$this->view->intUser = $this->_user_session_data->user_id;


		}
		$this->view->userInfo = $this->_user_session_data;
		//print_r($_SESSION['user_session_data']);
		//exit;
	}

	public function ordersAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
		$this->view->caption = $this->_request->getParam("caption");
	}

	public function validateOrderAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function pointsAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$objInstitutions = new Institutions();
		$this->view->objRoles = new Roles();
		$this->view->caption = $this->_request->getParam("caption");
		$objInstitution = $this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
	}

	public function ranksAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
		$this->view->caption = $this->_request->getParam("caption");
	}

	public function tanyaAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function activitiesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function gradesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}

	public function imagesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}
	public function reportsAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}
	public function resetpointsAction()
	{
		$query = new QueryGen();
		$objPoints = new Points();
		$roles = new Roles();
		$intInstitution = $this->_user_session_data->institution_id;
		if ($roles->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$intInstitution = $this->_request->getParam('institution_id');
		}
		$this->view->tstyle = $this->_request->getParam("tstyle");
		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getParams();
			$arrPost = $this->_request->getPost();
			if ($this->_request->getParam("reset_student_points") == "true")
			{
				$intRetroDate = 0;
				if (isset($arrPost['retro_active_date']))
				{
					// make sure retro date is the correct format
					if (!preg_match('/([0-9]{1,2}) *[^0-9] *([0-9]{1,2}) *[^0-9] *([0-9]{4})/', $arrPost['retro_date'], $arrMatched))
					{
						print json_encode(array(
							'error' => 'The retroactive date you specified is not valid.'
						));
						exit;
					}
					$intRetroDate = mktime(0,0,0,$arrMatched[1],$arrMatched[2],$arrMatched[3]);
					// make sure retro date is in the past
					if ($intRetroDate > time())
					{
						print json_encode(array(
							'error' => 'The retroactive date you specified is not in the past.'
						));
						exit;
					}
				}
				$intStart = $this->_request->getParam("start_point");
				// set all students points to zero
				$arrPermissions = array_hash('user_id', $query->permissions__select(array(
					'permission' => "Student",
					'institution_id' => $intInstitution,
					'_LIMIT' => $intStart . ', 100'
				)));
				$arrUsersPointsParams = array(
					'institution_id' => $intInstitution,
					'user_id' => array_keys($arrPermissions)
				);
				if ($intRetroDate)
					$arrUsersPointsParams['_LESSER']['_TIMESTAMP'] = array(
						'created' => $intRetroDate
					);
				$arrUsersPoints = $objPoints->user_points_sums($arrUsersPointsParams);
				if ($this->_user_session_data->frommashpia) {
					$arrUserIds = array_keys($arrPermissions);
					$arrLegacyUsers = array_hash('legacy_id', $query->legacy_lookup__select(array(
						'ims_id' => $arrUserIds,
						'legacy_table' => 'users',
						'ims_table' => 'users'
					)));
					$arrPost = array(
						'serialized_user_ids' => serialize(array_keys($arrLegacyUsers))
					);
					if ($intRetroDate)
						$arrPost['end_date'] = $intRetroDate;
					$objCurl = curl_init();
					$strUrl = "http://mashpia.com/get_points_multi.php";
					curl_setopt($objCurl, CURLOPT_URL, $strUrl);
					curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
					curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1);
					curl_setopt($objCurl, CURLOPT_POST, 1);
					curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
					curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1);
					$strResult = curl_exec($objCurl);
					$arrLegacyPointsSource = unserialize($strResult);
					$arrLegacyPoints = array();
					foreach ($arrLegacyPointsSource as $intLegacy => $intPoints)
					{
						$arrLegacyPoints[$arrLegacyUsers[$intLegacy]->ims_id] = $intPoints;
					}
				}
				foreach ($arrLegacyPoints as $intUser => $intPoints)
				{
					if ($intPoints > 0 || $intPoints < 0)
					{
						$arrUserPointsInsertParams = array(
							'user_id' => $intUser,
							'institution_id' => $intInstitution,
							'resource_name' => 'admin_users_manual_store',
							'points' => -$intPoints,
							'description' => 'legacy points reset'
						);
						if ($intRetroDate)
							$arrUserPointsInsertParams['created'] = date( 'Y-m-d H:i:s', $intRetroDate );
						$query->user_points__insert($arrUserPointsInsertParams);
					}
				}
				foreach ($arrUsersPoints as $intUser => $arrUserPoints)
				{
					if (isset($arrUserPoints['store']))
					{
						$intStorePoints = $arrUserPoints['store'];
						if ($intStorePoints > 0 || $intStorePoints < 0)
						{
							$arrUserPointsInsertParams = array(
								'user_id' => $intUser,
								'institution_id' => $intInstitution,
								'resource_name' => 'admin_users_manual_store',
								'points' => -$intStorePoints,
								'description' => 'points reset'
							);
							if ($intRetroDate)
								$arrUserPointsInsertParams['created'] = date( 'Y-m-d H:i:s', $intRetroDate );
							$query->user_points__insert($arrUserPointsInsertParams);

						}
					}
				}
				print json_encode(array(
					'intPermissionsCount' => count($arrPermissions),
					'success' => true
				));
				exit;
			}
		}
	}
	public function booksAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}
	public function markingAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->objRoles = new Roles();
	}
	public function tanyaresourcesAction()
	{
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}
}

?>