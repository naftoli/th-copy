<?php
class Kiosk01Controller extends Zend_Controller_Action
{
	private $_sesh;
	private $objPermission; // permission instance
	private $boolVerbose = 0;

	function preDispatch()
	{
		$query = new QueryGen();
		$this->_sesh = new Zend_Session_Namespace('hebrewschools');

		$arrParams = $this->_request->getParams();
		if ($arrParams["action"] == "index")
			return;

		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));
		/*
		// Load thie session array
		if (
			!$this->_sesh->user_id
			|| !$this->_sesh->permission_id
			|| !$this->_sesh->permission
			|| !$this->_sesh->institution_id
		)
			$this->_redirect('kiosk01/index/' . $strParam);
		$this->objPermission = first($query->permissions__select(array(
			"user_id" => $this->_sesh->user_id,
			"permission_id" => $this->_sesh->permission_id,
			"permission" => $this->_sesh->permission,
			"institution_id" => $this->_sesh->institution_id
		)));
		if (!$this->objPermission)
			$this->_redirect('kiosk01/index/' . $strParam);
		*/
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

		$objHebrewSchools = new HebrewSchools();

		$strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		$this->_sesh = new Zend_Session_Namespace('hebrewschools');
		$this->_sesh->template_style = $strTemplateStyle;

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

	function processbarcodeAction()
	{
		$query = new QueryGen();
		$mixBarCode = $this->_request->getPost("mixBarCode");
		$arrUserClasses = array_hash("class_id", $query->user_classes__select(array(
			"user_id" => $this->_sesh->user_id
		)));

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
			// Find students current class
			$objClass = first($query->classes__select(array(
				"user_id" => $this->_sesh->user_id
			)));

			// Achievement Card
			$this->view->objCard = $objCard = first($query->achievement_cards__select(array(
				"card_serial" => (string) $intCard
			)));
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
	}

	public function storeAction()
	{
		$query = new QueryGen();
		$objPoints = new Points();
		$intLocalPoints = $objPoints->user_points_sum(array(
			"user_id" => $this->_sesh->user_id
		));

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
					}
					$intLocalPoints = $objPoints->user_points_sum(array(
						"user_id" => $this->_sesh->user_id
					));
					$arrResult = array(
						"success" => "true",
						"intLocalPoints" => $intLocalPoints
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
		$intLocalPoints -= $intPointsOffset;

		// Send data sets to view
		$this->view->arrPrizeSizes = $arrPrizeSizes;
		$this->view->arrDistinctPoints = $arrDistinctPoints;
		$this->view->arrGroupedPrizes = $arrGroupedPrizes;
		$this->view->arrPrizes = $arrPrizesFiltered;
		$this->view->intLocalPoints = $intLocalPoints;
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

		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('hebrewschools');
		$this->_helper->layout()->bgColor = "lgreen";
		$this->_helper->layout()->strTitle = "Hebrew Schools";

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

		$arrResult["items_count"] = 0;

		$arrExtraParams = array();
		$arrUserPoints = array();

		$arrPointsParams = array(
			"user_id" => $this->_sesh->user_id,
			"prize_id" => 0,
			"_ORDER" => "created DESC, user_point_id ASC"
		);
		if ($strCampaignType != "all")
			$arrPointsParams["campaign_id"] = $strCampaignType;
		$arrUserPoints = array_hash("user_point_id", $query->user_points__select($arrPointsParams));
		// load achievement cards related to user points
		$arrAchievementCards = array_hash("achievement_card_id", $query->achievement_cards__select(array(
			"achievement_card_id" => array_keys(array_stack("achievement_card_id", $arrUserPoints))
		)));
		// load all campaigns from achievement cards
		$this->view->arrCampaigns = $arrCampaigns = array_hash("campaign_id", $query->campaigns__select(array(
			"campaign_id" => array_keys(array_stack("campaign_id", $arrAchievementCards))
		)));
		$arrTasks = array_hash("task_id", $query->tasks__select(array(
			"task_id" => array_keys(array_stack("task_id", $arrAchievementCards))
		)));

		if (in_array($strDisplayType, array("all", "points")))
		{
			$arrResult["items_count"] += count($arrUserPoints);
			foreach ($arrUserPoints as $objUserPoint)
			{
				$arrDates["user_point_" . $objUserPoint->user_point_id] = $objUserPoint->created ? $objUserPoint->created : 0;
			}
			$arrResourcesCreatedBy = array_stack("resource_name", "created_by", $arrUserPoints);
			$arrAdmins = $arrAdminKeys = array();
			if (isset($arrResourcesCreatedBy["admin_users_manual"]))
				$arrAdminKeys = array_merge_real_recursive($arrAdminKeys, $arrResourcesCreatedBy["admin_users_manual"]);
			if (isset($arrResourcesCreatedBy["direct_transfer"]))
				$arrAdminKeys = array_merge_real_recursive($arrAdminKeys, $arrResourcesCreatedBy["direct_transfer"]);
			if (count($arrAdminKeys))
				$arrAdmins = array_hash("user_id", $query->users__select(array(
					"user_id" => array_keys($arrAdminKeys)
				)));
		}
		if (in_array($strDisplayType, array("all", "purchases")))
		{
			$arrUserPrizes = array_hash("user_prize_id", $query->user_prizes__select(array(
				"user_id" => $this->_sesh->user_id,
				"_ORDER" => "created DESC, user_prize_id ASC"
			)));

			$arrResult["items_count"] += count($arrUserPrizes);
			foreach ($arrUserPrizes as $objUserPrize)
			{
				$arrDates["user_prize_" . $objUserPrize->user_prize_id] = $objUserPrize->created ? $objUserPrize->created : 0;
			}

			$arrUserPointsFromPrizes = array_hash("user_prize_id", $query->user_points__select(array(
				"user_id" => $this->_sesh->user_id,
				"_NOT" => array(
					"prize_id" => 0
				),
				"_ORDER" => "created DESC, user_prize_id ASC"
			)));

			$query->prize__select(array(
				"prize_id" => array_keys(array_stack("prize_id", $arrUserPointsFromPrizes))
			));
			$arrPrizes = array_hash("prize_id", $query->prize__select(array(
				"prize_id" => array_keys(array_stack("prize_id", $arrUserPointsFromPrizes))
			)));
		}


		natcasesort($arrDates);
		$arrDates = array_reverse($arrDates, true);
		$intStart = $boolPost ? $arrPost["navigate_to"] : 1;
		$arrDates = array_slice($arrDates, $intStart-1, $intItemsPerPage);
		$arrResult["first_row_number"] = $intStart;
		$intRow = $intStart;

		$arrResult["rows"] = array();
		foreach ($arrDates as $strKey => $strDate)
		{
			$arrRow = array();
			if (preg_match("/^user_point_([0-9]+)$/", $strKey, $arrMatched))
			{
				$arrRow["user_point"] = $arrUserPoints[$arrMatched[1]];
				if ($arrRow["user_point"]->achievement_card_id > 0)
				{
					if (!isset($arrAchievementCards[$arrRow["user_point"]->achievement_card_id]))
					{
						$arrResult["error"] = text("Sorry, there was an error") . ": CH-TH103-GDG33G";
						break;
					}
					$arrRow["achievement_card"] = $arrAchievementCards[$arrRow["user_point"]->achievement_card_id];
					$arrRow["campaign"] = @$arrCampaigns[$arrRow["achievement_card"]->campaign_id];
					$arrRow["task"] = @$arrTasks[$arrRow["achievement_card"]->task_id];
				}
				if (
					$arrRow["user_point"]->resource_name == "admin_users_manual"
					|| $arrRow["user_point"]->resource_name == "admin_users_manual_store"
					|| $arrRow["user_point"]->resource_name == "direct_transfer"
				)
					$arrRow["admin"] = $arrAdmins[$arrRow["user_point"]->created_by];
			}
			else if (preg_match("/^user_prize_([0-9]+)$/", $strKey, $arrMatched))
			{
				$arrRow["user_prize"] = $arrUserPrizes[$arrMatched[1]];
				if (!isset($arrUserPointsFromPrizes[$arrRow["user_prize"]->user_prize_id]))
				{
					continue;
					$arrResult["error"] = text("Sorry, there was an error") . ": CH-TH102-6JJJK6";
					break;
				}
				$arrRow["user_point"] = $arrUserPointsFromPrizes[$arrRow["user_prize"]->user_prize_id];
				$arrRow["prize"] = $arrPrizes[$arrRow["user_prize"]->prize_id];
			}
			else
			{
				$arrResult["error"] = text("Sorry, there was an error") . ": CH-TH101-3HH4H6";
				break;
			}

			$intTime = strtotime($arrRow["user_point"]->created);

			$arrRow["transaction_date"] = date("d M, Y", $intTime);
			$arrRow["transaction_time"] = date("h:i a", $intTime);
			$arrResult["rows"][$strKey] = $arrRow;

			$intRow++;
		}
		// Load ballances
		foreach ($arrResult["rows"] as $strKey => $arrRow)
		{
			$objUserPoints = first($query->user_points__select(array(
				"_SUM" => "points",
				"_GROUP_BY" => "user_id",
				"user_id" => $this->_sesh->user_id,
				array(
					"_GREATER" => array(
						"points" => 0
					),
					array(
						"_LESSER" => array(
							"points" => 0
						),
						"resource_name" => "admin_users_manual",
					)
				),
				"_ELESSER" => array(
					"created" => @$arrRow["user_point"]->created ? $arrRow["user_point"]->created : 0
				)
			)));
			$arrResult["rows"][$strKey]["total_points"] = intval($objUserPoints->_sum_points);
			$objUserPoints = first($query->user_points__select(array(
				"_SUM" => "points",
				"_GROUP_BY" => "user_id",
				"user_id" => $this->_sesh->user_id,
				"_ELESSER" => array(
					"created" => @$arrRow["user_point"]->created ? $arrRow["user_point"]->created : 0
				)
			)));
			$arrResult["rows"][$strKey]["store_points"] = intval($objUserPoints->_sum_points);
		}
		$objUserPoints = first($query->user_points__select(array(
			"_SUM" => "points",
			"_GROUP_BY" => "user_id",
			"user_id" => $this->_sesh->user_id,
			array(
				"_GREATER" => array(
					"points" => 0
				),
				array(
					"_LESSER" => array(
						"points" => 0
					),
					"resource_name" => "admin_users_manual",
				)
			)
		)));
		$intUserPointsTotal = $objUserPoints->_sum_points;

		$arrResult["last_row_number"] = $intRow;
		$arrResult["rows"] = array_reverse($arrResult["rows"], true);
		if ($boolPost)//$this->_request->getParam("output") == "ajax")
		{
			print json_encode($arrResult);
			exit;
		}
		$this->view->arrResult = $arrResult;

		// Other site required data
		$arrPointsParams = array(
			"_SUM" => "points",
			"_GROUP_BY" => "user_id",
			"user_id" => $this->_sesh->user_id,
			array(
				"_GREATER" => array(
					"points" => 0
				),
				array(
					"_LESSER" => array(
						"points" => 0
					),
					"resource_name" => "admin_users_manual",
				)
			)
		);
		$objUserPoints = first($query->user_points__select($arrPointsParams));
		$this->view->intUserPointsTotal = $objUserPoints->_sum_points;
		$objUserPoints = first($query->user_points__select(array(
			"_SUM" => "points",
			"_GROUP_BY" => "user_id",
			"user_id" => $this->_sesh->user_id
		)));
		$this->view->intUserPointsStore = $objUserPoints->_sum_points;

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