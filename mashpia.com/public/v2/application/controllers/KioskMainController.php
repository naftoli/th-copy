<?php
class KioskMainController extends Zend_Controller_Action
{
	private $_kiosk_user_session_data;
	public $_verbose;

	public $objUsers; // class
	public $objUser; // current user
	public $objPermissions; // class
	public $objPermission; // current user

	function preDispatch()
	{
		$this->_verbose = $this->_request->getParam("verbose");
		//$this->_kiosk_user_session_data = new Zend_Session_Namespace('kiosk_user_session_data');

		$this->objUsers = new Users();
		//$this->objPermissions = new Permissions();
		$this->objInstitutions = new Institutions();
		
		$this->_kiosk_user_session_data = new Zend_Session_Namespace('userInfo');

		$intBarCode = $this->_request->getParam("bar_code");
		if (
			!$intBarCode
			&& $this->_kiosk_user_session_data->barcode>0
			//&& $this->_kiosk_user_session_data->permission_id>0
			//&& $this->_kiosk_user_session_data->institution_id>0
		) {
			/*
			$this->objUser = current($this->objUsers->_users_select(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"bar_code" => $this->_kiosk_user_session_data->bar_code
			)));
			*/
			$this->objUser = first($this->objUsers->getMashpiaUser($this->_kiosk_user_session_data->barcode));
			if (
				!$this->objUser
			) {
				$this->_redirect('kiosk/logout/target/home');
				exit;
			}
			/*
			$objPermission = current($this->objPermissions->_permissions_select(array(
				"permission_id" => $this->_kiosk_user_session_data->permission_id,
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"institution_id" => $this->_kiosk_user_session_data->institution_id
			)));
			if (
				!$objPermission
				|| $objPermission->institution_id != $this->_kiosk_user_session_data->institution_id
				|| $objPermission->user_id != $this->_kiosk_user_session_data->user_id
				|| $objPermission->permission_id != $this->_kiosk_user_session_data->permission_id
			) {
				$this->_redirect('kiosk/logout/target/home');
				exit;
			}
			
			$this->view->objInstitution = first($this->objInstitutions->_institutions_select(array(
				"institution_id" => $this->_kiosk_user_session_data->institution_id
			)));
			*/
			$this->view->objInstitution = first($this->objInstitutions->_institutions_select(array(
				"institution_id" => $this->objUser->school_id
			)));
			
			if (!$this->view->objInstitution)
			{
				print text("Sorry, there was an error") . " CKM-PD101-7DF8D";
				exit;
			}
		}
		/*
		else if ($intBarCode)
		{
			$objKiosk = new Kiosk();
			$objLegacy = new Legacy();

			$objUser = $objLegacy->import_student(array(
				"bar_code" => $intBarCode
			));
			if($this->objUsers->KioskAuthenticate($intBarCode)){
				$objUserInfo->institution_id = $this->_kiosk_user_session_data->institution_id;
				$objUserInfo->institution_name = $this->_kiosk_user_session_data->institution_name;
				$objUserInfo->institution_image_id = $this->_kiosk_user_session_data->institution_logo;
				$objUserInfo->user_id = $this->_kiosk_user_session_data->user_id;
				$objUserInfo->first_name = $this->_kiosk_user_session_data->first_name;
				$objUserInfo->last_name = $this->_kiosk_user_session_data->last_name;
				$objUserInfo->user_photo_id = $this->_kiosk_user_session_data->user_picture;
				$objUserInfo->magic = $this->_request->getParam('magic');
				$objUserInfo->tanya_portal = $this->_request->getParam('tanya_portal');
				$objUserInfo->rank_title = $this->_request->getParam('rank_title');
				$objUserInfo->base_num = $this->_request->getParam('base_num');
				$objUserInfo->remote_balance = 0;
				$objUserInfo->current_balance = 0;
				$objUserInfo->classes = $objKiosk->getUserClasses($this->_kiosk_user_session_data->user_id);
				//get store configuration setting
				$store = new Store();
				$configuration = $store->store_configuration_get($objUserInfo->institution_id);
				$objUserInfo->config->army_points = $configuration->army_points;
				$objUserInfo->config->base_points = $configuration->base_points;
				if($configuration->army_points == 1 && $configuration->base_points == 1){
					$p = "both";
				}elseif($configuration->army_points == 1 && $configuration->base_points == 0){
					$p = "army";
				}elseif($configuration->army_points == 0 && $configuration->base_points == 1){
					$p = "base";
				}else{
					$p = "none";
				}
				/*
				if(!empty($configuration->created)){
					$date = strtotime($configuration->created);
				}else{

				}
				 *
				 */
				/*
				$date = NULL;
				if ($objUserInfo->magic == "true")
				{
					$objUserInfo->remote_balance = 0;
				}
				else
				{
					$url = 'http://mashpia.com/get_points.php?s='.$intBarCode.'&p='.$p.'&d='.$date;
					ob_start();
					$objCurl = curl_init();
					curl_setopt($objCurl, CURLOPT_URL, $url);
					curl_exec($objCurl);
					$remote_balance = ob_get_contents();
					curl_close($objCurl);
					ob_end_clean();
					if(!preg_match("/^[0-9\.]+$/", $remote_balance))
					{
						echo text("Sorry, there was an error") . ": CK-AL101-SDF896";
						exit;
					}

					$objUserInfo->remote_balance = $remote_balance;
				}
				$strPath = "/kiosk-main/view-campaign/campaign_id/1";
				if ($this->_verbose)
					$strPath .= "/verbose/true";
				$this->_redirect($strPath);
			}
		}
		*/
		else
		{
		
			$this->_redirect('kiosk/logout/target/home');
			exit;
		}
	}

	public function indexAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');

		$this->_helper->layout()->bgColor = "green";

		$objPoints = new Points();
		/*
		$this->view->objUser = $objUser = current($this->objUsers->_users_select(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		)));
		*/
		$this->view->objUser = $objUser = first($this->objUsers->getMashpiaUser($this->_kiosk_user_session_data->barcode));
		if (!$objUser)
		{
			print text("Sorry, there was an error") . ": CKM-Ind101-7SD6FD";
			exit;
		}
	}

	public function scratchcardAction()
	{

	}

	public function cardpopAction()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objInstitutions = new Institutions();
		$objAchievementCards = new AchievementCards();
		$objTasks = new Tasks();
		$objPoints = new Points();
		$objClasses = new Classes();
		$objUsers = new Users();

		$intCard = $this->view->scan_card = $this->_request->getParam("card_id");
		
		$this->objUser = $objUser = first($this->objUsers->getMashpiaUser($this->_kiosk_user_session_data->barcode));
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->objUser->school_id
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
			else if($objCard)
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
						$this->view->strScanCodeMessage = "The scratch card entered seems to be used.";
						print 'alert("The scratch card entred seems to be used. If you think this was indeed a new card please contact an administrator about this issue.");';
						exit;
					}
					else
					{
						$intPointsKey = $query->user_points__insert(array(
							"institution_id" => $this->objUser->school_id,
							"scratch_card_id" => $objCard->scratch_card_id,
							"user_id" => $this->objUser->user_id,
							"points" => $objCard->card_points,
							"resource_name" => "scratch_card"
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
						exit;
					}
				}
				$this->view->boolHarvestControlCode = true;
			}
		}
		else
		{
			// Find students current class
			/*
			$objClass = first($objClasses->_classes_select(array(
				"user_id" => $objUserInfo->user_id
			)));
			*/

			// Achievement Card
			$this->view->objCard = $objCard = first($objAchievementCards->_achievement_cards_select(array(
				"card_serial" => $intCard
			)));
			/*
			if (isset($objCard->task_id))
			{
				$objTask = $this->view->objTask = first($objTasks->_tasks_select(array(
					"task_id" => $objCard->task_id
				)));
				if ($objTask)
					$objCampaign = $this->view->objCampaign = first($objCampaigns->_campaigns_select(array(
						"campaign_id" => $objTask->campaign_id
					)));
			}
			*/
			if (!$objCard)
			{
				// Achievement cards sheet probably wasnt printed
				$this->view->strScanCodeMessage = "The scan code was not found in our system. Maybe the bar code wasn't scanned properly.";
			}
			/*
			else if (
				$objCard->task_id
				&& (
					!$objTask
					|| !$objCampaign
				)
			) {
				$this->view->strScanCodeMessage = "Sorry, your scan card is not longer valid.";
			}
			*/
			// check that child is in same institution as card created for
			
			else if ( $objCard->institution_id != $objUser->school_id && $objCard->institution_id > 0 )
			{
				$this->view->strScanCodeMessage = "You are not in the correct base to scan this card.";
			}
			else if (!$objUser->class_id > 0)
			{
				$this->view->strScanCodeMessage = "You must be in a platoon to scan this card.";
			}
			else if ($objCard->class_id > 0 && $objUser->class_id != $objCard->class_id)
			{
				$this->view->strScanCodeMessage = "You are not in the correct platoon to scan this card.";
			}
			else if($objCard->status == "scanned")
			{
				$this->view->strScanCodeMessage = "This card has already been scanned.";
			}
			else if($objCard->status != "scanned")
			{
				/*
				if ($objCard->campaign_id)
				{
					//deposit points to child's account and mark the achievement card as scanned
					$objCampaigns->_user_campaigns_insert(array(
						"user_id"			=> $this->objUser->user_id,
						"institution_id"	=> $this->objUser->school_id,
						"campaign_id"		=> $objCard->campaign_id,
						"mission_id"		=> $objCard->mission_id,
						"task_id"			=> $objCard->task_id,
						"class_id"			=> $objCard->class_id,
						"schedule_date"		=> time(),
						"points_given"		=> $objCard->card_points
					));
				}
				*/
				$query->user_points__insert(array(
					"achievement_card_id" => $objCard->achievement_card_id,
					"user_id"			=> $this->objUser->user_id,
					"campaign_id"		=> $objCard->campaign_id,
					"mission_id"		=> $objCard->mission_id,
					"task_id"			=> $objCard->task_id,
					"institution_id"	=> $this->objUser->school_id,
					"class_id"			=> $objCard->class_id,
					"points"			=> $objCard->card_points,
					"resource_name"		=> "specific achievement card"
				));
				$query->achievement_cards__update(array(
					"where" => array(
						"achievement_card_id" => $objCard->achievement_card_id
					),
					"values" => array(
						"status" => "scanned"
					)
				));
				if ( $this->_request->getParam('mobileKiosk') == 1 ) {
					echo "You just earned " . $objCard->card_points . " points!";
					exit;
				}
			}
		}
		if ( $this->_request->getParam('mobileKiosk') == 1 ) {
			echo $this->view->strScanCodeMessage;
			exit;
		}
	}
	
	public function cardpopmobileAction()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objInstitutions = new Institutions();
		$objAchievementCards = new AchievementCards();
		$objTasks = new Tasks();
		$objPoints = new Points();
		$objClasses = new Classes();
		$objUsers = new Users();
		
		$this->objUser = $objUser = first($this->objUsers->getMashpiaUser($this->_kiosk_user_session_data->barcode));
		$this->view->objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->objUser->school_id
		)));

		// Find students current class
		/*
		$objClass = first($objClasses->_classes_select(array(
			"user_id" => $objUserInfo->user_id
		)));
		*/

		// Achievement Card
		$intCard = $this->_request->getParam("card_id");
		$objCard = first($objAchievementCards->_achievement_cards_select(array(
			"card_serial" => $intCard
		)));
		/*
		if (isset($objCard->task_id))
		{
			$objTask = $this->view->objTask = first($objTasks->_tasks_select(array(
				"task_id" => $objCard->task_id
			)));
			if ($objTask)
				$objCampaign = $this->view->objCampaign = first($objCampaigns->_campaigns_select(array(
					"campaign_id" => $objTask->campaign_id
				)));
		}
		*/
		if (!$objCard)
		{
			// Achievement cards sheet probably wasnt printed
			print "The scan code was not found in our system. Maybe the bar code wasn't scanned properly.";
		}
		/*
		else if (
			$objCard->task_id
			&& (
				!$objTask
				|| !$objCampaign
			)
		) {
			$this->view->strScanCodeMessage = "Sorry, your scan card is not longer valid.";
		}
		*/
		// check that child is in same institution as card created for
		else if ( $objCard->institution_id != $objUser->school_id && $objCard->institution_id > 0 )
		{
			print "You are not in the correct base to scan this card.";
		}
		else if (!$objUser->class_id > 0)
		{
			print "You must be in a platoon to scan this card.";
		}
		else if ($objCard->class_id > 0 && $objUser->class_id != $objCard->class_id)
		{
			print "You are not in the correct platoon to scan this card.";
		}
		else if ($objCard->status == "scanned") 
		{
			print "This card has already been scanned.";
		}
		else if($objCard->status != "scanned")
		{
			$query->user_points__insert(array(
				"achievement_card_id" => $objCard->achievement_card_id,
				"user_id"			=> $this->objUser->user_id,
				"campaign_id"		=> $objCard->campaign_id,
				"mission_id"		=> $objCard->mission_id,
				"task_id"			=> $objCard->task_id,
				"institution_id"	=> $this->objUser->school_id,
				"class_id"			=> $objCard->class_id,
				"points"			=> $objCard->card_points,
				"resource_name"		=> "specific achievement card"
			));
			$query->achievement_cards__update(array(
				"where" => array(
					"achievement_card_id" => $objCard->achievement_card_id
				),
				"values" => array(
					"status" => "scanned"
				)
			));
			print "Congratulations! You have just been awarded " . $objCard->card_points . " points!";
		}
		exit;
	}

	public function profileAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$this->_helper->layout()->bgColor = "red";
		$this->_helper->layout()->strTitle = "User Profile";

		$objUsers = new Users();

		$this->view->objUser = $objUser = current($objUsers->_users_select(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		)));
		if (!$objUser)
		{
			print text("Sorry, there was an error") . ": CKM-Ind101-7SD6FD";
			exit;
		}
	}


	public function campaignsAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$this->_helper->layout()->strTitle = "Campaigns";
		$this->_helper->layout()->bgColor = "blue";
		$objScheduler = new Scheduler();
		$intStart = $objScheduler->_tools->microtime_float();
		$this->view->arrCampaigns = $objScheduler->load_campaigns(array(
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"institution_id" => $this->_kiosk_user_session_data->institution_id,
			"deny" => array("Incremental", "General")
		));
		$this->view->intBMTime = $objScheduler->_tools->microtime_float() - $intStart;
	}

	public function incrementalsProgressAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$this->_helper->layout()->bgColor = "blue";
		$this->_helper->layout()->strTitle = "Incrementals Progress";
		$objScheduler = new Scheduler();
		$objTasks = new Tasks();
		$objCampaigns = new Campaigns();
		$arrCampaigns = $this->view->arrCampaigns = $objScheduler->load_campaigns(array(
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"institution_id" => $this->_kiosk_user_session_data->institution_id,
			"deny" => array("Book", "General")
		));
		foreach ($arrCampaigns as $intKey => $objCampaign)
		{
			if (!$objCampaign->image_smallmed)
			{
				$objHostCampaign = first($objCampaigns->_campaigns_select(array(
					"campaign_id" => $objCampaign->installed_campaign_id
				)));
				$objCampaign->image_smallmed = $objHostCampaign->image_smallmed;
				$arrCampaigns[$intKey] = $objCampaign;
			}
		}

		// Go through the campaigns and establish the olds entry
		$intOldestTask = 0;
		foreach ($arrCampaigns as $objCampaign)
		{
			$objFirstTask = first($objCampaigns->_user_campaigns_select(array(
				"campaign_id" => $objCampaign->campaign_id,
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"_ORDER" => "schedule_date + 0 ASC",
				"_LIMIT" => 1
			)));
			if (!$objFirstTask)
				continue;
			if (!$intOldestTask || $intOldestTask > $objFirstTask->schedule_date)
				$intOldestTask = $objFirstTask->schedule_date-1;
		}
		if (!$intOldestTask)
			$intOldestTask = time();
		$this->view->intOldestTask = $intOldestTask;
		$intOldestTask -= 604800;

		// pages
		$arrPages = array();
		$arrTasksBuffer = array();
		$_DEBUG = 0;
		foreach ($arrCampaigns as $objCampaign)
		{
			if ($_DEBUG)
				var_dump($objCampaign);
			$arrUserTasks = $objCampaigns->_user_campaigns_select(array(
				"campaign_id" => $objCampaign->campaign_id,
				"user_id" => $this->_kiosk_user_session_data->user_id,
			));
			if ($_DEBUG)
				var_dump($arrUserTasks);
			if (!count($arrUserTasks))
				continue;
			for ($intPages = 1; $intPages != 6; $intPages++)
			{
				$arrWeekTasks = array();
				for ($intWeeks = 1; $intWeeks != 9; $intWeeks++)
				{
					$intEpoch = $intOldestTask + (($intWeeks * $intPages) * 604800);
					$intElapsedWeek = $intWeeks * $intPages;

					// Colect this weeks tasks
					foreach ($arrUserTasks as $objUserTask)
					{
						if (!isset($arrWeekTasks[$objUserTask->task_id][$intWeeks]["count"]))
							$arrWeekTasks[$objUserTask->task_id][$intWeeks]["count"] = 0;
						if (
							$objUserTask->schedule_date > $intEpoch
							&& $objUserTask->schedule_date <= $intEpoch + 604800
						) {
							if (!isset($arrTasksBuffer[$objUserTask->task_id]))
								$arrTasksBuffer[$objUserTask->task_id] = first($objTasks->_tasks_select(array(
									"task_id" => $objUserTask->task_id,
									"_ORDER" => "created"
								)));
							$arrWeekTasks[$objUserTask->task_id][$intWeeks] = array(
								"count" => isset($arrWeekTasks[$objUserTask->task_id][$intWeeks]) ? $arrWeekTasks[$objUserTask->task_id][$intWeeks]["count"] + 1 : 0,
								"objUserTask" => $objUserTask,
								"objTask" => $arrTasksBuffer[$objUserTask->task_id]
							);
						}
					}

					$arrPages[$intPages][$objCampaign->campaign_id] = array(
						"objCampaign" => $objCampaign,
						"intWeek" => $intWeeks,
						"epoch" => $intEpoch,
						"arrWeekTasks" => $arrWeekTasks
					);

				}
			}
		}
		if ($_DEBUG)
			exit;
		$this->view->arrPages = $arrPages;
		$this->view->arrTasksBuffer = $arrTasksBuffer;
	}

	public function medalsAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$objScheduler = new Scheduler();
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objMarking = new Marking();
		$objUsers = new Users();

		$intStart = $objScheduler->_tools->microtime_float();

		$this->_helper->layout()->strTitle = "Medals";
		$this->_helper->layout()->bgColor = "blue";

		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-Med101-987SDS";
			exit;
		}

		$this->view->objCampaign = current($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));

		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));

		$objLatestMission = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $objMission->mission_id,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"institution_id" => $this->_kiosk_user_session_data->institution_id,
			"status" => "Completed",
			"_LIMIT" => 1
		)));
		$this->view->intLatestMission = $objLatestMission ? $objLatestMission->mission_increment + 1 : 0;
		$intBatBarEpoch = $objUsers->user_batbar_in_epoch(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		));

		// Schedule proccessing
		$arrMedals = $this->view->arrMedals = $objScheduler->load_book_medals(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"kiosk" => true,
			"capture_end_date" => $intBatBarEpoch - 7 * 86400
			//"load_missions" => true, // load the missions with the tasks into the medals
		));

		$this->view->intBMTime = $objScheduler->_tools->microtime_float() - $intStart;
	}

	public function missionsAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$intMarkWeeks = $this->view->intMarkWeeks = 3;

		$objScheduler = new Scheduler();
		$objScheduler->_VERBOSE = $this->_verbose;
		$intStart = $objScheduler->_tools->microtime_float();

		$this->_helper->layout()->strTitle = "Missions";
		$this->_helper->layout()->bgColor = "blue";

		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		$this->view->medal_hierarchy = $intMedalHierarchy = intval($this->_request->getParam("medal"));

		if (!$intMedalHierarchy)
		{
			print text("Sorry, there was an error") . ": CKM-Mis101-G8GF9D";
			exit;
		}
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-Mis102-DF89F7";
			exit;
		}

		$objMedals = new Medals();
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objMarking = new Marking();
		$objUsers = new Users();

		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objMission)
		{
			print text("Sorry, there was an error") . ": CKM-Mis103-78SD6F";
			exit;
		}

		// Latest mission
		$objUserCampaign = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $objMission->mission_id,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"status" => "Completed",
			"_LIMIT" => 1
		)));
		$this->view->intLatestMission = $objUserCampaign ? $objUserCampaign->mission_increment : -1;

		$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"campaign_id" => $intCampaign,
			"capture_end_medal" => $intMedalHierarchy-1
		));
		for ($intItr=0; $intItr!=$intMarkWeeks+1; $intItr++)
		{
			array_pop($arrPendingMissions);
		}
		$this->view->arrCurrentMission = array_pop($arrPendingMissions);
		$arrPendingHash = array();
		foreach ($arrPendingMissions as $arrMission)
		{
			$arrPendingHash[$arrMission["epoch"]] = 1;
		}
		$this->view->arrPendingHash = $arrPendingHash;

		$intBatBarEpoch = $objUsers->user_batbar_in_epoch(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		));

		$arrSchedule = $this->view->arrSchedule = current($objScheduler->load_book_medals(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"load_missions" => true, // load the missions with the tasks into the medals
			"capture_start_medal" => $intMedalHierarchy-1,
			"capture_end_medal" => $intMedalHierarchy-1,
			"kiosk" => true,
			"capture_end_date" => $intBatBarEpoch - 7 * 86400
		)));
		$this->view->arrBookLines = $objScheduler->harvest_schedule_book_lines($arrSchedule);

		$this->view->intMedals = count($objScheduler->load_book_medals(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"kiosk" => true,
			"capture_end_date" => $intBatBarEpoch - 86400 * 7
			//"load_missions" => true, // load the missions with the tasks into the medals
		)))-1;
		$this->view->intBMTime = $objScheduler->_tools->microtime_float() - $intStart;
	}

	public function tasksAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$objScheduler = new Scheduler();
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objBooks = new Books();

		$intStart = $objScheduler->_tools->microtime_float();

		$this->_helper->layout()->strTitle = "Tasks";
		$this->_helper->layout()->bgColor = "blue";

		$this->view->mission_time = $intMissionTime = intval($this->_request->getParam("missiontime"));
		$this->view->medal_hierarchy = $intMedalHierarchy = intval($this->_request->getParam("medal"));
		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intMedalHierarchy)
		{
			print text("Sorry, there was an error") . ": CKM-Tas101-23WES3";
			exit;
		}
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-Tas102-456RT5";
			exit;
		}
		if (!$intMissionTime)
		{
			print text("Sorry, there was an error") . ": CKM-Tas103-32EWF2";
			exit;
		}

		// Load the campaign
		$this->view->objCampaign = current($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));

		// Load the mission - Only intended for a campaign with only 1 mission
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objMission)
		{
			print text("Sorry, there was an error") . ": MS-LBM101-7DF6GF";
			exit;
		}

		// Schedule proccessing
		$arrSchedule = current($objScheduler->load_book_medals(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"load_missions" => true, // load the missions with the tasks into the medals
			"capture_start_medal" => $intMedalHierarchy-1,
			"capture_end_medal" => $intMedalHierarchy-1,
			"kiosk" => true
		)));

		// Find the current mission
		foreach ($arrSchedule["missions"] as $arrMission)
		{
			if ($arrMission["epoch"] == $intMissionTime)
			{
				$arrMissionFound = $arrMission;
				break;
			}
		}
		if (!isset($arrMissionFound))
		{
			print text("Sorry, there was an error") . ": CKM-Tas104-DFG76F";
			exit;
		}

		// Load the corresponding tasks / lines
		$arrTaskData = array();
		$intStart = current($arrMissionFound["tasks"]);
		$intEnd = end($arrMissionFound["tasks"]);
		//$intEnd = end($arrMissionFound["tasks"]) + (end($arrMissionFound["tasks"]) - $intStart); // another option i thought up
		$intFantomLine = floor($intStart + $arrMission["velocity"]-0.01);
		if ($intFantomLine > $intEnd)
		{
			$intEnd = $intFantomLine-1;
			$intStart--;
		}
		for ($intLine=floor($intStart); $intLine<=floor($intEnd); $intLine++)
		{
			$arrTaskData[$intLine] = current($objBooks->_book_lines_select(array(
				"book_id" => $objMission->book_id,
				"line_hierarchy" => $intLine+1
			)));
		}
		$arrMissionFound["tasks"] = $arrTaskData;
		$this->view->arrSchedule = $arrMissionFound;

		$this->view->intBMTime = $objScheduler->_tools->microtime_float() - $intStart;

		//var_dump($this->view->arrSchedule);exit;

		// Loop through the missions to find the tasks to the current mission
		//$arrSchedule

		/*
		$arrMedals = $objScheduler->load_medals(array(
			"campaign_id" => $intCampaign,
			"institution_id" => $this->_kiosk_user_session_data->institution_id,
			"user_id" => $this->_kiosk_user_session_data->user_id
			//"medal_hierarchy" => $intMedalHierarchy - 1
		));
		//var_dump($arrMedals);exit;
		$arrTasks = $this->view->arrTasks = $arrMedals[$intMedalHierarchy - 1][$intTime][$intMission];

		//var_dump($arrTasks);exit;


		$arrSchedule = $objScheduler->load_book_schedule(array(
			"mission_id" => $intMission,
			"institution_id" => $this->_kiosk_user_session_data->institution_id,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"capture_start_date" => @$arrParams["capture_start_date"]
		));

		var_dump($arrSchedule);
		*/
	}

	public function viewCampaignAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		//print "user_id: " . $this->_kiosk_user_session_data->user_id . " <br>\n";

		$intMarkWeeks = $this->view->intMarkWeeks = 3;

		$this->_helper->layout()->strTitle = "View Campaigns";
		$this->_helper->layout()->bgColor = "blue";

		$intCampaign = intval($this->_request->getParam("campaign_id"));

		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-VC101-D87F8D";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objLadders = new Ladders();
		$objScheduler = new Scheduler();
		$objMissions = new Missions();
		$objMarking = new Marking();
		$objUsers = new Users();
		$objBooks = new Books();
		$this->view->strLastAction = $this->_request->getParam("lastaction");
		$this->view->intOldLadder = $this->_request->getParam("oldladder");
		$objCampaign = $this->view->objCampaign = $objCampaigns->campaign_select_id($intCampaign);
		if (!$objCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-VC102-SD67FD";
			exit;
		}
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));

		$objEnrollment = $this->view->objEnrollment =  current($objCampaigns->_user_campaigns_select(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"status" => "Enrollment"
		)));

		$this->view->objUser = current($objUsers->_users_select(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		)));

		$this->view->arrArmy = $objMarking->armywide_campaign_holding(array(
			"campaign_id" => $intCampaign,
			"institution_type" => "School"
		));

		if ($objEnrollment)
		{
			$this->view->intLadderVelocity = $objLadders->campaign_user_ladder_velocity(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"campaign_id" => $intCampaign,
				"institution_id" => $this->_kiosk_user_session_data->institution_id
			));
			$this->view->objSchedulingParams = current($objScheduler->_scheduling_params_select(array(
				"mission_id" => $objMission->mission_id,
				"task_id" => 0
			)));

			$intYearStart = capture_start_date;
			$intYearStart = $intYearStart < $objEnrollment->schedule_date ? $objEnrollment->schedule_date : $intYearStart;
			$arrYearSchedule = $objScheduler->load_book_schedule(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"institution_id" => $this->_kiosk_user_session_data->institution_id,
				"mission_id" => $objMission->mission_id,
				"capture_start_date" => $intYearStart,
				"capture_end_date" => capture_end_date,
				"kiosk" => true
			));
			$arrYearEnd = end($arrYearSchedule);
			$this->view->intLineEnd = end($arrYearEnd["tasks"])+1;
			//var_dump($this->view->intLineEnd);exit;
			$arrSchedule = $objScheduler->load_book_schedule(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"institution_id" => $this->_kiosk_user_session_data->institution_id,
				"mission_id" => $objMission->mission_id,
				"capture_start_date" => mktime(0, 0, 0, 0, 0, date("y")+1),
				"capture_end_date" => mktime(0, 0, 0, 0, 0, date("y")+2)
			));

			$strCurrentStatus = $objMarking->user_campaign_status(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"campaign_id" => $intCampaign
			));

			$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"campaign_id" => $intCampaign,
				"extra_weeks" => 1
			));
			array_pop($arrPendingMissions);
			$this->view->arrPendingMissions = $arrPendingMissions;

			$intBatBarEpoch = $objUsers->user_batbar_in_epoch(array(
				"user_id" => $this->_kiosk_user_session_data->user_id
			));

			$intNextBirthEpoch = $objUsers->user_next_birthdate_epoch(array(
				"user_id" => $this->_kiosk_user_session_data->user_id
			));
			// get the line of the user on bar/bat
			$this->view->objBarBatLocation = $objBooks->book_location_by_date(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"institution_id" => $this->_kiosk_user_session_data->institution_id,
				"mission_id" => $objMission->mission_id,
				// Bar/Bat Mitzvah date
				"capture_start_date" => mktime(0, 0, 0, date("n", $intBatBarEpoch), date("j", $intBatBarEpoch), date("Y", $intBatBarEpoch)) - 7 * 86400,
				"capture_end_date" => mktime(0, 0, 0, date("n", $intBatBarEpoch), date("j", $intBatBarEpoch), date("Y", $intBatBarEpoch))
			));
			$arrSchedule = $objScheduler->load_book_schedule(array(
				"user_id"		=> $this->_kiosk_user_session_data->user_id,
				"mission_id"	=> $objMission->mission_id,
				"capture_end_date" => mktime(0, 0, 0, date("n", $intBatBarEpoch), date("j", $intBatBarEpoch), date("Y", $intBatBarEpoch)) - 7 * 86400
			));

			// get the line of the user on bar/bat
			$this->view->objNextEpochLocation = $objBooks->book_location_by_date(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"institution_id" => $this->_kiosk_user_session_data->institution_id,
				"mission_id" => $objMission->mission_id,
				// Bar/Bat Mitzvah date
				"capture_start_date" => $intNextBirthEpoch,
				"capture_end_date" => $intNextBirthEpoch + 86400 * 7,
			));

			$this->view->intNextBirthdayAge = $objUsers->user_age_at_epoch(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"epoch" => $intNextBirthEpoch
			));

			$this->view->intLatestLine = floor($objMarking->latest_line_hierarchy(array(
				"mission_id" => $objMission->mission_id,
				"user_id" => $this->_kiosk_user_session_data->user_id
			)));

			// calcualte the avarage
			$arrEnrollmentEpoch = (array) current($objCampaigns->select_user_campaign_created_epoch(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"campaign_id" => $intCampaign,
				"status" => "Enrollment"
			)));
			$intEnrollmentEpoch = $arrEnrollmentEpoch["schedule_date"];
			$intTenWeeksAgoEpoch = time() - (86400 * 7 * 10);

			$intMinEpoch = $intEnrollmentEpoch > $intTenWeeksAgoEpoch ? $intEnrollmentEpoch : $intTenWeeksAgoEpoch;
			//print "Date: " . date("F j, Y, g:i a", $intMinEpoch) . " <br>\n";
			$intNow = time() + (86400 * 7); // Now + current week
			$intTotalWeeks = floor(($intNow - $intMinEpoch) / 86400 / 7);
			$arrTaskCluster = $objCampaigns->_user_campaigns_select(array(
				"mission_id" => $objMission->mission_id,
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"created_min" => $intMinEpoch
			));
			$arrScheduleCluster = $objScheduler->load_book_schedule(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"institution_id" => $this->_kiosk_user_session_data->institution_id,
				"mission_id" => $objMission->mission_id,
				// Bar/Bat Mitzvah date
				"capture_start_date" => $intMinEpoch,
				"capture_end_date" => $intNow
			));
			array_shift($arrScheduleCluster);
			//$intTotalWeeks = count($arrScheduleCluster);
			if (count($arrTaskCluster))
			{
				$objFirstTask = reset($arrTaskCluster);
				$intFirstTaskIncrement = $objFirstTask->task_increment;
				$objLastTaskInrement = end($arrTaskCluster);
				$intLastTaskIncrement = $objLastTaskInrement->task_increment;
				$intTotalLines = $intFirstTaskIncrement - $intLastTaskIncrement;
				$this->view->intWeeklyAvarage = number_format($intTotalLines ? $intTotalLines / ($intTotalWeeks) : 0, 2)*1;
			}
		}
	}

	public function simulatorAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-S101-65RTEF";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objRules = new Rules();
		$objUsers = new Users();
		$objScheduler = new Scheduler();
		$objMissions = new Missions();
		$objBooks = new Books();
		$objLadders = new Ladders();
		$objAutomations = new Automation();

		$intStart = $objScheduler->_tools->microtime_float();

		$this->_helper->layout()->strTitle = "Simulator";
		$this->_helper->layout()->bgColor = "blue";

		// Select In Progress lines
		$this->view->arrInProgress = $objCampaigns->_user_campaigns_select(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"status" => "In Progress"
		));

		$objUser = current($objUsers->_users_select(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		)));

		$intLadderVelocity = $this->view->intLadderVelocity = $objLadders->campaign_user_ladder_velocity(array(
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"campaign_id" => $intCampaign,
			"institution_id" => $this->_kiosk_user_session_data->institution_id
		));

		$intAgeEpoch = $objUsers->user_age_in_epoch(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		));
		$intCurrentYearsOld = floor((time() - $intAgeEpoch) / 31557600); // Years Old

		// Collect all the lines/pages/chapters est. on each year of the users birth day
		// Loop forward 13 years, starting at current age

		$arrBirthdays = array();
		for ($intYear=$intCurrentYearsOld; $intYear!=130; $intYear++)
		{
			// Attempt to parse the rules to find
			$boolResult = $objRules->rule_is_allowed(array(
				//"rule_applies_to" => "Campaign Simulation",
				"rule_applies_to" => "Campaign Availability",
				"institution_id" => $this->_kiosk_user_session_data->institution_id,
				"campaign_id" => $intCampaign,

				"rule_params" => "age=" . $intYear . " && gender=" . ($objUser->gender == "F" ? "Female" : "Male")
			));
			if (!$boolResult)
				break;

			// Convert the users age to a timestamp of their birthday at that age
			$intTime = mktime(0, 0, 0, date("n", $intAgeEpoch), date("j", $intAgeEpoch), date("Y", $intAgeEpoch) + $intYear);
			$arrBirthdays[$intYear] = $intTime;
		}

		// Load the available ladders
		$arrLadders = $objScheduler->load_available_ladders2(array(
			"user_id" 			=> $this->_kiosk_user_session_data->user_id,
			"institution_id"	=> $this->_kiosk_user_session_data->institution_id,
			"campaign_id"		=> $intCampaign,
		));

		// Save the ladder
		if ($this->_request->isPost())
		{
			$intLadderHierarchy = -1;
			foreach ($arrLadders as $objLadder)
			{
				if ($objLadder->velocity == $this->_request->getPost("ladder"))
					$intLadderHierarchy = $objLadder->ladder;
			}
			if ($intLadderHierarchy < 0)
			{
				print text("Sorry, there was an error") . ": CKM-S101-8SSDSA";
				exit;
			}
			// Change the ladder
			$objCampaigns->_user_campaigns_update(array(
				"values" => array(
					"ladder" => $intLadderHierarchy,
					"ladder_velocity" => $this->_request->getPost("ladder")
				),
				"where" => array(
					"user_id" => $this->_kiosk_user_session_data->user_id,
					"status" => "Enrollment",
					"campaign_id" => $intCampaign
				)
			));
			// Alter pending task
			$objCampaigns->_user_campaigns_update(array(
				"values" => array(
					"ladder" => $intLadderHierarchy,
					"ladder_velocity" => $this->_request->getPost("ladder")
				),
				"where" => array(
					"user_id" => $this->_kiosk_user_session_data->user_id,
					"status" => "In Progress",
					"campaign_id" => $intCampaign
				)
			));
			$arrResult = $objAutomations->user_goal(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"campaign_id" => $intCampaign
			));

			$this->_redirect("./kiosk-main/view-campaign/campaign_id/" . $intCampaign . "/lastaction/ladderchanged/oldladder/" . $intLadderVelocity);
			exit;
		}

		// Load the book mission
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));

		// Loop through each ladder, generate the schedule and collect the line
		// numbers on the corresponding milestones
		$arrResults = array();
		$arrWeeklyQuotas = array();
		$intLastBirthdate = end($arrBirthdays);
		foreach ($arrLadders as $intKey => $objLadder)
		{
			$arrSchedule = $objScheduler->load_book_schedule(array(
				"user_id"		=> $this->_kiosk_user_session_data->user_id,
				"mission_id"	=> $objMission->mission_id,
				"ladder" => $objLadder->ladder,
				"capture_end_date" => mktime(0, 0, 0, date("n", $intLastBirthdate), date("j", $intLastBirthdate), date("Y", $intLastBirthdate)) - 7 * 86400
			));

			$arrBirthdayTasks = array();
			$intLastTime = time();
			$arrYearStartCounts = array();
			foreach ($arrSchedule as $arrMission)
			{
				if (!isset($arrYearStartCounts[date("Y", $arrMission["epoch"])]))
					$arrYearStartCounts[date("Y", $arrMission["epoch"])] = current($arrMission["tasks"]);

				// Loop through the birthday milestones to find tasks within range
				foreach ($arrBirthdays as $intYearOld => $intTaskTime)
				{
					if (
						(
							$intLastTime <= $intTaskTime
							&& $arrMission["epoch"] >= $intTaskTime
						) || end($arrBirthdays) == $arrBirthdays[$intYearOld]
					) {
						$arrBirthdayTasks[$intYearOld] = current($arrMission["tasks"]);
					}
				}
				ksort($arrBirthdayTasks);

				$intLastTime = $arrMission["epoch"];
			}
			//array_shift($arrBirthdayTasks); // shift the current year off the results
			$arrResults["ladder " . $objLadder->velocity] = $arrBirthdayTasks;
			// Calculate the yearly quotas
			$arrYearlyQuotas["ladder " . $objLadder->velocity] = end($arrYearStartCounts) - prev($arrYearStartCounts);
		}
		// Loop through the results and associate the corresponding line
		$arrResults2 = array();
		$objBookLast = end($objBooks->_book_lines_select(array(
			"book_id" => $objMission->book_id,
			"LIMIT" => 1,
			"ORDER" => "line_hierarchy+0 DESC"
		)));

		if ($this->_verbose)
		{
			var_dump($arrLadders);
			exit;
		}
		foreach ($arrResults as $strLadder => $arrLines)
		{
			foreach ($arrLines as $intAge => $intLine)
			{
				$objBook = current($objBooks->_book_lines_select(array(
					"book_id" => $objMission->book_id,
					"line_hierarchy" => ceil($intLine)
				)));
				if ($objBook)
				{
					unset($objBook->line_data);
					$arrResults2[$strLadder][$intAge] = $objBook;
				}
				else
				{
					unset($objBook->line_data);
					$arrResults2[$strLadder][$intAge] = $objBookLast;
				}
			}
		}
		$this->view->arrLadderLines = $arrResults2;
		$this->view->arrYearlyQuotas = $arrYearlyQuotas;

		$this->view->intBMTime = $objScheduler->_tools->microtime_float() - $intStart;
		//var_dump($this->view->intBMTime);exit;
	}

	public function tanyaDedicationAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$this->_helper->layout()->strTitle = "Dedication";
		$this->_helper->layout()->bgColor = "blue";
	}

	public function campaignEnrollAction()
	{
		$query = new QueryGen();
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$this->_helper->layout()->strTitle = "Enrollment";
		$this->_helper->layout()->bgColor = "blue";
		$intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-CE101-SD8F7D";
			exit;
		}
		$objGrades = new Grades();
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objScheduler = new Scheduler();
		$objBooks = new Books();
		$objLadders = new Ladders();
		$objUsers = new Users();
		$objMarking = new Marking();
		$objAutomations = new Automation();
		$objLegacy = new Legacy();

		$this->view->objUsers = current($objUsers->_users_select(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		)));

		$this->view->arrLadders = $arrLadders = $objScheduler->load_available_ladders2(array(
			"user_id" 			=> $this->_kiosk_user_session_data->user_id,
			"institution_id"	=> $this->_kiosk_user_session_data->institution_id,
			"campaign_id"		=> $intCampaign
		));

		$arrVelocityHash = array();
		foreach ($arrLadders as $objLadder)
		{
			$arrVelocityHash["ladder " . $objLadder->velocity] = $objLadder;
		}

		$this->view->objCampaign = $objCampaign = current($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));

		$arrMissions = $objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		));
		$objMission = current($arrMissions);

		// AJAX for enrollment
		if ($this->_request->getParam("enroll") == "true")
		{
			$intLadder = preg_replace("/[^ .0-9]/", "", $this->_request->getPost("ladder"));
			$intTaskOffset = intval($this->_request->getPost("task_offset"));
			if (!preg_match("/^[.0-9]+$/", $intLadder))
			{
				print text("Sorry, there was an error") . ": MKM-CE102-7896SD";
				exit;
			}
			$arrUserCampaigns = $objCampaigns->_user_campaigns_select(array(
				"user_id" 			=> $this->_kiosk_user_session_data->user_id,
				"campaign_id"		=> $intCampaign,
				"status"			=> "Enrollment"
			));
			if (count($arrUserCampaigns))
			{
				print "You are already enrolled to this campaign.";
				exit;
			}
			if (!isset($arrVelocityHash["ladder " . $intLadder]))
			{
				print text("Sorry, there was an error") . ": CKM-CE103-8SDFDD";
				exit;
			}
			// Find out if this is a legacy user
			$objLegacyUser = first($query->legacy_lookup__select(array(
				"ims_id" => $this->_kiosk_user_session_data->user_id,
				'legacy_table' => 'users'
			)));

			$intLadderVelocity = $arrVelocityHash["ladder " . $intLadder]->ladder;
			$intScheduleDate = time();
			if (0 && $objLegacyUser)
			{
				$intBackdateMissionsMax = 60;
				$intBackdateAmount = $intTaskOffset;
				$intMarkTo = $intTaskOffset;
				$intTaskOffset = $intNewTaskOffset = 0;
				$intEstimatedMissions = $intBackdateAmount * $intLadder;
				if (floor($intEstimatedMissions) > $intBackdateMissionsMax)
				{
					while (floor($intBackdateAmount * $intLadder) > $intBackdateMissionsMax)
					{
						$intBackdateAmount--;
						$intNewTaskOffset++;
					}
				}
				$intTaskOffset = $intNewTaskOffset;
				$intEstimatedMissions = $intBackdateAmount * $intLadder;
				$intScheduleDate -= $intEstimatedMissions * (7.024038461538462 * 86400);
			}

			// Enroll the user
			$objUserCampaign = first($objCampaigns->_user_campaigns_select(array(
				"user_id" 			=> $this->_kiosk_user_session_data->user_id,
				"campaign_id"		=> $intCampaign,
				"status"			=> "Enrollment"
			)));
			if ($objUserCampaign)
			{
				$query->user_campaigns__update(array(
					'where' => array(
						'user_campaign_id' => $objUserCampaign->user_campaign_id
					),
					'values' => array(
						"status"			=> "Enrollment",
						"ladder"			=> $intLadderVelocity,
						"line_offset" 		=> $intTaskOffset
					)
				));
				$objLatestItem = first($query->user_campaigns__select(array(
					"institution_id" => $this->_kiosk_user_session_data->institution_id,
					"campaign_id" => 1,
					"user_id" => $this->_kiosk_user_session_data->user_id,
					'_ORDER' => 'schedule_date+0 DESC',
					'_LIMIT' => 1
				)));
				if ($objLatestItem->status == 'Paused')
				{
					$query->user_campaigns__insert(array(
						'campaign_id' => 1,
						'mission_id' => 1,
						'user_id' => $this->_kiosk_user_session_data->user_id,
						'institution_id' => $this->_kiosk_user_session_data->institution_id,
						'status' => 'Resumed',
						'schedule_date' => time(),
						'input_value' => '_enrollment'
					));
				}
			}
			else
			{
				$intAI = $objCampaigns->_user_campaigns_insert(array(
					"user_id" 			=> $this->_kiosk_user_session_data->user_id,
					"institution_id"	=> $this->_kiosk_user_session_data->institution_id,
					"campaign_id"		=> $intCampaign,
					"mission_id"		=> $objMission->mission_id,
					"status"			=> "Enrollment",
					"ladder"			=> $intLadderVelocity,
					"line_offset" 		=> $intTaskOffset,
					"schedule_date"		=> $intScheduleDate,
					"created"			=> $intScheduleDate
				));
			}

			// Mark the tasks complete for legacy users
			if ($objLegacyUser && @$intBackdateAmount > 0)
			{
				$objMarking->mark_task_incrament(array(
					"user_id" => $this->_kiosk_user_session_data->user_id,
					"campaign_id" => $intCampaign,
					"task_incrament" => $intMarkTo
				));
			}

			$arrResult = $objAutomations->user_goal(array(
				"user_id" => $this->_kiosk_user_session_data->user_id,
				"campaign_id" => $intCampaign
			));

			$objLegacy->update_legacy_user_tracks(array(
				"user_id" => $this->_kiosk_user_session_data->user_id
			));

			print 1;
			exit; // ajax
		}

		// Book campaigns can only have 1 mission (for now at least...)
		if (count($arrMissions) == 1)
		{
			$objMission = current($arrMissions);
			// Book campaigns have have a NOT NULL book_id from the single mission under the campaign
			if ($objMission->book_id)
			{
				$this->view->objMissionsSch = current($objScheduler->_scheduling_params_select(array(
					"mission_id" => $objMission->mission_id,
					"task_id" => 0
				)));

				// AJAX post to calculate ladder
				if ($this->_request->isPost())
				{
					$arrPost = $this->_request->getPost();
					$intBatBarEpoch = $objUsers->user_batbar_in_epoch(array(
						"user_id" => $this->_kiosk_user_session_data->user_id
					));

					$intLadderVelocity = $objLadders->ladder_from_velocity(array(
						"user_id" => $this->_kiosk_user_session_data->user_id,
						"institution_id" => $this->_kiosk_user_session_data->institution_id,
						"campaign_id" => $objMission->campaign_id,
						"ladder_velocity" => $this->_request->getPost("ladder")
					));

					$objLocation = $objBooks->book_location_by_date(array(
						"user_id" => $this->_kiosk_user_session_data->user_id,
						"institution_id" => $this->_kiosk_user_session_data->institution_id,
						"mission_id" => $objMission->mission_id,
						// Bar/Bat Mitzvah date
						"capture_start_date" => $intBatBarEpoch,
						"capture_end_date" => $intBatBarEpoch + 7 * 86400,
						// Line offset is the number of "lines" to start from
						"task_offset" => intval($this->_request->getPost("task_offset")),

						"ladder" => $intLadderVelocity
					));
					print json_encode($objLocation);
					exit;
				}


				/*
				redundent?
				$objBooks->range_from_date(array(
					"campaign_id" => $intCampaign,
					"mission_id" => $objMission->mission_id,
					"book_id" => $objMission->book_id,
					"intDate" => 123,
					"user_id" => $this->_kiosk_user_session_data->user_id
				));
				$objBooks->range_from_book_params(array(
					"book_id" => $objMission->book_id,
					"book_measurement" => $objMission->book_measurement
				));
				*/
			}
		}
	}

	public function overviewAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$this->_helper->layout()->strTitle = "Overview";
		$this->_helper->layout()->bgColor = "blue";
		$intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CKM-Ove101-SD7F6D";
			exit;
		}
		$objCampaigns = new Campaigns();
		$objScheduler = new Scheduler();

		$this->view->objCampaign = $objCampaign = current($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));

		$this->view->arrFullSchedule = $objScheduler->load_book_medals(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"kiosk" => true
		));

		$arrSchedules = array();
		$arrSchedules[1] = current($objScheduler->load_book_medals(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"load_missions" => true, // load the missions with the tasks into the medals
			"capture_start_medal" => 0,
			"capture_end_medal" => 0,
			"kiosk" => true
		)));
		$arrSchedules[2] = current($objScheduler->load_book_medals(array(
			"campaign_id" => $intCampaign,
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"load_missions" => true, // load the missions with the tasks into the medals
			"capture_start_medal" => 1,
			"capture_end_medal" => 1,
			"kiosk" => true
		)));
		$this->view->arrSchedules = $arrSchedules;
	}

	public function doCampaignEnrollAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$objCampaign = new Campaigns();
		$this->_helper->layout()->bgColor = "blue";

		$campaign_id = $this->_request->campaign_id;
		$ladder = $this->_request->ladder;
		$this->view->objCampaign = $objCampaign->campaign_select_id($campaign_id);
		$date = date("Y-m-d H:i:s");

		$arrInsert = array("user_id"		=> $this->_kiosk_user_session_data->user_id,
						   "institution_id"	=> $this->_kiosk_user_session_data->institution_id,
						   "campaign_id"	=> $campaign_id,
						   "status"			=> "In Progress",
						   "ladder"			=> $ladder,
						   "created"		=> $date
						   );


		$result = $objCampaign->campaign_usercampaign_add($arrInsert);
	}

	public function doCampaignUnenrollAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$objCampaign = new Campaigns();
		$this->_helper->layout()->bgColor = "blue";

		$campaign_id = $this->_request->campaign_id;
		$this->view->objCampaign = $objCampaign->campaign_select_id($campaign_id);

		$result = $objCampaign->campaign_usercampaign_delete($this->_kiosk_user_session_data->user_id, $campaign_id);
	}

	public function showImageAction()
	{
		$layout = Zend_Layout::startMvc();
		$layout->setLayoutPath('application/layouts/scripts');
		$this->_helper->layout->setLayout('mainlayout');
		$store = new Store();
		//echo "asdasdasd"; exit;
		$image = $store->show_picture($this->_request->getParam('image_id'));
		header('Content-type: ' . $image->photo_type);
		 if (base64_decode($image->photo))
			echo base64_decode($image->photo);
		   else
			echo $image->photo;
		exit;
	}

	public function storeAction()
	{
		$classes = new Classes();
		$image = new Image();
		$kiosk = new Kiosk();
		$objPoints = new Points();

		$this->view->intPoints = $objPoints->user_points_sum(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		));
		$user = $this->view->user = new Zend_Session_Namespace('kiosk_user_session_data');
		$this->view->image = $image;


		$host_id = $this->_kiosk_user_session_data->host_id;
		$network_id = $this->_kiosk_user_session_data->network_id;
		$institution_id = $this->_kiosk_user_session_data->institution_id;

		// based on user_id from the session get classes

		$arrClasses = $classes->get_class_id_by_user_id($this->_kiosk_user_session_data->user_id);


		// this contains distinct prizes to be displayed filtered by inst.id
		$arrDistinctPrizes = $kiosk->getDistinctPrizes_a($host_id, $network_id, $institution_id, $user->classes);
		//$arrDistinctPrizes = $kiosk->getDistinctPrizes($institution_id, $arrClasses);
		$this->view->distinctPrizes = $arrDistinctPrizes;

		$arrAllPrizesByPoints = array();
		// get all prizes for a given price range filtered by inst.id
		foreach($arrDistinctPrizes as $r){
			$arrBuffer = $kiosk->getPrizesByPoints_a($r->points, $host_id, $network_id, $institution_id, $user->classes);
			//$arrBuffer = $kiosk->getPrizesByPoints($r->points, $institution_id, $arrClasses);
			$arrAllPrizesByPoints[$r->points] = $arrBuffer;
		}
		$this->view->allPrizesByPoints = $arrAllPrizesByPoints;

		//get all available prizes regardless of price range filtered by inst.id
		//$this->view->allPrices = $kiosk->getAllPrizes($user->institution_id, $user->classes);
		$this->view->allPrices = $kiosk->getAllPrizes($institution_id, $user->classes);

		// get all the prizes in the cart
		$this->view->arrCartItems = array();
		$cart = new Zend_Session_Namespace('cart');
		if (isset($cart->cartItems))
		{
			foreach ($cart->cartItems as $cartItem) {
				array_push($this->view->arrCartItems, $cartItem);
			}
		}
	}

	public function disablePrintingAction()
	{
		if (!isset($this->_kiosk_user_session_data->disablePrinting) || !$this->_kiosk_user_session_data->disablePrinting)
			$this->_kiosk_user_session_data->disablePrinting = 1;
		else
			$this->_kiosk_user_session_data->disablePrinting = 0;
		if ($this->_kiosk_user_session_data->disablePrinting)
		{
			print "Kiosk printing has been disabled.";
		}
		else
		{
			print "Kiosk printing has been enabled.";
		}
	}
}
?>
