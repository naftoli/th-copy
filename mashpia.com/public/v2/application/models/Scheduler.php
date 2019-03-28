<?php
/*
 * Description:
 * This class handles all input, output and loading of parameters for scheduling.
 * It should be a used as an interface for any other Scheduler objects such as
 * the SchedulerProc class which will process any parameters provided by this
 * class within given conditions.
 */
class Scheduler
{
	// config variables

	public $_VERBOSE = 0;
	public $_IS_SCHEDULER = true;

	public $_config = array(
		"min_increments"				=> 60, // Seconds
		"max_iterations"				=> 2000,
		"max_years"						=> 20,
		"week_start"					=> "sun",
		"day_start"						=> array('9','00'),
		"day_end"						=> array('17','00')
	);

	// config end

	// Required User Details
	private $_user_specs = array(
		"user_id"						=> NULL,
		"age" 							=> NULL,
		"enrollment_date" 				=> NULL,
		"last_completed_mission_date" 	=> NULL,
		"current_mission"				=> NULL
	);

	private $_benchmarking = array(
		"start_time" 					=> NULL
	);

	// Scheduler processing pointer and data handlers
	private $_rendering_progress;
	private $_rendering_buffer;
	private $_rendered_mission; // Used to hold a mission in place while processing tasks.

	// Parameters
	public $_arrSchParams;
	public $_arrMultiSch;

	// Static date values
	private $_arrTimes = NULL;

	// Tools
	private $_db;
	private $_user_session_data;
	public $_tools;

	public function __construct()
	{
	   // Start the DB objects
	   $this->_db = Zend_Registry::get('db');
	   $this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

	   // Start the session object
	   $this->_user_session_data = new Zend_Session_Namespace('user_session_data');

	   // Model tools
	   $this->_tools = new ToolsModels();

	   // Iniy the proc pointer
	   $this->new_process();
	   $this->_benchmarking["start_time"] = $this->_tools->microtime_float();
	}

	public function _scheduling_params_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"scheduler_id"			=> @$arrParams["scheduler_id"],
			"mission_id"			=> @$arrParams["mission_id"],
			"task_id"				=> @$arrParams["task_id"],
			"years"					=> @$arrParams["years"],
			"weeks_in_year"			=> @$arrParams["weeks_in_year"],
			"days_in_year"			=> @$arrParams["days_in_year"],
			"months"				=> @$arrParams["months"],
			"weeks_in_month"		=> @$arrParams["weeks_in_month"],
			"days_in_month"			=> @$arrParams["days_in_month"],
			"days_of_week"			=> @$arrParams["days_of_week"],
			"frequency"				=> @$arrParams["frequency"],
			"start_time"			=> @$arrParams["start_time"],
			"expiration"			=> @$arrParams["expiration"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				scheduling_params
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (isset($Value))
			{
				if (!is_int($Value))
				{
					$Value = '"' . $Value . '"';
				}
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/*
	 * Create a new process of the scheduler.
	 */
	public function new_process ($arrParams=0)
	{
		$intProcPointer = date("U");
		if (is_array($arrParams))
		{
			if (isset($arrParams["proc_pointer"]))
			{
				$intProcPointer = $arrParams["proc_pointer"];
			}
		}
		$this->_rendering_progress = array(
			"proc_pointer" => $intProcPointer
		);
	}

	/*
	 * Load all available ladders for a given campaign.
	 * Important note: This function is currently only handling books.
	 */
	public function load_available_ladders2 ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (
			!isset($arrParams["user_id"])
			|| !$arrParams["user_id"]
		) {
			print "Sorry, there was an error: MS-LAL101-SD3F32";
			exit;
		}
		if (
			!isset($arrParams["institution_id"])
			|| !$arrParams["institution_id"]
		) {
			print "Sorry, there was an error: MS-LAL102-ASDG45";
			exit;
		}
		if (
			!isset($arrParams["campaign_id"])
			|| !$arrParams["campaign_id"]
		) {
			print "Sorry, there was an error: MS-LAL103-JKH543";
			exit;
		}
		$objLadders = new Ladders();
		$objGrades = new Grades();
		$objMissions = new Missions();
		$objBooks = new Books();
		//
		$arrClassGrades = array_values((array) current($objGrades->classes_select_grades(array(
			"user_id" => $arrParams["user_id"]
		))));
		if (count($arrClassGrades) < 2)
			return array();
		list($intGrade, $intGradeHierarchy) = $arrClassGrades;

		$objGradeVelocity = current($objGrades->_velocity_grades_select(array(
			"campaign_id" => $arrParams["campaign_id"],
			"grade_hierarchy" => $intGrade
		)));

		if (!$objGradeVelocity || !$objGradeVelocity->velocity)
		{
			// No velocities were set for the current grade
			return array();
		}

		// Collect all the ladders scaled to the users grade, institution and campaign
		$arrTaskScaleParams = array(
			"campaign_id" => $arrParams["campaign_id"],
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"],
			"grade" => $intGradeHierarchy
		);
		$arrTaskScales = $objLadders->tasks_scale_select_hierarchy($arrTaskScaleParams);

		// Collect the total number of tasks required per mission on each ladder
		// using the combined velocity of the grade and the ladder
		$arrVelocities = array();
		$arrTaskCounts = array(); // Holds the number of tasks remaining for a given mission

		foreach ($arrTaskScales as $objTaskScale)
		{
			$objLadderVelocity = current($objGrades->_velocity_ladders_select(array(
				"ladder" => $objTaskScale->ladder-1,
				"campaign_id" => $arrParams["campaign_id"]
			)));
			if (!$objLadderVelocity || !$objLadderVelocity->velocity)
			{
				continue;
			}
			if (!isset($arrTaskCounts[$objTaskScale->mission_id]))
			{
				$objMission = current($objMissions->_missions_select(array(
					"mission_id" => $objTaskScale->mission_id
				)));
				if (!isset($objMission->book_id))
				{
					print "Sorry, this feature is not implemented yet: MS-LALTWO101-DSFFGF";
					exit;
				}
				$intBookCount = $objBooks->book_lines_select_count(array(
					"book_id" => $objMission->book_id
				));
				$arrTaskCounts[$objTaskScale->mission_id] = $intBookCount;
			}
			$intTotalVelocity = $objLadderVelocity->velocity * $objGradeVelocity->velocity;
			// Verify there enough tasks for a single mission
			if ($intTotalVelocity <= $arrTaskCounts[$objTaskScale->mission_id])
			{
				$objTaskScale->velocity = $intTotalVelocity;
				$arrVelocities[$objTaskScale->ladder] = $objTaskScale;
			}
		}
		return $arrVelocities;
	}

	/*
	 * Get all available missions for a particular user and campaign that have
	 * avilable tasks.
	 */
	public function load_available_ladders ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (
			!isset($arrParams["user_id"])
			|| !$arrParams["user_id"]
		) {
			print "Sorry, there was an error: MS-LAL101-SD3F32";
			exit;
		}
		if (
			!isset($arrParams["institution_id"])
			|| !$arrParams["institution_id"]
		) {
			print "Sorry, there was an error: MS-LAL102-ASDG45";
			exit;
		}
		if (
			!isset($arrParams["campaign_id"])
			|| !$arrParams["campaign_id"]
		) {
			print "Sorry, there was an error: MS-LAL103-JKH543";
			exit;
		}

		// Quick and dirty way to load up some missions to see what tasks are sch'ed
		$arrMedals = $this->load_medals(array(
			"user_id" 			=> $arrParams["user_id"],
			"institution_id"	=> $arrParams["institution_id"],
			"campaign_id"		=> $arrParams["campaign_id"]
		));

		$arrTasksFound = array();
		foreach ($arrMedals as $arrItems)
		{
			foreach ($arrItems as $arrTimes)
			{
				foreach ($arrTimes as $arrMissions)
				{
					if (is_array($arrMissions))
					{
						foreach ($arrMissions as $arrTasks)
						{
							foreach ($arrTasks as $arrTask)
							{
								$arrTasksFound[$arrTask["task"]["object"]->task_id] = 1;
							}
						}
					}
				}
			}
		}
		$objTasks = new Tasks();
		$objLadders = new Ladders();
		$arrLadders = array();
		foreach (array_keys($arrTasksFound) as $intTask)
		{
			$objTaskScales = current($objLadders->_tasks_scale_select(array(
				"task_id" => $intTask
			)));
			$arrLadders[$objTaskScales->ladder] = 1;
		}
		$arrLadders = array_keys($arrLadders);
		return $arrLadders;
	}

	public function harvest_schedule_book_lines(&$arrSchedule)
	{
		$objBooks = new Books();
		$objMissions = new Missions();
		$arrLinesFound = array();
		foreach ($arrSchedule["missions"] as $arrMission)
		{
			foreach ($arrMission["tasks"] as $intTask)
			{
				$arrLinesFound[floor($intTask)] = 1;
			}
		}
		$arrLinesFound = array_keys($arrLinesFound);
		$objMission = first($objMissions->_missions_select(array(
			"campaign_id" => $arrSchedule["medal"]->campaign_id
		)));
		if (!$objMission)
		{
			print "Sorry, there was an error: MS-HSBL101-123SDF";
			exit;
		}
		if (!$objMission->book_id)
		{
			print "Sorry, there was an error: MS-HSBL102-87DD7S";
			exit;
		}
		$arrBookLines = $objBooks->_book_lines_select(array(
			"book_id" => $objMission->book_id,
			"line_hierarchy" => $arrLinesFound
		));
		$arrBookLinesHash = array();
		foreach ($arrBookLines as $objBookLine)
		{
			$arrBookLinesHash[$objBookLine->line_hierarchy] = $objBookLine;
		}
		return $arrBookLinesHash;
	}

	/*
	 * Load a book schedule into its respecting medels, missions, tasks
	 * Required: campaign_id, user_id
	 * Optional: ladder, capture_start_medal, capture_end_medal, load_missions, capture_end_mission, capture_end_epoch, capture_start_date
	 */
	public function load_book_medals ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MS-LBM101-SD897F";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MS-LBM102-FD6GF6";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objMedals = new Medals();
		$objBooks = new Books();

		$objCampaign = current($objCampaigns->_campaigns_select(array(
			"campaign_id" => $arrParams["campaign_id"]
		)));

		// Load the mission - Only intended for a campaign with only 1 mission
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objMission)
		{
			print "Sorry, there was an error: MS-LBM101-7DF6GF";
			exit;
		}

		// Load all medals for this campaign
		$arrMedals = $objMedals->_medals_select_hierarchy(array(
			"campaign_id" => $arrParams["campaign_id"],
			"institution_id" => $objCampaign->institution_id
		));

		// Load the last line of the book
		$objBookLast = end($objBooks->_book_lines_select(array(
			"book_id" => $objMission->book_id,
			"LIMIT" => 1,
			"ORDER" => "line_hierarchy+0 DESC"
		)));

		// Load the schedule for the whole mission
		$arrParams["mission_id"] = $objMission->mission_id;
		$arrSchedule = $this->load_book_schedule($arrParams);

		// The start mission
		$intMission = 1;

		// Loop throug all medals and build the results set
		$arrResult = array();
		foreach ($arrMedals as $objMedal)
		{
			// Loop through the medals per mission
			for ($intMedal = 0; $intMedal != $objMedal->medal_value; $intMedal++)
			{
				// Add the tasks to the mission
				$arrMission = array_shift($arrSchedule);
				$arrMission["mission_number"] = $intMission++;

				// Break out based on param specification
				if (
					(
						// if capture_end_mission has been set, stop the iterations once its been reached.
						isset($arrParams["capture_end_mission"])
						&& $arrParams["capture_end_mission"] < $arrMission["mission_number"]
					) || (
						// if capture_end_epoch has been set, stop the iterations once the time has been reached.
						isset($arrParams["capture_end_epoch"])
						&& $arrParams["capture_end_epoch"] < $arrMission["epoch"]
					)
					|| !isset($arrMission["tasks"])
				)
					break 2;

				$intLastTask = end($arrMission["tasks"]);
				if ($objBookLast->line_hierarchy <= $intLastTask)
					break 2;

				// Specifiy medal ranges for collection
				if (
					!(
						(
							isset($arrParams["capture_start_medal"])
							&& $objMedal->medal_hierarchy < $arrParams["capture_start_medal"]
						) || (
							isset($arrParams["capture_end_medal"])
							&& $objMedal->medal_hierarchy > $arrParams["capture_end_medal"]
						)
					)
				) {
					// Add the medal object
					if (!isset($arrResult[$objMedal->medal_hierarchy]["medal"]))
						$arrResult[$objMedal->medal_hierarchy]["medal"] = $objMedal;
					if (isset($arrParams["load_missions"]))
						$arrResult[$objMedal->medal_hierarchy]["missions"][] = $arrMission;

					// Count the missions per medal added
					if (!isset($arrResult[$objMedal->medal_hierarchy]["mission_count"]))
						$arrResult[$objMedal->medal_hierarchy]["mission_count"] = 0;
					$arrResult[$objMedal->medal_hierarchy]["mission_count"]++;

					// Count the tasks per mission added
					if (!isset($arrResult[$objMedal->medal_hierarchy]["task_count"]))
						$arrResult[$objMedal->medal_hierarchy]["task_count"] = 0;
					if (isset($arrMission["tasks"]) && is_array($arrMission["tasks"]))
						$arrResult[$objMedal->medal_hierarchy]["task_count"] += count($arrMission["tasks"]);
				}
			}
		}
		return $arrResult;
	}

	/*
	 * Load a schedule for a book. Book schedules load the tasks from the
	 * book_lines table and only require processing of the schedule.
	 * Required: mission_id, user_id
	 * Optional: capture_start_date, capture_end_date, task_offset, start_epoch
	 */
	public function load_book_schedule ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["mission_id"]))
		{
			print "Sorry, there was an error: MS-LBS102-SDFF3F";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MS-LBS103-DFGDG4";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();

		// Check if a ladder was provided, if not check enrollment for the user ladder
		if (!isset($arrParams["ladder"]) || $arrParams["ladder"] == "")
		{
			if (!isset($arrParams["campaign_id"]))
			{
				$objMission = current($objMissions->_missions_select(array(
					"mission_id" => $arrParams["mission_id"]
				)));
				if (!$objMission)
				{
					print "Sorry, there was an error: MS-LSB104-7DSD7F";
					exit;
				}
				$arrParams["campaign_id"] = $objMission->campaign_id;
			}
			// Find users ladder
			$objUserCampaign = current($objCampaigns->_user_campaigns_select(array(
				"campaign_id" => $arrParams["campaign_id"],
				"user_id" => $arrParams["user_id"],
				"status" => "Enrollment"
			)));
			if (!$objUserCampaign)
			{
				print "Sorry, there was an error: MS-LBS101-FD76GF";
				exit;
			}
			$arrParams["ladder"] = $objUserCampaign->ladder;
			if (!$objUserCampaign->schedule_date)
			{
				print "Sorry, there was an error: MS-LBS104-6SDF76";
				exit;
			}
			if (!isset($arrParams["start_epoch"]))
			{
				$arrParams["start_epoch"] = $objUserCampaign->schedule_date;
			}
		}
		$objSchedulerProc = new SchedulerProc($this);

		$arrMissionBooks = $objSchedulerProc->process_mission_book3(array(
			"mission_id" => $arrParams["mission_id"],
			"user_id" => $arrParams["user_id"],
			"ladder" => $arrParams["ladder"],

			// Optional
			"capture_start_date" => @$arrParams["capture_start_date"],
			"capture_end_date" => @$arrParams["capture_end_date"],
			"task_offset" => @$arrParams["task_offset"],
			"start_epoch" => @$arrParams["start_epoch"], // if not provided will use current date/time
			"kiosk" => @$arrParams["kiosk"],
			"future" => @$arrParams["future"]
		));

		return $arrMissionBooks;
	}

	/*
	 * Load a list of campagins that a user has available and has possible task to complete.
	 * Params: institution_id, user_id
	 */
	public function load_campaigns ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (
			!isset($arrParams["institution_id"])
			|| !$arrParams["institution_id"]
		) {
			print "Sorry, there was an error: MS-LC101-9DS9SA";
			exit;
		}
		if (
			!isset($arrParams["user_id"])
			|| !$arrParams["user_id"]
		) {
			print "Sorry, there was an error: MS-LC102-6D7D8S";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objLadders = new Ladders();
		$objMissions = new Missions();
		$objIncrementals = new Incrementals();

		// Get an array of missions that a user has available

		/*
		 * Process each mission and task under each campaign available.
		 */

		/*
		 * Temporarily disabled
		 $arrCampaignsLadders = $objLadders->task_scale_select_campaigns_ladders(array(
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"]
		));
		 */
		$arrCampaignsLadders = array();
		$arrCampaignResults = array();

		// Books
		if (!isset($arrParams["deny"]) || !in_array("Book", $arrParams["deny"]))
		{
			$arrCampaignsLaddersBooks = $objLadders->task_scale_select_campaigns_books(array(
				"user_id" => $arrParams["user_id"],
				"institution_id" => $arrParams["institution_id"]
			));

			if (isset($arrCampaignsLaddersBooks["campaigns"]))
			{
				// Merge the results from books
				foreach ($arrCampaignsLaddersBooks["campaigns"] as $intKey => $arrCampaignItems)
				{
					$arrCampaignsLadders["campaigns"][$intKey] = $arrCampaignItems;
				}

				// Loop through all the campaigns available for this user
				$arrCachedCampaigns = array();
				foreach ($arrCampaignsLadders["campaigns"] as $intCampaign => $arrCampaigns)
				{
					// Loop through all missions under each campaign
					$boolFoundATask = 0;
					foreach ($arrCampaigns["missions"] as $intMission => $arrMissions)
					{
						$objMission = current($objMissions->_missions_select(array(
							"installed_mission_id" => $intMission,
							"institution_id" => $arrParams["institution_id"],
							"mission_type" => "Book"
						)));
						if (!$objMission)
							continue;
						$arrResults = $this->load_mission(array(
							"mission_id" => $objMission->mission_id,
							"campaign_id" => $intCampaign,
							"user_id" => $arrParams["user_id"],
							"institution_id" => $arrParams["institution_id"],
							"start_time" => time(),
							"max_results" => 1
						));

						if (count($arrResults))
						{
							$boolFoundATask = 1;
							break;
						}
					}
					if ($boolFoundATask)
					{
						// Select the campaign object if it has not already been found (optimization)
						if (!isset($arrCachedCampaigns[$intCampaign]))
						{
							$arrCachedCampaigns[$intCampaign] = current($objCampaigns->_campaigns_select(array(
								"campaign_id" => $intCampaign
							)));
							if ($arrCachedCampaigns[$intCampaign])
							{
								$arrCachedCampaigns[$intCampaign]->mission_type = "Book";
							}
						}
						// Provide the campaign object to the array
						if ($arrCachedCampaigns[$intCampaign])
							$arrCampaignResults[$intCampaign] = $arrCachedCampaigns[$intCampaign];
					}
				}
			}
		}

		// Incremental
		if (!isset($arrParams["deny"]) || !in_array("Incremental", $arrParams["deny"]))
		{
			$arrCampaignsIncrementals = $objIncrementals->load_incrementals(array(
				"user_id" => $arrParams["user_id"],
				"institution_id" => $arrParams["institution_id"]
			));
			foreach ($arrCampaignsIncrementals as $objIncremental)
			{
				$objIncremental->mission_type = "Incremental";
				$arrCampaignResults[$objIncremental->campaign_id] = $objIncremental;
			}
		}

		return $arrCampaignResults;
	}

	/*
	 * Load as many missions, tasks as required by incramenting the processing.
	 * Params: campaign_id, max_missions, max_year
	 */
	public function load_campaign ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MS-LC101-DSF342";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MS-LC102-FGH543";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MS-LC103-HGF432";
			exit;
		}

		$objLadders = new Ladders();

		$arrMissionLadders = $objLadders->task_scale_select_campaigns_ladders(array(
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"],
			"campaign_id" => $arrParams["campaign_id"]
		));

		$arrMissionBuffer = array();
		$intTime = time();

		foreach ($arrMissionLadders["missions"] as $intMission => $arrTaskLadders)
		{
			$Result = $this->load_mission(array(
				"mission_id" => $intMission,
				"institution_id" => $arrParams["institution_id"],
				"user_id" => $arrParams["user_id"],
				"start_time" => $intTime
				//"max_results" => 1
			));
			var_dump($Result);
			exit;
		}
	}
	/*
	 * Load the medals, missions, tasks for a given campaign.
	 */
	public function load_medals ($arrParams=0)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MS-LM101-56GTF3";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MS-LM102-76S6S6";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MS-LM103-5DS5S6";
			exit;
		}
		$objMedals = new Medals();
		$objLadders = new Ladders();

		$arrMissionLadders = $objLadders->task_scale_select_campaigns_ladders(array(
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"],
			"campaign_id" => $arrParams["campaign_id"]
		));
		$arrMissionLaddersBooks = $objLadders->task_scale_select_campaigns_books(array(
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"],
			"campaign_id" => $arrParams["campaign_id"]
		));
		// Merge the results from books
		foreach ($arrMissionLaddersBooks["missions"] as $intKey => $arrCampaignItems)
		{
			$arrMissionLadders["missions"][$intKey] = $arrCampaignItems;
		}
		// Process each mission under this campaign to find the order the missions fall into the campaign
		$arrMissionBuffer = array();
		$intTime = time();
		foreach ($arrMissionLadders["missions"] as $intMission => $arrTaskLadders)
		{
			if ($this->_VERBOSE >= 3)
				print "Loading mission: " . $intMission . " <br>\n";
			$Result = $this->load_mission(array(
				"institution_id" => $arrParams["institution_id"],
				"user_id" => $arrParams["user_id"],
				"mission_id" => $intMission,
				"start_time" => $intTime
				//"max_results" => 1
			));
			if ($this->_VERBOSE >= 3)
				print "Found in mission: " . count($Result) . " <br>\n";
			if (
				isset($Result)
				&& is_array($Result)
				&& count($Result)
			) {
				$intLastItrTime = 0;
				foreach ($Result as $arrData)
				{
					// Contruct the scheduled tasks into their respective missions
					if ($arrData["task"]["frequency"] == "Yearly")
					{
						$intMissionTime = mktime(0,0,0,1,1,date("Y", $arrData["epoch"]));
					}
					else if ($arrData["task"]["frequency"] == "Monthly")
					{
						$intMissionTime = mktime(0,0,0,date("n", $arrData["epoch"]),1,date("Y", $arrData["epoch"]));
					}
					else if ($arrData["task"]["frequency"] == "Weekly")
					{
						$intItr=0;
						$intMissionTime = mktime(0,0,0,date("n", $arrData["epoch"]),date("j", $arrData["epoch"])+1,date("Y", $arrData["epoch"]));
						while (date("D", $intMissionTime) != "Sun")
						{
							$intItr++;
							$intMissionTime = mktime(0,0,0,date("n", $arrData["epoch"]),date("j", $arrData["epoch"])-$intItr,date("Y", $arrData["epoch"]));
						}
					}
					else if ($arrData["task"]["frequency"] == "Daily")
					{
						$intMissionTime = mktime(0,0,0,date("n", $arrData["epoch"]),date("j", $arrData["epoch"])+1,date("Y", $arrData["epoch"]));
					}
					if (!isset($intMissionTime) || !$intMissionTime)
					{
						print "Sorry, there was an error: MS-LM102-S97DFS";
						exit;
					}
					if (!isset($arrMissionBuffer[$intMissionTime][$arrData["task"]["object"]->mission_id]))
					{
						$arrMissionBuffer[$intMissionTime][$arrData["task"]["object"]->mission_id][$arrData["task"]["object"]->task_id] = array();
					}
					if ($arrData["epoch"] - $intLastItrTime < 86401)
					{
						$intCurrentKey = count($arrMissionBuffer[$intMissionTime][$arrData["task"]["object"]->mission_id][$arrData["task"]["object"]->task_id])-1;
						$arrMissionBuffer[$intMissionTime][$arrData["task"]["object"]->mission_id][$arrData["task"]["object"]->task_id][$intCurrentKey]["end_epoch"] = $arrData["epoch"];
						$arrMissionBuffer[$intMissionTime][$arrData["task"]["object"]->mission_id][$arrData["task"]["object"]->task_id][$intCurrentKey]["end_date"] = date("F j, Y, g:i a", $arrData["epoch"]);
					}
					else
					{
						$arrMissionBuffer[$intMissionTime][$arrData["task"]["object"]->mission_id][$arrData["task"]["object"]->task_id][] = $arrData;
					}
					$intLastItrTime = $arrData["epoch"];
				}
			}
		}

		// That all the missions where constructed, now is time to merge them into medals
		$arrMedalsParams = array(
			"campaign_id" => $arrParams["campaign_id"],
			"institution_id" => $arrParams["institution_id"]
		);
		/*
		 Wishful thinking...
		if (isset($arrParams["medal_hierarchy"]))
			$arrMedalsParams["medal_hierarchy"] = $arrParams["medal_hierarchy"];
		*/
		$arrMedals = $objMedals->_medals_select_hierarchy($arrMedalsParams);
		$arrMedalsBuffer = array();
		// Loop though the medals
		$intMissionsFound = 0;
		foreach ($arrMedals as $objMedal)
		{
			// Loop through the times
			foreach ($arrMissionBuffer as $intTime => $arrMissions)
			{
				// Loop through the missions
				foreach ($arrMissions as $intMission => $arrTasks)
				{
					//print "intMission: " . $intMission . " <br>\n";
					$arrMedalsBuffer[$objMedal->medal_hierarchy][$intTime][$intMission] = $arrTasks;
					$arrMedalsBuffer[$objMedal->medal_hierarchy]["object"] = $objMedal;
					unset($arrMissionBuffer[$intTime][$intMission]);
					$intMissionsFound++;
					if ($intMissionsFound >= $objMedal->medal_value)
					{
						//print "Max reached <br>\n";
						// Medal max missions reached, go to the next medal
						$intMissionsFound = 0;
						break 2;
					}
				}
			}
		}
		return $arrMedalsBuffer;
	}

	/*
	 * Load the schedules of the mission and all the tasks under a mission
	 * Params: mission_id, start_time, max_results
	 */
	public function load_mission ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (
			!isset($arrParams["mission_id"])
			|| !$arrParams["mission_id"]
		) {
			print "Sorry, there was an error: MS-LM103-T43T4T";
			exit;
		}

		$this->clear_params();

		$objMissions = new Missions();

		$objMission = current($objMissions->_missions_select(array(
			"mission_id" => $arrParams["mission_id"]
		)));
		if (!$objMission)
		{
			print "Sorry, there was an error: MS-LM101-S76FD6";
			exit;
		}
		if ($objMission->book_id)
		{
			$arrParams["book_id"] = $objMission->book_id;
			$arrParams["campaign_id"] = $objMission->campaign_id;
			return $this->init_mission_book_processing($arrParams);
		}
		else
		{
			return $this->init_mission_processing($arrParams);
		}
	}

	/*
	 * Uses the velocities provided by the mission to dynamically create the
	 * tasks under a the missions schedule.
	 */
	public function init_mission_book_processing($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$arrMissionParams = (array) current($this->_scheduling_params_select(array(
			"mission_id" => $arrParams["mission_id"],
			"task_id" => 0
		)));
		if (!isset($arrMissionParams["scheduler_id"]))
		{
			// No parameters where found for the mission
			return 0;
		}
		$arrMissionParams["mission"] = $arrParams; // Important flag
		$this->params($arrMissionParams);

		// Mission / Book Processing

		$objSchedulerProc = new SchedulerProc($this);
		$arrProcParams = array();
		$arrProcParams["time"] = date("U", mktime(0,0,0,date("j", time()),date("n", time()),date("Y", time())));
		if (isset($arrParams["start_time"]))
			$arrProcParams["time"] = $arrParams["start_time"];
		$arrProcParams["max_results"] = $this->_config["max_iterations"];
		if (isset($arrParams["max_results"]))
			$arrProcParams["max_results"] = $arrParams["max_results"];
		if (isset($arrParams["max_missions"]))
			$arrProcParams["max_missions"] = $arrParams["max_missions"];

		$objSchedulerProc->params($arrProcParams);
		$Result = $objSchedulerProc->process_mission_book(array(
			"mission_id" => $arrParams["mission_id"]
		));

		if (isset($arrParams["calendar_format"]))
		{
			// Format the ranges into the calendar ajax format
			$Result = $this->calendar_format($Result);
		}
		if (isset($arrParams["json_encode"]))
		{
			$Result = json_encode($Result);
		}
		return $Result;
	}

	/*
	 * Combines the schedules of all tasks under a mission into the schedule
	 * of the parent mission schedule.
	 */
	public function init_mission_processing($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$objTasks = new Tasks();
		$objLadders = new Ladders();
		// Load the mission parameters
		$arrMissionParams = (array) current($this->_scheduling_params_select(array(
			"mission_id" => $arrParams["mission_id"],
			"task_id" => 0
		)));
		if (!isset($arrMissionParams["scheduler_id"]))
		{
			// No parameters where found for the mission
			return 0;
		}
		$arrMissionParams["mission"] = $arrParams; // Important flag
		$this->params($arrMissionParams);

		/*
		 * This fallowing if statment was an attempt to handle a none user based
		 * schedule.
		 */
		if (
			isset($arrParams["user_id"])
			&& $arrParams["user_id"]
		) {
			$arrTaskLadders = $objLadders->task_scale_select_campaigns_ladders(array(
				"user_id" => $arrParams["user_id"],
				"institution_id" => $arrParams["institution_id"],
				"mission_id" => $arrParams["mission_id"]
			));
		}
		else
		{
			$arrTaskLadders = $objLadders->task_scale_select_ladders(array(
				"mission_id" => $arrParams["mission_id"]
			));
		}


		// Get an array of tasks that a user has available


		/*
		 * Select all the possible task records under this mission for the
		 * purpose of passing them along with the params for processing.
		 */

		$arrTasks = $objTasks->_tasks_select(array(
			"mission_id" => $arrParams["mission_id"]
		));
		$arrTasksHash = array();
		foreach ($arrTasks as $objTask)
		{
			$arrTasksHash[$objTask->task_id] = $objTask;
		}

		foreach ($arrTaskLadders["tasks"] as $intTaskId => $objLadder)
		{
			$arrTaskParams = (array) current($this->_scheduling_params_select(array(
				"mission_id" => $arrParams["mission_id"],
				"task_id" => $intTaskId
			)));
			// Only add parameters if they are available
			if (isset($arrTaskParams["scheduler_id"]))
			{
				$arrTaskParams["task"] = $arrTasksHash[$intTaskId]; // Important flag
				$this->params($arrTaskParams);
			}
		}
		// Mission / Task Processing
		$objSchedulerProc = new SchedulerProc($this);
		$arrProcParams = array();
		$arrProcParams["time"] = date("U", mktime(0,0,0,date("n", time()),date("j", time()),date("Y", time())));
		if (isset($arrParams["start_time"]))
			$arrProcParams["time"] = $arrParams["start_time"];
		if (isset($arrParams["max_results"]))
			$arrProcParams["max_results"] = $arrParams["max_results"];
		if (isset($arrParams["max_missions"]))
			$arrProcParams["max_missions"] = $arrParams["max_missions"];

		$objSchedulerProc->params($arrProcParams);
		$Result = $objSchedulerProc->process_mission(array(
			"mission_id" => $arrParams["mission_id"]
		));

		if (isset($arrParams["calendar_format"]))
		{
			// Format the ranges into the calendar ajax format
			$Result = $this->calendar_format($Result);
		}
		if (isset($arrParams["json_encode"]))
		{
			$Result = json_encode($Result);
		}
		return $Result;
	}

	// Retreive the all data rendered in the buffer
	public function export ($arrParams = 0)
	{
		$arrData = array();
		if (isset($arrParams["data"]))
		{
			$arrData = $arrParams["data"];
		}
		else
		{
			$arrData = end($this->_rendering_buffer);
		}

		if (
			isset($arrParams["format"])
			&& $arrParams["format"] == "calendar"
		) {
			// Optimize the ranges into start and end points
			$arrData = $this->range_format($arrData);
			// Format the ranges into the calendar ajax format
			$arrData = $this->calendar_format($arrData);
		}

		if (
			isset($arrParams["encoding"])
			&& $arrParams["encoding"] == "json"
		) {
			$arrData = json_encode($arrData);
		}

		return $arrData;
	}

	// Format the raw data of a schedule to the array format required for the jquery calendar
	public function calendar_format($arrData)
	{
		$arrResult = array();
		$intSchId = 1;
		foreach ($arrData as $intKey => $arrItem)
		{
			$arrItem["epoch"] = mktime($this->_config["day_start"][0],$this->_config["day_start"][1],0,date("n", $arrItem["epoch"]),date("j", $arrItem["epoch"]),date("Y", $arrItem["epoch"]));
			$arrResult[] = array(
				'id' => $intSchId++,
				// $arrItem["epoch"] . " - " . date("F j, Y, g:i a", $arrItem["epoch"]) . " - " .
				'title' => $arrItem["task"]["object"]->task_name,
				'start' => $arrItem["epoch"]
			);
			/*
			if (
				isset($arrItem["end_epoch"])
				&& $arrItem["epoch"] != $arrItem["end_epoch"]
			) {
				$arrResult[] = array(
					'id' => $intSchId++,
					'title' => "Schedule " . $intKey . " ended @ " . date("F j, Y, g:i a", $arrItem["end_epoch"]) . " started @ " . date("F j, Y, g:i a", $arrItem["epoch"]),
					'start' => $arrItem["end_epoch"],
					'end' => $arrItem["epoch"] // Date from or true
				);
			}
			*/
		}
		return $arrResult;
	}

	/*
	 * Loop through the dates removing inbetween progression (1,2,3,4 to 1,3,4)
	 * Add end date to output...
	 */
	public function range_format($arrData)
	{
		$intMission = 1; // Change to dynamic
		$arrResult = array();
		$intCurrentTimestamp = $intLastTimestamp = 0;
		if (
			isset($arrData)
			&& is_array($arrData)
			&& count($arrData)
		) {
			foreach ($arrData as $arrItem)
			{
				// Some logic that adds then end based on the begining of the next item
				$intCurrentTimestamp = mktime($this->_config["day_start"][0],$this->_config["day_start"][1],0,date("n", $arrItem["epoch"]),date("j", $arrItem["epoch"]),date("Y", $arrItem["epoch"]));
				if (
					isset($intLastTimestamp)
					&& date("j", $intCurrentTimestamp) == date("j", $intLastTimestamp)
					&& date("n", $intCurrentTimestamp) == date("n", $intLastTimestamp)
					&& date("Y", $intCurrentTimestamp) == date("Y", $intLastTimestamp)
				) {
					$intLastTimestamp = mktime($this->_config["day_start"][0],$this->_config["day_start"][1],0,date("n", $arrItem["epoch"]),date("j", $arrItem["epoch"])+1,date("Y", $arrItem["epoch"]));
					$intEndDate = mktime($this->_config["day_end"][0],$this->_config["day_end"][1],59,date("n", $arrItem["epoch"]),date("j", $arrItem["epoch"]),date("Y", $arrItem["epoch"]));
					$arrResult[count($arrResult)-1]["end_date"] = date("F j, Y, g:i a", $intEndDate);
					$arrResult[count($arrResult)-1]["end_epoch"] = $intEndDate;
					continue;
				}
				// Adjust the start time to the config settings
				$arrItem["epoch"] = mktime($this->_config["day_start"][0],$this->_config["day_start"][1],00,date("n", $arrItem["epoch"]),date("j", $arrItem["epoch"]),date("Y", $arrItem["epoch"]));

				// New entry to the results
				$arrResult[] = $arrItem;

				// This is incase an end date was not added close the gap
				if (
					count($arrResult)
					&& isset($arrResult[count($arrResult)-2])
					&& !isset($arrResult[count($arrResult)-2]["end_date"])
				) {
					$intDate = $arrResult[count($arrResult)-2]["epoch"];
					$intEndDate = date("U", mktime($this->_config["day_end"][0],$this->_config["day_end"][1],59,date("n", $intDate),date("j", $intDate),date("Y", $intDate)));
					$arrResult[count($arrResult)-2]["end_date"] = date("F j, Y, g:i a", $intEndDate);
					$arrResult[count($arrResult)-2]["end_epoch"] = $intEndDate;
				}
				$intLastTimestamp = mktime($this->_config["day_start"][0],$this->_config["day_start"][1],0,date("n", $arrItem["epoch"]),date("j", $arrItem["epoch"])+1,date("Y", $arrItem["epoch"]));
			}
		}
		return $arrResult;
	}

	/*
	 * calendar_wrapper function is different from the process function in the sence
	 * that it will load the parameters from the mission stored on the database.
	 * Senario Exceptions:
	 * 1. If no mission parameters are stored this will act like a normal process
	 *    request.
	 * 2. If parameters are supplied with the params function then it will not supply
	 *    the parameters from the database for the task which it would normally do
	 *    otherwise.
	 */
	public function calendar_wrapper($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Identify a task id
		if (isset($this->_config["task_id"]))
			$intTask = $this->_config["task_id"];
		if (isset($arrParams["task_id"]))
			$intTask = $arrParams["task_id"];
		if (!isset($intTask) || !$intTask)
		{
			print "Sorry, there was an error: MS-PW101-8S7DFS";
			exit;
		}
		/*
		 * If parameters have been provided then load the schedule from them rather than from
		 * from the database.
		 */
		if (
			is_array($this->_arrSchParams)
			&& count($this->_arrSchParams)
		) {
			// Process the task parameters provided
			$arrPossition = $this->_rendering_progress; // save the position
			$this->process($arrParams);
			$arrTaskResult = $this->export(array(
				"format" => "calendar"
			));
			$this->_rendering_progress = $arrPossition; // reload the position
		}
		/*
		 * Retreive mission parameters and load them into a schedule instance. If no parameters are
		 * stored on the db then process the tasks normally without any wrapping.
		 */
		$objTasks = new Tasks();
		$objTask = current($objTasks->_tasks_select(array(
			"task_id" => $intTask
		)));
		if (!$objTask)
		{
			print "Sorry, there was an error: MS-PW102-DFG8DA";
			exit;
		}
		$objMission = current($objMissions->_missions_select(array(
			"mission_id" => $objTask->mission_id
		)));
		$intMission = $objMission->mission_id;
		/*
		 * Assuming the parameters have been loaded for the task and processed, now it's time to load
		 * and process the mission schedule.
		 */
		$boolParamSuccess = $this->params($this->_scheduling_params_select(array(
			"mission_id" => $intMission,
			"task_id" => 0
		)));
		$this->process($arrParams);
		$arrMissionResult = $this->export(array(
			"format" => "calendar"
		));
		// Validate that a mission result was generated
		if (
			!is_array($arrMissionResult)
			&& !is_array($arrTaskResult)
		) {
			print "Sorry, there was an error: MS-PW103-8S7DFD";
			exit;
		}
		// Wrap the tasks into the missions
		$arrStart = 0;
		$arrData = array();
		// If item fall outside the range the key is refactored counting on succesfully in range schedules.
		$intNewKey = 0;
		foreach ($arrMissionResult as $arrMissionEnd)
		{
			if (!isset($arrMissionEnd["end"]))
			{
				$arrMissionStart = $arrMissionEnd;
				continue;
			}
			/*
			 * Loop through the tasks and only include the tasks that are in range with the mission.
			 * The current difinition of a task in range is:
			 * Partly or completely within the range of mission schedule. The situation where a task is
			 * partly outside the mission the part of the task that is ouside the mission are cropped.
			 */
			foreach ($arrTaskResult as $arrTaskEnd)
			{
				if (!isset($arrTaskEnd["end"]))
				{
					$arrTaskStart = $arrTaskEnd;
					continue;
				}
				$boolInRange = 0;
				if (
					// Mission overlaps task from start to end
					$arrTaskStart["start"] >= $arrMissionStart["start"]
					&& $arrTaskEnd["start"] <= $arrMissionEnd["start"]
				) {
					if ($this->_VERBOSE)
						$arrTaskStart["range_exception"] = 1;
					// This is a normal event and no alteration is required
					$boolInRange = 1;
				} elseif (
					// Task overlaps mission from start to end
					$arrTaskStart["start"] < $arrMissionStart["start"]
					&& $arrTaskEnd["start"] > $arrMissionEnd["start"]
				) {
					if ($this->_VERBOSE)
						$arrTaskStart["range_exception"] = 2;
					$arrTaskStart["start"] = $arrMissionStart["start"];
					$arrTaskEnd["start"] = $arrMissionEnd["start"];
					$boolInRange = 1;
				} elseif (
					// Start is out of range but the end is within range of mission
					$arrTaskStart["start"] < $arrMissionStart["start"]
					&& $arrTaskEnd["start"] >= $arrMissionStart["start"]
					&& $arrTaskEnd["start"] <= $arrMissionEnd["start"]
				) {
					if ($this->_VERBOSE)
						$arrTaskStart["range_exception"] = 3;
					$arrTaskStart["start"] = $arrMissionStart["start"];
					$boolInRange = 1;
				} elseif (
					// End is out of range but the start is within range of mission
					$arrTaskEnd["start"] > $arrMissionEnd["start"]
					&& $arrTaskStart["start"] >= $arrMissionStart["start"]
					&& $arrTaskStart["start"] <= $arrMissionEnd["start"]
				) {
					if ($this->_VERBOSE)
						$arrTaskStart["range_exception"] = 4;
					$arrTaskEnd["start"] = $arrMissionEnd["start"];
					$boolInRange = 1;
				}
				if ($boolInRange)
				{
					if (
						isset($arrParams["refactor_date"])
						&& $arrParams["refactor_date"] == "true"
					) {
						$arrTaskStart = $this->calendar_refactor_date($arrTaskStart, $intNewKey);
						$arrTaskEnd = $this->calendar_refactor_date($arrTaskEnd, $intNewKey);
					}
					array_push($arrData, $arrTaskStart, $arrTaskEnd);
					$intNewKey++;
				}
			}
		}
		if (
			isset($arrParams["encoding"])
			&& $arrParams["encoding"] == "json"
		) {
			$arrData = json_encode($arrData);
		}
		return $arrData;
	}

	/*
	 * In the calendar the epoch is change on elements of a calendar formated array so this function
	 * is being used to make it easy to sync up anything in the element the way it should be.
	 */
	private function calendar_refactor_date($arrElement, $intKey)
	{
		if (!isset($arrElement["start"]))
		{
			print "Sorry, there was an error: MS-CRD101-DS89F7";
			exit;
		}
		if (!isset($arrElement["end"]))
		{
			$arrElement["title"] = "Schedule " . $intKey . " started @ " . date("F j, Y, g:i a", $arrElement["start"]);
		}
		else
		{
			$arrElement["title"] = "Schedule " . $intKey . " ended @ " . date("F j, Y, g:i a", $arrElement["start"]) . " started @ " . date("F j, Y, g:i a", $arrElement["end"]);
		}
		return $arrElement;
	}

	// Intention of the future of this function will reset the object without the need to instantiate the class
	public function clear_buffer()
	{
		$this->_rendering_buffer = NULL;
	}

	// Handles the timing in ms of the processes
	public function benchmarking($arrParams=0)
	{
		if (is_array($arrParams))
		{
			if (
				isset($arrParams["calculate"])
				&& $arrParams["calculate"] == "sum"
			) {
				$intStart = $this->_benchmarking["start_time"];
				$intSum = 0;
				foreach ($this->_benchmarking as $intVal)
				{
					if ($intStart != $intVal)
					{
						$intSum += $intVal - $intStart;
					}
				}
				return $intSum;
			}
		}
		return $this->_benchmarking;
	}

	// Resets the scheduling parameters current missions and tasks
	public function clear_params ()
	{
		$this->_arrMultiSch = array();
		$this->_arrSchParams = array();
	}

	/*
	 * Define the parameters for scheduling for the missions and tasks
	 *
	 * 	years = 2010,2100
	 * 	weeks_in_year = 1,52
	 * 	days_in_year = 1,365
	 * 	months = Jan,Dec
	 * 	weeks_in_month = 1,4
	 * 	days_in_month = 1,31
	 * 	days_of_week = Sun,Sat
	 * 	hours_in_day = 0,23
	 * 	minutes_in_hour = 0,59
	 * 	expiration = 0-100... (minutes)
	 * 	frequency = Yearly, Monthly, Weekly, Daily
	 *
	 */
	public function params ($arrParams = 0)
	{
		// Validate that some parameters where set
		if (!$arrParams)
		{
			print "Sorry, there was an error: MS-P101-7SDFS7";
			exit;
		}
		// Allow array from fetchAll
		if (
			isset($arrParams[0])
			&& is_object($arrParams[0])
		) {
			$arrParams = current($arrParams);
		}
		// Allow object params
		if (is_object($arrParams))
		{
			$arrParams = (array) $arrParams;
		}
		// Validate that some parameters where set
		if (!is_array($arrParams))
		{
			print "Sorry, there was an error: MS-P102-KHJ324";
			exit;
		}

		// Process params
		$arrValues = array();

		if ( // Years (2010-2020)
			isset($arrParams["years"])
			&& $arrParams["years"]
		) {
			$arrData = array();
			foreach (preg_split("/ *, */", $arrParams["years"]) as $Value)
			{
				$Value = intval($Value);
				if (
					$Value
					&& $Value >= date("Y")
					&& $Value < date("Y") + 100
				)
					$arrData[$Value] = 1;
			}
			$arrValues["years"] = $arrData;
		}
		if ( // Weeks in year (1-52)
			isset($arrParams["weeks_in_year"])
			&& $arrParams["weeks_in_year"]
		) {
			$arrData = array();
			foreach (preg_split("/ *, */", $arrParams["weeks_in_year"]) as $Value)
			{
				$Value = intval($Value);
				if (
					$Value >= 1
					&& $Value <= 52
				)
					$arrData[$Value] = 1;
			}
			$arrValues["weeks_in_year"] = $arrData;
		}
		if ( // Days in year (1-365)
			isset($arrParams["days_in_year"])
			&& $arrParams["days_in_year"]
		) {
			$arrData = array();
			foreach (preg_split("/ *, */", $arrParams["days_in_year"]) as $Value)
			{
				$Value = intval($Value);
				if (
					$Value >= 1
					&& $Value <= 365
				)
					$arrData[$Value] = 1;
			}
			$arrValues["days_in_year"] = $arrData;
		}
		if ( // Months (Jan-Dec)
			isset($arrParams["months"])
			&& $arrParams["months"]
		) {
			$arrPossible = array("jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec");
			$arrData = array();
			foreach (preg_split("/ *, */", $arrParams["months"]) as $Value)
			{
				$Value = strtolower(strval($Value));
				if (in_array($Value, $arrPossible))
					$arrData[$Value] = 1;
			}
			$arrValues["months"] = $arrData;
		}
		if ( // Weeks in month (1-4)
			isset($arrParams["weeks_in_month"])
			&& $arrParams["weeks_in_month"]
		) {
			$arrData = array();
			foreach (preg_split("/ *, */", $arrParams["weeks_in_month"]) as $Value)
			{
				$Value = intval($Value);
				if (
					$Value >= 1
					&& $Value <= 4
				)
					$arrData[$Value] = 1;
			}
			$arrValues["weeks_in_month"] = $arrData;
		}
		if ( // Days in month (1-31)
			isset($arrParams["days_in_month"])
			&& $arrParams["days_in_month"]
		) {
			$arrData = array();
			foreach (preg_split("/ *, */", $arrParams["days_in_month"]) as $Value)
			{
				$Value = intval($Value);
				if (
					$Value >= 1
					&& $Value <= 31
				)
					$arrData[$Value] = 1;
			}
			$arrValues["days_in_month"] = $arrData;
		}
		if ( // Days of week (Sun-Sat)
			isset($arrParams["days_of_week"])
			&& $arrParams["days_of_week"]
		) {
			$arrPossible = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
			$arrData = array();
			foreach (preg_split("/ *, */", $arrParams["days_of_week"]) as $Value)
			{
				$Value = strtolower(strval($Value));
				if (in_array($Value, $arrPossible))
					$arrData[$Value] = 1;
			}
			$arrValues["days_of_week"] = $arrData;
		}
		if ( // Days of week (Sun-Sat)
			isset($arrParams["frequency"])
			&& $arrParams["frequency"]
		) {
			$arrPossible = array("Yearly", "Monthly", "Weekly", "Daily");
			if (in_array($arrParams["frequency"], $arrPossible))
				$arrValues["frequency"] = $arrParams["frequency"];
		}
		if (
			isset($arrParams["start_time"])
			&& preg_match("/^([0-9]+):([0-9]+)$/", $arrParams["start_time"], $arrMatched)
		) {
			$this->_config["day_start"] = array($arrMatched[1], $arrMatched[2]);
		}
		$this->_config["expiration"] = intval(@$arrParams["expiration"]);
		$this->_config["mission_id"] = intval(@$arrParams["mission_id"]);
		$this->_config["task_id"] = intval(@$arrParams["task_id"]);
		if (!count($arrParams))
		{
			print "There where missing parameters: MS-PARAMS101-23WEF23";
			exit;
		}
		$arrParams["expiration"] = intval(@$arrParams["expiration"]);

		if (count($arrValues))
		{
			if (isset($arrParams["mission"]))
			{
				$arrValues = array_merge($arrValues, $arrParams);
				$this->_arrMultiSch["mission"] = $arrValues;
				$this->_arrSchParams = $arrValues;
			}
			else if (isset($arrParams["task"]))
			{
				$arrValues["object"] = $arrParams["task"];
				$this->_arrMultiSch["task"][] = $arrValues;
			}
			else
			{
				$this->_arrSchParams = $arrValues;
			}
			return 1;
		}
		else
		{
			return 0;
		}
	}

	// Insert the params to the scheduling_params database table
	public function insert_params ()
	{
		if (
			!is_array($this->_arrSchParams)
			&& !count($this->_arrSchParams)
		) {
			print "Sorry, there was an error: MS-SP101-34DF3S";
			exit;
		}

		if (isset($this->_config["task_id"]) && $this->_config["task_id"])
		{
			print "task_id=" . $this->_config["task_id"] . " <br>\n";
			$this->_db->delete("scheduling_params", "task_id=" . $this->_config["task_id"]);
		}
		else if (isset($this->_config["mission_id"]) && $this->_config["mission_id"])
		{
			print "mission_id=" . $this->_config["mission_id"] . "<br>\n";
			$this->_db->delete("scheduling_params", "mission_id=" . $this->_config["mission_id"]);
		}
		$arrInsert = array();
		foreach ($this->_arrSchParams as $intKey => $Values)
		{
			if (is_array($Values))
			{
				$arrInsert[$intKey] = join(",", array_keys($Values));
			}
			else
			{
				$arrInsert[$intKey] = $Values;
			}
		}
		$arrInsert["task_id"] = $this->_config["task_id"];
		$arrInsert["mission_id"] = $this->_config["mission_id"];
		$arrInsert["expiration"] = $this->_config["expiration"];
		$arrInsert["created"] = date("Y-m-d H:i:S");
		$arrInsert["created_by"] = $this->_user_session_data->user_id;
		$intResult = $this->_db->insert("scheduling_params", $arrInsert);
		if ($intResult)
		{
			$intAI = $this->_db->lastInsertId();
			return $intAI;
		}
		return 0;
	}
}