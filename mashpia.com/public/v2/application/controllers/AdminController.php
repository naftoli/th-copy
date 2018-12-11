<?php
class AdminController extends Zend_Controller_Action
{
	function preDispatch()
	{

		$arrNoSecurity = array("index", "login", "missionappcards", "missionappcardsqr");
		$boolFailOut = FALSE;
		if (!in_array($this->_request->action, $arrNoSecurity))
		{
			$arrGet = $this->_request->getParams();
			if (empty($arrGet["uid"]))
			{
				$boolFailOut = TRUE;
			}
			else
			{
				$cook = new Zend_Session_Namespace('system_admin_' . $arrGet["uid"]);
				$this->_cook = $cook;
				if ($cook->success != "true") {
					$boolFailOut = TRUE;
				}
			}
		}
		if ($boolFailOut) {
			header("Location: " . bp . "/admin");
			exit;
		}
		$this->view->uid = $this->_request->getParam("uid");
		
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
	}
	
	public function missionappreportAction()
	{
		$query = new QueryGen();
		$this->view->arrNetowrks = array(
			1 => (object) array(
				"network_id" => 1,
				"network_name" => "My Yum Tov"
			),
			2 => (object) array(
				"network_id" => 2,
				"network_name" => "Custom Missions"
			)
		);
		$this->view->arrMissions = $arrMissions = array_bubble_hash("network_id", $query->ckids_mission_app__select(array(
			"_ALL" => TRUE
		)));
		$this->view->arrHolidays = $arrHolidays = array_bubble_hash("network_id", $query->ckids_mission_app__select(array(
			"_ALL" => TRUE,
			"_GROUP" => "network_id, holiday_name"
		)));
		$this->view->arrBadges = $arrBadges = array_bubble_hash("network_id", $query->ckids_mission_app__select(array(
			"_ALL" => TRUE,
			"_GROUP" => "network_id, badge",
			//"_VERBOSE" => 2
		)));
	}
	
	public function missionappreportoutputAction()
	{
		$query = new QueryGen();
		$strParams = $this->_request->getParam("params");
		$objParams = json_decode(urldecode($strParams));
		
		$boolInclusive = isset($objParams->inclusive_query);
		$boolOrderedCards = isset($objParams->ordered_cards);
		$boolSummaryReport = isset($objParams->summary_report);
		
		$strNetworkIds = $objParams->networks;
		$strHolidays = $objParams->holidays;
		$strBadges = $objParams->badges;
		$strOperator = $boolInclusive ? " AND " : " OR ";
		
		$arrPermissionParams = array(
			"permission" => "MissionsApp"
		);
		
		$strMarkingCreatedDate = "";
		if (!empty($objParams->start_date_on) && !empty($objParams->start_date) && parse_date_mdy($objParams->start_date)) {
			$intDate = parse_date_mdy($objParams->start_date);
			$strMarkingCreatedDate = "UNIX_TIMESTAMP(ckids_mission_marking.created) >= " . $intDate;
		}
		if (!empty($objParams->end_date_on) && !empty($objParams->end_date) && parse_date_mdy($objParams->end_date)) {
			if (!empty($strMarkingCreatedDate))
				$strMarkingCreatedDate .= " AND ";
			$intDate = parse_date_mdy($objParams->end_date);
			$strMarkingCreatedDate .= "UNIX_TIMESTAMP(ckids_mission_marking.created) <= " . $intDate;
		}

		$arrHolidayNames = array();
		$arrBadgeNames = array();
		if (
			strlen($strHolidays) > 0
			|| strlen($strBadges) > 0
		) {
			$arrSqlInStatements = array();
			if (strlen($strHolidays) > 0) {
				$arrHolidayNames = explode(",", $strHolidays);
				$arrSqlInStatements[] = "ckids_mission_app.holiday_name IN ('" . join("','", array_clean_sql($arrHolidayNames)) . "')";
			}
			if (strlen($strBadges) > 0) {
				$arrBadgeNames = explode(",", $strBadges);
				$arrSqlInStatements[] = "ckids_mission_app.badge IN ('" . join("','", array_clean_sql($arrBadgeNames)) . "')";
			}
			$strSql = "
				SELECT
					DISTINCT ckids_mission_marking.user_id
				FROM
					" . ($boolOrderedCards ? "ckids_mission_cards," : "") . "
					ckids_mission_marking,
					ckids_mission_app
				WHERE
					(
						" . join ($strOperator, $arrSqlInStatements) . "
					)
					" . ($boolOrderedCards ? "AND ckids_mission_cards.user_id = ckids_mission_marking.user_id" : "") . "
					AND ckids_mission_app.task_id = ckids_mission_marking.task_id
					" . (!empty($strMarkingCreatedDate) ? "AND " : "") . $strMarkingCreatedDate . "
			";
			//dumper($strSql,1,1);
			$arrMarkingUsers = array_stack("user_id", $this->_db->fetchAll($strSql));
			$arrPermissionParams["user_id"] = $arrMarkingUsers;
		}
		else
		{
			
			$arrNetworkIds = explode(",", $strNetworkIds);
			if (strlen($strNetworkIds) == 0)
			{
				
				$strMarkingCreatedDate = "";
				if (!empty($objParams->start_date_on) && !empty($objParams->start_date) && parse_date_mdy($objParams->start_date)) {
					$intDate = parse_date_mdy($objParams->start_date);
					$strMarkingCreatedDate = "UNIX_TIMESTAMP(permissions.created) >= " . $intDate;
				}
				if (!empty($objParams->end_date_on) && !empty($objParams->end_date) && parse_date_mdy($objParams->end_date)) {
					if (!empty($strMarkingCreatedDate))
						$strMarkingCreatedDate .= " AND ";
					$intDate = parse_date_mdy($objParams->end_date);
					$strMarkingCreatedDate .= "UNIX_TIMESTAMP(permissions.created) <= " . $intDate;
				}
				$strSql = "
					SELECT
						permissions.user_id
					FROM
						" . ($boolOrderedCards ? "ckids_mission_cards," : "") . "
						permissions
					WHERE
						permission = 'MissionsApp'
						" . (!empty($strMarkingCreatedDate) ? "AND " : "") . $strMarkingCreatedDate . "
						" . ($boolOrderedCards ? "AND permissions.user_id = ckids_mission_cards.user_id" : "") . "
				";
			}
			else
			{
				$strSql = "
					SELECT
						DISTINCT ckids_mission_marking.user_id
					FROM
						" . ($boolOrderedCards ? "ckids_mission_cards," : "") . "
						ckids_mission_marking
					WHERE
						network_id IN (" . join(",", array_clean_sql($arrNetworkIds)) . ")
						" . (!empty($strMarkingCreatedDate) ? "AND " : "") . $strMarkingCreatedDate . "
						" . ($boolOrderedCards ? "AND ckids_mission_marking.user_id = ckids_mission_cards.user_id" : "") . "
				";
			}
			
			$arrMarkingUsers = array_stack("user_id", $this->_db->fetchAll($strSql));
			$arrPermissionParams["user_id"] = $arrMarkingUsers;
		}
		$arrList = array();
		if ($objParams->report_type == "Users Report") {
			$arrPermissions = $query->permissions__select($arrPermissionParams);
			if (count($arrPermissions)) {
				$arrUserIds = array_stack("user_id", $arrPermissions);
				$arrUsers = array_hash("user_id", $query->users__select(array(
					"user_id" => $arrUserIds
				)));
				$arrUserCards = array_hash("user_id", $query->ckids_mission_cards__select(array(
					"user_id" => $arrUserIds
				)));
				$arrUsersData = array();
				foreach ($arrPermissions as $objPermission)
				{
					if (!isset($arrUsersData[$objPermission->user_id]))
					{
						$arrUsersData[$objPermission->user_id] = (object) array();
					}
					$objData = &$arrUsersData[$objPermission->user_id];
					$objUser = &$arrUsers[$objPermission->user_id];
					$objData->user_id = $objPermission->user_id;
					$objData->registration_location = $objPermission->registration_location;
					$objData->email = $objUser->student_email;
					$objData->serial = $objUser->bar_code;
					$objData->first_name = $objUser->first_name;
					$objData->last_name = $objUser->last_name;
					$objData->dob = $objUser->dob;
					$objData->gender = $objUser->gender;
					$objData->address = $objUser->address;
					$objData->city = $objUser->city;
					$objData->state = $objUser->state;
					$objData->country = $objUser->country;
					$objData->postal = $objUser->postal;
					$objData->phone = $objUser->phone;
					$objData->image_id = $objUser->image_id;
					$objData->created = $objUser->created;
					$objData->card_order_status = isset($arrUserCards[$objPermission->user_id]) ? $arrUserCards[$objPermission->user_id]->order_status : "";
					$arrList[] = $objData;
				}
			}
		} else if ($objParams->report_type == "User Missions Report") {
			$arrPermissions = $query->permissions__select($arrPermissionParams);
			if (count($arrPermissions)) {
				$arrUserIds = array_stack("user_id", $arrPermissions);
				$arrUsers = array_hash("user_id", $query->users__select(array(
					"user_id" => $arrUserIds
				)));
				
				$strSql = "
					SELECT 
						*, 
						COUNT(*) AS count,
						CONCAT(ckids_mission_app.holiday_name, \" - \", ckids_mission_app.description) as task_name
					FROM
						ckids_mission_marking,
						ckids_mission_app
					WHERE
						ckids_mission_marking.user_id IN (" . join(",", array_clean_sql($arrUserIds)) . ")
						AND ckids_mission_marking.task_id = ckids_mission_app.task_id
					GROUP BY
						task_name, ckids_mission_marking.user_id
				";
				//dumper($strSql,1,1);
				$arrMarkingCounts = $this->_db->fetchAll($strSql);
				//dumper($arrMarkingCounts,1,1);
				$arrTasksParams = array(
					"task_id" => array_stack("task_id", $arrMarkingCounts),
					"_GROUP" => "holiday_name, description"
				);
				if (count($arrHolidayNames))
					$arrTasksParams["holiday_name"] = $arrHolidayNames;
				if (count($arrBadgeNames))
					$arrTasksParams["badge"] = $arrBadgeNames;
					
				$arrTasks = array_hash("task_id", $query->ckids_mission_app__select($arrTasksParams));
				//dumper($arrTasksParams,1,1);
				$arrMarkingCountsHash = array_bubble_hash("user_id", "task_name", $arrMarkingCounts);
				$arrList[] = (object) array(
					"user_id" => "Totals",
					"first_name" => "",
					"last_name" => ""
				); // place for header totals
				
				$arrTaskTotals = array();
				foreach ($arrMarkingCountsHash as $intUser => $arrTaskCounts) {
					//dumper($arrTaskCounts,1,1);
					$objData = (object) array();
					$objUser = $arrUsers[$intUser];
					$objData->user_id = $objUser->user_id;
					$objData->first_name = $objUser->first_name;
					$objData->last_name = $objUser->last_name;
					$intTotal = 0;
					foreach ($arrTasks as $objTask)
					{
						$strTaskName = $objTask->holiday_name . " - " . $objTask->description;
						$intCount = 0;
						if (isset($arrTaskCounts[$strTaskName]))
							$intCount = first($arrTaskCounts[$strTaskName])->count;
						$objData->$strTaskName = $intCount;
						$intAmount = $objData->$strTaskName == "" ? 0 : $objData->$strTaskName;
						$intTotal += $intAmount;
						if (!isset($arrTaskTotals[$strTaskName]))
							$arrTaskTotals[$strTaskName] = 0;
						$arrTaskTotals[$strTaskName] += $intAmount;
						
					
					}
					$objData->Total = $intTotal;
					$arrList[] = $objData;
				}
				$intSum = 0;
				foreach ($arrTaskTotals as $strName => $intAmount)
				{
					$arrList[0]->$strName = $intAmount;
					$intSum += $intAmount;
				}
				//$objData->Total = $intSum;
				$arrList[0]->Totals = $intSum;
			}
		}
		// output
		if (TRUE)
		{
			if ($this->_request->getParam("download") == "true") {
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=MissionsApp_Export_" . date ("M_d_o_g_i_s_a") . ".csv");
			}
			
			header("Pragma: no-cache");
			header("Expires: 0");
			$arrLines = array();
			if (!empty($arrList))
			{
				$arrKeys = array_keys((array) first($arrList));
				$arrLines[] = '"' . join('","', $arrKeys) . '"';
				foreach ($arrList as $objItem)
				{
					$arrLines[] = '"' . join('","', (array) $objItem) . '"';
				}
			}
			else
			{
				print "Nothing found";
				exit;
			}
			if ($this->_request->getParam("download") == "true") {
				print join("\n", $arrLines);
			} else {
				print "<button onclick='window.location.href=\"" . getenv('REQUEST_URI') . "/download/true\"' style='padding:3px;margin:3px;border:1px solid darkblue;margin-bottom:10px'>Download CSV</button>";
				print "&nbsp;&nbsp;" . (count($arrLines) < 2 ? "0" : count($arrLines)-2) . " rows";
				print array_to_table($arrList, "width:100px;");
			}
			exit;
		}
		$this->view->arrUsersData = array_slice($arrList, 0, 100);
	}
	
	public function missioncardsAction()
	{
		$query = new QueryGen();
		$this->view->arrNetworks = $query->ckids_mission_networks__select(array(
			"_ALL" => TRUE
		));
	}
	
	public function missionappcardsqrAction()
	{
		$query = new QueryGen();
		$intNetwork = $this->_request->getParam("network_id");
		$arrTaskIds = array_stack("task_id", $query->ckids_mission_app__select(array(
			"_COLUMNS" => array("task_id"),
			"network_id" => $intNetwork
		)));
		if (!$arrTaskIds)
			exit;
		$objCards = $query->achievement_cards__select(array(
			"task_id" => $arrTaskIds,
			"card_type" => "MissionsApp",
			"_ORDER" => "task_id + 0"
		));
		$this->view->arrBarcodes = $objCards;
	}
	
	public function missionappcardsAction()
	{
		$query = new QueryGen();
		$intNetwork = $this->_request->getParam("network_id");
		$arrTaskIds = array_stack("task_id", $query->ckids_mission_app__select(array(
			"_COLUMNS" => array("task_id"),
			"network_id" => $intNetwork
		)));
		if (!$arrTaskIds)
			exit;
		$objCards = $query->achievement_cards__select(array(
			"task_id" => $arrTaskIds,
			"card_type" => "MissionsApp",
			"_ORDER" => "task_id + 0"
		));
		$this->view->arrBarcodes = $objCards;
	}

	public function idcardsselectAction()
	{
		

	}

	public function markprintedAction()
	{
		$query = new QueryGen();
		$arrPost = $this->_request->getPost();
		$arrUserIds = explode(",", $arrPost["user_ids"]);
		if (!count($arrUserIds))
		{
			json(array(
				"error" => "Unexpected error: CA-MP101-fjn23f"
			));
		}
		$arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			"user_id" => $arrUserIds,
			"institution_id" => 601
		)));
		foreach ($arrUserIds as $intUser)
		{
			if (!isset($arrAuthCards[$intUser]))
			{
				$query->auth_cards__insert(array(
					"user_id" => $intUser,
					"date_printed" => time(),
					"institution_id" => 601
				));
			}
		}
		if (count($arrAuthCards))
		{
			$query->auth_cards__update(array(
				"where" => array(
					"user_id" => array_keys($arrAuthCards),
					"institution_id" => 601
				),
				"values" => array(
					"date_printed" => time()
				)
			));
		}
		json(array(
			"success" => "true"
		));
	}

	public function usersselectAction()
	{

		$objUsers = new Users();
		$objClasses = new Classes();
		$objRoles = new Roles();
		$query = new QueryGen();

		$intInstitution = 601;

		// Configure list
		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Accounts";

		$arrParams = $this->_request->getParams();
		$this->view->is_active = $this->_request->getParam('is_active');
		$this->view->is_registered = $this->_request->getParam('is_registered');
		$this->view->class_id = $this->_request->getParam('class_id');
		$this->view->permission = $this->_request->getParam('permission');
		$this->view->url = urlencode($this->_request->getParam('url'));
		$this->view->pt = $this->_request->getParam("pt");
		$arrPost = $this->_request->getPost();
		$this->view->arrPost = $arrPost;
		$arrSql = array();

		$arrColumnOrder = array('first_name', 'last_name', 'ordered', 'registered');

		if (isset($arrParams["is_active"]))
			$arrSql["is_active"] = $arrParams["is_active"];
		$arrSql[0]["_IN"] = array(
			"_TABLE" => "permissions",
			"_DEPENDENT" => "user_id",
			"_INDEPENDENT" => "user_id",
			'institution_id' => $intInstitution,
			"_NOT" => array(
				"registration_location" => "v2dev1.mashpia.com"
			)
		);
		$arrSql[1]["_IN"] = array(
			"_TABLE" => "ckids_mission_cards",
			"_DEPENDENT" => "user_id",
			"_INDEPENDENT" => "user_id",
			'order_status' => "ordered"
		);
		//$arrSql["_VERBOSE"] = 2;

		// Select the users
		//print $arrSql;
		$arrUsers = $query->users__select($arrSql);


		// Query additional information about users
		$arrUsersIds = array_keys(array_stack('user_id', $arrUsers));
		$arrUserClasses = $query->user_classes__select(array(
			'user_id' => $arrUsersIds
		));
		$arrUserClassesHash = array_bubble_hash('user_id', $arrUserClasses);
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => array_keys(array_stack('class_id', $arrUserClasses))
		)));
		$arrUserOrders = array_hash("user_id", $query->auth_cards__select(array(
			"_COLUMNS" => array("user_id", "date_printed"),
			"institution_id" => $intInstitution,
			"user_id" => $arrUsersIds
		)));

		// Build the list of data
		$arrList = array();
		$boolAllPossible = $this->_request->getParam("all") == "true";
		foreach ($arrUsers as $intItr => $objUser)
		{
			$strNewParam = (preg_match("/\/$/", $strForwardTo) ? "" : "/") .  "user_id/" . $objUser->user_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);
			$intIndex = count($arrList);
			$arrList[$intIndex] = array(
				'intOriginalIndex' => $intIndex,
				'arrParams' => array('user_id' => $objUser->user_id),
				'first_name' => $objUser->first_name,
				'last_name' => $objUser->last_name,
				'image_id' => $objUser->image_id,
				'registered' => date("Y/m/d", datetime_to_timestamp($objUser->created)),
				"ordered" => isset($arrUserOrders[$objUser->user_id]) ? date("Y/m/d", $arrUserOrders[$objUser->user_id]->date_printed) : "Never"
			);
		}

		// Filter
		if (isset($arrPost['strSearchFilter']))
		{
			$strSearchFilter = $arrPost['strSearchFilter'];
			$arrExactMatches = array();
			while (preg_match('/"([^"]+)"/', $strSearchFilter, $arrMatched)) {
				$strSearchFilter = preg_replace('/"' . preg_quote($arrMatched[1], '/') . '"/', '', $strSearchFilter);
				$arrExactMatches[] = $arrMatched[1];
			}
			$strSearchFilter = preg_replace('/"/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/^ +/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/ +$/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/^ +$/', '', $strSearchFilter);
			$strSearchFilter = preg_quote($strSearchFilter, '/');
			$arrSearchFilter = array();
			if (!empty($strSearchFilter))
				$arrSearchFilter = preg_split('/ +/', $strSearchFilter);
			foreach ($arrExactMatches as $strExactMatch)
			{
				if (!empty($strExactMatch))
					array_unshift($arrSearchFilter, $strExactMatch);
			}
			//if (!empty($arrSearchFilter))
			//	dumper(array($arrExactMatches,$arrSearchFilter),1,1);
			if (count($arrSearchFilter))
			{
				foreach ($arrList as $intKey => $arrRow)
				{
					$intMatchFound = 0;
					foreach ($arrColumnOrder as $strColumn)
					{
						$strCellData = $arrRow[$strColumn];
						foreach ($arrSearchFilter as $strKeyword)
						{
							if (preg_match('/' . $strKeyword . '/i', $strCellData))
								$intMatchFound++;
						}
					}
					if ($intMatchFound < count($arrSearchFilter))
						unset($arrList[$intKey]);
				}
			}
		}

		// Sort the data
		$arrSortParams = array();
		if (isset($arrPost['strSortOrder']))
		{
			$arrSortOrder = json_decode($arrPost['strSortOrder']);
			foreach ($arrSortOrder as $intItr => $strOrder)
			{
				$strColumn = $arrColumnOrder[$intItr];
				array_push($arrSortParams, $strColumn);
				if ($strOrder == 'desc')
					array_push($arrSortParams, SORT_ASC);
				else if ($strOrder == 'asc')
					array_push($arrSortParams, SORT_DESC);
				array_push($arrSortParams, SORT_STRING);
			}
			//dumper($arrSortParams,1,1);
		}
		array_push($arrSortParams, $arrList);
		$arrList = call_user_func_array("msort", $arrSortParams);

		// Paging
		$this->view->intTotalItems = $intTotalItems = count($arrList);
		$this->view->intItemsPerPage = $intItemsPerPage = 16;
		$this->view->intTotalPages = $intTotalPages = floor($intTotalItems / $intItemsPerPage)+1;
		$this->view->intPage = $intPage = isset($arrPost["page"]) ? $arrPost["page"] : 0;
		if ($intPage+1 > $intTotalPages)
			$intPage = $intTotalPages;
		$this->view->intFirstItem = $intFirstItem = $intPage * $intItemsPerPage;

		// Output
		$arrList = array_slice($arrList, $intFirstItem, $intItemsPerPage);
		$this->view->intPageCount = count($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function deleteuserAction()
	{
		$query = new QueryGen();
		$intUser = $this->view->user_id = $this->_request->getParam("user_id");
		$objPermission = first($query->permissions__select(array(
			"user_id" => $intUser,
			"permission" => "MissionsApp"
		)));
		if ($objPermission)
		{
			$query->users__delete(array(
				"user_id" => $intUser
			));
			$query->permissions__delete(array(
				"user_id" => $intUser
			));
			$query->permissions__delete(array(
				"user_id" => $intUser
			));
			$query->ckids_mission_marking__delete(array(
				"user_id" => $intUser
			));
			$query->ckids_mission_cards__delete(array(
				"user_id" => $intUser
			));
		}
		json(array(
			"success" => "true"
		));
	}

	public function userAction()
	{
		$intUser = $this->view->user_id = $this->_request->getParam("user_id");

		$query = new QueryGen();
		$this->view->objUser = $objUser = first($query->users__select(array(
			"user_id" => $intUser
		)));
		if (!$objUser)
		{
			json(array(
				"error" => "Unexpected error: CA-UA101-kn2fnj-" . $intUser
			));
		}
		$this->view->objPermission = $objPermission = first($query->permissions__select(array(
			"user_id" => $intUser,
			"permission" => "MissionsApp"
		)));
		if (!$objPermission)
		{
			json(array(
				"error" => "Unexpected error: CA-UA102-nf2k2b-" . $intUser
			));
		}


		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getPost();
			if (!isset($arrParams["first_name"]) || !strlen($arrParams["first_name"]))
			{
				$arrResult["error"]["first_name"] = "First name is a required field.";
			}
			if (!isset($arrParams["last_name"]) || !strlen($arrParams["last_name"]))
			{
				$arrResult["error"]["last_name"] = "Last name is a required field.";
			}
			if (!isset($arrResult["error"]))
			{
				$query->users__update(array(
					"where" => array(
						"user_id" => $intUser
					),
					"values" => $arrParams
				));
				json(array(
					"success" => "true"
				));
			}

			json(array(
				"error" => "Unexpected error: CA-UA103-nrjkn2-" . $intUser
			));
		}
	}

	public function bulkimageuploaderAction()
	{
	}

	public function usersmenuAction()
	{

		$objUsers = new Users();
		$objClasses = new Classes();
		$objRoles = new Roles();
		$query = new QueryGen();

		$intInstitution = 601;

		// Configure list
		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Accounts";

		$arrParams = $this->_request->getParams();
		$this->view->is_active = $this->_request->getParam('is_active');
		$this->view->is_registered = $this->_request->getParam('is_registered');
		$this->view->class_id = $this->_request->getParam('class_id');
		$this->view->permission = $this->_request->getParam('permission');
		$this->view->url = urlencode($this->_request->getParam('url'));
		$this->view->pt = $this->_request->getParam("pt");
		$arrPost = $this->_request->getPost();
		$this->view->arrPost = $arrPost;
		$arrSql = array();

		$arrColumnOrder = array('first_name', 'last_name', 'class_list');

		if (isset($arrParams["is_active"]))
			$arrSql["is_active"] = $arrParams["is_active"];
		$arrSql[0]["_IN"] = array(
			"_TABLE" => "permissions",
			"_DEPENDENT" => "user_id",
			"_INDEPENDENT" => "user_id",
			'institution_id' => $intInstitution,
			"_NOT" => array(
				"registration_location" => "v2dev1.mashpia.com"
			)
		);
		if (isset($arrParams["is_registered"]))
			$arrSql[0]['_IN']['_GREATER']['registration_expiration'] = time();
		if (isset($arrParams["class_id"]))
			$arrSql[1]["_IN"] = array(
				"_TABLE" => "user_classes",
				"_DEPENDENT" => "user_id",
				"_INDEPENDENT" => "user_id",
				'class_id' => $arrParams["class_id"]
			);
		// Select the users
		$arrUsers = $query->users__select($arrSql);


		// Query additional information about users
		$arrUserClasses = $query->user_classes__select(array(
			'user_id' => array_keys(array_stack('user_id', $arrUsers))
		));
		$arrUserClassesHash = array_bubble_hash('user_id', $arrUserClasses);
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => array_keys(array_stack('class_id', $arrUserClasses))
		)));

		// Build the list of data
		$arrList = array();
		$boolAllPossible = $this->_request->getParam("all") == "true";
		foreach ($arrUsers as $intItr => $objUser)
		{
			$strNewParam = (preg_match("/\/$/", $strForwardTo) ? "" : "/") .  "user_id/" . $objUser->user_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);
			$intIndex = count($arrList);
			$arrList[$intIndex] = array(
				'intOriginalIndex' => $intIndex,
				'strForwardTo' => "/" . $strForwardTo . $strNewParam,
				'arrParams' => array('user_id' => $objUser->user_id),
				'first_name' => $objUser->first_name,
				'last_name' => $objUser->last_name
			);
			// Build class list
			$arrClassNames = array();
			$arrCurrentUserClasses = isset($arrUserClassesHash[$objUser->user_id]) ? $arrUserClassesHash[$objUser->user_id] : array();
			foreach ($arrCurrentUserClasses as $objUserClass)
			{
				if (isset($arrClasses[$objUserClass->class_id]))
				{
					$objClass = $arrClasses[$objUserClass->class_id];
					$arrClassNames[] = $objClass->custom_name1;
				}
			}
			$arrList[$intIndex]['class_list'] = join(', ', $arrClassNames);
		}

		// Filter
		if (isset($arrPost['strSearchFilter']))
		{
			$strSearchFilter = $arrPost['strSearchFilter'];
			$arrExactMatches = array();
			while (preg_match('/"([^"]+)"/', $strSearchFilter, $arrMatched)) {
				$strSearchFilter = preg_replace('/"' . preg_quote($arrMatched[1], '/') . '"/', '', $strSearchFilter);
				$arrExactMatches[] = $arrMatched[1];
			}
			$strSearchFilter = preg_replace('/"/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/^ +/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/ +$/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/^ +$/', '', $strSearchFilter);
			$strSearchFilter = preg_quote($strSearchFilter, '/');
			$arrSearchFilter = array();
			if (!empty($strSearchFilter))
				$arrSearchFilter = preg_split('/ +/', $strSearchFilter);
			foreach ($arrExactMatches as $strExactMatch)
			{
				if (!empty($strExactMatch))
					array_unshift($arrSearchFilter, $strExactMatch);
			}
			//if (!empty($arrSearchFilter))
			//	dumper(array($arrExactMatches,$arrSearchFilter),1,1);
			if (count($arrSearchFilter))
			{
				foreach ($arrList as $intKey => $arrRow)
				{
					$intMatchFound = 0;
					foreach ($arrColumnOrder as $strColumn)
					{
						$strCellData = $arrRow[$strColumn];
						foreach ($arrSearchFilter as $strKeyword)
						{
							if (preg_match('/' . $strKeyword . '/i', $strCellData))
								$intMatchFound++;
						}
					}
					if ($intMatchFound < count($arrSearchFilter))
						unset($arrList[$intKey]);
				}
			}
		}

		// Sort the data
		$arrSortParams = array();
		if (isset($arrPost['strSortOrder']))
		{
			$arrSortOrder = json_decode($arrPost['strSortOrder']);
			foreach ($arrSortOrder as $intItr => $strOrder)
			{
				$strColumn = $arrColumnOrder[$intItr];
				array_push($arrSortParams, $strColumn);
				if ($strOrder == 'desc')
					array_push($arrSortParams, SORT_ASC);
				else if ($strOrder == 'asc')
					array_push($arrSortParams, SORT_DESC);
				array_push($arrSortParams, SORT_STRING);
			}
			//dumper($arrSortParams,1,1);
		}
		array_push($arrSortParams, $arrList);
		$arrList = call_user_func_array("msort", $arrSortParams);

		// Paging
		$this->view->intTotalItems = $intTotalItems = count($arrList);
		$this->view->intItemsPerPage = $intItemsPerPage = 16;
		$this->view->intTotalPages = $intTotalPages = floor($intTotalItems / $intItemsPerPage)+1;
		$this->view->intPage = $intPage = isset($arrPost["page"]) ? $arrPost["page"] : 0;
		if ($intPage+1 > $intTotalPages)
			$intPage = $intTotalPages;
		$this->view->intFirstItem = $intFirstItem = $intPage * $intItemsPerPage;

		// Output
		$arrList = array_slice($arrList, $intFirstItem, $intItemsPerPage);
		$this->view->intPageCount = count($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function indexAction()
	{

	}

	public function frontAction()
	{
		$this->view->account_name = $cook->account_name;
	}

	public function logoutAction()
	{
		$arrGet = $this->_request->getParams();
		if (isset($arrGet["uid"]))
		{
			if (isset($_SESSION['system_admin_' . $arrGet["uid"]]))
				unset($_SESSION['system_admin_' . $arrGet["uid"]]);
		}
		header("Location: " . bp . "/admin");
		exit;
	}

	public function loginAction()
	{
		$query = new QueryGen();
		$arrPost = $this->_request->getPost();
		$arrOutput = array();
		// accounts username -> user_id
		$arrAccounts = array(
			"missionsapp" => "33104", //j06th0wNo0n6 //78813c7bd85509ec558d1bc0a9568836
			"andyware@gmail.com" => "13426"
		);
		$arrAccountNames = array(
			"missionsapp" => "MissionsApp",
			"andyware@gmail.com" => "MissionsApp"
		);

		if (empty($arrPost["username"]))
		{
			$arrOutput["error"] = "Username is required.";
		}
		else if (empty($arrPost["password"]))
		{
			$arrOutput["error"] = "Password is required.";
		}
		else if (!isset($arrAccounts[$arrPost["username"]]))
		{
			$arrOutput["error"] = "Username or password is incorrect.";
		}
		else
		{
			$objUser = first($query->users__select(array(
				"user_id" => $arrAccounts[$arrPost["username"]],
				"password" => md5($arrPost["password"])
			)));
			if (!$objUser)
			{
				// password was wrong
				$arrOutput["error"] = "Username or password is incorrect.";
			}
			else
			{
				$cook = new Zend_Session_Namespace('system_admin_' . $objUser->user_id);
				$cook->success = "true";
				$cook->username = $arrPost["username"];
				$cook->account_name = $arrAccountNames[$arrPost["username"]];
				$arrOutput["success"] = "true";
				$arrOutput["id"] = $objUser->user_id;
			}
		}
		json($arrOutput);
	}

	public function dashboardAction ()
	{
		$this->view->account_name = $this->_cook->account_name;
		$query = new QueryGen();
		$this->view->objUser = first($query->users__select(array(
			"user_id" => $this->view->uid
		)));
	}

	public function idcardsprintcampsAction()
	{
		$query = new QueryGen();
		$arrPost = $this->_request->getPost();
		if (!isset($arrPost["user_ids"]))
		{
			print "Sorry, there was an error" . ": CU-ICP101-SD0F9D";
			exit;
		}
		$arrIds = (array) json_decode(stripslashes($arrPost["user_ids"]));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			"user_id" => array_keys($arrIds)
		));
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => 601
		)));
	}

	public function idcardshostprintcampsAction()
	{
		$query = new QueryGen();

		$this->view->intInstitution = $intInstitution = 601;
		$this->view->strCustomeInstitutionName = "Missions";
		$this->view->arrUserIds = $arrUserIds = explode(",", $this->_request->getPost("user_ids"));

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
		$this->view->arrUsers = array_hash("user_id", $query->users__select(array(
			"user_id" => $arrUserIds //array_keys($arrAuthCards)
		)));
	}
	
	public function idcardshostprintcampsqrAction()
	{
		$query = new QueryGen();

		$this->view->intInstitution = $intInstitution = 601;
		$this->view->strCustomeInstitutionName = "Missions";
		$this->view->arrUserIds = $arrUserIds = explode(",", $this->_request->getPost("user_ids"));

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
		$this->view->arrUsers = array_hash("user_id", $query->users__select(array(
			"user_id" => $arrUserIds //array_keys($arrAuthCards)
		)));
	}
	
	public function missionsAction()
	{
		$query = new QueryGen();
		$intNetwork = $this->_request->getParam("network_id");
		$this->view->arrNetworks = $arrNetworks = array_hash("network_id", $query->ckids_mission_networks__select(array(
			"_ALL" => TRUE
		)));
		$objCurrentNetwork = empty($intNetwork) ? first($arrNetworks) : $arrNetworks[$intNetwork];
		$this->view->objCurrentNetwork = $objCurrentNetwork;
		$arrMissions = $query->ckids_mission_app__select(array(
			"network_id" => $objCurrentNetwork->network_id
		));
		$this->view->arrMissions = $arrMissions; 
	}
	
	public function missiondeleteAction()
	{
		$query = new QueryGen();
		$arrGet = $this->_request->getParams();
		$intMission = $this->_request->getParam("mission_id");
		if (empty($intMission)) 
		{
			json(array(
				"failure" => "There was an error: CA-MD101-11nfnf"
			));
		}
		$query->ckids_mission_app__delete(array(
			"task_id" => $intMission
		));
		$query->ckids_mission_marking__delete(array(
			"task_id" => $intMission
		));
		$query->achievement_cards__delete(array(
			"institution_id" => 601,
			"task_id" => $intMission,
			"card_type" => "MissionsApp"
		));
		json(array(
			"success" => "true"
		));
	}
	
	public function missioneditAction()
	{
		$query = new QueryGen();
		$arrGet = $this->_request->getParams();
		$this->view->mission_id = $intMission = $this->_request->getParam("mission_id");
		$intNetwork = $this->_request->getParam("network_id");
		$objMission = FALSE;
		$this->view->arrNetworks = $arrNetworks = array_hash("network_id", $query->ckids_mission_networks__select(array(
			"_ALL" => TRUE
		)));
		if (!empty($intMission))
		{
			$objMission = first($query->ckids_mission_app__select(array(
				"task_id" => $intMission
			)));
			if (!$objMission) {
				print "Error: This mission is not available";
				exit;
			}
			$objAchievement = first($query->achievement_cards__select(array(
				"institution_id" => 601,
				"task_id" => $objMission->task_id,
				"card_type" => "MissionsApp"
			)));
			$objMission->bar_code = $objAchievement->card_serial; 
			
		}
		if (!empty($intNetwork)) {
			$this->view->objCurrentNetwork = $arrNetworks[$intNetwork];
		} else {
			$this->view->objCurrentNetwork = $objMission ? $arrNetworks[$objMission->network_id] : first($arrNetworks);
		}
		
		$this->view->objMission = $objMission;
		
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (empty($arrPost["network"]))
			{
				print json_encode(array(
					"error" => "There was an error: CA-ME101-jvdslm"
				));
				exit;
			}
			if (!isset($arrNetworks[$arrPost["network"]]))
			{
				print json_encode(array(
					"error" => "There was an error: CA-ME102-mg4i3o"
				));
				exit;
			}
			if (empty($arrPost["mission_image_id"])) {
				print json_encode(array(
					"error" => array(
						"mission_image_id" => "You must provide a image" 
					)
				));
				exit;
			}
			if (empty($arrPost["holiday_name"])) {
				print json_encode(array(
					"error" => array(
						"holiday_name" => "You must provide a holiday name" 
					)
				));
				exit;
			}
			if (empty($arrPost["description"])) {
				print json_encode(array(
					"error" => array(
						"description" => "You must provide a description" 
					)
				));
				exit;
			}
			if (empty($arrPost["badge"])) {
				print json_encode(array(
					"error" => array(
						"badge" => "You must provide a badge name" 
					)
				));
				exit;
			}
			if (empty($arrPost["date_label"])) {
				print json_encode(array(
					"error" => array(
						"date_label" => "You must provide a date label" 
					)
				));
				exit;
			}
			if (empty($arrPost["start_date"])) {
				print json_encode(array(
					"error" => array(
						"start_date" => "You must provide a start date" 
					)
				));
				exit;
			}
			if (!preg_match('/^ *([0-9]{1,2}) *\/ *([0-9]{1,2}) *\/ *([0-9]{4}) *$/', $arrPost["start_date"], $arrStartDateMatched)) {
				print json_encode(array(
					"error" => array(
						"start_date" => "The start date is not properly formatted" 
					)
				));
				exit;
			}
			if (empty($arrPost["end_date"])) {
				print json_encode(array(
					"error" => array(
						"end_date" => "You must provide an end date" 
					)
				));
				exit;
			}
			if (!preg_match('/^ *([0-9]{1,2}) *\/ *([0-9]{1,2}) *\/ *([0-9]{4}) *$/', $arrPost["end_date"], $arrEndDateMatched)) {
				print json_encode(array(
					"error" => array(
						"end_date" => "The end date is not properly formatted" 
					)
				));
				exit;
			}
			if (empty($arrPost["start_time"])) {
				print json_encode(array(
					"error" => array(
						"start_time" => "You must provide an start time" 
					)
				));
				exit;
			}
			if (!preg_match('/^ *([0-9]{1,2}) *: *([0-9]{2}) *(am|pm)? *$/i', $arrPost["start_time"], $arrStartTimeMatched)) {
				print json_encode(array(
					"error" => array(
						"start_time" => "The start time is not properly formatted" 
					)
				));
				exit;
			}
			if (empty($arrPost["end_time"])) {
				print json_encode(array(
					"error" => array(
						"end_time" => "You must provide an end time" 
					)
				));
				exit;
			}
			if (!preg_match('/^ *([0-9]{1,2}) *: *([0-9]{2}) *(am|pm)? *$/i', $arrPost["end_time"], $arrEndTimeMatched)) {
				print json_encode(array(
					"error" => array(
						"end_time" => "The end time is not properly formatted" 
					)
				));
				exit;
			}
			if (empty($arrPost["access_level"]) || !in_array($arrPost["access_level"], array(1,2))) {
				print json_encode(array(
					"error" => "There was an error: CA-ME101X-12nfnf3"
				));
				exit;
			}
			$objCurrentNetwork = $arrNetworks[$arrPost["network"]];
			
			if ($objCurrentNetwork->network_id == 1) {
				$arrPost["access_level"] = 1;
			}
			$intStartDate = mktime(
				$arrStartTimeMatched[1]+(isset($arrStartTimeMatched[3]) && strtolower($arrStartTimeMatched[3]) == "pm" ? 12:0),
				 $arrStartTimeMatched[2],
				 0,
				 $arrStartDateMatched[1],
				 $arrStartDateMatched[2],
				 $arrStartDateMatched[3]);
			$intEndDate = mktime(
				$arrEndTimeMatched[1]+(isset($arrEndTimeMatched[3]) && strtolower($arrEndTimeMatched[3]) == "pm" ? 12:0),
				 $arrEndTimeMatched[2],
				 0,
				 $arrEndDateMatched[1],
				 $arrEndDateMatched[2],
				 $arrEndDateMatched[3]);
			
			$arrParams = array(
				"network_id" => $objCurrentNetwork->network_id,
				"holiday_name" => $arrPost["holiday_name"],
				"description" => $arrPost["description"],
				"access_level" => $arrPost["access_level"],
				"badge" => $arrPost["badge"],
				"date_label" => $arrPost["date_label"],
				"start_date" => $intStartDate,
				"end_date" => $intEndDate,
				"image_id" => $arrPost["mission_image_id"]
			);
			if (isset($arrGet["mission_id"])) // edit
			{
				$query->ckids_mission_app__update(array(
					"where" => array(
						"task_id" => $arrGet["mission_id"]
					),
					"values" => $arrParams
				));
				$query->achievement_cards__update(array(
					"where" => array(
						"task_id" => $arrGet["mission_id"],
						"card_type" => "MissionsApp",
					),
					"values" => array(
						"campaign_image_id" => $arrPost["mission_image_id"],
						"achievement" => $arrPost["holiday_name"] . " /  " . $arrPost["description"]
					)
				));
				json(array(
					"success" => "true"
				));
			} 
			else
			{ // add
				do {
					$intBarcode = "5" . rand_num_string(19);
					$objAchievementCard = first($query->achievement_cards__select(array(
						"card_serial" => $intBarcode
					)));
				}
				while ($objAchievementCard);
				
				$intNewMission = $query->ckids_mission_app__insert($arrParams);
				$query->achievement_cards__insert(array(
					"institution_id" => 601,
					"task_id" => $intNewMission,
					"card_serial" => $intBarcode,
					"card_type" => "MissionsApp",
					"campaign_image_id" => $arrPost["mission_image_id"],
					"achievement" => $arrPost["holiday_name"] . " /  " . $arrPost["description"]
				));
				json(array(
					"success" => "true",
					"mission_id" => $intNewMission,
					"new" => "true"
				));
			}
		}
		
	}
}
?>