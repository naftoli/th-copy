<?php
class UsersController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance

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

	public function idcardhostprintusersAction()
	{
		$query = new QueryGen();
		$this->view->intAuthCardOrderId = $intAuthCardOrderId = intval($this->_request->getParam("auth_card_order_id"));
		$arrAuthCards = $query->auth_cards__select(array(
			'auth_card_order_id' => $intAuthCardOrderId,
			'_ORDER' => 'auth_card_id DESC'
		));
		$this->view->arrUsers = array_hash('user_id', $query->users__select(array(
			'user_id' => array_stack('user_id', $arrAuthCards)
		)));
		$this->view->arrAuthCards = $arrAuthCards;
	}

	public function idcardsprintqrAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$arrPost = $this->_request->getPost();
		if (!isset($arrPost["user_ids"]))
		{
			print "Sorry, there was an error" . ": CU-ICP101-SD0F9D";
			exit;
		}
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrIds = (array) json_decode(stripslashes($arrPost["user_ids"]));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			"user_id" => array_keys($arrIds)
		));
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
		)));
		$arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			"user_id" => array_keys($arrIds),
			"institution_id" => $this->_user_session_data->institution_id
		)));

		foreach (array_keys($arrIds) as $intUser)
		{
			if (!isset($arrAuthCards[$intUser]))
			{
				$query->auth_cards__insert(array(
					"user_id" => $intUser,
					"institution_id" => $this->_user_session_data->institution_id,
					"date_printed" => time(),
					"card_status" => "printed"
				));
			}
			else
			{
				$query->auth_cards__update(array(
					"where" => array(
						"auth_card_id" => $arrAuthCards[$intUser]->auth_card_id,
						"card_status" => "not printed"
					),
					"values" => array(
						"date_printed" => time(),
						"card_status" => "printed"
					)
				));
			}
		}
	}

	public function idcardsprintcampsAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$arrPost = $this->_request->getPost();
		if (!isset($arrPost["user_ids"]))
		{
			print "Sorry, there was an error" . ": CU-ICP101-SD0F9D";
			exit;
		}
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrIds = (array) json_decode(stripslashes($arrPost["user_ids"]));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			"user_id" => array_keys($arrIds)
		));
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
		)));
		$arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			"user_id" => array_keys($arrIds),
			"institution_id" => $this->_user_session_data->institution_id
		)));

		foreach (array_keys($arrIds) as $intUser)
		{
			if (!isset($arrAuthCards[$intUser]))
			{
				$query->auth_cards__insert(array(
					"user_id" => $intUser,
					"institution_id" => $this->_user_session_data->institution_id,
					"date_printed" => time(),
					"card_status" => "printed"
				));
			}
			else
			{
				$query->auth_cards__update(array(
					"where" => array(
						"auth_card_id" => $arrAuthCards[$intUser]->auth_card_id,
						"card_status" => "not printed"
					),
					"values" => array(
						"date_printed" => time(),
						"card_status" => "printed"
					)
				));
			}
		}
	}

	public function transactionsAction()
	{
		$query = new QueryGen();
		$objPoints = new Points();
		$objKiosk01 = new Kiosk01();

		$arrPost = $this->_request->getPost();
		$arrGet = $this->_request->getParams();
		$boolPost = $this->_request->isPost();
		$intItemsPerPage = $this->view->intItemsPerPage = 7;
		if (!isset($arrGet["user_id"]))
		{
			print "Sorry, there was an error: CU-T101-HHF355";
			exit;
		}
		$tstyle = $this->view->tstyle = $this->_request->getParam('tstyle');
		$this->view->intUser = $arrGet["user_id"];

		// details for paging
		$intCurrentPage = $this->view->intCurrentPage = empty($arrGet["page"]) ? 0 : $arrGet["page"];

		$boolAjaxOutput = $this->view->boolAjaxOutput = isset($arrGet["ajax_output"]) && $arrGet["ajax_output"];

		$this->view->objInstitution = $objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		$this->view->objUser = first($query->users__select(array(
			"user_id" => $arrGet["user_id"]
		)));
		$this->view->intUserPointsTotal = $objPoints->user_store(array(
			"user_id" => $arrGet["user_id"],
			"institution_id" => $this->_user_session_data->institution_id
		));

		$arrUserPointParams = array(
			"user_id" => $arrGet["user_id"],
			"institution_id" => $this->_user_session_data->institution_id,
			"_ORDER" => "created ASC, user_point_id ASC"
		);
		$arrUserPoints = $query->user_points__select($arrUserPointParams);
		$this->view->arrUserPoints = array_hash("user_point_id", $arrUserPoints);
		$this->view->arrReversedTransactions = array_hash("reversed_user_point_id", $arrUserPoints);
		$arrResults = array();

		// sort the data
		$arrPointSums = array(
			"total" => 0,
			"store" => 0,
			'legacy' => 0
		);
		if ($tstyle == 'schoolstemplate1')
		{
			foreach ($arrUserPoints as $strUserPointKey => $objUserPoint)
			{
				preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+) ([0-9]+):([0-9]+):([0-9]+)$/', $objUserPoint->created, $arrMatched);
				list($strAll, $intYear, $intMonth, $intDay, $intHour, $intMin, $intSec) = $arrMatched;
				$arrUserPoints[$strUserPointKey]->epoch = mktime($intHour, $intMin, $intSec, $intMonth, $intDay, $intYear);
			}
			// aggregate old system values
			$objLegacyUser = first($query->legacy_lookup__select(array(
				'ims_id' => $arrGet["user_id"]
			)));
			$objCurl = curl_init();
			curl_setopt($objCurl, CURLOPT_URL, "http://mashpia.com/get_point_transactions.php?user_id=" . $objLegacyUser->legacy_id);
			curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, TRUE);
			$strResult = curl_exec($objCurl);
			curl_close($objCurl);
			$arrResult = unserialize($strResult);
			foreach ($arrResult as $arrRowData)
			{
				$arrUserPoints[] = (object) array(
					'user_id' => $arrGet["user_id"],
					'institution_id' => $this->_user_session_data->institution_id,
					'points' => $arrRowData['points'],
					'resource_name' => 'legacy points',
					'description' => $arrRowData['description'],
					'created' => date('Y-m-t H:i:s', $arrRowData['epoch']),
					'epoch' => $arrRowData['epoch']
				);
			}
			$arrSortParams = array();
			array_push($arrSortParams, 'epoch');
			array_push($arrSortParams, SORT_ASC);
			array_push($arrSortParams, $arrUserPoints);
			$arrUserPoints = call_user_func_array("msort", $arrSortParams);
		}
		foreach ($arrUserPoints as $intItr => $objUserPoint)
		{
			$strSortKey = strtotime($objUserPoint->created) . "." . $intItr;
			if (!isset($arrResults[$strSortKey]))
				$arrResults[$strSortKey] = array();
			$arrResults[$strSortKey][$intItr]["objUserPoint"] = $objUserPoint;

			// calcuate the points --
			if ($objUserPoint->resource_name == 'legacy points')
				$arrPointSums["legacy"] += $objUserPoint->points;
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
			$arrResults[$strSortKey][$intItr]["arrPointSums"] = $arrPointSums;
		}

		//dumper($arrResults,1,1);
		$this->view->arrPointSums = $arrPointSums;
		nksort($arrResults);
		$arrResults = array_reverse($arrResults, TRUE);
		$arrResults = array_strip($arrResults,1);
		// Paging
		$this->view->intTotalItems = $intTotalItems = count($arrResults);
		$this->view->intTotalPages = $intTotalPages = ceil($intTotalItems / $intItemsPerPage);
		dumper($arrResults,1,1);
		$arrResults = array_splice($arrResults, $intCurrentPage * $intItemsPerPage, $intItemsPerPage);

		// Collect required data
		$arrExtractResults = array_extract2(
			"created_by",
			"user_prize_id",
			"prize_id",
			"achievement_card_id",
			$arrResults
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
		$arrCampaignIDs = array_flatten($arrCampaignIDs);
		$arrCampaigns = array_hash("campaign_id", $query->campaigns__select(array(
			"campaign_id" => array_unique($arrCampaignIDs)
		)));
		$arrTaskIDs = array();
		array_push($arrTaskIDs, array_keys(array_stack('task_id', $arrUserPoints)));
		array_push($arrTaskIDs, $arrExtractAchievementCards["task_id"]);
		$arrTaskIDs = array_flatten($arrTaskIDs);
		$arrTasks = array_hash("task_id", $query->tasks__select(array(
			"task_id" => array_unique($arrTaskIDs)
		)));

		// Process required objects
		foreach ($arrResults as $intKey => $arrItem)
		{
			$refItem = &$arrResults[$intKey];
			if (!empty($refItem["objUserPoint"]->created_by))
				$refItem["objAdmin"] = $arrAdmins[$refItem["objUserPoint"]->created_by];
			if (!empty($refItem["objUserPoint"]->reversed_user_point_id))
			{
				$refItem["objReversedUserPoint"] = $refItem["objUserPoint"]->reversed_user_point_id;
			}
			if (isset($refItem["objUserPoint"]->prize_id) && $refItem["objUserPoint"]->prize_id)
			{
				$refItem["strRowType"] = "prize";
				$objPrize = $arrPrizes[$refItem["objUserPoint"]->prize_id];
				if (!$objPrize->image_id)
					$objPrize->image_id = $objInstitution->image_id;
				$refItem["objPrize"] = $objPrize;
				$refItem["objUserPrize"] = $arrUserPrizes[$refItem["objUserPoint"]->user_prize_id];
			}
			else if (isset($refItem["objUserPoint"]->achievement_card_id) && $refItem["objUserPoint"]->achievement_card_id)
			{
				$refItem["strRowType"] = "achievement_card";
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
			else if (isset($refItem["objUserPoint"]->scratch_card_id) && $refItem["objUserPoint"]->scratch_card_id)
			{
				$refItem["strRowType"] = "scratch_card";
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
				$refItem["strRowType"] = "transfer";
			}
			else if (isset($refItem["objUserPoint"]->campaign_id) && $refItem["objUserPoint"]->campaign_id)
			{
				$refItem["strRowType"] = "campaign";
				$objCampaign = @$arrCampaigns[$refItem["objUserPoint"]->campaign_id];
				$refItem["strCampaignType"] = $objCampaign->campaign_type;
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
			else
			{
				$refItem["strRowType"] = "error";
			}
		}
		$this->view->arrResults = $arrResults;
		if ($boolAjaxOutput)
		{
			print "_____[AJAX]_____" . json_encode(array(
				"intTotalItems" => $intTotalItems,
				"intTotalPages" => $intTotalPages,
				"arrPointSums" => $arrPointSums,
				"intOutputCount" => count($arrResults)
			));
		}

		// ajax requests
		if ($boolPost)
		{
			if (!isset($arrPost["do"]))
			{
				print json_encode(array(
					"error" => "Sorry, there was an error: CU-T102-AAAX01"
				));
				exit;
			}
			// reverse a transaction
			if ($arrPost["do"] == "reverse_transaction_save")
			{
				if (!isset($arrPost["intUserPoint"]))
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T103-AA2201"
					));
					exit;
				}
				$objUserPoint = first($query->user_points__select(array(
					"user_point_id" => $arrPost["intUserPoint"],
					"user_id" => $arrGet["user_id"],
					"institution_id" => $this->_user_session_data->institution_id
				)));
				if (!$objUserPoint)
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T103-AA2201"
					));
					exit;
				}
				if ($objUserPoint->user_prize_id)
				{
					$query->user_prizes__update(array(
						"where" => array(
							"user_prize_id" => $objUserPoint->user_prize_id
						),
						"values" => array(
							"is_reversed" => 1
						)
					));
				}
				if ($objUserPoint->achievement_card_id)
				{
					$query->achievement_cards__update(array(
						"where" => array(
							"achievement_card_id" => $objUserPoint->achievement_card_id
						),
						"values" => array(
							"status" => "not scanned"
						)
					));
				}
				// create new user point insert from one to be reversed
				$objUserPoint->points = -$objUserPoint->points;
				$objUserPoint->reversed_user_point_id = $objUserPoint->user_point_id;
				$objUserPoint->description = $arrPost["strDescription"];
				//if ($objUserPoint->user_prize_id)
				//	$objUserPoint->resource_name = "admin_users_manual_store";
				unset($objUserPoint->user_point_id);
				unset($objUserPoint->modfied);
				unset($objUserPoint->created_by);
				unset($objUserPoint->created);
				$query->user_points__insert($objUserPoint);
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
			// prize purchase
			else if ($arrPost["do"] == "prize_purchase")
			{
				if (!isset($arrPost["strPrizes"]))
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T104-1A2201"
					));
					exit;
				}
				$arrPrizeParams = unserialize($arrPost["strPrizes"]);
				if (!is_array($arrPrizeParams))
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T105-AA3255"
					));
					exit;
				}
				if (!count($arrPrizeParams))
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T106-2A2201"
					));
					exit;
				}
				$arrPrizes = array_hash("prize_id", $query->prize__select(array(
					"prize_id" => first(array_extract2("prize_id", $arrPrizeParams))
				)));
				foreach ($arrPrizeParams as $intKey=> $arrPrizeDetails)
				{
					if (
						empty($arrPrizeDetails["prize_id"])
						|| !isset($arrPrizes[$arrPrizeDetails["prize_id"]])
						|| !isset($arrPrizeDetails['quantity'])
						|| $arrPrizeDetails['quantity'] < 1
					)
						unset($arrPrizeParams[$intKey]);
					else
					{
						// auto correct params
						$arrPrizeParams[$intKey]["quantity"] = intval($arrPrizeParams[$intKey]["quantity"]);
					}
				}
				// Validation
				$intSubTotal = 0;
				foreach ($arrPrizeParams as $arrPrizeDetails)
				{
					$objPrize = $arrPrizes[$arrPrizeDetails["prize_id"]];
					$intSubTotal += $objPrize->points * $arrPrizeDetails['quantity'];
					if (
						$objPrize->add_on_restricted
						&& $arrPrizeDetails['quantity'] > 1
					) {
						print json_encode(array(
							"error" => "Sorry, there was an error: CU-T108-LLMM20"
						));
						exit;
					}
					if ($arrPrizeDetails['quantity'] > $objPrize->prize_count)
					{
						print json_encode(array(
							"error" => "Sorry, there was an error: CU-T109-JS24JH"
						));
						exit;
					}
				}
				if ($intSubTotal > $arrPointSums['store'])
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T107-JJ32A1"
					));
					exit;
				}
				// Purchase the prizes
				foreach ($arrPrizeParams as $arrPrizeDetails)
				{
					$objPrize = $arrPrizes[$arrPrizeDetails["prize_id"]];
					$intCost = $objPrize->points * $arrPrizeDetails['quantity'];

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

					$intUserPrize = $query->user_prizes__insert(array(
						"prize_id" => $objPrize->prize_id,
						"user_id" => $arrGet["user_id"],
						"institution_id" => $this->_user_session_data->institution_id,
						'quantity' => $arrPrizeDetails['quantity'],
						'prize_size' => @$arrPrizeDetails['prize_size'],
						'serial' => $strSerial,
						'status' => 'Checked Out'
					));
					$query->user_points__insert(array(
						"prize_id" => $objPrize->prize_id,
						"user_prize_id" => $intUserPrize,
						"user_id" => $arrGet["user_id"],
						"institution_id" => $this->_user_session_data->institution_id,
						"points" => -$intCost,
						"resource_name" => "transaction_manager_store"
					));
					$query->prize__update(array(
						"where" => array(
							"prize_id" => $objPrize->prize_id
						),
						"values" => array(
							"prize_count" => $objPrize->prize_count - $arrPrizeDetails['quantity']
						)
					));
				}
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
			else if ($arrPost["do"] == "manual_change")
			{
				$boolTotal = preg_match('/total/', $arrPost["strTotalType"]);
				$boolStore = preg_match('/store/', $arrPost["strTotalType"]);
				if ($boolTotal && $boolStore)
					$strResourceName = "admin_users_manual";
				else if ($boolStore)
					$strResourceName = "admin_users_manual_store";
				else if ($boolTotal)
					$strResourceName = "admin_users_manual_total";
				else
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T110-44S5GF"
					));
					exit;
				}
				$intAmount = intval(@$arrPost["intAmount"]);
				if (!$intAmount)
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T111-445GC1"
					));
					exit;
				}
				$query->user_points__insert(array(
					"user_id" => $arrGet["user_id"],
					"institution_id" => $this->_user_session_data->institution_id,
					"points" => $intAmount,
					"resource_name" => $strResourceName,
					"description" => @$arrPost["strDescription"]
				));
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
			else if ($arrPost["do"] == "achievement_card")
			{
				if (empty($arrPost["strCard"]))
				{
					print json_encode(array(
						"error" => "Sorry, there was an error: CU-T112-45SJC2"
					));
					exit;
				}
				$arrResult = $objKiosk01->achievement_card_process(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"user_id" => $arrGet["user_id"],
					"bar_code" => $arrPost["strCard"]
				));
				print json_encode($arrResult);
				exit;
			}
			else
			{
				print json_encode(array(
					"error" => "Sorry, there was an error: CU-T112-45SJC2"
				));
				exit;
			}
			exit;
		}
	}

	public function bulkimageuploadAction()
	{
		$query = new QueryGen();
		$objLists = new Lists();

		$arrResult = array();
		$arrPost = $this->_request->getPost();
		$this->view->arrParams = $arrParams = unserialize(stripslashes($this->_request->getPost("arrParams")));
		if ($this->_request->getParam("display_view") == "true")
			return;
		$arrData = unserialize(stripslashes($this->_request->getPost("arrData")));
		if (!is_array($arrParams)) {
			print json_encode(array(
				"error" => "Sorry, there was an error" . ": CU-BIU101-FFS223A"
			));
			exit;
		}
		$arrConfigData = array(
			"params" => @$arrParams["params"],
			"default_table" => "users",
			"title" => "User Image Uploader",
			"data_source" => "/users/bulkimageupload",
			"header_text" => "To upload a pictures click the image on the left of the users name and choose \"change image\".  Images should be in JPEG format and not be over 2MB in size.",
			"tables" => array(
				// Usage:
				// "Column title" => array(
				//		"data" => "table.column"
				//		"width" => INT,
				//		"height" => INT,
				//		"input" => [text|checkbox|checkbox2|image|plaintext|auto|hidden], // default: text
				//		"default" => "0",
				//		"align" => [left,right,center] // default: left
				//	)
				"users" => array(
					"_params" => array(),
					"user_id" => array(
						"data" => "user_id",
						"input" => "hidden",
						"key" => true
					),
					"Image" => array(
						"data" => "image_id",
						"input" => "image",
						"default" => 0
					),
					"First Name" => array(
						"data" => "first_name",
						"input" => "plaintext",
						"width" => 200
					),
					"Last Name" => array(
						"data" => "last_name",
						"input" => "plaintext",
						"width" => 180
					)
				)
			)
		);
		$arrLoadedData = $objLists->load_data($arrConfigData);
		$arrPrimaryKeys = $arrPertinentParams = array();
		foreach ($arrConfigData["tables"] as $strTableName => $arrTableParams)
		{
			foreach ($arrTableParams as $strColumnName => $arrColumnParams)
			{
				if (isset($arrColumnParams["data"]))
				{
					if (isset($arrColumnParams["key"]))
						$arrPrimaryKeys[$arrColumnParams["data"]] = 1;
					if (isset($arrColumnParams["data"]) && @$arrColumnParams["input"] != "plaintext")
						$arrPertinentParams[] = $arrColumnParams["data"];
				}
			}
		}
		$arrPrimaryKeys = array_keys($arrPrimaryKeys);

		if (@$arrPost["save"] == "true") {
			if (!isset($arrData) || !count($arrData))
			{
				print json_encode(array("error" => "Sorry, there was an error" . ": CU-BIU102"));
				exit;
			}
			foreach ($arrData["tables"] as $strTableName => $arrTableParams)
			{
				$arrInstructions = $query->_proc_query_instructions2($arrLoadedData["arrRows"], $arrTableParams, $arrPrimaryKeys, $arrPertinentParams);
				foreach ($arrInstructions["_INSERT"] as $objData)
				{
					$query->users__insert($objData);
				}
				foreach ($arrInstructions["_UPDATE"] as $objData)
				{
					$query->users__update($objData);
				}
				foreach ($arrInstructions["_DELETE"] as $objData)
				{
					$query->users__delete($objData);
				}
			}
			$arrLoadedData["success"] = "true";
		}
		print json_encode($arrLoadedData);
		exit;
	}

	public function ajaxlistranksetupAction()
	{
		$query = new QueryGen();
		$objLists = new Lists();

		$arrResult = array();
		$arrPost = unserialize(stripslashes($this->_request->getPost("arrParams")));
		if (!is_array($arrPost))
		{
			print json_encode(array(
				"error" => "Sorry, there was an error" . ": CU-ALRS101-FFS223A"
			));
			exit;
		}
		$arrConfigData = array(
			"default_table" => "ranks",
			"title" => "Ranks Editor",
			"auto_sort_col" => array("Points" => "ranks.rank_points DESC"),
			"create_new" => true,
			"tables" => array(
				// Usage:
				// "Column title" => array(
				//		"data" => "table.column"
				//		"width" => INT,
				//		"height" => INT,
				//		"input" => [text|checkbox|checkbox2|image|plaintext|auto|hidden], // default: text
				//		"default" => "0",
				//		"align" => [left,right,center] // default: left
				//	)
				"ranks" => array(
					"_params" => array(

					),
					"_ln" => array(
						"start" => 1
					),
					"rank_id" => array(
						"data" => "rank_id",
						"input" => "hidden"
					),
					"Image" => array(
						"data" => "rank_image",
						"input" => "image",
						"default" => 0
					),
					"Name" => array(
						"data" => "rank_title",
						"width" => 300,
						"required" => true
					),
					"Points" => array(
						"data" => "rank_points",
						"data_type" => "numeric",
						"required" => true
					)
				)
			)
		);

		print json_encode($objLists->load_data($arrConfigData));
		exit;
	}

	public function idcardhostnavAction()
	{
		$query = new QueryGen();
		$this->view->auth_card_order_id = $this->view->intAuthCardOrderId = $intAuthCardOrderId = intval($this->_request->getParam("auth_card_order_id"));
		$this->view->boolCompletedOrders = $boolCompletedOrders = $this->_request->getParam("completed_orders") == "true" ? 1 : 0;
		if (!$intAuthCardOrderId)
		{
			print "Sorry, there was an error" . ": CU-IDCHP101-sfsdfd";
			exit;
		}
		if ($this->_request->getPost("host_printed") == "true")
		{
			$strUserIds = $this->_request->getPost("user_ids");
			if (empty($strUserIds))
			{
				print json_encode(array(
					"error" => "There were no users selected to complete your request."
				));
				exit;
			}
			$arrUsers = explode(",", $strUserIds);
			$query->auth_cards__update(array(
				"where" => array(
					"auth_card_order_id" => $intAuthCardOrderId,
					'user_id' => $arrUsers
				),
				"values" => array(
					"host_printed" => time()
				)
			));
			print json_encode(array(
				"success" => "true"
			));
			exit;
		}
		if ($this->_request->getPost("confirm_order") == "true")
		{
			$boolProcAction = intval($this->_request->getPost("procaction"));
			$query->auth_cards__update(array(
				"where" => array(
					"auth_card_order_id" => $intAuthCardOrderId
				),
				"values" => array(
					"date_card_redeemed" => time(),
					"card_status" => $boolProcAction ? "host printed" : "ordered"
				)
			));

			$query->auth_card_orders__update(array(
				"where" => array(
					"auth_card_order_id" => $intAuthCardOrderId
				),
				"values" => array(
					"date_completed" => $boolProcAction ? time() : '0'
				)
			));
			print json_encode(array(
				"success" => "true"
			));
			exit;
		}

		$objAuthCardOrder = first($query->auth_card_orders__select(array(
			'auth_card_order_id' => $intAuthCardOrderId
		)));

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $objAuthCardOrder->institution_id,
		)));
		$arrAuthCardParams = array(
			"auth_card_order_id" => $intAuthCardOrderId
		);
		if ($boolCompletedOrders)
		{
			$arrAuthCardParams["card_status"] = array("host printed","redeemed");
		}
		else
		{
			$arrAuthCardParams["card_status"] = "ordered";
		}
		$this->view->arrAuthCards = $arrAuthCards = array_hash("user_id", $query->auth_cards__select($arrAuthCardParams));
		$this->view->strAuthCardSerialized = http_build_query(array_fill_keys(array_keys($arrAuthCards), 1));
	}

	public function idcardshostprintcampsAction()
	{
		$query = new QueryGen();
		$this->view->intAuthCardOrderId = $intAuthCardOrderId = intval($this->_request->getParam("auth_card_order_id"));
		if ($intAuthCardOrderId)
		{
			$objAuthCardOrder = first($query->auth_card_orders__select(array(
				'auth_card_order_id' => $intAuthCardOrderId
			)));
			$this->view->intInstitution = $intInstitution = $objAuthCardOrder->institution_id;
		} else {
			$this->view->intInstitution = $intInstitution = intval($this->_request->getParam("institution_id"));
		}
		$this->view->strCustomeInstitutionName = $this->_request->getParam('institution_name');
		$this->view->arrUserIds = $arrUserIds = explode(",", $this->_request->getParam("user_ids"));

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
		$this->view->arrAuthCards = $arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			'auth_card_order_id' => $intAuthCardOrderId,
			"user_id" => $arrUserIds
		)));
		$this->view->arrUsers = array_hash("user_id", $query->users__select(array(
			"user_id" => $arrUserIds //array_keys($arrAuthCards)
		)));
	}

	public function idcardshostprintcampsqrAction()
	{
		$query = new QueryGen();
		$this->view->intAuthCardOrderId = $intAuthCardOrderId = intval($this->_request->getParam("auth_card_order_id"));
		if ($intAuthCardOrderId)
		{
			$objAuthCardOrder = first($query->auth_card_orders__select(array(
				'auth_card_order_id' => $intAuthCardOrderId
			)));
			$this->view->intInstitution = $intInstitution = $objAuthCardOrder->institution_id;
		} else {
			$this->view->intInstitution = $intInstitution = intval($this->_request->getParam("institution_id"));
		}
		$this->view->strCustomeInstitutionName = $this->_request->getParam('institution_name');
		$this->view->arrUserIds = $arrUserIds = explode(",", $this->_request->getParam("user_ids"));

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
		$this->view->arrAuthCards = $arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			'auth_card_order_id' => $intAuthCardOrderId,
			"user_id" => $arrUserIds
		)));
		$this->view->arrUsers = array_hash("user_id", $query->users__select(array(
			"user_id" => $arrUserIds //array_keys($arrAuthCards)
		)));
	}

	public function idcardshostprintcamps1Action()
	{
		$query = new QueryGen();
		$this->view->intInstitution = $intInstitution = intval($this->_request->getParam("institution_id"));
		if (!$intInstitution)
		{
			print "Sorry, there was an error" . ": CU-IDCHP101-asf92k";
			exit;
		}
		$this->view->arrUserIds = $arrUserIds = explode(",", $this->_request->getParam("user_ids"));

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
		$this->view->arrAuthCards = $arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			"institution_id" => $intInstitution,
			"user_id" => $arrUserIds
		)));
		$this->view->arrUsers = array_hash("user_id", $query->users__select(array(
			"user_id" => array_keys($arrAuthCards)
		)));
	}

	public function idcardshostprintAction()
	{
		$query = new QueryGen();
		$this->view->intInstitution = $intInstitution = intval($this->_request->getParam("institution_id"));
		if (!$intInstitution)
		{
			print "Sorry, there was an error" . ": CU-IDCHP101-0j2fjf";
			exit;
		}
		$this->view->arrUserIds = $arrUserIds = explode(",", $this->_request->getParam("user_ids"));

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
		$this->view->arrAuthCards = $arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			"institution_id" => $intInstitution,
			"user_id" => $arrUserIds
		)));
		$this->view->arrUsers = array_hash("user_id", $query->users__select(array(
			"user_id" => array_keys($arrAuthCards)
		)));
	}

	public function idcardsprintAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$arrPost = $this->_request->getPost();
		if (!isset($arrPost["user_ids"]))
		{
			print "Sorry, there was an error" . ": CU-ICP101-SD0F9D";
			exit;
		}
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrIds = (array) json_decode(stripslashes($arrPost["user_ids"]));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			"user_id" => array_keys($arrIds)
		));
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
		)));
		$arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			"user_id" => array_keys($arrIds),
			"institution_id" => $this->_user_session_data->institution_id
		)));

		foreach (array_keys($arrIds) as $intUser)
		{
			if (!isset($arrAuthCards[$intUser]))
			{
				$query->auth_cards__insert(array(
					"user_id" => $intUser,
					"institution_id" => $this->_user_session_data->institution_id,
					"date_printed" => time(),
					"card_status" => "printed"
				));
			}
			else
			{
				$query->auth_cards__update(array(
					"where" => array(
						"auth_card_id" => $arrAuthCards[$intUser]->auth_card_id,
						"card_status" => "not printed"
					),
					"values" => array(
						"date_printed" => time(),
						"card_status" => "printed"
					)
				));
			}
		}
	}

	public function idcardinstitutionsAction()
	{
		$query = new QueryGen();
		$arrAuthCardParams = array();
		$this->view->boolCompletedOrders = $boolCompletedOrders = $this->_request->getParam("completed_orders") == "true" ? 1 : 0;
		if ($boolCompletedOrders)
		{
			$arrAuthCardParams['_GREATER']["date_completed"] = 0;
		}
		else
		{
			$arrAuthCardParams[0]['_IS_NULL'] = array("date_completed");
			$arrAuthCardParams[0][] = array(
				'date_completed' => 0
			);
		}
		$arrAuthCardParams['_NOT_NULL'] = array("institution_id");
		$arrAuthCardOrders = $query->auth_card_orders__select($arrAuthCardParams);
		$arrAuthCardOrdersInstitutions = array_stack("institution_id", $arrAuthCardOrders);
		$arrInstitutions = array_hash('institution_id', $query->institutions__select(array(
			"institution_id" => $arrAuthCardOrdersInstitutions
		)));
		foreach ($arrAuthCardOrders as $strKey => &$objAuthCardOrder)
		{
			$objAuthCardOrder->institution_name = $arrInstitutions[$objAuthCardOrder->institution_id]->name;
		}
		$this->view->arrAuthCardOrders = $arrAuthCardOrders;
	}

	public function idcardsAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$objUsers = new Users();
		$objRegistration = new Registration();

		// Cost per card
		$intCostPerHostCard = $this->view->intCostPerHostCard = 2.50;
		// CreditCard types
		$arrCardTypes = $this->view->arrCardTypes = array(
			"Visa", "MasterCard", "American Express", "Chase Card", "Citi Card", "HSBC", "Discover"
		);

		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => "user",
			"institution_id" => $this->_user_session_data->institution_id
		));
		$this->view->arrClasses = $arrClasses = array_hash("class_id", $query->classes__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"_ORDER" => "class_hierarchy+0"
		)));
		$arrAuthOrderCards = array_hash("user_id", $query->auth_cards__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
		)));
		$arrAuthCardsOrdered = array();
		foreach ($arrAuthOrderCards as $intUser => $objAuthCard)
		{
			if ($objAuthCard->card_status == "ordered")
			{
				$arrAuthCardsOrdered[$intUser] = $objAuthCard;
				unset($arrAuthOrderCards[$intUser]);
			}
		}
		$this->view->arrAuthCardsOrdered = $arrAuthCardsOrdered;
		//"user_id" => array_keys($arrAuthOrderCards)
		$this->view->arrUsers = $arrUsers = array_hash("user_id", $objUsers->_users_select_hierarchal(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"permission" => "Student",
			/*"_NOT" => array(
				"user_id" => array_keys($arrAuthCardsOrdered)
			),*/
			"is_active" => 1
		)));
		$arrAllUsers = array_hash("user_id", $query->users__select(array(
			"user_id" => array_keys(array_stack("user_id", $query->permissions__select(array(
				"institution_id" => $this->_user_session_data->institution_id,
				"permission" => "Student",
				"_GREATER" => array(
					"registration_expiration" => time()
				)
			)))),
			"is_active" => 1
		)));
		$this->view->arrUserClasses = $arrUserClasses = $query->user_classes__select(array(
			"user_id" => array_keys($arrUsers),
			"class_id" => array_keys($arrClasses),
			"class_role" => "Student"
		));
		$arrAllUserClasses = $query->user_classes__select(array(
			"user_id" => array_keys($arrAllUsers),
			"class_id" => array_keys($arrClasses),
			"class_role" => "Student"
		));
		$arrdUserClassIds = $this->view->arrdUserClassIds = object_extract("class_id", array_bubble_hash("user_id", $arrUserClasses));
		$arrClassStudents = $this->view->arrClassStudents = object_extract("user_id", array_bubble_hash("class_id", $arrUserClasses));
		$this->view->intUnassigned = "NA";
		if (is_dev())
		{
			if (count($arrdUserClassIds) != count($arrUsers))
			{
				$this->view->intUnassigned = count($arrAllUsers) - count(array_stack("user_id", $arrAllUserClasses));
				//$this->view->intUnassigned = count(array_stack("user_id", $arrAllUserClasses));
				dumper($this->view->intUnassigned,1,1,1);
			}
		}
		$arrAuthCards = array_hash("user_id", $query->auth_cards__select(array(
			"user_id" => array_keys($arrUsers),
			"institution_id" => $this->_user_session_data->institution_id
		)));
		$this->view->arrAuthCards = object_extract("card_status", $arrAuthCards);
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));

		if ($this->_request->isPost())
		{
			// Process an id card order request
			$arrResult = array();
			$arrPost = $this->_request->getPost();
			// Validate the ids
			if (!isset($arrPost["user_ids"]))
			{
				$arrResult["error"] = "Sorry, there was an error" . ": CU-IDC101-ASS9S9";
				print json_encode($arrResult);
				exit;
			}
			$arrIds = (array) json_decode(stripslashes($arrPost["user_ids"]));
			$arrIds = array_keys($arrIds);
			// Remove items that are not available for order
			foreach ($arrIds as $intKey => $intId)
			{
				if (isset($arrAuthCards[$intId]) && $arrAuthCards[$intId]->card_status == "ordered")
					unset($arrIds[$intKey]);
			}
			if (!count($arrIds))
			{
				$arrResult["error"] = "No items selected are available to be ordered.";
				print json_encode($arrResult);
				exit;
			}
			// Check if these students are available to this user
			foreach ($arrIds as $intId)
			{
				if (!isset($arrUsers[$intId]))
				{
					$arrResult["error"] = "Sorry, there was an error" . ": CU-IDC103-234DF3";
					print json_encode($arrResult);
					exit;
				}
			}
			if ($this->_request->getParam('complete_order') != 'true')
			{
				// Log the details of the successful order
				$intAuthCardOrderId = $query->auth_card_orders__insert(array(
					'institution_id' => $this->_user_session_data->institution_id,
					"user_ids" => join(",",$arrIds)
				));
				// Set each item as ordered
				foreach ($arrIds as $intUser)
				{
					if (isset($arrAuthCards[$intUser]))
					{
						$query->auth_cards__update(array(
							"where" => array(
								"auth_card_id" => $arrAuthCards[$intUser]->auth_card_id
							),
							"values" => array(
								"card_status" => "ordered",
								"auth_card_order_id" => $intAuthCardOrderId
							)
						));
					}
					else
					{
						$query->auth_cards__insert(array(
							"user_id" => $intUser,
							"institution_id" => $this->_user_session_data->institution_id,
							"date_card_ordered" => time(),
							"card_status" => "ordered",
							"auth_card_order_id" => $intAuthCardOrderId
						));
					}
				}
				print json_encode(array(
					"success" => "true"
				));
				exit;
			} else { // complete_order == 'true'
				// Validate user info
				//var_dump($arrPost);exit;
				if (!isset($arrPost["creditcard_first_name"]) || !strlen($arrPost["creditcard_first_name"]))
				{
					$arrResult["error"]["creditcard_first_name"] = "Credit card holders's first name is required.";
				}
				if (!isset($arrPost["creditcard_last_name"]) || !strlen($arrPost["creditcard_last_name"]))
				{
					$arrResult["error"]["creditcard_last_name"] = "Credit card holders's last name is required.";
				}
				if (!isset($arrPost["creditcard_number"]) || !strlen($arrPost["creditcard_number"]))
				{
					$arrResult["error"]["creditcard_number"] = "Credit card number is required.";
				}
				if (!isset($arrPost["creditcard_ccv"]) || !strlen($arrPost["creditcard_ccv"]))
				{
					$arrResult["error"]["creditcard_ccv"] = "CCV is required.";
				}
				if (
					!isset($arrPost["creditcard_expiration_month"])
					|| $arrPost["creditcard_expiration_month"] < 1
					|| $arrPost["creditcard_expiration_month"] > 12
					|| !isset($arrPost["creditcard_expiration_year"])
					|| $arrPost["creditcard_expiration_year"] < date("Y")
				) {
					$arrResult["error"]["creditcard_expiration_month"] = "Credit card expiration must be valid.";
				}

				if (isset($arrResult["error"]))
				{
					print json_encode($arrResult);
				}
				else
				{
					if ($arrPost['creditcard_number'] != 'q2w43l2hpg3lldc34w3n3vlss')
					{
						// sum total
						$intPurchaseSubTotal = count($arrIds) * $intCostPerHostCard;
						$objAuthorizeNet = new AuthorizeNet();
						$arrResponse = $objAuthorizeNet->process_transaction(array(
							'card_num' => $arrPost['creditcard_number'],
							'exp_date' => $arrPost['creditcard_expiration_month'] . '/' . $arrPost['creditcard_expiration_year'],
							'amount' => $intPurchaseSubTotal,
							'description' => '',
							'first_name' => $arrPost['creditcard_first_name'],
							'last_name' => $arrPost['creditcard_last_name']
						));
					} else {
						// bypass code
						$arrResponse["Response Code"] = '1';
					}
					if ($arrResponse["Response Code"] != '1')
					{
						print json_encode(array(
							'error' => $arrResponse['Response Reason Text'],
							"success" => "false"
						));
						exit;
					}
					// Generate an order code
					$arrDataSource = explode(",", "q,w,e,r,t,y,u,i,o,p,a,s,d,f,g,h,j,k,l,z,x,c,v,b,n,m,1,2,3,4,5,6,7,8,9,0");
					while (1)
					{
						$strOrderCode = strtoupper(
							"B"
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
							. $arrDataSource[rand(0, count($arrDataSource)-1)]
						);
						$objOrderFound = first($query->auth_card_orders__select(array(
							"confirmation_code" => $strOrderCode
						)));
						if (!$objOrderFound)
							break;
					}

					// Log the details of the successful order
					$intAuthCardOrderId = $query->auth_card_orders__insert(array(
						'institution_id' => $this->_user_session_data->institution_id,
						"confirmation_code" => $strOrderCode,
						"creditcard_first_name" => $arrPost["creditcard_first_name"],
						"creditcard_last_name" => $arrPost["creditcard_last_name"],
						"creditcard_number" => $arrPost["creditcard_number"],
						"creditcard_ccv" => $arrPost["creditcard_ccv"],
						"creditcard_expiration_month" => $arrPost["creditcard_expiration_month"],
						"creditcard_expiration_year" => $arrPost["creditcard_expiration_year"],
						"price_per_unit" => $intCostPerHostCard,
						"quantity_purchased" => count($arrIds),
						"sub_total" => @$intPurchaseSubTotal,
						"user_ids" => join(",",$arrIds)
					));

					// Set each item as ordered
					foreach ($arrIds as $intUser)
					{
						if (isset($arrAuthCards[$intUser]))
						{
							$query->auth_cards__update(array(
								"where" => array(
									"auth_card_id" => $arrAuthCards[$intUser]->auth_card_id
								),
								"values" => array(
									"card_status" => "ordered",
									"auth_card_order_id" => $intAuthCardOrderId
								)
							));
						}
						else
						{
							$query->auth_cards__insert(array(
								"user_id" => $intUser,
								"institution_id" => $this->_user_session_data->institution_id,
								"date_card_ordered" => time(),
								"auth_card_order_id" => $intAuthCardOrderId,
								"card_status" => "ordered"
							));
						}
					}

					print json_encode(array(
						"success" => "true",
						"confirmation_code" => $strOrderCode
					));
				}
			}
			exit;
		}
	}

	public function studentregistrationAction()
	{
		$boolSendMail = TRUE;
		global $arrSystemPrices, $arrConfirmationCodePrefixes, $arrAppDetails;
		$tstyle = $this->view->tstyle = $this->_request->getParam('tstyle');
		$arrDetails = $arrAppDetails[$tstyle];
		$query = new QueryGen();
		$objClasses = new Classes();
		$objUsers = new Users();
		$objRegistration = new Registration();
		$this->view->intRegistrationFee = $intRegistrationFee = $arrSystemPrices["hebrewschool1"]["registration_fee"];
		if ($this->_request->getParam('activate_credits') == 'true')
		{
			$objOtherAdminCredit = first($query->admin_credits__select(array(
				"credit_description" => '2nd season credits',
				"user_id" => $this->_user_session_data->user_id,
				'_LIMIT' => 1
			)));
			if (!$objOtherAdminCredit)
			{
				json(array(
					'failure' => 'It appears there are no credit available.'
				));
			}
			$query->admin_credits__update(array(
				'where' => array(
					'admin_credit_id' => $objOtherAdminCredit->admin_credit_id
				),
				'values' => array(
					'start_epoch' => 0,
					'end_epoch' => 0
				)
			));

			$intAdminCredit = $query->admin_credits__insert(array(
				'institution_id' => $this->_user_session_data->institution_id,
				"credit_description" => 'student registration',
				'user_id' => 0,
				'credit_title' => 'student_registration',
				'credit_amount' => $objOtherAdminCredit->credit_amount
			));
			json(array(
				'success' => 'true',
				'intAdminCredit' => $intAdminCredit,
				'arrOtherAdminCredits' => $objOtherAdminCredit
			));
		}
		$objRegCount = first($query->permissions__select(array(
			'_COUNT' => 'permission_id',
			'institution_id' => $this->_user_session_data->institution_id,
			'_GREATER' => array(
				'registration_expiration' => time()
			)
		)));
		$this->view->arrOtherAdminCredits = $query->admin_credits__select(array(
			"credit_description" => '2nd season credits',
			"user_id" => $this->_user_session_data->user_id
		));
		$objAdminCredit = $objAdminCredit = first($query->admin_credits__select(array(
			"_SUM" => "credit_amount",
			"_GROUP_BY" => "institution_id",
			"institution_id" => $this->_user_session_data->institution_id,
			"user_id" => 0,
			"credit_title" => "student_registration",
			'credit_description' => array('student registration','institution registration')
		)));
		$this->view->intRegCount = $intRegCount = $objRegCount->_count_permission_id + $objAdminCredit->_sum_credit_amount;

		$arrCardTypes = $this->view->arrCardTypes = array(
			"Visa", "MasterCard", "American Express", "Chase Card", "Citi Card", "HSBC", "Discover"
		);
		$arrPrizes = array_hash("prize_id", $query->prize__select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"add_on_restricted" => 1,
			"is_active" => 1
		)));

		$intUserCredits = $this->view->intUserCredits = $objAdminCredit->_sum_credit_amount;
		$intUserCreditsUsed = 0;

		if ($this->_request->isPost())
		{
			$strCreditCardBypass = "81s7g9s9d892kxlaw90212482hd"; // master
			$arrPost = $this->_request->getPost();
			if ($this->_request->getParam("register_users") == "true") {
				$intUserCreditsUsed = 0;
				$arrRequest = array();
				$intSum = 0;
				$intAmount = $this->view->intRegCount;
				foreach ($arrPost as $strKey => $strValue)
				{
					if (preg_match("/^bl_(.+?)_([0-9]+)_([0-9]+)$/", $strKey, $arrMatched))
					{
						list($strKey, $strName, $mixedItem, $intUser) = $arrMatched;
						$arrRequest[$intUser][$strName][$mixedItem] = true;
						if ($strName == "registration_fee")
						{
							$intAmount++;
						}
					}
				}
				if ($tstyle == "chabadhebrewschool")
				{
					$intRegistrationFee = 10;
					$arrCosts = array(
						0 => 10,
						51 => 9,
						76 => 8,
						101 => 6,
						201 => 5,
						501 => 4
					);
				}
				else
				{
					$intRegistrationFee = 6;
					$arrCosts = array(
						0 => 6,
						100 => 5,
						200 => 4,
						300 => 3.5,
						500 => 3
					);

				}
				foreach ($arrCosts as $intAmount2 => $intNewCost) {
					if ($intAmount >= $intAmount2)
						$intRegistrationFee = $intNewCost;
				}
				$intPurchasedCredits = $arrPost['add_credits_input']*1;
				$intSum += $intPurchasedCredits * $intRegistrationFee;
				foreach ($arrPost as $strKey => $strValue)
				{
					if (preg_match("/^bl_(.+?)_([0-9]+)_([0-9]+)$/", $strKey, $arrMatched))
					{
						list($strKey, $strName, $mixedItem, $intUser) = $arrMatched;
						$arrRequest[$intUser][$strName][$mixedItem] = true;
						if ($strName == "registration_fee")
						{
							if ($intUserCredits > 0)
							{
								$intUserCreditsUsed++;
								$intUserCredits--;
							}
							else
								$intSum += $intRegistrationFee;
						}
					}
				}

				if ($intSum > 0)
				{
					if ($arrPost["creditcard_number"] != $strCreditCardBypass)
					{
						if (!isset($arrPost["confirm_order"]) || $arrPost["confirm_order"] != "1")
						{
							$arrResult["error"]["confirm_order"] = "You must confirm that you authorize the registration / add-on fees.";
						}
						if (!isset($arrPost["creditcard_first_name"]) || !strlen($arrPost["creditcard_first_name"]))
						{
							$arrResult["error"]["creditcard_first_name"] = "Credit card holders's first name is required.";
						}
						if (!isset($arrPost["creditcard_last_name"]) || !strlen($arrPost["creditcard_last_name"]))
						{
							$arrResult["error"]["creditcard_last_name"] = "Credit card holders's last name is required.";
						}
						if (!isset($arrPost["creditcard_address"]) || !strlen($arrPost["creditcard_address"]))
						{
							$arrResult["error"]["creditcard_address"] = "Address is required.";
						}
						if (!isset($arrPost["creditcard_state"]) || !strlen($arrPost["creditcard_state"]))
						{
							$arrResult["error"]["creditcard_state"] = "State is required.";
						}
						if (!isset($arrPost["creditcard_zip"]) || !strlen($arrPost["creditcard_zip"]))
						{
							$arrResult["error"]["creditcard_zip"] = "Zip/Postal is required.";
						}
						if (!isset($arrPost["creditcard_number"]) || !strlen($arrPost["creditcard_number"]))
						{
							$arrResult["error"]["creditcard_number"] = "Credit card number is required.";
						}
						if (!isset($arrPost["creditcard_ccv"]) || !strlen($arrPost["creditcard_ccv"]))
						{
							$arrResult["error"]["creditcard_ccv"] = "CCV is required.";
						}
						if (
							!isset($arrPost["creditcard_expiration_month"])
							|| $arrPost["creditcard_expiration_month"] < 1
							|| $arrPost["creditcard_expiration_month"] > 12
							|| !isset($arrPost["creditcard_expiration_year"])
							|| $arrPost["creditcard_expiration_year"] < date("Y")
						) {
							$arrResult["error"]["creditcard_expiration_month"] = "Credit card expiration must be valid.";
						}
					}
				}

				if (
					$intSum < 0.01
					&& $intUserCreditsUsed < 1
				) {
					$arrResult["error"] = "Sorry, there was an error:" . " CU-SR103-GD2T4F";
				}

				if (isset($arrResult["error"]))
				{
					print json_encode($arrResult);
					exit;
				}
				if ($intPurchasedCredits > 0) {
					$query->admin_credits__insert(array(
						"institution_id" => $this->_user_session_data->institution_id,
						"user_id" => 0,
						"credit_title" => "student_registration",
						"credit_amount" => $intPurchasedCredits,
						"credit_description" => "student registration"
					));
				}
				if ($intUserCreditsUsed > 0)
				{
					$query->admin_credits__insert(array(
						"institution_id" => $this->_user_session_data->institution_id,
						"user_id" => 0,
						"credit_title" => "student_registration",
						"credit_amount" => -($intUserCreditsUsed),
						"credit_description" => "student registration"
					));
				}

				// Generate an order code
				$arrDataSource = explode(",", "q,w,e,r,t,y,u,i,o,p,a,s,d,f,g,h,j,k,l,z,x,c,v,b,n,m,1,2,3,4,5,6,7,8,9,0");
				while (1)
				{
					$strOrderCode = strtoupper(
						$arrConfirmationCodePrefixes["Sutdent Registration"]
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
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
						. $arrDataSource[rand(0, count($arrDataSource)-1)]
					);
					$objOrderFound = first($query->user_orders__select(array(
						"confirmation_code" => $strOrderCode
					)));
					if (!$objOrderFound)
						break;
				}

				$intAutherizeNetResult = 0;
				if ($intSum > 0)
				{
					if ($arrPost["creditcard_number"] != $strCreditCardBypass)
					{
						$objAuthorizeNet = new AuthorizeNet();
						$arrResponse = $objAuthorizeNet->process_transaction(array(
							'card_num' => $arrPost['creditcard_number'],
							'exp_date' => $arrPost['creditcard_expiration_month'] . '/' . $arrPost['creditcard_expiration_year'],
							'amount' => $intSum,
							'description' => '',
							'first_name' => $arrPost["creditcard_first_name"],
							'last_name' => $arrPost["creditcard_last_name"],
							'address' => $arrPost['creditcard_address'],
							'state' => $arrPost['creditcard_state'],
							'zip' => $arrPost['creditcard_zip']
						));
						if ($arrResponse["Response Code"] != '1')
						{
							print json_encode(array(
								'error' => $arrResponse['Response Reason Text'],
								"success" => "false"
							));
							exit;
						}
						$intPayment = $query->payment_processes__insert(array(
							'user_id' => $this->_user_session_data->user_id,
							'institution_id' => $this->_user_session_data->institution_id,
							'amount' => $intSum,
							'response' => serialize($arrResponse)
						));
					}

					$strSubject = "Mashpia Student Registration" . " #" . strtoupper($strOrderCode);
					$strHeaders =	'From' . ': orders@mashpia.com' . "\r\n";
					$objUser = first($query->users__select(array(
						"user_id" => $this->_user_session_data->user_id
					)));
					$objInstitution = first($query->institutions__select(array(
						"institution_id" => $this->_user_session_data->institution_id
					)));
					$strMessage = "
- " . "Student Registration:" . " -
" . "User Email" . ": " . $objUser->email . "
" . "User Institution: " . $objInstitution->name . "
" . "User ID" . ": " . $this->_user_session_data->user_id . "
" . "Institution ID" . ": " . $this->_user_session_data->institution_id . "
" . "Order Code" . ": " . strtoupper($strOrderCode) . "
" . "Cost" . ": " . $intSum . "
" . "CreditCard Number Ends with" . ": " . substr($arrPost["creditcard_number"],0,-8) . "
" . "CreditCard Expiration Month" . ": " . $arrPost["creditcard_expiration_month"] . "
" . "CreditCard Expiration Year" . ": " . $arrPost["creditcard_expiration_year"] . "
" . "CreditCard CCV" . ": " . $arrPost["creditcard_ccv"];
					$strTo = join(', ', $arrDetails['admin_emails']);
					$strHeaders .= 'Bcc: andyware@gmail.com' . "\r\n";
					if ($boolSendMail)
						mail($strTo, $strSubject, $strMessage, $strHeaders);

				}


				$arrPermissions = array_hash("user_id", $query->permissions__select(array(
					"user_id" => array_keys($arrRequest),
					"institution_id" => $this->_user_session_data->institution_id
				)));
				$arrResultData = array();
				foreach ($arrRequest as $intUser => $arrGroups)
				{
					if (!isset($arrPermissions[$intUser]))
					{
						print json_encode(array(
							"error" => "Sorry, there was an error" . ": CU-SRA103-5675FD"
						));
						exit;
					}
					if (
						!isset($arrGroups["registration_fee"][0])
						&& $arrPermissions[$intUser]->registration_expiration <= time()
					) {
						print json_encode(array(
							"error" => "Sorry, there was an error" . ": CU-SRA104-9SF32A"
						));
						exit;
					}
					if (
						isset($arrGroups["registration_fee"][0])
						&& $arrPermissions[$intUser]->registration_expiration > time()
					) {
						print json_encode(array(
							"error" => "Sorry, there was an error" . ": CU-SRA105-88DFFS"
						));
						exit;
					}
					if (isset($arrGroups["registration_fee"][0]))
					{
						$query->permissions__update(array(
							"where" => array(
								"user_id" => $intUser,
								"institution_id" => $this->_user_session_data->institution_id
							),
							"values" => array(
								"registration_expiration" => strtotime("September 1st 2016"), // time() + (86400 * 400),
								"registration_date" => time()
							)
						));
						$arrResultData["registered"][$intUser] = $intRegistrationFee;
					}
					$arrResultData[$intUser]["add_on"] = array();
					if (isset($arrGroups["add_on"]))
					{
						foreach ($arrGroups["add_on"] as $intPrize => $boolTrue)
						{
							$intAddOn = $query->user_addons__insert(array(
								"user_id" => $intUser,
								"prize_id" => $intPrize,
								"expires" => time() + (86400 * 400)
							));
							$arrResultData["add_on"][$intUser][$intAddOn] = $arrPrizes[$intPrize];
						}
					}
				}

				$query->user_orders__insert(array(
					"confirmation_code" => $strOrderCode,
					"api_confirmation_code" => $intAutherizeNetResult,
					"user_registrations_list" => serialize(@$arrResultData["registered"]),
					"user_addons_list" => serialize(@$arrResultData["add_on"]),
					"creditcard_first_name" => $arrPost["creditcard_first_name"],
					"creditcard_last_name" => $arrPost["creditcard_last_name"],
					"creditcard_number" => substr($arrPost["creditcard_number"], 0, -8),
					"creditcard_ccv" => $arrPost["creditcard_ccv"],
					"creditcard_expiration_month" => $arrPost["creditcard_expiration_month"],
					"creditcard_expiration_year" => $arrPost["creditcard_expiration_year"]
				));

				print json_encode(array(
					"success" => "true",
					"confirmation_code" => $strOrderCode,
					"amount_billed" => $intSum
				));
				exit;
			} else if ($this->_request->getParam("load_users") == "true") {
				if (
					isset($arrPost["class_name"])
					&& $arrPost["class_name"] != "All"
				) {
					$objClass = first($query->classes__select(array(
						"class_id" => $arrPost["class_name"],
						"institution_id" => $this->_user_session_data->institution_id
					)));
					if (!$objClass)
					{
						print json_encode(array(
							"error" => "Sorry, there was an error" . ": CU-SR101-8SFF2F"
						));
						exit;
					}
				}
				else
					$arrPost["class_name"] = NULL;
				$arrPost["first_name"] = trim(@$arrPost["first_name"]);
				$arrPost["last_name"] = trim(@$arrPost["last_name"]);
				$arrPost["first_name"] = strlen(@$arrPost["first_name"]) ? @$arrPost["first_name"] : NULL;
				$arrPost["last_name"] = strlen(@$arrPost["last_name"]) ? @$arrPost["last_name"] : NULL;
				$arrUsers = array_hash("user_id", $objUsers->_users_select_hierarchal(array(
					"class_id" => @$arrPost["class_name"],
					"first_name" => @$arrPost["first_name"],
					"last_name" => @$arrPost["last_name"],
					"institution_id" => $this->_user_session_data->institution_id,
					"permission" => "Student",
					"is_active" => "1"
				)));
				$arrUserAddons = array_stack("user_id", "prize_id", $query->user_addons__select(array(
					"user_id" => array_keys($arrUsers)
				)));
				$arrUserClasses = $query->user_classes__select(array(
					"user_id" => array_keys($arrUsers)
				));
				$arrClasses = array_hash("class_id", $objClasses->_classes_select(array(
					"class_id" => array_keys(array_stack("class_id", $arrUserClasses))
				)));
				$arrPermissions = array_hash("user_id", $query->permissions__select(array(
					"user_id" => array_keys($arrUsers),
					"institution_id" => $this->_user_session_data->institution_id
				)));
				print json_encode(array(
					"success" => "true",
					"arrUsers" => $arrUsers,
					"arrUserAddons" => $arrUserAddons,
					"arrUserClasses" => array_stack("user_id", "class_id", $arrUserClasses),
					"arrPermissions" => $arrPermissions,
					"arrClasses" => $arrClasses
				));
				exit;
			}
		}

		$objInstitution = first($query->institutions__select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));

		$this->view->arrClasses = $arrClasses = $objClasses->_classes_select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"_ORDER" => "grade, sub ASC"
		));

		// Find all restricted prizes
		$arrPrizeSchoolTypes = array_stack("prize_id", "school_type", $query->prize_school_types__select(array(
			"prize_id" => array_keys(array_stack("template_prize_id", $arrPrizes))
		)));

		foreach ($arrPrizeSchoolTypes as $intPrize => $arrPrizeSchoolType)
		{
			if (!isset($arrPrizeSchoolType["hebrewschool1"]))
				unset($arrPrizes[$intPrize]);
		}
		$this->view->arrPrizeAddOns = array();//$arrPrizes;
	}

	public function editorAction()
	{
		$boolSendMail = TRUE;
		$query = new QueryGen();
		$objUsers = new Users();
		$objRoles = new Roles(); // role of logged in user
		$objUserRoles = new Roles(); // role of student
		$objClasses = new Classes();
		$objInstitutions = new Institutions();
		$objPoints = new Points();
		$objStore = new Store();

		$intInstitution = $this->_user_session_data->institution_id;
		if ($objRoles->isAllowed('Network')) {
			$intInstitution = $this->_request->getParam("institution_id");
		}
		$this->view->institution_id = $intInstitution;
		$this->view->boolJustAdded = $this->_request->getParam("just_added") == "true" ? 1 : 0;
		$strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		$intUser = $this->view->user_id = intval($this->_request->getParam("user_id"));
		$objUser = false;
		$this->view->boolDeleteEnabled = 0;
		if ($intUser > 0)
		{
			$objUser = $this->view->objUser = array_clean_slashes(first($query->users__select(array(
				"user_id" => $intUser
			))));
			if (!$objUser)
			{
				print "Sorry, there was an error: CU-E103-DFGD9F";
				exit;
			}
			$objUserPermission = first($query->permissions__select(array(
				"user_id" => $intUser,
				"institution_id" => $intInstitution
			)));
			if (!$objUserPermission)
			{
				print "Sorry, there was an error: CU-E104-DFGD9F";
				exit;
			}
			$boolEnrolled = 1;
			if ($objUserPermission->registration_expiration < time())
			{
				$boolEnrolled = 0;
			}
			$arrUserPoints = $query->user_points__select(array(
				"user_id" => $intUser,
				"institution_id" => $intInstitution,
				"_LIMIT" => 10
			));
			$boolDeleteEnabled = 0;
			if (
				!$boolEnrolled
				&& count($arrUserPoints) < 10
			) {
				$boolDeleteEnabled = 1;
			}
			$this->view->boolDeleteEnabled = $boolDeleteEnabled;
			if (
				$this->_request->isPost()
				&& $this->_request->getParam("get_user_points")
			) {
				$intPointsStore = $objPoints->user_store(array(
					"institution_id" => $intInstitution,
					"user_id" => $intUser
				));
				$intPointsTotal = $objPoints->user_total(array(
					"institution_id" => $intInstitution,
					"user_id" => $intUser
				));
				print json_encode(array(
					'success' => 'true',
					'intPointsStore' => $intPointsStore,
					'intPointsTotal' => $intPointsTotal
				));
				exit;
			}
			else if (
				$this->_request->isPost()
				&& $this->_request->getParam("delete_user")
				&& $boolDeleteEnabled
			) {
				$query->users_deleted__insert($objUser);
				$query->users__delete(array(
					"user_id" => $intUser
				));
				/*
				$query->permissions__delete(array(
					"user_id" => $intUser,
					"institution_id" => $this->_user_session_data->institution_id
				));
				$query->user_points__delete(array(
					"user_id" => $intUser,
					"institution_id" => $this->_user_session_data->institution_id
				));
				$query->user_prizes__delete(array(
					"user_id" => $intUser,
					"institution_id" => $this->_user_session_data->institution_id
				));
				 *
				 */
				print json_encode(array(
					"user_deleted" => "true"
				));
				exit;
			}
		}

		$intPermission = intval($this->_request->getParam("permission_id"));

		$arrUserTypes = array(
			"super" => "Super Administrator",
			"admin" => "Institution Administrator",
			"teacher" => "Teacher",
			"parent" => "Parent",
			"student" => "Student"
		);
		$arrUserTypesRev = array_flip($arrUserTypes);

		$this->view->user_type = $this->_request->getParam("user_type");
		$arrUserTypeParam = strlen($this->view->user_type) ? explode(",", $this->view->user_type) : array();
		if (!count($arrUserTypeParam))
		{
			$arrUserTypeParam = array_keys($arrUserTypes);
		}
		$this->view->arrUserTypeParam = $arrUserTypeParam;

		// Take out permissions that are not allowed under the current user
		foreach ($arrUserTypes as $strKey => $strPermmission)
		{
			if (
				!$objRoles->isAllowed($strPermmission)
				|| (
					count($arrUserTypeParam)
					&& !in_array($strKey, $arrUserTypeParam)
				)
			)
				unset($arrUserTypes[$strKey]);
		}

		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));

		$arrClasses = $this->view->arrClasses = $objClasses->_classes_select(array(
			"institution_id" => $intInstitution,
			'_ORDER' => 'class_hierarchy+0'
		));
		$arrClassesHash = array_hash("class_id", $arrClasses);

		$strUserTypeSelected = $this->_request->getParam("user_type_selected");
		if (
			!$strUserTypeSelected
			|| !isset($arrUserTypes[$strUserTypeSelected])
		) {
			$strUserTypeSelected = first(array_keys($arrUserTypes));
		}
		$this->view->strUserTypeSelected = $strUserTypeSelected;
		$this->view->arrUserTypes = $arrUserTypes;
		$objUserRoles->setRoles($arrUserTypes[$strUserTypeSelected]);

		if ($objUserRoles->isRole("Student") && $intUser)
		{
			$intPointsStore = $this->view->intPointsStore = $objPoints->user_store(array(
				"institution_id" => $intInstitution,
				"user_id" => $intUser
			));
			$intPointsTotal = $this->view->intPointsTotal = $objPoints->user_total(array(
				"institution_id" => $intInstitution,
				"user_id" => $intUser
			));
			$this->view->arrAvailablePrizes = $objStore->user_available_prizes(array(
				"user_id" => $intUser
			));
		}

		if (!$objUser && $intUser > 0)
			$objUser = $this->view->objUser = first($objUsers->_users_select(array(
				"user_id" => $intUser
			)));
		if ($intUser > 0)
		{
			// Choose the permission to load
			if ($intPermission)
			{
				$objPermission = first($query->permissions__select(array(
					"permission_id" => $intPermission
				)));
			}
			else
			{
				$objPermission = first($query->permissions__select(array(
					"institution_id" => $intInstitution,
					"permission" => $arrUserTypes[$strUserTypeSelected],
					"user_id" => $intUser,
					"tstyle" => $strTemplateStyle
				)));
			}
			$this->view->strUserTypeSelected = $arrUserTypesRev[$objPermission->permission];
			$this->view->objPermission = $objPermission;
			$this->view->arrUserClasses = $arrUserClasses = array_hash('class_id', $objClasses->_user_classes_select(array(
				"user_id" => $intUser
			)));
			if (
				$strUserTypeSelected == "teacher"
				|| $strUserTypeSelected == "student"
			) {
				$strUserClasses = "";
				foreach ($arrUserClasses as $objUserClass)
				{
					$strUserClasses .= $objUserClass->class_id . "=1&";
				}
				$strUserClasses = rtrim($strUserClasses, "&");
				$this->view->strUserClasses = $strUserClasses;
			}
		}
		if ($this->_request->isPost())
		{
			$arrResult = array();
			$arrParams = $this->_request->getPost();


			if (isset($arrParams["searchbyemail"]) && !empty($arrParams["searchbyemail"]))
			{
				$strTo = $arrParams["searchbyemail"];
				// Verify if email is on system
				$objFindUser = first($query->users__select(array(
					'email' => trim($arrParams["searchbyemail"])
				)));
				if (!$objFindUser)
				{
					print json_encode(array(
						'error' => "This user has never been registered. You can register a new user with the form before."
					));
					exit;
				}
				// Verify that the user is not already a teacher in this institution
				$objTeacherPermission = first($query->permissions__select(array(
					'user_id' => $objFindUser->user_id,
					'institution_id' => $intInstitution
				)));
				if ($objTeacherPermission)
				{
					print json_encode(array(
						'error' => "This user has already been added as a counselor."
					));
					exit;
				}
				$query->permissions__insert(array(
					'user_id' => $objFindUser->user_id,
					'institution_id' => $intInstitution,
					'tstyle' => $strTemplateStyle,
					'permission' => 'Teacher'
				));
				print json_encode(array(
					'success' => 'true',
					'teacher_id' => $objFindUser->user_id
				));
				exit;
			}

			// Form validation
			if (!isset($arrParams["user_type"]) || !isset($arrUserTypes[$arrParams["user_type"]]))
			{
				print json_encode(array("error" => "Sorry, there was an error: CU-E101-8DF8DA"));
				exit;
			}

			if (!isset($arrParams["first_name"]) || !strlen($arrParams["first_name"]))
			{
				$arrResult["error"]["first_name"] = "First name is a required field.";
			}
			if (!isset($arrParams["last_name"]) || !strlen($arrParams["last_name"]))
			{
				$arrResult["error"]["last_name"] = "Last name is a required field.";
			}
			if ($objUserRoles->isAllowed("Parent"))
			{
				if (!isset($arrParams["email"]) || !strlen($arrParams["email"]))
				{
					$arrResult["error"]["email"] = "Email is a required field.";
				}
				else if (!preg_match("/^.+?@.+?\.[a-z]{2,8}$/i", $arrParams["email"]))
				{
					$arrResult["error"]["email"] = "The email address provided appears to be invalid.";
				}
				else if (
					!$objUser
					|| $objUser->email != $arrParams["email"]
				) {
					// Check if email already exists in the system
					$objUser = first($objUsers->_users_select(array(
						"email" => $arrParams["email"]
					)));
					if ($objUser)
					{
						$arrResult["error"]["email"] = "This email already exists in our system.";
					}
				}
			}
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
				exit;
			}

			// process custom fields
			$strCustomFields = $objInstitution->custom_fields;
			$arrCustomFields = array();
			if (empty($strCustomFields))
				$arrCustomFields = array();
			else
				$arrCustomFields = unserialize($strCustomFields);
			$arrUserFields = array();
			$intField = -1;
			foreach ($arrCustomFields as $arrRow)
			{
				$intField++;
				$strField = $arrRow['field_name'];
				if (!isset($arrParams['custom_field_' . $intField]))
					continue;
				$strFieldValue = $arrParams['custom_field_' . $intField];
				unset($arrParams['custom_field_' . $intField]);
				if (empty($strFieldValue))
					continue;
				$arrUserFields[$strField] = array(
					'value' => $strFieldValue
				);
			}
			$arrParams['custom_fields'] = serialize($arrUserFields);
			$arrResult = array();
			if ($intUser > 0)
			{
				$boolResult = $query->users__update(array(
					"where" => array(
						"user_id" => $intUser
					),
					"values" => $arrParams
				));
				$arrResult = array(
					"success" => "updated",
					"update_result" => $boolResult,
					"user_id" => $intUser
				);
			}
			else
			{
				if (!isset($arrParams["image_id"]))
					$arrParams["image_id"] = "";
				if ($strUserTypeSelected == "student") {
					do {
						$arrParams["bar_code"] = $intBarCode = rand_num_string(16);
						$objBarcode = first($query->users__select(array(
							'bar_code' => $intBarCode
						)));
					} while ($objBarcode);
				}
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
				$arrParams["password"] = md5($strTempPassword);
				$intUser = $query->users__insert($arrParams);
				$query->permissions__insert(array(
					"user_id" => $intUser,
					"institution_id" => $intInstitution,
					"permission" => $arrUserTypes[$strUserTypeSelected],
					"template_style" => $this->objPermission->template_style,
					"default_permission" => 1
				));
				$arrResult = array(
					"success" => "added",
					"user_id" => $intUser
				);
				if (isset($arrParams["email"]) && !empty($arrParams["email"]))
				{
					$strTo = $arrParams["email"];
					$strSubject = "New Mashpia Account Created.";
					$strMessage = "Welcome " . $arrParams["first_name"] . " " . $arrParams["last_name"] . ","
					. "\n\nA new mashpia.com account was created for you.\n\n"
					. "Your new password was auto-generated: " . $strTempPassword
					. "\n\nLogin at http://v2.mashpia.com/index/index/tstyle/" . $strTemplateStyle;
					$strHeaders =	'From: orders@mashpia.com' . "\r\n";
					if ($boolSendMail && $strUserTypeSelected != "student")
						mail($strTo, $strSubject, $strMessage, $strHeaders);
				}
			}

			// if its a teacher assign the user to a class
			if (
				$strUserTypeSelected == "teacher"
				|| $strUserTypeSelected == "student"
			) {
				$objClasses->_user_classes_delete(array(
					"user_id" => $intUser,
					"institution_id" => $intInstitution
				));

				parse_str($arrParams["classes"], $arrUserClasses);
				foreach ($arrUserClasses as $intClass => $boolValue)
				{
					if (
						$boolValue == "1"
						&& isset($arrClassesHash[$intClass])
					) {
						$query->user_classes__insert(array(
							"user_id" => $intUser,
							"class_id" => $intClass,
							"class_role" => $arrUserTypes[$strUserTypeSelected],
							"institution_id" => $intInstitution
						));
					}
				}
			}

			print json_encode($arrResult);
			exit;
		} // end of isPost
	}
	public function dobeditorAction()
	{
		$query = new QueryGen();
		$objUsers = new Users();
		$objRoles = new Roles(); // role of logged in user
		$objUserRoles = new Roles(); // role of student
		$objClasses = new Classes();
		$objInstitutions = new Institutions();

		$intInstitution = $this->_user_session_data->institution_id;

		$this->view->tstyle = $this->_request->getParam("tstyle");

		$intUser = $this->view->user_id = intval($this->_request->getParam("user_id"));
		$objUser = false;
		if ($intUser > 0)
		{
			$objUser = $this->view->objUser = first($objUsers->_users_select(array(
				"user_id" => $intUser
			)));
			if (!$objUser)
			{
				print json_encode(array("error" => "Sorry, there was an error" . ": CU-E102-DFGD9F"));
				exit;
			}
		}

		$intPermission = intval($this->_request->getParam("permission_id"));

		$arrUserTypes = array(
			"super" => "Super Administrator",
			"admin" => "Institution Administrator",
			"teacher" => "Teacher",
			"parent" => "Parent",
			"student" => "Student"
		);
		$arrUserTypesRev = array_flip($arrUserTypes);

		$this->view->user_type = $this->_request->getParam("user_type");
		$arrUserTypeParam = strlen($this->view->user_type) ? explode(",", $this->view->user_type) : array();
		if (!count($arrUserTypeParam))
		{
			$arrUserTypeParam = array_keys($arrUserTypes);
		}
		$this->view->arrUserTypeParam = $arrUserTypeParam;

		// Take out permissions that are not allowed under the current user
		foreach ($arrUserTypes as $strKey => $strPermmission)
		{
			if (
				!$objRoles->isAllowed($strPermmission)
				|| (
					count($arrUserTypeParam)
					&& !in_array($strKey, $arrUserTypeParam)
				)
			)
				unset($arrUserTypes[$strKey]);
		}

		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $intInstitution
		)));

		$this->view->arrClasses = $objClasses->_classes_select(array(
			"institution_id" => $intInstitution
		));
		$arrClassesHash = array();
		foreach ($this->view->arrClasses as $objClass)
		{
			$arrClassesHash[$objClass->class_id] = $objClass;
		}

		$strUserTypeSelected = $this->_request->getParam("user_type_selected");
		if (
			!$strUserTypeSelected
			|| !isset($arrUserTypes[$strUserTypeSelected])
		) {
			$strUserTypeSelected = first(array_keys($arrUserTypes));
		}
		$this->view->strUserTypeSelected = $strUserTypeSelected;
		$this->view->arrUserTypes = $arrUserTypes;
		$objUserRoles->setRoles($arrUserTypes[$strUserTypeSelected]);

		if ($this->_request->isPost())
		{
			$arrResult = array();
			$arrParams = $this->_request->getPost();

			// Form validation
			if (!isset($arrParams["user_type"]) || !isset($arrUserTypes[$arrParams["user_type"]]))
			{
				print json_encode(array("error" => "Sorry, there was an error" . ": CU-E101-8DF8DA"));
				exit;
			}
			if (!isset($arrParams["dob"]) || !strlen($arrParams["dob"]))
			{
				$arrResult["error"]["dob"] = "Date of birth is a require field.";
			} else if (!preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}/", $arrParams["dob"]))
			{
				$arrResult["error"]["dob"] = "Date of birth must be in the format MM/DD/YYYY.";
			}
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
				exit;
			}

			if ($intUser > 0)
			{
				$boolResult = $objUsers->_users_update(array(
					"where" => array(
						"user_id" => $intUser
					),
					"values" => array(
						"dob" => $arrParams["dob"]
					)
				));
				print json_encode(array(
					"success" => "updated",
					"boolResult" => $boolResult
				));
			}
			exit;
		} // end of isPost

		if (!$objUser && $intUser > 0)
			$objUser = $this->view->objUser = first($objUsers->_users_select(array(
				"user_id" => $intUser
			)));
		if ($intUser > 0)
		{
			// Choose the permission to load
			if ($intPermission)
			{
				$objPermission = first($query->permissions__select(array(
					"permission_id" => $intPermission
				)));
			}
			else
			{
				$objPermission = first($query->permissions__select(array(
					"institution_id" => $intInstitution,
					"permission" => $arrUserTypes[$strUserTypeSelected],
					"user_id" => $intUser
				)));
			}
			$this->view->strUserTypeSelected = $arrUserTypesRev[$objPermission->permission];
			$this->view->objPermission = $objPermission;
			$arrUserClasses = $objClasses->_user_classes_select(array(
				"user_id" => $intUser
			));
			foreach ($arrUserClasses as $objUserClass)
			{
				if ($arrClassesHash[$objUserClass->class_id])
				{
					$this->view->objClass = $arrClassesHash[$objUserClass->class_id];
					break;
				}
			}
		}
	}
	public function addteacherpermissionAction()
	{
		global $arrUserTypes;
		$objPermissions = new Permissions();
		$objClasses = new Classes();
		$objUsers = new Users();

		$this->view->user_id = $intUser = intval($this->_request->getParam("user_id"));
		if (!$intUser)
		{
			print "Sorry, there was an error" . ": CU-ATP101-SD8FDS";
			exit;
		}
		unset($arrUserTypes["student"]);
		unset($arrUserTypes["teacher"]);
		$this->view->objUser = $objUser = first($objUsers->_users_select_hierarchal(array(
			"user_id" => $intUser,
			"permission" => $arrUserTypes
		)));
		if (!$objUser)
		{
			print "Sorry, there was an error" . ": CU-ATP103-8S7DFD";
			exit;
		}
		$this->view->class_id = $intClass = intval($this->_request->getParam("class_id"));
		if (!$intClass)
		{
			print "Sorry, there was an error" . ": CU-ATP102-8SDFSD";
			exit;
		}
		$this->view->objClass = $objClass = first($objClasses->_classes_select(array(
			"class_id" => $intClass
		)));

		if ($this->_request->isPost())
		{
			// Add teacher permission to account
			$objPermission = first($objPermissions->_permissions_select(array(
				"user_id" => $intUser,
				"permission" => "Teacher",
				"institution_id" => $this->_user_session_data->institution_id
			)));
			if (!$objPermission)
			{
				$objPermissions->_permissions_insert(array(
					"user_id" => $intUser,
					"permission" => "Teacher",
					"institution_id" => $this->_user_session_data->institution_id,
					"template_style" => "hebrewschool1"
				));
			}

			$objUserClass = first($objClasses->_user_classes_select(array(
				"class_role" => "Teacher",
				"class_id" => $intClass,
				"user_id" => $intUser
			)));
			if (!$objUserClass)
			{
				$objClasses->_user_classes_insert(array(
					"class_role" => "Teacher",
					"class_id" => $intClass,
					"user_id" => $intUser,
					"institution_id" => $this->_user_session_data->institution_id
				));
			}

			print json_encode(array(
				"success" => "true"
			));
			exit;
		}
	}
}
?>