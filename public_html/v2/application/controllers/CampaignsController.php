<?php
class CampaignsController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $_roles;
	private $objPermission; // permission instance
	private $boolVerbose = 0;

    function preDispatch()
    {
		$this->_roles = new Roles();

		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (!$this->_user_session_data->institution_id)
			$this->_redirect('logout');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function pointcardsetsprintoutputAction()
	{
		$query = new QueryGen();
		$arrGet = $this->_request->getParams();
		if (!isset($arrGet['dataparams']))
		{
			print 'Sorry, there was an error: CC-ACPO101-g3g4dd';
			exit;
		}
		$arrDataParams = json_decode($arrGet['dataparams']);
		if (!$arrDataParams || gettype($arrDataParams) != "object")
		{
			print 'Sorry, there was an error: CC-ACPO102-g9dd9d';
			exit;
		}
		$arrDataParams = (array) $arrDataParams;
		if (!isset($arrDataParams['arrTasks']))
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$this->view->arrDataParams = $arrDataParams;
		$this->view->arrTasks = $arrTasks = $query->tasks__select(array(
			'task_id' => $arrDataParams['arrTasks'],
			'_ORDER' => 'campaign_id+0,sequence+0 ASC'
		));
		$this->view->arrCampaigns = $arCampaigns = array_hash('campaign_id', $query->campaigns__select(array(
			'campaign_id' => array_stack('campaign_id', $arrTasks)
		)));
		$this->view->objInstitution = first($query->institutions__select(array(
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$arrBarcodes = array();
		for ($intSet=0; $intSet<$arrDataParams['intSetCount']; $intSet++)
		{
			foreach ($arrTasks as $objTask)
			{
				$objCampaign = $arCampaigns[$objTask->campaign_id];
				$intBarcode = "4" . rand_num_string(19);
				$arrInsert = array(
					"institution_id"	 => $this->_user_session_data->institution_id,
					"campaign_id"	 	 => $objCampaign->campaign_id,
					"task_id" 			 => $objTask->task_id,
					"class_id"			 => 0,
					"card_serial"		 => $intBarcode,
					"card_type"	 		 => $this->_user_session_data->permission,
					"card_points"		 => $objTask->points,
					"status"			 => 'not scanned'
				);
				$query->achievement_cards__insert($arrInsert);
				$arrBarcodes[$intSet][$objTask->task_id] = $intBarcode;
			}
		}
		$this->view->arrBarcodes = $arrBarcodes;
	}

	public function handbookachievementcardsAction()
	{

	}

	public function handbooksAction()
	{
		$query = new QueryGen();
		$arrGet = $this->_request->getParams();
		$intUser = $this->view->user_id = $arrGet['user_id'];
		if (empty($intUser))
		{
			print "Sorry, there was an error: CC-HB101-sdf8df";
		}
		$this->view->objStudent = first($query->users__select(array(
			'user_id' => $intUser
		)));
		if (!isset($arrGet['campaign_id']))
			$arrGet['campaign_id'] = array(1320,1321,1322,1323);
		$this->view->arrCampaigns = $arrCampaigns = array_hash('campaign_id', $query->campaigns__select(array(
			'campaign_id' => $arrGet['campaign_id'],
			'institution_id' => 1,
			'is_active' => 1
		)));
		$arrTasks = $query->tasks__select(array(
			'campaign_id' => array_keys($arrCampaigns)
		));
		$this->view->arrTasks = array_bubble_hash('campaign_id', $arrTasks);
		$arrUserCampaigns = $query->user_campaigns__select(array(
			'user_id' => $intUser,
			'campaign_id' => array_keys($arrCampaigns),
			'institution_id' => $this->view->institution_id
		));
		$this->view->arrUserCampaigns = array_bubble_hash('campaign_id', 'task_id', $arrUserCampaigns);
		$arrUserPoints = $query->user_campaigns__select(array(
			'user_id' => $intUser,
			'campaign_id' => array_keys($arrCampaigns)
		));
		$this->view->arrUserPoints = array_bubble_hash('campaign_id', 'task_id', $arrUserPoints);
		$this->view->arrAdmins = array_hash('user_id', $query->users__select(array(
			'user_id' => array_stack('created_by', $arrUserPoints),
		)));
		$arrPost = $this->_request->getPost();
		if (count($arrPost))
		{
			$objMe = first($query->users__select(array(
				'user_id' => $this->_user_session_data->user_id
			)));
			$objTask = first($query->tasks__select(array(
				'task_id' => $arrPost['task_id']
			)));
			$query->user_campaigns__delete(array(
				'user_id' => $intUser,
				'task_id' => $arrPost['task_id']
			));
			$query->user_points__delete(array(
				'user_id' => $intUser,
				'task_id' => $arrPost['task_id']
			));
			if ($arrPost['is_checked'] == 'true')
			{
				$query->user_campaigns__insert(array(
					'user_id' => $intUser,
					'task_id' => $arrPost['task_id'],
					'institution_id' => $this->_user_session_data->institution_id,
					'campaign_id' => $objTask->campaign_id,
					'task_id' => $objTask->task_id,
					'points_given' => $objTask->points
				));
				$query->user_points__insert(array(
					'user_id' => $intUser,
					'campaign_id' => $objTask->campaign_id,
					'task_id' => $objTask->task_id,
					'institution_id' => $this->_user_session_data->institution_id,
					'points' => $objTask->points,
					'resource_name' => 'handbook'
				));
				$objUserPoint = first($query->user_points__select(array(
					'user_id' => $intUser,
					'task_id' => $arrPost['task_id']
				)));
				print json_encode(array(
					'success' => 'true',
					'date_marked' => $objUserPoint->created,
					'marked_by' => $objMe->first_name . ' ' . $objMe->last_name
				));
				exit;
			}
			print json_encode(array(
				'success' => 'true',
				'date_marked' => 'Not Marked',
				'marked_by' => 'Not Marked'
			));
			exit;
		}
	}

    public function indexAction()
    {
		exit;
	}

	public function achievementcardsprintAction()
	{
		$query = new QueryGen();
		$objTasks = new Tasks();
		$objCampaigns = new Campaigns();
		
		$this->view->arrCampaigns = $arrCampaigns = array_hash('subject_id', $objCampaigns->achievement_campaigns_select());
		$this->view->arrTasks = $arrTasks = array_hash('achievement_task_id', $objTasks->getAchievementCardTasks($arrCampaigns));
		$this->view->arrTasksHash = array_bubble_hash('subject_id', $arrTasks);
		/*
		$this->view->arrCampaigns = $arrCampaigns = array_hash('campaign_id', $query->campaigns__select(array(
			'institution_id' => $this->_user_session_data->institution_id,
			'is_active' => 1
		)));
		$this->view->arrTasks = $arrTasks = array_hash('task_id', $objTasks->task_filter_date_ranges($query->tasks__select(array(
			'is_card' => 1,
			'institution_id' => $this->_user_session_data->institution_id,
			'campaign_id' => array_stack('campaign_id', $arrCampaigns),
			'is_active' => 1
		))));
		$this->view->arrTasksHash = array_bubble_hash('campaign_id', $arrTasks);
		*/
	}
	public function achievementcardsprintoutputAction()
	{
		$query = new QueryGen();
		//$objConfig = new Config();
		$arrGet = $this->_request->getParams();
		//dumper($arrGet['dataparams'],1,1);
		if (!isset($arrGet['dataparams']))
		{
			print 'Sorry, there was an error: CC-ACPO101-g3g4dd';
			exit;
		}
		$arrDataParams = json_decode($arrGet['dataparams']);
		if (!$arrDataParams || gettype($arrDataParams) != "object")
		{
			print 'Sorry, there was an error: CC-ACPO102-g9dd9d';
			exit;
		}
		$arrDataParams = (array) $arrDataParams;
		if (!isset($arrDataParams['intTask']))
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		/*
		$this->view->objTask = $objTask = first($query->tasks__select(array(
			'task_id' => $arrDataParams['intTask'],
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$this->view->arrAchievementCardConfig = $objConfig->load(array(
			"set" => array("achievementcards"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		if (!$objTask)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
			'campaign_id' => $objTask->campaign_id
		)));
		*/
		$intSubject = intval($arrDataParams['intSubject']);
		$objCampaigns = new Campaigns();
		$this->view->objCampaign = $objCampaign = first($objCampaigns->achievement_campaigns_select(array(
			'campaign_id' => $intSubject
		)));
		//dumper($objCampaign,1,1);
		if (!$objCampaign)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		/*
		if ($objTask->is_locked == 1)
		{
			$intPoints = $objTask->points;
		}
		*/
		$this->view->intPoints = $intPoints = $arrDataParams['intPoints'];
		$this->view->intTask = $intTask = $arrDataParams['intTask'];
		$this->view->strTask = $strTask = $arrDataParams['strTask'];
		/*
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		*/
		$objInstitutions = new Institutions();
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		
		$intCat = intval($arrDataParams['intTask']);
		$intPages = intval($arrDataParams['intPageCount']);
		$intPages = $intPages < 1 ? 1 : $intPages;
		$this->view->intPages = $intPages;
		$intCardsPerPage = 10;
		$intClass = $this->_user_session_data->class_id ? $this->_user_session_data->class_id : 0;
		$arrBarcodes = array();
		for ($intItr=0; $intItr<$intPages*$intCardsPerPage; $intItr++)
		{
			$intBarcode = $arrBarcodes[$intItr] = "4" . rand_num_string(19);
			// check if the database to see if barcode already exists
			// this code needs optimization because it will make a lot of queries
			// checking that database for each bar code is a bad method in the first place
			// a better solution is to get a proper random function that doesnt bug out like this one
			do {
				$objAchievementCard = first($query->achievement_cards__select(array(
					"card_serial" => $intBarcode
				)));
			}
			while ($objAchievementCard);

			$arrInsert = array(
				"institution_id"	 => $this->_user_session_data->institution_id,
				"campaign_id"	 	 => $intSubject,
				"task_id" 			 => $intTask,
				"class_id"			 => $intClass,
				"card_serial"		 => $intBarcode,
				"card_type"	 		 => $this->_user_session_data->permission,
				"card_points"		 => $intPoints,
				"status"			 => 'not scanned',
				"created_by"         => $this->_user_session_data->user_id
			);
			$query->achievement_cards__insert($arrInsert);
		}
		$this->view->arrBarcodes = $arrBarcodes;
	}
	public function achievementcardsprintoutputqrAction()
	{
		/*
		$query = new QueryGen();
		$objConfig = new Config();
		$arrGet = $this->_request->getParams();
		if (!isset($arrGet['dataparams']))
		{
			print 'Sorry, there was an error: CC-ACPO101-g3g4dd';
			exit;
		}
		$arrDataParams = json_decode($arrGet['dataparams']);
		if (!$arrDataParams || gettype($arrDataParams) != "object")
		{
			print 'Sorry, there was an error: CC-ACPO102-g9dd9d';
			exit;
		}
		$arrDataParams = (array) $arrDataParams;
		if (!isset($arrDataParams['intTask']))
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$this->view->objTask = $objTask = first($query->tasks__select(array(
			'task_id' => $arrDataParams['intTask'],
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$this->view->arrAchievementCardConfig = $objConfig->load(array(
			"set" => array("achievementcards"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		if (!$objTask)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
			'campaign_id' => $objTask->campaign_id
		)));
		if (!$objCampaign)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$intPoints = $arrDataParams['intPoints'];
		if ($objTask->is_locked == 1)
		{
			$intPoints = $objTask->points;
		}
		$this->view->intPoints = $intPoints;
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		$intPages = intval($arrDataParams['intPageCount']);
		$intPages = $intPages < 1 ? 1 : $intPages;
		$this->view->intPages = $intPages;
		$intCardsPerPage = 10;
		$intClass = 0;
		$arrBarcodes = array();
		for ($intItr=0; $intItr<$intPages*$intCardsPerPage; $intItr++)
		{
			$intBarcode = $arrBarcodes[$intItr] = "4" . rand_num_string(19);
			// check if the database to see if barcode already exists
			// this code needs optimization because it will make a lot of queries
			// checking that database for each bar code is a bad method in the first place
			// a better solution is to get a proper random function that doesnt bug out like this one
			do {
				$objAchievementCard = first($query->achievement_cards__select(array(
					"card_serial" => $intBarcode
				)));
			}
			while ($objAchievementCard);

			$arrInsert = array(
				"institution_id"	 => $this->_user_session_data->institution_id,
				"campaign_id"	 	 => $objCampaign->campaign_id,
				"task_id" 			 => $objTask->task_id,
				"class_id"			 => $intClass,
				"card_serial"		 => $intBarcode,
				"card_type"	 		 => $this->_user_session_data->permission,
				"card_points"		 => $intPoints,
				"status"			 => 'not scanned',
				"created_by"         => $this->_user_session_data->user_id
			);
			$query->achievement_cards__insert($arrInsert);
		}
		$this->view->arrBarcodes = $arrBarcodes;
		*/
		$query = new QueryGen();
		//$objConfig = new Config();
		$arrGet = $this->_request->getParams();
		//dumper($arrGet['dataparams'],1,1);
		if (!isset($arrGet['dataparams']))
		{
			print 'Sorry, there was an error: CC-ACPO101-g3g4dd';
			exit;
		}
		$arrDataParams = json_decode($arrGet['dataparams']);
		if (!$arrDataParams || gettype($arrDataParams) != "object")
		{
			print 'Sorry, there was an error: CC-ACPO102-g9dd9d';
			exit;
		}
		$arrDataParams = (array) $arrDataParams;
		if (!isset($arrDataParams['intTask']))
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		/*
		$this->view->objTask = $objTask = first($query->tasks__select(array(
			'task_id' => $arrDataParams['intTask'],
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$this->view->arrAchievementCardConfig = $objConfig->load(array(
			"set" => array("achievementcards"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		if (!$objTask)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
			'campaign_id' => $objTask->campaign_id
		)));
		*/
		$intSubject = intval($arrDataParams['intSubject']);
		$objCampaigns = new Campaigns();
		$this->view->objCampaign = $objCampaign = first($objCampaigns->achievement_campaigns_select(array(
			'campaign_id' => $intSubject
		)));
		//dumper($objCampaign,1,1);
		if (!$objCampaign)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		/*
		if ($objTask->is_locked == 1)
		{
			$intPoints = $objTask->points;
		}
		*/
		$this->view->intPoints = $intPoints = $arrDataParams['intPoints'];
		$this->view->intTask = $intTask = $arrDataParams['intTask'];
		$this->view->strTask = $strTask = $arrDataParams['strTask'];
		/*
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		*/
		$objInstitutions = new Institutions();
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		
		$intCat = intval($arrDataParams['intTask']);
		$intPages = intval($arrDataParams['intPageCount']);
		$intPages = $intPages < 1 ? 1 : $intPages;
		$this->view->intPages = $intPages;
		$intCardsPerPage = 10;
		$intClass = $this->_user_session_data->class_id ? $this->_user_session_data->class_id : 0;
		$arrBarcodes = array();
		for ($intItr=0; $intItr<$intPages*$intCardsPerPage; $intItr++)
		{
			$intBarcode = $arrBarcodes[$intItr] = "4" . rand_num_string(19);
			// check if the database to see if barcode already exists
			// this code needs optimization because it will make a lot of queries
			// checking that database for each bar code is a bad method in the first place
			// a better solution is to get a proper random function that doesnt bug out like this one
			do {
				$objAchievementCard = first($query->achievement_cards__select(array(
					"card_serial" => $intBarcode
				)));
			}
			while ($objAchievementCard);

			$arrInsert = array(
				"institution_id"	 => $this->_user_session_data->institution_id,
				"campaign_id"	 	 => $intSubject,
				"task_id" 			 => $intTask,
				"class_id"			 => $intClass,
				"card_serial"		 => $intBarcode,
				"card_type"	 		 => $this->_user_session_data->permission,
				"card_points"		 => $intPoints,
				"status"			 => 'not scanned',
				"created_by"         => $this->_user_session_data->user_id
			);
			$query->achievement_cards__insert($arrInsert);
		}
		$this->view->arrBarcodes = $arrBarcodes;
	}

	public function pausemissionsAction()
	{
		$query = new QueryGen();
		$objScheduler = new Scheduler();
		$objUsers = new Users();
		$objMarking = new Marking();
		$objLadders = new Ladders();
		$objCampaigns = new Campaigns();
		$objClasses = new Classes();
		$objAutomation = new Automation();

		$this->view->class_id = $intClass = $this->_request->getParam("class_id");
		$this->view->user_id = $intUser = $this->_request->getParam("user_id");
		// Load the campaign
		$intCampaign = 1;
		$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objCampaign)
		{
			print "Sorry, there was an error: CM-PM104-SD6DDF";
			exit;
		}
		// Load the mission
		$objMission = current($query->missions__select(array(
			"campaign_id" => $intCampaign
		)));

		// Load the parameters provided by the mission of the campaign
		$objSchedulingParams = current($objScheduler->_scheduling_params_select(array(
			"mission_id" => $objMission->mission_id,
			"task_id" => 0
		)));
		if (!empty($intUser))
			$arrUsers = $query->users__select(array(
				'user_id' => $intUser
			));
		else if (!empty($intClass))
			$arrUsers = $objClasses->user_classes_select_user(array(
				"class_id" => $intClass
			));
		else
			$arrUsers = $objUsers->_users_select_hierarchal(array(
				"institution_id" => $this->_user_session_data->institution_id,
				"permission" => "Student"
			));
		$arrUsers = array_hash("user_id", array_clean_slashes($arrUsers));
		$arrUserCampaignProgress = $objAutomation->user_goal(array(
			"user_id" => array_keys($arrUsers),
			"campaign_id" => 1,
			'multi' => TRUE
		));
		// Loop through the users and collect all the data required for a marking report
		$intMarkWeeks = 500;
		$arrResults = array();
		$arrEnrollments = array_hash('user_id', $query->user_campaigns__select(array(
			"user_id" => array_stack('user_id', $arrUsers),
			"campaign_id" => $intCampaign,
			"status" => "Enrollment"
		)));
		$arrLinesAheadHash = array_bubble_hash('user_id', $query->user_campaigns__select(array(
			"user_id" => array_keys($arrEnrollments),
			"campaign_id" => 1,
			'_GREATER' => array(
				'schedule_date' => strtotime('-1 week')
			),
			"_ORDER" => "task_increment + 0 ASC",
			'_NOT' => array(
				'status' => array('Paused', 'Resumed', 'Enrollment')
			)
		)));
		foreach ($arrUsers as $intUser => $objUser)
		{
			$strKey = $objUser->last_name . " : " . $objUser->first_name . " : " . $objUser->user_id;
			if (!isset($arrEnrollments[$intUser]))
				continue;
			$objEnrollment = $arrEnrollments[$intUser];
			if (!@$arrUserCampaignProgress[$objUser->user_id]['goal'])
				continue;

			// Get the users ladder velocity
			$intLadderVelocity = $objLadders->campaign_user_ladder_velocity(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id
			));
			$intLatestLineFraction = $objMarking->latest_line_hierarchy(array(
				"mission_id" => $objMission->mission_id,
				"user_id" => $objUser->user_id
			));
			$intLatestMissionLineFraction = $objMarking->latest_mission_line_hierarchy(array(
				"mission_id" => $objMission->mission_id,
				"user_id" => $objUser->user_id
			));
			$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"extra_weeks" => -1
			));
			$arrLinesAhead = isset($arrLinesAheadHash[$objUser->user_id]) ? $arrLinesAheadHash[$objUser->user_id] : array();

			$intMissionsAhead = 0;
			if (
				count($arrLinesAhead) > 1
			) {
				$objFirstUserCampaign = reset($arrLinesAhead);
				$objLastUserCampaign = end($arrLinesAhead);
				$intLinesAhead = floor($objLastUserCampaign->task_increment) - $objFirstUserCampaign->task_increment;
				$intMissionsAhead = $objLastUserCampaign->mission_increment - $objFirstUserCampaign->mission_increment +1;
			}
			else
				$intLinesAhead = 0;
			$intPendingMissionsReal = 0;
			if (count($arrPendingMissions) == 1)
				$intTotalPendingLines = $intLadderVelocity;
			else if (count($arrPendingMissions))
			{
				$intTotalPendingLines = 0;
				$arrFirstPendingMission = reset($arrPendingMissions);
				$intFirstPendingLine = reset($arrFirstPendingMission["tasks"]);
				// find the last due mission
				$intCount = 0;
				for ($intItr=0; $intItr!=count($arrPendingMissions); $intItr++) {
					$arrPendingMission = $arrPendingMissions[$intItr];
					$arrLastPendingMission = $arrPendingMission;
					if ($arrPendingMission['epoch'] > time())
					{
						break;
					}
					$intCount++;
				}
				$intPendingMissionsReal = $intCount;

				$intLastMission = end($arrLastPendingMission["tasks"]);
				$intPendingTotal = $intLastMission - $intFirstPendingLine+1;
				$intTotalPendingLines = $intPendingTotal;
			}
			else
				$intTotalPendingLines = 0;


			$intPending = $objMarking->pending_lines_sum(array(
				"mission_id" => $objMission->mission_id,
				"institution_id" => $this->_user_session_data->institution_id,
				"user_id" => $objUser->user_id
			));
			$intLinesDue = $intPending;

			$strUserStatus = "";
			if ($intMissionsAhead > 0)
				$strUserStatus = ($intMissionsAhead-1) . " " . ucfirst(FrequencyTextToSingular($objSchedulingParams->frequency)) . "(s) ahead";
			else if ($intPendingMissionsReal < 2)
				$strUserStatus = "Current";
			else
				$strUserStatus = ($intPendingMissionsReal - 1) . " " . ucfirst(FrequencyTextToSingular($objSchedulingParams->frequency)) . "(s) behind";
			$arrResults[$strKey]['user_status'] = $strUserStatus;
			// lines due
			$strLinesDue = "";
			$intLinesOverDue = $intTotalPendingLines;
			//var_dump($arrUser["arrPendingMissions"]);$arrParams["campaign_id"]
			$intTotalPendingMissions = $intPendingMissionsReal;
			//dumper($intTotalPendingMissions);
			$intTotalInvert = ($intTotalPendingMissions - ($intTotalPendingMissions * 2));
			if ($intTotalPendingMissions == 1)
			{
				$arrMission = current($arrPendingMissions);
				if ($intLinesOverDue >= 0)
					$intLinesOverDue += $intTotalPendingMissions * $arrMission["velocity"];

				if ($intLinesOverDue)// && ($arrMission["velocity"] - $intLinesOverDue))
				{
					if ($intLinesOverDue < 0)
						$intLinesOverDue = $intTotalPendingMissions * $arrUser["intLadderVelocity"] + ($intTotalPendingMissions * $arrUser["intLadderVelocity"] + ceil($intLinesOverDue));
					if ($intLinesOverDue < 0)
					{
						$intLinesOverDue2 = $intLinesOverDue;
						//var_dump($intLinesOverDue);
						$intLinesOverDue = $intLatestLine + (!($intLinesOverDue < -1) ? $intLinesOverDue / 2 : $intLinesOverDue);
						if ($arrUser["intLadderVelocity"] < 1)
							$intLinesOverDue += $intLinesOverDue2;
					}
					//$intLinesDue = (floor($arrUser["intLatestLine"]) + $intLinesOverDue);
					$strLinesDue = ($intLadderVelocity-($intLinesDue + ($intLatestLine - $intLatestMissionLineFraction))) . " lines due";
				}
				else
					$strLinesDue = "No lines due";
			}
			else if ($intTotalPendingMissions < 1)
			{
				if (!$intLinesAhead)
				{
					if ($intTotalInvert)
						$strLinesDue = $intTotalInvert . ' lines ahead';
					else
						$strLinesDue = "No lines due";
				}
				else
					$strLinesDue = $intLinesAhead . " lines ahead";
			}
			else
			{
				$intLinesOverDue -= $intLadderVelocity;
				$intLinesOverDue = floor($intLinesOverDue);
				if (!$intLinesOverDue)
					$strLinesDue = "Current";
				else
					$strLinesDue = $intLinesOverDue . " lines overdue";
			}
			$arrResults[$strKey]['intOverdue'] = $intPendingMissionsReal;
			$arrResults[$strKey]['lines_due'] = $strLinesDue;
			$arrResults[$strKey]['objUser'] = $objUser;
		}
		ksort($arrResults);
		$this->view->arrResults = $arrResults;
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			foreach ($arrPost as $strKey => $intMissions)
			{
				$intMissions = intval($intMissions);
				if (
					$intMissions > 0
					&& preg_match('/^user_missions_([0-9]+)$/', $strKey, $arrMatched)
				) {
					$intUser = $arrMatched[1];
					$objLatestUserCampaign = first($query->user_campaigns__select(array(
						'_NOT' => array(
							'status' => array('Paused', 'Resumed'),
							'input_value' => '_enrollment'
						),
						'user_id' => $intUser,
						'campaign_id' => 1,
						'_ORDER' => 'schedule_date + 0 DESC',
						'_LIMIT' => 1
					)));
					//dumper($objLatestUserCampaign,1,1);
					$intStart = $objLatestUserCampaign->schedule_date+1;
					$intEnd = strtotime('+' . $intMissions . " week", $intStart);
					$strDate = date("Y-m-d H:i:s");
					/*
					 * Potential issue if pause was already created ahead of marking
					 */
					$query->user_campaigns__insert(array(
						'campaign_id' => 1,
						'mission_id' => 1,
						'user_id' => $intUser,
						'institution_id' => $this->_user_session_data->institution_id,
						'status' => 'Paused',
						'schedule_date' => $intStart,
						'created' => $strDate
					));
					$query->user_campaigns__insert(array(
						'campaign_id' => 1,
						'mission_id' => 1,
						'user_id' => $intUser,
						'institution_id' => $this->_user_session_data->institution_id,
						'status' => 'Resumed',
						'schedule_date' => $intEnd,
						'created' => $strDate
					));
					$objAutomation = new Automation();
					$objAutomation->user_goal(array(
						'campaign_id' => 1,
						'user_id' => $intUser,
						'institution_id' => $this->_user_session_data->institution_id
					));
				}
			}
			$arrResult['success'] = 'true';
			print json_encode($arrResult);
			exit;
		}
	}

	public function groupedpausingAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$objUsers = new Users();
		$objRegistration = new Registration();

		$this->view->arrClasses = $arrClasses = array_hash("class_id", $query->classes__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"_ORDER" => "class_hierarchy+0"
		)));
		$arrUserEnrollments = array_hash('user_id', $query->user_campaigns__select(array(
			'status' => array('Enrollment',	'Unenrollment'),
			'campaign_id' => 1,
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$this->view->arrUsers = $arrUsers = array_hash("user_id", $query->users__select(array(
			'user_id' => array_stack('user_id', $arrUserEnrollments)
		)));
		$this->view->arrUserClasses = $arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
			"user_id" => array_keys($arrUsers),
			"class_id" => array_keys($arrClasses),
			"class_role" => "Student"
		)));
		$arrdUserClassIds = $this->view->arrdUserClassIds = object_extract("class_id", array_bubble_hash("user_id", $arrUserClasses));
		$arrClassStudents = $this->view->arrClassStudents = object_extract("user_id", array_bubble_hash("class_id", $arrUserClasses));
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrResult = array();
			$arrResult['success'] = 'false';
			if (!empty($arrPost['strPostType']))
			{
				if ($arrPost['strPostType'] == 'add_pause')
				{
					if (empty($arrPost['pause_start']))
						$arrResult['error']['pause_start'] = 'This is a required field.';
					if (empty($arrPost['pause_end']))
						$arrResult['error']['pause_end'] = 'This is a required field.';
					$arrDates = array('pause_start','pause_end');
					// validate dates
					foreach ($arrDates as $strName)
					{
						if (isset($arrResult['error'][$strName]))
							continue;
						if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $arrPost[$strName]))
							$arrResult['error'][$strName] = 'This field is invalid.';
					}
					// verify that the pause start is not before the latest line
					if (!isset($arrResult['error']))
					{
						$arrLatestCampaigns = array_hash('user_id', $query->user_campaigns__select(array(
							'campaign_id' => 1,
							'user_id' => array_keys($arrUserClasses),
							'institution_id' => $this->_user_session_data->institution_id,
							'_NOT' => array(
								'status' => array('Paused', 'Resumed'),
								'input_value' => '_enrollment'
							),
							'_ORDER' => 'schedule_date+0 DESC',
							'_GROUP' => 'user_id'
						)));
						$strDate = date("Y-m-d H:i:s");
						list($intMonth, $intDay, $intYear) = explode('/', $arrPost['pause_start']);
						$intStartTime = mktime(0,0,0,$intMonth,$intDay,$intYear);
						list($intMonth, $intDay, $intYear) = explode('/', $arrPost['pause_end']);
						$intEndTime = mktime(0,0,0,$intMonth,$intDay,$intYear);
						foreach ($arrUserClasses as $intUser => $objUserClass)
						{
							$objLatestCampaign = $arrLatestCampaigns[$intUser];
							$intLatest = $objLatestCampaign->schedule_date;
							if ($intEndTime <= $intLatest)
								continue;
							if ($intStartTime <= $intLatest)
								$intStartTime = $intLatest+1;
							$query->user_campaigns__insert(array(
								'campaign_id' => 1,
								'mission_id' => 1,
								'user_id' => $intUser,
								'institution_id' => $this->_user_session_data->institution_id,
								'status' => 'Paused',
								'schedule_date' => $intStartTime,
								'created' => $strDate,
								'input_value' => '_enrollment'
							));
							$query->user_campaigns__insert(array(
								'campaign_id' => 1,
								'mission_id' => 1,
								'user_id' => $intUser,
								'institution_id' => $this->_user_session_data->institution_id,
								'status' => 'Resumed',
								'schedule_date' => $intEndTime,
								'created' => $strDate,
								'input_value' => '_enrollment'
							));
						}
						$arrResult['success'] = 'true';
					}
				}
			}
			print json_encode($arrResult);
			exit;
		}
	}

	public function pausemanagerAction()
	{
		$query = new QueryGen();
		$objAutomation = new Automation();
		$arrGet = $this->_request->getParams();
		if (empty($arrGet['user_id']))
		{
			print "Sorry, there was an error: CC-PM101-FDG98D";
			exit;
		}
		if (empty($arrGet['campaign_id']))
		{
			print "Sorry, there was an error: CC-PM102-9DG8GD";
			exit;
		}
		$this->view->user_id = $arrGet['user_id'];
		$this->view->campaign_id = $arrGet['campaign_id'];
		$arrPauseResume = array_bubble_hash('created', 'status', $query->user_campaigns__select(array(
			'user_id' => $arrGet['user_id'],
			'status' => array('Paused', 'Resumed'),
			'_ORDER' => 'schedule_date+0 DESC'
		)));
		$arrLadders = array_hash('ladder', $query->velocity_ladders__select(array(
			'campaign_id' => 1
		)));
		$this->view->arrPauseResume = $arrPauseResume;
		if ($this->_request->isPost())
		{
			$arrResult = array();
			$arrResult['success'] = 'false';
			$arrPost = $this->_request->getPost();
			if (!empty($arrPost['strPostType']))
			{
				if ($arrPost['strPostType'] == 'remove_pause')
				{
					if (empty($arrPost['intPausedId']) || empty($arrPost['intResumedId']))
						$arrResult['error'] = 'Sorry, there was an error: CC-PM103-sdfd87';
					if (!isset($arrResult['error']))
					{
						$query->user_campaigns__delete(array(
							'user_campaign_id' => $arrPost['intPausedId'],
							'campaign_id' => 1,
							'mission_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id
						));
						$query->user_campaigns__delete(array(
							'user_campaign_id' => $arrPost['intResumedId'],
							'campaign_id' => 1,
							'mission_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id
						));
						$objAutomation->user_goal(array(
							'campaign_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id
						));
						$arrResult['success'] = 'true';
					}
				}
				else if ($arrPost['strPostType'] == 'add_pause')
				{
					if (empty($arrPost['pause_start']))
						$arrResult['error']['pause_start'] = 'This is a required field.';
					if (empty($arrPost['pause_end']))
						$arrResult['error']['pause_end'] = 'This is a required field.';
					$arrDates = array('pause_start','pause_end');
					// validate dates
					foreach ($arrDates as $strName)
					{
						if (isset($arrResult['error'][$strName]))
							continue;
						if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $arrPost[$strName]))
							$arrResult['error'][$strName] = 'This field is invalid.';
					}
					// verify that the pause start is not before the latest line
					if (!isset($arrResult['error']))
					{
						list($intMonth, $intDay, $intYear) = explode('/', $arrPost['pause_start']);
						$intStart = mktime(0,0,0,$intMonth,$intDay,$intYear);
						list($intMonth, $intDay, $intYear) = explode('/', $arrPost['pause_end']);
						$intEnd = mktime(0,0,0,$intMonth,$intDay,$intYear);
						$arrCampaigns = $query->user_campaigns__select(array(
							'campaign_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id,
							'_GREATER' => array(
								'schedule_date' => $intStart+1
							),
							'_NOT' => array(
								'status' => array('Paused', 'Resumed')
							),
							'_LIMIT' => 1
						));
						if (count($arrCampaigns))
						{
							$objUserLatestCampaign = first($query->user_campaigns__select(array(
								'campaign_id' => 1,
								'user_id' => $arrGet['user_id'],
								'institution_id' => $this->_user_session_data->institution_id,
								'_ORDER' => 'schedule_date+0 DESC',
								'_NOT' => array(
									'status' => array('Paused', 'Resumed')
								),
								'_LIMIT' => 1
							)));
							$arrResult['error'] = 'You can only add a pause of this user after ' . date('M d, Y', $objUserLatestCampaign->schedule_date) . ' (' . date('m/d/Y', $objUserLatestCampaign->schedule_date) . ')';
						}
					}
					if (!isset($arrResult['error']))
					{
						$strDate = date("Y-m-d H:i:s");
						list($intMonth, $intDay, $intYear) = explode('/', $arrPost['pause_start']);
						$intStartTime = $intTime = mktime(0,0,0,$intMonth,$intDay,$intYear);

						// check if the pause shoud be associated with a ladder
						$objLatestItem = first($query->user_campaigns__select(array(
							'_LESSER' => array(
								'schedule_date' => $intStartTime
							),
							'campaign_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id,
							'_NOT' => array(
								'status' => array('Paused', 'Resumed')
							),
							'_ORDER' => 'schedule_date+1 DESC',
							'_LIMIT' => 1
						)));
						$intLadder = $intVelocity = NULL;
						if ($objLatestItem->ladder)
						{
							$intLadder = $objLatestItem->ladder ? $objLatestItem->ladder-1 : 0;
							$intVelocity = $arrLadders[$intLadder]->velocity;
						}
						$query->user_campaigns__insert(array(
							'campaign_id' => 1,
							'mission_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id,
							'status' => 'Paused',
							'ladder' => $intLadder,
							'ladder_velocity' => $intVelocity,
							'schedule_date' => $intTime,
							'created' => $strDate,
							'input_value' => '_enrollment'
						));
						list($intMonth, $intDay, $intYear) = explode('/', $arrPost['pause_end']);
						$intTime = mktime(0,0,0,$intMonth,$intDay,$intYear);
						$query->user_campaigns__insert(array(
							'campaign_id' => 1,
							'mission_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id,
							'status' => 'Resumed',
							'ladder' => $intLadder,
							'ladder_velocity' => $intVelocity,
							'schedule_date' => $intTime,
							'created' => $strDate,
							'input_value' => '_enrollment'
						));
						$objAutomation->user_goal(array(
							'campaign_id' => 1,
							'user_id' => $arrGet['user_id'],
							'institution_id' => $this->_user_session_data->institution_id
						));
						$arrResult['success'] = 'true';
					}
				}
			}
			print json_encode($arrResult);
			exit;
		}
	}

	/*
	 * Display all available campaigns for a parents children to enroll into
	 */
	public function campaignchildenrollAction()
	{
		$objUsers = new Users();
		$objCampaigns = new Campaigns();
		$objPermissions = new Permissions();
		$objInstituions = new Institutions();
		$objScheduler = new Scheduler();
		$objRoles = new Roles();
		$objClasses = new Classes();

		$strTStyle = $this->view->tstyle = $this->_request->getParam("tstyle");

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrGet = $this->_request->getParams();
			if (!isset($arrPost["user_id"]))
			{
				print text("Sorry, there was an error") . ": CC-CCE101-D7DFDS";
				exit;
			}
			if (!isset($arrGet["enrollaction"]) || !in_array($arrGet["enrollaction"], array("enroll", "unenroll")))
			{
				print text("Sorry, there was an error") . ": CC-CCE102-D7DF7D";
				exit;
			}
			if (!isset($arrGet["enrollcampaign"]))
			{
				print text("Sorry, there was an error") . ": CC-CCE103-8DF8DS";
				exit;
			}
			$objCampaign = first($objCampaigns->_campaigns_select(array(
				"campaign_id" => $arrGet["enrollcampaign"],
				"institution_id" => $this->_user_session_data->institution_id
			)));
			if (!$objCampaign)
			{
				print text("Sorry, there was an error") . ": CC-CCE104-9DFDDS";
				exit;
			}
			if ($arrGet["enrollaction"] == "enroll")
			{
				$objUserCampaign = first($objCampaigns->_user_campaigns_select(array(
					"status" => "Enrollment",
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => $objCampaign->installed_campaign_id,
					"user_id" => $arrPost["user_id"]
				)));
				if ($objUserCampaign)
				{
					print text("Sorry, there was an error") . ": CC-CCE106-8D8FDS";
					exit;
				}
				$arrSql = array(
					"status" => "Enrollment",
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => $objCampaign->installed_campaign_id,
					"user_id" => $arrPost["user_id"],
					"schedule_date" => time()
				);
				if ($objCampaign->campaign_type == "Book")
				{
					$arrSql["line_offset"] = $arrPost["lines_ahead"];
					$arrSql["ladder"] = $arrPost["ladder"];
				}

				$objCampaigns->_user_campaigns_insert($arrSql);
			}
			else
			{
				$objCampaigns->_user_campaigns_delete(array(
					"status" => "Enrollment",
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => $objCampaign->installed_campaign_id,
					"user_id" => $arrPost["user_id"]
				));
			}
			print 1;
			exit;
		}

		// Loop through the children of this parent and create a list of campaigns with enrollment status
		$arrEnrollList = array();
		$arrCampaignParams = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if ($strTStyle == "tanyatemplate1")
		{
			$arrCampaignParams["campaign_name"] = "Tanya";
		}
		$this->view->arrCampaigns = $arrCampaigns = $objCampaigns->_campaigns_select($arrCampaignParams);

		// Collect the users
		$arrUserIds = array();
		if ($objRoles->isRole("Parent"))
		{
			$arrRelationships = $objUsers->_relationships_select(array(
				"user_id" => $this->_user_session_data->user_id
			));
			foreach ($arrRelationships as $objRelationship)
			{
				$arrUserIds[] = $objRelationship->relation_id;
			}
		}
		else if ($objRoles->isRole("Teacher"))
		{
			$objTeacherClass = first($objClasses->_user_classes_select(array(
				"user_id" => $this->_user_session_data->user_id,
				"class_role" => "Teacher"
			)));
			if (!$objTeacherClass)
			{
				print text("Sorry, there was an error") . ": CC-CCE101-DSF7DS";
				exit;
			}
			$arrStudentClasses = $objClasses->_user_classes_select(array(
				"class_id" => $objTeacherClass->class_id,
				"class_role" => "Student"
			));
			foreach ($arrStudentClasses as $objUserClass)
			{
				$arrUserIds[] = $objUserClass->user_id;
			}
		}
		else
		{
			// Get uses from classes
			$arrClasses = $objClasses->_classes_select(array(
				"institution_id" => $this->_user_session_data->institution_id
			));
			$intClassParam = $this->_request->getParam("class_id");
			if ($intClassParam < 1)
			{
				$arrClassIds = array();
				foreach ($arrClasses as $objClass)
				{
					$arrClassIds[] = $objClass->class_id;
				}
			}
			else
				$arrClassIds[] = $intClassParam;
			$arrUserClasses = $objClasses->_user_classes_select(array(
				"class_id" => $arrClassIds
			));
			$arrUserIdsHash = array();
			foreach ($arrUserClasses as $objUserClass)
			{
				$arrUserIdsHash[$objUserClass->user_id] = 1;
			}
			$arrUserIds = array_keys($arrUserIdsHash);
		}
		$arrChildrenData = $objUsers->_users_select(array(
			"user_id" => $arrUserIds
		));
		$arrChildrenHash = array();
		foreach ($arrChildrenData as $objChild)
		{
			$arrChildrenHash[$objChild->user_id] = $objChild;
		}

		$arrChildren = array();
		foreach ($arrUserIds as $intUser)
		{
			if (!isset($arrChildren[$intUser]))
			{
				if (!isset($arrChildrenHash[$intUser]))
					continue;
				$objChild = $arrChildren[$intUser] = $arrChildrenHash[$intUser];
				$arrEnrolledCampaigns = $objCampaigns->_user_campaigns_select(array(
					"user_id" => $intUser,
					"status" => "Enrollment",
					"institution_id" => $this->_user_session_data->institution_id
				));
				$arrEnrolledCampaignsHash = array();
				foreach ($arrEnrolledCampaigns as $objEnrolledCampaings)
				{
					$arrEnrolledCampaignsHash[$objEnrolledCampaings->campaign_id] = $objEnrolledCampaings;
				}
				// Make sure this child has the right permissions
				$objPermission = first($objPermissions->_permissions_select(array(
					"permission" => "Student",
					"institution_id" => $this->_user_session_data->institution_id,
					"user_id" => $objChild->user_id
				)));

				if ($objPermission)
				{
					// Loop though all campaigns for each user and find campaigns installed status
					foreach ($arrCampaigns as $objCampaign)
					{
						$arrLadders = array();
						if ($objCampaign->campaign_type == "Book")
						{
							$arrLadders = $objScheduler->load_available_ladders2(array(
								"user_id" 			=> $objChild->user_id,
								"institution_id"	=> $this->_user_session_data->institution_id,
								"campaign_id"		=> $objCampaign->installed_campaign_id
							));
						}
						$arrEnrollList[$objChild->user_id][] = array(
							"boolEnrolled" => isset($arrEnrolledCampaignsHash[$objCampaign->installed_campaign_id]),
							"objCampaign" => $objCampaign,
							"arrLadders" => $arrLadders
						);
					}
				}
			}
		}
		$this->view->arrChildren = $arrChildren;
		$this->view->arrEnrollList = $arrEnrollList;
	}

	/*
	 * Display all available campaigns for a parents children to enroll into
	 */
	public function campaignchildenrolltanyaAction()
	{
		$query = new QueryGen();
		$objUsers = new Users();
		$objCampaigns = new Campaigns();
		$objPermissions = new Permissions();
		$objInstituions = new Institutions();
		$objScheduler = new Scheduler();
		$objRoles = new Roles();
		$objClasses = new Classes();
		$objAutomations = new Automation();
		$objLegacy = new Legacy();

		$strTStyle = $this->view->tstyle = $this->_request->getParam("tstyle");

		$arrGet = $this->_request->getParams();
		if (isset($arrGet["resetaction"]))
		{
			$objCampaign = first($objCampaigns->_campaigns_select(array(
				"campaign_id" => intval($arrGet["resetcampaign"])
			)));
			$objCampaigns->_user_campaigns_delete(array(
				"institution_id" => $this->_user_session_data->institution_id,
				"user_id" => intval($arrGet["resetaction"]),
				"campaign_id" => $objCampaign->installed_campaign_id
			));
			print $arrGet["resetaction"];
			exit;
		}
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (!isset($arrPost["user_id"]))
			{
				print text("Sorry, there was an error") . ": CC-CCE101-D7DFDS";
				exit;
			}
			if (!isset($arrGet["enrollaction"]) || !in_array($arrGet["enrollaction"], array("enroll", "unenroll")))
			{
				print text("Sorry, there was an error") . ": CC-CCE102-D7DF7D";
				exit;
			}
			if (!isset($arrGet["enrollcampaign"]))
			{
				print text("Sorry, there was an error") . ": CC-CCE103-8DF8DS";
				exit;
			}
			$objCampaign = first($objCampaigns->_campaigns_select(array(
				"campaign_id" => $arrGet["enrollcampaign"],
				"institution_id" => $this->_user_session_data->institution_id
			)));
			if (!$objCampaign)
			{
				print text("Sorry, there was an error") . ": CC-CCE104-9DFDDS";
				exit;
			}
			if ($arrGet["enrollaction"] == "enroll")
			{
				$objUserCampaign = first($objCampaigns->_user_campaigns_select(array(
					"status" => "Enrollment",
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => $objCampaign->installed_campaign_id,
					"user_id" => $arrPost["user_id"]
				)));
				if ($objUserCampaign)
				{
					print text("Sorry, there was an error") . ": CC-CCE106-8D8FDS";
					exit;
				}
				$objUnenrollment = first($query->user_campaigns__select(array(
					"status" => "Unenrollment",
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => 1,
					"user_id" => $arrPost["user_id"]
				)));
				if (!$objUnenrollment)
				{
					$arrSql = array(
						"status" => "Enrollment",
						"institution_id" => $this->_user_session_data->institution_id,
						"campaign_id" => 1,
						"mission_id" => 1,
						"user_id" => $arrPost["user_id"],
						"schedule_date" => time(),
						'input_value' => '_enrollment'
					);
					if ($objCampaign->campaign_type == "Book")
					{
						$arrSql["line_offset"] = $arrPost["lines_ahead"];
						$arrSql["ladder"] = $arrPost["ladder"];
					}
					$query->user_campaigns__insert($arrSql);
				} else {
					$query->user_campaigns__update(array(
						'where' => array(
							'user_campaign_id' => $objUnenrollment->user_campaign_id
						),
						'values' => array(
							'status' => 'Enrollment',
							'ladder' => $arrPost["ladder"],
							"line_offset" => $arrPost["lines_ahead"]
						)
					));
				}
				$objLatestItem = first($query->user_campaigns__select(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => 1,
					"user_id" => $arrPost["user_id"],
					'_ORDER' => 'schedule_date+0 DESC',
					'_LIMIT' => 1
				)));
				if ($objLatestItem->status == 'Paused')
				{
					$query->user_campaigns__insert(array(
						'campaign_id' => 1,
						'mission_id' => 1,
						'user_id' => $arrGet['user_id'],
						'institution_id' => $this->_user_session_data->institution_id,
						'status' => 'Resumed',
						'schedule_date' => time(),
						'input_value' => '_enrollment'
					));
				}
				$arrResult = $objAutomations->user_goal(array(
					"user_id" => $arrPost["user_id"],
					"campaign_id" => $objCampaign->installed_campaign_id
				));
				$objLegacy->update_legacy_user_tracks(array(
					"user_id" => $arrPost["user_id"],
					"enrolled" => 1
				));

			}
			else
			{
				$objCampaigns->_user_campaigns_update(array(
					'where' => array(
						"status" => "Enrollment",
						"institution_id" => $this->_user_session_data->institution_id,
						"campaign_id" => 1,
						"user_id" => $arrPost["user_id"]
					),
					'values' => array(
						"status" => "Unenrollment"
					)
				));
				$objLegacy->update_legacy_user_tracks(array(
					"user_id" => $arrPost["user_id"],
					"enrolled" => 0
				));
				$query->user_campaign_progress__update(array(
					"where" => array(
						"user_id" => $arrPost["user_id"],
						"campaign_id" => $objCampaign->installed_campaign_id,
						"institution_id" => $this->_user_session_data->institution_id
					),
					"values" => array(
						"campaign_goal" => '0',
						'current_line' => '0'
					)
				));
				$query->user_campaigns__insert(array(
					'campaign_id' => 1,
					'mission_id' => 1,
					'user_id' => $arrGet['user_id'],
					'institution_id' => $this->_user_session_data->institution_id,
					'status' => 'Paused',
					'schedule_date' => time(),
					'input_value' => '_enrollment'
				));
			}
			print 1;
			exit;
		}

		// Loop through the children of this parent and create a list of campaigns with enrollment status
		$arrEnrollList = array();
		$arrCampaignParams = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if ($strTStyle == "tanyatemplate1")
		{
			$arrCampaignParams["campaign_name"] = "Tanya";
		}
		$this->view->arrCampaigns = $arrCampaigns = $objCampaigns->_campaigns_select($arrCampaignParams);

		// Collect the users
		$arrUserIds = array();
		if ($objRoles->isRole("Parent"))
		{
			$arrRelationships = $objUsers->_relationships_select(array(
				"user_id" => $this->_user_session_data->user_id
			));
			foreach ($arrRelationships as $objRelationship)
			{
				$arrUserIds[] = $objRelationship->relation_id;
			}
		}
		else if ($objRoles->isRole("Teacher"))
		{
			$objTeacherClass = first($objClasses->_user_classes_select(array(
				"user_id" => $this->_user_session_data->user_id,
				"class_role" => "Teacher"
			)));
			if (!$objTeacherClass)
			{
				print text("Sorry, there was an error") . ": CC-CCE101-DSF7DS";
				exit;
			}
			$arrStudentClasses = $objClasses->_user_classes_select(array(
				"class_id" => $objTeacherClass->class_id,
				"class_role" => "Student"
			));
			foreach ($arrStudentClasses as $objUserClass)
			{
				$arrUserIds[] = $objUserClass->user_id;
			}
		}
		else if ($this->_request->getParam("user_id"))
		{
			$arrUserIds = array($this->_request->getParam("user_id"));
		}
		else
		{
			// Get uses from classes
			$arrClasses = $objClasses->_classes_select(array(
				"institution_id" => $this->_user_session_data->institution_id
			));
			$intClassParam = $this->_request->getParam("class_id");
			if ($intClassParam < 1)
			{
				$arrClassIds = array();
				foreach ($arrClasses as $objClass)
				{
					$arrClassIds[] = $objClass->class_id;
				}
			}
			else
				$arrClassIds[] = $intClassParam;
			$arrUserClasses = $objClasses->_user_classes_select(array(
				"class_id" => $arrClassIds
			));
			$arrUserIdsHash = array();
			foreach ($arrUserClasses as $objUserClass)
			{
				$arrUserIdsHash[$objUserClass->user_id] = 1;
			}
			$arrUserIds = array_keys($arrUserIdsHash);
		}
		$arrChildrenData = array_clean_slashes($query->users__select(array(
			"user_id" => $arrUserIds,
			'_ORDER' => 'last_name ASC, first_name ASC'
		)));
		$arrChildrenHash = array();
		foreach ($arrChildrenData as $objChild)
		{
			$arrChildrenHash[$objChild->user_id] = $objChild;
		}

		$arrChildren = array();
		foreach ($arrChildrenHash as $intUser => $objChild)
		{
			if (!isset($arrChildren[$intUser]))
			{
				if (!isset($arrChildrenHash[$intUser]))
					continue;
				$arrChildren[$intUser] = $objChild;
				$arrEnrolledCampaigns = $objCampaigns->_user_campaigns_select(array(
					"user_id" => $intUser,
					"status" => array(
						"Enrollment",
						"Unenrollment"
					),
					"institution_id" => $this->_user_session_data->institution_id
				));
				$arrEnrolledCampaignsHash = array();
				foreach ($arrEnrolledCampaigns as $objEnrolledCampaings)
				{
					$arrEnrolledCampaignsHash[$objEnrolledCampaings->campaign_id] = $objEnrolledCampaings;
				}
				// Make sure this child has the right permissions
				$objPermission = first($objPermissions->_permissions_select(array(
					"permission" => "Student",
					"institution_id" => $this->_user_session_data->institution_id,
					"user_id" => $objChild->user_id
				)));
				if ($objPermission)
				{
					// Loop though all campaigns for each user and find campaigns installed status
					foreach ($arrCampaigns as $objCampaign)
					{
						$arrLadders = array();
						$intLatest = 0;
						if ($objCampaign->campaign_type == "Book")
						{
							$arrLadders = $objScheduler->load_available_ladders2(array(
								"user_id" 			=> $objChild->user_id,
								"institution_id"	=> $this->_user_session_data->institution_id,
								"campaign_id"		=> $objCampaign->installed_campaign_id
							));
							$objLatestTask = first($objCampaigns->_user_campaigns_select(array(
								"campaign_id" => $objCampaign->installed_campaign_id,
								"user_id" => $objChild->user_id,
								"institution_id" => $this->_user_session_data->institution_id,
								"_ORDER" => "task_increment + 0 DESC",
								"_LIMIT" => 1
							)));
							if ($objLatestTask)
							{
								$intLatest = $objLatestTask->task_increment;
								if (
									isset($arrEnrolledCampaignsHash[$objCampaign->installed_campaign_id])
									&& $objLatestTask->task_increment < $arrEnrolledCampaignsHash[$objCampaign->installed_campaign_id]->line_offset
								)
									$intLatest = $arrEnrolledCampaignsHash[$objCampaign->installed_campaign_id]->line_offset;
							}
						}
						if (count($arrLadders))
							$arrEnrollList[$objChild->user_id][] = array(
								"boolEnrolled" => isset($arrEnrolledCampaignsHash[$objCampaign->installed_campaign_id]) && $arrEnrolledCampaignsHash[$objCampaign->installed_campaign_id]->status == 'Enrollment',
								"objEnrollment" => @$arrEnrolledCampaignsHash[$objCampaign->installed_campaign_id],
								"intLatest" => @$intLatest,
								"objCampaign" => $objCampaign,
								"arrLadders" => $arrLadders,
								"objLatestTask" => $objLatestTask
							);
					}
				}
			}
		}
		$this->view->arrChildren = $arrChildren;
		$this->view->arrEnrollList = $arrEnrollList;
	}


	public function parentsetupAction()
	{
		$objUsers = new Users();
		$objInstitutions = new Institutions();
		$objPermissions = new Permissions();
		$this->view->objUser = first($objUsers->_users_select(array(
			"user_id" => $this->_user_session_data->user_id
		)));
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		$this->view->objPermission = $this->objPermission;
	}

	public function campaignrulesAction()
	{
		$intCampaign = $this->view->campaign_id = $this->_request->getParam("campaign_id");
		if (
			!isset($intCampaign)
			|| !$intCampaign
		) {
			print text("Sorry, there was an error") . ": CC-CR101-GD4GD4";
			exit;
		}

		$objRules = new Rules();
		$intDeleteId = intval($this->_request->getParam("delete"));
		if ($intDeleteId)
		{
			$boolDelete = $objRules->_rules_delete(array(
				"rule_id" => $intDeleteId,
				"campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id
			));
			print $boolDelete;
			exit; // ajax
		}
		if ($this->_request->isPost()) // Ajax
		{
			// Post the parameters to the database
			$arrParams = $this->_request->getPost();
			$arrParams = $objRules->rule_process_param($arrParams);
			if (!count($arrParams))
			{
				print "No feilds where provided.";
				exit; // ajax
			}

			$strParams = join(";", $arrParams);

			$strRuleType = $this->_request->getPost("ruletype");
			if (!in_array($strRuleType, array("Allow", "Deny")))
			{
				print text("Sorry, there was an error") . ": CC-CR102-9S78DF";
				exit;
			}

			$intAI = $objRules->_rules_insert(array(
				"rule_type"			=> $strRuleType,
				"rule_applies_to"	=> $this->_request->getPost("applies_to"),
				"rule"				=> $strParams,
				"institution_id"	=> $this->_user_session_data->institution_id,
				"campaign_id"		=> $intCampaign
			));

			$objRule = current($objRules->_rules_select(array(
				"rule_id" => $intAI
			)));
			print $intAI . "\t" . json_encode($objRule);
			exit; // Ajax
		}

		// Send the current rules to the view
		$this->view->objRules = $objRules->_rules_select(array(
			"institution_id" 	=> $this->_user_session_data->institution_id,
			"campaign_id"		=> $intCampaign,
			"rule_applies_to"	=> "Campaign Availability"
		));
	}

	public function missionbooksAction()
	{
		$this->view->mission_id = $intMission = intval($this->_request->getParam("mission_id"));
		if (!$intMission)
		{
			print text("Sorry, there was an error") . ": CC-MB101-FDWERT";
			exit;
		}
		$objMissions = new Missions();
		$objMission = $this->view->objMission = current($objMissions->_missions_select(array(
			"mission_id" => $intMission
		)));
		if (!$objMission)
		{
			print text("Sorry, there was an error") . ": CC-MB102-DF87GD";
			exit;
		}

		if ($this->_request->isPost())
		{
			$intBook = current($this->_request->getPost("book_id"));
			if (!$intBook)
			{
				print text("Sorry, there was an error") . ": CC-MB103-8D7FGD";
				exit;
			}
			$intResult = $objMissions->_missions_update(array(
				"where" => array(
					"mission_id" => $intMission
				),
				"values" => array(
					"book_id" => $intBook
				)
			));
			print $intResult;
			exit;
		}

		$objBooks = new Books();
		$this->view->arrBooks = $objBooks->_books_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
	}

	public function velocitiesAction()
	{
		$this->view->host_id = $this->_request->getParam("host_id");
		$this->view->network_id = $this->_request->getParam("network_id");
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$this->view->campaign_id = $this->_request->getParam("campaign_id");
		if (
			!isset($this->view->campaign_id)
			|| !$this->view->campaign_id
		) {
			print text("Sorry, there was an error") . ": CC-V101-G4G44S";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objBooks = new Books();

		$this->view->objCampaign = current(array_clean_slashes($objCampaigns->_campaigns_select(array(
			"campaign_id" => $this->view->campaign_id
		))));

		$arrMissions = $objMissions->_missions_select(array(
			"campaign_id" => $this->view->campaign_id
		));

		// Begin to check if this is a book campaign
		if (count($arrMissions) == 1)
		{
			$objMission = $this->view->objMission = current($arrMissions);
			if ($objMission->book_id)
			{
				$objBook = $this->view->objBook = current($objBooks->_books_select(array(
					"book_id" => $objMission->book_id
				)));
			}
		}

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (isset($objBook))
			{
				$strBookMeasurement = $arrPost["book_measurement"];
				unset($arrPost["book_measurement"]);
				$objMissions->missions_update_book_measurement(array(
					"campaign_id" => $this->view->campaign_id,
					"book_measurement" => $strBookMeasurement
				));
			}
			$arrPost["campaign_id"] = $this->view->campaign_id;
			print $objCampaigns->velocity_insert($arrPost);
			exit;
		}

		$objGrades = new Grades();
		$intInstitution = $this->view->objCampaign->institution_id;

		// select all the grades
		$this->view->arrGrades = $objGrades->_grades_select_hierarchal(
			array (
				"institution_id" => $intInstitution
			)
		);
		// select velocities
		$this->view->arrVelocityGrades = $objGrades->_velocity_grades_select(
			array(
				"campaign_id" => $this->view->campaign_id
			)
		);
		// process hash with hierarchy key
		$arrGradeHash = array();
		foreach ($this->view->arrVelocityGrades as $objGradeVel)
		{
			$arrGradeHash[$objGradeVel->grade_hierarchy] = $objGradeVel;
		}
		$this->view->arrGradeHash = $arrGradeHash;
		// select all ladder velocities
		$this->view->arrVelocityLadders = $objGrades->_velocity_ladders_select(
			array(
				"campaign_id" => $this->view->campaign_id
			)
		);
		// process hash with
		$arrLadderHash = array();
		foreach ($this->view->arrVelocityLadders as $objLadderVel)
		{
			$arrLadderHash[$objLadderVel->ladder] = $objLadderVel;
		}
		$this->view->arrLadderHash = $arrLadderHash;
	}

	/********** CAMPAIGNS *********************/

    public function campaigninstallAction()
    {
		$query = new QueryGen();
		$role = new Roles();
        $objCampaigns = new Campaigns();
		$intInstitution = $this->_user_session_data->institution_id;
		if ($role->isAllowed('Network')) {
			$intInstitution = $this->_request->getParam('institution_id');
		}
		$this->view->institution_id = $intInstitution;
		$intHost = 1;
		$objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));

		// Ajax
		if ($this->_request->isPost())
		{
			$intCampaign = intval($this->_request->getPost("campaign_id"));
			$intTemplateCampaignId = intval($this->_request->getPost("template_campaign_id"));
			if (!$intCampaign && !$intTemplateCampaignId)
			{
				print text("Sorry, there was an error") . ": CC-CI102-34ERGS";
				exit;
			}
			$strAction = $this->_request->getPost("action");
			if ($strAction == "install") {

				$intSuccess = $objCampaigns->campaigns_install($intTemplateCampaignId, $intInstitution);
			} else {
				$intSuccess = $objCampaigns->campaigns_uninstall($intCampaign, $intInstitution);
			}
			print $intSuccess;
			exit;
		}

		//$arrCampaigns = $objCampaigns->rule_filter_campaign_object($objCampaigns->campaigns_select_templates($intHost, $intInstitution, 1));
		$arrHostCampaigns = array_hash("campaign_id", $objCampaigns->rule_filter_campaign_object(array_clean_slashes($query->campaigns__select(array(
			"institution_id" => 1,
			"is_active" => 1,
			"_NOT" => array(
				"campaign_name" => "Tanya"
			)
		)))));
		$arrCampaignSchoolTypes = array_hash("campaign_id", "school_type", $query->campaign_school_types__select(array(
			"campagin_id" => array_keys($arrHostCampaigns)
		)));
		foreach ($arrHostCampaigns as $intHostCampaign => $objHostCampaign)
		{
			if (
				isset($arrCampaignSchoolTypes[$intHostCampaign])
				&& !isset($arrCampaignSchoolTypes[$intHostCampaign][$objInstitution->template_style])
			) {
				unset($arrHostCampaigns[$intHostCampaign]);
			}
		}
		$this->view->arrHostCampaigns = $arrHostCampaigns;
		$this->view->arrInstalledCampaigns = array_hash("installed_campaign_id", $objCampaigns->rule_filter_campaign_object($query->campaigns__select(array(
			"installed_campaign_id" => array_keys($arrHostCampaigns),
			"institution_id" => $intInstitution
		))));
	}

	public function campaigninstall_current_Action()
    {
        $this->view->host_id = $host_id = $this->_request->host_id;
        $this->view->network_id = $network_id = $this->_request->network_id;
        $this->view->institution_id = $institution_id = $this->_request->institution_id;

        if ($this->_user_session_data->permission == "Super Administrator")
		{
			$Institutions = new Institutions();
			$this->view->institutions = $Institutions->get_all_active_institutions();
		}
		elseif ($this->_user_session_data->permission == "Host Administrator")
		{
			$Institutions = new Institutions();
			$this->view->institutions = $Institutions->get_institutions_by_host_id($host_id);
		}
		elseif ($this->_user_session_data->permission == "Network Administrator")
		{
			$Institutions = new Institutions();
			$this->view->institutions = $Institutions->get_institutions_by_network_id($network_id);
		}
		elseif ($this->_user_session_data->permission == "Institution Administrator")
		{
			$Campaigns = new Campaigns();
			$this->view->arrCampaigns = $Campaigns->get_template_campaigns_by_host_id($host_id, $institution_id);
		}
    }

	public function missionrulesAction()
	{
		$this->view->campaign_id = $this->_request->getParam("campaign_id");
		$intMission = $this->view->mission_id = intval($this->_request->getParam("mission_id"));
		if (!$intMission)
		{
			print text("Sorry, there was an error") . ": CC-MR101-S89D7F";
			exit;
		}
	}

    public function campaignlistAction()
    {
		$host_id = $network_id = $institution_id = $intInstitution = 0;
		$status = 1; //pull only active records

		$this->view->institution_id = $intInstitution = $institution_id = $this->_request->getParam("institution_id");
		$this->view->network_id = $intInstitution = $network_id = $this->_request->getParam("network_id");
		$this->view->host_id = $intInstitution = $host_id = $this->_request->getParam("host_id");

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();

		$boolInactive = $this->_request->getParam("is_active") == "0" ? 0 : 1;

		// Handle sitution where no hierarchy should be implamented
		if (
			(
				$this->view->host_id
				&& !$this->view->network_id
				&& !$this->view->institution_id
			) || (
				!$this->view->host_id
				&& $this->view->network_id
				&& !$this->view->institution_id
			) || (
				!$this->view->host_id
				&& !$this->view->network_id
				&& $this->view->institution_id
			)
		) {
			$arrCampaignParams = array(
				"institution_id" => $intInstitution,
				"is_active" => $boolInactive
			);
		}
		else
		{
			$arrCampaignParams = array(
				"hierarchy" => array(
					"host_id" => $this->view->host_id,
					"network_id" => $this->view->network_id
				),
				"institution_id" => $this->view->institution_id,
				"is_active" => $boolInactive
			);
		}

		if ($this->_roles->isAllowed("Host Administrator"))
			$arrCampaignParams["installed_campaign_id"] = 0;
		$arrCampaigns = array_clean_slashes($objCampaigns->_campaigns_select($arrCampaignParams));
		$arrInstitutionTypes = array(
			"camp" => "Camp",
			"school" => "School"
		);
		if (isset($arrInstitutionTypes[$this->_request->getParam("institution_type")]))
		{
			$arrCampaigns = $objCampaigns->rule_filter_campaign_object($arrCampaigns, $arrInstitutionTypes[$this->_request->getParam("institution_type")]);
		}


		// Loop through the found campaigns and decide weather or not to show them
		$arrResults = array();
		foreach ($arrCampaigns as $objCampaign)
		{
			if (!$this->_roles->isAllowed("Host Administrator"))
				$objMission = current($objMissions->_missions_select(array(
					"campaign_id" => $objCampaign->installed_campaign_id ? $objCampaign->installed_campaign_id : $objCampaign->campaign_id
				)));
			if (
				$this->_roles->isAllowed("Host Administrator") // hosts do everything
				|| !$objMission // doesnt have a mission
				|| !$objCampaign->installed_campaign_id // isnt installed
				|| $objMission->mission_type != "Incremental"
			) {
				$arrResults[] = $objCampaign;
			}
		}

		$this->view->objCampaigns =	$arrResults;
    }

	public function incrementalscampaignlistAction()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objTasks = new Tasks();
		$objInstitutions = new Institutions();
		$objRoles = new Roles();
		$intInstitution = $this->_request->getParam("institution_id");
		$strTStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		if ($objRoles->isAllowed("Network"))
			$this->view->institution_id = $intInstitution;

		$this->view->objInstitution = $objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$objInstitution)
		{
			print text("Sorry, there was an error") . ": CC-ICL101-8SDF9D";
			exit;
		}

		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getPost();
			if (!(isset($arrParams["campaign_name"]) && !empty($arrParams["campaign_name"])))
			{
				print json_encode(array("error" => "You must provide a campaign name."));
				exit;
			}
			$query = new QueryGen();
			$arrCampaignInsert = array(
				"campaign_name" => $arrParams["campaign_name"],
				"campaign_type" => "Incremental",
				"institution_id" => $this->_user_session_data->institution_id
			);
			if ($objRoles->isAllowed("Network"))
			{
				$objCampaign = first($query->campaigns__select(array(
					"campaign_name" => $arrParams["campaign_name"],
					"network_id" => $this->_user_session_data->network_id,
					"campaign_type" => "Incremental",
					'is_active' => 1
				)));
				$arrCampaignInsert['network_id'] = $this->_user_session_data->network_id;
			}
			else
			{
				$objCampaign = first($query->campaigns__select(array(
					"campaign_name" => $arrParams["campaign_name"],
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_type" => "Incremental",
					'is_active' => 1
				)));
				$arrCampaignInsert['institution_id'] = $this->_user_session_data->institution_id;
			}
			if ($objCampaign)
			{
				print json_encode(array("error" => "This campaign already exists."));
				exit;
			}
			$intCampaign = $query->campaigns__insert($arrCampaignInsert);
			/*$query->missions__insert(array(
				"mission_name" => $arrParams["campaign_name"],
				"mission_type" => "Incremental",
				"campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id,
				"is_active" => 1

			));*/
			print json_encode(array("success" => "true"));
			exit;
		}
		if ($objRoles->isAllowed("Network"))
		{
			if ($intInstitution) {
				$arrCampaignParams = array(
					"institution_id" => $intInstitution,
					"is_active" => 1,
					"campaign_type" => "Incremental"
				);
			} else {
				$arrCampaignParams = array(
					"network_id" => $this->_user_session_data->network_id,
					"is_active" => 1,
					"campaign_type" => "Incremental"
				);
			}
		}
		else
		{
			$arrCampaignParams = array(
				"institution_id" => $this->_user_session_data->institution_id,
				"is_active" => 1,
				"campaign_type" => "Incremental"
			);
		}
		$arrCampaigns = array_clean_slashes($query->campaigns__select($arrCampaignParams));
		if (!$objRoles->isAllowed("Super Administrator"))
			$arrCampaigns = $objCampaigns->rule_filter_campaign_object($arrCampaigns);
		$arrResults = array();
		foreach ($arrCampaigns as $objCampaign)
		{
			$intCampaign = $objRoles->isAllowed("Super Administrator") ? $objCampaign->campaign_id : $objCampaign->installed_campaign_id;
			/*$objHostCampaign = first(array_clean_slashes($objCampaigns->_campaigns_select(array(
				"campaign_id" => $intCampaign
			))));
			if (!$objHostCampaign || !$objHostCampaign->is_active)
				continue;*/
			$intTaskCount = first(first($objTasks->_tasks_select(array(
				"campaign_id" => $objCampaign->campaign_id,
				"institution_id" => $this->_user_session_data->institution_id,
				"_COUNT" => true,
				'is_active' => 1
			))));
			$arrResults[] = array(
				"objCampaign" => $objCampaign,
				"intTaskCount" => $intTaskCount
			);
		}
		$this->view->arrResults = $arrResults;
	}

	public function copycampaigntocampAction()
	{
		$query = new QueryGen();
		$auto = new Automation();
		$intCampaign = $this->_request->getParam("campaign_id");
		$objCampaign = first($query->campaigns__select(array(
			'campaign_id' => $intCampaign,
			'institution_id' => $this->_user_session_data->institution_id
		)));
		if (!$objCampaign) {
			print "Sorry, there was an error: CC-CCTC-askj22";
			exit;
		}
		print json_encode(
			$auto->copy_campaign_to_camp($intCampaign)
		);
		exit;
	}

    public function campaigneditAction()
    {
		$query = new QueryGen();
		global $arrTemplateTypes;

		$strTStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->intCampaign = $intCampaign = $intId = $this->_request->getParam("campaign_id");
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CC-CE101-8SD7FD";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objCampaign = $this->view->objCampaign = first($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));
		$this->view->arrCampaignSchoolTypes = $arrCampaignSchoolTypes = $objCampaigns->_campaign_school_types_select(array(
			"campaign_id" => $intCampaign
		));
		$strCampaignSchoolTypes = "";
		foreach ($arrCampaignSchoolTypes as $objCampaignSchoolType)
		{
			$strCampaignSchoolTypes .= $objCampaignSchoolType->school_type . "=1&";
		}
		$strCampaignSchoolTypes = rtrim($strCampaignSchoolTypes, "&");
		$this->view->strCampaignSchoolTypes = $strCampaignSchoolTypes;

		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getPost();
			$arrResult = array();
			if (!isset($arrParams["campaign_name"]) || !strlen($arrParams["campaign_name"]))
			{
				$arrResult["error"]["campaign_name"] = "Campaign name is a required field.";
			}
			if (isset($arrParams["campaign_name"]) && strlen($arrParams["campaign_name"]) > 40)
			{
				$arrResult["error"]["campaign_name"] = "The campaign name must not be greater than 40 characters.";
			}
			if ($arrParams["campaign_name"] != $objCampaign->campaign_name)
			{
				$objCampaignTemp = first($objCampaigns->_campaigns_select(array(
					"campaign_name" => $arrParams["campaign_name"],
					"institution_id" => $this->_user_session_data->institution_id
				)));
				if ($objCampaignTemp)
				{
					$arrResult["error"]["campaign_name"] = "This campaign name already exists.";
				}
			}
			if (strlen($arrParams["campaign_name"]) > 18)
			{
				$arrResult["error"]["campaign_name"] = "The campaign name must not be longer than 18 characters.";
			}
			if (isset($arrParams["description"]) && strlen($arrParams["description"]) > 400)
			{
				$arrResult["error"]["description"] = "The description must not be greater than 400 characters.";
			}

			$arrParams["points"] = intval(@$arrParams["points"]);
			$arrParams["default_installed"] = intval(@$arrParams["default_installed"]);

			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
				exit;
			}
			else
			{
				$objCampaigns->_campaigns_update(array(
					"where" => array(
						"campaign_id" => $intCampaign
					),
					"values" => $arrParams
				));
				if (isset($arrParams["school_types"]))
				{
					$objCampaigns->_campaign_school_types_delete(array(
						"campaign_id" => $intCampaign
					));
					parse_str($arrParams["school_types"], $arrSchoolTypes);
					foreach ($arrSchoolTypes as $strSchoolType => $boolValue)
					{
						$objCampaigns->_campaign_school_types_insert(array(
							"campaign_id" => $intCampaign,
							"school_type" => $strSchoolType
						));
					}
				}
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
		}
    }

	public function achievementcardeditorAction()
	{
		$query = new QueryGen();
		$objCampaign = new Campaigns();
		$objTask = new Tasks();
		$objUsers = new Users();
		// select campaigns that are available to school.
		$intInstitution = intval($this->_request->getParam("institution_id"));
		if (!$intInstitution)
			$intInstitution = $this->_user_session_data->institution_id;
		$this->view->institution_id = $intInstitution;
		$arrCampaigns = $objCampaign->rule_filter_campaign_object(array_clean_slashes($objCampaign->_campaigns_select(array(
			"institution_id" => $intInstitution,
			"is_active" => 1
		))));
		$arrTasks = array_bubble_hash('campaign_id', $query->tasks__select(array(
			'campaign_id' => array_stack('campaign_id', $arrCampaigns)
		)));
		$arrResults = array();
		foreach ($arrCampaigns as $objCampaignItem)
		{
			if (!isset($arrTasks[$objCampaignItem->campaign_id]))
				continue;
			if (!in_array($objCampaignItem->campaign_name, array("Tanya")))
			{
				$arrResults[] = $objCampaignItem;
			}
		}

		$this->view->arrCampaigns = $arrResults;
		//var_dump($this->view->arrCampaigns); exit;
		if($this->_request->isPost())
		{
			$mode = $this->_request->mode;
			switch($mode){
				case "gettasks":
				$intInstitution = $this->_request->getParam("institution_id");
				$intCampaign = $this->_request->getParam("campaign_id");


				//get tasks for this campaign and institution
				$arrResultTasks = array_clean_slashes($objTask->_tasks_select(array("campaign_id"=>$intCampaign, "institution_id"=>$intInstitution)));
				$i=0;
				if(count($arrResultTasks))
				{
					foreach($arrResultTasks as $objTask)
					{
						$arrTasks['tasks'][$i]['task_id'] = $objTask->task_id;
						$arrTasks['tasks'][$i]['task_name'] = $objTask->task_name;
						$i++;
					}
					echo json_encode($arrTasks);
					break;
				}
				else
				{
					echo "You have to create tasks first!";
					break;
				}
				case "gettaskpoints":
					$intTask = $this->_request->getParam("task_id");
					$objTasks = $objTask->task_point_by_task_id($intTask);
					echo json_encode($objTasks);
					break;
				case "cardpreview":
					$objUtilities = new Utilities();
					$intTask = $this->_request->getParam("task_id");
					$intCampaign = $this->_request->getParam("campaign_id");
					$intInstitution = $this->_request->getParam("institution_id");
					$intSheetNumber = $this->_request->getPost("sheet_number");
					$intCardsPerPage = 10;
					if(isset($intTask)){
						$arrResultTasks = $objTask->task_name_select_by_task_id($intTask);
					}
					else{
						$strDescription = $this->_request->getPost("task_description");
					}

					$arrResultCampaigns = $objCampaign->get_campaign_name($intCampaign);
					//get image for the campaign
					$arrCampaignImage = $objCampaign->get_campaign_image_id($intCampaign);

					// we need to generate 10 unique barcodes at a time
					for($i=0; $i<$intSheetNumber*$intCardsPerPage; $i++)
					{
						$intCardBarcode[$i] = "3" . rand_num_string(19);
						$arrCardBarcode['barcode'] = $intCardBarcode;
						$arrCardBarcode['campaign_name'] = $arrResultCampaigns;
						if(isset($arrResultTasks)){
							$arrCardBarcode['task_name'] = $arrResultTasks;
						}
						elseif(isset($strDescription))
						{
							$arrCardBarcode['description'] = $strDescription;
						}

						$arrCardBarcode['campaign_image'] = $arrCampaignImage;
					}
					echo json_encode($arrCardBarcode);
					break;
			}
			exit;
		}
	}

	public function achievementcardprintAction()
	{
		$created = date("Y-m-d H:i:S");
		$objCampaigns = new Campaigns();
		$objInstitutions = new Institutions();
		$objTasks = new Tasks();
		$objRoles = new Roles();
		$objClasses = new Classes();
		$objConfig = new Config();
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => $this->_user_session_data->institution_id
		));

		//send all views for printing
		$this->view->intTask = $intTask = intval($this->_request->getPost("task_id"));
		$this->view->intCampaign = $intCampaign = $this->_request->getPost("campaign_id");
		$this->view->intPoint = $intPoint =  $this->_request->getPost("points");
		$objCampaign = $this->view->objCampaign = first(array_clean_slashes($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		))));

		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		$this->view->objTask = first($objTasks->_tasks_select(array(
			"task_id" => $intTask
		)));

		$this->view->strAchievement = $strAchievement =  $this->_request->getPost("achievement");
		$this->view->intCampaignImage = $intCampaignImage =  $this->_request->getPost("campaign_image_id");
		$this->view->intInstitution = $intInstitution = $this->_request->getParam("institution_id");
		$this->view->leftCircle = $intLeftCircle = $this->_request->getPost("leftcircle");
		$this->view->rightCircle = $intRightCircle = $this->_request->getPost("rightcircle");
		$intClass = intval($this->_request->getPost("class_id"));
		if (!$intClass && $objRoles->isRole("Teacher"))
		{
			$objTeacherClass = first($objClasses->_user_classes_select(array(
				"user_id" => $this->_user_session_data->user_id,
				"class_role" => "Teacher"
			)));
			if (!$objTeacherClass)
			{
				print text("Sorry, there was an error") . ": CC-CCE101-DSF7DS";
				exit;
			}
			$intClass = $objTeacherClass->class_id;
		}
		$this->view->arrBarcodes = $_POST['card_barcode'];
		if($this->_request->isPost())
		{
			$mode = $this->_request->mode == "saveTemplate" ? "template" : "none";
			if($mode != "template")
			{
				foreach($_POST['card_barcode'] as $key => $value)
				{
					$arrInsert = array(
							"card_serial"		 => $value,
							"campaign_id"	 	 => $intCampaign,
							"institution_id"	 => $intInstitution,
							"card_type"	 		 => $this->_user_session_data->permission,
							"task_id" 			 => $intTask,
							"class_id"			 => $intClass,
							"card_points"		 => $intPoint,
							"achievement"		 => $strAchievement,
							"campaign_image_id"	 => $intCampaignImage,
							"status"			 => 'not scanned',
							"created"		     => $created,
							"created_by"         => $this->_user_session_data->user_id
						);
					//var_dump($arrInsert);
					//insert achievement cards into the database
					$boolInsert = $objCampaigns->insert_achievement_card($arrInsert);
				}

			}
			else
			{
				// insert one template without a barcode
				$arrInsertTemplate = array(
							"mode"				 => $mode,
							"campaign_id"	 	 => $intCampaign,
							"mission_id"		 => 0,
							"institution_id"	 => $intInstitution,
							"task_id" 			 => $intTask,
							"class_id"			 => $intClass,
							"card_points"		 => $intPoint,
							"achievement"		 => $strAchievement,
							"campaign_image_id"	 => $intCampaignImage,
							"status"			 => 'not scanned',
							"card_type"			 => 'Template',
							"created"		     => $created,
							"created_by"         => $this->_user_session_data->user_id
						);
					//var_dump($arrInsertTemplate);
					$boolInsert = $objCampaigns->insert_achievement_card($arrInsertTemplate);

			}
			return $boolInsert;
			exit;
		}
	}
	public function achievementcardlistAction()
	{
		$objCampaign = new Campaigns();
		$this->view->institution_id = $intInstitution = $this->_request->getParam("institution_id");
		$this->view->arrCardTemplates = $arrCardTemplates = $objCampaign->select_achievement_card_templates(
			array(
					"institution_id"	=> $intInstitution,
					"card_type"			=> "Template",
					"created_by"		=> $this->_user_session_data->user_id
			)
		);
	}
	public function achievementcardtemplateprintAction()
	{
		$objUtilities = new Utilities();
		$objCampaigns = new Campaigns();
		$created = date("Y-m-d H:i:S");
		if($this->_request->isPost())
		{
			$intSheetNumber = $this->_request->getPost("sheet_number");
			$intAchievementCard = $this->_request->getPost("template_name");
			//get all info for a selected tempate
			$this->view->objTemplate = $objTemplate = $objCampaigns->select_achievement_card_template(array("achievement_card_id"=>$intAchievementCard));
			$mode = $this->_request->getParam("mode") == "generic"? "generic" : "specific";
			if($this->_user_session_data->permission == "Institution Administrator")
			{
				$strCardType = "School created";
			}
			elseif($this->_user_session_data->permission == "Teacher")
			{
				$strCardType = "Teacher created";
			}

			if($mode == "generic")
			{
				//generate barcodes
				for($i=0; $i<$intSheetNumber*10; $i++)
				{
					$intCardBarcode[$i] = $objUtilities->generateStudentBarcode(20);
					$arrCardBarcode['barcode'] = $intCardBarcode;
				}
				$this->view->arrCardBarcode = $arrCardBarcode['barcode'];
				//insert new achievement card printed form a template
				foreach($arrCardBarcode['barcode'] as $key => $value)
				{
					//prepare an array
					$arrInsert = array (
								"card_serial"		=> $value,
								"achievement"		=> $objTemplate->achievement,
								"institution_id" 	=> $objTemplate->institution_id,
								"campaign_id"		=> $objTemplate->campaign_id,
								"mission_id"		=> $objTemplate->mission_id,
								"task_id"			=> $objTemplate->task_id,
								"campaign_image_id"	=> $objTemplate->campaign_image_id,
								"card_points"		=> $objTemplate->card_points,
								"left_circle"		=> $objTemplate->left_circle,
								"right_circle"		=> $objTemplate->right_circle,
								"card_type"			=> $strCardType,
								"created"			=> $created,
								"created_by"		=> $this->_user_session_data->user_id
							);
					//var_dump($arrInsert);
					$objCampaigns->insert_achievement_card($arrInsert);
				}
			}
			else{
				//generate barcodes
				for($i=0; $i<$intSheetNumber*10; $i++)
				{
					$intCardBarcode[$i] = $objUtilities->generateStudentBarcode(20);
					$arrCardBarcode['barcode'] = $intCardBarcode;
				}
				$this->view->arrCardBarcode = $arrCardBarcode['barcode'];

				//insert new achievement card printed form a template
				foreach($arrCardBarcode['barcode'] as $key => $value)
				{
					//prepare an array
					$arrInsert = array (
								"card_serial"		=> $value,
								"achievement"		=> $objTemplate->achievement,
								"institution_id" 	=> $objTemplate->institution_id,
								"campaign_id"		=> $objTemplate->campaign_id,
								"mission_id"		=> $objTemplate->mission_id,
								"task_id"			=> $objTemplate->task_id,
								"campaign_image_id"	=> $objTemplate->campaign_image_id,
								"card_points"		=> $objTemplate->card_points,
								"left_circle"		=> $objTemplate->left_circle,
								"right_circle"		=> $objTemplate->right_circle,
								"card_type"			=> $strCardType,
								"created"			=> $created,
								"created_by"		=> $this->_user_session_data->user_id
							);
					//var_dump($arrInsert);
					$objCampaigns->insert_achievement_card($arrInsert);
				}
			}
		}
	}
}
?>