<?php

class Points2Controller extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission;

	function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (!$this->_user_session_data->institution_id) {
			$this->_redirect('logout');
		}
		/*
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

	public function classtasksprintAction()
	{
		$this->view->tstyle = $this->_request->getParam('tstyle');
		$query = new QueryGen();
		$role = new Roles();
		$objConfig = new Config();
		$objClasses = new Classes();
		$arrParams = $this->_request->getParams();
		$arrPost = $this->view->arrPost = $this->_request->getPost();
		if (!count($arrPost)) {
			$arrPost = array(
				"headers" => 1,
				"names_on_wrapped_pages" => 1,
				"page_columns" => 12,
				"page_rows" => 18
			);
			$this->view->arrPost = $arrPost;
		}
		$arrConfigParams = array(
			"set" => "admin",
			"key" => array(
				"pointsclasstasks",
				"classtasksclasses",
				"classtasksettings",
				"classtaskcolumnorder"
			),
			"user_id" => $this->_user_session_data->user_id,
			"institution_id" => $this->_user_session_data->institution_id,
			'_NOHOST' => 1
		);
		$this->view->boolAjax = isset($arrParams["boolAjax"]);
		$this->view->arrConfig = $arrConfig = $objConfig->load($arrConfigParams);
		$arrUserClassParams = array(
			'class_role' => 'Student'
		);
		$arrClassesParams = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if ($role->isRole("Teacher"))
		{
			$arrTeacherClasses = array_hash("class_id", $query->user_classes__select(array(
				"user_id" => $this->_user_session_data->user_id,
				"user_role" => "Teacher"
			)));
			$arrClassesParams["class_id"] = $arrUserClassParams["class_id"] = array_keys(array_stack('class_id', $arrTeacherClasses));
		}
		$arrClasses = $this->view->arrClasses = array_hash('class_id', $objClasses->_classes_select($arrClassesParams));
		if (!isset($arrUserClassParams["class_id"]))
			$arrUserClassParams["class_id"] = array_keys($arrClasses);
		if (isset($arrConfig["admin"]["classtasksclasses"]))
		{
			parse_str($arrConfig["admin"]["classtasksclasses"], $arrClassHash);
			if (isset($arrClassesParams["class_id"]))
			{
				foreach ($arrClassHash as $intClass => $boolOn)
				{
					// check if there is a setting saved for a class that the teach doesnt have access to
					if (!isset($arrClasses[$intClass]) || !in_array($intClass, $arrClassesParams["class_id"]))
						unset($arrClassHash[$intClass]);
				}
			}
			if (count($arrClassHash))
				$arrUserClassParams["class_id"] = array_keys($arrClassHash);
		}
		$this->view->arrUserClasses = $arrUserClasses = array_bubble_hash('user_id', $query->user_classes__select($arrUserClassParams));

		$arrUserIds = array_keys(array_stack("user_id", $arrUserClasses));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			"user_id" => $arrUserIds,
			'is_active' => 1,
			array(
				"_IN" => array(
					"_TABLE" => "permissions",
					"_DEPENDENT" => "user_id",
					"_INDEPENDENT" => "user_id",
					'_GREATER' => array(
						"registration_expiration" => time()
					)
				)
			)
		));

		$arrCampaigns = array_hash("campaign_id", $query->campaigns__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			'is_active' => 1
		)));
		$arrTaskParams = array(
			"campaign_id" => array_keys(array_stack("campaign_id", $arrCampaigns)),
			'is_active' => 1
		);
		$this->view->arrTasks = $arrTasks = array_hash("task_id", $query->tasks__select($arrTaskParams));
		$arrTasksHash = array_hash("campaign_id", "task_id", $arrTasks);
		$strUserPointMonth = date("m");
		$strUserPointDay = date("d");
		$strUserPointYear = date("Y");
		if ($this->_request->getParam("class_tasks_date"))
		{
			$strInputDate = urldecode($this->_request->getParam("class_tasks_date"));
			if (preg_match("/([0-9]+) *\/ *([0-9]+) *\/ *([0-9]+)/", $strInputDate, $arrMatched))
			{
				list($strDateValue, $strUserPointMonth, $strUserPointDay, $strUserPointYear) = $arrMatched;
			}
		}
		$intStartPoint = mktime(0, 0, 0, $strUserPointMonth, $strUserPointDay, $strUserPointYear);
		$boolIsToday = $intStartPoint == mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$intEndPoint = strtotime("+1 day", $intStartPoint);
		$this->view->arrUserPoints = $query->user_points__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"created_by" => $this->_user_session_data->user_id,
			"user_id" => $arrUserIds,
			"resource_name" => "direct_transfer",
			"_GREATER" => array(
				"_TIMESTAMP" => array(
					"created" => $intStartPoint-1
				)
			),
			"_LESSER" => array(
				"_TIMESTAMP" => array(
					"created" => $intEndPoint+1
				)
			),
			//"_VERBOSE" => 2
		));
		$this->view->arrUserPointsHash = $arrUserPointsHash = array_hash("user_id", "task_id", $this->view->arrUserPoints);

		$arrRows = array();
		$arrUnasignedRows = array();
		$arrTaskOrder = array();
		if (isset($arrConfig["admin"]["classtaskcolumnorder"]))
		{
			$arrTaskOrder = array_flip(explode(",", $arrConfig["admin"]["classtaskcolumnorder"]));
		}
		$intItr = 0;
		foreach ($arrCampaigns as $intCampaign => $objCampaign)
		{
			if (!isset($arrTasksHash[$intCampaign]))
				continue;
			foreach ($arrTasksHash[$intCampaign] as $intTask => $objTask)
			{
				if (isset($arrTaskOrder[$intTask])) {
					$strKey = $arrTaskOrder[$intTask];
					$arrRows["a" . $strKey] = array(
						"objCampaign" => $objCampaign,
						"objTask" => $objTask
					);
					$intItr++;
				}
			}
			foreach ($arrTasksHash[$intCampaign] as $intTask => $objTask)
			{
				$strKey = "_" . $intItr;
				if (!isset($arrTaskOrder[$intTask])) {
					$arrRows["a" . $strKey] = array(
						"objCampaign" => $objCampaign,
						"objTask" => $objTask
					);
					$intItr++;
				}
			}
		}
		uksort($arrRows, 'strnatcasecmp');
		//dumper($arrRows,1,1);
		$this->view->arrRows = $arrRows;

		$arrResults = array();
		$arrSortIndex = array();
		foreach ($arrUsers as $objUser) {
			$objClass = NULL;
			if (isset($arrUserClasses[$objUser->user_id]))
			{
				$objUserClass = reset($arrUserClasses[$objUser->user_id]);
				if (isset($arrClasses[$objUserClass->class_id]))
					$objClass = $arrClasses[$objUserClass->class_id];
			}
			$arrUserTaskPoints = array();
			if (isset($arrUserPointsHash[$objUser->user_id]))
				$arrUserTaskPoints = $arrUserPointsHash[$objUser->user_id];
			$arrResults[$objUser->user_id] = array(
				'objUser' => $objUser,
				'objClass' => $objClass,
				'arrUserTaskPoints' => $arrUserTaskPoints
			);
			$strClassKey = $objClass ? $objClass->class_hierarchy : '_';
			$arrSortIndex[$strClassKey][$objUser->last_name . ':' . $objUser->first_name] = $objUser->user_id;
		}
		uksort($arrSortIndex, 'strnatcasecmp');
		foreach ($arrSortIndex as $strKey => $arrSortItem)
		{
			uksort($arrSortIndex[$strKey], 'strnatcasecmp');
		}
		$arrSortIndex = array_flatten2($arrSortIndex);
		$arrOutResults = array();
		foreach ($arrSortIndex as $intUser) {
			$arrOutResults[] = $arrResults[$intUser];
		}
		$this->view->arrOutResults = $arrOutResults;
	}

	public function classtasksAction()
	{
		$this->view->tstyle = $this->_request->getParam('tstyle');
		$query = new QueryGen();
		$role = new Roles();
		$objConfig = new Config();
		$objClasses = new Classes();
		$objTasks = new Tasks();
		$arrParams = $this->_request->getParams();

		$arrConfigParams = array(
			"set" => "admin",
			"key" => array(
				"pointsclasstasks",
				"classtasksclasses",
				"classtasksettings",
				"classtaskcolumnorder"
			),
			"user_id" => $this->_user_session_data->user_id,
			"institution_id" => $this->_user_session_data->institution_id,
			'_NOHOST' => 1
		);
		$this->view->boolAjax = isset($arrParams["boolAjax"]);
		$this->view->arrConfig = $arrConfig = $objConfig->load($arrConfigParams);
		$arrUserClassParams = array(
			'class_role' => 'Student'
		);
		$arrClassesParams = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if ($role->isRole("Teacher"))
		{
			$arrTeacherClasses = array_hash("class_id", $query->user_classes__select(array(
				"user_id" => $this->_user_session_data->user_id,
				"user_role" => "Teacher"
			)));
			$arrClassesParams["class_id"] = $arrUserClassParams["class_id"] = array_keys(array_stack('class_id', $arrTeacherClasses));
		}
		$arrClasses = $this->view->arrClasses = array_hash('class_id', $objClasses->_classes_select($arrClassesParams));
		if (!isset($arrUserClassParams["class_id"]))
			$arrUserClassParams["class_id"] = array_keys($arrClasses);
		if (isset($arrConfig["admin"]["classtasksclasses"]))
		{
			parse_str($arrConfig["admin"]["classtasksclasses"], $arrClassHash);
			if (isset($arrClassesParams["class_id"]))
			{
				foreach ($arrClassHash as $intClass => $boolOn)
				{
					// check if there is a setting saved for a class that the teach doesnt have access to
					if (!isset($arrClasses[$intClass]) || !in_array($intClass, $arrClassesParams["class_id"]))
						unset($arrClassHash[$intClass]);
				}
			}
			if (count($arrClassHash))
				$arrUserClassParams["class_id"] = array_keys($arrClassHash);
		}
		$this->view->arrUserClasses = $arrUserClasses = array_bubble_hash('user_id', $query->user_classes__select($arrUserClassParams));

		$arrUserIds = array_keys(array_stack("user_id", $arrUserClasses));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			//"user_id" => 25924,//$arrUserIds,
			"user_id" => $arrUserIds,
			'is_active' => 1,
			array(
				"_IN" => array(
					"_TABLE" => "permissions",
					"_DEPENDENT" => "user_id",
					"_INDEPENDENT" => "user_id",
					'_GREATER' => array(
						"registration_expiration" => time()
					)
				)
			)
		));

		$arrCampaigns = array_hash("campaign_id", $query->campaigns__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			'is_active' => 1
		)));
		$arrTaskParams = array(
			"campaign_id" => array_keys(array_stack("campaign_id", $arrCampaigns)),
			'is_active' => 1,
			'is_grid' => 1
		);
		$this->view->arrTasks = $arrTasks = array_hash("task_id", $objTasks->task_filter_date_ranges($query->tasks__select($arrTaskParams)));
		$arrTasksHash = array_hash("campaign_id", "task_id", $arrTasks);
		$strUserPointMonth = date("m");
		$strUserPointDay = date("d");
		$strUserPointYear = date("Y");
		if ($this->_request->getParam("class_tasks_date"))
		{
			$strInputDate = urldecode($this->_request->getParam("class_tasks_date"));
			if (preg_match("/([0-9]+) *\/ *([0-9]+) *\/ *([0-9]+)/", $strInputDate, $arrMatched))
			{
				list($strDateValue, $strUserPointMonth, $strUserPointDay, $strUserPointYear) = $arrMatched;
			}
		}
		$intStartPoint = mktime(0, 0, 0, $strUserPointMonth, $strUserPointDay, $strUserPointYear);
		$boolIsToday = $intStartPoint == mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$intEndPoint = strtotime("+1 day", $intStartPoint);
		$this->view->arrUserPoints = $query->user_points__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"created_by" => $this->_user_session_data->user_id,
			"user_id" => $arrUserIds,
			"resource_name" => "direct_transfer",
			"_GREATER" => array(
				"_TIMESTAMP" => array(
					"created" => $intStartPoint-1
				)
			),
			"_LESSER" => array(
				"_TIMESTAMP" => array(
					"created" => $intEndPoint+1
				)
			),
			//"_VERBOSE" => 2
		));
		$this->view->arrUserPointsHash = $arrUserPointsHash = array_hash("user_id", "task_id", $this->view->arrUserPoints);

		$arrRows = array();
		$arrUnasignedRows = array();
		$arrTaskOrder = array();
		if (isset($arrConfig["admin"]["classtaskcolumnorder"]))
		{
			$arrTaskOrder = array_flip(explode(",", $arrConfig["admin"]["classtaskcolumnorder"]));
		}
		$intItr = 0;
		foreach ($arrCampaigns as $intCampaign => $objCampaign)
		{
			if (!isset($arrTasksHash[$intCampaign]))
				continue;
			foreach ($arrTasksHash[$intCampaign] as $intTask => $objTask)
			{
				if (isset($arrTaskOrder[$intTask])) {
					$strKey = $arrTaskOrder[$intTask];
					$arrRows["a" . $strKey] = array(
						"objCampaign" => $objCampaign,
						"objTask" => $objTask
					);
					$intItr++;
				}
			}
			foreach ($arrTasksHash[$intCampaign] as $intTask => $objTask)
			{
				$strKey = "_" . $intItr;
				if (!isset($arrTaskOrder[$intTask])) {
					$arrRows["a" . $strKey] = array(
						"objCampaign" => $objCampaign,
						"objTask" => $objTask
					);
					$intItr++;
				}
			}
		}
		uksort($arrRows, 'strnatcasecmp');
		//dumper($arrRows,1,1);
		$this->view->arrRows = $arrRows;

		$arrResults = array();
		$arrSortIndex = array();
		foreach ($arrUsers as $objUser) {
			$objClass = NULL;
			if (isset($arrUserClasses[$objUser->user_id]))
			{
				$objUserClass = reset($arrUserClasses[$objUser->user_id]);
				if (isset($arrClasses[$objUserClass->class_id]))
					$objClass = $arrClasses[$objUserClass->class_id];
			}
			$arrUserTaskPoints = array();
			if (isset($arrUserPointsHash[$objUser->user_id]))
				$arrUserTaskPoints = $arrUserPointsHash[$objUser->user_id];
			$arrResults[$objUser->user_id] = array(
				'objUser' => $objUser,
				'objClass' => $objClass,
				'arrUserTaskPoints' => $arrUserTaskPoints
			);
			$strClassKey = $objClass ? $objClass->class_hierarchy : '_';
			$arrSortIndex[$strClassKey][$objUser->last_name . ':' . $objUser->first_name] = $objUser->user_id;
		}
		uksort($arrSortIndex, 'strnatcasecmp');
		foreach ($arrSortIndex as $strKey => $arrSortItem)
		{
			uksort($arrSortIndex[$strKey], 'strnatcasecmp');
		}
		$arrSortIndex = array_flatten2($arrSortIndex);
		$arrOutResults = array();
		foreach ($arrSortIndex as $intUser) {
			$arrOutResults[] = $arrResults[$intUser];
		}
		$this->view->arrOutResults = $arrOutResults;

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (isset($arrPost["save_all"]))
			{
				$arrFormData = json_decode($arrPost["arrFormData"]);
				$arrUserCount = array();
				$intPointCount = 0;
				//dumper($arrUserPointsHash,0,1);
				$intQueryCount = 0;
				foreach ($arrFormData as $arrInput)
				{
					$arrInput = (array) $arrInput;
					// parse results from the post
					if (!empty($arrInput["name"]) && preg_match("/^points_([0-9]+)_([0-9]+)$/", $arrInput["name"], $arrMatched))
					{
						list($strInputName, $intTask, $intUser) = $arrMatched;
						$objTask = $arrTasks[$intTask];
						if (
							@$arrInput["value"] > 0
							|| (
								isset($arrInput["value"])
								&& isset($arrUserPointsHash[$intUser][$intTask])
								&& $arrUserPointsHash[$intUser][$intTask]->points != $arrInput["value"]
							)
							|| (
								$objTask->is_checkbox == 1
								&& (
									!isset($arrUserPointsHash[$intUser][$intTask])
									|| $arrUserPointsHash[$intUser][$intTask]->points != $arrInput["value"]
								)
							)
						) {

							if (isset($arrUserPointsHash[$intUser][$intTask]))
							{
								if (
									(
										$objTask->is_checkbox == 0
										&& intval($arrInput["value"]) == 0
									) || (
										$objTask->is_checkbox == 1
										&& $arrInput["value"] == ''
									)
								) {
									$query->user_points__delete(array(
										"user_point_id" => $arrUserPointsHash[$intUser][$intTask]->user_point_id
									));
									$intQueryCount++;
								}
								else
								{
									$arrUserCount[$intUser] = 1;
									$query->user_points__update(array(
										"where" => array(
											"user_point_id" => $arrUserPointsHash[$intUser][$intTask]->user_point_id
										),
										"values" => array(
											"points" => $arrInput["value"]
										)
									));
									$intQueryCount++;
								}
							}
							else if (!empty($arrInput["value"]))
							{
								$arrUserCount[$intUser] = 1;
								$arrUserPointsInsert = array(
									"user_id" => $intUser,
									"campaign_id" => $arrTasks[$intTask]->campaign_id,
									"mission_id" => $arrTasks[$intTask]->mission_id,
									"task_id" => $arrTasks[$intTask]->task_id,
									"institution_id" => $this->_user_session_data->institution_id,
									"points" => $arrInput["value"],
									"resource_name" => "direct_transfer"//,
									/*"description" => $arrPost["strDescription"]*/
								);
								if (!$boolIsToday)
									$arrUserPointsInsert["created"] = date("Y-m-d H:i:s", $intStartPoint);
								$query->user_points__insert($arrUserPointsInsert);
								$intQueryCount++;
							}
						}
					}
				}
				//print "intQueryCount : " . $intQueryCount;
				//exit;
				print json_encode(array(
					"success" => "true",
					"intUserCount" => count($arrUserCount)
				));
				exit;
			}
			else if (isset($arrPost["campaign_tasks"]))
			{
				$arrNewConfigParams = array(
					"set" => "admin",
					"key" => "pointsclasstasks",
					"user_id" => $this->_user_session_data->user_id,
					"institution_id" => $this->_user_session_data->institution_id
				);
				$arrConfigResult = array();
				$arrConfigResult["admin"]["pointsclasstasks"] = $arrPost["campaign_tasks"];
				$objConfig->save($arrConfigResult, $arrNewConfigParams);
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
			else if (isset($arrPost["classes"]))
			{
				$arrNewConfigParams = array(
					"set" => "admin",
					"key" => "classtasksclasses",
					"user_id" => $this->_user_session_data->user_id,
					"institution_id" => $this->_user_session_data->institution_id
				);
				$arrConfigResult = array();
				$arrConfigResult["admin"]["classtasksclasses"] = $arrPost["classes"];
				$objConfig->save($arrConfigResult, $arrNewConfigParams);
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
			else if (isset($arrPost["classtasksettings"]))
			{
				$arrNewConfigParams = array(
					"set" => "admin",
					"key" => "classtasksettings",
					"user_id" => $this->_user_session_data->user_id,
					"institution_id" => $this->_user_session_data->institution_id
				);
				$arrConfigResult = array();
				$arrConfigResult["admin"]["classtasksettings"] = $arrPost["classtasksettings"];
				$objConfig->save($arrConfigResult, $arrNewConfigParams);
				print json_encode(array(
					"success" => "true"
				));

				exit;
			}
			else if (isset($arrPost["classtaskcolumnorder"]))
			{
				$arrPost["classtaskcolumnorder"] = unserialize($arrPost["classtaskcolumnorder"]);
				$arrNewConfigParams = array(
					"set" => "admin",
					"key" => "classtaskcolumnorder",
					"user_id" => $this->_user_session_data->user_id,
					"institution_id" => $this->_user_session_data->institution_id
				);
				$arrConfigResult = array();
				natcasesort($arrPost["classtaskcolumnorder"]);
				$arrConfigResult["admin"]["classtaskcolumnorder"] = join(",",array_keys($arrPost["classtaskcolumnorder"]));
				$objConfig->save($arrConfigResult, $arrNewConfigParams);
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
		}
	}

	public function pointscampaignsAction()
	{
		$objPoints = new Points();

		$this->view->institution_id = $this->_request->getParam('institution_id');
		//$this->view->class_id = $this->_request->getParam('class_id');
		$this->view->class_id = $this->_request->getParam('class_id');

		$intClass = $this->view->intClass = $this->_request->getParam("class_id");

		$objCampaigns = new Campaigns();
		$arrCampaigns = $objCampaigns->campaigns_select_institution($this->_request->getParam("institution_id"));
		$this->view->arrCampaigns = $arrCampaigns;

		$objUsers = new Users();
		$arrUsers = $objUsers->users_student_select_class($intClass);
		$this->view->arrUsers = $arrUsers;


		//get points for users so we can populate grid with existing data if any
		//insert new data into database


		if ($this->_request->isPost()){

			$created_by = $this->_user_session_data->user_id;
			$institution_id = $this->_request->getParam("institution_id");
			$class_id = $this->_request->getParam('class_id');

			//print_r($_POST); exit;

			for($i=0; $i <= $this->_request->getParam('arr_size'); $i++){

				if($this->_request->getParam('points_'.$i)=='' || !is_numeric($this->_request->getParam('points_'.$i))) continue;

				$user_id = $this->_request->getParam('user_id_'.$i);
				$campaign_id = $this->_request->getParam('campaign_id_'.$i);
				$points = $this->_request->getParam('points_'.$i);



				try{
					$result = $objPoints->points_insert($user_id, $class_id, $institution_id, $campaign_id, $points, $created_by);
				}
				catch(Zend_Exception $e){
					echo "There was a problem inserting this item";
					exit;
				}
			}

			echo $this->_request->getParam('arr_size');
			exit;
		}

	}
	public function pointsviewtotalAction()
	{
		$objPoints = new Points();

		$this->view->class_id = $this->_request->getParam('class_id');
		$this->view->mode = $this->_request->getParam('mode');

		$intClass = $this->view->intClass = $this->_request->getParam("class_id");

		$objCampaigns = new Campaigns();
		$arrCampaigns = $objCampaigns->campaigns_select_institution($this->_request->getParam('institution_id'));
		$this->view->arrCampaigns = $arrCampaigns;

		$objUsers = new Users();
		$arrUsers = $objUsers->users_student_select_class($intClass);
		$this->view->arrUsers = $arrUsers;

		try{
			$this->view->arrUserData = $objPoints->pointsuserdata($arrUsers, $arrCampaigns);
		}
		catch(Zend_Exception $e){
			echo "There was an error: CP-PVT101-AKIE85";
		}
		//exit;
	}
}
?>
