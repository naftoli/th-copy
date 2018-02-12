<?php
class ChecklistController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance
	private $boolVerbose = 0;
	private $arrUserTypes = array(
		"super" => "Super Administrator",
		"admin" => "Institution Administrator",
		"teacher" => "Teacher",
		"parent" => "Parent",
		"student" => "Student"
	);

	function preDispatch()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function campaigntasksAction()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objTasks = new Tasks();
		$strTitlePrefix = trim($this->_request->getParam("pre"));
		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Campaign Tasks";
		/*
		$this->view->arrCampaigns = $arrCampaigns = array_hash("campaign_id", $objCampaigns->rule_filter_campaign_object($query->campaigns__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			'is_active' => 1
		))));
		$this->view->arrTasks = $arrTasks = array_hash("task_id", $objTasks->task_filter_date_ranges($query->tasks__select(array(
			"campaign_id" => array_keys(array_stack("campaign_id", $arrCampaigns)),
			'is_active' => 1,
			'is_grid' => 1
		))));
		$this->view->arrTasksHash = $arrTasksHash = array_hash("campaign_id", "task_id", $arrTasks);
		*/
		$objCampaigns = new Campaigns();
		$arrCampaigns = array_hash("subject_id", $objCampaigns->_campaigns_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		//dumper($arrCampaigns,1,1);
		$intStartDate = $this->_request->getParam('taskdate');
		$day = date('w', $intStartDate);
		$start = unixtojd($intStartDate);
		switch ($day) {
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
				$start = $start - $day - 2;
				break;
			case 5:
				break;
			case 6:
				$start = $start -1;
				break;
		}
		if ($start < 2457641) $start = 2457641;
		$end = $start + 6;
		$taskType = $this->_request->getParam('tasktype');		
		$this->view->arrTasks = $arrTasks = array_hash("grid_id", $objTasks->getMashpiaTasks($taskType, $arrCampaigns, $start, $end, true));
		$arrTasksHash = array_hash("subject_id", "grid_id", $arrTasks);
		
		$arrList = array();
		foreach ($arrTasksHash as $intCampaign => $arrTasks)
		{
			foreach ($arrTasks as $intTask => $objTask)
			{
				$arrList[$arrCampaigns[$intCampaign]->subject_name][$objTask->cat]["value"] = $objTask->grid_id;
				$arrList[$arrCampaigns[$intCampaign]->subject_name][$objTask->cat]["text"] = $objTask->cat;
			}
			ksort($arrList[$arrCampaigns[$intCampaign]->subject_name]);
		}
		ksort($arrList);
		$this->view->arrList = $arrList;
		$this->view->arrParams = $this->_request->getParams();
	}

	public function schooltypesAction()
	{
		global $arrAppDetails;

		$strTitlePrefix = trim($this->_request->getParam("pre"));
		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "School Types";

		$this->view->strViewMod = $this->_request->getParam("viewmod");

		foreach ($arrAppDetails as $strName => $arrData)
		{
			$arrList[$strName]["value"] = $strName;
			$arrList[$strName]["text"] = $arrData['name'];
		}
		ksort($arrList);
		$this->view->arrList = $arrList;
	}

	public function classesAction()
	{
		$objClasses = new Classes();
		$objInstitutions = new Institutions();
		$objRoles = new Roles();
		$arrParams = $this->_request->getParams();

		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$objInstitution)
		{
			print "Sorry, there was an error: CM-C101-8DFDSS";
			exit;
		}

		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "[|Classes|]";
		$this->view->strViewMod = $this->_request->getParam("viewmod");
		$this->view->boolNone = $this->_request->getParam("none") == "all" ? 0 : 1;

		$arrSql = array();
		if ($objRoles->isRole("Teacher"))
		{
			/*
			$arrTeacherClasses = array_hash("class_id", $objClasses->_user_classes_select(array(
				"class_role" => "Teacher",
				"user_id" => $this->_user_session_data->user_id
			)));
			*/
			$arrTeacherClasses = array_hash("class_id", $objClasses->_classes_select(array(
				'class_id'	=>	$this->_user_session_data->class_id
			)));
			if (!count($arrTeacherClasses))
			{
				$this->view->arrList = array();
				return;
			}
			$arrSql["class_id"] = array_keys($arrTeacherClasses);
		}
		//$arrSql["institution_id"] = $this->_user_session_data->institution_id;
		$arrClasses = $objClasses->_classes_select($arrSql);

		$arrList = array();

		foreach ($arrClasses as $objClass)
		{
			$strKey = preg_replace("/^pre(?:\-?school)? *([0-9a-z]+)/i", "0\1", $objClass->class_grade)  ." ". $objClass->class_sub;
			$arrList[$strKey]["value"] = $objClass->class_id;
			$arrList[$strKey]["text"] = $objClass->class_grade  ." ". $objClass->class_sub;
		}
		//ksort($arrList);
		$this->view->arrList = $arrList;
		$this->view->arrParams = $arrParams;
	}

	public function usersAction()
	{
		$objUsers = new Users();
		$query = new QueryGen();
		$objRoles = new Roles();

		$this->view->strTitle = $strTitle = trim($this->_request->getParam("title"));
		$this->view->strZero = trim($this->_request->getParam("zero"));
		$this->view->strHiddenInputName = trim($this->_request->getParam("inputname"));
		$this->view->strContainer = trim($this->_request->getParam("container"));
		$arrColumns = array();
		$arrColumnNames = array();

		if (!$strTitle)
		{
			$strTitlePrefix = trim($this->_request->getParam("pre"));
			$this->view->strTitle = $strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Accounts";
		}

		$arrParams = $this->_request->getParams();

		$arrSql = array();
		$arrSql["institution_id"] = $this->_user_session_data->institution_id;

		if (
			isset($arrParams["auth_card_institution_id"])
			&& $objRoles->isAllowed("Super Administrator")
		) {
			$arrAuthCardUserParams = array(
				"institution_id" => $arrParams["auth_card_institution_id"]
			);
			if (isset($arrParams["auth_card_status_not"]))
				$arrAuthCardUserParams["_NOT"]["card_status"] = $arrParams["auth_card_status_not"];
			if (isset($arrParams["auth_card_status"]))
				$arrAuthCardUserParams["card_status"] = explode(",", $arrParams["auth_card_status"]);
			$arrAuthCardUsers = object_extract("user_id", $query->auth_cards__select($arrAuthCardUserParams));
			$arrAllUsers = array_hash("user_id", $query->users__select(array(
				"user_id" => $arrAuthCardUsers
			)));
		}

		if (!isset($arrAllUsers))
		{
			// Define the permissions applicable
			if (isset($arrParams["permission"]))
			{
				$arrMultiPermissions = explode(",", $arrParams["permission"]);
				if (count($arrMultiPermissions) > 1)
				{
					$arrSql["permission"] = array();
					foreach ($arrMultiPermissions as $strMultiPermission)
					{
						if (isset($this->arrUserTypes[$arrParams["permission"]]))
							$arrSql["permission"][] = $this->arrUserTypes[$arrParams["permission"]];
					}
				}
				else if (isset($this->arrUserTypes[$arrParams["permission"]]))
				{
					$arrSql["permission"] = $this->arrUserTypes[$arrParams["permission"]];
				}
			}
			if (isset($arrParams["class_id"]))
				$arrSql["class_id"] = $arrParams["class_id"];
			else if ($objRoles->isAllowed("Teacher")) {
				$arrTeacherClassIds = array_stack('class_id', $query->user_classes__select(array(
					'user_id' => $this->_user_session_data->user_id
				)));
				$arrTeacherClasses = array_stack('class_id', $query->classes__select(array(
					'class_id' => $arrTeacherClassIds,
					'institution_id' => $this->_user_session_data->institution_id
				)));
				$arrSql["class_id"] = $arrTeacherClasses;
			}
			if (isset($arrParams["is_active"]))
				$arrSql["is_active"] = $arrParams["is_active"];
			$arrAllUsers = array_hash("user_id", $objUsers->_users_select_hierarchal($arrSql));
		}

		if (isset($arrParams["cols"]))
		{
			$arrColData = explode(";", $this->_request->getParam("colnames"));
			$arrColumnsParam = array_fill_keys(explode(",", $arrParams["cols"]), 1);

			if (isset($arrColumnsParam["has_photo"]))
			{
				$intRow = 0;
				foreach ($arrAllUsers as $intUser => $objUser)
				{
					$arrColumns[$intRow][] = empty($objUser->image_id) ? 'No' : 'Yes';
					$intRow++;
				}
			}
			if (isset($arrColumnsParam["auth_card_card_status"]))
			{
				$arrAuthCardUsers = array_hash("user_id", $query->auth_cards__select(array(
					"user_id" => array_keys($arrAllUsers)
				)));
				$intRow = 0;
				foreach ($arrAllUsers as $intUser => $objUser)
				{
					$arrColumns[$intRow][] = isset($arrAuthCardUsers[$intUser]) ? $arrAuthCardUsers[$intUser]->card_status : "not printed";
					$intRow++;
				}
			}
			// Build the column name and col params
			if (isset($arrColumns[0]))
			{
				foreach ($arrColumns[0] as $intCol => $strColData)
				{
					$arrColParams = explode(":", $arrColData[$intCol]);
					$arrColumnNames[$intCol] = array(
						"name" => $arrColParams[0],
						"width" => $arrColParams[1]
					);
				}
			}
		}

		$arrList = array();
		$intItr = 0;
		foreach ($arrAllUsers as $intUser => $objUser)
		{
			$arrList[$objUser->last_name . " " . $objUser->first_name . " " . $intItr]["value"] = $objUser->user_id;
			$arrList[$objUser->last_name . " " . $objUser->first_name . " " . $intItr]["text"] = $objUser->first_name . " " . $objUser->last_name . ($objUser->user_serial ? " (" . $objUser->user_serial . ")" : "");
			$arrList[$objUser->last_name . " " . $objUser->first_name . " " . $intItr]['columns'] = @$arrColumns[$intItr];
			$intItr++;
		}
		ksort($arrList);
		$this->view->arrList = $arrList;
		$this->view->arrColumns = $arrColumns;
		$this->view->arrColumnNames = $arrColumnNames;
	}

	public function prizesAction()
	{
		$query = new QueryGen();
		$objRoles = new Roles();
		$objStore = new Store();
		$objPoints = new Points();

		$arrGet = $this->_request->getParams();

		if (!isset($arrGet["user_id"]))
		{
			print "Sorry, there was an error: CCL-P101-GJH321";
			exit;
		}

		$this->view->strTitle = $strTitle = trim($this->_request->getParam("title"));
		$this->view->strInputPath = trim($this->_request->getParam("input_path"));

		$this->view->objUser = first($query->users__select(array(
			"user_id" => $arrGet["user_id"]
		)));
		$this->view->intUserPointsTotal = $objPoints->user_store(array(
			"user_id" => $arrGet["user_id"],
			"institution_id" => $this->_user_session_data->institution_id
		));

		$arrColumns = array();
		$arrColumnNames = array();

		if (!$strTitle)
		{
			$strTitlePrefix = trim($this->_request->getParam("pre"));
			$this->view->strTitle = $strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Accounts";
		}


		$arrParams = $this->_request->getParams();

		$arrPrizes = $objStore->user_available_prizes(array(
			"user_id" => $arrGet["user_id"]
		));
		$arrPrizeSizes = array_bubble_hash("prize_id", $query->prize_sizes__select(array(
			"prize_id" => array_keys(array_stack("prize_id", $arrPrizes))
		)));
		// of the prizes available which are one_per_user
		$arrOnePerUserPrizes = array_bubble_hash("one_per_user", $arrPrizes);
		$arrOnePerUserPrizes = array_keys(array_stack("prize_id", $arrOnePerUserPrizes[1]));
		// load the users prizes which are one_per_user
		$arrOnePerUserUserPrizes = array_stack("prize_id", $query->user_prizes__select(array(
			"user_id" => $arrGet["user_id"],
			"institution_id" => $this->_user_session_data->institution_id,
			"prize_id" => $arrOnePerUserPrizes
		)));


		$arrList = array();
		foreach ($arrPrizes as $intItr => $objPrize)
		{
			$arrList[$objPrize->prize_name . " " . $intItr]["value"] = $objPrize->prize_id;
			$arrList[$objPrize->prize_name . " " . $intItr]["text"] = $objPrize->prize_name;
			$arrList[$objPrize->prize_name . " " . $intItr]["objPrize"] = $objPrize;
			$arrList[$objPrize->prize_name . " " . $intItr]["arrPrizeSizes"] = @$arrPrizeSizes[$objPrize->prize_id];
			$arrList[$objPrize->prize_name . " " . $intItr]["boolUserHasOnePerUserPrize"] = isset($arrOnePerUserUserPrizes[$objPrize->prize_id]);
		}
		ksort($arrList);
		$this->view->arrList = $arrList;
	}
}
?>