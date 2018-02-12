<?php
/*
 * Description:
 * This script servers as the core process iteration of the parameters provided
 * within the scheduler object. Only load this object as a child of Scheduler
 * because it should always remain as a dependent class to keep the logic separated.
 */
class SchedulerProc
{
	public $_VERBOSE = 0;
	public $_SCHEDULER; // Parent object
	public $_POSITION;
	public $_DATA;
	public $_PARAMS;
	public $_PROCESS_BUFFER;

	// Tools
	private $_tools;

	public function __construct(&$objScheduler)
	{
		// Set the parent scheduler object
		if (!isset($objScheduler->_IS_SCHEDULER))
		{
			print "Sorry, there was an error: MSP-C101-DF8FD9";
			exit;
		}
		$this->_SCHEDULER = $objScheduler;

		// Pull verbose from parent
		if (
			$objScheduler->_VERBOSE
			&& !$this->_VERBOSE
		) {
			$this->_VERBOSE = $objScheduler->_VERBOSE;
		}

		// Model tools
		$this->_tools = new ToolsModels();

		// Initalize
		$this->params();
	}

	// Load or inherit setting from params or object defults
	public function params($arrParams=0)
	{
		if (
			!isset($this->_PARAMS)
			|| !is_array($this->_PARAMS)
		) {
			$this->_PARAMS = array();
		}
		$this->_PARAMS["time"] = time();
		$this->_PARAMS["max_missions"] = 1000;
		if (is_array($arrParams))
		{
			if (isset($arrParams["time"]))
			{
				$this->_PARAMS["time"] = $arrParams["time"];
			}
			if (isset($arrParams["max_results"]))
			{
				$this->_PARAMS["max_results"] = $arrParams["max_results"];
			}
			if (isset($arrParams["max_missions"]))
			{
				$this->_PARAMS["max_missions"] = $arrParams["max_missions"];
			}
		}
	}

	public function export()
	{

	}

	/*
	 * Loop through the mission schedule and define tasks from a book where the
	 * number of tasks are defined by the (grade * ladder) velocity.
	 * Optional parameters: ladder, start_epoch
	 * Note: Provide the ladder parameter either as an optimization or if the user
	 * is not currently enrolled.
	 */
	public function process_mission_book3($arrParams)
	{
		if (!isset($arrParams["mission_id"]))
		{
			print "Sorry, there was an error: MSP-PMB3101-SDF343";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MSP-PMB3102-23EW3E";
			exit;
		}
		if (!isset($arrParams["ladder"]))
		{
			print "Sorry, there was an error: MSP-PMB3103-345ER3";
			exit;
		}
		if (isset($arrParams["start_epoch"]))
		{
			$this->_POSITION["time"] = $arrParams["start_epoch"];
		}
		else
		{
			$this->_POSITION["time"] = time();
		}

		$query = new QueryGen();
		$objLadders = new Ladders();
		$objMissions = new Missions();
		$objMarking = new Marking();
		$objCampaigns = new Campaigns();

		// Load the mission
		$objMission = current($objMissions->_missions_select(array(
			"mission_id" => $arrParams["mission_id"]
		)));
		if (!$objMission)
		{
			print "Sorry, there was an error: MSP-PMB3104-S97DFD";
			exit;
		}

		$intMaxIterations = $this->_SCHEDULER->_config["max_iterations"];
		if (isset($arrParams["max_iterations"]))
		{
			$intMaxIterations = $arrParams["max_iterations"];
		}

		// Load the task velocity

		$intTaskVelocity = $objLadders->grade_ladder_task_velocity(array(
			"user_id" => $arrParams["user_id"],
			"ladder" => $arrParams["ladder"],
			"institution_id" => $objMission->institution_id,
			"campaign_id" => $objMission->campaign_id
		));

		// Load the mission params
		$arrSchParams = (array) current($this->_SCHEDULER->_scheduling_params_select(array(
			"mission_id" => $arrParams["mission_id"],
			"task_id" => 0
		)));

		// Offset number of tasks (or lines) to start from
		if (!isset($arrParams["task_offset"]) || (!$arrParams["task_offset"] && $arrParams["task_offset"] !== 0))
		{
			$objLatestUserCampaignStart = current($objCampaigns->_user_campaigns_select(array(
				"user_id" => $arrParams["user_id"],
				"campaign_id" => $objMission->campaign_id,
				"mission_id" => $objMission->mission_id,
				"_ORDER" => "mission_increment + 0 DESC, task_increment + 0 ASC",
				"_LIMIT" => 1
			)));
			if ($objLatestUserCampaignStart && $objLatestUserCampaignStart->status != "Enrollment") // Campaign as already started
			{
				$arrParams["task_offset"] = $objLatestUserCampaignStart->task_increment;
				$this->_POSITION["time"] = $objLatestUserCampaignStart->schedule_date;
				if ($objLatestUserCampaignStart->ladder_velocity != $intTaskVelocity)
				{
					$arrParams["task_offset"] += $objLatestUserCampaignStart->ladder_velocity;
					$this->step_forward($arrSchParams["frequency"]);
				}
			}
			else
			{
				// Campaign has not started yet, check if we need to offset the lines
				$objUserCampaignEnrollment = current($objCampaigns->_user_campaigns_select(array(
					"user_id" => $arrParams["user_id"],
					"campaign_id" => $objMission->campaign_id,
					"status" => "Enrollment"
				)));
				$arrParams["task_offset"] = intval(@$objUserCampaignEnrollment->line_offset);
			}
		}

		$intIteration = 1;
		$intTaskIteration = $arrParams["task_offset"];
		$arrBuffer = array();
		$this->_POSITION["result_count"] = 0;
		$boolEndDateReached = 0;

		// Load all completed missions/tasks
		if ($arrParams["kiosk"])
		{
			$arrMissionsTasks = $query->user_campaigns__select(array(
				"user_id" => $arrParams["user_id"],
				"campaign_id" => $objMission->campaign_id,
				"status" => "Completed",
				"_ORDER" => "task_increment + 0 ASC"
			));
			foreach ($arrMissionsTasks as $objUserCampaign)
			{
				if ($boolEndDateReached)
					break;
				if (
					!isset($arrParams["capture_start_date"])
					|| $arrParams["capture_start_date"] <= $this->_POSITION["time"]
				) {
					if (
						isset($arrParams["capture_end_date"])
						&& $arrParams["capture_end_date"] < $this->_POSITION["time"]
					) {
						// Reached date end
						$boolEndDateReached = 1;
					}
					if (!isset($arrBuffer[$objUserCampaign->mission_increment]))
						$arrBuffer[$objUserCampaign->mission_increment] = array(
							"date" => date("F j, Y, g:i a", $objUserCampaign->schedule_date),
							"epoch" => $objUserCampaign->schedule_date,
							"velocity" => $objUserCampaign->ladder_velocity
						);
					$arrBuffer[$objUserCampaign->mission_increment]["tasks"][] = $objUserCampaign->task_increment;
					$intTaskIteration = $objUserCampaign->task_increment + 1;
				}
				//sort($arrBuffer[$objUserCampaign->mission_increment]["tasks"], SORT_NUMERIC);
			}
			if ($this->_VERBOSE)
			{
				var_dump($arrMissionsTasks);
				var_dump($arrBuffer);
				exit;
			}
			if (count($arrBuffer) && isset($arrBuffer[count($arrBuffer)-1]))
			{
				//sort($arrBuffer, SORT_NUMERIC);
				$this->_POSITION["time"] = $arrBuffer[count($arrBuffer)-1]["epoch"];
				$this->step_forward($arrSchParams["frequency"]);
			}
		}

		$arrPauseResume = $query->user_campaigns__select(array(
			"user_id" => $arrParams["user_id"],
			"campaign_id" => $objMission->campaign_id,
			"status" => array("Paused",'Resumed'),
			"_ORDER" => "schedule_date + 0 ASC"
		));
		$boolPaused = 0;
		while ($intIteration < $intMaxIterations)
		{
			if ($boolEndDateReached)
				break;

			// Calcualte the time in the current possition into various forms
			$this->_POSITION["year"] = date("Y", $this->_POSITION["time"]);
			$this->_POSITION["month"] = strtolower(date("M", $this->_POSITION["time"]));
			$this->_POSITION["day_of_year"] = date("z", $this->_POSITION["time"])+1;
			$this->_POSITION["week_of_month"] = $this->date_week_of_month($this->_POSITION["time"]);

			// Mission
			$intResult = $this->process($arrSchParams);
			$intIteration++;
			if ($intResult === 1)
			{
				continue;
			}
			else if (!$intResult)
			{
				break;
			}
			if (count($arrPauseResume))
			{
				$intItr = 0;
				foreach ($arrPauseResume as $objPauseResume)
				{
					if (
						!$boolPaused
						&& $objPauseResume->status == 'Paused'
						&& $objPauseResume->schedule_date <= $this->_POSITION["time"]
					) {
						if (
							!(
								isset($arrParams['future'])
								&& $arrParams['future']
								&& $intItr == count($arrPauseResume)-1
							)
						)
							$boolPaused = 1;
					} else if (
						$boolPaused
						&& $objPauseResume->status == 'Resumed'
						&& $objPauseResume->schedule_date <= $this->_POSITION["time"]
					) {
						$boolPaused = 0;
					}
					$intItr++;
				}
			}
			if (!$boolPaused)
			{

				// Generate tasks
				$arrTask = array();
				$intTaskVelocitySum = $intTaskVelocity; // 0.75

				while ($intTaskVelocitySum)
				{
					$arrTask[] = $intTaskIteration; // 0
					$intRemainder = 1 - ($intTaskIteration - floor($intTaskIteration)); // 1
					if (
						$intRemainder != 1
						&& $intTaskVelocity < $intRemainder
					) {
						$intRemainder = $intRemainder > $intTaskVelocitySum ? $intTaskVelocitySum : $intRemainder;
						$intTaskIteration += $intRemainder;
						$intTaskVelocitySum -= $intRemainder;
					}
					else
					{
						$intTaskIteration += $intTaskVelocitySum >= 1 ? 1 : $intTaskVelocitySum;
						$intTaskVelocitySum -= $intTaskVelocitySum >= 1 ? 1 : $intTaskVelocitySum;
					}
				}
			}


			if (
				!$boolPaused
				&& (
					!isset($arrParams["capture_start_date"])
					|| $arrParams["capture_start_date"] <= $this->_POSITION["time"]
				)
			) {
				if (
					isset($arrParams["capture_end_date"])
					&& $arrParams["capture_end_date"] < $this->_POSITION["time"]
				) {
					// Reached date end
					$boolEndDateReached = 1;
				}


				if (count($arrTask))
					$arrBuffer[] = array(
						//"date" => date("F j, Y, g:i a", round($this->_POSITION["time"])),
						"epoch" => round($this->_POSITION["time"]),
						"tasks" => $arrTask,
						"velocity" => $intTaskVelocity
					);
			}

			// Check if we have a reached a maximum number of results
			$this->_POSITION["result_count"]++;
			if (
				isset($this->_POSITION["max_results"])
				&& $this->_POSITION["result_count"] >= $this->_POSITION["max_results"]
			) {
				break;
			}

			$this->step_forward($arrSchParams["frequency"]);
		}
		/*print "<pre>";
		var_dump($arrBuffer);
		print "</pre>";exit;*/
		return $arrBuffer;
	}

	// Move forward in time
	private function step_forward($strFrequency)
	{
		if ($strFrequency == "Yearly")
		{
			$this->_POSITION["time"] = $this->iterate_year($this->_POSITION["time"]);
		}
		else if ($strFrequency == "Monthly")
		{
			$this->_POSITION["time"] = $this->iterate_month($this->_POSITION["time"]);
		}
		else if ($strFrequency == "Weekly")
		{
			$this->_POSITION["time"] = $this->iterate_week($this->_POSITION["time"]);
		}
		else
		{
			$this->_POSITION["time"] = $this->iterate_day($this->_POSITION["time"]);
		}
	}


	/*
	 * Book process iterator
	 * Loop through the mission schedule and define tasks from a book where the
	 * number of tasks are defined by the (grade * ladder) velocity.
	 */
	public function process_mission_book($arrParams=0)
	{
		$this->_PROCESS_BUFFER = array();
		$this->_POSITION["iteration"] = 1;
		$this->_POSITION["result_count"] = 0;
		$this->_POSITION["book_current_line"] = 0;
		$this->_POSITION = array_merge($this->_POSITION, $this->_PARAMS);

		$arrParams = $this->_SCHEDULER->_arrMultiSch["mission"]["mission"];
		$intUser = $arrParams["user_id"];
		$intCampaign = $arrParams["campaign_id"];

		if (!isset($this->_POSITION["time"]))
		{
			print "Sorry, there was an error: MSP-PMB101-SD67DS";
			exit;
		}
		$objGrades = new Grades();
		$objCampaigns = new Campaigns();
		$objBooks = new Books();

		// Find out the current grade of a user (temporarily only handling one grade at a time)
		$intGrade = current(current($objGrades->classes_select_grades(array(
			"user_id" => $intUser
		))));

		// Find the current velocity for the users grade under this campaign
		$objGrade = current($objGrades->_velocity_grades_select(array(
			"campaign_id" => $intCampaign,
			"grade_hierarchy" => $intGrade
		)));
		if (!$objGrade)
		{
			print "Sorry, there was an error: MSP-PMB101-SD7D8S";
			exit;
		}

		// The grade must have a higher velocity than zero
		$intGradeVelocity = $objGrade->velocity;
		if ($intGradeVelocity < 1)
		{
			// Grade velocity must be greater than zero
			return $this->_PROCESS_BUFFER;
		}

		// Find the current ladder of the user
		$objUserCampaign = current($objCampaigns->_user_campaigns_select(array(
			"campaign_id" => $intCampaign,
			"mission_id" => 0,
			"task_id" => 0
		)));

		if (!$objUserCampaign) // Not enrolled
		{
			//print "Not enrolled <br>\n";
			/*
			 * The user is currently not enrolled, and therefore has no current
			 * ladder for this campaign. So verify that there is a velocity with
			 * a value greater than 1 then check check if there are enough lines
			 * to complete at least one mission.
			 */
			$arrVelocities = $objGrades->_velocity_ladders_select(array(
				"campaign_id" => $intCampaign
			));

			// Find the smallest ladder with a velocity greater than zero
			$intSmallestLadder = $intSmallestVelocity = NULL;
			foreach ($arrVelocities as $objVelocity)
			{
				if (
					(
						$intSmallestVelocity == NULL
						|| $intSmallestVelocity > $objVelocity->velocity
					) && $objVelocity->velocity != 0
				) {
					$intSmallestVelocity = $objVelocity->velocity;
					$intSmallestLadder = $objVelocity->ladder;
				}
			}
			//print "intSmallestVelocity: $intSmallestVelocity <br>\n";
			//print "intSmallestLadder: $intSmallestLadder <br>\n";
			if (!$intSmallestVelocity)
			{
				return array(); // No result
			}

			$intBookCount = $objBooks->book_lines_select_count(array(
				"book_id" => $arrParams["book_id"]
			));
			//print "intBookCount: $intBookCount <br>\n";
			// Is there at the very least one missions to complete on the smallest ladder
			if ($intSmallestVelocity <= $intBookCount)
			{
				$arrResult = array();
				$arrResult[$intSmallestLadder] = "There was at least one task";
				return $arrResult;
			}
			else
			{
				return array();
			}
		} // Not enrolled clause complete

		$intPreviousTime = $this->_POSITION["time"];
		$arrTaskTimes = array();
		$arrTaskMissions = array();
		$intLadderHierarchy = $objUserCampaign->ladder;
		$objLadder = current($objGrades->_velocity_ladders_select(array(
			"campaign_id" => $intCampaign,
			"ladder" => $intLadderHierarchy
		)));

		$intLadderVelocity = $objLadder->velocity;

		// Defined above:
		// $intGradeVelocity
		// $this->_POSITION["book_current_line"]

		$intTaskVelocity = $intLadderVelocity * $intGradeVelocity;

		// Load the book lines into a hash
		$arrBookLines = $objBooks->_book_lines_select(array(
			"book_id" => $arrParams["book_id"]
		));

		/*
		 * The following iteration runs through time and processes the
		 * position data for a book schedule.
		 */
		while ($this->_POSITION["iteration"] < $this->_SCHEDULER->_config["max_iterations"])
		{
			$intPreviousTime = $this->_POSITION["time"];
			// Stop when the max missions have been reached

			// Calcualte the the time in the current possition into various forms
			$this->_POSITION["year"] = date("Y", $this->_POSITION["time"]);
			$this->_POSITION["month"] = strtolower(date("M", $this->_POSITION["time"]));
			$this->_POSITION["day_of_year"] = date("z", $this->_POSITION["time"])+1;
			$this->_POSITION["week_of_month"] = $this->date_week_of_month($this->_POSITION["time"]);

			// Mission
			$intResult = $this->process($this->_SCHEDULER->_arrMultiSch["mission"]);
			$this->_POSITION["iteration"]++;
			if ($intResult === 1)
			{
				continue;
			}
			else if (!$intResult)
			{
				break;
			}

			// Mission success, generate tasks now
			$arrTask = array();
			for($intItr=0;$intItr!=$intTaskVelocity;$intItr+=$intTaskVelocity)
			{
				if (!isset($arrBookLines[$this->_POSITION["book_current_line"]]))
				{
					// End of book
					// Set the current count to the max for a q&d break out
					$this->_POSITION["result_count"] = $this->_POSITION["max_results"];
					break;
				}
				$arrTask[] = $arrBookLines[$this->_POSITION["book_current_line"]];
				$this->_POSITION["book_current_line"]++;
			}
			$this->_PROCESS_BUFFER[] = array(
				"date" => date("F j, Y, g:i a", round($this->_POSITION["time"])),
				"epoch" => round($this->_POSITION["time"]),
				"book_lines" => $arrTask,

				// Very dirty but in a big rush
				"task" => array_merge($this->_SCHEDULER->_arrMultiSch["mission"], array("object" => (object) $this->_SCHEDULER->_arrMultiSch["mission"]))

			);

			// Check if we have a reached a maximum number of results
			$this->_POSITION["result_count"]++;
			if (
				isset($this->_POSITION["max_results"])
				&& $this->_POSITION["result_count"] >= $this->_POSITION["max_results"]
			) {
				break;
			}

			// Move forward in time
			if ($this->_SCHEDULER->_arrMultiSch["mission"]["frequency"] == "Monthly")
			{
				$this->_POSITION["time"] = $this->iterate_month($this->_POSITION["time"]);
			}
			else
			{
				$this->_POSITION["time"] = $this->iterate_day($this->_POSITION["time"]);
			}


		}
		return $this->_PROCESS_BUFFER;
	}

	/*
	 * Process iterator
	 *
	 * Process flags used throughout the various functions in this class:
	 * 0 = break;
	 * 1 = continue;
	 * 2 = success!
	 */
	public function process_mission()
	{
		if (!isset($this->_POSITION))
		{
			$this->_POSITION = $this->_PARAMS;
		}
		if (!isset($this->_POSITION["time"]))
		{
			print "Sorry, there was an error: MSP-PM101-34ER3E";
			exit;
		}
		$this->_PROCESS_BUFFER = array();
		$this->_POSITION["iteration"] = 1;
		$this->_POSITION["result_count"] = 0;
		//$this->_PARAMS["max_results"]
		$intPreviousTime = $this->_POSITION["time"];
		$arrTaskTimes = array();
		$arrTaskMissions = array();
		while ($this->_POSITION["iteration"] < $this->_SCHEDULER->_config["max_iterations"])
		{
			$intPreviousTime = $this->_POSITION["time"];
			// Stop when the max missions have been reached

			// Calcualte the the time in the current possition into various forms
			$this->_POSITION["year"] = date("Y", $this->_POSITION["time"]);
			$this->_POSITION["month"] = strtolower(date("M", $this->_POSITION["time"]));
			$this->_POSITION["day_of_year"] = date("z", $this->_POSITION["time"])+1;
			$this->_POSITION["week_of_month"] = $this->date_week_of_month($this->_POSITION["time"]);

			// Mission
			$intResult = $this->process($this->_SCHEDULER->_arrMultiSch["mission"]);
			$this->_POSITION["iteration"]++;
			if ($intResult === 1)
			{
				continue;
			}
			else if (!$intResult)
			{
				break;
			}
			// Mission success, check for tasks now
			$arrTasks = $this->_SCHEDULER->_arrMultiSch["task"];
			foreach ($arrTasks as $arrTaskSch)
			{
				$intTime = $this->_POSITION["time"]; // preserve the time
				$intResult = $this->process($arrTaskSch);
				if ($intResult === 2)
				{
					// Successfully found a sch'ed task
					$this->_PROCESS_BUFFER[] = array(
						"date" => date("F j, Y, g:i a", round($this->_POSITION["time"])),
						"epoch" => round($this->_POSITION["time"]),
						"task" => $arrTaskSch
					);
					$this->_POSITION["result_count"]++;

					// Check if we have a reached a maximum number of results
					if (
						isset($this->_PARAMS["max_results"])
						&& $this->_POSITION["result_count"] >= $this->_PARAMS["max_results"]
					) {
						break 2;
					}
				}
				$this->_POSITION["time"] = $intTime;
			}
			$this->_POSITION["time"] = $this->iterate_day($this->_POSITION["time"]);
		}
		return $this->_PROCESS_BUFFER;
	}

	/*
	 * This function will process one item of the current schedule by stringing
	 * together the various processing required.
	 * Process flags:
	 * 0 = break;
	 * 1 = continue;
	 * 2 = success!
	 */
	public function process($arrParams=0)
	{
		if (!$arrParams)
		{
			print "Sorry, there was an error: MSP-P101-FG987F";
			exit;
		}
		// Validate: Years (2010-2020)
		if (
			isset($arrParams["year"])
			&& is_array($arrParams["year"])
			&& count($arrParams["year"])
		) {
			$intResult = $this->process_year(array(
				"params" => $arrParams["year"]
			));
			// 2 == success
			if ($intResult === 1)
			{
				return 1;
			}
			else if (!$intResult)
			{
				return 0;
			}
		}
		// Validate: Months (Jan-Dec)
		if (
			isset($arrParams["months"])
			&& is_array($arrParams["months"])
			&& count($arrParams["months"])
		) {
			$intResult = $this->process_month(array(
				"params" => $arrParams["months"]
			));
			// 2 == success
			if ($intResult === 1)
			{
				return 1;
			}
			else if (!$intResult)
			{
				return 0;
			}
		}
		// Validate: Weeks of month (1-4)
		if (
			isset($arrParams["weeks_in_month"])
			&& is_array($arrParams["weeks_in_month"])
			&& count($arrParams["weeks_in_month"])
		) {
			$intResult = $this->process_weeks_in_month(array(
				"params" => $arrParams["weeks_in_month"]
			));
			// 2 == success
			if ($intResult === 1)
			{
				return 1;
			}
			else if (!$intResult)
			{
				return 0;
			}
		}
		// Validate: Weeks in year (1-52)
		if (
			isset($arrParams["weeks_in_year"])
			&& is_array($arrParams["weeks_in_year"])
			&& count($arrParams["weeks_in_year"])
		) {
			$intResult = $this->process_weeks_in_year(array(
				"params" => $arrParams["weeks_in_month"]
			));
			// 2 == success
			if ($intResult === 1)
			{
				return 1;
			}
			else if (!$intResult)
			{
				return 0;
			}
		}
		// Validate: Days in year (1-365)
		if (
			isset($arrParams["days_in_year"])
			&& is_array($arrParams["days_in_year"])
			&& count($arrParams["days_in_year"])
		) {
			$intResult = $this->process_days_in_year(array(
				"params" => $arrParams["days_in_year"]
			));
			// 2 == success
			if ($intResult === 1)
			{
				return 1;
			}
			else if (!$intResult)
			{
				return 0;
			}
		}
		return 2;
	}

	private function process_year($arrParams=0)
	{
		if ($this->_tools->array_greater_than($this->_POSITION["year"]-1, array_keys($arrParams["params"])))
		{
			if ($this->_VERBOSE >= 1)
				print "Year range limit reached. <br>\n";
			return 0;
		}
		// Is the mission in range?
		if (!isset($arrParams["params"][$this->_POSITION["year"]]))
		{
			// Nope, skip forward to the next year
			$this->_POSITION["time"] = $this->iterate_year($this->_POSITION["time"]);
			return 1;
		}
		return 2;
	}

	private function process_month($arrParams=0)
	{
		// Check if the current date is in range
		if (!isset($arrParams["params"][$this->_POSITION["month"]]))
		{
			$this->_POSITION["time"] = $this->iterate_month($this->_POSITION["time"]);
			return 1;
		}
		return 2;
	}

	public function process_weeks_in_month($arrParams=0)
	{
		// Skip if possible limit has been reached
		if ($this->_tools->array_greater_than($this->_POSITION["week_of_month"], array_keys($arrParams["params"])))
		{
			if ($this->_VERBOSE >= 2)
				print "Weeks of month range limit reached1. <br>\n";
			$this->_POSITION["time"] = $this->iterate_month($this->_POSITION["time"]);
			return 1;
		}
		// Check if the current date is in range
		if (!isset($arrParams["params"][$this->_POSITION["week_of_month"]]))
		{
			// Skip forward a week
			$this->_POSITION["time"] = $this->iterate_week($this->_POSITION["time"]);
			return 1;
		}
		return 2;
	}

	public function process_weeks_in_year($arrParams=0)
	{
		// Skip if possible limit has been reached
		if ($this->_tools->array_greater_than($strWeekofYear, array_keys($arrParams["params"])))
		{
			if ($this->_VERBOSE >= 2)
				print "Weeks in year range limit reached. <br>\n";
			$this->_POSITION["time"] = $this->iterate_year($this->_POSITION["time"]);
			return 1;
		}
		// Check if the current date is in range
		if (!isset($arrParams["params"][$strWeekofYear]))
		{
			// Skip forward a week
			$this->_POSITION["time"] = $this->iterate_day($this->_POSITION["time"], 7);
			return 1;
		}
		return 2;
	}

	public function process_days_in_year($arrParams=0)
	{
		// Skip if possible limit has been reached
		if ($this->_tools->array_greater_than($this->_POSITION["day_of_year"], array_keys($arrParams["params"])))
		{
			if ($this->_VERBOSE >= 2)
				print "Days in year range limit reached. <br>\n";
			$this->_POSITION["time"] = $this->iterate_year($this->_POSITION["time"]);
			return 1;
		}
		if (!isset($arrParams["params"][$this->_POSITION["day_of_year"]]))
		{
			$this->_POSITION["time"] = $this->iterate_day($this->_POSITION["time"]);
			return 1;
		}
		return 2;
	}

	/*
	 * Step the day forward from anytime timestamp
	 * Different from adding 86400 to current time because it will start the day at 12:00 am
	 * Adventually this function may be adapted to derive the begining time of each day from
	 * an institution defined value.
	 */
	public function iterate_day ($intTimestamp=0, $intNumberOfDays=1)
	{
		if (!$intTimestamp)
		{
			// If no time is provided use current time
			$intTimestamp = date("U");
		}
		// Set the time to the start of day
		return mktime(0,0,0,date("n", $intTimestamp),date("j", $intTimestamp)+$intNumberOfDays,date("Y", $intTimestamp));
	}

	public function iterate_week ($intTimestamp=0, $intNumberOfWeeks=1)
	{
		if (!$intTimestamp)
		{
			// If no time is provided use current time
			$intTimestamp = date("U");
		}
		// Set default day of week if not defined
		if (!isset($this->_config["week_start"]))
			$this->_config["week_start"] = "sun";
		// Loop through the process as many weeks needed to go forwards
		for ($intItrWeek=0; $intItrWeek != $intNumberOfWeeks; $intItrWeek++)
		{
			// Go forward 1 day
			$intTimestamp = mktime(0,0,0,date("n", $intTimestamp),date("j", $intTimestamp)+1,date("Y", $intTimestamp));
			// Then, loop through days until you reach the user defined begining day of the week
			while ($this->_config["week_start"] != strtolower(date("D", $intTimestamp)))
			{
				// Go forward 1 day
				$intTimestamp = mktime(0,0,0,date("n", $intTimestamp),date("j", $intTimestamp)+1,date("Y", $intTimestamp));
			}
		}
		// Set the time to the start of day
		return mktime(0,0,0,date("n", $intTimestamp),date("j", $intTimestamp),date("Y", $intTimestamp));
	}

	public function iterate_month ($intTimestamp=0, $intNumberOfMonths=1)
	{
		if (!$intTimestamp)
		{
			// If no time is provided use current time
			$intTimestamp = date("U");
		}

		$intTimestamp = mktime(0,0,0,date("n", $intTimestamp)+$intNumberOfMonths,date("j", $intTimestamp),date("Y", $intTimestamp));
		// Set the time to the start of day & first day of the month
		return mktime(0,0,0,date("n", $intTimestamp),1,date("Y", $intTimestamp));
	}

	public function iterate_year ($intTimestamp=0, $intNumberOfYears=1)
	{
		if (!$intTimestamp)
		{
			// If no time is provided use current time
			$intTimestamp = date("U");
		}
		// Set the time to the jan 1st of start of day of th next year
		return mktime(0,0,0,1,1,date("Y", $intTimestamp) + $intNumberOfYears);
	}

	/*
	 * User defined institution week start day
	 * defines when the month begins. The begining
	 * of the month is only on the start of the first week
	 * of the current month.
	 */
	private function date_week_of_month ($intTimeStamp)
	{
		$intMonth = 0;
		$intStartMonth = date("F", mktime(0, 0, 0, date("n", $intTimeStamp), 1, date("Y", $intTimeStamp)));
		for ($intDay = 1; $intDay != 31; $intDay++)
		{
			$intCurrent = mktime(0, 0, 0, date("n", $intTimeStamp), $intDay, date("Y", $intTimeStamp));
			// Check if the month changed and if so break out
			if ($intStartMonth != date("F", $intCurrent))
				return "Sorry, there was an error: MS-DWOM101-A65S4D";
			if (strtolower($this->_SCHEDULER->_config["week_start"]) == strtolower(date("D", $intCurrent)))
				$intMonth++;
			// Return the current week on current day
			if ($intCurrent == mktime(0, 0, 0, date("n", $intTimeStamp), date("j", $intTimeStamp), date("Y", $intTimeStamp)))
				return $intMonth;
		}
		return $intMonth;
		//return "Sorry, there was an error: MS-DWOM102-WERE7R";
	}
}