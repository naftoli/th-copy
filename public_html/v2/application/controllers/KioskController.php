<?php
class KioskController extends Zend_Controller_Action
{
	private $_kiosk_user_session_data;
	private $_VERBOSE;

	function init()
	{}

	function preDispatch()
	{
		//$this->_kiosk_user_session_data = new Zend_Session_Namespace('kiosk_user_session_data');
		// initialize cart and user info sessions
		$cart = new Zend_Session_Namespace('cart');
		$user = new Zend_Session_Namespace('userInfo');
		$this->_VERBOSE = $this->_request->getParam("verbose");
		$this->_kiosk_user_session_data = $user;
	}

	public function indexAction()
	{}

	public function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting)
			$starting = unixtojd();

		$today = cal_from_jd($starting, CAL_JEWISH);
		$strDate = cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
		return $strDate;
	}

	public function getHebrewPoints()
	{
		$objUsers = new Users();
		$objUser = first($objUsers->getMashpiaUser($this->_kiosk_user_session_data->barcode));

		// find out if school has a store reset date set
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$sql = "select store_reset from schools where school_id = " . $objUser->school_id;
		$result = $db->query( $sql );
		$row = $result->fetch();
		$date = $row['store_reset'];
		if ($date > 0 && $date <= unixtojd()) { // make sure we are now after the start date set by the school
			$start_date = $date;
		} else {
			$australian = array(55,66,110,112,180);
			$start_date = in_array($objUser->school_id, $australian) ? 2457629 : 2457934;
		}
		
		$objPoints = new Points();
		$intUserPointsStore = $objPoints->user_store(array(
			'user_id' 		 => $objUser->user_id,
			'institution_id' => $objUser->school_id,
			'start_date'	 => $start_date
		));
		//dumper($intUserPointsStore,1,1);
		return $intUserPointsStore;
		/*
		$objKiosk = new Kiosk();
		$intMonth = 13;
		$intDay = 18;
		$year_offset = 0;
		$starting = 0;
		$arrHebrewPointsParams = array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		);
		$arrHebrewPointsParams["jd_date"] = $this->_kiosk_user_session_data->jd_point_restriction;
		if (!empty($arrHebrewPointsParams["jd_date"]))
		{
			print "Sorry, there was an error: CK-GHP101-sbvdgy";
			exit;
		}
		$intHebrewPoints = intval($objKiosk->user_points_sum_select($arrHebrewPointsParams));
		return $intHebrewPoints;
		 *
		 */
	}

	public function storeAction()
	{
		$query = new QueryGen();
		$objPoints = new Points();
		if (!isset($this->_kiosk_user_session_data->remote_balance))
			$this->_redirect('kiosk/logout');
		$this->view->intOrgPoints = $intLocalPoints = $this->getHebrewPoints();
		
		// Collect a hash of all hq prizes from the session
		$arrHQPrizesHash = array();
		$objUserSession = new Zend_Session_Namespace("userInfo");
		/*
		if (isset($objUserSession->arrTHHQPrizes))
		{
			foreach ($objUserSession->arrTHHQPrizes as $objItems)
			{
				$arrHQPrizesHash[$objItems["school_add_on_id"]] = $objItems;
			}
			$this->view->arrHQPrizesHash = $arrHQPrizesHash;
		}
		*/
		$this->view->intLegPoints = $objUserSession->remote_balance;
		$intLocalPoints = $objUserSession->remote_balance + $intLocalPoints;
		
		$objUsers = new Users();
		$objUser = first($objUsers->getMashpiaUser($objUserSession->barcode));
		
		$objInstitutions = new Institutions();
		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $objUser->school_id
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
				$arrUserClasses = array($objUser->class_id => 1);
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
						"is_active" => 1
					)));
					// Skip prizes that no longer exist
					if (!$objPrize)
					{
						$arrResult["error"]["prize_id_" . $arrCartItem["prize_id"]][] = "This prize doesn't seem to exist any longer.";
						continue;
					}
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
							"user_id" => $objUser->user_id,
							"is_reversed" => 0, 
							"_LIMIT" => 1
						)));
						if ($objUserPrizes)
						{
							$arrResult["error"]["prize_id_" . $objPrize->prize_id][] = $objPrize->prize_name . " is a prize that has been limited to one per user and therefor cannot be purchased again.";
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
					if (!count($arrPurchasePrizes))
					{
						$arrResult["error"] = "Nothing seems to be available to be purchased.";
						print json_encode($arrResult);
						exit;
					}
					if ($intPointsSum > $intLocalPoints)
					{
						$intPointRemainder = $intLocalPoints - $intPointsSum;
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
							$strSerial = rand_num_string(10);
							$objTempUserPrize = first($query->user_prizes__select(array(
								"serial" => (string) $strSerial
							)));
							if ($objTempUserPrize)
								$strSerial = FALSE;
						}

						$intUserPrizeId = $query->user_prizes__insert(array(
							"prize_id" => $arrData["objPrize"]->prize_id,
							"user_id" => $objUser->user_id,
							"institution_id" => $objUser->school_id,
							"quantity" => $arrData["arrCartItem"]["user_quantity"],
							"status" => "Checked Out",
							"serial" => $strSerial
						));
						$intUserPointId = $query->user_points__insert(array(
							"prize_id" => $arrData["objPrize"]->prize_id,
							"user_prize_id" => $intUserPrizeId,
							"user_id" => $objUser->user_id,
							"institution_id" => $objUser->school_id,
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

					}
					$intLocalPoints = $objUserSession->remote_balance + $this->getHebrewPoints();
					$intLocalPoints -= $intPointsSum;
					$arrResult = array(
						"success" => "true",
						"intLocalPoints" => $intLocalPoints
					);
				}
				print json_encode($arrResult);
				exit;
			}
		}

		$objStore = new Store();
		$objPoints = new Points();
		$objConfig = new Config();

		$this->view->objUser = $objUser;
		$this->view->objInstitution = $objInstitution;
		/*
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("kiosk", "admin"),
			"institution_id" => $this->_kiosk_user_session_data->institution_id
		));

		$this->view->objUser = first($objUsers->_users_select(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		)));
		$objInstitution = $this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_kiosk_user_session_data->institution_id
		)));
		if (isset($_COOKIE["hebrewschools_store_cart"]))
			$this->view->arrStoreCart = $arrStoreCart = (array) json_decode(stripslashes($_COOKIE["hebrewschools_store_cart"]));
		
		$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		)));
		$arrClassIds = array_keys($arrUserClasses);
		
		// Check that all add-on prizes from old system exist in new system for host and all institutions
        $arrInstIDs = $query->institutions__select(
        	array(
        		"_COLUMNS" => array("institution_id"),
	        	"network_id" => 2,
	            array(
	                "template_style" => array(
	                    "",
	                    "tanyatemplate1",
	                ),
	                "_IS_NULL"=>"template_style"
	             )
	         )
        );
		// Add host to top of list of institution ids
		array_unshift($arrInstIDs, (object) array("institution_id" => "1"));

        foreach ($arrInstIDs as $objInst) {
        	$intInstId = (int)$objInst->institution_id;

	        $arrAddOns = $query->prize__select(array(
	            "institution_id" => $intInstId,
	            "legacy_add_on_id" => array_keys($arrHQPrizesHash),
			));

	        $arrAddOnIDs = array();
	        foreach ($arrAddOns as $arrAddOn) {
	            $arrAddOnIDs[] = $arrAddOn->legacy_add_on_id;
	        }
	        $arrMissingAddOns = array_diff(array_keys($arrHQPrizesHash), $arrAddOnIDs);

	        // Insert missing add-ons into new system
	        if ($intInstId == 1) {
	        	$type = "Installable";
				$default = 1;
	        } else {
	        	$type = "";
				$default = 0;
	        }
	        foreach ($arrMissingAddOns as $arrAddOn) {
	            if (trim($arrHQPrizesHash[$arrAddOn]['name']) == "Rebbe Album & Stickers") {
	                $subPrizes = 1;
	                $onePerUser = 0;
	                $boolSubAddOn = true;
	            } else {
	                $subPrizes = 0;
	                $onePerUser = 1;
					$boolSubAddOn = false;
	            }
	            $id = $query->prize__insert(array(
	                "parent_prize_id"   => 0,
	                "template_prize_id" => isset($intTemplatePrizeId[$arrHQPrizesHash[$arrAddOn]['school_add_on_id']]) ? $intTemplatePrizeId[$arrHQPrizesHash[$arrAddOn]['school_add_on_id']] : 0,
	                "institution_id"    => $intInstId,
	                "teacher_id"        => 0,
	                "guardian_id"       => 0,
	                "legacy_add_on_id"  => $arrHQPrizesHash[$arrAddOn]['school_add_on_id'],
	                "prize_name"        => trim($arrHQPrizesHash[$arrAddOn]['name']),
	                "prize_category"    => "General Prize",
	                "prize_description" => $arrHQPrizesHash[$arrAddOn]['description'],
	                "add_on_restricted" => 1,
	                "use_sub_prizes"    => $subPrizes,
	                "one_per_user"      => $onePerUser,
	                "prize_count"       => 900,
	                "points"            => 300,
	                "prize_type"        => $type,
	                "installable_default_on" => $default,
	                "prize_price"       => $arrHQPrizesHash[$arrAddOn]['value'],
	                "prize_discounted_price" => $arrHQPrizesHash[$arrAddOn]['price'],
	                "is_active"         => 1
	            ));

				// If we are in the host, set the template_prize_id for the other schools
				if ($intInstId == 1) {
					$intTemplatePrizeId[$arrHQPrizesHash[$arrAddOn]['school_add_on_id']] = $id;
				}

				// Change parent_prize_id and template_prize_id of sub_add_ons to point to the new add_on
				if ($boolSubAddOn) {
					// Variable to find out if we need to create the sub-add-ons
					$insertSub = false;
					// Get parent_prize_id of previous year's add_on
					$arrSubInfo = $query->prize__select(array(
						"_COLUMNS" => array("prize_id"),
						"legacy_add_on_id" => 6,
						"institution_id" => $intInstId
					));
					if (count($arrSubInfo) > 0) {
						$intSubAddOnId = $arrSubInfo[0]->prize_id;
						//echo "SubAddOnId: " . $intSubAddOnId . "<br />";
						//echo "Institution: " . $intInstId . "<br />";
						//echo "ParentPrizeId: " . $id . "<br />";
						// Find out if sub-prizes exist
						$arrSubPrizes = $query->prize__select(array(
							"institution_id" => $intInstId,
							"parent_prize_id" => $intSubAddOnId
						));
						if (count($arrSubPrizes) > 0) {
							$query->prize__update(array(
								"values" => array(
									"parent_prize_id" => $id
								),
								"where" => array(
									"institution_id" => $intInstId,
									"parent_prize_id" => $intSubAddOnId
								)
							));
						} else {
							$insertSub = true;
						}
					} else {
						$insertSub = true;
					}
					if ($insertSub) {
						// Add sub-add-ons for this school
						// Get sub-add-ons from host
						$arrSubAddOns = $query->prize__select(array(
							"institution_id" => 1,
							"_GREATER" => array(
								"parent_prize_id" => 0
							)
						));
						foreach ($arrSubAddOns as $objSubAddOn) {
							$intSubAddOnId = $query->prize__insert(array(
								"parent_prize_id"   => $id,
				                "template_prize_id" => $objSubAddOn->prize_id,
				                "institution_id"    => $intInstId,
				                "prize_name"        => $objSubAddOn->prize_name,
				                "prize_category"    => $objSubAddOn->prize_category,
				                "prize_description" => "",
				                "add_on_restricted" => 0,
				                "use_sub_prizes"    => 0,
				                "one_per_user"      => 0,
				                "prize_count"       => 9999,
				                "points"            => 50,
				                "prize_type"        => "Template",
				                "installable_default_on" => 0,
				                "prize_price"       => 0,
				                "is_active"         => 1
				            ));
						}
					}
				}
	        }
	    }
		*/
		
		$objClasses = new Classes();
		$arrUserClasses = array_hash("class_id", $objClasses->getMashpiaClasses(array(
			$objUser->user_id => 1
		)));
		$arrClassIds = array_keys($arrUserClasses);
		
		// Load all the applicable prizes
		$arrPrizes = array_hash("prize_id", $query->prize__select(array(
			"institution_id" => array(1, $objUser->school_id),
			"parent_prize_id" => "0",
			"is_active" => 1,
			"_ORDER" => "prize_id+0 ASC",
			"_NOT" => array(
				"prize_count" => 0
			)
		)));
		
		// remove any prizes that are one time prizes that have been purchased
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$oneTimePrizes = array();
		foreach ($arrPrizes as $prize_id => $objPrize) {
			if ($objPrize->one_per_user) {
				$oneTimePrizes[] = $prize_id;
			}
		}
		$strSql = "select prize_id from pointsDB.user_prizes where is_reversed = 0 and prize_id in (" . implode(',', $oneTimePrizes) . ") and user_id = " . $objUser->user_id;
		//echo $strSql; exit;
		$arrResult = $db->fetchAll($strSql);
		if (count($arrResult) > 0) {
			foreach ($arrResult as $objPrize) {
				unset($arrPrizes[$objPrize->prize_id]);
			}
		}
		
		$arrPrizeClasses = array_bubble_hash("prize_id", "class_id", $query->prize_classes__select(array(
			"prize_id" => array_keys($arrPrizes)
		)));

		// Loop through prizes to find the sub category
		$arrNewPrizes = array();
		foreach ($arrPrizes as $intPrize => $objPrize)
		{
			if (
				isset($arrPrizeClasses[$intPrize])
				&& !array_in_array(array_keys($arrUserClasses), array_keys($arrPrizeClasses[$intPrize]))
			) {
				continue;
			}

			//if (in_array($objPrize->template_prize_id, array(1589)) && isset($arrHQPrizesHash[$objPrize->legacy_add_on_id]))
			if (in_array($objPrize->template_prize_id, array(142169)) && isset($arrHQPrizesHash[$objPrize->legacy_add_on_id]))
			{
				$arrChildPrizes = $query->prize__select(array(
					"institution_id" => array(1, $objInstitution->school_id),
					"parent_prize_id" => $objPrize->prize_id,
					"is_active" => 1,
					"_ORDER" => "prize_id+0 ASC"
				));
				foreach ($arrChildPrizes as $objParentPrize)
				{
					$arrNewPrizes[] = $objParentPrize;
				}
			}
			else if ($objPrize->institution_id == $objInstitution->school_id)
				$arrNewPrizes[] = $objPrize;
		}
		$arrPrizes = array_hash("prize_id", $arrNewPrizes);

		$arrPrizeSizes = array_bubble_hash("prize_id", "prize_size_hierarchy", $query->prize_sizes__select(array(
			"prize_id" => array_keys($arrPrizes)
		)));

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
				&& !($objPrize->add_on_restricted && !isset($arrHQPrizesHash[$objPrize->legacy_add_on_id]))
				&& (
					$objPrize->parent_prize_id > 0
					|| (
						(
							$objPrize->prize_type != "Template"
							|| isset($arrHQPrizesHash[$objPrize->legacy_add_on_id])
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
					$objPrize->add_on_restricted && isset($arrHQPrizesHash[$objPrize->legacy_add_on_id])
					&& strlen($arrHQPrizesHash[$objPrize->legacy_add_on_id]["item_size"])
				) {
					$objPrize->prize_name .= " size: " . $arrHQPrizesHash[$objPrize->legacy_add_on_id]["item_size"];
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
					"objPrize" => $objPrize,
					"arrPrizeSizes" => @$arrPrizeSizes[$objPrize->prize_id]
				);
				if ($objPrize->one_per_user == "1")
					$arrOnePerUserPrizeIds[$objPrize->prize_id] = 1;
			}
		}
		ksort($arrDistinctPoints);

		$arrOnePerUserUserPrizes = array_hash("prize_id", $query->user_prizes__select (array(
			"prize_id" => array_keys($arrOnePerUserPrizeIds),
			"user_id" => $objUser->user_id,
			"is_reversed" => 0
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
		$intLocalPoints -= $intPointsOffset;

		// Send data sets to view
		$this->view->arrDistinctPoints = $arrDistinctPoints;
		$this->view->arrGroupedPrizes = $arrGroupedPrizes;
		$this->view->arrPrizes = $arrPrizesFiltered;
		$this->view->intLocalPoints = $intLocalPoints;
		$this->view->arrCartItems = $arrCartItems;
		$this->view->arrPrizeDetails = $arrPrizeDetails;
		$this->view->arrOnePerUserPrizeIds = $arrOnePerUserPrizeIds;
		$this->view->arrOnePerUserUserPrizes = $arrOnePerUserUserPrizes;
	}

	public function shadowpoptemplateAction()
	{
		$this->view->strTitle = $this->_request->getParam("title");
		$this->view->strContent = $this->_request->getParam("msg");
	}

	public function store2Action()
	{
		if (!$this->_kiosk_user_session_data->user_id)
			$this->_redirect('logout');
		$objUserSession = new Zend_Session_Namespace("userInfo");
		$objCartSession = new Zend_Session_Namespace('cart');
		$objImages = new Image();
		$objKiosk = new Kiosk();
		$objInstitutions = new Institutions();
		$objStore = new Store();

		// Collect a hash of all hq prizes from the session
		$arrHQPrizesHash = array();
		foreach ($objUserSession->arrTHHQPrizes as $objItems)
		{
			$arrHQPrizesHash[$objItems["store_item"]] = $objItems;
		}

		// Load the institution object
		$objInstitution = $this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $objUserSession->institution_id
		)));
		if (!$objInstitution)
		{
			print text("Sorry, there was an error") . ": CK-S101-9DSF8D";
			exit;
		}

		// query the current local balance
		$intLocalPoints = $objKiosk->user_points_sum_select_hebrew(array(
			"user_id" => $objUserSession->user_id
		));
		// Calculate the sum of the points from the local system and remote system
		$intCurrentBalance = $objUserSession->remote_balance + $intLocalPoints;
		$intCurrentClass = first($objUserSession->classes);
		$arrClassIds = array();
		$arrClassIds[] = "0";
		if ($intCurrentClass)
			$arrClassIds[] = $intCurrentClass;

		// Load all the aplicable prizes
		$arrPrizes = $objStore->_prizes_select(array(
			"class_id" => $arrClassIds,
			"institution_id" => array($objInstitution->host_id, $objInstitution->institution_id),
			"parent_prize_id" => "0",
			"is_active" => 1,
			"_ORDER" => "prize_id+0 ASC"
		));

		if ($this->_VERBOSE)
		{
			var_dump($this->_kiosk_user_session_data->institution_id);
			//var_dump($arrHQPrizesHash);
			//var_dump($arrPrizes);
			exit;
		}

		// Loop through prizes to find the sub category exception defined by Nafotli
		$arrNewPrizes = array();
		foreach ($arrPrizes as $intPrize => $objPrize)
		{
			if (in_array($objPrize->template_prize_id, array(1589)) && isset($arrHQPrizesHash[$objPrize->prize_name]))
			{
				$arrChildPrizes = $objStore->_prizes_select(array(
					"class_id" => $arrClassIds,
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
			else if ($objPrize->institution_id == $objInstitution->institution_id)
				$arrNewPrizes[] = $objPrize;
		}
		$arrPrizes = $arrNewPrizes;

		// Build the data sets required for the store
		$arrDistinctPoints = array();
		$arrGroupedPrizes = array();
		$arrPrizesFiltered = array();
		foreach ($arrPrizes as $objPrize)
		{
			if (
				// Check if there are items still in stock
				$objPrize->prize_count > 0
				&& $objPrize->points > 0
				// Check if the item is an add on and if so require legacy listing
				&& !($objPrize->add_on_restricted && !isset($arrHQPrizesHash[$objPrize->prize_name]))
				&& (
					$objPrize->parent_prize_id > 0
					|| (
						(
							$objPrize->prize_type != "Template"
							|| isset($arrHQPrizesHash[$objPrize->prize_name])
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
					$objPrize->add_on_restricted && isset($arrHQPrizesHash[$objPrize->prize_name])
					&& strlen($arrHQPrizesHash[$objPrize->prize_name]["item_size"])
				) {
					$objPrize->prize_name .= " size: " . $arrHQPrizesHash[$objPrize->prize_name]["item_size"];
				}

				// Collect prize groups (grouped by points)
				if (!isset($arrDistinctPoints[$objPrize->points]))
					$arrDistinctPoints[$objPrize->points] = 0;
				$arrDistinctPoints[$objPrize->points]++;
				if (!isset($arrGroupedPrizes[$objPrize->points]))
					$arrGroupedPrizes[$objPrize->points] = array();
				$arrGroupedPrizes[$objPrize->points][] = $objPrize;
				$arrPrizesFiltered[] = $objPrize;
			}
		}
		$arrCartItems = array();
		$intPointsOffset = 0;
		if (isset($objCartSession->cartItems))
		{
			foreach ($objCartSession->cartItems as $objCartItem) {
				array_push($arrCartItems, $objCartItem);
				$intPointsOffset += $objCartItem->points;
			}
		}
		$intCurrentBalance -= $intPointsOffset;

		// Send data sets to view
		ksort($arrDistinctPoints);
		$this->view->arrDistinctPoints = $arrDistinctPoints;
		$this->view->arrGroupedPrizes = $arrGroupedPrizes;
		//var_dump($arrGroupedPrizes);exit;
		$this->view->arrPrizes = $arrPrizesFiltered;
		$this->view->intPointsOffset = $intPointsOffset;
		$this->view->current_balance = $intCurrentBalance;
		$this->view->arrCartItems = $arrCartItems;
		$this->view->user = $objUserSession;
	}

	public function storewithdrawAction()
	{

		$user = new Zend_Session_Namespace("userInfo");
		$cart = new Zend_Session_Namespace('cart');
		$kiosk = new Kiosk();

		$this->view->user = $user;

		//calculate current balance
		$total_local_points = $kiosk->getUserPointsTotal($user->user_id);
		$user->current_balance = $this->view->current_balance = $current_balance = $user->remote_balance + $total_local_points;

		$this->view->current_balance = $user->current_balance;

		if ($this->_request->isPost())
		{
			if (isset($cart))
			{
				if (count($cart->cartItems) > 0)
				{
					//echo "asdasd" . $cart->cartItems->prize_id; exit;

					foreach ($cart->cartItems as $key => $value) {
						$prize_id = $cart->cartItems[$key]->prize_id;
						$quantity = $cart->cartItems[$key]->quantity;
						$intResult = $kiosk->insertUserPrize($user->user_id, $prize_id, $quantity);
						$intResult2 = $kiosk->insertUserPoints($user->user_id, $prize_id, $quantity);
						$intResult3 = $kiosk->insertUserRules($user->institution_id, $user->user_id, $prize_id);
						$intResult4 = $kiosk->adjust_prize_quantity($prize_id, $quantity);
					}
					unset($cart->cartItems);
				}
			}
		}

		// Get all the checked out user prizes //
		$this->view->arrCheckedOutUserPrizes = $kiosk->getCheckedOutUserPrizes($user->user_id);
	}

	public function displayCartAction()
	{
		$cart = new Zend_Session_Namespace('cart');
		foreach ($cart->cartItems as $cartItem) {
			echo "PRIZE ID:" . $cartItem->prize_id . " NAME:" . $cartItem->prize_name . " QUANTITY:" . $cartItem->quantity . "<br />";
		}
		exit;
	}

	/**
	* Function that updates the cart session variable
	**/
	public function addcartitemAction()
	{
		$kiosk = new Kiosk();
		$intPrizeId = $this->getRequest()->getParam('prize_id');
		$intQuantity = $this->getRequest()->getParam('quantity');

		$user = new Zend_Session_Namespace("userInfo");
		$cart = new Zend_Session_Namespace('cart');

		//check if there is any rules that apply to this item
		$result = $kiosk->prize_rules_apply($intPrizeId, $intQuantity, $this->_kiosk_user_session_data->user_id);
		//$result->response_code = 0;
		$this->view->rules = $result;
		if ($intPrizeId > 0 && $result->response_code == 1)
		{

			$objCartItem = new CartItem($intPrizeId, $intQuantity);

			if (isset($cart->cartItems))
			{
				// If the prize is already in the cart //
				if (isset($cart->cartItems[$intPrizeId]))
				{

					// We need to know what the quantity was before //
					$intOldQuantity = $cart->cartItems[$intPrizeId]->quantity;

					//set price for item
					$cart->cartItems[$intPrizeId]->points = $objCartItem->points;

					if ($intQuantity > $intOldQuantity)
					{
						// If they have bought more then we deduct from the users balance //
						$difference = $intQuantity - $intOldQuantity;
						$minus_cost = $difference * $objCartItem->points;
						$user->current_balance = $user->current_balance - $minus_cost;
					}
					elseif ($intQuantity < $intOldQuantity)
					{
						// If they have bought less then we add back to the users balance //
						$difference =  $intOldQuantity - $intQuantity;
						$add_cost = $difference * $objCartItem->points;
						$user->current_balance = $user->current_balance + $add_cost;
					}

					if ($intQuantity > 0)
						// If there is a quantity then the cart is updated
						$cart->cartItems[$intPrizeId] = $objCartItem;
					else
						// If the quantity is 0 then the prize is removed from the cart
						unset($cart->cartItems[$intPrizeId]);
				}
				else
				{
					// If the prize is not in the cart then it is added and the total cost is deducted from the balance //
					$cart->cartItems[$intPrizeId] = $objCartItem;
					$user->current_balance = $user->current_balance - ($objCartItem->quantity * $objCartItem->points);
				}
			}
			else
			{
				// If there are no items in the cart then the cart is instantiated and the //
				// prize is added to the cart and the cost is deducted from the balance    //
				$cart->cartItems = array();
				$cart->cartItems[$intPrizeId] = $objCartItem;
				$user->current_balance = $user->current_balance - ($objCartItem->quantity * $objCartItem->points);
			}

			$this->view->arrCartItems = $cart->cartItems;
			$this->view->current_balance = $user->current_balance;
		}else{
			$this->view->arrCartItems = $cart->cartItems;
			$this->view->current_balance = $user->current_balance;
		}

	}

	public function emptyCartAction()
	{
		unset($_SESSION["cart"]);
		$cart = new Zend_Session_Namespace('cart');
		$this->_redirect('kiosk/store');
	}

	/**
	 * Function checks the status of the order in the user_prize table to see
	 * if it has been already printed. If not, it updates status and inserts it
	 * into orders table. Sets view->isPrinted = 0 so we can print from view and
	 * generates a random serial number to be used with the barcode
	 *
	 * Otherwise it sets view->isPrinted = 1 and view->msg ="ERROR - duplicate"
	 * so we display an alert instead of printing the voucher
	 *
	 */
	public function printVoucherAction()
	{
		$user_prize_id		= $this->_request->getParam('id');
		$description 		= $this->_request->getParam('description');
		$kiosk 				= new Kiosk();

		$this->view->description = $description;


		//check if we have a legit id for user_prize
		try	{
			$isExist = $kiosk->userPrizeExist($user_prize_id);
		}
		catch (Zend_Exception $e) {
			echo "Error message: KC-PV:ALKJSD " . $e->getMessage() . "\n";
			$this->view->isPrinted = 1;
			$this->view->msg = "There was an error looking up this order.";
			return;
		}

		//return if the item doesnt exist
		if(!$isExist){
			$this->view->isPrinted = 1;
			$this->view->msg = "This item with item id: ".$user_prize_id." does not exist.";
			return;
		}

		//check to see if the item has been already printed
		try	{
			$isPrinted = $kiosk->prizePrinted($user_prize_id);
		}
		catch (Zend_Exception $e) {
			echo "Error message: KC-PV:ALKSI8 " . $e->getMessage() . "\n";
			$this->view->isPrinted = 1;
			$this->view->msg = "There was a system error while printing.";
			return;
		}

		//it hasnt been printed, we are good to go
		if(!$isPrinted){
			$serial = $this->_generateSerial();

			//update user_prize table to "printed"
			try	{
				$kiosk->updateUserPrize($user_prize_id, $serial);
			}
			catch (Zend_Exception $e) {
				echo "Error message: KC-PV:SJDFHG " . $e->getMessage() . "\n";
				$this->view->isPrinted = 1;
				$this->view->msg = "There was a system error while updating the database.";
				return;
			}

			//capture order
			try	{
				$kiosk->insertOrder($user_prize_id, $serial);
			}
			catch (Zend_Exception $e) {
				echo "Error message: KC-PV:QIRYUF " . $e->getMessage() . "\n";
				exit;
				$this->view->isPrinted = 1;
				$this->view->msg = "There was a system error while capturing the order.";
				return;
			}

			$this->view->serial = $serial;
			$this->view->isPrinted = 0;
			$this->view->msg = "";
		}
		else{
			$this->view->isPrinted = 1;
			$this->view->msg = "This order has been printed already";
		}


	}

	private function _generateSerial()
	{
		$floor		= 1000000000;
		$ceiling		= 9999999999;
		$chunks		= 1;  //format: 111-222-333
		$serial 		= "";

		// seed with microseconds
		for($i=0; $i<$chunks; $i++){
			$serial .= "-";
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * 100000);

			//seed the random number generator
			srand($seed);
			$serial .= rand($floor, $ceiling);
		}
		return substr($serial, 1);

	}

	/**
	 * This function will generate a barcode
	 *
	 * @param string	serial
	 *
	 * @return none
	 */
	public function getBarcodeAction()
	{
		$serial 	= $this->_request->getParam('serial');
		if(!$serial || $serial == "")
		{
			$serial = $serial;
		}
			//header('Content-type: image/gif');
			$config = new Zend_Config(array(
					'barcode'        => 'code39',
					'setBarHeight'   => '50',
					'barcodeParams'  => array('text' => $serial),
					'renderer'       => 'image',
					'rendererParams' => array('imageType' => 'gif')
			));

			$renderer = Zend_Barcode::factory($config);
			$renderer->render();
	}

	public function getBarcodeLegacyAction()
	{
		$serial 	= $this->_request->getParam('serial');
		if(!$serial || $serial == "") $serial = $serial;

		header('Content-type: image/png');
		$img = $this->_barcode128c($serial);
		imagepng($img);
		imagedestroy($img);
		exit;
	}

	/**
	 *This function generates a barcode of unknown type
	 */
	private function _barcode128c($text)
	{
	  $code = array();
	  $code[0] = "212222";  // " "
	  $code[1] = "222122";  // "!"
	  $code[2] = "222221";  // "{QUOTE}"
	  $code[3] = "121223";  // "#"
	  $code[4] = "121322";  // "$"
	  $code[5] = "131222";  // "%"
	  $code[6] = "122213";  // "&"
	  $code[7] = "122312";  // "'"
	  $code[8] = "132212";  // "("
	  $code[9] = "221213";  // ")"
	  $code[10] = "221312"; // "*"
	  $code[11] = "231212"; // "+"
	  $code[12] = "112232"; // ","
	  $code[13] = "122132"; // "-"
	  $code[14] = "122231"; // "."
	  $code[15] = "113222"; // "/"
	  $code[16] = "123122"; // "0"
	  $code[17] = "123221"; // "1"
	  $code[18] = "223211"; // "2"
	  $code[19] = "221132"; // "3"
	  $code[20] = "221231"; // "4"
	  $code[21] = "213212"; // "5"
	  $code[22] = "223112"; // "6"
	  $code[23] = "312131"; // "7"
	  $code[24] = "311222"; // "8"
	  $code[25] = "321122"; // "9"
	  $code[26] = "321221"; // ":"
	  $code[27] = "312212"; // ";"
	  $code[28] = "322112"; // "<"
	  $code[29] = "322211"; // "="
	  $code[30] = "212123"; // ">"
	  $code[31] = "212321"; // "?"
	  $code[32] = "232121"; // "@"
	  $code[33] = "111323"; // "A"
	  $code[34] = "131123"; // "B"
	  $code[35] = "131321"; // "C"
	  $code[36] = "112313"; // "D"
	  $code[37] = "132113"; // "E"
	  $code[38] = "132311"; // "F"
	  $code[39] = "211313"; // "G"
	  $code[40] = "231113"; // "H"
	  $code[41] = "231311"; // "I"
	  $code[42] = "112133"; // "J"
	  $code[43] = "112331"; // "K"
	  $code[44] = "132131"; // "L"
	  $code[45] = "113123"; // "M"
	  $code[46] = "113321"; // "N"
	  $code[47] = "133121"; // "O"
	  $code[48] = "313121"; // "P"
	  $code[49] = "211331"; // "Q"
	  $code[50] = "231131"; // "R"
	  $code[51] = "213113"; // "S"
	  $code[52] = "213311"; // "T"
	  $code[53] = "213131"; // "U"
	  $code[54] = "311123"; // "V"
	  $code[55] = "311321"; // "W"
	  $code[56] = "331121"; // "X"
	  $code[57] = "312113"; // "Y"
	  $code[58] = "312311"; // "Z"
	  $code[59] = "332111"; // "["
	  $code[60] = "314111"; // "\"
	  $code[61] = "221411"; // "]"
	  $code[62] = "431111"; // "^"
	  $code[63] = "111224"; // "_"
	  $code[64] = "111422"; // "`"
	  $code[65] = "121124"; // "a"
	  $code[66] = "121421"; // "b"
	  $code[67] = "141122"; // "c"
	  $code[68] = "141221"; // "d"
	  $code[69] = "112214"; // "e"
	  $code[70] = "112412"; // "f"
	  $code[71] = "122114"; // "g"
	  $code[72] = "122411"; // "h"
	  $code[73] = "142112"; // "i"
	  $code[74] = "142211"; // "j"
	  $code[75] = "241211"; // "k"
	  $code[76] = "221114"; // "l"
	  $code[77] = "413111"; // "m"
	  $code[78] = "241112"; // "n"
	  $code[79] = "134111"; // "o"
	  $code[80] = "111242"; // "p"
	  $code[81] = "121142"; // "q"
	  $code[82] = "121241"; // "r"
	  $code[83] = "114212"; // "s"
	  $code[84] = "124112"; // "t"
	  $code[85] = "124211"; // "u"
	  $code[86] = "411212"; // "v"
	  $code[87] = "421112"; // "w"
	  $code[88] = "421211"; // "x"
	  $code[89] = "212141"; // "y"
	  $code[90] = "214121"; // "z"
	  $code[91] = "412121"; // "{"
	  $code[92] = "111143"; // "|"
	  $code[93] = "111341"; // "}"
	  $code[94] = "131141"; // "~"
	  $code[95] = "114113"; // 95
	  $code[96] = "114311"; // 96
	  $code[97] = "411113"; // 97
	  $code[98] = "411311"; // 98
	  $code[99] = "113141"; // 99
	  $code[100] = "114131"; // CODE_B
	  $code[101] = "311141"; // CODE_A
	  $code[102] = "411131"; // FUNC_1
	  $code[103] = '211412'; // START_A
	  $code[104] = '211214'; // START_B
	  $code[105] = '211232'; // START_C
	  $code[106] = '2331112'; // STOP

	  if(strlen($text) % 2) $text = '0' . $text;

	  $bar_pattern = $code[105];
	  $checksum = 105;

	  for($i = 0; $i < strlen($text); $i+=2) {
		 $val = intval($text[$i] . $text[$i+1]);
		 $checksum += $val * ($i/2 + 1);
		 $bar_pattern .= $code[$val];
	  }
	  $bar_pattern .= $code[$checksum % 103];
	  $bar_pattern .= $code[106];

	  $bar_width = (strlen($bar_pattern)-1)/6*11+2; //each pattern is 6 chars long, and all patterns are 11 pixels wide, except the stop code

	  $img = ImageCreate($bar_width*3, 50);
	  $black = ImageColorAllocate($img, 0, 0, 0);
	  $white = ImageColorAllocate($img, 255, 255, 255);
	  imagefill($img, 0, 0, $white);

	  $color = true;
	  $xpos = 0;
	  for($i = 0; $i < strlen($bar_pattern); $i++) {
		 $width = intval($bar_pattern[$i])*3;
		 if($color) imagefilledrectangle($img, $xpos, 0, $xpos + $width-1, 50, $black);
		 $xpos += $width;
		 $color = !$color;
	  }

	  return $img;
	}


	public function dummyLoginAction()
	{
		$kiosk = new Kiosk();

		//this is a test function to initialize userInfo to proper values
		//fill it with test data
		$userx = new Zend_Session_Namespace("userInfo");
		$userx->current_balance = 1000;
		$userx->id = 21;
		$userx->institution_id = 4;
		$userx->first_name = "School";
		$userx->last_name = "Child";
		$userx->institution_name = "IMS 1 network CAMP";
		$userx->current_balance	= 100000;
		$userx->class_id = 1;

		$userx->classes = $kiosk->getUserClasses($userx->id);

		//var_dump($_SESSION['userInfo']);
		exit;
	}

	public function loginAction()
    {
		$kiosk = new Kiosk();

        //this is a test function to initialize userInfo to proper values
        //fill it with test data
        $userx = new Zend_Session_Namespace("userInfo");
        $userx->current_balance = 1000;
		$userx->id = 15;
        $userx->institution_id = 5;
        $userx->first_name = "Yitzchok Chaim";
        $userx->last_name = "Atkins";
        $userx->institution_name = "Cheder Lubavitch Chicago Boys";
		$userx->classes = $kiosk->getUserClasses($userx->id);
		$userx->image_id = 1;
		$userx->institution_image_id = 5;

		$this->_redirect('kiosk/store');
    }
	
	private function encrypt_decrypt($action, $string) {
		$output = false;
	
		$encrypt_method = "AES-256-CBC";
		$secret_key = 'Tzivos Hashem mashpia key';
		$secret_iv = 'Tzivos Hashem mashpia iv';
	
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
	
		if( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		}
		else if( $action == 'decrypt' ){
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
	
		return $output;
	}

	public function autoLoginAction()
	{
		if (isset($_SESSION['kiosk_user_session_data']))
			unset($_SESSION['kiosk_user_session_data']);
		if (isset($_SESSION['userInfo']))
			unset($_SESSION['userInfo']);

		//$kiosk = new Kiosk();
		//$objUsers = new Users();
		//$objLegacy = new Legacy();
		//$objConfig = new Config(); 

		$user = new Zend_Session_Namespace("userInfo");
		if ($this->_request->getParam('encrypted') == '0') $barcode = $this->_request->getParam('uc');
		else $barcode = $this->encrypt_decrypt('decrypt', $this->_request->getParam('uc'));
		$code = substr($barcode, 1);
		$user->return_url = @$_SERVER["HTTP_REFERER"];
		$user->barcode = $code;
		$user->remote_balance = $this->encrypt_decrypt('decrypt', $this->_request->getParam('pb'));
		/*
		$date = NULL;
		$strDate = "";
		$url = 'http://mashpia.com/get_points.php?s='.$barcode.'&p='.$p.'&d='.$date.$strDate;
		//get user points
		//get the user's available points on the local system
		ob_start();
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, $url);
		curl_exec($objCurl);
		$remote_balance = ob_get_contents();
		curl_close($objCurl);
		ob_end_clean();
		if(!preg_match("/^[0-9\.]+$/", $remote_balance))
		{
			echo text("Sorry, there was an error") . ": CK-AL102-SDF897";
			exit;
		}
		$user->remote_balance = $remote_balance*1;
		/*
		$url = 'http://mashpia.com/get_thhq_prizes.php?s=' . $barcode;
		ob_start();
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, $url);
		curl_exec($objCurl);
		$arrTHHQPrizes = ob_get_contents();
		curl_close($objCurl);
		ob_end_clean();

		$user->arrTHHQPrizes = unserialize($arrTHHQPrizes);
							
		$objUser = $objLegacy->import_student(array(
			"bar_code" => $barcode
		));
		if (!$objUser)
		{
			print text("Sorry, there was an error") . ": CK-AL101-8SDF7D";
			exit;
		}
		if($objUsers->KioskAuthenticate($barcode)){
			$arrConfigOptions = $objConfig->load(array(
				"set" => array("kiosk"),
				"key" => array("import_mission_miles"),
				"institution_id" => $this->_kiosk_user_session_data->institution_id
			));
			$boolImportMissionMiles = isset($arrConfigOptions['kiosk']['import_mission_miles']) && $arrConfigOptions['kiosk']['import_mission_miles'] != "0";
			//echo $this->_kiosk_user_session_data->user_id; exit;
			$user->institution_id = $this->_kiosk_user_session_data->institution_id;
			$user->institution_name = $this->_kiosk_user_session_data->institution_name;
			$user->institution_image_id = $this->_kiosk_user_session_data->institution_logo;
			$user->user_id = $this->_kiosk_user_session_data->user_id;
			$user->first_name = $this->_kiosk_user_session_data->first_name;
			$user->last_name = $this->_kiosk_user_session_data->last_name;
			$user->user_photo_id = $this->_kiosk_user_session_data->user_picture;
			$user->magic = $this->_request->getParam('magic');
			$user->jd_point_restriction = $this->_request->getParam('jd_point_restriction');
			$user->remote_balance = 0;
			$user->current_balance = 0;
			$user->classes = $kiosk->getUserClasses($this->_kiosk_user_session_data->user_id);
			$user->return_url = @$_SERVER["HTTP_REFERER"];

			//get store configuration setting
			$store = new Store();
			$configuration = $store->store_configuration_get($user->institution_id);
			$user->config->army_points = $configuration->army_points;
			$user->config->base_points = $configuration->base_points;

			/*if($configuration->army_points == 1 && $configuration->base_points == 1){
				$p = "both";
			}elseif($configuration->army_points == 1 && $configuration->base_points == 0){
				$p = "army";
			}elseif($configuration->army_points == 0 && $configuration->base_points == 1){
				$p = "base";
			}else{
				$p = "none";
			}*/
			/*
			$p = "army";

			if (
				$user->magic == "true"
				|| $user->institution_id == 15
				|| !$boolImportMissionMiles
			) {
				$user->remote_balance = 0;
			}
			else
			{
				if(!empty($configuration->created)){
					$date = strtotime($configuration->created);
				}else{
					$date = NULL;
				}
				$strDate = "";
				//if ($user->institution_id == 128) {
					//$strDate .= "&start_date=" . mktime(0, 0, 0, 8, 18, 2013);
				//}
				$url = 'http://mashpia.com/get_points.php?s='.$barcode.'&p='.$p.'&d='.$date.$strDate;
				//get user points
				//get the user's available points on the local system
				ob_start();
				$objCurl = curl_init();
				curl_setopt($objCurl, CURLOPT_URL, $url);
				curl_exec($objCurl);
				$remote_balance = ob_get_contents();
				curl_close($objCurl);
				ob_end_clean();
				if(!preg_match("/^[0-9\.]+$/", $remote_balance))
				{
					echo text("Sorry, there was an error") . ": CK-AL102-SDF897";
					exit;
				}

				$user->remote_balance = $remote_balance*1;
			}
			$url = 'http://mashpia.com/get_thhq_prizes.php?s=' . $barcode;
			ob_start();
			$objCurl = curl_init();
			curl_setopt($objCurl, CURLOPT_URL, $url);
			curl_exec($objCurl);
			$arrTHHQPrizes = ob_get_contents();
			curl_close($objCurl);
			ob_end_clean();

			$user->arrTHHQPrizes = unserialize($arrTHHQPrizes);
		} else{
			$this->_redirect('kiosk-login/reset');
		}
		*/
	}

	public function showimageAction()
	{
		$image_id = $this->_request->getParam("image_id");
		$image = new Image();
		$photo = $image->get_image($image_id);

		// If there is an image then it will be dislayed //
		if ($photo->photo)
		{
			header('Content-type: ' . $photo->photo_type);
			echo $photo->photo;
			exit;
		}
		else
		{
			exit;
		}

	}


	// ****************************** CAMPAIGNS ****************************** //
	public function campaignsloginAction()
	{
		$this->_user_session_data = new Zend_Session_Namespace('userInfo');
		$this->_user_session_data->user_id = $this->_request->getParam("user_id");
        $this->_user_session_data->institution_id = $this->_request->getParam("school_id");;

		$objUser = new Users();
		$this->_user_session_data->user = $objUser->get_user($this->_user_session_data->user_id);

		$objInstitution = new Institutions();
		$this->_user_session_data->institution = $objInstitution->get_institution($this->_user_session_data->institution_id);

		$this->_redirect('kiosk/campaigns');
	}


	public function campaignsAction()
	{
		$this->_user_session_data = new Zend_Session_Namespace('userInfo');
		$this->view->user_data = $this->_user_session_data;

		$objInstitution = new Institutions();
		$this->view->objCampaigns = $objInstitution->get_campaigns_by_institution_id($this->_user_session_data->institution_id);
	}

	public function campaigngoalsAction()
	{
		$this->_user_session_data = new Zend_Session_Namespace('userInfo');
		$this->view->user_data = $this->_user_session_data;

		$this->view->intCampaignId = $this->_request->getParam("campaign_id");

		$objCampaign = new Campaigns();
		$this->view->objCampaign = $objCampaign->get_campaign($this->view->intCampaignId);
		$this->view->intUserRegistered = $objCampaign->get_user_campaign($this->view->user_data->user_id, $this->view->intCampaignId);
	}

	public function campaignenrolloneAction()
	{
		$this->_user_session_data = new Zend_Session_Namespace('userInfo');
		$this->view->user_data = $this->_user_session_data;

		$this->view->intCampaignId = $this->_request->getParam("campaign_id");
	}

	public function campaignenrolltwoAction()
	{
		$this->_user_session_data = new Zend_Session_Namespace('userInfo');
		$this->view->user_data = $this->_user_session_data;

		$this->view->intCampaignId = $this->_request->getParam("campaign_id");

		$objCampaign = new Campaigns();
		$this->view->objCampaign = $objCampaign->get_campaign($this->view->intCampaignId);
	}

	public function campaignenrollthreeAction()
	{
		$this->_user_session_data = new Zend_Session_Namespace('userInfo');
		$this->view->user_data = $this->_user_session_data;

		$this->view->intCampaignId = $this->_request->getParam("campaign_id");

		$objUser = new Users();
		$intUserCampaignId = $objUser->enroll_user_to_campaign($this->view->user_data->user_id, $this->view->user_data->institution->institution_id, $this->view->intCampaignId);

		$objCampaign = new Campaigns();
		$this->view->objCampaign = $objCampaign->get_campaign($this->view->intCampaignId);
	}
	// ****************************** CAMPAIGNS ****************************** //

	// ****************************** MISSIONS ****************************** //
	public function campaignmissionsAction()
	{
		$this->_user_session_data = new Zend_Session_Namespace('userInfo');
		$this->view->user_data = $this->_user_session_data;

		$this->view->intCampaignId = $this->_request->getParam("campaign_id");

		$objCampaign = new Campaigns();
		$this->view->objMissions = $objCampaign->mission_select_campaign_id(array("campaign_id" => $this->view->intCampaignId));
	}
	// ****************************** MISSIONS ****************************** //

	// ****************************** TASKS ****************************** //
	public function campaignmissiontasksAction()
	{
		if ($this->_request->isPost())
		{
			$this->_user_session_data = new Zend_Session_Namespace('userInfo');

			if ($this->_request->getParam('perform') == "add")
			{
				$arrFeilds = array ("user_id" 			=> $this->_user_session_data->user_id,
									"institution_id" 	=> $this->_user_session_data->institution->institution_id,
									"campaign_id" 		=> $this->_request->getParam('campaign_id'),
									"mission_id" 		=> $this->_request->getParam('mission_id'),
									"task_id" 			=> $this->_request->getParam('task_id'),
									"status" 			=> 'Completed',
									"created" 			=> date("Y-m-d H:i:S"));

				$objCampaign = new Campaigns();
				$intSuccess = $objCampaign->set_task_to_completed($arrFeilds);
				print $intSuccess;
			}
			else
			{
				$objCampaign = new Campaigns();
				$intSuccess = $objCampaign->remove_user_campaign($this->_request->getParam('user_campaign_id'));
				print $intSuccess;
			}
			exit;
		}
		else
		{
			$this->_user_session_data = new Zend_Session_Namespace('userInfo');
			$this->view->user_data = $this->_user_session_data;

			$this->view->intCampaignId = $this->_request->getParam("campaign_id");
			$this->view->intMissionId = $this->_request->getParam("mission_id");

			$objMission = new Missions();
			$this->view->strMissionName = $objMission->get_mission_name($this->view->intMissionId);
			$this->view->objTasks = $objMission->get_active_tasks_and_status_by_mission_id($this->view->user_data->user_id, $this->view->intMissionId);
		}
	}
	// ****************************** TASKS ****************************** //

	public function displaycampaignphotoAction()
	{
		$intCampaignId = $this->_request->getParam("campaign_id");
		$strRGB = $this->_request->getParam("rgb");

		$store = new Store();
		$image = $store->get_campaign_image($intCampaignId, $strRGB);

		// If there is an image then it will be dislayed //
		if ($image->photo)
		{
			header('Content-type: ' . $image->photo_type);
			echo $image->photo;
		}
		// If there is no image then there is a default image displayed //
		else
		{
			header('Content-Type: image/gif');
			readfile("images/back-end/kiosk/noimage.gif");
		}

		exit;

	}

	public function campaignphotosAction()
	{
		if ($this->_request->isPost())
		{
			if (isset($_FILES['image']))
			{
				foreach ($_FILES as $photo)
				{
					$objCampaign = new Campaigns();
					$objCampaign->set_campaign_photo($this->_request->getParam('campaign_id'), $this->_request->getParam('photo_type'), $this->_request->getParam('rgb'), $photo);

				}

			}
		}

		$objCampaign = new Campaigns();
		$this->view->objCampaigns = $objCampaign->campaigns_select(1);
	}

	public function rankphotosAction()
	{
		if ($this->_request->isPost())
		{
			if (isset($_FILES['image']))
			{
				foreach ($_FILES as $photo)
				{
					$objCampaign = new Campaigns();
					$objCampaign->set_rank_photo($this->_request->getParam('rank_id'), $this->_request->getParam('rbp'), $this->_request->getParam('photo_type'), $photo);
				}

			}
		}

		$objCampaign = new Campaigns();
		$this->view->objRanks = $objCampaign->ranks_select();
	}

	public function logoutAction()
	{
		$strPath = "http://mashpia.com/statement.php";
		$user = new Zend_Session_Namespace("userInfo");
		$bp = "http://www.mashpia.com/";
		if ($user->return_url)
		{
			$strPath = $user->return_url;
			$arrPath = parse_url($strPath);
			$bp = $arrPath["scheme"] . "://" . $arrPath["host"] . "/";
		}
		switch($this->_request->getParam('target')){
			case "home":
				$strPath = $bp . "statement.php";
				break;
			case "logout":
				$strPath = $bp . "logout.php?n=statement.php";
				break;
		}

		Zend_Session::namespaceUnset('kiosk_user_session_data');
		if (isset($_SESSION['kiosk_user_session_data']))
			unset($_SESSION['kiosk_user_session_data']);
		if (isset($_SESSION['userInfo']))
			unset($_SESSION['userInfo']);
		$this->_redirect($strPath);
		exit;
	}

	/**
	 * These function is there just temporarily to migrate data over from
	 * the old db to the new one and should be deleted eventually
	 */
	public function migrateUserAction()
	{
		$host = 'mashpia.icorpa.com';
		$username = 'mashpia_prod';
		$pwd = '9mSawieHde7S';

		$db_old_tables = 'mashpia_old';
		$db_production = 'mashpia_production';
		$db_devel = 'mashpia_devel';

		echo '<h1>USERS WITH NO PERMISSIONS IN PRODUCTION DB</h1>';

		$dbLink = mysql_connect($host, $username, $pwd);

		$mashpia_old = mysql_select_db($db_old_tables);
		//$mashpia_prod = mysql_select_db($db_production);

		//echo $dbLink . ' - ' . $mashpia_prod . ' - ' . $mashpia_old; exit;

		$sqlUsers = 'SELECT * FROM users';
		$rsUsers = mysql_query($sqlUsers);

		//switch to the production DB
		$mashpia_prod = mysql_select_db($db_production);

		$i = 0;
		$date = date("Y-m-d H:i:s", time());

		while($rowUsers = mysql_fetch_array($rsUsers)){
			//print_r($row); exit;
			//@echo $row['first'] . ' ' . $row['last'] . '<br />';
			//check if permission exists in production database
			$sqlPermissions = 'SELECT * FROM permissions WHERE permissions.user_id='.$rowUsers['user_id'];
			$rsPermissions = mysql_query($sqlPermissions);
			if(mysql_numrows($rsPermissions) == 0 ){
				//echo 'There is no permission for user id: ' .$rowUsers['user_id']."<br />";

				$sql = '
				INSERT INTO permissions
				(
					user_id,
					institution_id,
					permissions,
					default_permission,
					created
				)
				VALUES
				(
					'.$rowUsers['user_id'].',
					'.$rowUsers['school_id'].',
					"Student",
					1,
					"'.$date.'"
				)';
				echo $sql ."<br />";
				$i++;

			}
		}
		echo 'TOTAL missing permissions: ' . $i;

		exit;

	}

	/**
	 * This is just a placeholder function to test different functionalitites
	 * Delete this in production
	 */
	public function testAction()
	{
		$objTest = new Kiosk();
		$user = new Zend_Session_Namespace("userInfo");
		$total = $objTest->getUserPointsTotal($user->user_id);
		echo $total; exit;
	}

}

?>
