<?php
class Automation
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

	public function _user_campaign_progress_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$arrFeilds = array (
			"campaign_id"			=> @$arrParams["campaign_id"],
			"user_id"				=> @$arrParams["user_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"current_line"			=> @$arrParams["current_line"],
			"campaign_goal"			=> @$arrParams["campaign_goal"]
		);

		// Execute
		$boolResult = $this->_db->insert("user_campaign_progress", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _user_campaign_progress_update($arrParams)
	{
		$arrValuesParams = array("institution_id","campaign_id","user_id","current_line","campaign_goal");
		$arrWhereParams = array("user_campaign_progress_id","institution_id","campaign_id","user_id","current_line","campaign_goal","modified");

		$arrParams = $this->_tools->rsqlclean($arrParams);

		$arrValues = array();

		// Values

		foreach ($arrValuesParams as $strKey)
		{
			if (isset($arrParams["values"][$strKey]))
				$arrValues[$strKey] = $arrParams["values"][$strKey];
		}
		$arrValues["modified"] = date("Y-m-d H:i:S");

		// Where
		$arrWhere = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams["where"][$strKey]);
		}

		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MA-UCPU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("user_campaign_progress", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _user_campaign_progress_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_campaign_progress_id"		=> @$arrParams["user_campaign_progress_id"],
			"institution_id"				=> @$arrParams["institution_id"],
			"campaign_id"					=> @$arrParams["campaign_id"],
			"user_id"						=> @$arrParams["user_id"],
			"current_line"					=> @$arrParams["current_line"],
			"campaign_goal"					=> @$arrParams["campaign_goal"],
			"modified"						=> @$arrParams["modified"]
		);

		$strSql = "
			SELECT
				*
			FROM
				user_campaign_progress
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (is_array($Value))
			{
				$arrValues = array();
				foreach ($Value as $SubValue)
				{
					if (!is_null(@$SubValue)) {
						if (!is_int($SubValue))
							$SubValue = '"' . $SubValue . '"';
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
			else if (!is_null(@$Value))
			{
				if (!is_int($Value))
					$Value = '"' . $Value . '"';
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}

		$strSql .= "
			ORDER BY
				modified DESC";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/*
	 * return as array(resulted_sum)
	 */
	public function user_campaign_progress_sum($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["_SUM"]))
		{
			print "Sorry, there was an error: MA-UCPS101-SD90F8";
			exit;
		}

		// Possible column selections
		$arrColumns = array (
			"user_campaign_progress_id"		=> @$arrParams["user_campaign_progress_id"],
			"institution_id"				=> @$arrParams["institution_id"],
			"campaign_id"					=> @$arrParams["campaign_id"],
			"user_id"						=> @$arrParams["user_id"],
			"current_line"					=> @$arrParams["current_line"],
			"campaign_goal"					=> @$arrParams["campaign_goal"],
			"modified"						=> @$arrParams["modified"]
		);

		$strSql = "
			SELECT
				SUM(`" . $arrParams["_SUM"] . "`) as resulted_sum
			FROM
				user_campaign_progress
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (is_array($Value))
			{
				$arrValues = array();
				foreach ($Value as $SubValue)
				{
					if (!is_null(@$SubValue))
					{
						if (!is_int($SubValue))
							$SubValue = '"' . $SubValue . '"';
						$arrValues[] = $SubValue;
					}
				}
				if (count($arrValues))
				{
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
					return 0;
			}
			else if (!is_null(@$Value))
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
		$objResult = $this->_db->fetchRow($strSql);
		return intval(@$objResult->resulted_sum);
	}


	/*
	 * Shimmy:
They can also click on any prize or campaign and apply to Hebrew school
For the future when they create prizes or campaigns they can assign it to both institutions
	 */
	public function copy_prize_to_camp ($intPrize) {
		$query = new QueryGen();
		$objPrize = first($query->prize__select(array(
			'prize_id' => $intPrize
		)));
		$arrPrizeSizes = $query->prize_sizes__select(array(
			'prize_id' => $intPrize
		));

		// check to make sure that the institution is allowed to make this type
		// of transaction
		$objInstitution = first($query->institutions__select(array(
			'institution_id' => $objPrize->institution_id
		)));
		if ($objInstitution->template_style == "chabadhebrewschool") {
			// since all the schools in this network have only one
			// admin and always have a camp we can just look up who
			// the admin and find which camp is connected to it
			$objAdmin = first($query->permissions__select(array(
				'permission' => 'Institution Administrator',
				'institution_id' => $objInstitution->institution_id
			)));
			$objCampPermission = first($query->permissions__select(array(
				'user_id' => $objAdmin->user_id,
				'permission' => 'Institution Administrator',
				'template_style' => 'hebrewschool1'
			)));
			if (!$objCampPermission) {
				return array(
					'failure' => 'You account lacks network access to a camp.'
				);
			}
			/// check if host already has a prize by this name
			$objCampPrizeSearch = first($query->prize__select(array(
				'prize_name' => $objPrize->prize_name,
				'institution_id' => $objCampPermission->institution_id
			)));
			if ($objCampPrizeSearch) {
				return array(
					'failure' => 'There is already a prize with this name in the designated camp account.'
				);
			}
			$intPrize = $query->prize__insert(array(
				'template_prize_id' => $objPrize->template_prize_id,
				'parent_prize_id' => $objPrize->parent_prize_id,
				'legacy_add_on_id' => $objPrize->legacy_add_on_id,
				'teacher_id' => $objPrize->teacher_id,
				'guardian_id' => $objPrize->guardian_id,
				'network_id' => $objPrize->network_id,
				'institution_id' => $objCampPermission->institution_id,
				'prize_name' => $objPrize->prize_name,
				'points' => $objPrize->points,
				'prize_category' => $objPrize->prize_category,
				'bar_code' => $objPrize->bar_code,
				'prize_description' => $objPrize->prize_description,
				'image_id' => $objPrize->image_id,
				'add_on_restricted' => $objPrize->add_on_restricted,
				'use_sub_prizes' => $objPrize->use_sub_prizes,
				'one_per_user' => $objPrize->one_per_user,
				'prize_count' => $objPrize->prize_count,
				'prize_type' => $objPrize->prize_type,
				'installable_default_on' => $objPrize->installable_default_on,
				'prize_price' => $objPrize->prize_price,
				'prize_discounted_price' => $objPrize->prize_discounted_price,
				'is_active' => $objPrize->is_active,
				'created_by' => $this->_user_session_data->user_id
			));
			foreach ($arrPrizeSizes as $objPrizeSize)
			{
				$query->prize_sizes__insert(array(
					'prize_id' => $intPrize,
					'prize_size_hierarchy' => $objPrizeSize->prize_size_hierarchy,
					'prize_size' => $objPrizeSize->prize_size,
					'created_by' => $this->_user_session_data->user_id
				));
			}
			return array(
				'success' => 'true'
			);
		}
		return array(
			'failure' => 'You are not in a network that is allowed to complete this process.'
		);
	}

	public function copy_campaign_to_camp ($intCampaign) {
		$query = new QueryGen();
		$objCampaign = first($query->campaigns__select(array(
			'campaign_id' => $intCampaign
		)));
		$arrTasks = $query->tasks__select(array(
			'campaign_id' => $intCampaign
		));
		// check to make sure that the institution is allowed to make this type
		// of transaction
		$objInstitution = first($query->institutions__select(array(
			'institution_id' => $objCampaign->institution_id
		)));
		if ($objInstitution->template_style == "chabadhebrewschool") {
			// since all the schools in this network have only one
			// admin and always have a camp we can just look up who
			// the admin and find which camp is connected to it
			$objAdmin = first($query->permissions__select(array(
				'permission' => 'Institution Administrator',
				'institution_id' => $objInstitution->institution_id
			)));
			$objCampPermission = first($query->permissions__select(array(
				'user_id' => $objAdmin->user_id,
				'permission' => 'Institution Administrator',
				'template_style' => 'hebrewschool1'
			)));
			if (!$objCampPermission) {
				return array(
					'failure' => 'Sorry there was an error: MA-CCTC101-askdjs'
				);
				exit;
			}
			// Check to make sure the institution being copied to doesn't already
			// have a campaign by the same name
			$objCampaignInInstitution = first($query->campaigns__select(array(
				'institution_id' => $objCampPermission->institution_id,
				'campaign_name' => $objCampaign->campaign_name
			)));
			if ($objCampaignInInstitution) {
				return array(
					'failure' => 'There is already a campaign with this name in the designated camp account.'
				);
			}
			$intCampaign = $query->campaigns__insert(array(
				'installed_campaign_id' => $objCampaign->installed_campaign_id,
				'default_installed' => $objCampaign->default_installed,
				'institution_id' => $objCampPermission->institution_id,
				'network_id' => $objCampaign->network_id,
				'campaign_name' => $objCampaign->campaign_name,
				'image_largemed' => $objCampaign->image_largemed,
				'image_smallmed' => $objCampaign->image_smallmed,
				'image_achievement' => $objCampaign->image_achievement,
				'description' => $objCampaign->description,
				'commitments' => $objCampaign->commitments,
				'slogan' => $objCampaign->slogan,
				'campaign_type' => $objCampaign->campaign_type,
				'image_id' => $objCampaign->image_id,
				'is_active' => $objCampaign->is_active,
				'ladder' => $objCampaign->ladder,
				'points' => $objCampaign->points,
				'medals' => $objCampaign->medals,
				'ranks' => $objCampaign->ranks,
				'is_editable' => $objCampaign->is_editable,
				'created_by' => $this->_user_session_data->user_id
			));
			foreach ($arrTasks as $objTask) {
				$query->tasks__insert(array(
					'installed_task_id' => $objTask->installed_task_id,
					'school_type' => $objTask->school_type,
					'task_name' => $objTask->task_name,
					'campaign_id' => $intCampaign,
					'institution_id' => $objCampPermission->institution_id,
					'points' => $objTask->points,
					'min_points' => $objTask->min_points,
					'max_points' => $objTask->max_points,
					'frequency' => $objTask->frequency,
					'start_date' => $objTask->start_date,
					'end_date' => $objTask->end_date,
					'sequence' => $objTask->sequence,
					'velocity' => $objTask->velocity,
					'is_checkbox' => $objTask->is_checkbox,
					'is_locked' => $objTask->is_locked,
					'is_grid' => $objTask->is_grid,
					'is_card' => $objTask->is_card,
					'is_required' => $objTask->is_required,
					'is_active' => $objTask->is_active,
					'created_by' => $this->_user_session_data->user_id
				));
			}
			return array(
				'success' => 'true'
			);
		}
		return array(
			'failure' => 'true'
		);
	}

	/*
	 *
	 * - remove this function when possible
	 * Reture a randomized list of institutions ids that have children currently enrolled
	 * into them.
	 * return: (array) list of ids
	 */
	public function user_enrolled_random_institutions ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$strSql = "
			SELECT
				DISTINCT institution_id
			FROM
				user_campaigns
			ORDER BY RAND()
		";
		$arrResults = $this->_db->fetchAll($strSql);
		$arrInsititionIds = array();
		foreach ($arrResults as $objRow)
		{
			$arrInsititionIds[] = $objRow->institution_id;
		}
		return $arrInsititionIds;
	}

	public function campaign_progress_institution_sums($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$strSql = "
			SELECT
				SUM(current_line) as current_line_sum,
				SUM(campaign_goal) as campaign_goal_sum,
				user_campaign_progress.institution_id
			FROM
				user_campaign_progress
			GROUP BY
				user_campaign_progress.institution_id
		";
		$arrResults = $this->_db->fetchAll($strSql);
		return $arrResults;
	}

	public function user_goal($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: CA-UG101-SDY7DD";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: CA-UG102-3F3F3W";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
			$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		$query = new QueryGen();
		$intStartDate = mktime(0,0,0,1,1,2008);
		if (isset($arrParams['start_date']))
			$intStartDate = $arrParams['start_date'];
		$intEndDate = strtotime('+10 years');
		if (isset($arrParams['end_date']))
			$intEndDate = $arrParams['end_date'];
		$arrCampaignQuotaParams = array(
			'user_id' => $arrParams["user_id"],
			'_COLUMNS' => array('institution_id','task_increment','user_id','schedule_date','ladder_velocity','status','line_offset'),
			'institution_id' => $arrParams["institution_id"],
			'campaign_id' => $arrParams["campaign_id"],
			'status' => array('Enrollment','Completed'),
			'_GREATER' => array(
				'schedule_date' => $intStartDate
			),
			'_LESSER' => array(
				'schedule_date' => $intEndDate
			)
		);
		$arrCampaignQuotas = $query->user_campaigns__select($arrCampaignQuotaParams);
		//dumper($arrCampaignQuotas,1,1);
		$arrCampaignPauseParams = array(
			'user_id' => $arrParams["user_id"],
			'_COLUMNS' => array('institution_id','user_id','ladder_velocity','status','schedule_date'),
			'institution_id' => $arrParams["institution_id"],
			'campaign_id' => 1,
			'status' => array('Paused','Resumed'),
			'_ORDER' => 'schedule_date+0 ASC'
		);
		$arrCampaignPauses = array_bubble_hash('institution_id', 'user_id', $query->user_campaigns__select($arrCampaignPauseParams));
		$arrCampaignEnrollment = array_hash('institution_id', 'user_id', $query->user_campaigns__select(array(
			'user_id' => $arrParams["user_id"],
			'_COLUMNS' => array('institution_id','user_id','schedule_date','ladder'),
			'institution_id' => $arrParams["institution_id"],
			'campaign_id' => 1,
			'status' => 'Enrollment'
		)));
		$arrUserLastLines = array_hash('user_id', $query->user_campaigns__select(array(
			'_COLUMNS' => array('user_id', 'institution_id', 'schedule_date'),
			'status' => 'Completed',
			'user_id' => array_stack('user_id', array_flatten2($arrCampaignEnrollment)),
			'_ORDER' => 'schedule_date+0 DESC',
			'_GROUP' => 'user_id'
		)));
		//dumper($arrUserLastLines,0,1);
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'_COLUMNS' => array('user_id', 'dob', 'gender'),
			'user_id' => array_stack('user_id', array_flatten2($arrCampaignEnrollment))
		)));
		// calculate lines and quotas
		$arrUserData = array();
		$arrLastCampaignItem = array();
		$arrProcessedUserSchedules = array();
		foreach ($arrCampaignQuotas as $objUserCampaign)
		{
			if (!isset($arrCampaignEnrollment[$objUserCampaign->institution_id][$objUserCampaign->user_id]))
				continue;
			if ($arrCampaignEnrollment[$objUserCampaign->institution_id][$objUserCampaign->user_id]->ladder == 0)
				continue;
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
			) {
				$arrUserItem['lines_max'] = $objUserCampaign->task_increment;
				$arrLastCampaignItem[$objUserCampaign->user_id] = $objUserCampaign;
			}
			// goals
			if (!isset($arrProcessedUserSchedules[$objUserCampaign->user_id][$objUserCampaign->schedule_date]))
			{
				$arrProcessedUserSchedules[$objUserCampaign->user_id][$objUserCampaign->schedule_date] = TRUE;
				$arrUserItem['goal'] += $objUserCampaign->ladder_velocity;
				$arrUserItem['goal_count']++;
			}
		}
		//dumper($arrUserData,0,1);
		// calulcate remainders for the unmarked items
		$arrLadders = array_hash('ladder', $query->velocity_ladders__select(array(
			'campaign_id' => 1
		)));
		// add missing goals
		// on missing items past their last line marked
		// don't add to thier goals after their birthdate
		foreach ($arrCampaignEnrollment as $arrInstitution)
		{
			foreach ($arrInstitution as $objEnrollment)
			{
				if (!isset($arrUsers[$objEnrollment->user_id]))
					continue;
				if (!isset($arrUserData[$objEnrollment->user_id]))
				{
					$arrUserData[$objEnrollment->user_id] = array(
						'goal' => 0,
						'goal_count' => 0,
						'lines' => 0
					);
				}
				// Calculate the schedule time
				$arrUserItem3 = &$arrUserData[$objEnrollment->user_id];
				$intUnmarkedStart = $intStartDate < $objEnrollment->schedule_date ? $objEnrollment->schedule_date : $intStartDate;
				$intBatBar = strtotime('+' . (strtolower($arrUsers[$objEnrollment->user_id]->gender) == "m" ? 13 : 12) . ' years', strtotime($arrUsers[$objEnrollment->user_id]->dob));
				$intEndSchedule = $intBatBar > $intEndDate ? $intEndDate : $intBatBar;
				if (isset($arrUserLastLines[$objEnrollment->user_id]))
				{
					$objLatestLine = $arrUserLastLines[$objEnrollment->user_id];
					$intUnmarkedStart = $intUnmarkedStart < $objLatestLine->schedule_date ? $objLatestLine->schedule_date : $intUnmarkedStart;
				}
				$arrPauses = array();
				if (isset($arrCampaignPauses[$objEnrollment->institution_id][$objEnrollment->user_id]))
					$arrPauses = $arrCampaignPauses[$objEnrollment->institution_id][$objEnrollment->user_id];
				$intUnmarkedPause = 0;
				$intPauseStartTime = NULL;
				$boolPaused = 0;
				$intLastPauseAmount = NULL;
				foreach ($arrPauses as $objPause)
				{
					if ($objPause->status == "Paused" && !$boolPaused)
					{
						$boolPaused = 1;
						$intPauseStartTime = $objPause->schedule_date;
					}
					if ($objPause->status == "Resumed")
					{
						if (!$boolPaused && $intLastPauseAmount)
						{
							$intUnmarkedPause -= $intLastPauseAmount;
						}
						$boolPaused = 0;
						$intPauseEndTime = $objPause->schedule_date;
						// innser section
						if (
							$intPauseStartTime >= $intUnmarkedStart
							&& $intPauseEndTime <= $intEndSchedule
						) {
							$intLastPauseAmount = $intPauseEndTime - $intPauseStartTime;
							$intUnmarkedPause += $intLastPauseAmount;
						}
						// left out of bounds, right in bounds
						if (
							$intPauseStartTime < $intUnmarkedStart
							&& $intPauseEndTime >= $intEndSchedule
							&& $intPauseStartTime < $intEndSchedule
						) {
							$intLastPauseAmount = $intPauseEndTime - $intUnmarkedStart;
							$intUnmarkedPause += $intLastPauseAmount;
						}
						// left in bounds, right out of bounds
						if (
							$intPauseStartTime > $intUnmarkedStart
							&& $intPauseStartTime < $intEndSchedule
							&& $intPauseEndTime > $intEndSchedule
						) {
							$intLastPauseAmount = $intEndSchedule - $intPauseStartTime;
							$intUnmarkedPause += $intLastPauseAmount;
						}
						// left through right pause, both out of bounds
						if (
							$intPauseStartTime < $intUnmarkedStart
							&& $intPauseEndTime > $intEndSchedule
						) {
							$intLastPauseAmount = $intEndSchedule - $intUnmarkedStart;
							$intUnmarkedPause += $intLastPauseAmount;
						}
					}
				}
				// find the amount of weeks that have not yet been acounted for
				// find the pauses within that range
				// remove the pause weeks from the unaccounted for weeks
				$intWeeks = floor(($intEndSchedule - $intUnmarkedStart - $intUnmarkedPause) / 60 / 60 / 24 / 7.02388844230769);
				//$arrUserItem2['missing'] = $intMissing;
				if ($intWeeks > 0)
				{
					$arrUserItem3['goal'] += $arrLadders[$objEnrollment->ladder ? $objEnrollment->ladder-1 : 1]->velocity * $intWeeks;
				}
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
			if ((!isset($arrUserItem2['lines']) || !$arrUserItem2['lines']) && isset($arrUserItem2['lines_max']))
			{
				$arrUserItem2['lines'] = $arrUserItem2['lines_max'] - $arrUserItem2['lines_min'];
			}
			$arrSums[$intUser]['lines'] += $arrUserItem2['lines'];
		}
		foreach ($arrLastCampaignItem as $objLastCampaign)
		{
			$arrSums[$objLastCampaign->user_id]["lines"] += $objLastCampaign->ladder_velocity;
			$arrSums[$objLastCampaign->user_id]["lines"] = floor($arrSums[$objLastCampaign->user_id]["lines"]);
		}
		if (isset($arrParams['multi']))
		{
			return $arrSums;
		} else {
			$arrData = reset($arrSums);
			return array(
				"current_line" => $arrData['lines'],
				"campaign_goal" => $arrData['goal']
			);
		}
	}

	public function user_goal2($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: CA-UG101-SDY7DD";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: CA-UG102-3F3F3W";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objScheduler = new Scheduler();
		$objMarking = new Marking();
		$objAutomation = new Automation();
		$query = new QueryGen();

		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objMission)
		{
			return -2;
		}
		$intLattestLine = $objMarking->latest_line_hierarchy(array(
			"mission_id" => $objMission->mission_id,
			"user_id" => $arrParams["user_id"]
		));
		$intGoal = 0;
		$objEnrollment = current($objCampaigns->_user_campaigns_select(array(
			"user_id" => $arrParams["user_id"],
			"campaign_id" => $arrParams["campaign_id"],
			"institution_id" => @$arrParams["institution_id"], //
			"status" => "Enrollment"
		)));
        if ($objEnrollment)
        {
			if (!empty($arrParams['start_date']))
				$intYearStart = $arrParams['start_date']; //mktime(0, 0, 0, 8, 31, date("Y")-1)-1;
			else
				$intYearStart = capture_start_date;
			$intYearStart = $intYearStart < $objEnrollment->schedule_date ? $objEnrollment->schedule_date : $intYearStart;

			if (!empty($arrParams['end_date']))
				$intEnd = $arrParams['end_date'];
			else
				$intEnd = capture_end_date;

			$arrYearSchedule = $objScheduler->load_book_schedule(array(
				"user_id" => $arrParams["user_id"],
				"institution_id" => $objEnrollment->institution_id,
				"mission_id" => $objMission->mission_id,
				"capture_start_date" => $intYearStart,
				"capture_end_date" => $intEnd,
				"kiosk" => true
			));
			//dumper($arrYearSchedule,1,1);
			if (count($arrYearSchedule))
			{
				$arrYearStart = reset($arrYearSchedule);
				$arrYearEnd = end($arrYearSchedule);
				$intLineStart = reset($arrYearStart["tasks"])+1;
				$intLineEnd = end($arrYearEnd["tasks"])+1;
				$intGoal = floor($intLineEnd);
			}
			if ($intLattestLine < $objEnrollment->line_offset)
				$intLattestLine = $objEnrollment->line_offset;
			if (!isset($arrParams["institution_id"]))
				$arrParams["institution_id"] = $objEnrollment->institution_id;
        }

		//print "  < intGoal: " . $intGoal . "\n  < intLattestLine: " . $intLattestLine . "\n";
		$objCampaignProgress = current($objAutomation->_user_campaign_progress_select(array(
			"user_id" => $arrParams["user_id"],
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!isset($arrParams['no_logs']))
		{
			$query->user_campaign_logs__insert(array(
				"log_date" => time(),
				"user_id" => $arrParams["user_id"],
				"campaign_id" => $arrParams["campaign_id"],
				"campaign_goal" => $intGoal,
				"institution_id" => $arrParams["institution_id"]
			));
		}
		if (!isset($arrParams['no_progress_cache']))
		{
			if ($objCampaignProgress)
			{
				//print "  > Item updated\n";
				$objAutomation->_user_campaign_progress_update(array(
					"where" => array(
						"user_id" => $arrParams["user_id"],
						"campaign_id" => $arrParams["campaign_id"]
					),
					"values" => array(
						"current_line" => floor($intLattestLine),
						"campaign_goal" => $intGoal
					)
				));
			}
			else
			{
				//print "  > Item inserted\n";
				$objAutomation->_user_campaign_progress_insert(array(
					"user_id" => $arrParams["user_id"],
					"campaign_id" => $arrParams["campaign_id"],
					"current_line" => floor($intLattestLine),
					"campaign_goal" => $intGoal,
					"institution_id" => $arrParams["institution_id"]
				));
			}
		}
		return array(
			"current_line" => floor($intLattestLine),
			"campaign_goal" => $intGoal
		);
	}
}