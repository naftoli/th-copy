<?php
class AdministrationController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance
	private $boolVerbose = 0;
	private $_utilities;

	function init()
	{}

	function preDispatch()
	{
		//$this->_utilities = new Utilities();
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (!$this->_user_session_data->institution_id) {
			$this->_redirect('logout');
		}
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function indexAction()
	{
		$this->_redirect('dashboard');
	}

	public function studenttransferAction()
	{
		$query = new QueryGen();
		$objClasses = new Classes();
		$this->view->tstyle = $this->_request->getParam('tstyle');
		$arrPermissions = array_hash('institution_id', $query->permissions__select(array(
			'user_id' => $this->_user_session_data->user_id,
			'permission' => array('Institution Administrator', 'Teacher')
		)));
		$this->view->arrInstitutions = $query->institutions__select(array(
			'_COLUMNS' => array('institution_id', 'name', 'template_style'),
			'institution_id' => array_keys($arrPermissions)
		));
		/*
		$arrClasses = $objClasses->_classes_select(array(
			'user_id' => $this->_user_session_data->user_id,
			'institution_id' => $this->_user_session_data->institution_id
		));
		$arrClassLCNameHash = array();
		foreach ($arrClasses as $objClass)
		{
			$arrClassLCNameHash[trim(strtolower($objClass->custom_name1))] = $objClass;
		}
		$this->view->arrClassNames = array_stack('custom_name1', $arrClasses);
		 *
		 */
	}

	public function studenttransferlistAction()
	{
		$query = new QueryGen();
		$objClasses = new Classes();
		$tstyle = $this->view->tstyle = $this->_request->getParam('tstyle');
		$boolTransferRegistration =
			$tstyle == "chabadhebrewschool"
			|| $tstyle == "hebrewschool1";
		$intInstitution = $this->_request->getParam('institution_id');
		$this->view->institution_id = $intInstitution;
		if (empty($intInstitution)) {
			json(array(
				'error' => 'Sorry, there was an error: CA-STL101-3galsl'
			));
		}
		// check if user has permission to institution
		$objPermission = first($query->permissions__select(array(
			'institution_id' => $intInstitution,
			'user_id' => $this->_user_session_data->user_id,
			'permission' => array('Institution Administrator', 'Teacher')
		)));
		if (!$objPermission)
		{
			json(array(
				'error' => 'Sorry, there was an error: CA-STL102-g3g4es'
			));
		}
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			'institution_id' => $intInstitution
		)));
		$arrClasses = $objClasses->_classes_select(array(
			'user_id' => $this->_user_session_data->user_id,
			'institution_id' => $intInstitution
		));
		$arrClassLCNameHash = array();
		$arrClassNames = array();
		foreach ($arrClasses as $objClass)
		{
			$arrClassLCNameHash[trim(strtolower($objClass->custom_name1))] = $objClass;
			$arrClassNames[$objClass->class_id] = $objClass->custom_name1;
		}
		$this->view->arrClassNames = array_stack('custom_name1', $arrClasses);
		$arrPermissions = array_stack('user_id', $query->permissions__select(array(
			'institution_id' => $intInstitution,
			'permission' => 'Student'
		)));
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'user_id' => $arrPermissions
		)));
		$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
			'user_id' => $arrPermissions
		)));
		//dumper($arrPermissions,1,1);
		$arrRows = array();
		$arrUserIds = array();
		foreach ($arrUsers as $objUser)
		{
			$strClass = '';
			if (isset($arrUserClasses[$objUser->user_id]))
			{
				$objUserClass = $arrUserClasses[$objUser->user_id];
				if (isset($arrClassNames[$objUserClass->class_id]))
					$strClass = $arrClassNames[$objUserClass->class_id];
			}
			$arrUserIds[$objUser->user_id] = $objUser;
			$arrRows[] = (object) array(
				'user_id' => $objUser->user_id,
				'class' => $strClass,
				'first_name' => $objUser->first_name,
				'last_name' => $objUser->last_name
			);
		}
		$this->view->arrRows = $arrRows;

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (isset($arrPost['get_rows'])) {
				json(array(
					'success' => 'true',
					'arrRows' => $arrRows
				));
			}
			$arrData = $arrPost['arrData'];
			$arrInsertUsers = array();
			//dumper($arrUserIds,1,1);
			foreach ($arrData as $arrUserData) {
				if (isset($arrUserIds[$arrUserData['user_id']])) {
					$query->users__update(array(
						'where' => array(
							'user_id' => $arrUserData['user_id']
						),
						'values' => array(
							'first_name' => $arrUserData['first_name'],
							'last_name' => $arrUserData['last_name']
						)
					));
				} else {
					$query->permissions__insert(array(
						'template_style' => $objInstitution->template_style,
						'registration_expiration' => $boolTransferRegistration ? $arrPermissions[$arrUserData['user_id']]->registration_expiration : 0,
						'registration_date' => $boolTransferRegistration ? $arrPermissions[$arrUserData['user_id']]->registration_date : 0,
						'user_id' => $arrUserData['user_id'],
						'institution_id' => $objInstitution->institution_id,
						'permission' => 'Student',
						'default_permission' => 0,
						'created_by' => $this->_user_session_data->user_id
					));
				}
				if (empty($arrUserData['class'])) {
					if (isset($arrUserClasses[$arrUserData['user_id']])) {
						$objUserClass = $arrUserClasses[$arrUserData['user_id']];
						// remove the class
						$query->user_classes__delete(array(
							'user_class_id' => $objUserClass->user_class_id
						));
					}
				}
				else
				{
					$strClass = trim(strtolower($arrUserData['class']));
					if (isset($arrClassLCNameHash[$strClass]))
					{
						$objClass = $arrClassLCNameHash[$strClass];
						if (isset($arrUserClasses[$arrUserData['user_id']]))
						{
							// update user class
							$objUserClass = $arrUserClasses[$arrUserData['user_id']];
							if ($objUserClass->class_id != $objClass->class_id) {
								$query->user_classes__update(array(
									'where' => array(
										'user_class_id' => $objUserClass->user_class_id
									),
									'values' => array(
										'class_id' => $objClass->class_id
									)
								));
							}
						}
						else
						{
							// insert user class
							$query->user_classes__insert(array(
								'institution_id' => $intInstitution,
								'class_id' => $objClass->class_id,
								'user_id' => $arrUserData['user_id'],
								'class_role' => 'Student'
							));
						}
					}
				}
				unset($arrUserIds[$arrUserData['user_id']]);
			}
			if (count($arrUserIds)) {
				// remove users who are no longer on the list
				foreach ($arrUserIds as $intUser => $objUser) {
					$query->permissions__delete(array(
						'institution_id' => $intInstitution,
						'user_id' => $intUser
					));
				}
			}
			json(array(
				"success" => "true",
				'arrUserData' => $arrUserData
			));
		}
	}

	public function profilenetworkAction()
	{
		$query = new QueryGen();
		$intNetwork = $this->_user_session_data->network_id;
		$this->view->objNetwork = $objNetwork = first($query->networks__select(array(
			'network_id' => $intNetwork
		)));
		$arrGet = $this->_request->getParams();
		$arrPost = $this->_request->getPost();
		if (isset($arrGet['network_logo']))
		{
			$query->networks__update(array(
				'where' => array(
					'network_id' => $intNetwork
				),
				'values' => array(
					'image_id' => $arrPost['system__network_logo']
				)
			));
			print 1;
			exit;
		}
	}

	public function editnetworkAction()
	{
		$query = new QueryGen();
		$this->view->network_id = $intNetwork = $this->_request->getParam('network_id');
		$this->view->objNetwork = $objNetwork = first($query->networks__select(array(
			'network_id' => $intNetwork
		)));
		$arrGet = $this->_request->getParams();
		$arrPost = $this->_request->getPost();
		$intNetwork = $arrGet['network_id'];
		if (isset($arrGet['network_save']))
		{
			$objUser = first($query->users__select(array(
				'email' => $arrPost['email']
			)));
			if (!$objUser)
			{
				print json_encode(array(
					'error' => 'There is no user in the system with this email.'
				));
				exit;
			}
			$objNetwork = first($query->networks__select(array(
				'network_id' => $intNetwork
			)));
			if (!$objNetwork)
			{
				print json_encode(array(
					'error' => 'Sorry, there was an error: CA-EN101-7sd8f7'
				));
			}
			$objInstitution = first($query->institutions__select(array(
				'institution_id' => $objNetwork->institution_id
			)));
			if (empty($arrPost['email'])) {
				$query->permissions__delete(array(
					'template_style' => $objNetwork->network_keyword,
					'permission' => 'Network'
				));
				$query->networks__update(array(
					'where' => array(
						'network_id' => $intNetwork
					),
					'values' => array(
						'network_email' => $arrPost['email'],
						'admin_user_id' => 0
					)
				));
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
			$objPermission = first($query->permissions__select(array(
				'permission' => array('Institution Administrator', 'Teacher'),
				'user_id' => $objUser->user_id
			)));
			if (!$objPermission)
			{
				print json_encode(array(
					'error' => 'This user doesn\'t appear to have access to the system.'
				));
			}
			$query->networks__update(array(
				'where' => array(
					'network_id' => $intNetwork
				),
				'values' => array(
					'network_email' => $arrPost['email'],
					'admin_user_id' => $objUser->user_id,
					'image_id' => $arrPost['image_id']
				)
			));
			// remove any previous network admins permissions
			$query->permissions__delete(array(
				'template_style' => $objNetwork->network_keyword,
				'permission' => 'Network'
			));
			$query->permissions__insert(array(
				'template_style' => $objNetwork->network_keyword,
				'user_id' => $objUser->user_id,
				'permission' => 'Network',
				'institution_id' => $objPermission->institution_id
			));
			$query->permissions__insert(array(
				'template_style' => $objNetwork->network_keyword,
				'user_id' => $objUser->user_id,
				'permission' => 'Network',
				'institution_id' => $objInstitution->institution_id
			));
			print json_encode(array(
				"success" => "true"
			));
			exit;
		}
	}

	public function changepasswordAction()
	{
		$objUsers = new Users();

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrResult = array();

			if (!isset($arrPost["current_password"]) || strlen($arrPost["current_password"]) < 1)
			{
				$arrResult["error"]["current_password"] = "This is not a valid password.";
			}
			else
			{
				$objUser = first($objUsers->_users_select(array(
					"user_id" => $this->_user_session_data->user_id,
					"password" => md5($arrPost["current_password"])
				)));
				if (!$objUser)
				{
					$arrResult["error"]["current_password"] = "This is not your correct password.";
				}
			}

			if (!isset($arrPost["new_password"]) || strlen($arrPost["new_password"]) < 6)
			{
				$arrResult["error"]["new_password"] = "The password field must contain at least 6 characters.";
			}
			if (!isset($arrPost["confirm_new_password"]) || $arrPost["confirm_new_password"] != $arrPost["new_password"])
			{
				$arrResult["error"]["confirm_new_password"] = "The passwords do not match. Please try again.";
			}
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
			}
			else
			{
				$objUsers->_users_update(array(
					"where" => array(
						"user_id" => $this->_user_session_data->user_id
					),
					"values" => array(
						"password" => md5($arrPost["new_password"])
					)
				));
				print json_encode(array(
					"success" => "true"
				));
			}
			exit;
		}
	}

	public function parenteditAction()
	{
		$objUsers = new Users();
		$arrPost = $this->_request->getPost();
		if ($this->_request->isPost())
		{
			$objUsers->_users_update(array(
				"where" => array(
					"user_id" => $this->_user_session_data->user_id
				),
				"values" => $arrPost
			));
			print 1;
			exit;
		}
		$arrPost = (array) first($objUsers->_users_select(array(
			"user_id" => $this->_user_session_data->user_id
		)));
		$this->view->arrPost = $arrPost;
	}

	public function profileparentAction()
	{
		$objUsers = new Users();
		$objInstitutions = new Institutions();
		$objPermissions = new Permissions();
		$objCampaigns = new Campaigns();
		$objClasses = new Classes();

		if ($this->_request->getParam("removeassociation"))
		{
			/*$objUsers->_relationships_delete(array(
				"user_id" => $this->_user_session_data->user_id,
				"relation_id" => $this->_user_session_data->relation_id
			));*/
			print "<script>window.location.refresh();</script>";
			exit;
		}

		$this->view->objUser = first($objUsers->_users_select(array(
			"user_id" => $this->_user_session_data->user_id
		)));
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		$this->view->objPermission = first($objPermissions->_permissions_select(array(
			"permission_id" => $this->_user_session_data->permission_id
		)));
		$this->view->arrCampaigns = $objCampaigns->_campaigns_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrRelationships = $objUsers->_relationships_select(array(
			"user_id" => $this->_user_session_data->user_id
		));
		$arrChildren = array();
		$arrAwaitingActions = array();
		foreach ($arrRelationships as $objRelationship)
		{
			if (!isset($arrChildren[$objRelationship->relation_id]))
			{
				$objChild = $arrChildren[$objRelationship->relation_id] = first($objUsers->_users_select(array(
					"user_id" => $objRelationship->relation_id
				)));
				if (!$objChild)
					continue;
				$objPermission = $objPermissions->_permissions_select(array(
					"user_id" => $objRelationship->relation_id,
					"institution_id" => $this->_user_session_data->institution_id,
					"permission" => "Student"
				));
				if (!$objPermission)
					continue;
				if (!preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{1,4}$/", $objChild->dob))
				{
					$arrAwaitingActions[]["text"] = $objChild->first_name . " " . $objChild->last_name . " does not have a valid birthdate";
					$arrAwaitingActions[count($arrAwaitingActions)-1]["link"] = "/users/editor/user_types/student/user_type/student/user_id/" . $objChild->user_id;
				}

			}
		}
		// Children are and are not enrolled into any book campaigns
		$arrChildrenCampaignEnrollment = $objCampaigns->_user_campaigns_select(array(
			"user_id" => array_keys($arrChildren),
			"institution_id" => $this->_user_session_data->institution_id,
			"status" => "Enrollment"
		));
		$arrChildrenInCampaigns = array();
		foreach ($arrChildrenCampaignEnrollment as $objUserCampaign)
		{
			$arrChildrenInCampaigns[$objUserCampaign->user_id] = $objUserCampaign;
		}
		$arrChildrenWithNoCampaignIds = array_diff(array_keys($arrChildren), array_keys($arrChildrenInCampaigns));
		foreach ($arrChildrenWithNoCampaignIds as $intChild)
		{
			$objPermission = $objPermissions->_permissions_select(array(
				"user_id" => $intChild,
				"institution_id" => $this->_user_session_data->institution_id,
				"permission" => "Student"
			));
			if (!$objPermission)
				continue;
			$arrAwaitingActions[]["text"] = $arrChildren[$intChild]->first_name . " " . $arrChildren[$intChild]->last_name . " is not enrolled into any campaigns";
			$arrAwaitingActions[count($arrAwaitingActions)-1]["link"] = "/campaigns/campaignchildenroll/user_id/" . $intChild;
		}

		$this->view->arrAwaitingActions = $arrAwaitingActions;
		$this->view->arrChildren = $arrChildren;
	}

    public function profileAction()
    {
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}
	public function profilesuperAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$objInstitutions = new Institutions();

		$this->view->arrAppTextLanguages = $query->app_text_languages__select(array(
			"_ALL" => true
		));

		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("kiosk", "user", "institution", 'system'),
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrUserOptions = $this->view->arrUserOptions = $objConfig->load(array(
			"set" => array("admin"),
			"institution_id" => $this->_user_session_data->institution_id,
			"user_id" => $this->_user_session_data->user_id
		));
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));

		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getParams();
			if ($this->_request->getParam("network_logo") == "true")
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
		}

		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function profileadminAction()
	{
		$objInstitutions = new Institutions();
		$objUsers = new Users();
		
		$this->view->objUser = first($objUsers->getMashpiaAdmin());		
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		
		$this->view->tstyle = $this->_request->getParam("tstyle");
		/*
		$query = new QueryGen();
		$objConfig = new Config();
		$objUsers = new Users();
		$objInstitutions = new Institutions();
		$objPermissions = new Permissions();
		$objCampaigns = new Campaigns();
		$objClasses = new Classes();
		$objScheduler = new Scheduler();
		$objPoints = new Points();

		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("kiosk", "user"),
			"institution_id" => $this->_user_session_data->institution_id
		));

		if ($this->_request->isPost())
		{
			if ($this->_request->getPost("buy_prize_barcode") == 'true')
			{
				$intUser = $this->_request->getPost("user_id");
				$intPrizeBarCode = $this->_request->getPost("prize_barcode");
				$objUser = first($objUsers->_users_select_hierarchal(array(
					'institution_id' => $this->_user_session_data->institution_id,
					'user_id' => $intUser,
					'_LIMIT' => 1
				)));
				if (!$objUser)
				{
					print json_encode(array(
						'error' => 'The user was not found.'
					));
					exit;
				}
				$objPrize = first($query->prize__select(array(
					'institution_id' => $this->_user_session_data->institution_id,
					'bar_code' => $intPrizeBarCode,
					'is_active' => 1,
					'_LIMIT' => 1
				)));
				if (!$objPrize)
				{
					print json_encode(array(
						'error' => 'The prize was not found or is deactivated.'
					));
					exit;
				}
				if ($objPrize->prize_count < 1)
				{
					print json_encode(array(
						'error' => 'This prize is no longer in stock.'
					));
					exit;
				}
				// check if user can afford the prize
				$intCost = $objPrize->points;
				$objUserPoints = $objPoints->user_points(array(
					'user_id' => $objUser->user_id
				));
				if ($objUserPoints->store < $intCost)
				{
					$intPointsMissing = $intCost - $objUserPoints->store;
					print json_encode(array(
						'error' => 'The student is missing ' . $intPointsMissing . ' points to purchase `' . $objPrize->prize_name . '`.'
					));
					exit;
				}
				$query->prize__update(array(
					"where" => array(
						"prize_id" => $objPrize->prize_id
					),
					"values" => array(
						"prize_count" => $objPrize->prize_count - 1
					)
				));

				// purchase the prize
				$strSerial = FALSE;
				while (!$strSerial)
				{
					$strSerial = rand_num_string(10);
					$objTempUserPrize = first($query->user_prizes__select(array(
						"serial" => (string) $strSerial
					)));
					if ($objTempUserPrize)
						$strSerial = FALSE;
				}

				$intUserPrize = $query->user_prizes__insert(array(
					"prize_id" => $objPrize->prize_id,
					"user_id" => $objUser->user_id,
					"institution_id" => $this->_user_session_data->institution_id,
					'quantity' => 1,
					'serial' => $strSerial,
					'status' => 'Checked Out'
				));

				$query->user_points__insert(array(
					"prize_id" => $objPrize->prize_id,
					"user_prize_id" => $intUserPrize,
					"user_id" => $objUser->user_id,
					"institution_id" => $this->_user_session_data->institution_id,
					"points" => -$intCost,
					"resource_name" => "transaction_manager_store"
				));

				$objUserPoints = $objPoints->user_points(array(
					'user_id' => $objUser->user_id
				));
				print json_encode(array(
					'success' => 'true',
					'objUserPoints' => $objUserPoints,
					'objPrize' => $objPrize
				));
				exit;
			}
			else if ($this->_request->getPost("student_bar_code"))
			{
				$intBarCode = $this->_request->getPost("student_bar_code");
				$objUser = first($objUsers->_users_select_hierarchal(array(
					'institution_id' => $this->_user_session_data->institution_id,
					'bar_code' => $intBarCode,
					'_LIMIT' => 1
				)));
				if (!$objUser)
				{
					print json_encode(array(
						'error' => 'This user was not found.'
					));
					exit;
				}
				$objUserClass = first($query->user_classes__select(array(
					'user_id' => $objUser->user_id
				)));
				if ($objUserClass)
					$objClass = first($objClasses->_classes_select(array(
						'class_id' => $objUserClass->class_id
					)));
				else
					$objClass = false;
				$objUserPoints = $objPoints->user_points(array(
					'user_id' => $objUser->user_id
				));
				print json_encode(array(
					'objUser' => $objUser,
					'objClass' => $objClass,
					'objUserPoints' => $objUserPoints
				));
				exit;
			}
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
					"set" => array("kiosk", "user"),
					"institution_id" => $this->_user_session_data->institution_id
				));
				print 1;
				exit;
			}
		}

		$intMaxAwaitingActions = 0;

		$this->view->strExtraActions = "";
		/*
		$this->view->objUser = first($objUsers->_users_select(array(
			"user_id" => $this->_user_session_data->user_id
		)));
		
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		
		$this->view->objPermission = first($objPermissions->_permissions_select(array(
			"permission_id" => $this->_user_session_data->permission_id
		)));
		
		$this->view->tstyle = $this->_request->getParam("tstyle");

		$arrChildren = array();
		$arrAwaitingActions = array();
		$intTotalActions = 0;
		/*
		$arrUsers = $objUsers->_users_select_hierarchal(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"permission" => "Student"
		));
		foreach ($arrUsers as $objUser)
		{
			if (!isset($arrChildren[$objUser->user_id]))
			{
				$arrChildren[$objUser->user_id] = $objUser;
				if (!preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", $objUser->dob))
				{
					$intTotalActions++;
					if (count($arrAwaitingActions) >= $intMaxAwaitingActions)
						continue;
					$arrAwaitingActions[]["text"] = $objUser->first_name . " " . $objUser->last_name . " does not have a valid birthdate";
					$arrAwaitingActions[count($arrAwaitingActions)-1]["link"] = "/users/dobeditor/user_types/student/user_type/student/user_id/" . $objUser->user_id;
				}

			}
		}
		// Children are and are not enrolled into any book campaigns
		$arrChildrenCampaignEnrollment = $objCampaigns->_user_campaigns_select(array(
			"user_id" => array_keys($arrChildren),
			"institution_id" => $this->_user_session_data->institution_id,
			"status" => "Enrollment"
		));
		$arrChildrenInCampaigns = array();
		foreach ($arrChildrenCampaignEnrollment as $objUserCampaign)
		{
			$arrChildrenInCampaigns[$objUserCampaign->user_id] = $objUserCampaign;
		}
		$arrChildrenWithNoCampaignIds = array_diff(array_keys($arrChildren), array_keys($arrChildrenInCampaigns));
		foreach ($arrChildrenWithNoCampaignIds as $intChild)
		{
			$intTotalActions++;
			if (count($arrAwaitingActions) >= 0)
				continue;
			if (!isset($arrChildrenInCampaigns[$intChild]))
				continue;
			$arrLadders = $objScheduler->load_available_ladders2(array(
				"user_id" 			=> $intChild,
				"institution_id"	=> $this->_user_session_data->institution_id,
				"campaign_id"		=> $arrChildrenInCampaigns[$intChild]->campaign_id
			));
			if (!count($arrLadders))
				continue;

			$objUserClass = first($objClasses->_user_classes_select(array(
				"user_id" => $intChild
			)));
			if (!$objUserClass)
				continue;
			if ($this->view->tstyle == "tanyatemplate1")
			{
				$arrAwaitingActions[]["text"] = $arrChildren[$intChild]->first_name . " " . $arrChildren[$intChild]->last_name . " is not enrolled into the Tanya campaign";
				$arrAwaitingActions[count($arrAwaitingActions)-1]["link"] = "/campaigns/campaignchildenrolltanya/class_id/" . $objUserClass->class_id . "/user_id/" . $intChild;
			}
			else
			{
				$arrAwaitingActions[]["text"] = $arrChildren[$intChild]->first_name . " " . $arrChildren[$intChild]->last_name . " is not enrolled into any campaigns";
				$arrAwaitingActions[count($arrAwaitingActions)-1]["link"] = "/campaigns/campaignchildenroll/class_id/" . $objUserClass->class_id . "/user_id/" . $intChild;
			}
		}

		$this->view->intTotalActions = $intTotalActions;
		$this->view->arrAwaitingActions = $arrAwaitingActions;
		$this->view->arrChildren = $arrChildren;
		 *
		 */
	}

	public function profileteacherAction()
	{
		$objUsers = new Users();
		$objInstitutions = new Institutions();
		$objClasses = new Classes();
		
		$this->view->objUser = first($objUsers->getMashpiaTeacher());		
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		
		$this->view->arrClasses = $objClasses->_classes_select(array(
			'class_id' => $this->_user_session_data->class_id,
		));
		
		$this->view->class_id = $this->_user_session_data->class_id;
		$this->view->tstyle = $this->_request->getParam("tstyle");
		/*

		$objInstitutions = new Institutions();
		$objPermissions = new Permissions();
		$objCampaigns = new Campaigns();
		$objClasses = new Classes();



		if ($this->_request->isPost())
		{
			$intClass = intval($this->_request->getPost("class_id"));
			$objClasses->_user_classes_delete(array(
				"user_id" => $this->_user_session_data->user_id,
				"class_role" => "Teacher"
			));
			if ($intClass > 0)
			{
				$objClasses->_user_classes_insert(array(
					"class_id" => $intClass,
					"user_id" => $this->_user_session_data->user_id,
					"class_role" => "Teacher"
				));
			}
			print 1;
			exit;
		}

		$intMaxAwaitingActions = 25;

		$this->view->strExtraActions = "";



		$this->view->arrClasses = $objClasses->_classes_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrChildren = array();
		$arrAwaitingActions = array();

		// Get the current class the teacher is in
		$objUserClass = first($objClasses->_user_classes_select(array(
			"user_id" => $this->_user_session_data->user_id,
			"class_role" => "Teacher"
		)));
		if (!$objUserClass)
		{
			$this->view->boolNoClass = true;
			return;
		}
		$this->view->objClass = first($objClasses->_classes_select(array(
			"class_id" => $objUserClass->class_id
		)));
		$arrClassStudents = $objClasses->_user_classes_select(array(
			"class_id" => $objUserClass->class_id,
			"class_role" => "Student"
		));
		$arrUsersIds = array();
		foreach ($arrClassStudents as $objUserClass)
		{
			$arrUsersIds[] = $objUserClass->user_id;
		}
		$arrUsers = $objUsers->_users_select(array(
			"user_id" => $arrUsersIds
		));
		$intTotalActions = 0;
		foreach ($arrUsers as $objUser)
		{
			if (!isset($arrChildren[$objUser->user_id]))
			{
				$arrChildren[$objUser->user_id] = $objUser;
				if (!preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", $objUser->dob))
				{
					$intTotalActions++;
					if (count($arrAwaitingActions) >= $intMaxAwaitingActions)
						continue;
					$arrAwaitingActions[]["text"] = $objUser->first_name . " " . $objUser->last_name . " does not have a valid birthdate";
					$arrAwaitingActions[count($arrAwaitingActions)-1]["link"] = "/users/editor/user_types/student/user_type/student/user_id/" . $objUser->user_id;
				}
			}
		}
		// Children are and are not enrolled into any book campaigns
		$arrChildrenCampaignEnrollment = $objCampaigns->_user_campaigns_select(array(
			"user_id" => array_keys($arrChildren),
			"institution_id" => $this->_user_session_data->institution_id,
			"status" => "Enrollment"
		));
		$arrChildrenInCampaigns = array();
		foreach ($arrChildrenCampaignEnrollment as $objUserCampaign)
		{
			$arrChildrenInCampaigns[$objUserCampaign->user_id] = $objUserCampaign;
		}
		$arrChildrenWithNoCampaignIds = array_diff(array_keys($arrChildren), array_keys($arrChildrenInCampaigns));
		foreach ($arrChildrenWithNoCampaignIds as $intChild)
		{
			$intTotalActions++;
			if (count($arrAwaitingActions) >= 25)
				continue;
			$objUserClass = first($objClasses->_user_classes_select(array(
				"user_id" => $intChild
			)));
			$arrAwaitingActions[]["text"] = $arrChildren[$intChild]->first_name . " " . $arrChildren[$intChild]->last_name . " is not enrolled into any campaigns";
			$arrAwaitingActions[count($arrAwaitingActions)-1]["link"] = "/campaigns/campaignchildenroll/class_id/" . $objUserClass->class_id . "/user_id/" . $intChild;
		}
		$this->view->intTotalActions = $intTotalActions;
		$this->view->arrAwaitingActions = $arrAwaitingActions;
		$this->view->arrChildren = $arrChildren;
		 *
		 */
	}

	public function campaignenrollmentlistAction()
	{
		$intUser = $this->view->user_id = intval($this->_request->getParam("user_id"));
		if (!$intUser)
		{
			print text("Sorry, there was an error") . ": CA-CEL101-7SDFDD";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objScheduler = new Scheduler();

		if ($this->_request->isPost())
		{
			$intCampaign = intval($this->_request->getPost("campaign_id"));
			if (!$intCampaign)
			{
				print text("Sorry, there was an error") . ": CA-CEL101-4R43G4";
				exit;
			}
			$intLinesAhead = intval($this->_request->getPost("lines_ahead"));
			$intLadder = intval($this->_request->getPost("ladder"));
			$strAction = $this->_request->getPost("action");
			if ($strAction == "enroll")
			{
				$arrUserCampaigns = $objCampaigns->_user_campaigns_select(array(
					"user_id" 			=> $intUser,
					"campaign_id"		=> $intCampaign,
					"status"			=> "Enrollment"
				));
				if (count($arrUserCampaigns))
				{
					print "You are already enrolled to this campaign.";
					exit;
				}
				$intAI = $objCampaigns->_user_campaigns_insert(array(
					"user_id" 			=> $intUser,
					"institution_id"	=> $this->_user_session_data->institution_id,
					"campaign_id"		=> $intCampaign,
					"status"			=> "Enrollment",
					"ladder"			=> $intLadder,
					"schedule_date"		=> time(),
					"line_offset"		=> $intLinesAhead
				));
			}
			else // unenroll
			{
				$objCampaigns->campaign_usercampaign_delete($intUser, $intCampaign);
			}
			print 1;
			exit; // ajax
		}

		$arrEnrolledCampaigns = $objCampaigns->_user_campaigns_select(array(
			"user_id" => $intUser,
			"status" => "Enrollment"
		));
		$arrEnrolledHash = array();
		foreach ($arrEnrolledCampaigns as $objCampaign)
		{
			$arrEnrolledHash[$objCampaign->campaign_id] = $objCampaign;
		}
		$arrCampaigns = $objScheduler->load_campaigns(array(
			"user_id" => $intUser,
			"institution_id" => $this->_user_session_data->institution_id
		));

		foreach ($arrCampaigns as $intKey => $objCampaign)
		{
			$arrCampaigns[$intKey]->boolEnrolled = isset($arrEnrolledHash[$objCampaign->campaign_id]);
			$arrLadders = $objScheduler->load_available_ladders2(array(
				"user_id" 			=> $intUser,
				"institution_id"	=> $this->_user_session_data->institution_id,
				"campaign_id"		=> $objCampaign->campaign_id
			));
			$arrCampaigns[$intKey]->arrLadders = $arrLadders;
		}
		$this->view->arrCampaigns = $arrCampaigns;
	}

	public function campaignladdersAction()
	{
		$intUser = $this->view->user_id = intval($this->_request->getParam("user_id"));
		if (!$intUser)
		{
			print text("Sorry, there was an error") . ": CA-CL101-DF8GF8";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objScheduler = new Scheduler();
		$objUsers = new Users();
		$objLadders = new Ladders();

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			foreach ($arrPost as $strKey => $intLadder)
			{
				if (preg_match("/^campaign_([0-9]+)$/", $strKey, $arrMatched))
				{
					$intCampaign = intval($arrMatched[1]);
					if (!$intCampaign)
					{
						print text("Sorry, there was an error") . ": CA-CL101-SDF6DD";
						exit;
					}
					$intVelocity = $objLadders->grade_ladder_task_velocity(array(
						"user_id" => $intUser,
						"campaign_id" => $intCampaign,
						"ladder" => intval($intLadder),
					));

					// Change the ladder
					$objCampaigns->_user_campaigns_update(array(
						"values" => array(
							"ladder" => intval($intLadder),
							"ladder_velocity" => $intVelocity
						),
						"where" => array(
							"user_id" => $intUser,
							"status" => "Enrollment",
							"campaign_id" => $intCampaign
						)
					));
					// Alter pending task
					$objCampaigns->_user_campaigns_update(array(
						"values" => array(
							"ladder" => intval($intLadder),
							"ladder_velocity" => $intVelocity
						),
						"where" => array(
							"user_id" => $intUser,
							"status" => "In Progress",
							"campaign_id" => $intCampaign
						)
					));


				}
			}
			print 1;
			exit;
		}

		$this->view->objUser = current($objUsers->_users_select(array(
			"user_id" => $intUser
		)));

		$arrCampaigns = $objCampaigns->_user_campaigns_select(array(
			"user_id" => $intUser,
			"status" => "Enrollment"
		));
		foreach ($arrCampaigns as $intKey => $objUserCampaign)
		{
			$arrCampaigns[$intKey]->arrLadders = $objScheduler->load_available_ladders2(array(
				"user_id" 			=> $intUser,
				"institution_id"	=> $this->_user_session_data->institution_id,
				"campaign_id"		=> $objUserCampaign->campaign_id
			));
			$arrCampaigns[$intKey]->objCampaign = current($objCampaigns->_campaigns_select(array(
				"campaign_id" => $objUserCampaign->campaign_id
			)));
		}
		$this->view->arrCampaigns = $arrCampaigns;
	}


	public function userslistreAction()
	{
		$objUsers = new Users();
		$objRoles = new Roles();
		$objClasses = new Classes();

		$intClass = $this->view->class_id = $this->_request->getParam("class_id");

		$arrUserTypes = explode(",", $this->_request->getParam("usertypes"));

		$arrResults = array();

		// Institution Administrator
		if (in_array("institutions", $arrUserTypes)) {
			$arrResults["Admins"] = array(
				"arrList" => $objUsers->_users_select_hierarchal(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"permission" => "Institution Administrator",
					"class_id" => $intClass
				)),
				"strUserType" => "Admins"
			);
		}

		// Teachers
		if (
			in_array("teachers", $arrUserTypes)
			|| in_array("counselors", $arrUserTypes)
		) {
			$arrResults["Teachers"] = array(
				"arrList" => $objUsers->_users_select_hierarchal(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"permission" => "Teacher",
					"class_id" => $intClass
				)),
				"strUserType" => $this->_user_session_data->institution_type == "School" ? "Teachers" : "Counselors"
			);
		}

		// Students
		if (
			in_array("student", $arrUserTypes)
			|| in_array("campers", $arrUserTypes)
		) {
			if (
				!$intClass
				&& $objRoles->isRole("Teacher")
			) {
				$arrTeacherClasses = array_hash("class_id", $objClasses->_user_classes_select(array(
					"user_id" => $this->_user_session_data->user_id,
					"user_role" => "Teacher"
				)));
				// Get the students that are in those classes and create their encoded values
				$arrStudentIds = array_keys(array_hash("user_id", $objClasses->_user_classes_select(array(
					"class_id" => array_keys($arrTeacherClasses),
					"class_role" => "Student"
				))));
				$arrResults["Students"] = array(
					"arrList" => $objUsers->_users_select(array(
						"user_id" => $arrStudentIds
					)),
					"strUserType" => $this->_user_session_data->institution_type == "School" ? "Students" : "Campers"
				);
			}
			else
			{
				$arrResults["Students"] = array(
					"arrList" => $objUsers->_users_select_hierarchal(array(
						"institution_id" => $this->_user_session_data->institution_id,
						"permission" => "Student",
						"class_id" => $intClass
					)),
					"strUserType" => $this->_user_session_data->institution_type == "School" ? "Students" : "Campers"
				);
			}
		}

		$arrUsers = array();

		// Define the order of the items
		if (isset($arrResults["Admins"]))
			$arrUsers[] = $arrResults["Admins"];
		if (isset($arrResults["Teachers"]))
			$arrUsers[] = $arrResults["Teachers"];
		if (isset($arrResults["Students"]))
			$arrUsers[] = $arrResults["Students"];

		$this->view->arrUsers = $arrUsers;
	}

	public function importusersAction()
	{
		$query = new QueryGen();
		$objClasses = new Classes();
		$this->view->tstyle = $this->_request->getParam('tstyle');
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$arrClasses = $objClasses->_classes_select(array(
			'user_id' => $this->_user_session_data->user_id,
			'institution_id' => $this->_user_session_data->institution_id
		));
		$arrClassLCNameHash = array();
		foreach ($arrClasses as $objClass)
		{
			$arrClassLCNameHash[trim(strtolower($objClass->custom_name1))] = $objClass;
		}
		$this->view->arrClassNames = array_stack('custom_name1', $arrClasses);
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (!empty($arrPost['import_users']))
			{
				if (!is_array($arrPost['arrData']))
				{
					json(array(
						'error' => 'Sorry, there was an error: CA-IU101-g3g3g5'
					));
				}
				$arrRowIssues = array();
				$arrClassIssues = array();
				foreach ($arrPost['arrData'] as $intItr => $arrRow)
				{
					if (
						empty($arrRow['first_name'])
						|| empty($arrRow['last_name'])
					) {
						$arrRowIssues[] = $intItr+1;
					} else {
						$arrPost['arrData'][$intItr]['first_name'] = trim($arrRow['first_name']);
						$arrPost['arrData'][$intItr]['last_name'] = trim($arrRow['last_name']);
					}
					$arrPost['arrData'][$intItr]['objClass'] = NULL;
					if (!empty($arrRow['class']))
					{
						$arrRow['class'] = preg_replace('/[^ 0-9a-z]/i', '', $arrRow['class']);
						if (!isset($arrClassLCNameHash[strtolower($arrRow['class'])]))
							$arrClassIssues[] = $intItr+1;
						else
						{
							$objClass = $arrClassLCNameHash[strtolower($arrRow['class'])];
							$arrPost['arrData'][$intItr]['objClass'] = $objClass;
						}
					}
				}
				if (count($arrRowIssues))
				{
					json(array(
						'error' => 'There were required fields were missing on row: ' . join(', ', $arrRowIssues)
					));
				}
				if (count($arrClassIssues))
				{
					json(array(
						'error' => 'There were items that did not match on row: ' . join(', ', $arrClassIssues)
					));
				}
				$strCustomFields = $objInstitution->custom_fields;
				$arrCustomFields = array();
				if (empty($strCustomFields))
					$arrCustomFields = array();
				else
					$arrCustomFields = unserialize($strCustomFields);
				foreach ($arrPost['arrData'] as $intItr => $arrUser)
				{
					do {
						$intBarCode = rand_num_string(16);
						$objBarcode = first($query->users__select(array(
							'bar_code' => $intBarCode
						)));
					} while ($objBarcode);
					// custom fields
					$arrUserFields = array();
					$intField = -1;
					foreach ($arrCustomFields as $arrRow)
					{
						$intField++;
						$strField = $arrRow['field_name'];
						if (!isset($arrUser['custom_field_' . $intField]))
							continue;
						$strFieldValue = $arrUser['custom_field_' . $intField];
						unset($arrUser['custom_field_' . $intField]);
						if (empty($strFieldValue))
							continue;
						$arrUserFields[$strField] = array(
							'value' => $strFieldValue
						);
					}
					if (!isset($arrUser['email']))
						$arrUser['email'] = $intBarCode . "nomail@mashpia.com";
					$arrUserImportParams = array(
						"first_name" => $arrUser["first_name"],
						"last_name" => $arrUser["last_name"],
						"student_email" => @$arrUser["student_email"],
						"cell" => @$arrUser["cell"],
						"phone" => @$arrUser["phone"],
						"bar_code" => $intBarCode,
						"is_active" => 1,
						"image_id" => "",
						'custom_fields' => serialize($arrUserFields)
					);
					//$arrUserImportParams[]
					$intUserID = $query->users__insert($arrUserImportParams);
					$intPermissionID = $query->permissions__insert(array(
						"user_id" => $intUserID,
						"institution_id" => $this->_user_session_data->institution_id,
						"permission" => "Student",
						"default_permission" => 1,
						"template_style" => $this->_user_session_data->template_style
					));
					if ($arrUser["objClass"])
					{
						$intClassID = $query->user_classes__insert(array(
							"class_id" => $arrUser["objClass"]->class_id,
							"user_id" => $intUserID,
							"class_role" => "Student",
							"institution_id" => $this->_user_session_data->institution_id
						));
					}
				}
				// validate all data
				json(array(
					'success' => 'true'
				));
			}
		}
	}

    public function studentimportAction()
    {
		$objUser = new Users();
		$objClasses = new Classes();
        $this->view->caption = $this->_request->getParam('caption');
		$this->view->arrClasses = $objClasses->_classes_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
        $strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");

		if($this->_request->isPost()){
			$mode = $this->_request->mode;
			switch($mode){
				case "getcsvdata":
					$file_name = $this->_request->getParam("file_name");
					//get csv content and push it to view to display in preview window
					$result = $objUser->user_import($file_name);
					$jsondata = json_encode($result);
					echo $jsondata;
					//echo addcslashes($jsondata, "'");

					break;
				case "insertdata":
					$arrResult = $objUser->batch_user_import(array(
						"arrUsersInfo" => $this->_utilities->sliced_to_stacked_array_converter($_POST),
						"institution_id" => $this->_user_session_data->institution_id,
						"template_style" => $strTemplateStyle
					));
                    echo json_encode($arrResult);
					break;
				case "getinstitutions":
					$result = $objUser->getUserIstitutions();
					echo json_encode($result);
					//echo addcslashes(json_encode($result), "'");
					break;
				case "getclasses":
					$classes = new Classes();
					$institution_id = $this->_request->institution_id;
					$result = $classes->get_classes_by_institution_id($institution_id);
                    if(count($result))
                    {
                        $i = 0;
                        foreach($result as $r)
                        {
                            $arrClasses['classes'][$i]['class_id'] = $r->class_id;
                            //$arrClasses['classes'][$i]['class_name'] = $r->grade . ' ' . $r->sub; //for schools
                            $arrClasses['classes'][$i]['class_name'] = $r->sub; // for camps
                            $i++;
                        }
                        echo json_encode($arrClasses);
                        break;
                    }
                    else{
                        echo text("You have to create classes first!");
                        break;
                    }
			}
			exit;
		}else{
			$this->view->arrStudents = array();
		}
    }

    public function userimportAction()
    {
		$objUser = new Users();
		$objClasses = new Classes();
        $this->view->caption = $this->_request->getParam('caption');
		$this->view->arrClasses = $objClasses->_classes_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		));

		if($this->_request->isPost()){
			$mode = $this->_request->mode;
			switch($mode){
				case "insertdata":
					$arrResult = $objUser->batch_user_import(array(
						"arrUsersInfo" => $this->_utilities->sliced_to_stacked_array_converter($_POST),
						"institution_id" => $this->_user_session_data->institution_id
					));
                    echo json_encode($arrResult);
					break;
				case "getinstitutions":
					$result = $objUser->getUserIstitutions();
					echo json_encode($result);
					//echo addcslashes(json_encode($result), "'");
					break;
				case "getclasses":
					$classes = new Classes();
					$institution_id = $this->_request->institution_id;
					$result = $classes->get_classes_by_institution_id($institution_id);
                    if(count($result))
                    {
                        $i = 0;
                        foreach($result as $r)
                        {
                            $arrClasses['classes'][$i]['class_id'] = $r->class_id;
                            //$arrClasses['classes'][$i]['class_name'] = $r->grade . ' ' . $r->sub; //for schools
                            $arrClasses['classes'][$i]['class_name'] = $r->sub; // for camps
                            $i++;
                        }
                        echo json_encode($arrClasses);
                        break;
                    }
                    else{
                        echo "You have to create classes first!";
                        break;
                    }
			}
			exit;
		}else{
			$this->view->arrStudents = array();
		}
    }

    public function importuserdataAction()
    {

    }

	public function downloadtemplateAction()
	{

	}

	public function profileeditorAction()
	{
		$query = new QueryGen();
		$objRole = new Roles();
		$intInstitution = $this->_user_session_data->institution_id;
		if ($objRole->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$intInstitution = $this->_request->getParam('institution_id');
		}
		$this->view->institution_id = $intInstitution;
		$objInstitutions = new Institutions();
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
        if ($this->_request->isPost())
        {
			$query->institutions__update(array(
				"where" => array(
					"institution_id" => $intInstitution
				),
				"values" => array(
					"name" => $this->_request->getPost("institution_name"),
					"hebrew_name" => $this->_request->getPost("hebrew_name"),
					"address" => $this->_request->getPost("address"),
					"city" => $this->_request->getPost("city"),
					"state" => $this->_request->getPost("state"),
					"country" => $this->_request->getPost("country"),
					"phone" => $this->_request->getPost("phone"),
					"postal" => $this->_request->getPost("postal"),
					"website" => $this->_request->getPost("website"),
					"image_id" => $this->_request->getPost("image_id"),
					"light_image_id" => $this->_request->getPost("light_image_id")
				)
			));
			print 1;
			exit;
		}
	}
}
?>