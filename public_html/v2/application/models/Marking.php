<?php
class Marking
{
	private $_db;
	private $_user_session_data;
	private $_tools;
	public $intMarkWeeks = 3; // The number of weeks allowed to mark ahead

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		$this->_tools = new ToolsModels();
   	}

	// Generic functions

	/*
	 * Generate a list all pending mission by stepping the scheduler forward
	 * through time until it has reached the current mission.
	 */
	public function pending_unmarked_missions($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MM-PUM101-SF6D8D";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MM-PUM102-76FG6F";
			exit;
		}

		$objScheduler = new Scheduler();
		$objMissions = new Missions();
		$objCampaigns = new Campaigns();

		// Load the mission
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objMission)
		{
			print "Sorry, there was an error: MM-PUM103-7SDFSS";
			exit;
		}

		// Load the latest entered schedule entered into the user_campaigns
		$arrSchedule = $objScheduler->load_book_schedule(array(
			"mission_id" => $objMission->mission_id,
			"user_id" => $arrParams["user_id"],
			//"start_epoch" => $intScheduleStartEpoch,

			"capture_end_date" => time() + (86400*7*($this->intMarkWeeks+intval(@$arrParams["extra_weeks"]))),
			"capture_start_medal" => @$arrParams["capture_start_medal"],
			"capture_end_medal" => @$arrParams["capture_end_medal"]
		));

		return $arrSchedule;
	}

	/*
	 * Mark the current campaign the designated number of lines forward from
	 * their current holding.
	 * Required: campaign_id, user_id, task_incrament
	 * Optional: institution_id
	 */
	public function mark_task_incrament($arrParams)
	{
		$query = new QueryGen();
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MM-MTI101-2T32T3";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MM-MTI102-45G5GG";
			exit;
		}
		if (!isset($arrParams["task_incrament"]))
		{
			print "Sorry, there was an error: MM-MTI103-7SDF6D";
			exit;
		}
		if (!isset($arrParams["institution_id"]) || !$arrParams["institution_id"])
		{
			$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		}

		$objMissions = new Missions();
		$objLadders = new Ladders();
		$objCampaigns = new Campaigns();
		$objGrades = new Grades();

		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $arrParams["campaign_id"]
		)));

		// Get the users ladder velocity
		$objCampaignEnrollment = current($objCampaigns->_user_campaigns_select(array(
			"user_id" => $arrParams["user_id"],
			"campaign_id" => $arrParams["campaign_id"],
			"status" => "Enrollment"
		)));
		if (!$objCampaignEnrollment)
		{
			print "Sorry, there was an error: ML-CULV103-9876FD";
			exit;
		}

		// Find the grade of a user
		$intGrade = intval(current(end($objGrades->classes_select_grades(array(
			"user_id" => $arrParams["user_id"],
			//"institution_id" => $arrParams["institution_id"]
		)))));
		$objGradeVelocity = current($objGrades->_velocity_grades_select(array(
			"grade_hierarchy" => $intGrade,
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objGradeVelocity)
		{
			print "Sorry, there was an error: ML-GLTV105-S87DDS";
			exit;
		}
		$objLaddersVelocity = current($objGrades->_velocity_ladders_select(array(
			"ladder" => $objCampaignEnrollment->ladder ? $objCampaignEnrollment->ladder-1 : $objCampaignEnrollment->ladder,
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objLaddersVelocity)
		{
			print "Sorry, there was an error: ML-GLTV106-SD76S8";
			var_dump($arrParams);exit;
		}
		$intLadderVelocity =  $objGradeVelocity->velocity * $objLaddersVelocity->velocity;

		// Select the latest entered task/mission
		$objLatestMission = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $objMission->mission_id,
			"user_id" => $arrParams["user_id"],
			"institution_id" => @$arrParams["institution_id"],
			"status" => "Completed",
			"_LIMIT" => 1
		)));
		$intLatestLine = $objLatestMission ? $objLatestMission->task_increment + 1 : $objCampaignEnrollment->line_offset;
		$intLatestMission = $objLatestMission ? $objLatestMission->mission_increment + 1 : 0;

		// Return an array of unmarked missions
		$arrPendingMissions = $this->pending_unmarked_missions(array(
			"campaign_id" => $arrParams["campaign_id"],
			"user_id" => $arrParams["user_id"]
		));
		// Find the total pending lines
		if (count($arrPendingMissions))
		{
			$arrFirstPendingMission = reset($arrPendingMissions);
			$intFirstPendingLine = reset($arrFirstPendingMission["tasks"]);
			$arrLastPendingMission = end($arrPendingMissions);
			$intLastPendingLine = isset($arrLastPendingMission["tasks"]) ? current($arrLastPendingMission["tasks"]) : 0;
			$intTotalPendingLines = $intLastPendingLine - $intFirstPendingLine;
		}
		else
		{
			$intTotalPendingLines = 0;
			$intFirstPendingLine = $intLatestLine;
		}
		$intMaxLines = $intTotalPendingLines + $intLatestLine;
		if ($arrParams["task_incrament"] < $intFirstPendingLine)
			return 0;
		// Don't allow marking past the max, set the value to the max if it is
		// greater than the max
		$intLinesOverMarked = 0;
		if ($arrParams["task_incrament"] > $intMaxLines)
		{
			$intLinesOverMarked = $arrParams["task_incrament"] - $intMaxLines;
			$arrParams["task_incrament"] = $intMaxLines;
		}

		// Don't allow marking less than the min
		//print "if ({$arrParams["task_incrament"]} <= $intFirstPendingLine) ";
		//print "1 <br>\n";

		// Loop through the pending missions and check if the marking is
		// required, if so add the tasks to the user_campaigns table
		//var_dump($arrPendingMissions);exit;
		$objCampaigns->_user_campaigns_update(array(
			"where" => array(
				"user_id" => $arrParams["user_id"],
				"institution_id" => $arrParams["institution_id"],
				"campaign_id" => $arrParams["campaign_id"],
				"mission_id" => $objMission->mission_id,
				"mission_increment" => $intLatestMission-1
			),
			"values" => array(
				"status" => "Completed"
			)
		));
		$this->add_paused_velocites(array(
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"],
			"campaign_id" => $arrParams["campaign_id"]
		));

		$objLatestUserCampaign = first($query->user_campaigns__select(array(
			"user_id" => $arrParams["user_id"],
			"mission_id" => $objMission->mission_id,
			'status' => array('Enrollment','In Progress','Completed'),
			"_ORDER" => "task_increment + 0 DESC",
			"_LIMIT" => 1
		)));
		if (!$objLatestUserCampaign) // campaign not began yet
		{
			$objLatestUserCampaign = current($objCampaigns->_user_campaigns_select(array(
				"campaign_id" => $arrParams["campaign_id"],
				"user_id" => $arrParams["user_id"],
				"status" => "Enrollment"
			)));
			$intLastTaskIncrament = $objLatestUserCampaign->line_offset;
		}
		else
			$intLastTaskIncrament = $objLatestUserCampaign->task_increment;
		$intTotalPendingMissions = count($arrPendingMissions)-$this->intMarkWeeks+1;
		//var_dump($intTotalPendingMissions);exit;
		if ($intTotalPendingMissions < 0)
			$intTotalPendingMissions = $intTotalPendingMissions-($intTotalPendingMissions*2);
		if (isset($arrPendingMissions[$intTotalPendingMissions+1]))
		{
			$arrLastMission = $arrPendingMissions[$intTotalPendingMissions+1];
			$intLastLine = floor(end($arrLastMission["tasks"]));
		}
		else
			$intLastLine = 0;
		$intTotalPendingMissions++;
		foreach ($arrPendingMissions as $intKey => $arrPendingMission)
		{
			if ($intTotalPendingMissions < $intKey)
				break;
			$intMissionIncrement = $intKey + $intLatestMission;
			// Loop through the lines of the mission
			if (0 == $intLastLine)
				break;
			foreach ($arrPendingMission["tasks"] as $intTaskIncrament)
			{
				// Tells where to stop marking
				if (
					floor($intTaskIncrament-1) >= $arrParams["task_incrament"]
					|| floor(end($arrLastPendingMission["tasks"])) < $arrParams["task_incrament"]
				)
					break 2;
				//print "end 1\n";
				$objUserCampaign = current($objCampaigns->_user_campaigns_select(array(
					"user_id" => $arrParams["user_id"],
					"institution_id" => $arrParams["institution_id"],
					"mission_id" => $objMission->mission_id,
					"task_increment" => $intTaskIncrament
				)));
				// Mark this item if it doesnt exist already
				if ($objUserCampaign)
					continue;

				// Break out of line has already been established in marking (in
				// other words: dont mark 1.25 if 1 was already marked)
				if (
					floor($intLastTaskIncrament) == floor($intTaskIncrament)
					&& floor($intTaskIncrament) == floor($arrParams["task_incrament"])
				) {
					break 2;
				}
				//print "end 2\n";

				$objCampaigns->_user_campaigns_insert(array(
					"user_id" => $arrParams["user_id"],
					"institution_id" => $arrParams["institution_id"],
					"campaign_id" => $arrParams["campaign_id"],
					"mission_id" => $objMission->mission_id,
					"mission_increment" => $intMissionIncrement,
					"task_increment" => $intTaskIncrament,
					"grade_hierarchy" => $intGrade,
					"grade_velocity" => $objGradeVelocity->velocity,
					"schedule_date" => $arrPendingMission["epoch"],
					"ladder" => $objCampaignEnrollment->ladder,
					"ladder_velocity" => $intLadderVelocity,
					"status" => $objGradeVelocity->velocity == 1 ? 'Completed' : "In Progress"
				));

				if ($intMissionIncrement > 0)
				{
					$objCampaigns->_user_campaigns_update(array(
						"where" => array(
							"user_id" => $arrParams["user_id"],
							"institution_id" => $arrParams["institution_id"],
							"campaign_id" => $arrParams["campaign_id"],
							"mission_id" => $objMission->mission_id,
							"mission_increment" => $intMissionIncrement-1
						),
						"values" => array(
							"status" => "Completed"
						)
					));
					$this->add_paused_velocites(array(
						"user_id" => $arrParams["user_id"],
						"institution_id" => $arrParams["institution_id"],
						"campaign_id" => $arrParams["campaign_id"],
					));
				}
				$intLastTaskIncrament = $intTaskIncrament;
				if (floor($intTaskIncrament) == $intLastLine)
					break 2;
			}
		}
		return $intLinesOverMarked;
	}

	public function add_paused_velocites($arrParams)
	{
		$query = new QueryGen();
		$objLatestCompleteMission = first($query->user_campaigns__select(array(
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"],
			"campaign_id" => $arrParams["campaign_id"],
			'status' => 'Completed',
			'_ORDER' => 'schedule_date+0 DESC',
			'_LIMIT' => 1
		)));
		if ($objLatestCompleteMission)
		{
			$intLatestCompletedSchedule = $objLatestCompleteMission->schedule_date;
			$arrPendingPausedResumed = array_stack('user_campaign_id', $query->user_campaigns__select(array(
				"user_id" => $arrParams["user_id"],
				"institution_id" => $arrParams["institution_id"],
				"campaign_id" => $arrParams["campaign_id"],
				'status' => array('Paused', 'Resumed'),
				'_LESSER' => array(
					'schedule_date' => $intLatestCompletedSchedule
				)
			)));
			foreach ($arrPendingPausedResumed as $intUserCampaign => $boolTrue)
			{
				$query->user_campaigns__update(array(
					'where' => array(
						'user_campaign_id' => $intUserCampaign
					),
					'values' => array(
						'ladder' => $objLatestCompleteMission->ladder,
						'ladder_velocity' => $objLatestCompleteMission->ladder_velocity
					)
				));
			}

		}
	}

	/*
	 * Get the latest line the user has been marked for.
	 * Gets the starting line of the latest completed mission.
	 * Required: mission_id, user_id
	 * Optional: institution_id
	 * Result: (int) number_of_lines
	 */
	public function latest_mission_line_hierarchy($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["mission_id"]))
		{
			print "Sorry, there was an error: MM-LMLH101-76DFGF";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MM-LMLH102-786FSD";
			exit;
		}
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();

		// Select the latest task entered within a confirmed mission
		$objLatestTask = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $arrParams["mission_id"],
			"user_id" => $arrParams["user_id"],
			"institution_id" => @$arrParams["institution_id"],
			"status" => "Completed",
			"_ORDER" => "mission_increment + 0 DESC, task_increment + 0 ASC",
			"_LIMIT" => 1
		)));

		$intTaskIncrement = 0;
		if ($objLatestTask) // campaign already in progress
			$intTaskIncrement = $objLatestTask->task_increment;
		else // campaign not began yet
		{
			// Find the campaign id if not provided
			if (!isset($arrParams["campaign_id"]))
			{
				$objMission = current($objMissions->_missions_select(array(
					"mission_id" => $arrParams["mission_id"]
				)));
				$arrParams["campaign_id"] = $objMission->campaign_id;
			}
			$objLatestTask = current($objCampaigns->_user_campaigns_select(array(
				"campaign_id" => $arrParams["campaign_id"],
				"user_id" => $arrParams["user_id"],
				"institution_id" => @$arrParams["institution_id"],
				"status" => "Enrollment"
			)));
			$intTaskIncrement = $objLatestTask->line_offset;
		}

		$intCurrentSum = $this->pending_lines_sum(array(
			"mission_id" => $arrParams["mission_id"],
			"institution_id" => @$arrParams["institution_id"],
			"user_id" => $arrParams["user_id"]
		));

		$intLatestLine = $intTaskIncrement + @$objLatestTask->ladder_velocity + $intCurrentSum;
		return $intLatestLine;
	}

	public function latest_line_hierarchy($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["mission_id"]))
		{
			print "Sorry, there was an error: MM-LMLH101-76DFGF";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MM-LMLH102-786FSD";
			exit;
		}
		$objCampaigns = new Campaigns();
		$objMissions = new Missions();

		// Select the latest task entered within a confirmed mission
		$objLatestTask = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $arrParams["mission_id"],
			"user_id" => $arrParams["user_id"],
			"institution_id" => @$arrParams["institution_id"],
			"_ORDER" => "task_increment + 0 DESC",
			"_LIMIT" => 1
		)));
		if (!$objLatestTask)
			return 0;
		if (isset($objLatestTask->task_increment))
			return $objLatestTask->task_increment;
		else
			return intval($objLatestTask->line_offset);
	}

	/*
	 * Find how many lines are done on the current pending mission
	 * Required: mission_id, user_id
	 * Optional: institution_id
	 */
	public function pending_lines_sum($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$_VERBOSE = 0;

		if (!isset($arrParams["mission_id"]))
		{
			print "Sorry, there was an error: MM-PLS101-SDF97D";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MM-PL102-S8DF7D";
			exit;
		}
		$objCampaigns = new Campaigns();

		$objLatestMission = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $arrParams["mission_id"],
			"user_id" => $arrParams["user_id"],
			"institution_id" => @$arrParams["institution_id"],
			"status" => "In Progress",
			"_LIMIT" => 1
		)));

		// Check if no lines have yet been inserted
		if (!$objLatestMission)
			return 0;

		$intLatestMission = $objLatestMission->mission_increment;

		// Select the latest entered task
		$arrLatestTasks = $objCampaigns->_user_campaigns_select(array(
			"mission_id" => $arrParams["mission_id"],
			"user_id" => $arrParams["user_id"],
			"institution_id" => @$arrParams["institution_id"],
			"mission_increment" => $intLatestMission,
			"_ORDER" => "task_increment + 0 ASC"
		));

		// Calculate
		$intCurrentSum = 0;
		$intTaskFirstIncrement = -1;
		foreach ($arrLatestTasks as $objLatestTask)
		{
			$intLadderVelocity = $objLatestTask->ladder_velocity;
			if ($intTaskFirstIncrement == -1)
			{
				$intTaskFirstIncrement = $objLatestTask->task_increment;
				$intReminder = $intTaskFirstIncrement - floor($intTaskFirstIncrement);
				if ($_VERBOSE)
					print "1 intReminder: $intReminder <br>\n";
				if ($intReminder)
				{
					$intCurrentSum += (1 - $intReminder < $intLadderVelocity ? $intLadderVelocity : 1 - $intReminder);
				}
				else
				{
					$intCurrentSum += $intLadderVelocity < 1 ? $intLadderVelocity : 1;
				}
				if ($_VERBOSE)
					print "1 intCurrentSum: $intCurrentSum <br>\n";
			}
			$intCurrent = $objLatestTask->task_increment - $intTaskFirstIncrement;
			$intCurrent = $intCurrent > 1 ? 1 : $intCurrent;
			if ($_VERBOSE)
				print "intCurrent: $intCurrent\n";
			if ($intCurrent)
				$intCurrentSum += $intCurrent;
			if ($_VERBOSE)
				print "2 intCurrentSum: $intCurrentSum <br>\n";
		}
		return $intCurrentSum;
	}

	public function user_campaign_status($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MM-UCS101-SDFH55";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MM-UCS102-HH5HH5";
			exit;
		}

		$arrPendingMissions = $this->pending_unmarked_missions(array(
			"user_id" => $arrParams["user_id"],
			"campaign_id" => $arrParams["campaign_id"]
		));
		array_pop($arrPendingMissions);
		for ($intItr=0; $intItr!=$this->intMarkWeeks+1; $intItr++)
		{
			array_pop($arrPendingMissions);
		}
		if (count($arrPendingMissions))
		{
			$arrFirstPendingMission = reset($arrPendingMissions);
			$intFirstPendingLine = reset($arrFirstPendingMission["tasks"]) + 1;
			$arrLastPendingMission = end($arrPendingMissions);
			$intLastPendingLine = end($arrLastPendingMission["tasks"]) + 1;
			$intTotalPendingLines = $intLastPendingLine - $intFirstPendingLine + 1;
		}
		else
			$intTotalPendingLines = 0;
		$this->view->intTotalPendingLines = $intTotalPendingLines;
		$this->view->arrPendingMissions = $arrPendingMissions;


	}

	public function armywide_line_goal($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MM-ALG101-S67DFS";
			exit;
		}
		$strSql = "
			SELECT
				MAX(task_increment) AS task_increment
			FROM
				user_campaigns
			WHERE
				institution_id = " . intval($arrParams["institution_id"]) . "
				AND mission_id IS NOT NULL
			GROUP BY user_id;
		";
		$arrResult = $this->_db->fetchAll($strSql);
		$intTotal = 0;
		foreach ($arrResult as $objTask)
		{
			$intTotal += floor($objTask->task_increment);
		}
		return $intTotal;
	}

	/*
	 * Required: user_id, campaign_id, institution_id
	 */
	public function armywide_progress_update($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MM-APU101-SD7FDD";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MM-APU102-SD7FDD";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MM-APU103-SD7FDD";
			exit;
		}
	}

	/*
	 * Get the total goal and line sum of a given institution, campaign or user
	 * Optional: institution_id, user_id, campaign_id
	 * Result: array(
		"lines" => X,
		"goals" => Y
	 )
	 */
	public function armywide_campaign_holding($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$objAutomation = new Automation();
		$objInstitutions = new Institutions();

		if (!isset($arrParams["institution_id"])) {
			$arrParams["_GREATER"] = array(
				"reg_expires" => time()
			);
			$arrInstitutions = array_hash("institution_id", $objInstitutions->_institutions_select($arrParams));
			if (!count($arrInstitutions))
				return array(
					'lines' => 0,
					'goals' => 0
				);
			$arrParams["institution_id"] = array_keys($arrInstitutions);

		}
		$arrParams["_SUM"] = "campaign_goal";
		$intCampaignGoal = $objAutomation->user_campaign_progress_sum($arrParams);
		$arrParams["_SUM"] = "current_line";
		$intCurrentLine = $objAutomation->user_campaign_progress_sum($arrParams);
		$arrResults = array(
			"lines" => $intCurrentLine,
			"goals" => $intCampaignGoal
		);
		return $arrResults;
	}

	private function user_lines_overdue($arrUsers)
	{
		$query = new QueryGen();
		$objLadders = new Ladders();
		$objMarking = new Marking();
		$objCampaigns = new Campaigns();
		$intCampaign = 1;
		$intMarkWeeks = 500;
		$arrResults = array();
		foreach ($arrUsers as $intUser => $objUser)
		{
			// Get the users ladder velocity
			$intLadderVelocity = $objLadders->campaign_user_ladder_velocity(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id
			));
			$intLatestLineFraction = $objMarking->latest_line_hierarchy(array(
				"mission_id" => 1,
				"user_id" => $objUser->user_id
			));
			$intLatestMissionLineFraction = $objMarking->latest_mission_line_hierarchy(array(
				"mission_id" => 1,
				"user_id" => $objUser->user_id
			));
			$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"extra_weeks" => $intMarkWeeks-2
			));
			array_pop($arrPendingMissions);
			$arrLinesAhead = $objCampaigns->_user_campaigns_select(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"schedule_date_min" => time()+100,
				"_ORDER" => "task_increment + 0 ASC"
			));

			$intTotalPendingMissions = count($arrPendingMissions)-$intMarkWeeks-1;

			// lines ahead
			if (
				count($arrLinesAhead) > 1
			) {
				$objFirstUserCampaign = reset($arrLinesAhead);
				$objLastUserCampaign = end($arrLinesAhead);
				$intLinesAhead = floor($objLastUserCampaign->task_increment) - $objFirstUserCampaign->task_increment;
			}
			else
				$intLinesAhead = 0;

			// Find the total pending lines
			if (count($arrPendingMissions) == 1)
				$intTotalPendingLines = $intLadderVelocity;
			else if (count($arrPendingMissions))
			{
				$intTotalPendingLines = 0;
				$arrFirstPendingMission = current($arrPendingMissions);
				$intFirstPendingLine = current($arrFirstPendingMission["tasks"]);
				$arrLastPendingMission = end($arrPendingMissions);
				$intLastMission = end($arrLastPendingMission["tasks"]);
				if (count($arrPendingMissions) > 2)
				{
					prev($arrPendingMissions);
					for ($intItr=0; $intItr!=$intMarkWeeks+1; $intItr++)
					{
						prev($arrPendingMissions);
					}
				}
				$arrLastPendingMission = current($arrPendingMissions);
				$intLastPendingLine = isset($arrLastPendingMission["tasks"]) ? current($arrLastPendingMission["tasks"]) : 0;
				$intLastPendingLineValue = $intLastPendingLine;
				$intLastPendingLineValue -= $intLatestLineFraction;
				if ($intLastPendingLineValue)
					$intTotalPendingLines += $intLastPendingLineValue;
			}
			else
				$intTotalPendingLines = 0;
			$intLinesOverDue = $intTotalPendingLines;
			$intLinesDue = $objMarking->pending_lines_sum(array(
				"mission_id" => 1,
				"institution_id" => $this->_user_session_data->institution_id,
				"user_id" => $objUser->user_id
			));
			$strLinesDue = "";

			if ($intPendingMissionsReal == 1)
			{
				$arrMission = current($arrPendingMissions);
				if ($intLinesOverDue >= 0)
					$intLinesOverDue += $intPendingMissionsReal * $arrMission["velocity"];

				if ($intLinesOverDue)
				{
					if ($intLinesOverDue < 0)
						$intLinesOverDue = $intPendingMissionsReal * $intLadderVelocity + ($intPendingMissionsReal * $intLadderVelocity + ceil($intLinesOverDue));
					if ($intLinesOverDue < 0)
					{
						$intLinesOverDue2 = $intLinesOverDue;
						$intLinesOverDue = $intLatestLineFraction + (!($intLinesOverDue < -1) ? $intLinesOverDue / 2 : $intLinesOverDue);
						if ($intLadderVelocity < 1)
							$intLinesOverDue += $intLinesOverDue2;
					}
					$strLinesDue = ( $intLadderVelocity-($intLinesDue + ($intLatestLineFraction - $intLatestMissionLineFraction))) . " lines due";
				}
				else
					$strLinesDue = "No lines due";
			}
			else if ($intPendingMissionsReal < 1)
			{
				if (!$intLinesAhead)
					$strLinesDue = "No lines due";
				else
					$strLinesDue = $intLinesAhead . " lines ahead";
			}
			else
			{
				$intLinesOverDue += $intLadderVelocity;
				$strLinesDue = floor($intLinesOverDue) . " lines overdue";
			}
			$arrResults[$intUser]['lines_due'] = $strLinesDue;
		}
		return $arrResults;
	}
	public function user_missions_overdue($arrUsers)
	{
		$query = new QueryGen();
		$objLadders = new Ladders();
		$objMarking = new Marking();
		$objCampaigns = new Campaigns();
		$objScheduler = new Scheduler();
		$intCampaign = 1;
		$intMarkWeeks = 500;
		$arrResults = array();
		// Load the parameters provided by the mission of the campaign
		$objSchedulingParams = current($objScheduler->_scheduling_params_select(array(
			"mission_id" => 1,
			"task_id" => 0
		)));
		foreach ($arrUsers as $intUser)
		{
			$intLadderVelocity = $objLadders->campaign_user_ladder_velocity(array(
				"user_id" => $intUser,
				"campaign_id" => 1
			));
			$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
				"user_id" => $intUser,
				"campaign_id" => 1,
				"extra_weeks" => -1
			));
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


			$arrLinesAhead = $query->user_campaigns__select(array(
				"user_id" => $intUser,
				"campaign_id" => $intCampaign,
				'_GREATER' => array(
					'schedule_date' => strtotime('-1 week')
				),
				"_ORDER" => "task_increment + 0 ASC",
				'_NOT' => array(
					'status' => array('Paused', 'Resumed', 'Enrollment')
				)
			));
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
			// status
			$strUserStatus = "";
			if ($intMissionsAhead > 0)
			{
				$strUserStatus = ($intMissionsAhead-1) . " " . ucfirst(FrequencyTextToSingular($objSchedulingParams->frequency)) . "(s) ahead";
			}
			else if ($intPendingMissionsReal < 2)
			{
				$strUserStatus = "Current";
			}
			else
			{
				$strUserStatus = ($intPendingMissionsReal - 1) . " " . ucfirst(FrequencyTextToSingular($objSchedulingParams->frequency)) . "(s) behind";
				$intPendingMissionsReal = -$intPendingMissionsReal+1;
			}
			$arrResults[$intUser]['user_status'] = $strUserStatus;
			$arrResults[$intUser]['missions'] = $intPendingMissionsReal;
		}
		return $arrResults;
	}

	public function user_missions_overdue2($arrUsers)
	{
		$query = new QueryGen();
		$objLadders = new Ladders();
		$objMarking = new Marking();
		$objCampaigns = new Campaigns();
		$objScheduler = new Scheduler();
		$intCampaign = 1;
		$intMarkWeeks = 500;
		$arrResults = array();
		// Load the parameters provided by the mission of the campaign
		$objSchedulingParams = current($objScheduler->_scheduling_params_select(array(
			"mission_id" => 1,
			"task_id" => 0
		)));
		foreach ($arrUsers as $intUser => $objUser)
		{
			$arrPendingMissions = $objMarking->pending_unmarked_missions(array(
				"user_id" => $objUser->user_id,
				"campaign_id" => $intCampaign,
				"extra_weeks" => $intMarkWeeks-2
			));
			array_pop($arrPendingMissions);
			$intMissions = 0;
			$intTotalPendingMissions = count($arrPendingMissions)-$intMarkWeeks-1;
			$intTotalInvert = ($intTotalPendingMissions - ($intTotalPendingMissions * 2));
			$intTotalPendingMissions = count($arrPendingMissions)-$intMarkWeeks-1;
			// status
			$strUserStatus = "";
			if ($intTotalPendingMissions == 1 || ($intTotalInvert==0 && $intTotalPendingMissions < 1))
			{
				$strUserStatus = "Current";
				$intMissions = 0;
			}
			else if ($intTotalPendingMissions < 1)
			{
				$strUserStatus = $intTotalInvert . " " . ucfirst(FrequencyTextToSingular($objSchedulingParams->frequency)) . "(s) ahead";
				$intMissions = $intTotalInvert;
			}
			else
			{
				$strUserStatus = ($intTotalPendingMissions - 1) . " " . ucfirst(FrequencyTextToSingular($objSchedulingParams->frequency)) . "(s) behind";
				$intMissions = -($intTotalPendingMissions - 1);
			}
			$arrResults[$intUser]['user_status'] = $strUserStatus;
			$arrResults[$intUser]['missions'] = $intMissions;
		}
		return $arrResults;
	}
}

?>