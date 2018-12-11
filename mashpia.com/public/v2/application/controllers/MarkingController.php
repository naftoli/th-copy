<?php
class MarkingController extends Zend_Controller_Action
{
    private $_user_session_data;
	private $_roles;
	private $_tools;
	private $objPermission;

    function preDispatch()
    {
		$this->_tools = new ToolsControllers();
		$this->_model_tools = new ToolsModels();
		$this->_roles = new Roles();

		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));
		/*
		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
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

	public function markingclassesAction()
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

	public function markingcampaignsAction()
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
			$this->view->user_id = $intUser = intval($this->_request->getParam("user_id"));
			if (!$intUser)
			{
				$this->view->user_id = null;
				$this->view->class_id = $intClass = intval($this->_request->getParam("class_id"));

				if (!$intClass)
				{
					print "Sorry, there was an error" . ": CM-MC101-23E32F";
					exit;
				}
				if (isset($intClass))
				{
					$objClasses = new Classes();

					$arrUsers = $objClasses->user_classes_select_user(array(
						"class_id" => $intClass
					));
				}
			}
			else
			{
				// Only a single user id was provided
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

	public function markinglistAction()
	{
		$_VERBOSE = 0;

		$query = new QueryGen();
		$objClasses = new Classes();
		$objAutomation = new Automation();
		$objMarking = new Marking();
		$objPermissions = new Permissions();
		$objScheduler = new Scheduler();
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objLadders = new Ladders();
		$objUsers = new Users();
		$objLegacy = new Legacy();
		$intMarkWeeks = $this->view->intMarkWeeks = 500;

		$this->view->campaign_id = $intCampaign = $this->_request->getParam("campaign_id");
		$this->view->class_id = $intClass = $this->_request->getParam("class_id");
		$intUser = $this->_request->getParam("user_id");
		$intCampaign = 1;
		if (!$intCampaign)
		{
			print "Sorry, there was an error" . ": CM-ML102-SDF76D";
			exit;
		}
		$this->view->arrResultData = @unserialize($this->_request->getParam("resultdata"));

		if ($this->_roles->isRole("Teacher"))
		{
			$objTeacherClass = first($objClasses->_user_classes_select(array(
				"user_id" => $this->_user_session_data->user_id,
				"class_role" => "Teacher"
			)));
			if (!$objTeacherClass)
			{
				print "Sorry, there was an error" . ": CM-ML103-AS98SS";
				exit;
			}
			$intClass = $objTeacherClass->class_id;
		}
		else if ($this->_roles->isRole("Institution Administrator"))
		{
			$intClass = $this->_request->getParam("class_id");
			//$this->view->institution_id = $intInstitution = $this->_request->getParam("institution_id");
			//if (!$intInstitution)
			//{
				$this->view->institution_id = $intInstitution = $this->_user_session_data->institution_id;
			//}
		}

		$this->view->objPermission = $objPermissions->_permissions_select(array(
			"permission_id" => $this->_user_session_data->permission_id
		));

		// Handle the AJAX post action
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrResult = array();
			foreach ($arrPost as $strKey => $intLinesCompleted)
			{
				if (preg_match("/^user_lines_([0-9]+)$/", $strKey, $arrMatched))
				{
					$intUser = $arrMatched[1];
					// Mark the current campaign the required amount of lines / missions
					// forward from their current holding
					$arrResult[$intUser] = $objMarking->mark_task_incrament(array(
						"user_id" => $intUser,
						"campaign_id" => $intCampaign,
						"task_incrament" => intval($intLinesCompleted)
					));
					if (legacy_link)
					{
						$objMedals = new Medals();
						$objMedal = $objMedals->user_medal_completed(array(
							'user_id' => $intUser,
							'campaign_id' => 1,
							'institution_id' => $this->_user_session_data->institution_id
						));
						if ($objMedal)
							$objLegacy->legacy_push_user_missions(array(
								"intUser" => $intUser,
								"strMedalName" => $objMedal->medal_name
							));
					}
					/*$objLegacy->update_users_medal(array(
						"user_id" => $objLatestLine->user_id,
						"medal" => $objMedal->medal_hierarchy+1,
						"verbose" => 0
					));*/
				}
			}
			print serialize($arrResult);
			exit; // ajax
		}

		// Construct the marking list data

		// Load the campaign
		$this->view->objCampaign = $objCampaign = current($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objCampaign)
		{
			print text("Sorry, there was an error") . ": CM-ML104-SD6DDF";
			exit;
		}

		// Load the mission
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objMission) {
			print text("Sorry, there was an error") . ": CM-ML103-87SDDD";
			exit;
		}

		// Load the parameters provided by the mission of the campaign
		$this->view->objSchedulingParams = current($objScheduler->_scheduling_params_select(array(
			"mission_id" => $objMission->mission_id,
			"task_id" => 0
		)));

		// Load the users of a class
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
			if (!empty($intClass))
				$arrUsers = $objClasses->user_classes_select_user(array(
					"class_id" => $intClass
				));
			else if (!empty($intUser))
				$arrUsers = $objUsers->_users_select(array(
					"user_id" => $intUser
				));
			else
				$arrUsers = $objUsers->_users_select_hierarchal(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"permission" => "Student"
				));
		}
		$arrUsers = array_hash("user_id", $this->_model_tools->cleanSlashes($arrUsers));
		$arrUserCampaignProgress = $objAutomation->user_goal(array(
			"user_id" => array_keys($arrUsers),
			"campaign_id" => 1,
			'multi' => TRUE
		));
		$arrEnrollments = array_hash('user_id', $query->user_campaigns__select(array(
			"user_id" => array_keys($arrUsers),
			"campaign_id" => $intCampaign,
			"status" => "Enrollment"
		)));
		$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
			"user_id" => array_keys($arrUsers)
		)));
		// Loop through the users and collect all the data required for a marking report
		$arrResults = array();
		$intStartTime = msfloat();
		foreach ($arrUsers as $intUser => $objUser)
		{
			if (!preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $objUser->dob))
				continue;
			if (!isset($arrEnrollments[$intUser]))
				continue;
			if (!isset($arrUserClasses[$intUser]))
				continue;
			if (!isset($arrUserCampaignProgress[$intUser]))
				continue;
			if (!$arrUserCampaignProgress[$intUser]['goal'])
				continue;
			$objEnrollment = $arrEnrollments[$intUser];
			// Get the users ladder velocity
			$intLadderVelocity = $objLadders->campaign_user_ladder_velocity(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id
			));
			if ($_VERBOSE)
				print "intLadderVelocity: " . $intLadderVelocity . " <br>\n";
			// Get just the pending missions + the next mission
			$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"extra_weeks" => -1
			));
			// Get the current line the user is holding on
			$intLatestLineFraction = $objMarking->latest_line_hierarchy(array(
				"mission_id" => $objMission->mission_id,
				"user_id" => $objUser->user_id
			));
			$intLatestMissionLineFraction = $objMarking->latest_mission_line_hierarchy(array(
				"mission_id" => $objMission->mission_id,
				"user_id" => $objUser->user_id
			));
			$intLatestLine = $intLatestLineFraction;
			if ($_VERBOSE)
			{
				print "intLatestLine: " . $intLatestLine . " <br>\n";
				exit;
			}
			// Find the total pending lines
			$intPendingMissionsReal = 0;
			if (count($arrPendingMissions) == 1)
				$intTotalPendingLines = $intLadderVelocity;
			else if (count($arrPendingMissions))
			{
				$intTotalPendingLines = 0;
				$arrFirstPendingMission = reset($arrPendingMissions);
				$intFirstPendingLine = reset($arrFirstPendingMission["tasks"]);
				// find the last due mission
				$intCount = 0;
				for ($intItr=0; $intItr!=count($arrPendingMissions); $intItr++) {
					$arrPendingMission = $arrPendingMissions[$intItr];
					$arrLastPendingMission = $arrPendingMission;
					if ($arrPendingMission['epoch'] > time())
					{
						break;
					}
					$intCount++;
				}
				$intPendingMissionsReal = $intCount;

				$intLastMission = end($arrLastPendingMission["tasks"]);
				$intPendingTotal = $intLastMission - $intFirstPendingLine+1;
				$intTotalPendingLines = $intPendingTotal;
			}
			else
				$intTotalPendingLines = 0;

			$intPending = $objMarking->pending_lines_sum(array(
				"mission_id" => $objMission->mission_id,
				"institution_id" => $this->_user_session_data->institution_id,
				"user_id" => $objUser->user_id
			));
			//print "intPending: $intPending <br>\n";

			$arrLinesAhead = $query->user_campaigns__select(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				'_GREATER' => array(
					'schedule_date' => strtotime('-1 week')
				),
				"_ORDER" => "task_increment + 0 ASC",
				'_NOT' => array(
					'status' => array('Paused', 'Resumed', 'Enrollment')
				)
			));
			if (isset($intFirstLine))
				unset($intFirstLine);
			$intMissionsAhead = 0;
			if (
				count($arrLinesAhead) > 1
			) {
				$objFirstUserCampaign = reset($arrLinesAhead);
				$objLastUserCampaign = end($arrLinesAhead);
				$intLinesAhead = floor($objLastUserCampaign->task_increment) - $objFirstUserCampaign->task_increment;
				$intMissionsAhead = $objLastUserCampaign->mission_increment - $objFirstUserCampaign->mission_increment +1;
			}
			else
				$intLinesAhead = 0;
			// Collect the various details required for a report for each user
			$strKey = $objUser->last_name . " : " . $objUser->first_name . " : " . $objUser->user_id;
			$arrResults[$strKey] = array(
				'intMissionsAhead' => $intMissionsAhead,
				'intPendingMissionsReal' => $intPendingMissionsReal,
				"arrPendingMissions" => $arrPendingMissions,
				"intPendingLines" => $intTotalPendingLines,
				"intLadderVelocity" => $intLadderVelocity,
				"objUser" => $objUser,
				"intLatestLine" => $intLatestLine,
				"intPending" => $intPending,
				"intLinesAhead" => $intLinesAhead,
				"intLatestMissionLineFraction" => $intLatestMissionLineFraction,
				"intGoal" => @$arrUserCampaignProgress[$objUser->user_id]['goal']
			);
		}
		//print (msfloat() - $intStartTime) / 10000;
		//exit;
		ksort($arrResults);
		//dumper($arrResults,1,1);
		// Pass the results to the view
		$this->view->arrResults = $arrResults;
	}
}
?>