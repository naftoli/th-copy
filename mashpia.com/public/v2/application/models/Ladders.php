<?php
class Ladders
{
	private $_db;
	private $_user_session_data;
	private $_tools;

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
	public function _tasks_scale_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"tasks_scale_id"	 => @$arrParams["tasks_scale_id"],
			"task_id"			 => @$arrParams["task_id"],
			"grade"			 	 => @$arrParams["grade"],
			"ladder"			 => @$arrParams["created_by"],
			"mission_id"		 => @$arrParams["mission_id"],
			"campaign_id"		 => @$arrParams["campaign_id"],
			"institution_id"	 => @$arrParams["institution_id"],
			"is_required"		 => @$arrParams["is_required"],
			"velocity"			 => @$arrParams["velocity"],
			"comment"			 => @$arrParams["comment"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				tasks_scale
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					is_int($Value)
					|| $Value
				)
			) {
				if (!is_int($Value))
				{
					$Value = '"' . $Value . '"';
				}
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}

		$strSql .= "
			ORDER BY
				grade+0, ladder+0 ASC";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}
	// Generic functions end

	/*
	 * Make a selection on the tasks_scale table but search the host_id and
	 * network_id with the institution_id therefor querying the entire hierarchy.
	 */
	public function tasks_scale_select_hierarchy ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"tasks_scale_id"	 => @$arrParams["tasks_scale_id"],
			"task_id"			 => @$arrParams["task_id"],
			"grade"			 	 => @$arrParams["grade"],
			"ladder"			 => @$arrParams["created_by"],
			"mission_id"		 => @$arrParams["mission_id"],
			"campaign_id"		 => @$arrParams["campaign_id"],
			"is_required"		 => @$arrParams["is_required"],
			"comment"			 => @$arrParams["comment"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);

		// Find the parent institutions from the current one
		$objInstitutions = new Institutions();
		$objInstitution = current($objInstitutions->_institutions_select(array(
			"institution_id" => $arrParams["institution_id"]
		)));
		$arrInstitution = array();
		if ($objInstitution->host_id)
			$arrInstitution[] = $objInstitution->host_id;
		if ($objInstitution->network_id)
			$arrInstitution[] = $objInstitution->network_id;
		if ($objInstitution->institution_id)
			$arrInstitution[] = $objInstitution->institution_id;

		$strSql = "
			SELECT
				*
			FROM
				tasks_scale
			WHERE
				institution_id IN (" . join(",", $arrInstitution) . ")";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (is_array($Value))
			{
				$arrValues = array();
				foreach ($Value as $SubValue)
				{
					if (
						is_int($SubValue)
						|| $SubValue
					) {
						if (!is_int($SubValue))
						{
							$SubValue = '"' . $SubValue . '"';
						}
						$arrValues[] = $SubValue;
					}
				}
				if (count($arrValues))
				{
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
			}
			else if (
				isset($Value)
				&& (
					is_int($Value)
					|| $Value
				)
			) {
				if (!is_int($Value))
				{
					$Value = '"' . $Value . '"';
				}
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}

		$strSql .= "
			ORDER BY
				grade+0, ladder+0 ASC";
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function tasks_scale_delete($arrParams)
	{
		// Clean
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Clean Int's
		foreach (array("mission_id", "task_id", "task_scale_id") as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrParams[$strKey] = intval($arrParams[$strKey]);
		}

		// Contruct query
		$arrSql = array();
		foreach (array("mission_id", "task_id", "task_scale_id") as $strKey)
		{
			if (isset($arrParams[$strKey]))
			{
				if (is_int($arrParams[$strKey]))
					$arrSql[] = $strKey . "=" . $arrParams[$strKey];
				else
					$arrSql[] = $strKey . "=\"" . $arrParams[$strKey] . "\"";
			}
		}

		// Execute
		if (count($arrSql))
		{
			$intAffected = $this->_db->delete("tasks_scale", join(" AND ", $arrSql));
		}
		else
		{
			print "Sorry, there was an error: MLad-TSD101-45SDFD";
			exit;
		}

		return $intAffected;
	}

	public function tasks_scale_insert($arrParams)
	{
		$intDate = date("Y-m-d H:i:S");

		// Clean
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Validate
		$intMission = intval($arrParams["mission_id"]);
		if (!$intMission)
		{
			print "Sorry, there was an error: MLAD-TSI103-SSFGD4";
			exit;
		}

		// Insert
		if (
			isset($arrParams["grades_ladders"])
			&& is_array($arrParams["grades_ladders"])
			&& count($arrParams["grades_ladders"])
		) {
			// Insert
			$arrInsert = array();
			$arrSuccess = $arrFail = array();
			foreach ($arrParams["grades_ladders"] as $arrItem)
			{
				$arrInsert = array(
					"task_id" => 0,
					"grade" => $arrItem["grade"],
					"ladder" => $arrItem["ladder"],
					"comment" => $arrItem["comment"],
					"task_id" => $arrParams["task_id"],
					"mission_id" => $arrParams["mission_id"],
					"campaign_id" => $arrParams["campaign_id"],
					"institution_id" => $arrParams["institution_id"],
					"is_required" => $arrParams["is_required"],
					"created" => $intDate,
					"created_by" => $this->_user_session_data->user_id
				);
				$boolResult = $this->_db->insert("tasks_scale", $arrInsert);
				if ($boolResult)
					$arrSuccess[] = $this->_db->lastInsertId();
				else
					$arrFail[] = $arrParams["mission_id"];
			}
			if (count($arrFail))
			{
				print "Sorry, there was an error: MLAD-TCI101-ASDSSD<br>\n";
				print "Extra: " . join(",", $arrFail) . "-" . join(",", $arrSuccess) . "\n";
				exit;
			}
			if (count($arrSuccess))
			{
				// Clean up task loose ends
				if (
					!isset($arrParams["task_id"])
					|| (
						isset($arrParams["task_id"])
						&& !$arrParams["task_id"]
					)
				) {
					$strSql = "
						SELECT
							*
						FROM
							tasks_scale
						WHERE
							task_id = 0
							AND mission_id = " . $arrParams["mission_id"];
					$arrResult = $this->_db->fetchAll($strSql);
					$arrSql2 = array();
					foreach ($arrResult as $objMissionTasks)
					{
						$arrSql2[] = "'" . $objMissionTasks->grade . "_" . $objMissionTasks->ladder . "'";
					}
					if (count($arrSql2))
					{
						$strSql = "
							mission_id = " . $arrParams["mission_id"] . "
							AND task_id != 0
							AND concat(grade, '_', ladder) NOT IN (" . join(",", $arrSql2) . ")
						";
						$this->_db->delete("tasks_scale", $strSql);
					}
				}

				return $arrSuccess;
			}
		}
	}

	public function tasks_scale_select($arrParams)
	{
		// Clean
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Build query
		$strSql = "";

		// Int's
		foreach (array("mission_id", "task_id") as $strKey)
		{
			if (isset($arrParams[$strKey]))
			{
				$strSql .= "
					AND $strKey=" . intval($arrParams[$strKey]);
			}
		}

		// Execute
		if ($strSql != "")
		{
			$strSql = "
				SELECT
					*
				FROM
					tasks_scale
				WHERE
					1" . $strSql;
			//print $strSql;
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
	}

	/*
	 * Remove tasks from task_scale when they fall out of range
	 */
	public function tasks_delete_crop($intCampagin,$intLadder)
	{
		if (
			!isset($intCampagin)
			|| !isset($intLadder)
		) {
			print "Sorry, there was an error: MLad-TDC101-SDF324";
			exit;
		}
		$intCampagin = intval($intCampagin);
		$intLadder = intval($intLadder);
		if (
			!$intCampagin
			|| !$intLadder
		) {
			print "Sorry, there was an error: MLad-TDC102-23R2EE";
			exit;
		}
		$strSql = "
			campaign_id = $intCampagin
			AND ladder > $intLadder
		";
		$this->_db->delete("tasks_scale", $strSql);
	}

	/*
	 * Generate an array of all campaigns, missions and tasks that are available
	 * based on whats available in the task_scale table with the value of the
	 * corresponding scale object as the values relative to a user.
	 */
	public function task_scale_select_campaigns_ladders($arrParams)
	{
		// Clean
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-TSSCL101-897SDF";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: ML-TSSCL102-SD8SDF";
			exit;
		}

		$objGrades = new Grades();
		$arrGrades = $objGrades->classes_select_grades(array(
			"user_id" => $arrParams["user_id"]
		));
		$arrMergedScales = array();
		foreach ($arrGrades as $objGrade)
		{
			$arrTaskScaleParams = array(
				"institution_id"	=> $arrParams["institution_id"],
				"grade" 			=> $objGrade->grade
			);
			if (isset($arrParams["campaign_id"]))
				$arrTaskScaleParams["campaign_id"] = $arrParams["campaign_id"];
			if (isset($arrParams["mission_id"]))
				$arrTaskScaleParams["mission_id"] = $arrParams["mission_id"];
			//var_dump($arrTaskScaleParams);

			$arrTaskScales = $this->tasks_scale_select_hierarchy($arrTaskScaleParams);
			foreach ($arrTaskScales as $objTaskScale)
			{
				if (!$objTaskScale->task_id) // Only look at task scheduling
					continue;
				if (isset($arrParams["mission_id"]))
					$arrMergedScales["tasks"][$objTaskScale->task_id] = $objTaskScale;
				else if (isset($arrParams["campaign_id"]))
					$arrMergedScales["missions"][$objTaskScale->mission_id]["tasks"][$objTaskScale->task_id] = $objTaskScale;
				else
					$arrMergedScales["campaigns"][$objTaskScale->campaign_id]["missions"][$objTaskScale->mission_id]["tasks"][$objTaskScale->task_id] = $objTaskScale;
			}
		}
		return $arrMergedScales;
	}

	/*
	 * Generate an array of all campaigns, missions and book that are available
	 * based on whats available in the task_scale table with the value of the
	 * corresponding scale object as the values relative to a user.
	 */
	public function task_scale_select_campaigns_books($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-TSSCB101-FGDS3S";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: ML-TSSCB102-SDFD3D";
			exit;
		}

		$objGrades = new Grades();
		$arrGrades = $objGrades->classes_select_grades(array(
			"user_id" => $arrParams["user_id"]
		));

		// Load all available missions for an instituion
		$objMissions = new Missions();
		$arrMissions = $objMissions->missions_select_hierarchy(array(
			"institution_id" => $arrParams["institution_id"]
		));

		$arrMissionHash = array();
		foreach ($arrMissions as $objMission)
		{
			if ($objMission->installed_mission_id)
				$arrMissionHash[$objMission->installed_mission_id] = $objMission;
			else
				$arrMissionHash[$objMission->mission_id] = $objMission;
		}

		// Load all the books available to the institution
		$objBooks = new Books();
		$arrBooks = $objBooks->books_select_hierarchy(array(
			"institution_id" => $arrParams["institution_id"]
		));

		$arrBooksHash = array();
		foreach ($arrBooks as $objBook)
		{
			$arrBooksHash[$objBook->book_id] = $objBook;
		}

		$arrMergedScales = array();
		foreach ($arrGrades as $objGrade)
		{
			$arrTaskScaleParams = array(
				"institution_id"	=> $arrParams["institution_id"],
				"grade" 			=> $objGrade->grade
			);
			if (isset($arrParams["campaign_id"]))
				$arrTaskScaleParams["campaign_id"] = $arrParams["campaign_id"];
			if (isset($arrParams["mission_id"]))
				$arrTaskScaleParams["mission_id"] = $arrParams["mission_id"];

			$arrTaskScaleParams["task_id"] = 0;
			$arrTaskScales = $this->tasks_scale_select_hierarchy($arrTaskScaleParams);

			foreach ($arrTaskScales as $objTaskScale)
			{
				if (!isset($arrMissionHash[$objTaskScale->mission_id]))
					continue;
				$intBook = $arrMissionHash[$objTaskScale->mission_id]->book_id;
				if ($intBook)
				{
					if (isset($arrParams["mission_id"]))
						$arrMergedScales["tasks"][$objTaskScale->task_id]["book"] = $arrBooksHash[$intBook];
					else if (isset($arrParams["campaign_id"]))
						$arrMergedScales["missions"][$objTaskScale->mission_id]["tasks"][$objTaskScale->task_id]["book"] = $arrBooksHash[$intBook];
					else
						$arrMergedScales["campaigns"][$objTaskScale->campaign_id]["missions"][$objTaskScale->mission_id]["book"] = $arrBooksHash[$intBook];
				}
			}
		}
		return $arrMergedScales;
	}

	/*
	 * Generate an array of all campaigns, missions and tasks that are available
	 * based on whats available in the task_scale table with the value of the
	 * corresponding scale object as the values.
	 */
	public function task_scale_select_ladders($arrParams)
	{
		// Clean
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$arrTaskScaleParams = array();
		if (isset($arrParams["institution_id"]))
			$arrTaskScaleParams["institution_id"] = $arrParams["institution_id"];
		if (isset($arrParams["campaign_id"]))
			$arrTaskScaleParams["campaign_id"] = $arrParams["campaign_id"];
		if (isset($arrParams["mission_id"]))
			$arrTaskScaleParams["mission_id"] = $arrParams["mission_id"];
		//var_dump($arrTaskScaleParams);
		$arrMergedScales = array();
		$arrTaskScales = $this->_tasks_scale_select($arrTaskScaleParams);
		foreach ($arrTaskScales as $objTaskScale)
		{
			if (!$objTaskScale->task_id) // Only look at task scheduling
				continue;
			if (isset($arrParams["mission_id"]))
				$arrMergedScales["tasks"][$objTaskScale->task_id] = $objTaskScale;
			else if (isset($arrParams["campaign_id"]))
				$arrMergedScales["missions"][$objTaskScale->mission_id]["tasks"][$objTaskScale->task_id] = $objTaskScale;
			else
				$arrMergedScales["campaigns"][$objTaskScale->campaign_id]["missions"][$objTaskScale->mission_id]["tasks"][$objTaskScale->task_id] = $objTaskScale;
		}
		return $arrMergedScales;
	}

	/*
	 * Calculate a users velocity on a specific campaign.
	 * Note: This function is only designed to handle users in a single grade
	 * at once. Will default also to highest grade.
	 */
	public function grade_ladder_task_velocity($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-GLTV101-SD78FD";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: ML-GLTV102-897DFD";
			exit;
		}
		if (!isset($arrParams["ladder"]))
		{
			print "Sorry, there was an error: ML-GLTV103-ASD32R";
			exit;
		}

		$objGrades = new Grades();

		// Find the grade of a user
		$arrGradeOfUser = end($objGrades->classes_select_grades(array(
			"user_id" => $arrParams["user_id"],
			//"institution_id" => $arrParams["institution_id"]
		)));
		if (!$arrGradeOfUser)
		{
			return false;
		}
		//print $arrParams["user_id"];exit;
		//var_dump($arrGradeOfUser);
		$intGrade = intval(current($arrGradeOfUser));

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
			"ladder" => $arrParams["ladder"] ? $arrParams["ladder"]-1 : $arrParams["ladder"],
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objLaddersVelocity)
		{
			print "Sorry, there was an error: ML-GLTV106-SD76S8";
			var_dump($arrParams);exit;
		}

		return $objGradeVelocity->velocity * $objLaddersVelocity->velocity;
	}

	/*
	 * Get the ladder hierarchy from a users rendered ladder velocities.
	 */
	public function ladder_from_velocity($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-LFV101-4GG4G4";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: ML-LFV102-SDF3F3";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: ML-LFV103-23REF3";
			exit;
		}
		if (!isset($arrParams["ladder_velocity"]))
		{
			print "Sorry, there was an error: ML-LFV104-CVCVCV";
			exit;
		}
		$objScheduler = new Scheduler();
		$objGrades = new Grades();

		// Get the grade velocity
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
			print "Sorry, there was an error: ML-LFV105-8S7DFD";
			exit;
		}
		$intGradeVelocity = $objGradeVelocity->velocity;

		$arrLadders = $objScheduler->load_available_ladders2(array(
			"user_id" 			=> $arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"campaign_id"		=> $arrParams["campaign_id"],
		));

		foreach ($arrLadders as $objLadder)
		{
			if ($arrParams["ladder_velocity"] == $objLadder->velocity)
			{
				return $objLadder->ladder;
			}
		}
		print "Sorry, there was an error: ML-LFV106-GT66H7";
		exit;
	}

	/*
	 * Get the current velocity for a user on a specific campaign
	 * Required: user_id, campaign_id, institution_id
	 */
	public function campaign_user_ladder_velocity($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-CULV101-SD87DD";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: ML-CULV102-7D6FG6";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objLadders = new Ladders();

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
		$intLadderVelocity = $objLadders->grade_ladder_task_velocity(array(
			"user_id" => $arrParams["user_id"],
			"campaign_id" => $arrParams["campaign_id"],
			"ladder" => $objCampaignEnrollment->ladder
		));
		return $intLadderVelocity;
	}

	/*
	 * Required: class_id
	 * creat a list of all ladders for a specific campagin and class
	 */
	public function class_campaign_ladders($arrParams)
	{
		if (!isset($arrParams["class_id"]))
		{
			print "Sorry, there was an error: ML-CCL101-A897SD";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: ML-CCL102-89A7SD";
			exit;
		}
		$objClasses = new Classes();
		$objGrades = new Grades();

		$objClass = first($objClasses->_classes_select(array(
			"class_id" => $arrParams["class_id"]
		)));
		if (!$objClass)
		{
			print "Sorry, there was an error: ML-CCL103-89A7SD";
			exit;
		}

		$objGrade = first($objGrades->_grades_select_hierarchal(array(
			"grade_name" => $objClass->grade,
			"institution_id" => $this->_user_session_data->institution_id
		)));

		// Find the velocity applied to the grade
		$objGradeVelocity = current($objGrades->_velocity_grades_select(array(
			"grade_hierarchy" => $objClass->grade,
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objGradeVelocity)
			$intGradeVelocity = 1;
		else
			$intGradeVelocity = $objGradeVelocity->velocity;
		if (!$intGradeVelocity)
			return array();

		// find all possible ladders available for this campaign and class
		$arrTaskScales = $this->tasks_scale_select_hierarchy(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"grade" => $objGrade->grade_hierarchy + 1,
			"campaign_id" => $arrParams["campaign_id"],
		));
		$arrLadders = $this->_tools->array_hash($arrTaskScales, "ladder");
		if (!count($arrLadders))
			return array();
		$arrLadderKeys = array_keys($arrLadders);
		foreach ($arrLadderKeys as $intKey => $intLadder)
		{
			$arrLadderKeys[$intKey] = $intLadder - 1;
		}

		// Find the velocity for all provided ladders
		$arrLaddersVelocity = $objGrades->_velocity_ladders_select(array(
			"ladder" => $arrLadderKeys,
			"campaign_id" => $arrParams["campaign_id"]
		));
		if (!count($arrLaddersVelocity))
			return array();

		$arrResult = array();
		foreach ($arrLaddersVelocity as $objLadderVelocity)
		{
			$arrResult[(string) ($objLadderVelocity->velocity * $intGradeVelocity)] = $objLadderVelocity->ladder;
		}
		ksort($arrResult, SORT_NUMERIC);
		return $arrResult;
	}
}
?>