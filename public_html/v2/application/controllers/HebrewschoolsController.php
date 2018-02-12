<?php
class HebrewschoolsController extends Zend_Controller_Action
{
	private $_sesh;
	private $objPermission; // permission instance
	private $boolVerbose = 0;

	function preDispatch()
	{
		$query = new QueryGen();
		$this->_sesh = new Zend_Session_Namespace('hebrewschools');
		$arrParams = $this->_request->getParams();
		$utilities = new Utilities();
		if (
			$this->_request->getParam('action') == 'processbarcode2'
			&& $this->_request->getParam("userbarcode")
		) {
			// dont check for session if barcode is available
		}
		else if ($this->_request->getParam('action') != 'index')
		{
			$this->objPermission = $utilities->dispatch_helper_hebrewschools($arrParams);
			$this->view->arrStudentPermissions = $query->permissions__select(array(
				'user_id' => $this->objPermission->user_id,
				'permission' => 'Student',
				'_GREATER' => array(
					'registration_expiration' => time()
				)
			));
			$this->view->objUser = $query->users__select(array(
				'user_id' => $this->objPermission->user_id
			));
		}
	}
	function distroysession()
	{
		if (isset($_SESSION['hebrewschools']))
			unset($_SESSION['hebrewschools']);
		if (isset($_COOKIE["hebrewschools_store_cart"]))
			setcookie ("hebrewschools_store_cart", "", time() - 86400, '/', parse_url(WEB_ROOT, PHP_URL_HOST));
	}

	function indexAction()
	{
		// all session data must be cleared before a user is logged in
		$this->distroysession();
		$objConfig = new Config();
		$objHebrewSchools = new HebrewSchools();
		$this->view->arrGet = $arrGet = $this->_request->getParams();
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		if (isset($arrGet['from_widget']) && empty($strTemplateStyle))
		{
			$strTemplateStyle = "chabadhebrewschool";
		}
		$this->_sesh = new Zend_Session_Namespace('hebrewschools');
		$this->_sesh->template_style = $strTemplateStyle;
		$this->view->template_style = $strTemplateStyle;
		$this->view->tstyle = $strTemplateStyle;
		$this->view->arrKioskLogos = $objConfig->load(array(
			"set" => array("kiosk_logos", "kiosk_logos_on"),
			"institution_id" => $this->view->institution_id
		));
		if ($this->_request->isPost())
		{
			// Validate and authenticate
			$intBarCode = $this->_request->getPost('bar_code');
			$arrResults = $objHebrewSchools->KioskAuthenticate(array(
				"bar_code" => $intBarCode,
				"template_style" => $strTemplateStyle
			));
			print json_encode($arrResults);
			exit;
		}
	}

	function logoutAction()
	{
		global $arrInstituionDetails;
		$this->view->institution_id = $this->_request->getParam("institution_id");
		if (isset($arrInstituionDetails[$this->_sesh->institution_id]['logout_link']))
		{
			$strLink = $arrInstituionDetails[$this->_sesh->institution_id]['logout_link'];
			$this->distroysession();
			header('Location: ' . $strLink);
			exit;
		}
		$this->distroysession();
		$strTemplateStyle = $this->_request->getParam('tstyle');
		$strParamPath = "";
		if (!empty($strTemplateStyle))
			$strParamPath .= '/tstyle/' . $strTemplateStyle;
		if (!empty($this->view->institution_id))
			$strParamPath .= '/institution_id/' . $this->view->institution_id;
		$this->_redirect('hebrewschools/index' . $strParamPath);
	}

	function announcementsAction()
	{
		$query = new QueryGen();
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$tstyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		$this->_helper->layout->setLayout('hebrewschools');
		$this->_helper->layout()->bgColor = "lgreen";
		$this->_helper->layout()->strTitle = "Hebrew Schools";
		$this->view->objInstitution = first($query->institutions__select(array(
			'institution_id' => $this->_sesh->institution_id
		)));
		$this->view->objUser = first($query->users__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$this->view->arrCampaigns = $arrCampaigns = array_hash('campaign_id', $query->campaigns__select(array(
			'institution_id' => $this->_sesh->institution_id,
			'is_active' => 1
		)));
		$this->view->arrTasks = $arrTasks = array_bubble_hash('campaign_id', $query->tasks__select(array(
			'campaign_id' => array_keys($arrCampaigns),
			'is_active' => 1
		)));
		$this->view->objUserClass = first($query->user_classes__select(array(
			"user_id" => $this->_sesh->user_id,
			'institution_id' => $this->_sesh->institution_id
		)));

		$arrGet = $this->_request->getParams();
		$arrGet['intItemsPerPage'] = $intItemsPerPage = 7;
		if (!isset($arrGet['intPage']))
			$arrGet['intPage'] = 0;
		$arrPost = $this->_request->getPost();
		$arrAnnouncementParams = array(
			'institution_id' => $this->_sesh->institution_id,
			'status' => 'Published',
			'_LIMIT' => $intItemsPerPage,
			'_ORDER' => 'announcement_id DESC'
		);
		if (isset($arrGet['ajax']))
		{
			if ($arrPost['strItem'] == 'remove request')
			{
				$query->announcements__update(array(
					'where' => array(
						'announcement_id' => $arrPost['announcement_id'],
						'institution_id' => $this->_sesh->institution_id,
						'status' => 'Publish Request',
						'created_by' => $this->_sesh->user_id
					),
					'values' => array(
						'status' => 'Saved',
						'class_id' => ''
					)
				));
				json(array(
					'success' => 'true'
				));
			}
			else if ($arrPost['strItem'] == 'request publish')
			{
				$arrResponse = array();
				if (strlen($arrPost['strTitle']) < 4 || strlen($arrPost['strTitle']) > 75) {
					$arrResponse['error'] = "The headline must be between 4-75 charcters";
					print json_encode($arrResponse);
					exit;
				}
				if (strlen($arrPost['strContent']) < 10) {
					$arrResponse['error'] = "The contents cannot be blank";
					print json_encode($arrResponse);
					exit;
				}
				$arrStudents = array();
				if (count($arrPost['arrStudents'])) {
					$arrStudents = array_stack('user_id', $query->permissions__select(array(
						'institution_id' => $this->_sesh->institution_id,
						'user_id' => $arrPost['arrStudents']
					)));
				}
				$intClass = NULL;
				if ($arrPost['strPublishLocation'] == 'class')
				{
					$arrUserClasses = array_stack('class_id', $query->user_classes__select(array(
						'user_id' => $this->_sesh->user_id
					)));
					$objClass = first($query->classes__select(array(
						'class_id' => $arrUserClasses,
						'institution_id' => $this->_sesh->institution_id
					)));
					if (!$objClass)
					{
						print json_encode(array(
							'error' => "You must be in a class to do this."
						));
						exit;
					}
					$intClass = $objClass->class_id;
				}
				if ($arrPost['intAnnouncement'] == 'new')
				{
					$intAnnouncement = $query->announcements__insert(array(
						'class_id' => $intClass,
						'institution_id' => $this->_sesh->institution_id,
						'status' => 'Publish Request',
						'headline' => $arrPost['strTitle'],
						'content' => $arrPost['strContent'],
						'campaign_id' => @$arrPost['intCampaign'],
						'task_id' => @$arrPost['intTask'],
						'created_by' => $this->_sesh->user_id
					));
				}
				else
				{
					$objAnnouncement = first($query->announcements__select(array(
						'announcement_id' => $arrPost['intAnnouncement'],
						'institution_id' => $this->_sesh->institution_id,
						'created_by' => $this->_sesh->user_id
					)));
					if (!$objAnnouncement)
					{
						$arrResponse['error'] = "Sorry, there was an error: CHS-A101-34g4gd";
						print json_encode($arrResponse);
						exit;
					}
					$intAnnouncement = $arrPost['intAnnouncement'];
					$query->announcements__update(array(
						'where' => array(
							'announcement_id' => $arrPost['intAnnouncement']
						),
						'values' => array(
							'class_id' => $intClass,
							'headline' => $arrPost['strTitle'],
							'content' => $arrPost['strContent'],
							'status' => 'Publish Request',
							'campaign_id' => !empty($arrPost['intCampaign']) ? $arrPost['intCampaign'] : 0,
							'task_id' => !empty($arrPost['intTask']) ? $arrPost['intTask'] : 0
						)
					));
					$query->announcement_students__delete(array(
						'announcement_id' => $arrPost['intAnnouncement']
					));
				}
				if (count($arrStudents))
				{
					foreach ($arrStudents as $intUser)
					{
						$query->announcement_students__insert(array(
							'announcement_id' => $intAnnouncement,
							'user_id' => $intUser
						));
					}
				}
				print json_encode(array(
					'success' => 'true'
				));
				exit;
			}
			else if ($arrPost['strItem'] == 'move to trash')
			{
				$arrResponse = array();
				$arrStudents = array();
				if (count($arrPost['arrStudents'])) {
					$arrStudents = array_stack('user_id', $query->permissions__select(array(
						'institution_id' => $this->_sesh->institution_id,
						'user_id' => $arrPost['arrStudents']
					)));
				}
				if ($arrPost['intAnnouncement'] == 'new')
				{
					if (!empty($arrPost['strTitle']) && !empty($arrPost['strTitle']))
						$intAnnouncement = $query->announcements__insert(array(
							'institution_id' => $this->_sesh->institution_id,
							'status' => 'Deleted',
							'headline' => $arrPost['strTitle'],
							'content' => $arrPost['strContent'],
							'created_by' => $this->_sesh->user_id,
							'campaign_id' => !empty($arrPost['intCampaign']) ? $arrPost['intCampaign'] : 0,
							'task_id' => !empty($arrPost['intTask']) ? $arrPost['intTask'] : 0
						));
				}
				else
				{
					$objAnnouncement = first($query->announcements__select(array(
						'announcement_id' => $arrPost['intAnnouncement'],
						'institution_id' => $this->_sesh->institution_id,
						'created_by' => $this->_sesh->user_id
					)));
					if (!$objAnnouncement)
					{
						$arrResponse['error'] = "Sorry, there was an error: CHS-A101-34g4gd";
						print json_encode($arrResponse);
						exit;
					}
					$intAnnouncement = $arrPost['intAnnouncement'];
					$query->announcements__update(array(
						'where' => array(
							'announcement_id' => $arrPost['intAnnouncement']
						),
						'values' => array(
							'headline' => $arrPost['strTitle'],
							'content' => $arrPost['strContent'],
							'status' => 'Deleted',
							'campaign_id' => !empty($arrPost['intCampaign']) ? $arrPost['intCampaign'] : 0,
							'task_id' => !empty($arrPost['intTask']) ? $arrPost['intTask'] : 0
						)
					));
					$query->announcement_students__delete(array(
						'announcement_id' => $arrPost['intAnnouncement']
					));
				}
				if (count($arrStudents) && $intAnnouncement)
				{
					foreach ($arrStudents as $intUser)
					{
						$query->announcement_students__insert(array(
							'announcement_id' => $intAnnouncement,
							'user_id' => $intUser
						));
					}
				}
				print json_encode(array(
					'success' => 'true'
				));
				exit;
			}
			else if ($arrPost['strItem'] == 'save_new')
			{
				$arrResponse = array();
				if (strlen($arrPost['strTitle']) < 4 || strlen($arrPost['strTitle']) > 75) {
					$arrResponse['error'] = "The headline must be between 4-75 charcters";
					print json_encode($arrResponse);
					exit;
				}
				if (strlen($arrPost['strContent']) < 10) {
					$arrResponse['error'] = "The contents cannot be blank";
					print json_encode($arrResponse);
					exit;
				}
				$arrStudents = array();
				if (count($arrPost['arrStudents'])) {
					$arrStudents = array_stack('user_id', $query->permissions__select(array(
						'institution_id' => $this->_sesh->institution_id,
						'user_id' => $arrPost['arrStudents']
					)));
				}
				if ($arrPost['intAnnouncement'] == 'new')
				{
					$intAnnouncement = $query->announcements__insert(array(
						'institution_id' => $this->_sesh->institution_id,
						'status' => 'Saved',
						'campaign_id' => @$arrPost['intCampaign'],
						'task_id' => @$arrPost['intTask'],
						'headline' => $arrPost['strTitle'],
						'content' => $arrPost['strContent'],
						'created_by' => $this->_sesh->user_id
					));
				}
				else
				{
					$objAnnouncement = first($query->announcements__select(array(
						'announcement_id' => $arrPost['intAnnouncement'],
						'institution_id' => $this->_sesh->institution_id,
						'created_by' => $this->_sesh->user_id
					)));
					if (!$objAnnouncement)
					{
						$arrResponse['error'] = "Sorry, there was an error: CHS-A101-34g4gd";
						print json_encode($arrResponse);
						exit;
					}
					$intAnnouncement = $arrPost['intAnnouncement'];
					$query->announcements__update(array(
						'where' => array(
							'announcement_id' => $arrPost['intAnnouncement']
						),
						'values' => array(
							'headline' => $arrPost['strTitle'],
							'content' => $arrPost['strContent'],
							'status' => 'Saved',
							'campaign_id' => !empty($arrPost['intCampaign']) ? $arrPost['intCampaign'] : 0,
							'task_id' => !empty($arrPost['intTask']) ? $arrPost['intTask'] : 0
						)
					));
					$query->announcement_students__delete(array(
						'announcement_id' => $arrPost['intAnnouncement']
					));
				}
				if (count($arrStudents))
				{
					foreach ($arrStudents as $intUser)
					{
						$query->announcement_students__insert(array(
							'announcement_id' => $intAnnouncement,
							'user_id' => $intUser
						));
					}
				}
				print json_encode(array(
					'success' => 'true'
				));
				exit;
			}
			else if ($arrPost['strItem'] == 'student_list')
			{
				$strKeywords = @$arrPost['strKeywords'];
				$arrUserPermissions = array_stack('user_id', $query->permissions__select(array(
					'institution_id' => $this->_sesh->institution_id,
					'permission' => 'Student',
					'_GREATER' => array(
						'registration_expiration' => time()
					)
				)));
				$arrUsers = $query->users__select(array(
					'_COLUMNS' => array('first_name', 'last_name', 'user_id', 'image_id'),
					'user_id' => $arrUserPermissions,
					'is_active' => 1
				));
				$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
					'_COLUMNS' => array('class_id', 'user_id'),
					'user_id' => $arrUserPermissions
				)));
				$arrClasses = array_hash('class_id', $query->classes__select(array(
					'_COLUMNS' => array('class_id', 'grade', 'sub', 'class_hierarchy'),
					'class_id' => first(array_extract2('class_id', $arrUserClasses))
				)));
				$arrList = array();
				$arrKeywords = preg_split('/ +/', $strKeywords);
				foreach ($arrUsers as $intKey => $objUser)
				{
					if (!isset($arrUserClasses[$objUser->user_id]))
						continue;
					$objUserClass = $arrUserClasses[$objUser->user_id];
					if (!isset($arrClasses[$objUserClass->class_id]))
						continue;

					$objClass = $arrClasses[$objUserClass->class_id];
					$strClassName = $objClass->grade . ' ' . $objClass->sub;
					$objUser->strClassName = $strClassName;
					$objUser->strClassHierarchy = $objClass->class_hierarchy;
					if ($strKeywords)
					{
						$strSearchString = $strClassName . ' ' . $objUser->first_name . ' ' . $objUser->last_name;
						$boolFound = true;
						foreach ($arrKeywords as $strKeyword)
						{
							if (!strlen($strKeyword))
								continue;
							if (!preg_match("/" . preg_quote($strKeyword) . "/i", $strSearchString))
							{
								$boolFound = false;
								break;
							}
						}
						if (!$boolFound)
							continue;
					}
					$arrList[] = $objUser;
				}

				// Sort the data
				$arrSortParams = array();
				array_push($arrSortParams, 'strClassHierarchy');
				array_push($arrSortParams, SORT_ASC);
				array_push($arrSortParams, SORT_NUMERIC);
				array_push($arrSortParams, 'first_name');
				array_push($arrSortParams, SORT_ASC);
				array_push($arrSortParams, SORT_STRING);
				array_push($arrSortParams, 'last_name');
				array_push($arrSortParams, SORT_ASC);
				array_push($arrSortParams, SORT_STRING);
				array_push($arrSortParams, $arrList);
				$arrList = call_user_func_array("msort", $arrSortParams);

				print json_encode(array(
					'arrUsers' => $arrList,
					'arrUserClasses' => $arrUserClasses,
					'arrClasses' => $arrClasses
				));
				exit;
			}
			else if ($arrPost['strItem'] == 'network')
			{
				$arrNetworkInstitutions = array_hash('institution_id', $query->institutions__select(array(
					'template_style' => $tstyle
				)));
				$arrAnnouncementParams = array(
					'institution_id' => array_keys($arrNetworkInstitutions),
					'status' => 'Published',
					'_LIMIT' => $intItemsPerPage,
					'_ORDER' => 'announcement_id DESC'
				);
			}
			else if ($arrPost['strItem'] == 'submissions')
			{
				$arrAnnouncementParams = array(
					'institution_id' => $this->_sesh->institution_id,
					'created_by' => $this->_sesh->user_id,
					'status' => array('Published', 'Publish Request'),
					'_LIMIT' => $intItemsPerPage,
					'_ORDER' => 'announcement_id DESC'
				);
			}
			else if ($arrPost['strItem'] == 'posts')
			{
				$arrAnnouncementParams = array(
					'institution_id' => $this->_sesh->institution_id,
					'created_by' => $this->_sesh->user_id,
					'status' => array('Saved', 'Denied Request'),
					'_LIMIT' => $intItemsPerPage,
					'_ORDER' => 'announcement_id DESC'
				);
			}
			else if ($arrPost['strItem'] == 'trash')
			{
				$arrAnnouncementParams = array(
					'institution_id' => $this->_sesh->institution_id,
					'created_by' => $this->_sesh->user_id,
					'status' => 'Deleted',
					'_LIMIT' => $intItemsPerPage,
					'_ORDER' => 'announcement_id DESC'
				);
			}
		}
		$arrAnnouncementes = $query->announcements__select($arrAnnouncementParams);
		$arrCreatedByIds = first(array_extract2('created_by', $arrAnnouncementes));
		$arrCreatedByUsers = array_hash('user_id', $query->users__select(array(
			'_COLUMNS' => array('user_id','first_name', 'last_name'),
			'user_id' => $arrCreatedByIds
		)));
		$arrAnnouncementIds = first(array_extract2('announcement_id', $arrAnnouncementes));
		$arrAnnouncementsStudents = array_bubble_hash('announcement_id', $query->announcement_students__select(array(
			'_COLUMNS' => array('user_id', 'announcement_id'),
			'announcement_id' => $arrAnnouncementIds
		)));
		$arrUsersIds = first(array_extract2('created_by', $arrAnnouncementes));
		$arrUsersIds = array_merge_real_recursive($arrUsersIds, first(array_extract2('user_id', $arrAnnouncementsStudents)));
		$arrUsersDetails = array_hash('user_id', $query->users__select(array(
			'_COLUMNS' => array('user_id', 'image_id', 'first_name', 'last_name'),
			'user_id' => $arrUsersIds
		)));
		$arrCampaignIds = first(array_extract2('campaign_id', $arrAnnouncementes));
		$arrAnnouncementCampaigns = array_hash('campaign_id', $query->campaigns__select(array(
			'campaign_id' => $arrCampaignIds
		)));
		$arrTaskIds = first(array_extract2('task_id', $arrAnnouncementes));
		$arrAnnouncementTasks = array_hash('task_id', $query->tasks__select(array(
			'task_id' => $arrTaskIds
		)));
		foreach ($arrAnnouncementes as $intKey => $objAnnouncement)
		{
			$arrAnnouncementes[$intKey]->objCreated = $arrCreatedByUsers[$objAnnouncement->created_by];
			$arrAnnouncementes[$intKey]->strSmallDate = date('m-d-Y', strtotime($objAnnouncement->created));
			$arrStudents = (object) array();
			if (isset($arrAnnouncementsStudents[$objAnnouncement->announcement_id]))
				$arrStudents = array_stack('user_id', $arrAnnouncementsStudents[$objAnnouncement->announcement_id]);
			$arrUserDetails = array();
			foreach ($arrStudents as $intUser)
			{
				if (isset($arrUsersDetails[$intUser]))
					$arrUserDetails[$intUser] = $arrUsersDetails[$intUser];
			}
			$arrAnnouncementes[$intKey]->arrAnnouncementsStudents = $arrUserDetails;
			$arrAnnouncementes[$intKey]->objCreatedBy = $arrUsersDetails[$arrAnnouncementes[$intKey]->created_by];
			if ($objAnnouncement->task_id > 0)
				$arrAnnouncementes[$intKey]->objTask = $arrAnnouncementTasks[$objAnnouncement->task_id];
			if ($objAnnouncement->campaign_id > 0)
				$arrAnnouncementes[$intKey]->objCampaign = $arrAnnouncementCampaigns[$objAnnouncement->campaign_id];
		}
		if (isset($arrGet['ajax']))
		{
			print json_encode($arrAnnouncementes);
			exit;
		}
		$arrGet['intItemsCount'] = count($arrAnnouncementes);
	}

	function profileAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$objPoints = new Points();

		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('hebrewschools');
		$this->_helper->layout()->bgColor = "green";
		$this->_helper->layout()->strTitle = "Hebrew Schools";
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$this->view->tstyle = $this->_request->getParam("tstyle");

		global $arrInstituionDetails;
		$this->view->arrInstituionDetails = @$arrInstituionDetails[$this->_sesh->institution_id];
		//dumper($this->_sesh->language_id,1,1);
		$this->view->intUserPointsTotal = $objPoints->user_total(array(
			"user_id" => $this->_sesh->user_id,
			"institution_id" => $this->_sesh->institution_id
		));
		$this->view->intUserPointsStore = $objPoints->user_store(array(
			"user_id" => $this->_sesh->user_id,
			"institution_id" => $this->_sesh->institution_id
		));
		$this->view->objUser = first($query->users__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_sesh->institution_id
		)));
		$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$arrClasses = $query->classes__select(array(
			"class_id" => array_keys($arrUserClasses)
		));
		$arrClassNames = array();
		foreach ($arrClasses as $objClass)
		{
			$arrClassNames[$objClass->class_id] = trim($objClass->grade . " " . $objClass->sub);
		}
		$this->view->arrClassNames = $arrClassNames;

		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("kiosk", "user"),
			"institution_id" => $this->_sesh->institution_id
		));
	}


	public function storewithdrawAction()
	{
		$query = new QueryGen();
		$kiosk = new Kiosk();
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$current_balance = $this->view->current_balance = $current_balance = $kiosk->getUserPointsTotal($this->_sesh->user_id);
		$this->view->current_balance = $current_balance;

		$this->view->arrUserPrizes = $arrUserPrizes = $query->user_prizes__select(array(
			"user_id" => $this->_sesh->user_id,
			"status" => "Checked Out"
		));
		$this->view->arrPrizes = array_hash("prize_id", $query->prize__select(array(
			"prize_id" => array_keys(array_hash("prize_id", $arrUserPrizes))
		)));


		$this->view->arrCheckedOutUserPrizes = $kiosk->getCheckedOutUserPrizes($this->_sesh->user_id);
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_sesh->institution_id
		)));
		$this->view->objUser = first($query->users__select(array(
			"user_id" => $this->_sesh->user_id
		)));

	}
	function processbarcode2Action()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objInstitutions = new Institutions();
		$objAchievementCards = new AchievementCards();
		$objTasks = new Tasks();
		$objPoints = new Points();
		$objClasses = new Classes();
		$objStore = new Store();

		$mixBarCode = $this->_request->getPost("mixBarCode");
		if (!$mixBarCode)
			$mixBarCode = $this->_request->getParam("mixBarCode");

		$intInstitution = $this->view->institution_id = $this->_request->getParam("institution_id");

		// allow a barcode to be posted rather than a sessions needing to be available
		$mixUserBarCode = $this->_request->getPost("userbarcode");
		if ($mixUserBarCode)
		{
			$objUser = first($query->users__select(array(
				'bar_code' => $mixUserBarCode,
				'_ORDER' => 'modified DESC',
				'_LIMIT' => 1
			)));
			if ($objUser)
			{
				print json_encode(array(
					'failure' => 'User not found'
				));
				exit;
			}
			$intUser = $objUser->user_id;
			if (!$intInstitution)
			{
				$objPermission = first($query->permissions__select(array(
					'user_id' => $objUser->user_id,
					'_ORDER' => 'modified DESC',
					'_LIMIT' => 1
				)));
				$intInstitution = $objPermission->institution_id;
			}
		}
		else
		{
			$intUser = $this->_sesh->user_id;
			if (!$intInstitution)
				$intInstitution = $this->_sesh->institution_id;
		}

		$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
			"user_id" => $intUser
		)));

		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));

		if (preg_match("/^[0-9]{20}|[0-9]{15}$/", $mixBarCode))
		{
			$objConfig = new Config();
			$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
				"set" => array("system"),
				"institution_id" => $intInstitution
			));
			// Find students current class
			$objClass = first($query->user_classes__select(array(
				"user_id" => $intUser
			)));

			// Achievement Card
			$this->view->objCard = $objCard = first($query->achievement_cards__select(array(
				"card_serial" => (string) $mixBarCode
			)));
			if (!$objCard)
			{
				$this->view->strScanCodeMessage = "This doesn't appear to be an achievement card.";
				print json_encode(array("error" => "Sorry, there was an error: CH-PB2101-f2ff3f."));
				exit;
			}
			if ($objCard->institution_id != 1 && $objCard->institution_id != $intInstitution)
			{
				$this->view->strScanCodeMessage = "This card was created for a different institution.";
				print json_encode(array("error" => "This card was created for a different institution."));
				exit;
			}
			if (!$objCard)
			{
				// Achievement cards sheet probably wasnt printed
				$this->view->strScanCodeMessage = "The scan code was not found in our system. Maybe the bar code wasn't scanned properly.";
				print json_encode(array("error" => "Sorry, there was an error: CH-PB2103-xxcvcd."));
				exit;
			}

			if (isset($objCard->task_id))
			{
				$objTask = $this->view->objTask = first($query->tasks__select(array(
					"task_id" => $objCard->task_id
				)));
				if ($objTask)
					$objCampaign = $this->view->objCampaign = first($query->campaigns__select(array(
						"campaign_id" => $objTask->campaign_id
					)));
			}

			if (
				$objCard->task_id
				&& (
					!$objTask
					|| !$objCampaign
				)
			) {
				$this->view->strScanCodeMessage = "Sorry, your scan card is not longer valid.";
				print json_encode(array("error" => "Sorry, there was an error: CH-PB2104-fadgds"));
				exit;
			}
			else if ($objCard->class_id > 0 && !$objClass && $objCard->class_id != 0)
			{
				$this->view->strScanCodeMessage = "You must be in a class to scan this card.";
				print json_encode(array("error" => "Sorry, there was an error: CH-PB2105-cxvege"));
				exit;
			}
			else if ($objCard->class_id > 0 && $objClass->class_id != $objCard->class_id)
			{
				$this->view->strScanCodeMessage = "You are not in the correct class to scan this card.";
				print json_encode(array("error" => "Sorry, there was an error: CH-PB2106-xzceeg."));
				exit;
			}
			else if($objCard->status != "scanned")
			{
				if ($objCard->campaign_id)
				{
					//deposit points to child's account and mark the achievement card as scanned
					$query->user_campaigns__insert(array(
						"user_id"			=> $intUser,
						"institution_id"	=> $intInstitution,
						"campaign_id"		=> $objCard->campaign_id,
						"mission_id"		=> $objCard->mission_id,
						"task_id"			=> $objCard->task_id,
						"class_id"			=> $objCard->class_id,
						"schedule_date"		=> time(),
						"points_given"		=> $objCard->card_points
					));
				}
				$query->user_points__insert(array(
					"achievement_card_id"	=> $objCard->achievement_card_id,
					"user_id"				=> $intUser,
					"institution_id"		=> $intInstitution,
					"campaign_id"			=> $objCard->campaign_id,
					"mission_id"			=> $objCard->mission_id,
					"task_id"				=> $objCard->task_id,
					"class_id"				=> $objCard->class_id,
					"points"				=> $objCard->card_points,
					"resource_name"			=> "specific achievement card",
					"prize_id"				=> 0
				));
				$query->achievement_cards__update(array(
					"where" => array(
						"card_serial" => $mixBarCode
					),
					"values" => array(
						"status" => "scanned"
					)
				));
				print json_encode(array("success" => "true"));
				exit;
			}
		}
		else if (strlen($mixBarCode))
		{
			$objPrize = first($query->prize__select(array(
				"bar_code" => $mixBarCode
			)));
			if ($objPrize)
			{
				if (!$objPrize->is_active)
				{
					$arrResult["error"] = "This prize has been deactived.";
					print json_encode($arrResult);
					exit;
				}
				if ($objPrize->prize_count < 1)
				{
					$arrResult["error"] = "This prize is no longer in stock.";
					print json_encode($arrResult);
					exit;
				}
				// Make sure that the child is in the same classes as the prizes
				$arrPrizeClasses = $query->prize_classes__select(array(
					"prize_id" => $objPrize->prize_id
				));
				$boolStudentInClass = 0;
				if (!count($arrPrizeClasses))
					$boolStudentInClass = 1;
				foreach ($arrPrizeClasses as $objPrizeClass)
				{
					if (isset($arrUserClasses[$objPrizeClass->class_id]))
					{
						$boolStudentInClass = 1;
					}
				}
				if (!$boolStudentInClass)
				{
					$arrResult["error"] = "This prize is no longer available in your class.";
					print json_encode($arrResult);
					exit;
				}
				// Check if its one per user
				if ($objPrize->one_per_user)
				{
					// Check if student has already gotten this prize
					$objUserPrizes = first($query->user_prizes__select(array(
						"prize_id" => $objPrize->prize_id,
						"user_id" => $intUser,
						"_LIMIT" => 1
					)));
					if ($objUserPrizes)
					{
						$arrResult["error"] = $objPrize->prize_name . " is a prize that has been limited to one per user and therefor cannot be purchased again.";
						print json_encode($arrResult);
						exit;
					}
				}
				// Make sure that the user has enough points for the prize
				$objUserPoints = first($query->user_points__select(array(
					"_SUM" => "points",
					"_GROUP_BY" => "user_id",
					"user_id" => $intUser
				)));
				$intUserStorePoints = $objUserPoints->_sum_points;
				if ($objPrize->points > $intUserStorePoints)
				{
					$arrResult["error"] = "You currently do not have enough store points to purchase a " . $objPrize->prize_name . ".";
					print json_encode($arrResult);
					exit;
				}
				// Create a serial
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

				$intUserPrizeId = $query->user_prizes__insert(array(
					"prize_id" => $objPrize->prize_id,
					"user_id" => $intUser,
					"institution_id" => $intInstitution,
					"quantity" => 1,
					"status" => "Checked Out",
					"serial" => $strSerial
				));
				$intUserPointId = $query->user_points__insert(array(
					"prize_id" => $objPrize->prize_id,
					"user_prize_id" => $intUserPrizeId,
					"user_id" => $intUser,
					"institution_id" => $intInstitution,
					"points" => -($objPrize->points),
					"resource_name" => "kiosk_barcode"
				));
				print json_encode(array(
					"success" => "true",
					"objPrize" => $objPrize
				));
			}
			else
			{
				print json_encode(array("nothing_found" => "true"));
			}
		}
		exit;
	}
	function processbarcodeAction()
	{
		$query = new QueryGen();
		$mixBarCode = $this->_request->getPost("mixBarCode");
		$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$this->view->institution_id = $this->_request->getParam("institution_id");
		if (strlen($mixBarCode))
		{
			$objPrize = first($query->prize__select(array(
				"bar_code" => $mixBarCode
			)));
			if ($objPrize)
			{
				if (!$objPrize->is_active)
				{
					$arrResult["error"] = "This prize has been deactived.";
					print json_encode($arrResult);
					exit;
				}
				if ($objPrize->prize_count < 1)
				{
					$arrResult["error"] = "This prize is no longer in stock.";
					print json_encode($arrResult);
					exit;
				}
				// Make sure that the child is in the same classes as the prizes
				$arrPrizeClasses = $query->prize_classes__select(array(
					"prize_id" => $objPrize->prize_id
				));
				$boolStudentInClass = 0;
				if (!count($arrPrizeClasses))
					$boolStudentInClass = 1;
				foreach ($arrPrizeClasses as $objPrizeClass)
				{
					if (isset($arrUserClasses[$objPrizeClass->class_id]))
					{
						$boolStudentInClass = 1;
					}
				}
				if (!$boolStudentInClass)
				{
					$arrResult["error"] = "This prize is no longer available in your class.";
					print json_encode($arrResult);
					exit;
				}
				// Check if its one per user
				if ($objPrize->one_per_user)
				{
					// Check if student has already gotten this prize
					$objUserPrizes = first($query->user_prizes__select(array(
						"prize_id" => $objPrize->prize_id,
						"user_id" => $this->_sesh->user_id,
						"_LIMIT" => 1
					)));
					if ($objUserPrizes)
					{
						$arrResult["error"] = $objPrize->prize_name . " is a prize that has been limited to one per user and therefor cannot be purchased again.";
						print json_encode($arrResult);
						exit;
					}
				}
				// Make sure that the user has enough points for the prize
				$objUserPoints = first($query->user_points__select(array(
					"_SUM" => "points",
					"_GROUP_BY" => "user_id",
					"user_id" => $this->_sesh->user_id
				)));
				$intUserStorePoints = $objUserPoints->_sum_points;
				if ($objPrize->points > $intUserStorePoints)
				{
					$arrResult["error"] = "You currently do not have enough store points to purchase a " . $objPrize->prize_name . ".";
					print json_encode($arrResult);
					exit;
				}
				// Create a serial
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

				$intUserPrizeId = $query->user_prizes__insert(array(
					"prize_id" => $objPrize->prize_id,
					"user_id" => $this->_sesh->user_id,
					"institution_id" => $this->_sesh->institution_id,
					"quantity" => 1,
					"status" => "Checked Out",
					"serial" => $strSerial
				));
				$intUserPointId = $query->user_points__insert(array(
					"prize_id" => $objPrize->prize_id,
					"user_prize_id" => $intUserPrizeId,
					"user_id" => $this->_sesh->user_id,
					"institution_id" => $this->_sesh->institution_id,
					"points" => -($objPrize->points),
					"resource_name" => "kiosk_barcode"
				));
				print json_encode(array(
					"success" => "true",
					"objPrize" => $objPrize
				));
			}
			else
			{
				print json_encode(array("nothing_found" => "true"));
			}
		}
		exit;
	}

	function achievementcardAction()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objInstitutions = new Institutions();
		$objAchievementCards = new AchievementCards();
		$objTasks = new Tasks();
		$objPoints = new Points();
		$objClasses = new Classes();
		$objStore = new Store();
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$intCard = $this->view->scan_card = $this->_request->getParam("q");

		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_sesh->institution_id
		)));

		if (preg_match("/^[0-9]{5}$/", $intCard))
		{
			// Scratch card
			$this->view->objCard = $objCard = first($objAchievementCards->_scratch_cards_select(array(
				"card_serial" => $intCard
			)));
			if (!$objCard)
			{
				// Achievement cards sheet probably wasnt printed
				$this->view->strScanCodeMessage = "The scan code was not found in our system. Maybe the bar code wasn't scanned properly.";
			}
			else if ($objCard)
			{
				if ($this->_request->isPost())
				{
					$arrPost = $this->_request->getPost();
					$strControl = intval($arrPost["control"]);
					$objCard = first($objAchievementCards->_scratch_cards_select(array(
						"card_serial" => $intCard,
						"card_control" => $strControl
					)));
					if (!$objCard || $strControl < 1)
					{
						$this->view->strScanCodeMessage = "The control code ($strControl) was not found, please try card ($intCard) again.";
						print 'alert("' . $this->view->strScanCodeMessage . '");';
						exit;
					}
					else if ($objCard->status == "scanned")
					{
						$this->view->strScanCodeMessage = "The scratch card entred seems to be used.";
						print 'alert("The scratch card entred seems to be used. If you think this was indeed a new card please contact an administrator about this issue.");';
						exit;
					}
					else
					{
						$intPointsKey = $query->user_points__insert(array(
							"institution_id"	=> $this->_sesh->institution_id,
							"card_serial"		=> $objCard->card_serial . ":" . $objCard->card_control,
							"user_id"			=> $this->_sesh->user_id,
							"points"		=> $objCard->card_points
						));
						$objAchievementCards->_scratch_cards_update(array(
							"values" => array(
								"status" => "scanned",
								"user_point_id" => $intPointsKey
							),
							"where" => array(
								"card_serial" => $intCard,
								"card_control" => $strControl
							)
						));
						print 'window.parent.add_to_miles(' . $objCard->card_points . ');';
						print 'window.parent.Shadowbox.close();';
						exit;
					}
				}
				$this->view->boolHarvestControlCode = true;
			}
		}
		else if (preg_match("/^[0-9]{20}|[0-9]{15}$/", $intCard))
		{
			$objConfig = new Config();
			$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
				"set" => array("system"),
				"institution_id" => $this->_sesh->institution_id
			));
			// Find students current class
			$objClass = first($query->user_classes__select(array(
				"user_id" => $this->_sesh->user_id
			)));

			// Achievement Card
			$this->view->objCard = $objCard = first($query->achievement_cards__select(array(
				"card_serial" => (string) $intCard
			)));
			if (!$objCard)
			{
				$this->view->strScanCodeMessage = "This doesn't appear to be an achievement card.";
				return;
			}
			if ($objCard->institution_id != 1 && $objCard->institution_id != $this->_sesh->institution_id)
			{
				$this->view->strScanCodeMessage = "This card was created for a different institution.";
				return;
			}
			if (!$objCard)
			{
				// Achievement cards sheet probably wasnt printed
				$this->view->strScanCodeMessage = "The scan code was not found in our system. Maybe the bar code wasn't scanned properly.";
				return;
			}

			if (isset($objCard->task_id))
			{
				$objTask = $this->view->objTask = first($query->tasks__select(array(
					"task_id" => $objCard->task_id
				)));
				if ($objTask)
					$objCampaign = $this->view->objCampaign = first($query->campaigns__select(array(
						"campaign_id" => $objTask->campaign_id
					)));
			}

			if (
				$objCard->task_id
				&& (
					!$objTask
					|| !$objCampaign
				)
			) {
				$this->view->strScanCodeMessage = "Sorry, your scan card is not longer valid.";
				return;
			}
			else if ($objCard->class_id > 0 && !$objClass && $objCard->class_id != 0)
			{
				$this->view->strScanCodeMessage = "You must be in a class to scan this card.";
				return;
			}
			else if ($objCard->class_id > 0 && $objClass->class_id != $objCard->class_id)
			{
				$this->view->strScanCodeMessage = "You are not in the correct class to scan this card.";
				return;
			}
			else if($objCard->status != "scanned")
			{
				if ($objCard->campaign_id)
				{
					//deposit points to child's account and mark the achievement card as scanned
					$query->user_campaigns__insert(array(
						"user_id"			=> $this->_sesh->user_id,
						"institution_id"	=> $this->_sesh->institution_id,
						"campaign_id"		=> $objCard->campaign_id,
						"mission_id"		=> $objCard->mission_id,
						"task_id"			=> $objCard->task_id,
						"class_id"			=> $objCard->class_id,
						"schedule_date"		=> time(),
						"points_given"		=> $objCard->card_points
					));
				}
				$query->user_points__insert(array(
					"achievement_card_id"	=> $objCard->achievement_card_id,
					"user_id"				=> $this->_sesh->user_id,
					"institution_id"		=> $this->_sesh->institution_id,
					"campaign_id"			=> $objCard->campaign_id,
					"mission_id"			=> $objCard->mission_id,
					"task_id"				=> $objCard->task_id,
					"class_id"				=> $objCard->class_id,
					"points"				=> $objCard->card_points,
					"resource_name"			=> "specific achievement card",
					"prize_id"				=> 0
				));
				$query->achievement_cards__update(array(
					"where" => array(
						"card_serial" => $intCard
					),
					"values" => array(
						"status" => "scanned"
					)
				));
			}
		}
		else
		{
			$this->view->strScanCodeMessage = "The scan code was not found in our system. Maybe the bar code wasn't scanned properly.";
			return;
		}
	}


	public function shadowpoptemplateAction()
	{
		$this->view->strTitle = $this->_request->getParam("title");
		$this->view->strContent = $this->_request->getParam("msg");
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function storeAction()
	{
		$query = new QueryGen();
		$objPoints = new Points();
		$objConfig = new Config();

		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$intUserPointsStore = $objPoints->user_store(array(
			"user_id" => $this->_sesh->user_id,
			"institution_id" => $this->_sesh->institution_id
		));
		$objStudent = first($query->users__select(array(
			'user_id' => $this->_sesh->user_id
		)));

		if ($this->_request->isPost())
		{
			if ($this->_request->getParam("buynow") == "true")
			{
				$objCartItems = json_decode(stripslashes($this->_request->getPost("arrCartItems")));
				//var_dump($objCartItems);exit;
				$arrResult = array();
				if (!is_object($objCartItems))
				{
					$arrResult["error"] = text("Sorry, there was an error") . ": CHS-S101-S8D9F7";
					print json_encode($arrResult);
					exit;
				}
				$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
					"user_id" => $this->_sesh->user_id
				)));
				$intPointsSum = 0;
				$arrPurchasePrizes = array();
				foreach ($objCartItems as $objCartItem)
				{
					$arrCartItem = (array) $objCartItem;
					// Validate post
					if (!isset($arrCartItem["prize_id"]))
					{
						$arrResult["error"] = text("Sorry, there was an error") . ": CHS-S102-SDFD7F";
						print json_encode($arrResult);
						exit;
					}
					$arrCartItem["user_quantity"] = intval(@$arrCartItem["user_quantity"]);
					if ($arrCartItem["user_quantity"] < 1)
					{
						$arrResult["error"] = text("Sorry, there was an error") . ": CHS-S103-SD897F";
						print json_encode($arrResult);
						exit;
					}
					// Load the prize
					$objPrize = first($query->prize__select(array(
						"prize_id" => $arrCartItem["prize_id"],
						"institution_id" => $this->_sesh->institution_id,
						"is_active" => 1
					)));
					// Skip prizes that no longer exist
					if (!$objPrize)
						continue;
					if ($objPrize->prize_count < $arrCartItem["user_quantity"])
					{
						$arrResult["error"]["prize_id_" . $objPrize->prize_id][] = "This prize has only " . $objPrize->prize_count . " item(s) remaining.";
						continue;
					}
					if ($objPrize->one_per_user == 1 && $arrCartItem["user_quantity"] > 1)
						$arrCartItem["user_quantity"] = 1;

					// Make sure that the child is in the same classes as the prizes
					$arrPrizeClasses = $query->prize_classes__select(array(
						"prize_id" => $objPrize->prize_id
					));
					$boolStudentInClass = 0;
					if (!count($arrPrizeClasses))
						$boolStudentInClass = 1;
					foreach ($arrPrizeClasses as $objPrizeClass)
					{
						if (isset($arrUserClasses[$objPrizeClass->class_id]))
						{
							$boolStudentInClass = 1;
						}
					}
					if (!$boolStudentInClass)
					{
						$arrResult["error"]["prize_id_" . $objPrize->prize_id][] = "This prize is no longer available in your class.";
						continue;
					}
					// Check if its one per user
					if ($objPrize->one_per_user && $arrCartItem["user_quantity"] > 1)
					{
						// Check if student has already gotten this prize
						$objUserPrizes = first($query->user_prizes__select(array(
							"prize_id" => $objPrize->prize_id,
							"user_id" => $this->_sesh->user_id,
							"_LIMIT" => 1
						)));
						if ($objUserPrizes)
						{
							$arrResult["error"]["prize_id_" . $objPrize->prize_id][] = $objPrize->prize_name . " is a prize that has been limited to one per user and therefor cannot be purchased again.";
							continue;
						}
					}
					if (isset($arrCartItem["prize_size"]) && !empty($arrCartItem["prize_size"]))
					{
						$objPrizeSizes = first($query->prize_sizes__select(array(
							"prize_id" => $objPrize->prize_id,
							"prize_size" => $arrCartItem["prize_size"]
						)));
						if (!$objPrizeSizes)
						{
							$arrResult["error"]["prize_id_" . $objPrize->prize_id][] = $objPrize->prize_name . " size: " . $arrCartItem["prize_size"] . " does't seem to be valid any longer.";
							continue;
						}
					}
					$arrPurchasePrizes[$objPrize->prize_id] = array(
						"objPrize" => $objPrize,
						"arrCartItem" => $arrCartItem
					);

					// Sum total points
					$intPointsSum += $objPrize->points * $arrCartItem["user_quantity"];
				}
				// No errors found
				if (!isset($arrResult["error"]))
				{
					if ($intPointsSum > $intUserPointsStore)
					{
						$intPointRemainder = $intUserPointsStore - $intPointsSum;
						$arrResult["error"] = "You are missing " . $intPointRemainder . " miles to complete your purchase.";
						print json_encode($arrResult);
						exit;
					}
					$intItemsPurchased = 0;
					foreach ($arrPurchasePrizes as $intPrize => $arrData)
					{
						// Create a serial
						$strSerial = FALSE;
						while (!$strSerial)
						{
							$strSerial = rand_num_string(19);
							$objTempUserPrize = first($query->user_prizes__select(array(
								"serial" => (string) $strSerial
							)));
							if ($objTempUserPrize)
								$strSerial = FALSE;
						}

						$intUserPrizeId = $query->user_prizes__insert(array(
							"prize_id" => $arrData["objPrize"]->prize_id,
							"user_id" => $this->_sesh->user_id,
							"institution_id" => $this->_sesh->institution_id,
							"quantity" => $arrData["arrCartItem"]["user_quantity"],
							"prize_size" => @$arrData["arrCartItem"]["prize_size"],
							"status" => "Checked Out",
							"serial" => $strSerial
						));
						$intUserPointId = $query->user_points__insert(array(
							"prize_id" => $arrData["objPrize"]->prize_id,
							"user_prize_id" => $intUserPrizeId,
							"user_id" => $this->_sesh->user_id,
							"institution_id" => $this->_sesh->institution_id,
							"points" => -($arrData["arrCartItem"]["user_quantity"] * $arrData["objPrize"]->points),
							"resource_name" => "store"
						));
						$query->prize__update(array(
							"where" => array(
								"prize_id" => $arrData["objPrize"]->prize_id
							),
							"values" => array(
								"prize_count" => $arrData["objPrize"]->prize_count - $arrData["arrCartItem"]["user_quantity"]
							)
						));
						$arrConfigOptions = $objConfig->load(array(
							"set" => "kiosk",
							"key" => "store_admin_notifications",
							"institution_id" => $this->_sesh->institution_id
						));
						if ($arrConfigOptions['kiosk']['store_admin_notifications'])
						{
							$arrAdminIds = array_stack('user_id', $query->permissions__select(array(
								'_COLUMNS' => array('user_id'),
								'institution_id' => $this->_sesh->institution_id,
								'permission' => 'Institution Administrator'
							)));
							$arrAdmins = $query->users__select(array(
								'user_id' => $arrAdminIds
							));
							foreach ($arrAdmins as $objAdmin)
							{
								$strTo      = $objAdmin->email;
								$strSubject = "New Kiosk Store Purchase";
								$strMessage = "
Hello, " . $objAdmin->first_name . " " . $objAdmin->last_name . ".

" . $objStudent->first_name . " " . $objStudent->last_name . " has just made a new purchase from the kiosk store.

Prize: " . $arrData["objPrize"]->prize_name . "
Quantity: " . $arrData["arrCartItem"]["user_quantity"] . "
Cost: " . ($arrData["arrCartItem"]["user_quantity"] * $arrData["objPrize"]->points) . " points.

";
								$strHeaders = 'From: Notifications <noreply@mashpia.com>' . "\r\n";
								mail($strTo, $strSubject, $strMessage, $strHeaders);

							}
						}
					}
					$arrResult = array(
						"success" => "true",
						"intLocalPoints" => $intUserPointsStore
					);
				}
				print json_encode($arrResult);
				exit;
			}
		}

		$objInstitutions = new Institutions();
		$objStore = new Store();
		$objUsers = new Users();
		$objPoints = new Points();
		$objConfig = new Config();

		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("kiosk", "admin"),
			"institution_id" => $this->_sesh->institution_id
		));

		$this->view->objUser = first($query->users__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_sesh->institution_id
		)));
		if (isset($_COOKIE["hebrewschools_store_cart"]))
			$this->view->arrStoreCart = $arrStoreCart = (array) json_decode(stripslashes($_COOKIE["hebrewschools_store_cart"]));
		// Collect a hash of all hq prizes from the session
		$arrHQPrizesHash = array_hash("prize_id", $query->prize__select(array(
			"institution_id" => 1,
			"add_on_restricted" => 1,
			"is_active" => 1,
			"template_prize_id" => 0,
			"parent_prize_id" => 0,
			"_ORDER" => "prize_id+0 ASC"
		)));
		$arrUserAddons = array_stack("prize_id", $query->user_addons__select(array(
			"user_id" => $this->_sesh->user_id
		)));

		$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$arrClassIds = array_keys($arrUserClasses);

		// Load all the aplicable prizes
		$arrPrizes = array_hash("prize_id", $query->prize__select(array(
			"institution_id" => $objInstitution->institution_id,
			"parent_prize_id" => "0",
			"is_active" => 1,
			"_ORDER" => "prize_id+0 ASC",
			"_NOT" => array(
				"prize_count" => 0
			)
		)));
		$arrPrizeClasses = array_bubble_hash_old("prize_id", "class_id", $query->prize_classes__select(array(
			"prize_id" => array_keys($arrPrizes)
		)));
		// Loop through prizes to find the sub category
		$arrNewPrizes = array();
		foreach ($arrPrizes as $intPrize => $objPrize)
		{
			if (
				isset($arrPrizeClasses[$intPrize])
				&& count($arrPrizeClasses[$intPrize])
				&& !array_in_array(array_keys($arrUserClasses), array_keys($arrPrizeClasses[$intPrize]))
			) {
				continue;
			}
			if (isset($arrUserAddons[$objPrize->prize_id]))
			{
				if ($objPrize->use_sub_prizes)
				{
					$arrChildPrizes = $query->prize__select(array(
						"institution_id" => array($objInstitution->host_id, $objInstitution->institution_id),
						"parent_prize_id" => $objPrize->template_prize_id,
						"is_active" => 1,
						"_ORDER" => "prize_id+0 ASC"
					));
					foreach ($arrChildPrizes as $objParentPrize)
					{
						$arrNewPrizes[] = $objParentPrize;
					}
				}
				else
				{
					$arrNewPrizes[] = $objPrize;
				}
			}
			else if ($objPrize->institution_id == $objInstitution->institution_id)
				$arrNewPrizes[] = $objPrize;
		}
		$arrPrizes = array_hash("prize_id", $arrNewPrizes);

		// Build the data sets required for the store
		$arrDistinctPoints = array();
		$arrGroupedPrizes = array();
		$arrPrizesFiltered = array();
		$arrPrizeDetails = array();
		$arrOnePerUserPrizeIds = array();
		foreach ($arrPrizes as $objPrize)
		{
			if (
				// Check if there are items still in stock
				$objPrize->prize_count > 0
				&& $objPrize->points > 0
				// Check if the item is an add on and if so require legacy listing
				&& !($objPrize->add_on_restricted && !isset($arrUserAddons[$objPrize->prize_id]))
				&& (
					$objPrize->parent_prize_id > 0
					|| (
						(
							$objPrize->prize_type != "Template"
							|| isset($arrHQPrizesHash[$objPrize->prize_id])
						)
						&& (
							$objPrize->prize_type != "Installable"
							|| (
								$objPrize->prize_type == "Installable"
								&& $objPrize->template_prize_id > 0
							)
						)
					)
				)
			) {
				if (
					$objPrize->add_on_restricted && isset($arrHQPrizesHash[$objPrize->prize_id])
					&& strlen($arrHQPrizesHash[$objPrize->prize_id]["item_size"])
				) {
					$objPrize->prize_name .= " size: " . $arrHQPrizesHash[$objPrize->prize_id]["item_size"];
				}

				// Collect prize groups (grouped by points)
				if (!isset($arrDistinctPoints[$objPrize->points]))
					$arrDistinctPoints[$objPrize->points] = 0;
				$arrDistinctPoints[$objPrize->points]++;
				if (!isset($arrGroupedPrizes[$objPrize->points]))
					$arrGroupedPrizes[$objPrize->points] = array();
				$arrGroupedPrizes[$objPrize->points][] = $objPrize;
				$arrPrizesFiltered[] = $objPrize;
				$arrPrizeDetails[$objPrize->prize_id] = array(
					"objPrize" => $objPrize
				);
				if ($objPrize->one_per_user == "1")
					$arrOnePerUserPrizeIds[$objPrize->prize_id] = 1;
			}
		}
		ksort($arrDistinctPoints);

		$arrOnePerUserUserPrizes = array_hash("prize_id", $query->user_prizes__select (array(
			"prize_id" => array_keys($arrOnePerUserPrizeIds),
			"user_id" => $this->_sesh->user_id
		)));

		// Prize Sizes
		$arrPrizeSizes = array_bubble_hash("prize_id", $query->prize_sizes__select(array(
			"prize_id" => array_keys($arrPrizeDetails),
			"_ORDER" => "prize_size_hierarchy+0 ASC",
			"_VERBOSE" => 0
		)));

		// Cart
		$arrCartItems = array();
		$intPointsOffset = 0;
		if (isset($objCartSession->cartItems))
		{
			foreach ($objCartSession->cartItems as $objCartItem)
			{
				array_push($arrCartItems, $objCartItem);
				$intPointsOffset += $objCartItem->points;
			}
		}
		$intUserPointsStore -= $intPointsOffset;

		// Send data sets to view
		$this->view->arrPrizeSizes = $arrPrizeSizes;
		$this->view->arrDistinctPoints = $arrDistinctPoints;
		$this->view->arrGroupedPrizes = $arrGroupedPrizes;
		$this->view->arrPrizes = $arrPrizesFiltered;
		$this->view->intLocalPoints = $intUserPointsStore;
		$this->view->arrCartItems = $arrCartItems;
		$this->view->arrPrizeDetails = $arrPrizeDetails;
		$this->view->arrOnePerUserPrizeIds = $arrOnePerUserPrizeIds;
		$this->view->arrOnePerUserUserPrizes = $arrOnePerUserUserPrizes;

	}

	public function transactionhistoryAction()
	{
		$query = new QueryGen();
		$objClasses = new Classes();
		$objPoints = new Points();

		$this->view->institution_id = $this->_request->getParam("institution_id");
		$this->view->tstyle = $this->_request->getParam("tstyle");

		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('hebrewschools');
		$this->_helper->layout()->bgColor = "lgreen";
		$this->_helper->layout()->strTitle = "Hebrew Schools";

		$this->view->objInstitution = $objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_sesh->institution_id
		)));

		$intItemsPerPage = $this->view->intItemsPerPage = 7;

		// Build the list output
		$arrResult = array();
		$arrDates = array();

		$arrPost = $this->_request->getPost();
		$boolPost = $this->_request->isPost();
		$strDisplayType = "all";
		if ($boolPost)
			$strDisplayType = $arrPost["display_type"];
		$strCampaignType = "all";
		if ($boolPost)
			$strCampaignType = $arrPost["campaign_type"];

		$arrPointsParams = array(
			"user_id" => $this->_sesh->user_id,
			"_ORDER" => "created ASC, user_point_id ASC"
		);

		$arrUserPoints = array_hash("user_point_id", $query->user_points__select($arrPointsParams));
		// sort the data
		$arrPointSums = array(
			"total" => 0,
			"store" => 0
		);
		$arrResults = array();
		$arrResults["rows"] = array();
		foreach ($arrUserPoints as $intItr => $objUserPoint)
		{
			$strSortKey = strtotime($objUserPoint->created) . "." . $intItr;
			if (!isset($arrResults["rows"][$strSortKey]))
				$arrResults["rows"][$strSortKey] = array();
			$arrResults["rows"][$strSortKey][$intItr]["objUserPoint"] = $objUserPoint;

			// calcuate the points --
			if (
				// Items that should be calculated against the store only
				$objUserPoint->resource_name == "admin_users_manual_store"
				|| $objUserPoint->resource_name == "store"
				|| !empty($objUserPoint->prize_id)
			) {
				$arrPointSums["store"] += $objUserPoint->points;
			}
			else if (
				// total only
				$objUserPoint->resource_name == "admin_users_manual_total"
			) {
				$arrPointSums["total"] += $objUserPoint->points;
			}
			else
			{
				$arrPointSums["total"] += $objUserPoint->points;
				$arrPointSums["store"] += $objUserPoint->points;
			}
			$arrResults["rows"][$strSortKey][$intItr]["arrPointSums"] = $arrPointSums;
		}

		nksort($arrResults["rows"]);
		$arrResults["rows"] = array_reverse($arrResults["rows"], TRUE);
		$arrResults["rows"] = array_strip($arrResults["rows"],1);

		// categories results
		foreach ($arrResults['rows'] as $intKey => $arrItem)
		{
			$refItem = &$arrResults['rows'][$intKey];
			if ($refItem["objUserPoint"]->prize_id)
				$refItem["strRowType"] = "prize";
			else if ($refItem["objUserPoint"]->achievement_card_id)
				$refItem["strRowType"] = "achievement_card";
			else if ($refItem["objUserPoint"]->scratch_card_id)
				$refItem["strRowType"] = "scratch_card";
			else if (
				$refItem["objUserPoint"]->resource_name == "admin_users_manual"
				|| $refItem["objUserPoint"]->resource_name == "admin_users_manual_store"
				|| $refItem["objUserPoint"]->resource_name == "admin_users_manual_total"
				|| $refItem["objUserPoint"]->resource_name == "direct_transfer"
			)
				$refItem["strRowType"] = "transfer";
			else
				$refItem["strRowType"] = "error";
			// filter results
			if (
				$strDisplayType == "purchases"
				&& $refItem["strRowType"] != 'prize'
			)
				unset($arrResults['rows'][$intKey]);
			else if (
				$strCampaignType != "all"
				&& $refItem["objUserPoint"]->campaign_id != $strCampaignType
			)
				unset($arrResults['rows'][$intKey]);
			else if (
				$strDisplayType == "points"
				&& (
					$refItem["strRowType"] != 'transfer'
					&& $refItem["strRowType"] != 'achievement_card'
					&& $refItem["strRowType"] != 'scratch_card'
				)
			)
				unset($arrResults['rows'][$intKey]);
		}

		// Collect required data
		$arrExtractResults = array_extract2(
			"created_by",
			"user_prize_id",
			"prize_id",
			"achievement_card_id",
			$arrResults["rows"]
		);
		$arrAdmins = array_hash("user_id", $query->users__select(array(
			"user_id" => $arrExtractResults["created_by"]
		)));
		$arrUserPrizes = array_hash("user_prize_id", $query->user_prizes__select(array(
			"user_prize_id" => $arrExtractResults["user_prize_id"]
		)));
		$arrPrizes = array_hash("prize_id", $query->prize__select(array(
			"prize_id" => $arrExtractResults["prize_id"]
		)));
		$arrAchievementCards = $arrAchievementCards = array_hash("achievement_card_id", $query->achievement_cards__select(array(
			"achievement_card_id" => $arrExtractResults["achievement_card_id"]
		)));
		$arrExtractAchievementCards = array_extract2(
			"campaign_id",
			"task_id",
			$arrAchievementCards
		);

		$arrCampaignIDs = array();
		array_push($arrCampaignIDs, array_keys(array_stack('campaign_id', $arrUserPoints)));
		array_push($arrCampaignIDs, $arrExtractAchievementCards["campaign_id"]);
		$arrCampaignIDs = array_flatten2($arrCampaignIDs);
		$this->view->arrCampaigns = $arrCampaigns = array_hash("campaign_id", $query->campaigns__select(array(
			"campaign_id" => array_unique($arrCampaignIDs)
		)));
		$arrTaskIDs = array();
		array_push($arrTaskIDs, array_keys(array_stack('task_id', $arrUserPoints)));
		array_push($arrTaskIDs, $arrExtractAchievementCards["task_id"]);
		$arrTaskIDs = array_flatten($arrTaskIDs);
		$arrTasks = array_hash("task_id", $query->tasks__select(array(
			"task_id" => array_unique($arrTaskIDs)
		)));


		$arrResults["items_count"] = count($arrUserPoints);

		$intStart = $boolPost ? $arrPost["navigate_to"] : 1;
		$arrResults["rows"] = array_slice($arrResults["rows"], $intStart-1, $intItemsPerPage);
		$arrResults["rows"] = array_reverse($arrResults["rows"], TRUE);
		$arrResults["first_row_number"] = $intStart;
		$arrResults["last_row_number"] = $intStart + count($arrResults['rows']);;

		// Process required objects
		foreach ($arrResults['rows'] as $intKey => $arrItem)
		{
			$refItem = &$arrResults['rows'][$intKey];
			if (!empty($refItem["objUserPoint"]->created_by))
				$refItem["objAdmin"] = $arrAdmins[$refItem["objUserPoint"]->created_by];
			if (!empty($refItem["objUserPoint"]->reversed_user_point_id))
			{
				$refItem["objReversedUserPoint"] = $refItem["objUserPoint"]->reversed_user_point_id;
			}
			$intTime = strtotime($refItem["objUserPoint"]->created);
			$refItem["transaction_date"] = date("d M, Y", $intTime);
			$refItem["transaction_time"] = date("h:i a", $intTime);
			if ($refItem["objUserPoint"]->prize_id)
			{
				$refItem["strRowType"] = "prize";
				$objPrize = $arrPrizes[$refItem["objUserPoint"]->prize_id];
				if (!$objPrize->image_id)
					$objPrize->image_id = $objInstitution->image_id;
				$refItem["objPrize"] = $objPrize;
				$refItem["objUserPrize"] = $arrUserPrizes[$refItem["objUserPoint"]->user_prize_id];
			}
			else if ($refItem["objUserPoint"]->achievement_card_id)
			{
				$objAchievementCard = $arrAchievementCards[$refItem["objUserPoint"]->achievement_card_id];
				$objCampaign = @$arrCampaigns[$objAchievementCard->campaign_id];
				$objTask = @$arrTasks[$objAchievementCard->task_id];
				if (!$objCampaign)
				{
					$objCampaign = new stdClass();
					$objCampaign->campaign_name = "Unavailable";
				}
				if (!$objTask)
				{
					$objTask = new stdClass();
					$objTask->task_name = "Unavailable";
				}
				if (empty($objCampaign->image_id))
					$objCampaign->image_id = $objInstitution->image_id;
				$refItem["objAchievementCard"] = $objAchievementCard;
				$refItem["objCampaign"] = $objCampaign;
				$refItem["objTask"] = $objTask;
			}
			else if ($refItem["objUserPoint"]->scratch_card_id)
			{

			}
			// awarded
			else if (
				$refItem["objUserPoint"]->resource_name == "admin_users_manual"
				|| $refItem["objUserPoint"]->resource_name == "admin_users_manual_store"
				|| $refItem["objUserPoint"]->resource_name == "admin_users_manual_total"
				|| $refItem["objUserPoint"]->resource_name == "direct_transfer"
			) {
				$objCampaign = @$arrCampaigns[$refItem["objUserPoint"]->campaign_id];
				$objTask = @$arrTasks[$refItem["objUserPoint"]->task_id];
				if (!$objCampaign)
				{
					$objCampaign = new stdClass();
					$objCampaign->campaign_name = "Unavailable";
				}
				if (!$objTask)
				{
					$objTask = new stdClass();
					$objTask->task_name = "Unavailable";
				}
				if (empty($objCampaign->image_id))
					$objCampaign->image_id = $objInstitution->image_id;
				$refItem["objCampaign"] = $objCampaign;
				$refItem["objTask"] = $objTask;
			}
		}

		if ($boolPost)//$this->_request->getParam("output") == "ajax")
		{
			print json_encode($arrResults);
			exit;
		}
		$this->view->arrResult = $arrResults;

		$this->view->intUserPointsTotal = $objPoints->user_total(array(
			"user_id" => $this->_sesh->user_id,
			"institution_id" => $this->_sesh->institution_id
		));
		$this->view->intUserPointsStore = $objPoints->user_store(array(
			"user_id" => $this->_sesh->user_id,
			"institution_id" => $this->_sesh->institution_id
		));
		$this->view->objUser = first($query->users__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_sesh->institution_id
		)));
		$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
			"user_id" => $this->_sesh->user_id
		)));
		$arrClasses = $objClasses->_classes_select(array(
			"class_id" => array_keys($arrUserClasses)
		));
		$arrClassNames = array();
		foreach ($arrClasses as $objClass)
		{
			$arrClassNames[$objClass->class_id] = trim($objClass->grade . " " . $objClass->sub);
		}
		$this->view->arrClassNames = $arrClassNames;
	}
}
?>