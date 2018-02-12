<?php

class PointsController extends Zend_Controller_Action
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
		$taskType = $this->view->taskType = $this->_request->getParam('tasktype');
		$role = new Roles();
		$objConfig = new Config();
		$objClasses = new Classes();
		$objTasks = new Tasks();
		
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
				"pointsclass" . $taskType . "tasks",
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
			$arrTeacherClasses = array_hash("class_id", $objClasses->_classes_select(array(
				"class_id" => array($this->_user_session_data->class_id)
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
		/*
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
		*/
		$objUsers = new Users();
		$this->view->arrUserClasses = $arrUserClasses = array_bubble_hash('user_id', $objClasses->getMashpiaUsers($arrUserClassParams["class_id"]));
		$this->view->arrUsers = $arrUsers = $objUsers->getClassUsers($arrUserClassParams["class_id"]);
		$arrUserIds = array_keys(array_stack("user_id", $arrUsers));
		/*
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
		*/
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
		$this->view->taskdate = $intStartPoint;
		
		$objCampaigns = new Campaigns();
		$arrCampaigns = array_hash("subject_id", $objCampaigns->_campaigns_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		
		$grid = array();
		if (isset($arrConfig["admin"]["pointsclass{$taskType}tasks"]))
		{
			$arrInfo = explode('&', $arrConfig["admin"]["pointsclass{$taskType}tasks"]);
			foreach ($arrInfo as $strTasks) {
				$arrTasks = explode('=', $strTasks);
				$grid[] = $arrTasks[0];
			}
		}
		//dumper($grid,1,1);
		
		$day = date('w', $intStartPoint);
		$start = unixtojd($intStartPoint);
		$markDate = $start;
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
		if ($start < 2457641) {
			$start = 2457641;
			$markDate = 2457641;
		}
		$end = $start + 6;
		
		$this->view->arrTasks = $arrTasks = array_hash("grid_id", $objTasks->getMashpiaTasks($taskType, $arrCampaigns, $start, $end, true));
		$arrTasksHash = array_hash("subject_id", "grid_id", $arrTasks);
		//dumper($arrTasks,1,1);
		// remove all campaign/tasks not selected by admin to show on points grid
		foreach ($arrCampaigns as $intCampaign => $objCampaign)
		{
			if (isset($arrTasksHash[$intCampaign])) {
				foreach ($arrTasksHash[$intCampaign] as $intTask => $objTask)
				{
					if (!in_array($intTask, $grid)) {
						unset($arrTasksHash[$intCampaign][$intTask]);
					}
				}
			}
		}
		//dumper($arrTasksHash,1,1);
		$this->view->arrUserPoints = $arrUserPoints = $objTasks->getMashpiaMarks($arrTasksHash, $arrUserIds, $start, $markDate);
		$this->view->arrUserPointsHash = $arrUserPointsHash = array_hash("user_id", "grid_id", $arrUserPoints);
		
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
			//$strClassKey = $objClass ? $objClass->class_hierarchy : '_';
			$strClassKey = $objClass ? $objClass->class_grade . (empty($objClass->class_sub) ? '' : '-' . $objClass->class_sub) : '_';
			$arrSortIndex[$strClassKey][$objUser->last . ':' . $objUser->first] = $objUser->user_id;
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
		//dumper($arrOutResults,1,1);
	}

	public function classtasksAction()
	{
		$this->view->tstyle = $this->_request->getParam('tstyle');
		$taskType = $this->view->taskType = $this->_request->getParam('tasktype');
		$query = new QueryGen();
		$role = new Roles();
		$objConfig = new Config();
		$objClasses = new Classes();
		$objTasks = new Tasks();
		$arrParams = $this->_request->getParams();
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		// for tehillim tasks make sure there's a setting in the config table
		if ($taskType == 'tehillim') {
			$sql = "select * from config_settings
					where `set` = 'admin'
					and `key` = 'pointsclasstehillimtasks'
					and user_id = " . $this->_user_session_data->user_id . "
					and institution_id = " . $this->_user_session_data->institution_id;
			if (isset($this->_user_session_data->class_id)) {
				$sql .= " and class_id = " . $this->_user_session_data->class_id;
			}
			$stmt = $db->query($sql);
			if ($stmt->rowCount() == 0) {
				$sql = "insert into config_settings
						set `set` = 'admin',
						`key` = 'pointsclasstehillimtasks',
						created = now(),
						val = '8001=1&8002=1', 
						user_id = " . $this->_user_session_data->user_id . ",
						institution_id = " . $this->_user_session_data->institution_id;
				if (isset($this->_user_session_data->class_id)) {
					$sql .= ", class_id = " . $this->_user_session_data->class_id;
				}
				$db->query($sql);
			}
			
			// calculations for sm work somewhat differently in v2 than in mashpia - hence different code
			function calculateSM( $year ) {
				$sm = array(); 
				$day = 29; // last day of month before Rosh Chodesh
				//first get last sm for previous year
				$date = jewishtojd( 13, $day, ($year-1) );
				//$date += 1; //fix issue with jdtounix showing a day off
				$time = jdtounix( $date + 1 );
				$dayOfWeek = date( "w", $time );
				if ($dayOfWeek < 6) $date -= ++$dayOfWeek;
				$shabbosMevorchim = $date; 
				$sm[0] = $shabbosMevorchim;
				for ( $i = 1; $i < 13; $i++ ) {
					$date = jewishtojd( $i, $day, $year );
					//$date += 1; //fix issue with jdtounix showing a day off
					$time = jdtounix( $date + 1 );
					$dayOfWeek = date( "w", $time );
					if ($dayOfWeek < 6) $date -= ++$dayOfWeek;
					$shabbosMevorchim = $date; 
					$sm[$i] = $shabbosMevorchim; //note: if value of index #6 == index #7 then that means that it is NOT a leap year
				}
				return $sm;
			}
			
			// find out which year we are working with
			$sql = "select `val` from mashpiadb.global_settings where `key` = 'current_year'";
			$stmt = $db->query($sql);
			$row = $stmt->fetch();
			$year = $row->val;
			$sm = $this->view->sm = calculateSM( $year );
			//dumper($sm,1,1);
			$this->view->months = array(
				0	=>	'Tishrei',
				1   =>  'Cheshvon', 
				2   =>  'Kisleiv', 
				3   =>  'Teves', 
				4   =>  'Shvat', 
				5   =>  'Adar', 
				6   =>  'Adar II', 
				7   =>  'Nissan',  
				8   =>  'Iyar', 
				9   =>  'Sivan', 
				10  =>  'Tamuz', 
				11  =>  'Av', 
				12  =>  'Elul' 
			);
			// find latest sm
			$current = 0;
			$today = unixtojd();
			foreach ($sm as $month => $date) {
				if ($today < $date) {
					$current = ($month-1);
					break;
				}
			}
			$this->view->current = $current;
			$jdTehillim = $sm[$current];
			//dumper($jdTehillim,1,1);
		}
		
		// make sure that there's a setting in the config table for the classes
		// otherwise it will show latest accessed class with no connection to this school
		if (!$role->isRole("Teacher")) {
			$stmt = $db->query("select * from config_settings
								where `set` = 'admin'
								and `key` = 'classtasksclasses'
								and user_id = " . $this->_user_session_data->user_id);
			if ($stmt->rowCount() == 0) {
				$sql = "insert into config_settings
						set `set` = 'admin',
						`key` = 'classtasksclasses',
						val = '',
						user_id = " . $this->_user_session_data->user_id;
				$db->query($sql);
			}
		}
		
		$arrConfigParams = array(
			"set" => "admin",
			"key" => array(
				"pointsclass" . $taskType . "tasks",
				"classtasksclasses",
				"classtasksettings",
				"classtaskcolumnorder"
			),
			"user_id" => $this->_user_session_data->user_id,
			"institution_id" => $this->_user_session_data->institution_id,
			//'_NOHOST' => 1
		);
		$this->view->boolAjax = isset($arrParams["boolAjax"]);
		$this->view->arrConfig = $arrConfig = $objConfig->load($arrConfigParams);
		//echo "<pre>"; print_r($arrConfigParams); print_r($arrConfig); echo "</pre>"; exit; 
		$arrUserClassParams = array(
			'class_role' => 'Student'
		);
		//dumper($arrConfig,1,1);
		$arrClassesParams = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if ($role->isRole("Teacher"))
		{
			$arrTeacherClasses = array_hash("class_id", $objClasses->_classes_select(array(
				"class_id" => array($this->_user_session_data->class_id)
			)));
			$arrClassesParams["class_id"] = $arrUserClassParams["class_id"] = array_keys(array_stack('class_id', $arrTeacherClasses));
			$this->view->role = "teacher";
		} else {
			$this->view->role = 'Admin';
		}
		$arrClasses = $this->view->arrClasses = array_hash('class_id', $objClasses->_classes_select($arrClassesParams));
		//dumper($arrClasses,1,1);
		if (!isset($arrUserClassParams["class_id"]))
			$arrUserClassParams["class_id"] = array_keys($arrClasses);
		if (isset($arrConfig["admin"]["classtasksclasses"]))
		{
			parse_str($arrConfig["admin"]["classtasksclasses"], $arrClassHash);
			if (isset($arrClassesParams["class_id"]))
			{
				foreach ($arrClassHash as $intClass => $boolOn)
				{
					// check if there is a setting saved for a class that the teacher doesnt have access to
					if (!isset($arrClasses[$intClass]) || !in_array($intClass, $arrClassesParams["class_id"]))
						unset($arrClassHash[$intClass]);
				}
			}
			if (count($arrClassHash))
				$arrUserClassParams["class_id"] = array_keys($arrClassHash);
		}
		/*
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
		*/
		$objUsers = new Users();
		$this->view->arrUserClasses = $arrUserClasses = array_bubble_hash('user_id', $objClasses->getMashpiaUsers($arrUserClassParams["class_id"]));
		$this->view->arrUsers = $arrUsers = $objUsers->getClassUsers($arrUserClassParams["class_id"]);
		$this->view->arrUserIds = $arrUserIds = array_keys(array_stack("user_id", $arrUsers));
		//dumper($arrUserIds, 1, 1);

		/*
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
		*/
		
		$strUserPointMonth = date("m");
		$strUserPointDay = date("d");
		$strUserPointYear = date("Y");
		if ($this->_request->getParam("class_tasks_date"))
		{
			if (is_numeric($this->_request->getParam("class_tasks_date"))) {
				$strInputDate = jdtogregorian($this->_request->getParam("class_tasks_date"));
			} else {
				$strInputDate = urldecode($this->_request->getParam("class_tasks_date"));
			}
			if (preg_match("/([0-9]+) *\/ *([0-9]+) *\/ *([0-9]+)/", $strInputDate, $arrMatched))
			{
				list($strDateValue, $strUserPointMonth, $strUserPointDay, $strUserPointYear) = $arrMatched;
			}
		} else if ($taskType == 'tehillim' && isset($jdTehillim)) {
			// load past tehillim sm
			$strInputDate = jdtogregorian($jdTehillim);
			if (preg_match("/([0-9]+) *\/ *([0-9]+) *\/ *([0-9]+)/", $strInputDate, $arrMatched))
			{
				list($strDateValue, $strUserPointMonth, $strUserPointDay, $strUserPointYear) = $arrMatched;
			}
		}
		$intStartPoint = mktime(0, 0, 0, $strUserPointMonth, $strUserPointDay, $strUserPointYear);
		$boolIsToday = $intStartPoint == mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$intEndPoint = strtotime("+1 day", $intStartPoint);
		$this->view->taskdate = $intStartPoint;
		
		$objCampaigns = new Campaigns();
		$arrCampaigns = array_hash("subject_id", $objCampaigns->_campaigns_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		
		//dumper($arrCampaigns,1,1);
		
		$grid = array();
		if ($taskType == 'tehillim') {
			$grid = array(8001,8002);
		} else {
			if (isset($arrConfig["admin"]["pointsclass{$taskType}tasks"]))
			{
				$arrInfo = explode('&', $arrConfig["admin"]["pointsclass{$taskType}tasks"]);
				foreach ($arrInfo as $strTasks) {
					$arrTasks = explode('=', $strTasks);
					$grid[] = $arrTasks[0];
				}
			} 
		}
		//dumper($grid,1,1);
		//if ($role->isRole("Teacher") && $this->_user_session_data->institution_id == ) dumper($grid,1,1);
		$day = date('w', $intStartPoint);
		$start = unixtojd($intStartPoint);
		$markDate = $start;
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
		if ($start < 2457641) {
			$start = 2457641;
			$markDate = 2457641;
		}
		$end = $start + 6;

		// find out all levels relevant to all users
		$campaigns = array_keys($arrCampaigns);
		//dumper($arrCampaigns,1,1);
		$arrLadders = array_keys(array_hash("level", $objUsers->getLadders($arrUserIds, $campaigns)));
		//dumper($arrLadders,1,1);
		//dumper($start,1,1);
		$this->view->arrTasks = $arrTasks = array_hash("grid_id", $objTasks->getMashpiaTasks($taskType, $arrCampaigns, $start, $end, true, $arrLadders));
		$arrTasksHash = array_hash("subject_id", "grid_id", $arrTasks);
		//dumper($arrTasksHash,1,1);
		// remove all campaign/tasks not selected by admin to show on points grid
		foreach ($arrCampaigns as $intCampaign => $objCampaign)
		{
			if (isset($arrTasksHash[$intCampaign])) {
				foreach ($arrTasksHash[$intCampaign] as $intTask => $objTask)
				{
					if (!in_array($intTask, $grid)) {
						unset($arrTasksHash[$intCampaign][$intTask]);
					}
				}
			}
		} 
		//dumper($arrTasksHash,1,1);
		/*
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
		*/
		$this->view->arrUserPoints = $arrUserPoints = $objTasks->getMashpiaMarks($arrTasksHash, $arrUserIds, $start, $markDate);
		//dumper($arrUserPoints,1,1);
		$this->view->arrUserPointsHash = $arrUserPointsHash = array_hash("user_id", "grid_id", $arrUserPoints);
		//dumper($arrUserPointsHash,1,1);
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
			$strClassKey = $objClass ? $objClass->class_grade . (empty($objClass->class_sub) ? '' : '-' . $objClass->class_sub) : '_';
			$arrSortIndex[$strClassKey][$objUser->last . ':' . $objUser->first] = $objUser->user_id;
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
			$strDate = $this->_request->getPost("dateString");
			$arrPost = $this->_request->getPost();
			if (isset($arrPost["arrFormData"]))
				$arrFormData = json_decode($arrPost["arrFormData"]);
				
			//dumper($arrFormData,1,1);
			if (isset($arrPost["save_all"]))
			{
				$arrUserCount = array();
				$intPointCount = 0;
				//dumper($arrUserPointsHash,0,1);
				$intQueryCount = 0;
				$mashpiaInfo = array();
				//dumper($arrFormData,1,1);
				foreach ($arrFormData as $arrInput)
				{
					$arrInput = (array) $arrInput;
					// parse results from the post
					if (!empty($arrInput["name"]) && preg_match("/^points_([0-9]+)_([0-9]+)_([0-9]+)$/", $arrInput["name"], $arrMatched))
					{
						list($strInputName, $intSubject, $strGrid, $intUser) = $arrMatched;
						$mashpiaInfo[$intUser][$intSubject][] = array(
							'grid_id'	=> $strGrid,
							'value'		=> $arrInput['value'],
							'start'		=> $start,
							'date'		=> $markDate
						);
					}
				}
				//if (isset($mashpiaInfo[19709])) dumper($mashpiaInfo[19709],1,1);
				//dumper($mashpiaInfo,1,1);
				if ($objTasks->markMashpiaTasks($mashpiaInfo)) {
				//print "intQueryCount : " . $intQueryCount;
				//exit;
					print json_encode(array(
						"success" => "true",
						"intUserCount" => count($arrUserCount)
					));
				} else {
					print json_encode(array(
						"success" => "false",
						"intUserCount" => count($arrUserCount)
					));
				}
				exit;
			}
			else if (isset($arrPost["campaign_tasks"]))
			{
				$arrNewConfigParams = array(
					"set" => "admin",
					"key" => "pointsclass{$taskType}tasks",
					"user_id" => $this->_user_session_data->user_id,
					"institution_id" => $this->_user_session_data->institution_id
				);
				$arrConfigResult = array();
				$arrConfigResult["admin"]["pointsclass{$taskType}tasks"] = $arrPost["campaign_tasks"];
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
