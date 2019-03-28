<?php
class MobileController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance

	function preDispatch()
	{
		$query = new QueryGen();
		$this->_sesh = new Zend_Session_Namespace('hebrewschools');
		$arrParams = $this->_request->getParams();
		$utilities = new Utilities();
		if (in_array($this->_request->getParam('action'), array(
			'profiledata',
			'profiledata2',
			'scancard',
			'scancard2',
			'privacy'
		))) {

		}
		else if ($this->_request->getParam('action') != 'index')
		{
			/*
			$this->objPermission = $utilities->dispatch_helper_hebrewschools($arrParams);
			$this->view->arrStudentPermissions = $query->permissions__select(array(
				'user_id' => $this->objPermission->user_id,
				'permission' => 'Student',
				'_GREATER' => array(
					'registration_expiration' => time()
				)
			));
			$this->view->objUser = $query->users__select(array(
				'user_id' => $this->objPermission->user_id
			));
			*/
		}
	}

	function privacyAction()
	{

	}

	function profileAction() {
		$query = new QueryGen();
		$objConfig = new Config();
		$objPoints = new Points();

		$this->view->institution_id = $this->_request->getParam("institution_id");
		$this->view->tstyle = $this->_request->getParam("tstyle");

		global $arrInstituionDetails;
		$this->view->arrInstituionDetails = @$arrInstituionDetails[$this->_sesh->institution_id];
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

	public function profiledataAction() {
		$query = new QueryGen();
		$objPoints = new Points();
		$intBarCode = $this->_request->getParam('bar_code');
		$intTimeSent = $this->_request->getParam('timesent');
		$intSleep = $this->_request->getParam('sleep');
		$boolMissionsApp = $this->_request->getParam('missionsapp') == "true";
		if (!empty($intSleep)) {
			sleep($intSleep);
		}
		$objUser = first($query->users__select(array(
			'bar_code' => $intBarCode,
			'_ORDER' => 'modified DESC',
			'_LIMIT' => 1
		)));
		if (!$objUser)
		{
			// Check if its an achievement card
			$objAchievementCard = first($query->achievement_cards__select(array(
				"card_serial" => $intBarCode,
				'_LIMIT' => 1
			)));
			$strMessage = 'Bar code not found';
			if ($objAchievementCard)
				$strMessage = "This appears to be an achievement card. Please scan your ID card to login and then scan your achievement card.";
			print json_encode(array(
				'failure' => $strMessage,
				'timesent' => $intTimeSent
			));
			exit;
		}
		$objPermission = first($query->permissions__select(array(
			'user_id' => $objUser->user_id,
			'_ORDER' => 'modified DESC',
			'_LIMIT' => 1
		)));

		$objInstitution = first($query->institutions__select(array(
			'institution_id' => $objPermission->institution_id
		)));
		$arrOutput = array(
			'success' => 'true',
			'strBarcode' => (string) $intBarCode, 
			'first_name' => $objUser->first_name,
			'last_name' => $objUser->last_name,
			'image_id' => $objUser->image_id,
			
			'parents_email' => $objUser->student_email,
			'dob' => $objUser->dob,
			'gender' => $objUser->gender,
			'address' => $objUser->address,
			'city' => $objUser->city,
			'state' => $objUser->state,
			'phone' => $objUser->phone,
			
			
			'institution_id' => $objPermission->institution_id,
			'permission_id' => $objPermission->permission_id,
			'strInstitutionName' => $objInstitution->name,
			'timesent' => $intTimeSent
		);
		if ($boolMissionsApp) {
			// Missions App System
			/*$arrMissions = $query->ckids_mission_app__select(array(
				"_ALL" => TRUE,
				'_ORDER' => 'start_date+0 ASC'
			));*/
			$arrMarking = array_hash('task_id', $query->ckids_mission_marking__select(array(
				'user_id' => $objUser->user_id
			)));
			$arrMissionCounts = array(5,15,30,50,75,105,140,180,225);
			$intMarkedMissions = count($arrMarking);
			$arrMedalNames = array("White", "Red", "Orange", "Yellow", "Green", "Blue", "Purple", "Brown", "Gray", "Black");
			$strCurrentMedal = $arrMedalNames[0];
			foreach ($arrMissionCounts as $intItr => $intMedalThreshold) {
				if ($intMedalThreshold <= $intMarkedMissions) {
					$strCurrentMedal = $arrMedalNames[$intItr+1];
				} else {
					break;
				}
			}
			$intNextMedal = $arrMissionCounts[$intItr] - $intMarkedMissions;
			if ($intNextMedal < 0) {
				$intNextMedal = "Finished";
			}
			$arrOutput["strCurrentMedal"] = $strCurrentMedal;
			$arrOutput["strNextMedal"] = (string) $intNextMedal;
		} else {
			// Hebrew School System
			$intUserPointsTotal = $objPoints->user_total(array(
				"user_id" => $objUser->user_id,
				"institution_id" => $objPermission->institution_id
			));
			$intUserPointsStore = $objPoints->user_store(array(
				"user_id" => $objUser->user_id,
				"institution_id" => $objPermission->institution_id
			));
			$arrOutput['strStoreBalance'] = "Store: " . number_format(intval($intUserPointsStore),0) . " point" . ($intUserPointsStore == 1 ? '' : 's');
			$arrOutput['strBalanace'] = "Total: " . number_format(intval($intUserPointsTotal),0) . " point" . ($intUserPointsTotal == 1 ? '' : 's');
		}
		print json_encode($arrOutput);
		exit;
	}

	public function profiledata2Action() {
		$query = new QueryGen();
		$objPoints = new Points();
		$objUsers = new Users();
		$objInstitutions = new Institutions();
		$intBarCode = $this->_request->getParam('bar_code');
		$intTimeSent = $this->_request->getParam('timesent');
		$intSleep = $this->_request->getParam('sleep');
		//$boolMissionsApp = $this->_request->getParam('missionsapp') == "true";
		if (!empty($intSleep)) {
			sleep($intSleep);
		}
		$objUser = first($objUsers->getMashpiaUser($intBarCode));
		if (!$objUser)
		{
			// Check if its an achievement card
			$objAchievementCard = first($query->achievement_cards__select(array(
				"card_serial" => $intBarCode,
				'_LIMIT' => 1
			)));
			$strMessage = 'Bar code not found';
			if ($objAchievementCard)
				$strMessage = "This appears to be an achievement card. Please scan your ID card to login and then scan your achievement card.";
			print json_encode(array(
				'failure' => $strMessage,
				'timesent' => $intTimeSent
			));
			exit;
		}
		/*
		$objPermission = first($query->permissions__select(array(
			'user_id' => $objUser->user_id,
			'_ORDER' => 'modified DESC',
			'_LIMIT' => 1
		)));
		*/
		$objInstitution = first($objInstitutions->_institutions_select(array(
			'institution_id' => $objUser->school_id
		)));
		$arrOutput = array(
			'success' => 'true',
			'strBarcode' => (string) $intBarCode, 
			'first_name' => $objUser->first,
			'last_name' => $objUser->last,
			'image_id' => $objUser->mobile_pic,
			
			'parents_email' => $objUser->email,
			'dob' => $objUser->dob,
			'gender' => $objUser->gender,
			'address' => $objUser->user_address1,
			'city' => $objUser->user_city,
			'state' => $objUser->user_state,
			
			'institution_id' => $objUser->school_id,
			'strInstitutionName' => $objInstitution->school_name,
			'timesent' => $intTimeSent
		);
		/*
		if ($boolMissionsApp) {
			/*$arrMissions = $query->ckids_mission_app__select(array(
				"_ALL" => TRUE,
				'_ORDER' => 'start_date+0 ASC'
			));*/
			/*
			$arrMarking = array_hash('task_id', $query->ckids_mission_marking__select(array(
				'user_id' => $objUser->user_id
			)));
			$arrMissionCounts = array(5,15,30,50,75,105,140,180,225);
			$intMarkedMissions = count($arrMarking);
			$arrMedalNames = array("White", "Red", "Orange", "Yellow", "Green", "Blue", "Purple", "Brown", "Gray", "Black");
			$strCurrentMedal = $arrMedalNames[0];
			foreach ($arrMissionCounts as $intItr => $intMedalThreshold) {
				if ($intMedalThreshold <= $intMarkedMissions) {
					$strCurrentMedal = $arrMedalNames[$intItr+1];
				} else {
					break;
				}
			}
			$intNextMedal = $arrMissionCounts[$intItr] - $intMarkedMissions;
			if ($intNextMedal < 0) {
				$intNextMedal = "Finished";
			}
			$arrOutput["strLineOne"] = "Next Medal: " . $strCurrentMedal;
			$arrOutput["strLineTwo"] = "Medal: " . $intNextMedal;
		} else {
			*/
			$intUserPointsTotal = $objPoints->user_total(array(
				"user_id" => $objUser->user_id,
				"institution_id" => $objUser->school_id
			));
			$intUserPointsStore = $objPoints->user_store(array(
				"user_id" => $objUser->user_id,
				"institution_id" => $objUser->school_id
			));			
			$arrOutput['strLineOne'] = "Store: " . number_format(intval($intUserPointsStore),0) . " point" . ($intUserPointsStore == 1 ? '' : 's');
			$arrOutput['strLineTwo'] = "Total: " . number_format(intval($intUserPointsTotal),0) . " point" . ($intUserPointsTotal == 1 ? '' : 's');
		//}
		print json_encode($arrOutput);
		exit;
	}

	public function scancard2Action() {
		$query = new QueryGen();
		$objConfig = new Config();
		$objPoints = new Points();
		$objUsers = new Users();
		$mixBarCode = $this->_request->getParam("mixBarCode");
		$intTimeSent = $this->_request->getParam('timesent');
		$intInstitution = $this->_request->getParam("institution_id");
		$mixUserBarCode = $this->_request->getParam("userbarcode");
		$intSleep = $this->_request->getParam('sleep');
		if (!empty($intSleep)) {
			sleep($intSleep);
		}
		/*
		$objUser = first($query->users__select(array(
			'bar_code' => $mixUserBarCode,
			'_ORDER' => 'modified DESC',
			'_LIMIT' => 1
		)));
		*/
		$objUser = first($objUsers->getMashpiaUser($mixUserBarCode));
		if (!$objUser)
		{
			print json_encode(array(
				'failure' => 'User not found',
				'timesent' => $intTimeSent
			));
			exit;
		}
		$intUser = $objUser->user_id;
		$arrOutput = array();
		$arrOutput['timesent'] = $intTimeSent;
		$objCard = first($query->achievement_cards__select(array(
			"card_serial" => (string) $mixBarCode
		)));
		if (!$objCard)
		{
			print json_encode(array(
				"error" => "This doesn't appear to be an achievement card.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		//if ($objCard->institution_id != 1 && $objCard->institution_id != $intInstitution)
		if ($objCard->institution_id != $intInstitution)
		{
			/*
			// Check if user has access to this institution
			$objSubPermission = first($query->permissions__select(array(
				'institution_id' => $objCard->institution_id,
				'user_id' => $objUser->user_id
			)));
			// Switch user account
			if ($objSubPermission)
			{
				$intInstitution = $objCard->institution_id;
				$objInstitution = first($query->institutions__select(array(
					'institution_id' => $objCard->institution_id
				)));
				// provide all info for app to switch
				$arrOutput['switch'] = 'true';
				$arrOutput['institution_id'] = $objSubPermission->institution_id;
				$arrOutput['permission_id'] = $objSubPermission->permission_id;
				$arrOutput['strInstitutionName'] = $objInstitution->name;
			}
			else
			{
			*/
				// switch profiles
				print json_encode(array(
					"error" => "This card was created for an institution you don't belong to.",
					'timesent' => $intTimeSent
				));
				exit;
			//}
		}
		/*
		if ($objCard->institution_id == 601) {
			$objCampaign = (object) array(
				"_FAKE" => TRUE
			);
		} else if (isset($objCard->task_id))
		{
			$objTask = $this->view->objTask = first($query->tasks__select(array(
				"task_id" => $objCard->task_id
			)));
			if ($objTask && !empty($objTask->campaign_id))
				$objCampaign = $this->view->objCampaign = first($query->campaigns__select(array(
					"campaign_id" => $objTask->campaign_id
				)));
		}
		
		// Find students current class
		$objClass = first($query->user_classes__select(array(
			"user_id" => $intUser
		)));
		if (
			$objCard->institution_id != 601
			&& $objCard->task_id
			&& (
				!$objTask
				|| !$objCampaign
			)
		) {
			print json_encode(array(
				"error" => "Your scan card is no longer available.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if ($objCard->class_id > 0 && !$objClass && $objCard->class_id != 0)		
		{
			print json_encode(array(
				"error" => "You must be in a class to scan this card.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if ($objCard->class_id > 0 && $objClass->class_id != $objCard->class_id)
		*/
		if ($objCard->class_id > 0 && $objCard->class_id != $objUser->class_id)
		{
			print json_encode(array(
				"error" => "You are not in the correct class to scan this card.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if($objCard->status == "scanned")
		{
			print json_encode(array(
				"error" => "This card was already scanned.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if($objCard->status != "scanned")
		{
			/*
			if ($objCard->card_type == "MissionsApp")
			{
				$objMission = first($query->ckids_mission_app__select(array(
					"task_id" => $objCard->task_id
				)));
				if (!$objMission)
				{
					json(array(
						"failure" => "Unable to find associated mission"
					));
				}
				$objMissionMarked = first($query->ckids_mission_marking__select(array(
					"task_id" => $objCard->task_id,
					"user_id" => $intUser,
					"_ORDER" => "created DESC",
					"_LIMIT" => 1
				)));
				if (
					$objMissionMarked
					&& (
						$objMission->access_level == 1
						|| (
							$objMission->access_level == 2
							&& (
								!preg_match("/^(2[0-9]{3})\-([0-9]{2})\-([0-9]{2})/", $objMissionMarked->created, $arrMatched)
								|| mktime(0,0,0,$arrMatched[2],$arrMatched[3]+1,$arrMatched[1]) > time()	
							)
						)
					)
				) {
					if ($objMission->access_level == 1)
					{
						print json_encode(array(
							"error" => "This card was already scanned.",
							'timesent' => $intTimeSent
						));
					} else { // 2
						print json_encode(array(
							"error" => "This card was already scanned today.",
							'timesent' => $intTimeSent
						));
					}
					
					exit;
				}
				
				$query->ckids_mission_marking__insert(array(
					"task_id" => $objCard->task_id,
					"user_id" => $intUser,
					"network_id" => $objMission->network_id
				));
				
				preg_match('/^([^\/]+)\/ (.+)/', $objCard->achievement, $arrMatch);
				list ($strMatch, $strName, $strDescription) = $arrMatch;
				$strName = trim($strName);
				$arrOutput['scan_response'] = TRUE;
				$arrOutput['name'] = $strName;
				$arrOutput['description'] = $strDescription;
				
				
				$arrMarking = array_hash('task_id', $query->ckids_mission_marking__select(array(
					'user_id' => $intUser
				)));
				$arrMissionCounts = array(5,15,30,50,75,105,140,180,225);
				$intMarkedMissions = count($arrMarking);
				$arrMedalNames = array("White", "Red", "Orange", "Yellow", "Green", "Blue", "Purple", "Brown", "Gray", "Black");
				$strCurrentMedal = $arrMedalNames[0];
				foreach ($arrMissionCounts as $intItr => $intMedalThreshold) {
					if ($intMedalThreshold <= $intMarkedMissions) {
						$strCurrentMedal = $arrMedalNames[$intItr+1];
					} else {
						break;
					}
				}
				$intNextMedal = $arrMissionCounts[$intItr] - $intMarkedMissions;
				if ($intNextMedal < 0) {
					$intNextMedal = "Finished";
				}
				
				$arrOutput["strLineOne"] = "Next Medal: " . $strCurrentMedal;
				$arrOutput["strLineTwo"] = "Medal: " . $intNextMedal;
				$arrOutput['success'] = 'true';
				
				print json_encode($arrOutput);
				exit;
			} 
			else
			{
				
				if ($objCard->campaign_id)
				{
					//deposit points to child's account and mark the achievement card as scanned
					$query->user_campaigns__insert(array(
						"user_id"			=> $intUser,
						"institution_id"	=> $intInstitution,
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
					"user_id"				=> $intUser,
					"institution_id"		=> $intInstitution,
					"campaign_id"			=> $objCard->campaign_id,
					"mission_id"			=> $objCard->mission_id,
					"task_id"				=> $objCard->task_id,
					"class_id"				=> $objCard->class_id,
					"points"				=> $objCard->card_points,
					"resource_name"			=> "MissionsApp",
					"prize_id"				=> 0
				));
				*/
				$query->achievement_cards__update(array(
					"where" => array(
						"card_serial" => $mixBarCode
					),
					"values" => array(
						"status" => "scanned"
					)
				));
				$intUserPointsTotal = $objPoints->user_total(array(
					"user_id" => $intUser,
					"institution_id" => $intInstitution
				));
				$intUserPointsStore = $objPoints->user_store(array(
					"user_id" => $intUser,
					"institution_id" => $intInstitution
				));
				$arrOutput['strLineOne'] = "Store: " . number_format($intUserPointsStore, 0) . " point" . ($intUserPointsStore == 1 ? '' : 's');
				$arrOutput['strLineTwo'] = "Total: " . number_format($intUserPointsTotal, 0) . " point" . ($intUserPointsTotal == 1 ? '' : 's');
				
				$arrOutput['strAlertTitle'] = "Scan successful";
				if ($objCard->card_points == 1)
					$arrOutput['strAlertMessage'] = $objCard->card_points . " point was added to your balance";
				else
					$arrOutput['strAlertMessage'] = $objCard->card_points . " points were added to your balance";
				
				$arrOutput['success'] = 'true';
				$arrOutput['points_added'] = $objCard->card_points;
				print json_encode($arrOutput);
				exit;
			//}
		}
	}


	public function scancardAction() {
		$query = new QueryGen();
		$objConfig = new Config();
		$objPoints = new Points();
		$mixBarCode = $this->_request->getParam("mixBarCode");
		$intTimeSent = $this->_request->getParam('timesent');
		$intInstitution = $this->_request->getParam("institution_id");
		$mixUserBarCode = $this->_request->getParam("userbarcode");
		$intSleep = $this->_request->getParam('sleep');
		if (!empty($intSleep)) {
			sleep($intSleep);
		}
		$objUser = first($query->users__select(array(
			'bar_code' => $mixUserBarCode,
			'_ORDER' => 'modified DESC',
			'_LIMIT' => 1
		)));
		if (!$objUser)
		{
			print json_encode(array(
				'failure' => 'User not found',
				'timesent' => $intTimeSent
			));
			exit;
		}
		$intUser = $objUser->user_id;
		$arrOutput = array();
		$arrOutput['timesent'] = $intTimeSent;
		$objCard = first($query->achievement_cards__select(array(
			"card_serial" => (string) $mixBarCode
		)));
		if (!$objCard)
		{
			print json_encode(array(
				"error" => "This doesn't appear to be an achievement card.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		if ($objCard->institution_id != 1 && $objCard->institution_id != $intInstitution)
		{
			// Check if user has access to this institution
			$objSubPermission = first($query->permissions__select(array(
				'institution_id' => $objCard->institution_id,
				'user_id' => $objUser->user_id
			)));
			// Switch user account
			if ($objSubPermission)
			{
				$intInstitution = $objCard->institution_id;
				$objInstitution = first($query->institutions__select(array(
					'institution_id' => $objCard->institution_id
				)));
				// provide all info for app to switch
				$arrOutput['switch'] = 'true';
				$arrOutput['institution_id'] = $objSubPermission->institution_id;
				$arrOutput['permission_id'] = $objSubPermission->permission_id;
				$arrOutput['strInstitutionName'] = $objInstitution->name;
			}
			else
			{
				// switch profiles
				print json_encode(array(
					"error" => "This card was created for an institution you don't belong to.",
					'timesent' => $intTimeSent
				));
				exit;
			}
		}
		if ($objCard->institution_id == 601) {
			$objCampaign = (object) array(
				"_FAKE" => TRUE
			);
		} else if (isset($objCard->task_id))
		{
			$objTask = $this->view->objTask = first($query->tasks__select(array(
				"task_id" => $objCard->task_id
			)));
			if ($objTask && !empty($objTask->campaign_id))
				$objCampaign = $this->view->objCampaign = first($query->campaigns__select(array(
					"campaign_id" => $objTask->campaign_id
				)));
		}
		
		// Find students current class
		$objClass = first($query->user_classes__select(array(
			"user_id" => $intUser
		)));
		if (
			$objCard->institution_id != 601
			&& $objCard->task_id
			&& (
				!$objTask
				|| !$objCampaign
			)
		) {
			print json_encode(array(
				"error" => "Your scan card is no longer available.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if ($objCard->class_id > 0 && !$objClass && $objCard->class_id != 0)
		{
			print json_encode(array(
				"error" => "You must be in a class to scan this card.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if ($objCard->class_id > 0 && $objClass->class_id != $objCard->class_id)
		{
			print json_encode(array(
				"error" => "You are not in the correct class to scan this card.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if($objCard->status == "scanned")
		{
			print json_encode(array(
				"error" => "This card was already scanned.",
				'timesent' => $intTimeSent
			));
			exit;
		}
		else if($objCard->status != "scanned")
		{
			if ($objCard->card_type == "MissionsApp")
			{
				$objMission = first($query->ckids_mission_app__select(array(
					"task_id" => $objCard->task_id
				)));
				if (!$objMission)
				{
					json(array(
						"failure" => "Unable to find associated mission"
					));
				}
				$objMissionMarked = first($query->ckids_mission_marking__select(array(
					"task_id" => $objCard->task_id,
					"user_id" => $intUser,
					"_ORDER" => "created DESC",
					"_LIMIT" => 1
				)));
				if (
					$objMissionMarked
					&& (
						$objMission->access_level == 1
						|| (
							$objMission->access_level == 2
							&& (
								!preg_match("/^(2[0-9]{3})\-([0-9]{2})\-([0-9]{2})/", $objMissionMarked->created, $arrMatched)
								|| mktime(0,0,0,$arrMatched[2],$arrMatched[3]+1,$arrMatched[1]) > time()	
							)
						)
					)
				) {
					if ($objMission->access_level == 1)
					{
						print json_encode(array(
							"error" => "This card was already scanned.",
							'timesent' => $intTimeSent
						));
					} else { // 2
						print json_encode(array(
							"error" => "This card was already scanned today.",
							'timesent' => $intTimeSent
						));
					}
					
					exit;
				}
				
				$query->ckids_mission_marking__insert(array(
					"task_id" => $objCard->task_id,
					"user_id" => $intUser,
					"network_id" => $objMission->network_id
				));
				
				preg_match('/^([^\/]+)\/ (.+)/', $objCard->achievement, $arrMatch);
				list ($strMatch, $strName, $strDescription) = $arrMatch;
				$strName = trim($strName);
				$arrOutput['scan_response'] = TRUE;
				$arrOutput['name'] = $strName;
				$arrOutput['description'] = $strDescription;
				
				
				$arrMarking = array_hash('task_id', $query->ckids_mission_marking__select(array(
					'user_id' => $intUser
				)));
				$arrMissionCounts = array(5,15,30,50,75,105,140,180,225);
				$intMarkedMissions = count($arrMarking);
				$arrMedalNames = array("White", "Red", "Orange", "Yellow", "Green", "Blue", "Purple", "Brown", "Gray", "Black");
				$strCurrentMedal = $arrMedalNames[0];
				foreach ($arrMissionCounts as $intItr => $intMedalThreshold) {
					if ($intMedalThreshold <= $intMarkedMissions) {
						$strCurrentMedal = $arrMedalNames[$intItr+1];
					} else {
						break;
					}
				}
				$intNextMedal = $arrMissionCounts[$intItr] - $intMarkedMissions;
				if ($intNextMedal < 0) {
					$intNextMedal = "Finished";
				}
				
				$arrOutput["strCurrentMedal"] = $strCurrentMedal;
				$arrOutput["strNextMedal"] = (string) $intNextMedal;
				$arrOutput['success'] = 'true';
				
				print json_encode($arrOutput);
				exit;
			} 
			else
			{
				if ($objCard->campaign_id)
				{
					//deposit points to child's account and mark the achievement card as scanned
					$query->user_campaigns__insert(array(
						"user_id"			=> $intUser,
						"institution_id"	=> $intInstitution,
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
					"user_id"				=> $intUser,
					"institution_id"		=> $intInstitution,
					"campaign_id"			=> $objCard->campaign_id,
					"mission_id"			=> $objCard->mission_id,
					"task_id"				=> $objCard->task_id,
					"class_id"				=> $objCard->class_id,
					"points"				=> $objCard->card_points,
					"resource_name"			=> "MissionsApp",
					"prize_id"				=> 0
				));
				$query->achievement_cards__update(array(
					"where" => array(
						"card_serial" => $mixBarCode
					),
					"values" => array(
						"status" => "scanned"
					)
				));
				$intUserPointsTotal = $objPoints->user_total(array(
					"user_id" => $intUser,
					"institution_id" => $intInstitution
				));
				$intUserPointsStore = $objPoints->user_store(array(
					"user_id" => $intUser,
					"institution_id" => $intInstitution
				));
				$arrOutput['strStoreBalance'] = "Store: " . number_format($intUserPointsStore, 0) . " point" . ($intUserPointsStore == 1 ? '' : 's');
				$arrOutput['strBalanace'] = "Total: " . number_format($intUserPointsTotal, 0) . " point" . ($intUserPointsTotal == 1 ? '' : 's');
				$arrOutput['success'] = 'true';
				$arrOutput['points_added'] = $objCard->card_points;
				print json_encode($arrOutput);
				exit;
			}
			
		}
	}

	public function indexAction() {
		// all session data must be cleared before a user is logged in
		$this->distroysession();
		$objConfig = new Config();
		$objHebrewSchools = new HebrewSchools();
		$this->view->arrGet = $arrGet = $this->_request->getParams();
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		if (isset($arrGet['from_widget']) && empty($strTemplateStyle))
		{
			$strTemplateStyle = "chabadhebrewschool";
		}
		$this->_sesh = new Zend_Session_Namespace('hebrewschools');
		$this->_sesh->template_style = $strTemplateStyle;
		$this->view->template_style = $strTemplateStyle;
		$this->view->tstyle = $strTemplateStyle;
		$this->view->arrKioskLogos = $objConfig->load(array(
			"set" => array("kiosk_logos", "kiosk_logos_on"),
			"institution_id" => $this->view->institution_id
		));
		$boolWithGet = $this->_request->getParam("withget") == 'true';
		if ($this->_request->isPost() || $boolWithGet)
		{
			// Validate and authenticate
			$intBarCode = $boolWithGet ? $this->_request->getParam('bar_code') : $this->_request->getPost('bar_code');
			$arrResults = $objHebrewSchools->KioskAuthenticate(array(
				"bar_code" => $intBarCode,
				"template_style" => $strTemplateStyle
			));
			print json_encode($arrResults);
			exit;
		}
	}

	public function testAction() {

	}
	function distroysession()
	{
		if (isset($_SESSION['hebrewschools']))
			unset($_SESSION['hebrewschools']);
		if (isset($_COOKIE["hebrewschools_store_cart"]))
			setcookie ("hebrewschools_store_cart", "", time() - 86400, '/', parse_url(WEB_ROOT, PHP_URL_HOST));
	}

}
?>