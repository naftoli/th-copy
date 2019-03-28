<?php
class Medals
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

	public function _medals_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"medal_id"	         => @$arrParams["medal_id"],
			"institution_id"	 => @$arrParams["institution_id"],
			"campaign_id"	     => @$arrParams["campaign_id"],
			"medal_hierarchy"	 => @$arrParams["medal_hierarchy"],
			"medal_name"	     => @$arrParams["medal_name"],
			"medal_value"	     => @$arrParams["medal_value"],
			"medal_image_id"	 => @$arrParams["medal_image_id"],
			"medal_image_id_2"	 => @$arrParams["medal_image_id_2"],
			"created"	         => @$arrParams["created"],
			"modified"	         => @$arrParams["modified"],
			"created_by"	     => @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				medals
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
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
				medal_hierarchy ASC";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _medals_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"medal_id"	         => @$arrParams["medal_id"],
			"institution_id"	 => @$arrParams["institution_id"],
			"campaign_id"	     => @$arrParams["campaign_id"],
			"medal_hierarchy"	 => @$arrParams["medal_hierarchy"],
			"medal_name"	     => @$arrParams["medal_name"],
			"medal_value"	     => @$arrParams["medal_value"],
			"medal_image_id"	 => @$arrParams["medal_image_id"],
			"medal_image_id_2"	 => @$arrParams["medal_image_id_2"],
			"created"	         => date("Y-m-d H:i:S"),
			"created_by"	     => $arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("medals", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _medals_select_hierarchy($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"medal_id"	         => @$arrParams["medal_id"],
			"campaign_id"	     => @$arrParams["campaign_id"],
			"medal_hierarchy"	 => @$arrParams["medal_hierarchy"],
			"medal_name"	     => @$arrParams["medal_name"],
			"medal_value"	     => @$arrParams["medal_value"],
			"medal_image_id"	 => @$arrParams["medal_image_id"],
			"medal_image_id_2"	 => @$arrParams["medal_image_id_2"],
			"created"	         => @$arrParams["created"],
			"modified"	         => @$arrParams["modified"],
			"created_by"	     => @$arrParams["created_by"]
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
				medals
			WHERE
				institution_id IN (" . join(",", $arrInstitution) . ")";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
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
				medal_hierarchy ASC";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _medals_update($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$arrValues = array();
		if (isset($arrParams["values"]["institution_id"]))
			$arrValues["institution_id"] = $arrParams["values"]["institution_id"];
		if (isset($arrParams["values"]["campaign_id"]))
			$arrValues["campaign_id"] = $arrParams["values"]["campaign_id"];
		if (isset($arrParams["values"]["medal_hierarchy"]))
			$arrValues["medal_hierarchy"] = $arrParams["values"]["medal_hierarchy"];
		if (isset($arrParams["values"]["medal_name"]))
			$arrValues["medal_name"] = $arrParams["values"]["medal_name"];
		if (isset($arrParams["values"]["medal_value"]))
			$arrValues["medal_value"] = $arrParams["values"]["medal_value"];
		if (isset($arrParams["values"]["medal_image_id"]))
			$arrValues["medal_image_id"] = $arrParams["values"]["medal_image_id"];
		if (isset($arrParams["values"]["medal_image_id_2"]))
			$arrValues["medal_image_id_2"] = $arrParams["values"]["medal_image_id_2"];
		$arrValues["modified"] = date("Y-m-d H:i:S");

		$arrWhere = array();
		if (isset($arrParams["where"]["medal_id"]))
			$arrWhere[] = $this->_db->quoteInto('medal_id = ?', $arrParams["where"]["medal_id"]);
		if (isset($arrParams["where"]["institution_id"]))
			$arrWhere[] = $this->_db->quoteInto('institution_id = ?', $arrParams["where"]["institution_id"]);
		if (isset($arrParams["where"]["campaign_id"]))
			$arrWhere[] = $this->_db->quoteInto('campaign_id = ?', $arrParams["where"]["campaign_id"]);
		if (isset($arrParams["where"]["medal_hierarchy"]))
			$arrWhere[] = $this->_db->quoteInto('medal_hierarchy = ?', $arrParams["where"]["medal_hierarchy"]);
		if (isset($arrParams["where"]["medal_name"]))
			$arrWhere[] = $this->_db->quoteInto('medal_name = ?', $arrParams["where"]["medal_name"]);
		if (isset($arrParams["where"]["medal_value"]))
			$arrWhere[] = $this->_db->quoteInto('medal_value = ?', $arrParams["where"]["medal_value"]);
		if (isset($arrParams["where"]["medal_image_id"]))
			$arrWhere[] = $this->_db->quoteInto('medal_image_id = ?', $arrParams["where"]["medal_image_id"]);
		if (isset($arrParams["where"]["medal_image_id_2"]))
			$arrWhere[] = $this->_db->quoteInto('medal_image_id_2 = ?', $arrParams["where"]["medal_image_id_2"]);
		if (isset($arrParams["where"]["created"]))
			$arrWhere[] = $this->_db->quoteInto('created = ?', $arrParams["where"]["created"]);
		if (isset($arrParams["where"]["created_by"]))
			$arrWhere[] = $this->_db->quoteInto('created_by = ?', $arrParams["where"]["created_by"]);

		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MM-MU101-SD7FSS";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("medals", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _medals_delete($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		if (isset($arrParams["medal_id"]))
			$arrFeilds[] = $this->_db->quoteInto('medal_id = ?', $arrParams["medal_id"]);
		if (isset($arrParams["institution_id"]))
			$arrFeilds[] = $this->_db->quoteInto('institution_id = ?', $arrParams["institution_id"]);
		if (isset($arrParams["campaign_id"]))
			$arrFeilds[] = $this->_db->quoteInto('campaign_id = ?', $arrParams["campaign_id"]);
		if (isset($arrParams["medal_hierarchy"]))
			$arrFeilds[] = $this->_db->quoteInto('medal_hierarchy = ?', $arrParams["medal_hierarchy"]);
		if (isset($arrParams["medal_name"]))
			$arrFeilds[] = $this->_db->quoteInto('medal_name = ?', $arrParams["medal_name"]);
		if (isset($arrParams["medal_value"]))
			$arrFeilds[] = $this->_db->quoteInto('medal_value = ?', $arrParams["medal_value"]);
		if (isset($arrParams["medal_image_id"]))
			$arrFeilds[] = $this->_db->quoteInto('medal_image_id = ?', $arrParams["medal_image_id"]);
		if (isset($arrParams["medal_image_id_2"]))
			$arrFeilds[] = $this->_db->quoteInto('medal_image_id_2 = ?', $arrParams["medal_image_id_2"]);
		if (isset($arrParams["created"]))
			$arrFeilds[] = $this->_db->quoteInto('created = ?', $arrParams["created"]);
		if (isset($arrParams["created_by"]))
			$arrFeilds[] = $this->_db->quoteInto('created_by = ?', $arrParams["created_by"]);
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MM-GMD101-97F78S";
			exit;
		}

		/*
		 * Because deleting a medal will break the hierarchy first make a selection
		 * to find which campaign its under inorder to run autocorrect_hierarchy function right afterwords.
		 */
		$objMedal = current($this->_medals_select($arrFeilds));

		$intCampaign = $objMedal->campaign_id;
		$intInstitution = $objMedal->institution_id;
		$boolResult = $this->_db->delete("medals", $arrFeilds);

		// Fill any gaps in the hierarchy created from deleting a medal
		$this->autocorrect_hierarchy(array(
			"campaign_id" => $intCampaign,
			"institution_id" => $intInstitution
		));
		return $boolResult;
	}

	/*
	 * Correct any flaws in medal hierarchy, 0,2,3,4 should look like 0,1,2,3
	 * Params: institution_id, campaign_id
	 */
	public function autocorrect_hierarchy($arrParams)
	{
		if (
			!isset($arrParams["institution_id"])
			|| !isset($arrParams["campaign_id"])
		) {
			print "Sorry, there was an error: MM-MH101-SD98F7";
			exit;
		}
		$arrMedals = $this->_medals_select(array(
			"institution_id" => $arrParams["institution_id"],
			"campaign_id" => $arrParams["campaign_id"]
		));

		foreach ($arrMedals as $intKey => $objMedal)
		{
			if ($intKey != $objMedal->medal_hierarchy)
			{
				$this->_medals_update(array(
					"where" => array(
						"medal_id" => $objMedal->medal_id
					),
					"values" => array(
						"medal_hierarchy" => $intKey
					)
				));
			}
		}
	}

	/*
	 * Process the movment of a medal up or down.
	 * Params: move = up or down, medal_id
	 * Return the resulted hierarchy.
	 */
	public function move_hierarchy($arrParams)
	{
		if (!isset($arrParams["medal_id"]))
		{
			print "Sorry, there was an error: MM-MH101-SD98F7";
			exit;
		}
		// Load the current medal
		$objMedal = current($this->_medals_select(array(
			"medal_id" => $arrParams["medal_id"]
		)));
		if (!$objMedal)
		{
			print "Sorry, there was an error: MM-MH102-SDF98D";
			exit;
		}
		$intMedalHierarchy = $objMedal->medal_hierarchy;
		if ($arrParams["move"] == "up")
		{
			// Check if the hierarchy is already at the max
			if ($intMedalHierarchy == 0)
				return 0;
			$intMedalHierarchyTo = $objMedal->medal_hierarchy-1;
		}
		else if ($arrParams["move"] == "down")
		{
			$intMedalHierarchyTo = $objMedal->medal_hierarchy+1;
		}

		// Find the medal that is being moved into to complete the translation.
		$objMedalTo = current($this->_medals_select(array(
			"campaign_id" => $objMedal->campaign_id,
			"institution_id" => $objMedal->institution_id,
			"medal_hierarchy" => $intMedalHierarchyTo
		)));
		if (!$objMedalTo)
			return $intMedalHierarchyTo;

		// Move the "from" medal
		$this->_medals_update(array(
			"where" => array(
				"medal_id" => $objMedal->medal_id
			),
			"values" => array(
				"medal_hierarchy" => $intMedalHierarchyTo
			)
		));
		// Move the "to" medal
		$this->_medals_update(array(
			"where" => array(
				"medal_id" => $objMedalTo->medal_id
			),
			"values" => array(
				"medal_hierarchy" => $intMedalHierarchy
			)
		));

		return $intMedalHierarchyTo;
	}

	/*
	 * Find what medal a user is on
	 */
	public function user_medal($arrParams)
	{
		if (!isset($arrParams['user_id']))
		{
			print "Sorry, there was an error: MM-UM101-dfg7ff";
		}
		if (!isset($arrParams['campaign_id']))
		{
			print "Sorry, there was an error: MM-UM102-gff9d9";
		}
		if (!isset($arrParams['institution_id']))
		{
			print "Sorry, there was an error: MM-UM101-artgg4";
		}
		$objUsers = new Users();
		$objScheduler = new Scheduler();
		$objMissions = new Missions();
		$objCampaigns = new Campaigns();
		$intBatBarEpoch = $objUsers->user_batbar_in_epoch(array(
			"user_id" => $arrParams['user_id']
		));
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $arrParams['campaign_id']
		)));
		$objLatestMission = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $objMission->mission_id,
			"user_id" => $arrParams['user_id'],
			"institution_id" => $arrParams['institution_id'],
			"status" => "Completed",
			"_LIMIT" => 1
		)));
		$intLatestMission = $objLatestMission ? $objLatestMission->mission_increment + 1 : 0;
		$arrMedals = $objScheduler->load_book_medals(array(
			"campaign_id" => $arrParams['campaign_id'],
			"user_id" => $arrParams['user_id'],
			"kiosk" => true,
			"capture_end_date" => $intBatBarEpoch - 7 * 86400
		));
		$intMedalsSum = 0;
		$objMedal = NULL;
		foreach ($arrMedals as $arrMedal)
		{
			$objMedal = $arrMedal["medal"];
			$intMedalsSum += $arrMedal["medal"]->medal_value;
			if ($intMedalsSum > $intLatestMission)
				break;
		}
		if (!$objMedal && isset($arrMedal["medal"]))
			$objMedal = $arrMedal["medal"];
		return $objMedal;
	}

	/*
	 * Find what medal a user completed
	 */
	public function user_medal_completed($arrParams)
	{
		if (!isset($arrParams['user_id']))
		{
			print "Sorry, there was an error: MM-UM101-dfg7ff";
		}
		if (!isset($arrParams['campaign_id']))
		{
			print "Sorry, there was an error: MM-UM102-gff9d9";
		}
		if (!isset($arrParams['institution_id']))
		{
			print "Sorry, there was an error: MM-UM101-artgg4";
		}
		$objUsers = new Users();
		$objScheduler = new Scheduler();
		$objMissions = new Missions();
		$objCampaigns = new Campaigns();
		$intBatBarEpoch = $objUsers->user_batbar_in_epoch(array(
			"user_id" => $arrParams['user_id']
		));
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $arrParams['campaign_id']
		)));
		$objLatestMission = current($objCampaigns->_user_campaigns_select(array(
			"mission_id" => $objMission->mission_id,
			"user_id" => $arrParams['user_id'],
			"institution_id" => $arrParams['institution_id'],
			"status" => "Completed",
			"_LIMIT" => 1
		)));
		$intLatestMission = $objLatestMission ? $objLatestMission->mission_increment + 1 : 0;
		$arrMedals = $objScheduler->load_book_medals(array(
			"campaign_id" => $arrParams['campaign_id'],
			"user_id" => $arrParams['user_id'],
			"kiosk" => true,
			"capture_end_date" => $intBatBarEpoch - 7 * 86400
		));
		$intMedalsSum = 0;
		$objMedal = NULL;
		foreach ($arrMedals as $arrMedal)
		{
			$intMedalsSum += $arrMedal["medal"]->medal_value;
			if ($intMedalsSum > $intLatestMission)
				break;
			$objMedal = $arrMedal["medal"];
		}
		return $objMedal;
	}

	/*
	 * Find the number of missions elapsed up to the medal provided.
	 */
	public function mission_start($arrParams=0)
	{
		// Two methods of providing the parameters
		if (
			isset($arrParams["institution_id"])
			&& isset($arrParams["campaign_id"])
			&& isset($arrParams["medal_hierarchy"])
		) {
			$intInstitution = $arrParams["institution_id"];
			$intCampaign = $arrParams["campaign_id"];
			$intMedalHierarchy = $arrParams["medal_hierarchy"];
		}
		else
		{
			if (!isset($arrParams["medal_id"]))
			{
				print "Sorry, there was an error: MM-MS101-SD89DS";
				exit;
			}
			$objCurrentMedal = current($this->_medals_select(array(
				"medal_id" => $arrParams["medal_id"]
			)));
			$intInstitution = $objCurrentMedal->institution_id;
			$intCampaign = $objCurrentMedal->campaign_id;
			$intMedalHierarchy = $objCurrentMedal->medal_hierarchy;
		}

		$arrMedals = $this->_medals_select(array(
			"institution_id" => $intInstitution,
			"campaign_id" => $intCampaign
		));
		$intMissionSum = 0;
		foreach ($arrMedals as $objMedal)
		{
			if ($objMedal->medal_hierarchy < $intMedalHierarchy)
			{
				$intMissionSum += $objMedal->medal_value;
			}
		}
		return $intMissionSum;
	}
}
?>