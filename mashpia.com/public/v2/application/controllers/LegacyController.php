<?php
// potential crons:
// http://v2dev1.mashpia.com/legacy/verifyclassnames
// http://v2dev1.mashpia.com/legacy/updatestudents/start/0
// http://v2dev1.mashpia.com/legacy/verifyuserdetails/start/0
// http://v2dev1.mashpia.com/legacy/verifyuserregistration



class LegacyController extends Zend_Controller_Action
{
	private $_db;


	function preDispatch()
	{

		/*$this->boolVerbose = 0; // Controller top level verbosity debug

		// Get the session object
		$user_session = new Zend_Session_Namespace('user_session_data');

		if ($user_session)
		{
			if (!empty($user_session->user_id) && !empty($user_session->institution_id) && !empty($user_session->permission) && $user_session->is_user_active)
			{
				// Send the user's id, their permission, and listing permissions to the view files
				$this->view->user_id = $user_session->user_id;
				$this->view->full_name = $user_session->full_name;
				$this->view->company_id = $user_session->company_id;
				$this->view->permission = $user_session->permission;
				$this->view->institution_name = $user_session->institution_name;
			}
			else
			{
				// Not allowed in
				$this->_redirect('logout');
			}
		}
		else
		{
			// Not allowed in
			$this->_redirect('logout');
		}*/
	}

	public function indexAction()
	{
		$objLegacy = new Legacy();
		var_dump($objLegacy->testtest());
		exit;
	}
	public function dailyAction()
	{
		$arrUrls = array(
			"http://v2dev1.mashpia.com/legacy/verifyclassnames",
			"http://v2dev1.mashpia.com/legacy/updatestudents/start/0",
			"http://v2dev1.mashpia.com/legacy/verifyuserdetails/start/0",
			"http://v2dev1.mashpia.com/legacy/verifyuserregistration"

		);
		foreach ($arrUrls as $strUrl)
		{
			print "<div><a href='$strUrl'>$strUrl</a></div><br/>";
		}

		$arrUrls = array(
			"http://v2dev1.mashpia.com/legacy/verifyaddons"

		);
		foreach ($arrUrls as $strUrl)
		{
			print "<div><a style='color:gray;' href='$strUrl'>$strUrl</a></div><br/>";
		}
		exit;
	}

	public function verifyaddonsAction()
	{
		$query = new QueryGen();
		// remove old add ons
		$objLegacy = new Legacy();
		$arrAddOns = array_stack('school_add_on_id', $objLegacy->datahacker(array(
			"strSql" => "
				SELECT
					school_add_on_id
				FROM
					school_add_ons
				WHERE
					year <= 2012
			"
		)));
		$arrPrizes = array_stack('prize_id', $query->prize__select(array(
			'legacy_add_on_id' => $arrAddOns,
			'is_active' => 1
		)));
		print "Disabling: " . count($arrPrizes) . "<br/>\n";
		foreach ($arrPrizes as $intPrize)
		{
			$query->prize__update(array(
				'where' => array(
					'prize_id' => $intPrize
				),
				'values' => array(
					'is_active' => 0
				)
			));
		}
		exit;
	}

	public function headerv2missionsAction()
	{
		$query = new QueryGen();
		$arrParams = unserialize($this->_request->getPost('arrParams'));
		if (0) {
			$arrParams['arrUserCodes'] = array('38095219178096557000','33678463922776710700');
		}
		if (!isset($arrParams['arrUserCodes']))
		{
			print "Sorry, there was an error: CL-HVM101-HGFD23";
			exit;
		}
		if (!is_array($arrParams['arrUserCodes']))
			$arrParams['arrUserCodes'] = array($arrParams['arrUserCodes']);
		if (isset($arrParams['intCampaign']))
			$arrParams['intCampaign'] = 1;
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'bar_code' => $arrParams['arrUserCodes']
		)));
		if (!count($arrUsers))
		{
			print serialize(array());
			exit;
		}
		$arrUserIds = array_keys($arrUsers);
		$arrUserCampaignParams = array(
			'_MAX' => 'mission_increment',
			'_GROUP' => 'user_id',
			'user_id' => $arrUserIds
		);
		if (isset($arrParams['intEndDate']))
			$arrUserCampaignParams['_LESSER'] = array(
				'schedule_date' => $arrParams['intEndDate']
			);
		if (isset($arrParams['intStartDate']))
			$arrUserCampaignParams['_GREATER'] = array(
				'schedule_date' => $arrParams['intStartDate']
			);
		$arrUserCampaigns = $query->user_campaigns__select($arrUserCampaignParams);
		$arrResults = array();
		foreach ($arrUserCampaigns as $objUserCampaign)
		{
			$arrResults[$arrUsers[$objUserCampaign->user_id]->bar_code] = $objUserCampaign->_max_mission_increment;
		}
		print serialize($arrResults);
		exit;
	}

	public function achievementcardapiAction()
	{
		$objCampaigns = new Campaigns();
		$objInstitutions = new Institutions();
		$objAchievementCards = new AchievementCards();
		$objTasks = new Tasks();
		$objUsers = new Users();
		$objPermissions = new Permissions();

		$intBarCode = $this->_request->getParam("bar_code");
		if (!$intBarCode)
		{
			print "Sorry, there was an error: CL-ACAPI101-9DSDDD";
			exit;
		}
		$objUser = first($objUsers->_users_select(array(
			"bar_code" => $intBarCode
		)));
		if (!$objUser)
		{
			print "Sorry, there was an error: CL-ACAPI101-9DSDDD";
			exit;
		}
		$objPermission = first($objPermissions->_permissions_select(array(
			"user_id" => $objUser->user_id
		)));
		if (!$objPermission)
		{
			print "Sorry, there was an error: CL-ACAPI101-9DSDDD";
			exit;
		}

		$intCard = $this->_request->getParam("card_id");
		if (!$intCard)
		{
			print "Sorry, there was an error: CL-ACAPI102-SD0D0S";
			exit;
		}

		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $objPermission->institution_id
		)));

		$objCard = first($objAchievementCards->_achievement_cards_select(array(
			"card_serial" => $intCard
		)));

		if (isset($objCard->task_id))
		{
			$objTask = $objTask = first($objTasks->_tasks_select(array(
				"task_id" => $objCard->task_id
			)));
			$objCampaign = $objCampaign = first($objCampaigns->_campaigns_select(array(
				"campaign_id" => $objTask->campaign_id
			)));
		}

		if (!$objCard)
		{
			// Achievement cards sheet probably wasnt printed
			print "failed";
			exit;
		}
		else if($objCard->status != "scanned")
		{
			if ($objCard->campaign_id)
			{
				//deposit points to child's account and mark the achievement card as scanned
				$objCampaigns->_user_campaigns_insert(array(
					"user_id"			=> $objUser->user_id,
					"institution_id"	=> $objPermission->institution_id,
					"campaign_id"		=> $objCard->campaign_id,
					"mission_id"		=> $objCard->mission_id,
					"task_id"			=> $objCard->task_id,
					"schedule_date"		=> time(),
					"points_given"		=> $objCard->card_points
				));
			}
			$objAchievementCards->deposit_points(array(
				"institution_id"	=> $objPermission->institution_id,
				"card_serial"		=> $objCard->card_serial,
				"user_id"			=> $objUser->user_id,
				"card_points"		=> $objCard->card_points,
				"campaign_id"		=> $objCard->campaign_id,
				"mission_id"		=> $objCard->mission_id,
				"task_id"			=> $objCard->task_id
			));
			print $objCard->card_points;
			exit;
		}
		print "failed";
		exit;
	}
	
	public function userpointsextract2Action()
	{
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		$objKiosk = new Kiosk();
		$objUsers = new Users();
		$objLegacy = new Legacy();
		$query = new QueryGen();
		$strBarCode = $this->_request->getPost("user_code");
		if (!$strBarCode)
		{
			print "Sorry, there was an error: CL-UPE2101-A553D";
			exit;
		}
		$arrBarCodes = unserialize($strBarCode);
		if (!$arrBarCodes)
		{
			print "Sorry, there was an error: CL-UPE2103-AD553D";
			exit;
		}
		if (!is_array($arrBarCodes))
			$arrBarCodes = array($arrBarCodes);
		if (!count($arrBarCodes))
		{
			print "Sorry, there was an error: CL-UPE2104-A1113D";
			exit;
		}

		$boolNoNegs = $this->_request->getParam("no_negs");

		// Get the user id from bar code
		$arrUsers = array();
		foreach ($arrBarCodes as $intBarCode)
		{
			$arrUsers[$intBarCode] = first($query->users__select(array(
				"bar_code" => strlen($intBarCode)==19 ? "3" . $intBarCode : $intBarCode
			)));
			/*
			$arrUsers[$intBarCode] = $objLegacy->import_student(array(
				"bar_code" => strlen($intBarCode)==19 ? "3" . $intBarCode : $intBarCode
			));
			*/
		}
	
		// Start end year for school. This will be different depending on the school
		$intMonth = 13;
		$intDay = 18;
		$year_offset = 0;
		$starting = 0;
		$arrHebrewPointsParams = array(
			"user_id" => array_keys(array_stack("user_id", $arrUsers)),
			"jd_date" => $this->dateThisYear($intMonth, $intDay, $starting, $year_offset)
		);
		if ($boolNoNegs)
			$arrHebrewPointsParams["no_negs"] = 1;
		$arrHebrewPoints = $objKiosk->user_points_sum_multi_select($arrHebrewPointsParams);

		$arrPointsParams = array(
			"user_id" => array_keys(array_stack("user_id", $arrUsers))
		);
		if ($boolNoNegs)
			$arrPointsParams["no_negs"] = 1;
		$arrAllPoints = $objKiosk->user_points_sum_multi_select($arrPointsParams);
		$intJD = $this->_request->getParam("jd_date");
		$arrJDPoints = array();
		if ($intJD)
		{
			$arrPointsParams["jd_date"] = $intJD;
			$arrJDPoints = $objKiosk->user_points_sum_multi_select($arrPointsParams);
		}
		$arrResults = array();
		foreach ($arrUsers as $objUser)
		{
			if (isset($objUser->bar_code))
			{
				$arrResults[$objUser->bar_code] = array(
					"jd_points" => intval(@$arrJDPoints[$objUser->user_id]->total),
					"hebrew_points" => intval(@$arrHebrewPoints[$objUser->user_id]->total),
					"all_points" => intval(@$arrAllPoints[$objUser->user_id]->total)
				);
			}
		}
		print serialize($arrResults);
		exit;
	}

	public function userpointsextract3Action()
	{
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		$objKiosk = new Kiosk();
		$objUsers = new Users();
		$objLegacy = new Legacy();
		$objPoints = new Points();
		$query = new QueryGen();
		$strBarCode = $this->_request->getPost("user_code");
		if (!$strBarCode)
		{
			print "Sorry, there was an error: CL-UPE2101-A553D";
			exit;
		}
		$arrBarCodes = unserialize($strBarCode);
		if (!$arrBarCodes)
		{
			print "Sorry, there was an error: CL-UPE2103-AD553D";
			exit;
		}
		if (!is_array($arrBarCodes))
			$arrBarCodes = array($arrBarCodes);
		if (!count($arrBarCodes))
		{
			print "Sorry, there was an error: CL-UPE2104-A1113D";
			exit;
		}
		
		
		// no negs was first implemented to be able to calculate the total, thus no subtractions from the store.
		// since calculations are done differently now we can just use the as a flag for total points
		// if its true its a request for the total (no store subtractions)
		$boolNoNegs = $this->_request->getParam("no_negs");

		// Get the user id from bar code
		$arrUsers = array();
		foreach ($arrBarCodes as $intBarCode)
		{
			$arrUsers[$intBarCode] = first($query->users__select(array(
				"bar_code" => strlen($intBarCode)==19 ? "3" . $intBarCode : $intBarCode
			)));
			/*
			$arrUsers[$intBarCode] = $objLegacy->import_student(array(
				"bar_code" => strlen($intBarCode)==19 ? "3" . $intBarCode : $intBarCode
			));
			*/
		}
		// Start end year for school. This will be different depending on the school
		$intMonth = 13;
		$intDay = 18;
		$year_offset = 0;
		$starting = 0;
		$arrHebrewPointsParams = array(
			"user_id" => array_keys(array_stack("user_id", $arrUsers)),
			"jd_date" => $this->dateThisYear($intMonth, $intDay, $starting, $year_offset)
		);
		//if ($boolNoNegs)
		//	$arrHebrewPointsParams["no_negs"] = 1;
		$arrHebrewPoints = $objPoints->user_points_sums($arrHebrewPointsParams);

		$arrPointsParams = array(
			"user_id" => array_keys(array_stack("user_id", $arrUsers))
		);
		//if ($boolNoNegs)
		//	$arrPointsParams["no_negs"] = 1;
		$arrAllPoints = $objPoints->user_points_sums($arrPointsParams);
		$intJD = $this->_request->getParam("jd_date");
		$arrJDPoints = array();
		if ($intJD)
		{
			$arrPointsParams["jd_date"] = $intJD;
			$arrJDPoints = $objPoints->user_points_sums($arrPointsParams);
		}
		$arrResults = array();
		foreach ($arrUsers as $objUser)
		{
			if (isset($objUser->bar_code))
			{
				$arrResults[$objUser->bar_code] = array(
					"jd_points" => intval(@$arrJDPoints[$objUser->user_id][$boolNoNegs ? "total" : "store"]),
					"hebrew_points" => intval(@$arrHebrewPoints[$objUser->user_id][$boolNoNegs ? "total" : "store"]),
					"all_points" => intval(@$arrAllPoints[$objUser->user_id][$boolNoNegs ? "total" : "store"])
				);
			}
		}
		print serialize($arrResults);
		exit;
	}
	
	public function userstorepointsAction() {
		//$query = new QueryGen();
		$objPoints = new Points();
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		$strBarCode = $this->_request->getPost("user_code");
		if (!$strBarCode)
		{
			print "Sorry, there was an error: CL-UPE2101-A553D";
			exit;
		}
		$arrBarCodes = unserialize($strBarCode);
		if (!$arrBarCodes)
		{
			print "Sorry, there was an error: CL-UPE2103-AD553D";
			exit;
		}
		if (!is_array($arrBarCodes))
			$arrBarCodes = array($arrBarCodes);
		if (!count($arrBarCodes))
		{
			print "Sorry, there was an error: CL-UPE2104-A1113D";
			exit;
		}
		$arrUsers = array();
		$objUsers = new Users();
		foreach ($arrBarCodes as $intBarCode)
		{
			/*
			$objUser = first($query->users__select(array(
				"bar_code" => strlen($intBarCode)==19 ? "3" . $intBarCode : $intBarCode
			)));
			$objPermission = first($query->permissions__select(array(
				'user_id' => $objUser->user_id
			)));
			*/
			
			$objUser = first($objUsers->getMashpiaUser($intBarCode));
			$intPoints = $objPoints->user_store(array(
				'user_id' => $objUser->user_id,
				'institution_id' => $objUser->school_id
			));
			$arrUsers[$intBarCode] = $intPoints;
		}
		print serialize($arrUsers);
		exit;
	}
	
	public function userpointsAction() {
		//$query = new QueryGen();
		$objPoints = new Points();
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		$strBarCode = $this->_request->getPost("user_code");
		if (!$strBarCode)
		{
			print "Sorry, there was an error: CL-UPE2101-A553D";
			exit;
		}
		$arrBarCodes = unserialize($strBarCode);
		if (!$arrBarCodes)
		{
			print "Sorry, there was an error: CL-UPE2103-AD553D";
			exit;
		}
		if (!is_array($arrBarCodes))
			$arrBarCodes = array($arrBarCodes);
		if (!count($arrBarCodes))
		{
			print "Sorry, there was an error: CL-UPE2104-A1113D";
			exit;
		}
		
		$boolNoNegs = $this->_request->getParam("no_negs");
		$date = $this->_request->getParam("date");
		$start_date = $this->_request->getParam("start_date") ? $this->_request->getParam("start_date") : 0;
		
		$arrUsers = array();
		$objUsers = new Users();
		foreach ($arrBarCodes as $intBarCode)
		{
			/*
			$objUser = first($query->users__select(array(
				"bar_code" => strlen($intBarCode)==19 ? "3" . $intBarCode : $intBarCode
			)));
			$objPermission = first($query->permissions__select(array(
				'user_id' => $objUser->user_id
			)));
			*/
			
			$objUser = first($objUsers->getMashpiaUser($intBarCode));
			if ($boolNoNegs) {
				$intPoints = $objPoints->user_total(array(
					'user_id' => $objUser->user_id,
					'institution_id' => $objUser->school_id,
					'start_date' => $start_date
				));
			} else if ($date) {
				$intPoints = $objPoints->user_auction(array(
					'user_id' => $objUser->user_id,
					'institution_id' => $objUser->school_id,
					'auction_date' => $date
				));
			} else if ($start_date) {
				$intPoints = $objPoints->user_store(array(
					'user_id' => $objUser->user_id,
					'institution_id' => $objUser->school_id,
					'start_date' => $start_date
				));
			}
			$arrUsers[$intBarCode] = $intPoints;
		}
		print serialize($arrUsers);
		exit;
	}

	public function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting)
			$starting = unixtojd();

		$today = cal_from_jd($starting, CAL_JEWISH);
		$strDate = cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
		return $strDate;
	}
	
	public function userpointsextractAction()
	{
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		$objKiosk = new Kiosk();
		$objUsers = new Users();
		$intBarCode = $this->_request->getParam("bar_code");
		if (!$intBarCode)
		{
			print "Sorry, there was an error: CL-UPE101-SD8FDD";
			exit;
		}

		// Get the user id from bar code
		$objUser = first($objUsers->_users_select(array(
			"bar_code" => $intBarCode
		)));
		if (!$objUser)
		{
			print "Sorry, there was an error: CL-UPE102-8SDF8D";
			exit;
		}

		print intval($objKiosk->user_points_sum_select_hebrew(array(
			"user_id" => $objUser->user_id
		)));
		exit;
	}
	
	public function updatestudentAction()
	{
		$objLegacy = new Legacy();
		$intLegacyStudent = intval($this->_request->getParam("legacy_user_id"));
		if ($intLegacyStudent < 1)
		{
			print "Sorry, there was an error: CL-US101-8FSSAA";
			exit;
		}
		$strResult = $objLegacy->import_student(array(
			"legacy_user_id" => $intLegacyStudent
		));
		var_dump($strResult);
		exit;
	}

	public function updatestudentsAction()
	{
		$objLegacy = new Legacy();
		//$this->_verbose = 1;
		$intStart = $this->_request->getParam('start');
		if (!$intStart)
			$intStart = 0;
		$strSql = "
				SELECT
					user_id
				FROM
					users
				WHERE
					class_id > 0
					AND user_registered IS NOT NULL
				LIMIT " . $intStart . ",200
			";
		print $strSql . ' | ';
		$arrData = $objLegacy->datahacker(array(
			"strSql" => $strSql
		));
		print count($arrData) . " | ";
		foreach ($arrData as $arrRow)
		{
			$objLegacy->import_student(array(
				"legacy_user_id" => $arrRow["user_id"]
			));
		}
		if (count($arrData) == 0)
		{
			print "Done.";
			exit;
		}
		print "<br /><a href='/legacy/updatestudents/start/" . ($intStart + 200) . "'>next</a>";
		print "<script>window.location.href='/legacy/updatestudents/start/" . ($intStart + 200) . "';</script>";
		exit;
	}

	public function updateallinstitutionsAction()
	{
		$objLegacy = new Legacy();
		$objLegacy->runallinstitutions();
		exit;
	}

	public function onetimefixAction()
	{
		$objLegacy = new Legacy();
		$objLegacy->onetimefix();
		exit;
	}

	public function updateallmedals2Action()
	{
		$intStart = $this->_request->getParam('start');
		if (!$intStart)
			$intStart = 0;
		$query = new QueryGen();
		$objMedals = new Medals();
		$objLegacy = new Legacy();
		$arrEnrollments = array_hash("user_id", $query->user_campaigns__select(array(
			//'user_id' => 16305,
			"status" => "Enrollment",
			"campaign_id" => 1,
			'_LIMIT' => $intStart . ',50'
		)));
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'user_id' => array_keys($arrEnrollments)
		)));
		if (!count($arrEnrollments))
		{
			print "Done.";
			exit;
		}
		$intCount = 0;
		foreach ($arrEnrollments as $intUser => $objEnrollment)
		{
			if (!isset($arrUsers[$intUser]))
				continue;
			$objUser = $arrUsers[$intUser];
			if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $objUser->dob))
				continue;
			$objMedal = $objMedals->user_medal_completed(array(
				'user_id' => $intUser,
				'campaign_id' => 1,
				'institution_id' => $objEnrollment->institution_id
			));
			if ($objMedal)
			{
				$objLegacy->legacy_push_user_missions(array(
					"intUser" => $intUser,
					"strMedalName" => $objMedal->medal_name
				));
				$intCount++;
			}
		}
		print "Updated: " . $intCount;
		print "<br /><a href='/legacy/updateallmedals2/start/" . ($intStart + 50) . "'>next</a>";
		print "<script>window.location.href='/legacy/updateallmedals2/start/" . ($intStart + 50) . "';</script>";
		exit;
	}

	public function updateallmedalsAction()
	{
		$boolVerbose = 0;
		$query = new QueryGen();
		$objLegacy = new Legacy();
		$arrEnrollments = array_hash("user_id", $query->user_campaigns__select(array(
			"status" => "Enrollment",
			"campaign_id" => 1,
			'user_id' => 3845
		)));
		$arrLatestLines = $query->user_campaigns__select(array(
			"_MAX" => "task_increment",
			"_GROUP" => "user_id",
			"_NOT_NULL" => "mission_id",
			"campaign_id" => 1
		));
		$arrTanyaMedals = array_hash("medal_value", $query->medals__select(array(
			"campaign_id" => 1,
			"institution_id" => 1
		)));
		$arrMedalValues = array_keys($arrTanyaMedals);
		$arrUsers = array();
		foreach ($arrLatestLines as $intItr => $objLatestLine)
		{
			if ($boolVerbose)
				print "User ID: " . $objLatestLine->user_id . " <br />\n";
			if (!isset($arrEnrollments[$objLatestLine->user_id]))
				continue;
			$intLatestLine = $objLatestLine->_max_task_increment - intval($arrEnrollments[$objLatestLine->user_id]->line_offset);

			if ($boolVerbose)
				print "Latest Line: " . $intLatestLine . " <br />\n";
			// Find the current medal from a line
			unset($intLastMedal);
			foreach ($arrMedalValues as $intItr2 => $intMedalValue)
			{
				if ($intMedalValue > $intLatestLine)
					break;
				$intLastMedal = $intMedalValue;
			}
			if (!isset($intLastMedal))
				continue;
			$objMedal = $arrTanyaMedals[$intLastMedal];
			if ($boolVerbose)
				var_dump($objMedal);
			$objLegacy->update_users_medal(array(
				"user_id" => $objLatestLine->user_id,
				"medal" => $objMedal->medal_hierarchy+1,
				"verbose" => $boolVerbose,
				"rank_update_bypass" => true
			));
			$arrUsers[] = $objLatestLine->user_id;
		}
		$arrLegacyUsers = array_stack("legacy_id", $query->legacy_lookup__select(array(
			"legacy_table" => "users",
			"ims_table" => "users",
			"ims_id" => $arrUsers
		)));
		//print join(",", array_keys($arrLegacyUsers));exit;
		print count($arrLegacyUsers) . " <br /> \n";
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_URL, "http://mashpia.com/classes/rank_updater_passthrough.php");
		curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, array(
			"user_ids" => join(",", array_keys($arrLegacyUsers))
		));
		$strResult = curl_exec($objCurl);
		curl_close($objCurl);
		print $strResult;
		exit;
	}

	public function legacyranksupdateAction()
	{
		$boolVerbose = 0;
		$query = new QueryGen();
		$objLegacy = new Legacy();
		$arrEnrollments = array_hash("user_id", $query->user_campaigns__select(array(
			"status" => "Enrollment",
			"campaign_id" => 1
		)));
		$arrLatestLines = $query->user_campaigns__select(array(
			"_MAX" => "task_increment",
			"_GROUP" => "user_id",
			"_NOT_NULL" => "mission_id",
			"campaign_id" => 1
		));
		$arrTanyaMedals = array_hash("medal_value", $query->medals__select(array(
			"campaign_id" => 1,
			"institution_id" => 1
		)));
		$arrMedalValues = array_keys($arrTanyaMedals);
		$arrUsers = array();
		foreach ($arrLatestLines as $intItr => $objLatestLine)
		{
			if (338 != $objLatestLine->user_id)
				continue;
			//if ($objLatestLine->user_id != 133)
			//	continue;
			if ($boolVerbose)
				print "User ID: " . $objLatestLine->user_id . " <br />\n";

			$intLatestLine = $objLatestLine->_max_task_increment - $arrEnrollments[$objLatestLine->user_id]->line_offset;

			if ($boolVerbose)
				print "Latest Line: " . $intLatestLine . " <br />\n";
			// Find the current medal from a line
			foreach ($arrMedalValues as $intItr2 => $intMedalValue)
			{
				if ($intMedalValue > $intLatestLine)
					break;
				$intLastMedal = $intMedalValue;
			}
			if (!isset($intLastMedal))
				continue;
			$arrUsers[] = $objLatestLine->user_id;
		}
		//print count($arrUsers) . " <br /> \n";exit;
		$arrLegacyUsers = array_stack("legacy_id", $query->legacy_lookup__select(array(
			"legacy_table" => "users",
			"ims_table" => "users",
			"ims_id" => $arrUsers
		)));
		//print count($arrLegacyUsers) . " <br /> \n";exit;
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_URL, "http://mashpia.com/classes/rank_updater_passthrough.php");
		curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, array(
			"user_ids" => join(",", array_slice(array_keys($arrLegacyUsers),0,1))
		));
		$strResult = curl_exec($objCurl);
		curl_close($objCurl);
		print $strResult;
		exit;
	}

	/*
	 *
	 */
	public function outputcustom1Action()
	{
		$query = new QueryGen();
		if (1)
		{
			$arrParams = unserialize($this->_request->getPost('params'));
		}
		else
		{
			$arrParams = array(
				'end_date' => strtotime('-10 months'),
				array(
					'user_id' => '5646', // 141
				),
				array(
					'user_id' => '5641', // 136
				)
			);
		}
		$intStartDate = 0;
		$intEndDate = time();
		if (isset($arrParams['start_date']))
		{
			$intStartDate = $arrParams['start_date'];
			unset($arrParams['start_date']);
		}
		if (isset($arrParams['end_date']))
		{
			$intEndDate = $arrParams['end_date'];
			unset($arrParams['end_date']);
		}

		if (!is_array(first($arrParams)))
			$arrParams = array($arrParams);

		$arrResponse = array(
			'success' => 'false',
			'data' => array()
		);
		$arrLegacyIds = array();
		foreach ($arrParams as $intItr => $arrUser)
		{
			if (is_array($arrUser['user_id']))
			{
				foreach ($arrUser['user_id'] as $intUserId)
				{
					$arrParams[]['user_id'] = $arrUser;
					$arrParams[count($arrParams)-1]['user_id'] = $intUserId;
				}
				unset($arrParams[$intItr]);
			}
		}
		foreach ($arrParams as $arrUser)
		{
			$arrLegacyIds[$arrUser['user_id']] = 1;
		}
		$arrUsers = array();
		if (count($arrLegacyIds))
		{
			$arrLegacyUsers = array_hash('legacy_id', $query->legacy_lookup__select(array(
				'legacy_id' => array_keys($arrLegacyIds),
				'legacy_table' => 'users',
				'ims_table' => 'users'
			)));
			$arrUsers = array_hash('user_id', $query->users__select(array(
				'user_id' => array_keys(array_stack('ims_id', $arrLegacyUsers))
			)));
		}
		$arrCampaignEnrollment = array_hash('user_id', $query->user_campaigns__select(array(
			"user_id" => array_keys(array_stack('user_id', $arrUsers)),
			"campaign_id" => 1,
			"status" => "Enrollment"
		)));
		$arrLadders = array_hash('ladder', $query->velocity_ladders__select(array(
			'_COLUMNS' => array('ladder', 'velocity'),
			'campaign_id' => 1
		)));
		if (count($arrUsers))
		{
			$intItr = 0;
			foreach ($arrParams as $arrUser)
			{
				$intLegacyUser = $arrUser['user_id'];
				if (!isset($arrLegacyUsers[$intLegacyUser]))
					continue;
				$intUser = $arrLegacyUsers[$intLegacyUser]->ims_id;
				if (!isset($arrUsers[$intUser]))
					continue;
				$objUser = $arrUsers[$intUser];
				// get users ladder
				if (!isset($arrCampaignEnrollment[$intUser]))
					continue;
				if ($arrCampaignEnrollment[$intUser]->ladder == 0)
					continue;
				$arrResponse['success'] = 'true';
				$objEnrollment = $arrCampaignEnrollment[$intUser];
				$arrResponse['data'][$intItr]['ims_id'] = $intUser;
				$arrResponse['data'][$intItr]['user_id'] = $intLegacyUser;
				$arrResponse['data'][$intItr]['line_offset'] = $arrCampaignEnrollment[$intUser]->line_offset;
				$intCurrentVelocity = $arrLadders[$objEnrollment->ladder-1]->velocity;
				$arrResponse['data'][$intItr]['ladder'] = $intCurrentVelocity;
				$intItr++;
			}
		}
		if ($arrResponse['success'] == 'true')
		{
			$arrCampaignQuotaParams = array(
				'_COLUMNS' => array('institution_id','task_increment','user_id','schedule_date','ladder_velocity','status','line_offset'),
				'user_id' => array_keys(array_stack('ims_id', $arrResponse['data'])),
				'campaign_id' => 1,
				'status' => array('Enrollment','In Progress','Completed'),
				'_GREATER' => array(
					'schedule_date' => $intStartDate
				),
				'_LESSER' => array(
					'schedule_date' => $intEndDate
				)
			);
			$arrCampaignQuotas = $query->user_campaigns__select($arrCampaignQuotaParams);
			// calculate lines and quotas
			$arrUserData = array();
			$arrProcessedUserSchedules = array();
			foreach ($arrCampaignQuotas as $objUserCampaign)
			{
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
				)
					$arrUserItem['lines_max'] = $objUserCampaign->task_increment;
				// goals
				if (!isset($arrProcessedUserSchedules[$objUserCampaign->user_id][$objUserCampaign->schedule_date]))
				{
					$arrProcessedUserSchedules[$objUserCampaign->user_id][$objUserCampaign->schedule_date] = TRUE;
					$arrUserItem['goal'] += $objUserCampaign->ladder_velocity;
					$arrUserItem['goal_count']++;
				}
			}
			//dumper($arrUserData,1,1);
			// calulcate remainders for the unmarked items
			foreach ($arrCampaignEnrollment as $objEnrollment)
			{
				if (!isset($arrUserData[$objEnrollment->user_id]))
				{
					$arrUserData[$objEnrollment->user_id] = array(
						'goal' => 0,
						'goal_count' => 0,
						'lines' => 0
					);
				}
				$arrUserItem = &$arrUserData[$objEnrollment->user_id];
				$intUserStart = $intStartDate < $objEnrollment->schedule_date ? $objEnrollment->schedule_date : $intStartDate;
				$intWeeks = floor(($intEndDate - $intUserStart) / 60 / 60/ 24 / 7.02388844230769);
				$intMissing = $intWeeks - $arrUserItem['goal_count'];
				//$arrUserItem['missing'] = $intMissing;
				if ($intMissing > 0)
				{
					$arrUserItem['goal'] += $arrLadders[$objEnrollment->ladder ? $objEnrollment->ladder-1 : 1]->velocity * $intMissing;
				}
			}
			$arrSums = array();
			foreach ($arrUserData as $intUser => $arrUserItem2)
			{
				if (!isset($arrSums[$intUser]['goal']))
					$arrSums[$intUser]['goal'] = 0;
				if (!isset($arrSums[$intUser]['lines']))
					$arrSums[$intUser]['lines'] = 0;
				$arrSums[$intUser]['goal'] += $arrUserItem2['goal'];
				if (
					!isset($arrUserItem2['lines'])
					|| $arrUserItem2['lines'] == 0
				) {
					if (!isset($arrUserItem2['lines_max']) || !isset($arrUserItem2['lines_min']))
						$arrUserItem2['lines'] = 0;
					else
						$arrUserItem2['lines'] = $arrUserItem2['lines_max'] - $arrUserItem2['lines_min'];
				}
				$arrSums[$intUser]['lines'] += $arrUserItem2['lines'];
			}
			foreach ($arrResponse['data'] as $intKey => $arrUser)
			{
				$arrResponse['data'][$intKey]['goal'] = $arrSums[$arrUser['ims_id']]['goal'];
				$arrResponse['data'][$intKey]['completed'] = $arrSums[$arrUser['ims_id']]['lines'];
			}
		}
		print serialize($arrResponse);
		exit;
	}

	/// ------------------------

	public function synctanyaclassesAction()
	{
		$query = new QueryGen();
		$objLegacy = new Legacy();
		$arrQueryItem1 = array(
			'template_style' => 'tanyatemplate1'
		);
		//if (devel)
			//$arrQueryItem1['institution_id'] = 47;
		$arrInstitutions = $query->institutions__select(array(
			$arrQueryItem1,
			'_ORDER' => 'name'
		));
		$arrClasses = $query->classes__select(array(
			'institution_id' => array_keys(array_stack('institution_id', $arrInstitutions))
		));
		$arrLookupClasses = $query->legacy_lookup__select(array(
			'_COLUMNS' => array('legacy_id', 'ims_id'),
			'ims_id' => array_keys(array_stack('class_id', $arrClasses)),
			'ims_table' => 'classes'
		));
		$arrLegacyClassIds = first(array_extract2('class_id', $objLegacy->datahacker(array(
			'strSql' => "SELECT `class_id` FROM `classes` WHERE `class_id` IN (" . join(',', array_keys(array_stack('legacy_id', $arrLookupClasses))) . ")"
		))));
		foreach ($arrLookupClasses as $objLookup)
		{
			if (!isset($arrLegacyClassIds[$objLookup->legacy_id]))
			{
				print "Deleted Legacy ID: " . $objLookup->legacy_id . " <br />\n";
				$query->legacy_lookup__delete(array(
					'ims_table' => 'classes',
					'legacy_id' => $objLookup->legacy_id,
					'ims_id' => $objLookup->ims_id
				));
				$query->classes__delete(array(
					'class_id' => $objLookup->ims_id
				));
				$query->user_classes__delete(array(
					'class_id' => $objLookup->ims_id
				));
			}
		}
		//dumper($arrLegacyClassIds,1,1);
		print 'done';
		exit;
	}

	public function verifyclassnamesAction()
	{
		$query = new QueryGen();
		$objLegacy = new Legacy();
		// Load the classes from mashpia
		$arrLegacyClassBubble = array_bubble_hash('school_id', $objLegacy->datahacker(array(
			"strSql" => "
				SELECT
					classes.class_id,
					classes.class_grade,
					classes.class_sub,
					classes.default_level,
					classes.school_id
				FROM
					classes
				WHERE
					1
				ORDER BY
					school_id+0 ASC, class_grade+0 ASC, class_sub ASC
			"
		)));
		$arrLegacyClassHash = array();
		foreach ($arrLegacyClassBubble as $intSchool => $arrSchoolClasses)
		{
			foreach ($arrSchoolClasses as $intHierarchy => $arrLegacyClass)
			{
				$arrLegacyClass['class_hierarchy'] = $intHierarchy;
				$arrLegacyClassHash[$arrLegacyClass['class_id']] = $arrLegacyClass;
			}
		}
		/*
		dumper($arrLegacyClassHash,1,1);
		$arrLegacyClasses = array_hash('class_id', $objLegacy->datahacker(array(
			"strSql" => "
				SELECT
					classes.class_id,
					classes.class_grade,
					classes.class_sub,
					classes.default_level
				FROM
					classes, users
				WHERE
					users.user_registered IS NOT NULL
					AND users.class_id IS NOT NULL
					AND users.class_id != 0
					AND classes.class_id = users.class_id
				GROUP BY
					users.class_id
			"
		)));
		 *
		 */
		$arrLookupClasses = array_hash('ims_id', $query->legacy_lookup__select(array(
			'_COLUMNS' => array('legacy_id', 'ims_id'),
			'legacy_id' => array_keys($arrLegacyClassHash),
			'legacy_table' => 'classes',
			'ims_table' => 'classes'
		)));
		$arrClasses = $query->classes__select(array(
			'class_id' => array_keys($arrLookupClasses)
		));
		foreach ($arrClasses as $objClass)
		{
			$intLegacyId = $arrLookupClasses[$objClass->class_id]->legacy_id;
			$objLegacyClass = $arrLegacyClassHash[$intLegacyId];
			if (
				$objLegacyClass['class_grade'] != $objClass->grade
				|| $objLegacyClass['class_sub'] != $objClass->sub
				|| $objLegacyClass['class_hierarchy'] != $objClass->class_hierarchy
			) {
				print "Missmatch found:<br />\n";
				print "Grade: `" . $objClass->grade . "` to `" . $objLegacyClass['class_grade'] . "`<br />\n";
				print "Sub: `" . $objClass->sub . "` to `" . $objLegacyClass['class_sub'] . "`<br />\n";
				print "Hierarchy: `" . $objClass->class_hierarchy . "` to `" . $objLegacyClass['class_hierarchy'] . "`<br />\n";
				$query->classes__update(array(
					'values' => array(
						'grade' => $objLegacyClass['class_grade'],
						'sub' => $objLegacyClass['class_sub'],
						'class_hierarchy' => $objLegacyClass['class_hierarchy']
					),
					'where' => array(
						'class_id' => $objClass->class_id
					)
				));
			}
		}
		exit;
	}

	public function verifyuserdetailsAction()
	{
		$query = new QueryGen();
		$objLegacy = new Legacy();

		$intStart = $this->_request->getParam('start');
		if (!$intStart)
			$intStart = 0;
		// Load the classes from mashpia
		$arrTanyaInstitutions = array_stack('institution_id', $query->institutions__select(array(
			'template_style' => 'tanyatemplate1'
		)));
		$arrUsersPermissions = array_stack('user_id', $query->permissions__select(array(
			'institution_id' => $arrTanyaInstitutions,
			'_LIMIT' => $intStart . ',200'
		)));
		if (!count($arrUsersPermissions))
		{
			print "Done.";
			exit;
		}
		$arrUsersLookup = array_hash('legacy_id', $query->legacy_lookup__select(array(
			'ims_id' => $arrUsersPermissions,
			'ims_table' => 'users',
			'legacy_table' => 'users'
		)));
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'user_id' => $arrUsersPermissions
		)));
		$arrLegacyUsers = $objLegacy->datahacker(array(
			"strSql" => "
				SELECT
					*
				FROM
					users
				WHERE
					user_id in (" . join(",", array_keys($arrUsersLookup)) . ")
			"
		));
		foreach ($arrLegacyUsers as $arrLegacyUser)
		{
			$intLegacyId = $arrLegacyUser['user_id'];
			$intUser = $arrUsersLookup[$intLegacyId]->ims_id;
			$objUser = $arrUsers[$intUser];
			if (
				$objUser->first_name != $arrLegacyUser['first']
				|| $objUser->last_name != $arrLegacyUser['last']
			) {
				print "Change User Details <br />\n";
				print "First name `" . $objUser->first_name . "` to `" . $arrLegacyUser['first'] . "` <br />\n";
				print "Last name `" . $objUser->last_name . "` to `" . $arrLegacyUser['last'] . "` <br />\n";
				$query->users__update(array(
					'where' => array(
						'user_id' => $objUser->user_id
					),
					'values' => array(
						'first_name' => $arrLegacyUser['first'],
						'last_name' => $arrLegacyUser['last']
					)
				));
			}
		}
		print "<br /><a href='/legacy/verifyuserdetails/start/" . ($intStart + 200) . "'>next</a>";
		print "<script>window.location.href='/legacy/verifyuserdetails/start/" . ($intStart + 200) . "';</script>";
		exit;
	}

	public function cleanuplookupsAction() {
		$query = new QueryGen();
		// Missing classes
		$arrLookupClasses = array_hash('ims_id', $query->legacy_lookup__select(array(
			'_COLUMNS' => array('ims_id', 'legacy_lookup_id'),
			'legacy_table' => 'classes',
			'ims_table' => 'classes'
		)));
		$arrFoundClasses = array_stack('class_id', $query->classes__select(array(
			'_COLUMNS' => array('class_id'),
			'class_id' => array_keys($arrLookupClasses)
		)));
		foreach ($arrLookupClasses as $objLookup)
		{
			if (!isset($arrFoundClasses[$objLookup->ims_id]))
			{
				$query->legacy_lookup__delete(array(
					'legacy_lookup_id' => $objLookup->legacy_lookup_id
				));
				print "Delete Legacy ID: " . $objLookup->legacy_lookup_id . " <br />\n";
			}
		}

		//dumper($arrFoundClasses,1,1);
		exit;
	}

	/*
	 * Check if user is unenrolled from legacy system. If so remove the
	 * student from any classes and unenroll them
	 */
	public function verifyuserregistrationAction()
	{
		$query = new QueryGen();
		$objLegacy = new Legacy();
		$intStart = $this->_request->getParam('start');
		if (!$intStart)
			$intStart = 0;

		$arrInstitutions = $query->institutions__select(array(
			'template_style' => 'tanyatemplate1'
		));
		$arrClasses = $query->classes__select(array(
			'institution_id' => array_stack('institution_id', $arrInstitutions)
		));
		$arrUserClasses = $query->user_classes__select(array(
			'class_id' => array_stack('class_id', $arrClasses),
			'_LIMIT' => $intStart . ',200'
		));
		//dumper($arrUserClasses,1,1);
		if (!count($arrUserClasses))
		{
			print 'Done.';
			exit;
		}
		$arrUserLookup = $query->legacy_lookup__select(array(
			'ims_id' => array_stack('user_id', $arrUserClasses),
			'ims_table' => 'users',
			'legacy_table' => 'users'
		));
		$arrLookupIds = array_stack('legacy_id', $arrUserLookup);
		$strSql = "
			SELECT
				users.user_id
			FROM
				users,
				classes
			WHERE
				users.class_id > 0
				AND users.user_registered IS NOT NULL
				AND users.user_id in (" . join(',', $arrLookupIds) . ")
				AND users.class_id = classes.class_id
				AND (classes.class_era = 0 OR classes.school_id = 61)
		";
		$arrData = $objLegacy->datahacker(array(
			"strSql" => $strSql
		));
		$arrDif = array_diff($arrLookupIds, array_stack('user_id', $arrData));
		print "Diff Count: " . count($arrDif) . " <br />\n";
		$arrUserLookup = $query->legacy_lookup__select(array(
			'legacy_id' => $arrDif,
			'ims_table' => 'users',
			'legacy_table' => 'users'
		));
		foreach ($arrUserLookup as $objLookup)
		{
			$intUser = $objLookup->ims_id;
			// remove the user class association
			$query->user_classes__delete(array(
				'user_id' => $intUser
			));
		}
		print "<br /><a href='/legacy/verifyuserregistration/start/" . ($intStart + 200) . "'>next</a>";
		dumper($arrDif,0,1);
		print "<script>window.location.href='/legacy/verifyuserregistration/start/" . ($intStart + 200) . "';</script>";
		exit;
	}
}
?>