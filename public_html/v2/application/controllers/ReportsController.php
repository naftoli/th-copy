<?php
class ReportsController extends Zend_Controller_Action
{
    private $_user_session_data;
	private $_roles;
	private $objPermission;

    function preDispatch()
    {
		$this->_roles = new Roles();
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
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

	public function reportlegacypointsAction()
	{
		$query = new QueryGen();
		$objPoints = new Points();
		if ($this->_request->isPost() || $this->_request->getParam("user_ids"))
		{
			$arrPost = $this->_request->getPost();
			if (!$this->_request->isPost())
			{
				$arrPost = $this->_request->getParams();
				$arrPost["user_ids"] = explode(",", $arrPost["user_ids"]); 
				$arrPost["process"] = "points";
				$arrPost['start_date_on'] = "0";
				$arrPost['end_date_on'] = "0";			
			}
			if ($arrPost['process'] == 'user_ids')
			{
				$arrPermissions = array_stack('user_id', $query->permissions__select(array(
					'institution_id' => $this->_user_session_data->institution_id,
					'permission' => "Student",
					'_GREATER' => array(
						'registration_expiration' => time()
					)
				)));
				print json_encode(array(
					"success" => 'true',
					"user_ids" => array_keys($arrPermissions)
				));
				exit;
			}
			else if ($arrPost['process'] == 'points')
			{
				$intStartDate = $intEndDate = NULL;
				if (
					$arrPost['start_date_on'] == "1"
					&& preg_match('/([0-9]+)\/([0-9]+)\/([0-9]+)/', $arrPost['start_date'], $arrMatched)
				) {
					$intStartDate = mktime(0,0,0,$arrMatched[1],$arrMatched[2],$arrMatched[3]);
				}
				if (
					$arrPost['end_date_on'] == "1"
					&& preg_match('/([0-9]+)\/([0-9]+)\/([0-9]+)/', $arrPost['end_date'], $arrMatched)
				) {
					$intEndDate = mktime(0,0,0,$arrMatched[1],$arrMatched[2],$arrMatched[3]);
				}

				// get new system points
				$arrUsersPointsParams = array(
					'institution_id' => $this->_user_session_data->institution_id,
					'user_id' => $arrPost['user_ids']
				);
				if ($intEndDate)
					$arrUsersPointsParams[]['_ELESSER']['_TIMESTAMP'] = array(
						'created' => $intEndDate
					);
				if ($intStartDate)
					$arrUsersPointsParams[]['_GREATER']['_TIMESTAMP'] = array(
						'created' => $intStartDate
					);
					//$arrUsersPointsParams["_VERBOSE"] = 4;
				$arrUsersPoints = $objPoints->user_points_sums($arrUsersPointsParams);
				//var_dump($arrUsersPoints);
				//dumper($arrUsersPoints,0,1);
				// get legacy system points
				$arrLegacyUsers = array_hash('legacy_id', $query->legacy_lookup__select(array(
					'ims_id' => $arrPost['user_ids'],
					'legacy_table' => 'users',
					'ims_table' => 'users'
				)));
				$objCurl = curl_init();
				$strUrl = "http://mashpia.com/get_points_multi.php";
				curl_setopt($objCurl, CURLOPT_URL, $strUrl);
				curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
				curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1);
				curl_setopt($objCurl, CURLOPT_POST, 1);
				curl_setopt($objCurl, CURLOPT_POSTFIELDS, array(
					'serialized_user_ids' => serialize(array_keys($arrLegacyUsers)),
					'start_date' => $intStartDate,
					'end_date' => $intEndDate
				));
				curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1);
				$strResult = curl_exec($objCurl);
				//dumper($strResult,1,1);
				$arrLegacyPointsSource = unserialize($strResult);
				$arrLegacyPoints = array();
				foreach ($arrLegacyPointsSource as $intLegacy => $intPoints)
				{
					$arrLegacyPoints[$arrLegacyUsers[$intLegacy]->ims_id] = $intPoints;
				}
				
				$arrUserDetails = array();
				$arrUsers = array_hash('user_id', $query->users__select(array(
					'user_id' => $arrPost['user_ids']
				)));
				$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
					'user_id' => array_keys($arrUsers)
				)));
				$arrClassIds = array_stack('class_id', $arrUserClasses);
				$arrClasses = array_hash('class_id', $query->classes__select(array(
					"class_id" => $arrClassIds
				)));
				foreach ($arrPost['user_ids'] as $intUser)
				{
					if (!isset($arrUsers[$intUser]))
						continue;
					$objUser = $arrUsers[$intUser];
					$intClassId = 0;
					if (isset($arrUserClasses[$intUser]))
						$intClassId = $arrUserClasses[$intUser]->class_id;
					$strClassName = "N/A";
					if ($intClassId && isset($arrClasses[$intClassId]))
					{
						$objClass = $arrClasses[$intClassId];
						$strClassName = $objClass->grade;
					}
					$intPoints = 0;
					if (isset($arrUsersPoints[$intUser]))
						$intPoints += $arrUsersPoints[$intUser]['store'];
					if (isset($arrLegacyPoints[$intUser]))
						$intPoints += $arrLegacyPoints[$intUser];
					$arrUserDetails[$intUser] = array(
						'user_id' => $intUser,
						'points' => $intPoints,
						'first_name' => $objUser->first_name,
						'last_name' => $objUser->last_name,
						'class' => $strClassName
					);
				}
				print json_encode(array(
					"success" => 'true',
					"user_details" => $arrUserDetails
				));
				exit;
			}
			if ($arrPost['start_date_on'])
			{

			}
			print json_encode($arrPost);
			exit;
		}

	}

	public function reportoutputAction()
	{
		$query = new QueryGen();
		$objPoints = new Points();
		$objClasses = new Classes();
		$arrGet = $this->_request->getParams();
		// validate, decode and load params
		if (!isset($arrGet['params']))
		{
			print "Sorry, there was an error: CR-RO101-sdg2g2";
			exit;
		}
		$arrParams = json_decode(urldecode($arrGet['params']));
		if (!$arrParams || gettype($arrParams) != 'object')
		{
			print "Sorry, there was an error: CR-RO102-aov9v9";
			exit;
		}
		$this->view->arrParams = $arrParams = (array) $arrParams;
		$boolStoreSubtractions = (!empty($arrParams['store_subtractions']) && $arrParams['store_subtractions'] == '1');
		$arrCampaignIds = array();
		if (!empty($arrParams['campaigns']))
			$arrCampaignIds = explode(',', $arrParams['campaigns']);
		$arrTaskIds = array();
		if (!empty($arrParams['tasks']))
			$arrTaskIds = explode(',', $arrParams['tasks']);
		$arrClassIds = array();
		if (!empty($arrParams['classes']))
			$arrClassIds = explode(',', $arrParams['classes']);
		$arrCampaigns = array();
		if ($arrCampaignIds)
			$this->view->arrCampaigns = $arrCampaigns = array_hash('campaign_id', $query->campaigns__select(array(
				'campaign_id' => $arrCampaignIds,
				'institution_id' => $this->_user_session_data->institution_id
			)));
		$arrTasks = array();
		if (count($arrCampaigns))
			$this->view->arrTasks = $arrTasks = array_hash('task_id', $query->tasks__select(array(
				'campaign_id' => array_keys($arrCampaigns),
				'task_id' => $arrTaskIds
			)));

		// collect users
		$arrUserIds = array();
		$arrClasses = array();
		$arrUserClasses = array();
		if (!empty($arrParams['classes']))
		{
			$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
				'class_id' => $arrClassIds,
				'institution_id' => $this->_user_session_data->institution_id
			)));
			$arrUserClasses = array();
			if (count($arrClasses))
				$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
					'class_id' => array_keys($arrClasses),
					'class_role' => 'Student'
				)));
			$arrUserIds = array_stack('user_id', $arrUserClasses);
		} else {
			$arrPermissions = $query->permissions__select(array(
				'institution_id' => $this->_user_session_data->institution_id,
				'permission' => 'Student'
			));
			$arrUserIds = array_stack('user_id', $arrPermissions);
			$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
				'user_id' => array_keys($arrUserIds)
			)));
			$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
				'class_id' => array_stack('class_id', $arrUserClasses)
			)));
		}
		$arrPermissions = array_stack('user_id', $query->permissions__select(array(
			'user_id' => $arrUserIds,
			'_GREATER' => array(
				'registration_expiration' => time()
			)
		)));
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'user_id' => $arrPermissions,
			'is_active' => 1
		)));
		$arrUserIds = array_keys($arrUsers);
		// build main point params
		$arrUserPointSumsParams = array(
			'user_id' => $arrUserIds
		);
		if (isset($arrParams['start_date_on']) && !empty($arrParams['start_date']))
			$arrUserPointSumsParams['_GREATER']['_TIMESTAMP'] = array(
				'created' => strtotime($arrParams['start_date'])
			);
		if (isset($arrParams['end_date_on']) && !empty($arrParams['end_date']))
			$arrUserPointSumsParams['_LESSER']['_TIMESTAMP'] = array(
				'created' => strtotime($arrParams['end_date'])
			);
		if (
			isset($arrParams['total_points'])
			|| isset($arrParams['store_balance'])
		) {
			$arrUserBalanceSums = $objPoints->user_points_sums($arrUserPointSumsParams);
		}
		$arrCampaignTotals = array();
		if (
			count($arrTasks)
			&& isset($arrParams['campaign_totals'])
		) {
			foreach ($arrUserIds as $intUser)
			{
				foreach ($arrCampaigns as $intCampaign => $objCampaign)
				{
					$arrCampaignTotals[$intUser][$intCampaign] = 0;
				}
			}
			foreach ($arrCampaigns as $intCampaign => $objCampaign)
			{
				$arrUserPointSumsParams2 = $arrUserPointSumsParams;
				$arrUserPointSumsParams2['campaign_id'] = $intCampaign;
				$arrUserPointSums = $objPoints->user_points_sums($arrUserPointSumsParams2);
				foreach ($arrUserPointSums as $intUser => $arrPointItem)
				{
					$arrCampaignTotals[$intUser][$intCampaign] = $arrPointItem['store'];
				}
			}
		}
		//$arrUserPointSumsParams['_VERBOSE'] = 4;
		// calculate points
		$arrRowCampaigns = array();
		$arrRowSums = array();
		if (count($arrTasks))
		{
			foreach ($arrUserIds as $intUser)
			{
				$arrRowSums[$intUser] = 0;
				foreach ($arrTasks as $intTask => $objTask)
				{
					$arrRowCampaigns[$intUser][$intTask] = 0;
				}
			}
			foreach ($arrTasks as $intTask => $objTask)
			{
				$arrUserPointSumsParams2 = $arrUserPointSumsParams;
				$arrUserPointSumsParams2['task_id'] = $intTask;
				$arrUserPointSums = $objPoints->user_points_sums($arrUserPointSumsParams2);
				foreach ($arrUserPointSums as $intUser => $arrPointItem)
				{
					$arrRowCampaigns[$intUser][$intTask] = $boolStoreSubtractions ? $arrPointItem['store'] : $arrPointItem['total'];
					$arrRowSums[$intUser] += $arrPointItem['store'];
				}
			}
		}
		else if (count($arrCampaigns))
		{
			foreach ($arrUserIds as $intUser)
			{
				$arrRowSums[$intUser] = 0;
				foreach ($arrCampaigns as $intCampaign => $objCampaign)
				{
					$arrRowCampaigns[$intUser][$intCampaign] = 0;
				}
			}
			foreach ($arrCampaigns as $intCampaign => $objCampaign)
			{
				$arrUserPointSumsParams2 = $arrUserPointSumsParams;
				$arrUserPointSumsParams2['campaign_id'] = $intCampaign;
				$arrUserPointSums = $objPoints->user_points_sums($arrUserPointSumsParams2);
				foreach ($arrUserPointSums as $intUser => $arrPointItem)
				{
					$arrRowCampaigns[$intUser][$intCampaign] = $arrPointItem['store'];
					$arrRowSums[$intUser] += $arrPointItem['store'];
				}
			}
		}
		else
		{
			$arrUserPointSumsParams2 = $arrUserPointSumsParams;
			$arrUserPointSums = $objPoints->user_points_sums($arrUserPointSumsParams2);
			foreach ($arrUserIds as $intUser)
			{
				$arrRowSums[$intUser] = 0;
			}
			foreach ($arrUserPointSums as $intUser => $arrPointItem)
			{
				$arrRowSums[$intUser] = $boolStoreSubtractions ? $arrPointItem['store'] : $arrPointItem['total'];
			}
		}

		$arrUserPointSumsParams2 = $arrUserPointSumsParams;
		$arrUserPointSumsParams2['resource_name'] = array(
			'admin_users_manual',
			'admin_users_editor',
			'admin_users_manual_total',
			'admin_users_manual_store'
		);
		$arrManualSums = $objPoints->user_points_sums($arrUserPointSumsParams2);
		$arrUserPointSumsParams2 = $arrUserPointSumsParams;
		$arrUserPointSumsParams2['resource_name'] = array(
			'store',
			'kiosk_barcode',
			'transaction_manager_store'
		);
		$arrPurchaseSums = $objPoints->user_points_sums($arrUserPointSumsParams2);
		$arrUserPointSumsParams2 = $arrUserPointSumsParams;
		$arrUserPointSumsParams2['resource_name'] = array(
			'specific achievement card',
			'generic achievement card',
			'direct_transfer',
			'scratch_card'
		);
		$arrAchievementSums = $objPoints->user_points_sums($arrUserPointSumsParams2);
		// build rows
		$arrRows = array();
		foreach ($arrRowSums as $intUser => $arrSumItem)
		{
			if (!isset($arrUsers[$intUser]))
				continue;
			if (!isset($arrUserClasses[$intUser]))
				continue;
			$objUser = $arrUsers[$intUser];
			$objUserClass = $arrUserClasses[$intUser];
			if (!isset($arrClasses[$objUserClass->class_id]))
				continue;
			$objClass = $arrClasses[$objUserClass->class_id];
			$intTotal = isset($arrRowSums[$objUser->user_id]) ? $arrRowSums[$objUser->user_id] : 0;
			if (
				isset($arrParams['active_users'])
				&& $intTotal == 0
			)
				continue;
			$intStudentPurchases = 0;
			if (isset($arrPurchaseSums[$objUser->user_id]))
			{
				$arrStudentPurchaseItem = $arrPurchaseSums[$objUser->user_id];
				$intStudentPurchases = $arrStudentPurchaseItem['store'];
			}
			$arrRows[] = array(
				'user_id' => $objUser->user_id,
				'class_hierarchy' => $objClass->class_hierarchy,
				'class' => $objClass->custom_name1,
				'name' => $objUser->first_name . ' ' . $objUser->last_name,
				'total' => $intTotal,
				'arrCampaignItems' => isset($arrRowCampaigns[$objUser->user_id]) ? $arrRowCampaigns[$objUser->user_id] : array(),
				'intTotalTransaction' => isset($arrManualSums[$objUser->user_id]['total']) ? $arrManualSums[$objUser->user_id]['total'] : 0,
				'intStoreTransaction' => isset($arrManualSums[$objUser->user_id]['store']) ? $arrManualSums[$objUser->user_id]['store'] : 0,
				'intAchievementSum' => isset($arrAchievementSums[$objUser->user_id]['store']) ? $arrAchievementSums[$objUser->user_id]['store'] : 0,
				'intStudentPurchases' => isset($arrPurchaseSums[$objUser->user_id]['store']) ? $arrPurchaseSums[$objUser->user_id]['store'] : 0,
				'intTotalPoints' => isset($arrUserBalanceSums[$objUser->user_id]['total']) ? $arrUserBalanceSums[$objUser->user_id]['total'] : 0,
				'intStoreBalance' => isset($arrUserBalanceSums[$objUser->user_id]['store']) ? $arrUserBalanceSums[$objUser->user_id]['store'] : 0,
				'arrCampaignTotals' => @$arrCampaignTotals[$objUser->user_id]

			);
		}

		// sort
		$arrSortParams = array();
		if (isset($arrParams['sort_points'])) {
			array_push($arrSortParams, 'total');
			array_push($arrSortParams, SORT_DESC);
			array_push($arrSortParams, 'class_hierarchy');
			array_push($arrSortParams, SORT_ASC);
		} else {
			array_push($arrSortParams, 'class_hierarchy');
			array_push($arrSortParams, SORT_ASC);
			array_push($arrSortParams, 'name');
			array_push($arrSortParams, SORT_ASC);
		}
		array_push($arrSortParams, $arrRows);
		$arrRows = call_user_func_array("msort", $arrSortParams);

		$this->view->arrRows = $arrRows;
	}

	public function reportoptionsAction()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objClasses = new Classes();
		$objConfig = new Config();
		$objRoles = new Roles();

		$this->view->arrCampaigns = $arrCampaigns = array_hash('campaign_id', $objCampaigns->rule_filter_campaign_object($query->campaigns__select(array(
			'institution_id' => $this->_user_session_data->institution_id,
			'_ORDER' => 'campaign_name ASC'
		))));
		$arrTasks = array_hash('task_id', $query->tasks__select(array(
			'institution_id' => $this->_user_session_data->institution_id,
			'campaign_id' => array_stack('campaign_id', $arrCampaigns)
		)));

		foreach ($arrTasks as $objTask)
		{
			$objTask->campaign_name = $arrCampaigns[$objTask->campaign_id]->campaign_name;
		}
		$arrSortParams = array();
		array_push($arrSortParams, 'campaign_name');
		array_push($arrSortParams, SORT_ASC);
		array_push($arrSortParams, 'task_name');
		array_push($arrSortParams, SORT_ASC);
		array_push($arrSortParams, $arrTasks);
		$arrTasks = call_user_func_array("msort", $arrSortParams);
		$this->view->arrTasks = $arrTasks;
		$arrTasksHash = $this->view->arrTasksHash = array_bubble_hash('campaign_id', $arrTasks);
		//dumper(array_stack("campaign_id", $arrTasksHash),0,1);
		//dumper(array_stack("campaign_id", $arrTasks),1,1);

		if ($objRoles->isAllowed("Institution Administrator"))
		{
			$this->view->arrClasses = $arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
				'institution_id' => $this->_user_session_data->institution_id
			)));
		}
		else
		{
			$arrTeacherClasses = array_hash("class_id", $objClasses->_user_classes_select(array(
				"user_id" => $this->_user_session_data->user_id,
				"user_role" => "Teacher"
			)));
			$arrClassIds = array_keys($arrTeacherClasses);
			$this->view->arrClasses = $arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
				'class_id' => $arrClassIds
			)));
		}
		$this->view->arrUserClasses = $arrUserClasses = array_bubble_hash('class_id', $query->user_classes__select(array(
			'class_id' => array_keys($arrClasses),
			array(
				"_IN" => array(
					"_TABLE" => "permissions",
					"_DEPENDENT" => "user_id",
					"_INDEPENDENT" => "user_id",
					'_GREATER' => array(
						"registration_expiration" => time()
					)
				)
			),
			array(
				"_IN" => array(
					"_TABLE" => "users",
					"_DEPENDENT" => "user_id",
					"_INDEPENDENT" => "user_id",
					'is_active' => 1
				)
			)
		)));
		$arrConfigParams = array(
			"set" => "admin_report_settings_save",
			"user_id" => $this->_user_session_data->user_id,
			"institution_id" => $this->_user_session_data->institution_id,
			'_NOHOST' => 1
		);
		$arrConfig = $objConfig->load($arrConfigParams);
		if (isset($arrConfig['admin_report_settings_save']))
			$arrConfig['admin_report_settings_save'] = array_reverse($arrConfig['admin_report_settings_save'], 1);
		$this->view->arrConfig = $arrConfig;

		if ($this->_request->getPost('save_report') == 'true')
		{
			$strParams = $this->_request->getPost('arrParams');
			$arrParams = json_decode($strParams);
			if (gettype($arrParams) != 'object')
			{
				print "Sorry, there was an error: CR-RO101-g3gd4f";
				exit;
			}
			$arrParams = (array) $arrParams;
			$arrParams['date'] = time();
			$arrParams['name'] = preg_replace('/["\']+/', '', $arrParams['name']);
			$arrNewConfigParams = array(
				"set" => "admin_report_settings_save",
				"key" => $arrParams['name'],
				"user_id" => $this->_user_session_data->user_id,
				"institution_id" => $this->_user_session_data->institution_id
			);
			$arrConfigResult = array();
			$arrConfigResult["admin_report_settings_save"][$arrParams['name']] = json_encode($arrParams);
			$objConfig->save($arrConfigResult, $arrNewConfigParams);
			$arrConfigParams = array(
				"set" => "admin_report_settings_save",
				"key" => $arrParams['name'],
				"user_id" => $this->_user_session_data->user_id,
				"institution_id" => $this->_user_session_data->institution_id,
				'_NOHOST' => 1
			);
			$objNewItem = first($objConfig->load($arrConfigParams));
			print json_encode(array(
				"success" => "true",
				'objNewItem' => $objNewItem,
				'strName' => $arrParams['name']
			));
			exit;
		}
		else if ($this->_request->getPost('delete_report') == 'true')
		{
			$strName = $this->_request->getPost('name');
			$query->config_settings__delete(array(
				'user_id' => $this->_user_session_data->user_id,
				'institution_id' => $this->_user_session_data->institution_id,
				'set' => 'admin_report_settings_save',
				'key' => $strName,
				'_VERBOSE' => 1
			));
			print json_encode(array(
				"success" => "true"
			));
			exit;
		}

	}

	public function reportgeneratorAction()
	{
		$query = new QueryGen();
		$objUsers = new Users();
		$objClasses = new Classes();
		$objCampaigns = new Campaigns();
		$objTasks = new Tasks();
		$objStore = new Store();
		$objPoints = new Points();
		$objLegacy = new Legacy();

		$this->view->boolDownload = $this->_request->getParam("download") == "true" ? true : false;

		$this->view->objLegacyInstitution = first($objLegacy->_legacy_lookup_select(array(
			"ims_id" => $this->_user_session_data->institution_id,
			"ims_table" => "institutions"
		)));
		$arrClasses = $objClasses->_classes_select_hierarchal(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrClassesHash = array();
		foreach ($arrClasses as $objClass)
		{
			$strKey = preg_replace("/^pre(?:\-?school)? *([0-9a-z]+)/i", "0\1", $objClass->grade)  ." ". $objClass->sub;
			$arrClassesHash[$strKey] = $objClass;
		}
		ksort($arrClassesHash);
		$this->view->arrClasses = $arrClassesHash;
		$arrClassesHash = array();
		foreach ($arrClasses as $objClass)
		{
			$arrClassesHash[$objClass->class_id] = $objClass;
		}

		$arrCampaigns = array_hash('campaign_id', $objCampaigns->rule_filter_campaign_object($objCampaigns->_campaigns_select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"campaign_type" => "Incremental"
		))));
		$arrTasks = array_bubble_hash('campaign_id', $query->tasks__select(array(
			'campaign_id' => array_stack('campaign_id', $arrCampaigns)
		)));
		foreach ($arrCampaigns as $objCampaign)
		{
			if (!isset($arrTasks[$objCampaign->campaign_id]))
			{
				unset($arrCampaigns[$objCampaign->campaign_id]);
			}
		}
		$this->view->arrCampaigns = $arrCampaigns;
		if ($this->_request->isPost())
		{
			$arrResults = array();
			$arrRowData = array();
			$arrPostParams = $this->_request->getPost();
			if ($this->view->boolDownload)
			{
				$arrPostParamsTemp = stripslashes($arrPostParams["data"]);
				$arrPostParams = array();
				parse_str($arrPostParamsTemp, $arrPostParams);
			}

			if (
				@$arrPostParams["tasks"] == "on"
				&& !(
					@$arrPostParams["campaign_points"] == "on"
					|| @$arrPostParams["campaign_status"] == "on"
				)
			) {
				unset($arrPostParams["tasks"]);
			}

			$arrResults["arrPostParams"] = $arrPostParams;

			if (isset($arrPostParams["date_range"]))
			{
				if (
					// month, day, year
					preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", $arrPostParams["start_date"], $arrStartDateMatched)
					&& preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", $arrPostParams["end_date"], $arrEndDateMatched)
				) {
					$intStartDate = mktime(0, 0, 0, $arrStartDateMatched[1], $arrStartDateMatched[2], $arrStartDateMatched[3]);
					$intEndDate = mktime(24, 0, 0, $arrEndDateMatched[1], $arrEndDateMatched[2], $arrEndDateMatched[3]);
				}
			}
			// Colect the classes that where checked
			$arrClassParams = array();
			foreach ($arrPostParams as $intKey => $strON)
			{
				if (preg_match("/^report_class_([0-9]+)/", $intKey, $arrMatched))
				{
					$arrClassParams[$arrMatched[1]] = $arrMatched[1];
				}
			}

			// Collect the campaigns that where checked
			$arrCampaignParams = array();
			foreach ($arrPostParams as $intKey => $strON)
			{
				if (preg_match("/^report_campaign_([0-9]+)/", $intKey, $arrMatched))
				{
					$arrCampaignParams[$arrMatched[1]] = $arrMatched[1];
				}
			}

			$arrUsers = array_hash("user_id", array_clean_slashes($objUsers->_users_select_hierarchal(array(
				"class_id" => $arrClassParams,
				"institution_id" => $this->_user_session_data->institution_id,
				"permission" => "Student",
				"is_active" => 1
			))));

			if (isset($arrPostParams["class"]))
			{
				$arrUserIds = array();
				foreach ($arrUsers as $objUser)
				{
					$arrUserIds[] = $objUser->user_id;
					$arrRowData[$objUser->user_id]["class"] = 0;
				}
				$arrUserClasses = $objClasses->_user_classes_select(array(
					"user_id" => $arrUserIds,
					"class_id" => $arrClassParams
				));
				$arrUserClassesHash = array();
				foreach ($arrUserClasses as $objUserClass)
				{
					$arrRowData[$objUserClass->user_id]["class"] = $arrClassesHash[$objUserClass->class_id]->grade . " " . $arrClassesHash[$objUserClass->class_id]->sub;
				}
			}

			// Build the results row/column data
			foreach ($arrUsers as $objUser)
			{
				$intUser = $objUser->user_id;
				if (isset($arrPostParams["user_code"]))
				{
					$arrRowData[$intUser]["user_code"] = $objUser->user_id;
				}
				if (isset($arrPostParams["first_name"]))
				{
					if (
						isset($arrPostParams["prioritize_hebrew_name"])
						&& $objUser->hebrew_first_name
						&& $objUser->hebrew_first_name != "NULL"
						&& !strpos($objUser->hebrew_first_name, "?")
					) {
						$arrRowData[$intUser]["first_name"] = $objUser->hebrew_first_name;
					}
					else
					{
						$arrRowData[$intUser]["first_name"] = $objUser->first_name;
					}
				}
				if (isset($arrPostParams["last_name"]))
				{
					if (
						isset($arrPostParams["prioritize_hebrew_name"])
						&& $objUser->hebrew_last_name
						&& $objUser->hebrew_last_name != "NULL"
						&& !strpos($objUser->hebrew_last_name, "?")
					) {
						$arrRowData[$intUser]["last_name"] = $objUser->hebrew_last_name;
					}
					else
					{
						$arrRowData[$intUser]["last_name"] = $objUser->last_name;
					}
				}
				if (isset($arrPostParams["gender"]))
				{
					if ($objUser->gender == "m")
						$strGender = "Male";
					else if ($objUser->gender == "f")
						$strGender = "Female";
					else
						$strGender = "N/A";
					$arrRowData[$intUser]["gender"] = $strGender;
				}
				if (isset($arrPostParams["dob"]))
				{
					$strDOB = $objUser->dob == "NULL" ? "N/A" : $objUser->dob;
					$arrRowData[$intUser]["dob"] = $strDOB;
				}
			}

			if (isset($arrPostParams["points"]))
			{
				foreach ($arrUsers as $objUser)
				{
					$arrParams = array(
						"user_id" => $objUser->user_id
					);
					$intPoints = $objPoints->user_points_sum($arrParams);
					$arrRowData[$objUser->user_id]["points"] = intval($intPoints);
				}

			}
			if (isset($arrPostParams["total_points"]))
			{
				foreach ($arrUsers as $objUser)
				{

					$arrPointsParams = array(
						"_SUM" => "points",
						"_GROUP_BY" => "user_id",
						"user_id" => $objUser->user_id,
						array(
							"_GREATER" => array(
								"points" => 0
							),
							array(
								"_LESSER" => array(
									"points" => 0
								),
								"resource_name" => "admin_users_manual"
							)
						)
						//,"_VERBOSE" => 1
					);
					if (isset($intStartDate))
					{
						$arrPointsParams["_GREATER"]["_TIMESTAMP"]["created"] = $intStartDate;
					}
					if (isset($intEndDate))
					{
						$arrPointsParams["_LESSER"]['_TIMESTAMP']["created"] = $intEndDate;
					}
					$objUserPoints = first($query->user_points__select($arrPointsParams));
					$arrRowData[$objUser->user_id]["total_points"] = intval($objUserPoints->_sum_points);
				}

			}

			if (
				isset($arrPostParams["campaign_points"])
				|| isset($arrPostParams["campaign_status"])
			) {
				$arrCampaignsFound = array();
				$arrTasksFound = array();
				$arrCampaignTasks = array();
				foreach ($arrUsers as $objUser)
				{
					$arrUserCampaignParams = array(
						"user_id" => $objUser->user_id,
						"campaign_id" => $arrCampaignParams
					);
					if (isset($arrPostParams["date_range"]))
					{
						$arrUserCampaignParams["created_min"] = $intStartDate;
						$arrUserCampaignParams["created_max"] = $intEndDate;
					}
					$arrUserCampaigns = $objCampaigns->_user_campaigns_select($arrUserCampaignParams);

					if (isset($arrPostParams["campaign_points"]))
						$arrRowData[$objUser->user_id]["campaign_points"] = array();
					if (isset($arrPostParams["campaign_status"]))
						$arrRowData[$objUser->user_id]["campaign_status"] = array();
					$intActivity = 0;
					foreach ($arrUserCampaigns as $objUserCampaigns)
					{
						if (!$objUserCampaigns->task_id)
							continue;
						$arrCampaignsFound[$objUserCampaigns->campaign_id] = $objUserCampaigns->campaign_id;
						$arrTasksFound[$objUserCampaigns->task_id] = $objUserCampaigns->task_id;
						$arrCampaignTasks[$objUserCampaigns->campaign_id][$objUserCampaigns->task_id] = 1;
						if (isset($arrPostParams["campaign_points"]))
						{
							if (!isset($arrRowData[$objUser->user_id]["campaign_points"][$objUserCampaigns->campaign_id][$objUserCampaigns->task_id]))
								$arrRowData[$objUser->user_id]["campaign_points"][$objUserCampaigns->campaign_id][$objUserCampaigns->task_id] = 0;
							$arrRowData[$objUser->user_id]["campaign_points"][$objUserCampaigns->campaign_id][$objUserCampaigns->task_id] += $objUserCampaigns->points_given;
							$intActivity += $objUserCampaigns->points_given;
						}
						if (isset($arrPostParams["campaign_status"]))
						{
							if (!isset($arrRowData[$objUser->user_id]["campaign_status"][$objUserCampaigns->campaign_id][$objUserCampaigns->task_id]))
								$arrRowData[$objUser->user_id]["campaign_status"][$objUserCampaigns->campaign_id][$objUserCampaigns->task_id] = 0;
							$arrRowData[$objUser->user_id]["campaign_status"][$objUserCampaigns->campaign_id][$objUserCampaigns->task_id]++;
							$intActivity++;
						}
					}
					if (
						isset($arrPostParams["active"])
						&& (
							isset($arrPostParams["campaign_points"])
							|| isset($arrPostParams["campaign_status"])
						)
						&& $intActivity < 1
					) {
						unset($arrRowData[$objUser->user_id]);
					}
				}

				// Fill in the blank point data
				$arrTasksSelected = $objTasks->_tasks_select(array(
					"campaign_id" => $arrCampaignParams
				));
				foreach ($arrUsers as $objUser)
				{
					if (!isset($arrRowData[$objUser->user_id]))
						continue;
					foreach ($arrTasksSelected as $objTask)
					{
						if (isset($arrPostParams["campaign_points"]))
						{
							if (!isset($arrRowData[$objUser->user_id]["campaign_points"][$objTask->campaign_id][$objTask->task_id]))
								$arrRowData[$objUser->user_id]["campaign_points"][$objTask->campaign_id][$objTask->task_id] = 0;
						}
						if (isset($arrPostParams["campaign_status"]))
						{
							if (!isset($arrRowData[$objUser->user_id]["campaign_status"][$objTask->campaign_id][$objTask->task_id]))
								$arrRowData[$objUser->user_id]["campaign_status"][$objTask->campaign_id][$objTask->task_id] = 0;
						}
					}
				}
				$arrCampaignsHash = array_hash("campaign_id", $objCampaigns->_campaigns_select(array(
					"campaign_id" => $arrCampaignsFound,
					"institution_id" => $this->_user_session_data->institution_id,
					"is_active" => 1
				)));
				$arrTasksHash = array_hash("task_id", $objTasks->_tasks_select(array(
					"task_id" => $arrTasksFound
				)));

				$arrResults["arrTasksHash"] = $arrTasksHash;
				$arrResults["arrCampaignsHash"] = $arrCampaignsHash;
				// Remove inactive campaigns
				/*
				foreach ($arrUsers as $objUser)
				{
					foreach ($arrRowData[$objUser->user_id]["campaigns"] as $intCampaign => $arrTaskIds)
					{
						if (!isset($arrCampaignsHash[$intCampaign]))
							unset($arrRowData[$objUser->user_id]["campaigns"][$intCampaign]);
					}
				}
				 *
				 */
			}
			// sort row data
			$arrSortedRowData = array();
			foreach ($arrRowData as $intUser => $arrParams)
			{
				$strSortKey =
					(@$arrParams["class"] == 0 ? "zzzzzzzz" : @$arrParams["class"])
					. "\t" . @$arrParams["last_name"]
					. "\t" . @$arrParams["first_name"]
					. "\t" . $intUser;
				$arrSortedRowData[$strSortKey][$intUser] = $arrParams;
			}
			ksort($arrSortedRowData);
			$arrRowData = array();
			foreach ($arrSortedRowData as $strKey => $arrParams)
			{
				foreach ($arrParams as $intUser => $arrParams)
				{
					if (isset($arrParams["class"]) && $arrParams["class"] == 0)
						$arrParams["class"] = "";
					$arrRowData[$intUser] = $arrParams;
				}
			}
			$arrResults["arrRowData"] = $arrRowData;
			if ($this->view->boolDownload)
			{
				$this->view->arrResults = $arrResults;
			}
			else
			{
				print json_encode($arrResults);
				exit;
			}
		}
	}

	public function reportcustomtanyaAction()
	{
		$query = new QueryGen();
		$objClasses = new Classes();
		$objCampaigns = new Campaigns();
		$objUsers = new Users();
		$objAutomation = new Automation();
		$objInstitutions = new Institutions();

		$arrParams = $this->view->arrParams = $this->_request->getParams();
		$strReportType = $this->view->strReportType = @$arrParams["report_type"];

		$intStartDate = $intEndDate = false;
		if (isset($arrParams["date_range"]))
		{
			if (
				// month, day, year
				preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", urldecode($arrParams["start_date"]), $arrStartDateMatched)
				&& preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", urldecode($arrParams["end_date"]), $arrEndDateMatched)
			) {
				$intStartDate = mktime(0, 0, 0, $arrStartDateMatched[1], $arrStartDateMatched[2], $arrStartDateMatched[3]);
				$intEndDate = mktime(24, 0, 0, $arrEndDateMatched[1], $arrEndDateMatched[2], $arrEndDateMatched[3]);
			}
		}

		$arrResult = array();
		//var_dump($this->_user_session_data->institution_id);
		if ($strReportType == "grade")
		{
			// Create a list of students and what they've been tested grouped up
			// to by grade
			$arrClasses = $objClasses->_classes_select(array(
				"institution_id" => $this->_user_session_data->institution_id
			));
			$this->view->arrClassLegacyIds = array_hash('ims_id', $query->legacy_lookup__select(array(
				"ims_id" => array_keys(array_stack('class_id', $arrClasses)),
				'ims_table' => 'classes'
			)));
			$arrClassLegacyIds = array_keys(array_stack('legacy_id', $this->view->arrClassLegacyIds));
			$objLegacy = new Legacy();
			$this->view->objTeacherNames = array_hash('class_id', $objLegacy->datahacker(array(
				'strSql' => "SELECT	class_id, class_teacher FROM classes WHERE class_id IN (" . join(',', $arrClassLegacyIds) . ")"
			)));
			//dumper($this->view->objTeacherNames,1,1);
			foreach ($arrClasses as $strKey => $objClass)
			{
				$arrResult[$strKey]["objClass"] = $objClass;
				// Find the teacher
				$arrTeacherClasses = array_hash("user_id", $objClasses->_user_classes_select(array(
					"class_id" => $objClass->class_id,
					"class_role" => "Teacher"
				)));
				$arrResult[$strKey]["arrTeachers"] = array_hash("user_id", $objUsers->_users_select(array(
					"user_id" => array_keys($arrTeacherClasses)
				)));
				// Find all students
				$arrStudentClasses = array_hash("user_id", $objClasses->_user_classes_select(array(
					"class_id" => $objClass->class_id,
					"class_role" => "Student"
				)));
				$arrStudents = array_hash("user_id", $objUsers->_users_select(array(
					"user_id" => array_keys($arrStudentClasses)
				)));
				$arrCampaignLines = array_hash("user_id", $objCampaigns->user_campaign_current_line(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"user_id" => array_keys($arrStudents),
					"campaign_id" => 1,
					"start_date" => $intStartDate,
					"end_date" => $intEndDate
				)));
				//var_dump($arrCampaignLines);
				$arrList = array();
				foreach ($arrCampaignLines as $objUserCampaign)
				{
					if (!isset($arrStudents[$objUserCampaign->user_id]))
						continue;
					$objUser = $arrStudents[$objUserCampaign->user_id];
					$arrList[] = array(
						"first_name" => $objUser->first_name,
						"last_name" => $objUser->last_name,
						"tested_up_to" => $objUserCampaign->current_line
					);
				}
				$arrResult[$strKey]["arrList"] = $arrList;
			}
		}
		else if ($strReportType == "school")
		{
			$arrClasses = $objClasses->_classes_select(array(
				"institution_id" => $this->_user_session_data->institution_id
			));
			$arrResult["army_accomplishment"] = $objAutomation->user_campaign_progress_sum(array(
				"campaign_id" => 1,
				"institution_id" => $this->_user_session_data->institution_id,
				"_SUM" => "current_line"
			));
			foreach ($arrClasses as $intItr => $objClass)
			{
				$strKey = $objClass->grade . ":" . $objClass->sub . ":" . $intItr;
				$arrResult["arrList"][$strKey]["objClass"] = $objClass;
				// Find the teacher
				$arrTeacherClasses = array_hash("user_id", $objClasses->_user_classes_select(array(
					"class_id" => $objClass->class_id,
					"class_role" => "Teacher"
				)));
				$arrResult["arrList"][$strKey]["arrTeachers"] = array_hash("user_id", $objUsers->_users_select(array(
					"user_id" => array_keys($arrTeacherClasses)
				)));
				$arrStudentClasses = array_hash("user_id", $objClasses->_user_classes_select(array(
					"class_id" => $objClass->class_id,
					"class_role" => "Student"
				)));
				$arrResult["arrList"][$strKey]["grade_accomplished"] = $objAutomation->user_campaign_progress_sum(array(
					"campaign_id" => 1,
					"user_id" => array_keys($arrStudentClasses),
					"_SUM" => "current_line"
				));
				$arrResult["arrList"][$strKey]["grade_goal"] = $objAutomation->user_campaign_progress_sum(array(
					"campaign_id" => 1,
					"user_id" => array_keys($arrStudentClasses),
					"_SUM" => "campaign_goal"
				));
				$arrResult["arrList"][$strKey]["count"] = count($arrStudentClasses);
			}
			ksort($arrResult["arrList"]);
		}
		else if ($strReportType == "all")
		{
			$arrQueryItem1 = array(
				'template_style' => 'tanyatemplate1'
			);
			if (devel)
				$arrQueryItem1['institution_id'] = 47;
			$arrInstitutions = $query->institutions__select(array(
				//"_GREATER" => array(
				//	"reg_expires" => time()
				//)
				$arrQueryItem1,
				'_ORDER' => 'name'
			));
			$arrCampaignProgress = array_hash("institution_id", $objAutomation->campaign_progress_institution_sums(array()));
			$intGoalSum = $intAccomplishedSum = 0;
			foreach ($arrInstitutions as $intKey => $objInstitution)
			{

				$arrResult["arrList"][$intKey]["institution_name"] = $objInstitution->name;
				$arrResult["arrList"][$intKey]["goal"] = intval(@$arrCampaignProgress[$objInstitution->institution_id]->campaign_goal_sum);
				$intGoalSum += $arrResult["arrList"][$intKey]["goal"];
				$arrResult["arrList"][$intKey]["accomplished"] = intval(@$arrCampaignProgress[$objInstitution->institution_id]->current_line_sum);
				$intAccomplishedSum += $arrResult["arrList"][$intKey]["accomplished"];
			}
			$arrResult["army_accomplishment"] = $intAccomplishedSum;
			$arrResult["army_goal"] = $intGoalSum;
		}
		$this->view->arrReport = $arrResult;
	}

	public function reportcustomtanya2Action()
	{
		$query = new QueryGen();
		$objClasses = new Classes();
		$objCampaigns = new Campaigns();
		$objUsers = new Users();
		$objAutomation = new Automation();
		$objInstitutions = new Institutions();

		$arrParams = $this->view->arrParams = $this->_request->getParams();
		$strReportType = $this->view->strReportType = @$arrParams["report_type"];

		$intStartDate = mktime(0,0,0,1,1,2008);
		$intEndDate = mktime(0,0,0,3,22,2013);
		//$intStartDate = mktime(0,0,0,1,1,200);
		//$intEndDate = time();
		if (!isset($arrParams["start_date"]))
			$arrParams["start_date"] = date('m/d/Y', $intStartDate);
		if (!isset($arrParams["end_date"]))
			$arrParams["end_date"] = date('m/d/Y', $intEndDate);
		$arrParams["date_range"] = 1;
		$this->view->arrParams = $arrParams;

		if (isset($arrParams["date_range"]))
		{
			if (
				// month, day, year
				preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", urldecode($arrParams["start_date"]), $arrStartDateMatched)
				&& preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", urldecode($arrParams["end_date"]), $arrEndDateMatched)
			) {
				$intStartDate = mktime(0, 0, 0, $arrStartDateMatched[1], $arrStartDateMatched[2], $arrStartDateMatched[3]);
				$intEndDate = mktime(24, 0, 0, $arrEndDateMatched[1], $arrEndDateMatched[2], $arrEndDateMatched[3]);
			}
		}


		$arrResult = array();
		//var_dump($this->_user_session_data->institution_id);
		if ($strReportType == "grade")
		{
			// Create a list of students and what they've been tested grouped up
			// to by grade
			$this->view->arrClasses = $arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
				"institution_id" => $this->_user_session_data->institution_id
			)));
			$this->view->arrClassLegacyIds = array_hash('ims_id', $query->legacy_lookup__select(array(
				"ims_id" => array_keys($arrClasses),
				'ims_table' => 'classes'
			)));
			$arrClassLegacyIds = array_keys(array_stack('legacy_id', $this->view->arrClassLegacyIds));
			$objLegacy = new Legacy();
			$this->view->objTeacherNames = array_hash('class_id', $objLegacy->datahacker(array(
				'strSql' => "SELECT	class_id, class_teacher FROM classes WHERE class_id IN (" . join(',', $arrClassLegacyIds) . ")"
			)));
			$arrCampaignQuotaParams = array(
				//'user_id' => 866,
				'_COLUMNS' => array('institution_id','task_increment','user_id','schedule_date','ladder_velocity','status','line_offset'),
				'institution_id' => $this->_user_session_data->institution_id,
				'campaign_id' => 1,
				'status' => array('Enrollment','Completed'),
				'_GREATER' => array(
					'schedule_date' => $intStartDate
				),
				'_LESSER' => array(
					'schedule_date' => $intEndDate
				)
			);
			$arrCampaignQuotas = $query->user_campaigns__select($arrCampaignQuotaParams);
			//dumper($arrCampaignQuotas,1,1);
			$arrCampaignPauseParams = array(
				//'user_id' => 866,
				'_COLUMNS' => array('institution_id','user_id','ladder_velocity','status','schedule_date'),
				'institution_id' => $this->_user_session_data->institution_id,
				'campaign_id' => 1,
				'status' => array('Paused','Resumed'),
				'_ORDER' => 'schedule_date+0 ASC'
			);
			$arrCampaignPauses = array_bubble_hash('institution_id', 'user_id', $query->user_campaigns__select($arrCampaignPauseParams));
			//dumper($arrCampaignPauses,1,1);
			$arrCampaignEnrollment = array_hash('institution_id', 'user_id', $query->user_campaigns__select(array(
				//'user_id' => 866,
				'_COLUMNS' => array('institution_id','user_id','schedule_date','ladder'),
				'institution_id' => $this->_user_session_data->institution_id,
				'campaign_id' => 1,
				'status' => 'Enrollment'
			)));
			$arrUserLastLines = array_hash('user_id', $query->user_campaigns__select(array(
				'_COLUMNS' => array('user_id', 'institution_id', 'schedule_date'),
				'status' => 'Completed',
				'user_id' => array_stack('user_id', array_flatten2($arrCampaignEnrollment)),
				'_ORDER' => 'schedule_date+0 DESC',
				'_GROUP' => 'user_id'
			)));
			$arrUsers = array_hash('user_id', $query->users__select(array(
				'_COLUMNS' => array('user_id', 'dob', 'gender'),
				'user_id' => array_stack('user_id', $arrCampaignQuotas)
			)));
			// calculate lines and quotas
			$arrUserData = array();
			$arrProcessedUserSchedules = array();
			$arrLastCampaignItem = array();
			foreach ($arrCampaignQuotas as $objUserCampaign)
			{
				if (!isset($arrCampaignEnrollment[$objUserCampaign->institution_id][$objUserCampaign->user_id]))
					continue;
				if ($arrCampaignEnrollment[$objUserCampaign->institution_id][$objUserCampaign->user_id]->ladder == 0)
					continue;
				if (!isset($arrUserData[$objUserCampaign->user_id]))
					$arrUserData[$objUserCampaign->user_id] = array(
						'goal' => 0,
						'goal_count' => 0,
						'lines_min' => NULL,
						'lines_max' => NULL,
						'lines' => 0
					);
				$arrUserItem = &$arrUserData[$objUserCampaign->user_id];
				if ($objUserCampaign->status == 'Enrollment')
				{
					$arrUserItem['goal'] += $objUserCampaign->line_offset;
					$arrUserItem['lines'] += $objUserCampaign->line_offset;
					continue;
				}
				// lines
				if (
					is_null($arrUserItem['lines_min'])
					|| $arrUserItem['lines_min'] > $objUserCampaign->task_increment
				)
					$arrUserItem['lines_min'] = $objUserCampaign->task_increment;
				if (
					is_null($arrUserItem['lines_max'])
					|| $arrUserItem['lines_max'] < $objUserCampaign->task_increment
				) {
					$arrUserItem['lines_max'] = $objUserCampaign->task_increment;
					$arrLastCampaignItem[$objUserCampaign->user_id] = $objUserCampaign;
				}
				// goals
				if (!isset($arrProcessedUserSchedules[$objUserCampaign->user_id][$objUserCampaign->schedule_date]))
				{
					$arrProcessedUserSchedules[$objUserCampaign->user_id][$objUserCampaign->schedule_date] = TRUE;
					$arrUserItem['goal'] += $objUserCampaign->ladder_velocity;
					$arrUserItem['goal_count']++;
				}
			}
			//dumper($arrUserData,0,1);
			// calulcate remainders for the unmarked items
			$arrLadders = array_hash('ladder', $query->velocity_ladders__select(array(
				'campaign_id' => 1
			)));
			// add missing goals
			// on missing items past their last line marked
			// don't add to thier goals after their birthdate
			foreach ($arrCampaignEnrollment as $arrInstitution)
			{
				foreach ($arrInstitution as $objEnrollment)
				{
					if (!isset($arrUserData[$objEnrollment->user_id]))
					{
						$arrUserData[$objEnrollment->user_id] = array(
							'goal' => 0,
							'goal_count' => 0,
							'lines' => 0
						);
					}
					if (!isset($arrUsers[$objEnrollment->user_id]))
						continue;
					// Calculate the schedule time
					$arrUserItem = &$arrUserData[$objEnrollment->user_id];
					$intUnmarkedStart = $intStartDate < $objEnrollment->schedule_date ? $objEnrollment->schedule_date : $intStartDate;
					$intBatBar = strtotime('+' . (strtolower($arrUsers[$objEnrollment->user_id]->gender) == "m" ? 13 : 12) . ' years', strtotime($arrUsers[$objEnrollment->user_id]->dob));
					$intEndSchedule = $intBatBar > $intEndDate ? $intEndDate : $intBatBar;
					if (isset($arrUserLastLines[$objEnrollment->user_id]))
					{
						$objLatestLine = $arrUserLastLines[$objEnrollment->user_id];
						$intUnmarkedStart = $intUnmarkedStart < $objLatestLine->schedule_date ? $objLatestLine->schedule_date : $intUnmarkedStart;
					}
					$arrPauses = array();
					if (isset($arrCampaignPauses[$objEnrollment->institution_id][$objEnrollment->user_id]))
						$arrPauses = $arrCampaignPauses[$objEnrollment->institution_id][$objEnrollment->user_id];
					$intUnmarkedPause = 0;
					$intPauseStartTime = NULL;
					$boolPaused = 0;
					$intLastPauseAmount = NULL;
					foreach ($arrPauses as $objPause)
					{
						if ($objPause->status == "Paused" && !$boolPaused)
						{
							$boolPaused = 1;
							$intPauseStartTime = $objPause->schedule_date;
						}
						if ($objPause->status == "Resumed")
						{
							if (!$boolPaused && $intLastPauseAmount)
							{
								$intUnmarkedPause -= $intLastPauseAmount;
							}
							$boolPaused = 0;
							$intPauseEndTime = $objPause->schedule_date;
							// innser section
							if (
								$intPauseStartTime >= $intUnmarkedStart
								&& $intPauseEndTime <= $intEndSchedule
							) {
								$intLastPauseAmount = $intPauseEndTime - $intPauseStartTime;
								$intUnmarkedPause += $intLastPauseAmount;
							}
							// left out of bounds, right in bounds
							if (
								$intPauseStartTime < $intUnmarkedStart
								&& $intPauseEndTime >= $intEndSchedule
								&& $intPauseStartTime < $intEndSchedule
							) {
								$intLastPauseAmount = $intPauseEndTime - $intUnmarkedStart;
								$intUnmarkedPause += $intLastPauseAmount;
							}
							// left in bounds, right out of bounds
							if (
								$intPauseStartTime > $intUnmarkedStart
								&& $intPauseStartTime < $intEndSchedule
								&& $intPauseEndTime > $intEndSchedule
							) {
								$intLastPauseAmount = $intEndSchedule - $intPauseStartTime;
								$intUnmarkedPause += $intLastPauseAmount;
							}
							// left through right pause, both out of bounds
							if (
								$intPauseStartTime < $intUnmarkedStart
								&& $intPauseEndTime > $intEndSchedule
							) {
								$intLastPauseAmount = $intEndSchedule - $intUnmarkedStart;
								$intUnmarkedPause += $intLastPauseAmount;
							}
						}
					}
					// find the amount of weeks that have not yet been acounted for
					// find the pauses within that range
					// remove the pause weeks from the unaccounted for weeks
					$intWeeks = floor(($intEndSchedule - $intUnmarkedStart - $intUnmarkedPause) / 60 / 60 / 24 / 7.02388844230769);
					//$arrUserItem['missing'] = $intMissing;
					if ($intWeeks > 0)
					{
						$arrUserItem['goal'] += $arrLadders[$objEnrollment->ladder ? $objEnrollment->ladder-1 : 1]->velocity * $intWeeks;
					}
				}
			}
			$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
				"class_id" => array_keys($arrClasses),
				"class_role" => "Student"
			)));
			$arrUsers = array_hash('user_id', $query->users__select(array(
				'user_id' => array_keys($arrUserClasses)
			)));
			$arrSums = array();
			foreach ($arrUserData as $intUser => $arrUserItem2)
			{
				if (!isset($arrUserClasses[$intUser]))
					continue;
				if (!isset($arrSums[$intUser]['goal']))
					$arrSums[$intUser]['goal'] = 0;
				if (!isset($arrSums[$intUser]['lines']))
					$arrSums[$intUser]['lines'] = 0;
				$arrSums[$intUser]['goal'] += $arrUserItem2['goal'];
				if (!isset($arrUserItem2['lines']) || !$arrUserItem2['lines'])
				{
					$arrUserItem2['lines'] = $arrUserItem2['lines_max'] - $arrUserItem2['lines_min'];
				}
				$arrSums[$intUser]['lines'] += $arrUserItem2['lines'];
			}
			//dumper($arrSums,0,1);
			$arrClassKeys = array();
			foreach ($arrUsers as $intUser => $objUser)
			{
				if (!isset($arrSums[$intUser]))
					continue;
				$objUserClass = $arrUserClasses[$intUser];
				$intClass = $objUserClass->class_id;
				$objClass = $arrClasses[$intClass];
				$strClassKey = $objClass->custom_name1;
				$arrClassKeys[$strClassKey] = $objClass->class_id;
				$strKey = $objUser->first_name . ":" . $objUser->last_name;
				$arrResult["arrList"][$strClassKey][$strKey]["accomplished"] = $arrSums[$intUser]['lines'];
				$arrResult["arrList"][$strClassKey][$strKey]["goal"] = floor($arrSums[$intUser]['goal']);
				$arrResult["arrList"][$strClassKey][$strKey]['objUser'] = $objUser;
				if (isset($arrLastCampaignItem[$intUser]))
				{
					$arrResult["arrList"][$strClassKey][$strKey]["accomplished"] += $arrLastCampaignItem[$intUser]->ladder_velocity;
				}
				$arrResult["arrList"][$strClassKey][$strKey]["accomplished"] = floor($arrResult["arrList"][$strClassKey][$strKey]["accomplished"]);
			}
			$this->view->arrClassKeys = $arrClassKeys;
			if (isset($arrResult["arrList"]))
				foreach ($arrResult["arrList"] as $strClassKey => $arrKeys)
				{
					ksort($arrResult["arrList"][$strClassKey]);
				}
			if (isset($arrResult["arrList"]))
				ksort($arrResult["arrList"]);
			//dumper($arrResult,1,1);
		}
		else if ($strReportType == "school")
		{
			$arrCampaignEnrollment = array_hash('user_id', $query->user_campaigns__select(array(
				'_COLUMNS' => array('institution_id','user_id','schedule_date','ladder'),
				'institution_id' => $this->_user_session_data->institution_id,
				'campaign_id' => 1,
				'status' => 'Enrollment'
			)));
			$arrGoals = $objAutomation->user_goal(array(
				'user_id' => array_keys($arrCampaignEnrollment),
				'campaign_id' => 1,
				'start_date' => $intStartDate,
				'end_date' => $intEndDate,
				'multi' => TRUE
			));
			$arrClasses = array_hash('class_id', $query->classes__select(array(
				"institution_id" => $this->_user_session_data->institution_id
			)));
			$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
				"class_id" => array_keys($arrClasses),
				"class_role" => "Student"
			)));
			$arrUserClassesHashClasses = array_bubble_hash('class_id', $arrUserClasses);
			$arrSums = array();
			//dumper($arrGoals,1,1);
			foreach ($arrGoals as $intUser => $arrUserItem)
			{
				if (!isset($arrUserClasses[$intUser]))
					continue;
				$intClass = $arrUserClasses[$intUser]->class_id;
				if (!isset($arrSums[$intClass]))
					$arrSums[$intClass] = array(
						'goal' => 0,
						'lines' => 0
					);
				$arrSums[$intClass]['goal'] += floor($arrUserItem['goal']);
				$arrSums[$intClass]['lines'] += floor($arrUserItem['lines']);
			}
			foreach ($arrClasses as $intItr => $objClass)
			{
				if (!isset($arrSums[$objClass->class_id]))
					continue;
				$strKey = $objClass->grade . ":" . $objClass->sub . ":" . $intItr;
				$arrResult["arrList"][$strKey]["objClass"] = $objClass;
				// Find the teacher
				$arrTeacherClasses = array_hash("user_id", $objClasses->_user_classes_select(array(
					"class_id" => $objClass->class_id,
					"class_role" => "Teacher"
				)));
				$arrResult["arrList"][$strKey]["arrTeachers"] = array_hash("user_id", $objUsers->_users_select(array(
					"user_id" => array_keys($arrTeacherClasses)
				)));
				$arrStudentClasses = $arrUserClassesHashClasses[$objClass->class_id];

				$arrResult["arrList"][$strKey]["grade_accomplished"] = $arrSums[$objClass->class_id]['lines'];
				$arrResult["arrList"][$strKey]["grade_goal"] = $arrSums[$objClass->class_id]['goal'];
				$arrResult["arrList"][$strKey]["count"] = count($arrStudentClasses);
			}
			ksort($arrResult["arrList"]);
		}
		else if ($strReportType == "all")
		{
			$arrQueryItem1 = array(
				'template_style' => 'tanyatemplate1'
			);
			if (devel)
				$arrQueryItem1['institution_id'] = 47;
			$arrInstitutions = $query->institutions__select(array(
				//"_GREATER" => array(
				//	"reg_expires" => time()
				//)
				//'institution_id' => 33,
				$arrQueryItem1,
				'_ORDER' => 'name'
			));
			$arrCampaignEnrollment = array_hash('user_id', $query->user_campaigns__select(array(
				'_COLUMNS' => array('institution_id','user_id','schedule_date','ladder'),
				'institution_id' => array_keys(array_stack('institution_id',$arrInstitutions)),
				'campaign_id' => 1,
				'status' => 'Enrollment'
			)));
			$arrGoals = $objAutomation->user_goal(array(
				'user_id' => array_keys($arrCampaignEnrollment),
				'institution_id' => array(47),
				'institution_id' => array_stack('institution_id',$arrInstitutions),
				'campaign_id' => 1,
				'start_date' => $intStartDate,
				'end_date' => $intEndDate,
				'multi' => TRUE
			));
			// calculate lines and quotas
			$arrUserData = array();
			$arrProcessedUserSchedules = array();
			// finish goal counts
			$arrLadders = array_hash('ladder', $query->velocity_ladders__select(array(
				'campaign_id' => 1
			)));
			$arrClasses = array_hash('class_id', $query->classes__select(array(
				"institution_id" => array_keys(array_stack('institution_id',$arrInstitutions))
			)));
			$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
				"class_id" => array_keys($arrClasses),
				"class_role" => "Student"
			)));
			$arrSums = array();
			foreach ($arrGoals as $intUser => $arrUserItem3)
			{
				if (!isset($arrCampaignEnrollment[$intUser]))
					continue;
				if (!isset($arrUserClasses[$intUser]))
					continue;
				$intInstitution = $arrCampaignEnrollment[$intUser]->institution_id;
				if (!isset($arrSums[$intInstitution]['goal']))
					$arrSums[$intInstitution]['goal'] = 0;
				if (!isset($arrSums[$intInstitution]['lines']))
					$arrSums[$intInstitution]['lines'] = 0;
				$arrSums[$intInstitution]['goal'] += floor($arrUserItem3['goal']);
				$arrSums[$intInstitution]['lines'] += floor($arrUserItem3['lines']);
			}
			foreach ($arrInstitutions as $intKey => $objInstitution)
			{
				$arrResult["arrList"][$intKey]["institution_name"] = $objInstitution->name;
				$arrResult["arrList"][$intKey]["goal"] = @$arrSums[$objInstitution->institution_id]['goal'];
				$arrResult["arrList"][$intKey]["accomplished"] = @$arrSums[$objInstitution->institution_id]['lines'];
			}
		}
		$this->view->arrReport = $arrResult;
	}




	public function reportclassesAction()
    {
		$objUsers = new Users();

		$intInstitution = $this->_user_session_data->institution_id;

		$objClasses = new Classes();
		$arrClasses = $objClasses->_classes_select_hierarchal(array(
			"institution_id" => $intInstitution
		));
		foreach ($arrClasses as $intKey => $objClass)
		{
			$objClassAssoc = current($objClasses->_user_classes_select(array(
				"class_id" => $objClass->class_id,
				"class_role" => "Teacher"
			)));
			if ($objClassAssoc)
			{
				$objUser = current($objUsers->_users_select(array(
					"user_id" => $objClassAssoc->user_id
				)));
				$arrClasses[$intKey]->objUser = $objUser;
			}
		}

		$this->view->arrClasses = $arrClasses;
	}

	public function reportlistclassesAction()
	{
		$intInstitution = $this->_user_session_data->institution_id;

		$objClasses = new Classes();
		$arrClasses = $objClasses->_classes_select_hierarchal(array(
			"institution_id" => $intInstitution
		));

		$this->view->arrClasses = $arrClasses;
	}

	public function reportpointsAction()
	{
		$objPoints = new Points();
		$objCampaigns = new Campaigns();
		$objTasks = new Tasks();
		$objUsers = new Users();
		$intInstitution = $this->_user_session_data->institution_id;
		$intClass = intval($this->_request->class_id);
		$arrCacheCampaigns = array();
		$arrCacheTasks = array();
		$arrCacheUsers = array();

		// Limit resuts to a class if one is provided, otherwise load the entire institution
		if (isset($intClass) && $intClass)
		{
			$objClasses = new Classes();
			$arrUsers = $objClasses->_user_classes_select(array(
				"class_id" => $intClass
			));
		}
		else
		{
			$objPermissions = new Permissions();
			$arrUsers = $objPermissions->_permissions_select(array(
				"institution_id" => $intInstitution,
				"permission" => "Student"
			));
		}

		// Construct the dataset and list of campaigns and tasks required
		$arrResult = array();
		$arrDataSet = array();
		foreach ($arrUsers as $objUserData)
		{
			$intUserId = $objUserData->user_id;
			if (isset($objUserData->user_class_id))
				$arrResult[$intUserId]["objUserClass"] = $objUserData;
			$arrResult[$intUserId]["arrUserCampaigns"] = $arrUserCampaign = $objCampaigns->_user_campaigns_select(array(
				"user_id" => $intUserId
			));
			foreach ($arrUserCampaign as $intUserCampaignKey => $objUserCampaign)
			{
				if (!isset($objUserCampaign->task_id) || !$objUserCampaign->task_id)
					continue;
				if (!isset($arrCacheCampaigns[$objUserCampaign->campaign_id]))
					$arrCacheCampaigns[$objUserCampaign->campaign_id] = first($objCampaigns->_campaigns_select(array(
						"campaign_id" => $objUserCampaign->campaign_id
					)));
				if (!$arrCacheCampaigns[$objUserCampaign->campaign_id])
					continue;
				if (!isset($arrCacheTasks[$objUserCampaign->task_id]))
					$arrCacheTasks[$objUserCampaign->task_id] = first($objTasks->_tasks_select(array(
						"task_id" => $objUserCampaign->task_id
					)));
				if (!$arrCacheTasks[$objUserCampaign->task_id])
					continue;
				$arrCacheUsers[$intUserId] = first($objUsers->_users_select(array(
					"user_id" => $intUserId
				)));
				$arrData = array();
				if (isset($objUserData->user_class_id))
					$arrData["objUserClass"] = $objUserData;
				$arrData["objUserCampaign"] = $objUserCampaign;
				if (!isset($arrDataSet[$intUserId][$objUserCampaign->campaign_id][$objUserCampaign->task_id]))
					$arrDataSet[$intUserId][$objUserCampaign->campaign_id][$objUserCampaign->task_id] = 0;
				$arrDataSet[$intUserId][$objUserCampaign->campaign_id][$objUserCampaign->task_id] += $objUserCampaign->points_given;
			}
		}
		$this->view->arrCacheCampaigns = $arrCacheCampaigns;
		$this->view->arrCacheTasks = $arrCacheTasks;
		$this->view->arrCacheUsers = $arrCacheUsers;
		$this->view->arrResult = $arrDataSet;
	}

	public function reportusersAction()
    {
		$this->view->class_id = $intClass = intval($this->_request->getParam("class_id"));
		if (!$intClass)
		{
			print text("Sorry, there was an error") . ": CR-RU101-87DF6D";
			exit;
		}

		$objClasses = new Classes();
		$this->view->arrUsers = $objClasses->user_classes_select_user(array(
			"class_id" => $intClass
		));
	}

	public function reportcampaignsAction()
	{
		$objUsers = new Users();

		if ($this->_roles->isRole("Parent"))
		{
			$arrUsers = $objUsers->parents_children(array(
				"user_id" => $this->_user_session_data->user_id
			));
		}
		else
		{
			$this->view->class_id = $intClass = intval($this->_request->getParam("class_id"));
			$this->view->user_id = $intUser = intval($this->_request->getParam("user_id"));

			if ($intClass)
			{
				$objClasses = new Classes();

				$arrUsers = $objClasses->user_classes_select_user(array(
					"class_id" => $intClass
				));
			}
			else
			{
				if (!$intUser)
				{
					// If no class was defined and no user id either then something went wrong
					print text("Sorry, there was an error") . ": CR-VR101-F88DF6";
					exit;
				}
				$arrUsers = $objUsers->_users_select(array(
					"user_id" => $intUser
				));
			}
		}

		$objCampaigns = new Campaigns();

		$arrUserList = array();
		foreach ($arrUsers as $objUser)
		{
			$arrUserList[] = $objUser->user_id;
		}

		$this->view->arrCampaigns = $arrCampaigns = $objCampaigns->user_enrolled_campaigns(array(
			"user_ids" => $arrUserList
		));
	}

	public function viewreportAction()
	{
		$objUsers = new Users();
		$objClasses = new Classes();
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objScheduler = new Scheduler();
		$objLadders = new Ladders();
		$objMissions = new Missions();
		$objInstitutions = new Institutions();
		$objMarking = new Marking();

		$intMarkWeeks = $this->view->intMarkWeeks = 3;

		$intStartDate = capture_start_date;
		$intEndDate = capture_end_date;
		//$intStartDate = mktime(0,0,0,1,1,200);
		//$intEndDate = time();
		$arrParams = $this->view->arrParams = $this->_request->getParams();
		if (!isset($arrParams["start_date"]))
			$arrParams["start_date"] = date('m/d/Y', $intStartDate);
		if (!isset($arrParams["end_date"]))
			$arrParams["end_date"] = date('m/d/Y', $intEndDate);
		$arrParams["date_range"] = 1;
		$this->view->arrParams = $arrParams;
		if (isset($arrParams["date_range"]))
		{
			if (
				// month, day, year
				preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", urldecode($arrParams["start_date"]), $arrStartDateMatched)
				&& preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", urldecode($arrParams["end_date"]), $arrEndDateMatched)
			) {
				$intStartDate = mktime(0, 0, 0, $arrStartDateMatched[1], $arrStartDateMatched[2], $arrStartDateMatched[3]);
				$intEndDate = mktime(24, 0, 0, $arrEndDateMatched[1], $arrEndDateMatched[2], $arrEndDateMatched[3]);
			}
		}

		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CR-VR101-SDF76D";
			exit;
		}

		if ($this->_roles->isRole("Parent"))
		{
			$arrUsers = $objUsers->parents_children(array(
				"user_id" => $this->_user_session_data->user_id
			));
			$arrUsersIds = array_hash("user_id", $arrUsers);
			$arrUsers = $objUsers->_users_select_hierarchal(array(
				"user_id" => array_keys($arrUsersIds),
				"institution_id" => $this->_user_session_data->institution_id
			));
		}
		else if ($this->_roles->isRole("Teacher"))
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
			$arrUsers = $objUsers->_users_select(array(
				"user_id" => $arrUserIds
			));
		}
		else
		{
			// Allow user selection by either class or individuals
			$this->view->class_id = $intClass = intval($this->_request->getParam("class_id"));
			$this->view->user_id = $intUser = intval($this->_request->getParam("user_id"));

			// Collect all the user ids if a class was selected
			if (!$intUser && $intClass)
			{
				$objClasses = new Classes();
				$arrUsers = $objClasses->user_classes_select_user(array(
					"class_id" => $intClass
				));
			}
			else
			{
				$objUsers = new Users();
				if ($intUser)
				{
					$arrUsers = $objUsers->_users_select(array(
						"user_id" => $intUser
					));
				} else {
					$arrUsers = $objUsers->_users_select_hierarchal(array(
						'institution_id' => $this->_user_session_data->institution_id
					));
				}
			}
		}
		$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
			'user_id' => array_stack('user_id', $arrUsers)
		)));
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => array_stack('class_id', $arrUserClasses)
		)));
		// Load the campaign
		$this->view->objCampaign = $objCampaign = current($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objCampaign)
		{
			print text("Sorry, there was an error") . ": CR-VR104-87DFGS";
			exit;
		}

		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));

		// Loop though the users collected and derive their schedules
		$arrReports = array();
		foreach ($arrUsers as $objUser)
		{
			$objUserCampaign = current($objCampaigns->_user_campaigns_select(array(
				"status" => "Enrollment",
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign
			)));
			if ($objUserCampaign)
			{
				// Load the book schedule
				$arrSchedule = $objScheduler->load_book_medals(array(
					"user_id" => $objUser->user_id,
					"campaign_id" => $intCampaign,
					"load_missions" => true,
					//"capture_end_mission" => 52,
					//"capture_start_date" => capture_start_date,
					"capture_end_date" => $intEndDate,
					"kiosk" => true
				));

				$intLadderVelocity = $objLadders->campaign_user_ladder_velocity(array(
					"user_id" => $objUser->user_id,
					"campaign_id" => $intCampaign,
					"institution_id" => $this->_user_session_data->institution_id
				));
				$objSchedulingParams = current($objScheduler->_scheduling_params_select(array(
					"mission_id" => $objMission->mission_id,
					"task_id" => 0
				)));
				$arrLastSchedule = $objScheduler->load_book_schedule(array(
					"user_id"		=> $objUser->user_id,
					"mission_id"	=> $objMission->mission_id,
					"ladder" => $objUserCampaign->ladder,
					"capture_end_date" => $intEndDate-1 //mktime(0, 0, 0, 0, 0, date("Y")+1)-1
				));

				$arrLastSchedule = array_pop($arrLastSchedule);
				$objInstitution = current($objInstitutions->_institutions_select(array(
					"institution_id" => $objUserCampaign->institution_id
				)));

				$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
					"user_id" => $objUser->user_id,
					"campaign_id" => $intCampaign
				));

				for ($intItr=0; $intItr!=$intMarkWeeks+1; $intItr++)
				{
					array_pop($arrPendingMissions);
				}
				$arrCurrentMission = array_pop($arrPendingMissions);
				$arrCurrentMissionHash[$arrCurrentMission["epoch"]] = 1;
				//var_dump($arrPendingMissions);exit;
				$arrPendingHash = array();
				foreach ($arrPendingMissions as $arrMission)
				{
					$arrPendingHash[$arrMission["epoch"]] = 1;
				}

				$objUserCompletedCampaign = current($objCampaigns->_user_campaigns_select(array(
					"mission_id" => $objMission->mission_id,
					"user_id" => $objUser->user_id,
					"status" => "Completed",
					"_LIMIT" => 1
				)));
				$intLatestMission = $objUserCompletedCampaign ? $objUserCompletedCampaign->mission_increment : -1;
				$intTotalMissions = 0;
				$arrNewSchedule = array();
				foreach ($arrSchedule as $arrMedal)
				{
					$arrNewMissions = array();
					foreach ($arrMedal['missions'] as $arrMission)
					{
						if ($arrMission['epoch'] >= $intStartDate)
						{
							$arrNewMissions[] = $arrMission;
						}
					}
					if (count($arrNewMissions))
					{
						$arrNewSchedule[] = $arrMedal;
						$arrNewSchedule[count($arrNewSchedule)-1]['missions'] = $arrNewMissions;
					}
				}

				// Collect the data
				$arrReports[] = array(
					"user" => $objUser,
					'objClass' => $arrClasses[$arrUserClasses[$objUser->user_id]->class_id],
					"schedule" => $arrNewSchedule,
					"ladder_velocity" => $intLadderVelocity,
					"objSchedulingParams" => $objSchedulingParams,
					"arrYearEndSchedule" => $arrLastSchedule,
					"objInstitution" => $objInstitution,
					"arrPendingHash" => $arrPendingHash,
					"intLatestMission" => $intLatestMission,
					"arrCurrentMissionHash" => $arrCurrentMissionHash
				);
			}
		}

		// Send to results to view
		$this->view->arrReports = $arrReports;
	}
}
?>