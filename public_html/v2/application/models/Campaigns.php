<?php
class Campaigns
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
	
	// get all subjects from mashpiadb where subject_type is in '', 'WWTC', 'Tanya', 'achievement',
	// potentially limit to subject_id
	public function achievement_campaigns_select($arrParams = array())
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		$strSql = "SELECT * FROM mashpiadb.subjects WHERE subject_type IN ('', 'WWTC', 'Tanya', 'achievement')";
		if (isset($arrParams['campaign_id'])) {
			$strSql .= " AND subject_id = " . $arrParams['campaign_id'];
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	// Generic functions
	public function _campaigns_select ($arrParams = array())
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		$strSql = "select * from mashpiadb.subjects where subject_type in ('', 'WWTC', 'Tanya')";
		if (isset($arrParams['campaign_id'])) {
			$strSql .= " and subject_id = " . $arrParams['campaign_id'];
		}
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
		/*
		// Possible column selections
		$arrColumns = array (
			"campaign_id"				=> @$arrParams["campaign_id"],
			"installed_campaign_id"		=> @$arrParams["installed_campaign_id"],
			"default_installed" 	  	=> @$arrParams["default_installed"],
			"book_id"					=> @$arrParams["book_id"],
			"campaign_name"				=> @$arrParams["campaign_name"],
			"description"				=> @$arrParams["description"],
			"commitments"				=> @$arrParams["commitments"],
			"slogan"					=> @$arrParams["slogan"],
			"campaign_type"				=> @$arrParams["campaign_type"],
			"is_active"					=> @$arrParams["is_active"],
			"points"					=> @$arrParams["points"],
			"medals"					=> @$arrParams["medals"],
			"ranks"						=> @$arrParams["ranks"],
			"is_editable"				=> @$arrParams["is_editable"],
			"created"					=> @$arrParams["created"],
			"modified"					=> @$arrParams["modified"],
			"created_by"				=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === "0"
					|| $Value === 0
					|| $Value
				)
			) {
				if (is_array($Value) && !count($Value))
					return array();
				if (is_array($Value))
				{
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (!is_int($Value1))
						{
							$Value1 = '"' . $Value1 . '"';
						}
						$arrValues[] = $Value1;

					}
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
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
		}

		if (isset($arrParams["hierarchy"])) // join select institution hierarchy
		{
			$strSql2 = "";
			if (
				isset($arrParams["hierarchy"]["host_id"])
				&& (
					$arrParams["hierarchy"]["host_id"] === 0
					|| $arrParams["hierarchy"]["host_id"]
				)
			) {
				$strSql2 .= "
					OR (
						host_id = " . $arrParams["hierarchy"]["host_id"] . "
						AND (institution_type = 'School' OR institution_type='Camp')
					)";
			}
			if (
				isset($arrParams["hierarchy"]["network_id"])
				&& (
					$arrParams["hierarchy"]["network_id"] === 0
					|| $arrParams["hierarchy"]["network_id"]
				)
			) {
				$strSql2 .= "
					OR (
						network_id = " . $arrParams["hierarchy"]["network_id"] . "
						AND (institution_type = 'School' OR institution_type='Camp')
					)";
			}
			if (
				isset($arrParams["institution_id"])
				&& (
					$arrParams["institution_id"] === 0
					|| $arrParams["institution_id"]
				)
			) {
				$strSql2 .= "
					OR (
						institution_id = {$arrParams["institution_id"]}
						AND (institution_type = 'School' OR institution_type='Camp')
					)";
			}
			if ($strSql2 != "")
			{
				$strSql2 = "
					SELECT
						institution_id
					FROM
						institutions
					WHERE
						0
				" . $strSql2;
				$strSql .= "
					AND institution_id in (" . $strSql2 . ")";
			}
		}
		else
		{	// query institution_id if its not hierarchile
			if (
				isset($arrParams["institution_id"])
				&& (
					$arrParams["institution_id"] === 0
					|| $arrParams["institution_id"]
				)
			)
			$strSql .= "
				AND institution_id = " . $arrParams["institution_id"];
		}

		$strSql .= " ORDER BY campaign_id+0 DESC";
		//print $strSql;
		$arrResult = $this->_tools->cleanSlashes($this->_db->fetchAll($strSql));
		return $arrResult;
		*/
	}

	public function _zzz_tanya_users_legacy_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_id"				=> @$arrParams["user_id"],
			"lines_done"			=> @$arrParams["lines_done"],
			"lines_offset"			=> @$arrParams["lines_offset"]
		);

		$strSql = "
			SELECT
				*
			FROM
				zzz_tanya_users_legacy
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

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _campaigns_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}
		if (!isset($arrParams["is_active"]))
			$arrParams["is_active"] = 1;

		$arrFeilds = array (
			"installed_campaign_id"		=> @$arrParams["installed_campaign_id"],
			"default_installed"			=> @$arrParams["default_installed"],
			"campaign_name"				=> @$arrParams["campaign_name"],
			"image_largemed"			=> @$arrParams["image_largemed"],
			"image_smallmed"			=> @$arrParams["image_smallmed"],
			"image_achievement"			=> @$arrParams["image_achievement"],
			"description"				=> @$arrParams["description"],
			"commitments"				=> @$arrParams["commitments"],
			"slogan"					=> @$arrParams["slogan"],
			"campaign_type"				=> @$arrParams["campaign_type"],
			"institution_id"			=> @$arrParams["institution_id"],
			"is_active"					=> @$arrParams["is_active"],
			"ladder"					=> @$arrParams["ladder"],
			"points"					=> @$arrParams["points"],
			"medals"					=> @$arrParams["medals"],
			"ranks"						=> @$arrParams["ranks"],
			"is_editable"				=> @$arrParams["is_editable"],
			"created"					=> date("Y-m-d H:i:S"),
			"created_by"				=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("campaigns", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _campaigns_update($arrParams)
	{
		$arrValuesParams = array("campaign_name","installed_campaign_id","default_installed","description","commitments","book_measurement","slogan","campaign_photo","campaign_gold_photo","campaign_black_photo","campaign_type","institution_id","is_active","ladder","points","medals","ranks","is_editable","modified","image_id");
		$arrWhereParams = array("campaign_id","installed_campaign_id","default_installed","campaign_name","description","commitments","slogan","campaign_photo","campaign_gold_photo","campaign_black_photo","campaign_type","institution_id","is_active","ladder","points","medals","ranks","is_editable","created","modified","created_by","image_id");

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
			print "Sorry, there was an error: MB-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("campaigns", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _campaign_school_types_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"campaign_school_type_id"	=> @$arrParams["campaign_school_type_id"],
			"campaign_id"				=> @$arrParams["campaign_id"],
			"school_type"				=> @$arrParams["school_type"]
		);

		$strSql = "
			SELECT
				*
			FROM
				campaign_school_types
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
					|| is_string($Value)
					|| $Value
				)
			) {
				if (
					is_array($Value)
					&& count($Value)
				) {
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (!is_int($Value1))
						{
							$Value1 = '"' . $Value1 . '"';
						}
						$arrValues[] = $Value1;

					}
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
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
		}
		if (isset($arrParams["_ORDER"]))
		{
			$strSql .= "
				ORDER BY
					" . $arrParams["_ORDER"];
		}
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _campaign_school_types_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"campaign_id"				=> @$arrParams["campaign_id"],
			"school_type"				=> @$arrParams["school_type"]
		);

		// Execute
		$boolResult = $this->_db->insert("campaign_school_types", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _campaign_school_types_update($arrParams)
	{
		$arrValuesParams = array("campaign_id", "school_type");
		$arrWhereParams = array("campaign_school_type_id", "campaign_id", "school_type");

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
			print "Sorry, there was an error: MC-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("campaign_school_types", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _campaign_school_types_delete($arrParams)
	{
		$arrWhereParams = array("campaign_school_type_id", "campaign_id", "school_type");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MB-BLD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("campaign_school_types", $arrFeilds);
		return $boolResult;
	}

	/*
	 * Add the campaigns that are set to be installed by default into a new institution
	 * Required: institution_id
	 */
	public function install_default_campaigns ($arrParams)
	{
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MC-IDC101-UDF8D9";
			exit;
		}
		if (!isset($arrParams["host_id"]))
			$arrParams["host_id"] = 1;

		$objCampaigns = new Campaigns();
		$objInstitutions = new Institutions();

		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $arrParams["institution_id"]
		)));
		if (!$objInstitution)
		{
			print "Sorry, there was an error: MC-IDC102-D8D8FA";
			exit;
		}
		$arrCampaigns = $objCampaigns->_campaigns_select(array(
			"default_installed" => 1,
			"institution_id" => $objInstitution->host_id
		));
		$arrResult = array();
		foreach ($arrCampaigns as $objCampaign)
		{
			if ($objCampaign->default_installed)
			{
				$arrPost = (array) $objCampaign;
				$arrPost["installed_campaign_id"] = $arrPost["campaign_id"];
				$arrPost["institution_id"] = $arrParams["institution_id"];
				unset($arrPost["campaign_id"]);
				unset($arrPost["created"]);
				$arrPost["created_by"] = 1;
				$this->_campaigns_insert($arrPost);
			}
		}
	}

	/*
	 * Added: schedule_date_min, schedule_date_max
	 */
	public function _user_campaigns_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_campaign_id"	=> @$arrParams["user_campaign_id"],
			"user_id"			=> @$arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"campaign_id"		=> @$arrParams["campaign_id"],
			"mission_id"		=> @$arrParams["mission_id"],
			"mission_increment" => @$arrParams["mission_increment"],
 			"task_id"			=> @$arrParams["task_id"],
			"class_id"			=> @$arrParams["class_id"],
			"book_id"			=> @$arrParams["book_id"],
			"task_increment"	=> @$arrParams["task_increment"],
			"status"			=> @$arrParams["status"],
			"line_offset"		=> @$arrParams["line_offset"],
			"ladder"			=> @$arrParams["ladder"],
			"ladder_velocity"	=> @$arrParams["ladder_velocity"],
			"grade_hierarchy"	=> @$arrParams["grade_hierarchy"],
			"grade_velocity"	=> @$arrParams["grade_velocity"],
			"schedule_date"		=> @$arrParams["schedule_date"],
			"input_value"		=> @$arrParams["input_value"],
			"points_given"		=> @$arrParams["points_given"],
			"created"			=> @$arrParams["created"],
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				user_campaigns
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
				if (is_array($Value) && !count($Value))
					return array();
				if (is_array($Value))
				{
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (!is_int($Value1))
						{
							$Value1 = '"' . $Value1 . '"';
						}
						$arrValues[] = $Value1;

					}
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
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
		}

		// Extras
		if (isset($arrParams["schedule_date_min"]))
			$strSql .= "
				AND schedule_date >= " . intval($arrParams["schedule_date_min"]);
		if (isset($arrParams["schedule_date_max"]))
			$strSql .= "
				AND schedule_date <= " . intval($arrParams["schedule_date_max"]);

		if (isset($arrParams["created_min"]))
			$strSql .= "
				AND unix_timestamp(created) >= " . intval($arrParams["created_min"]);
		if (isset($arrParams["created_max"]))
			$strSql .= "
				AND unix_timestamp(created) <= " . intval($arrParams["created_max"]);

		if (isset($arrParams["_ORDER"]))
		{
			$strSql .= "
				ORDER BY
					" . $arrParams["_ORDER"];
		}
		else
		{
			$strSql .= "
				ORDER BY
					task_increment + 0 DESC";
		}

		// Limits
		if (
			isset($arrParams["_LIMIT"])
			&& preg_match("/^[0-9,]+$/", $arrParams["_LIMIT"])
		) {
			$strSql .= "
				LIMIT
					" . $arrParams["_LIMIT"];
		}

		//print $strSql;exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _user_campaigns_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}
		if (isset($arrParams["created"]) && is_int($arrParams["created"]))
		{
			$arrParams["created"] = date("Y-m-d H:i:S", $arrParams["created"]);
		}
		else
		{
			$arrParams["created"] = date("Y-m-d H:i:S");
		}
		$arrFeilds = array (
			"user_campaign_id"		=> @$arrParams["user_campaign_id"],
			"user_id"				=> @$arrParams["user_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"campaign_id"			=> @$arrParams["campaign_id"],
			"mission_id"			=> @$arrParams["mission_id"],
			"mission_increment"		=> @$arrParams["mission_increment"],
			"task_id"				=> @$arrParams["task_id"],
			"class_id"				=> @$arrParams["class_id"],
			"book_id"				=> @$arrParams["book_id"],
			"task_increment"		=> @$arrParams["task_increment"],
			"status"				=> @$arrParams["status"],
			"line_offset"			=> @$arrParams["line_offset"],
			"grade_hierarchy"		=> @$arrParams["grade_hierarchy"],
			"grade_velocity"		=> @$arrParams["grade_velocity"],
			"schedule_date"			=> @$arrParams["schedule_date"],
			"ladder"				=> @$arrParams["ladder"],
			"ladder_velocity"		=> @$arrParams["ladder_velocity"],
			"input_value"			=> @$arrParams["input_value"],
			"points_given"			=> @$arrParams["points_given"],
			"created"				=> @$arrParams["created"],
			"created_by"			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("user_campaigns", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _user_campaigns_update($arrParams)
	{

		$arrValuesParams = array("institution_id","campaign_id","mission_id","mission_increment","task_id","class_id","book_id","task_increment","grade_hierarchy","grade_velocity","line_offset","ladder","ladder_velocity","input_value","status","modified");
		$arrWhereParams = array("user_campaign_id","user_id","institution_id","campaign_id","mission_id","mission_increment","task_id","book_id","task_increment","grade_hierarchy","grade_velocity","line_offset","ladder","ladder_velocity","input_value","status","created","modified","created_by");

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
			print "Sorry, there was an error: MB-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("user_campaigns", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _user_campaigns_delete($arrParams)
	{
		$arrWhereParams = array("user_campaign_id","user_id","institution_id","campaign_id","mission_id","task_id","class_id","book_id","task_increment","grade_hierarchy","grade_velocity","line_offset","ladder","ladder_velocity","input_value","status","created","modified","created_by");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MC-UCD101-45RT5R";
			exit;
		}
		$boolResult = $this->_db->delete("user_campaigns", $arrFeilds);
		return $boolResult;
	}

	public function user_campaign_current_line($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrParams["campaign_id"] = intval(@$arrParams["campaign_id"]);
		if (!$arrParams["campaign_id"])
		{
			print "Sorry, there was an error: MC-UCCL101-90SDSS";
			exit;
		}

		if (isset($arrParams["start_date"]) && $arrParams["start_date"])
			$arrParams["created_min"] = $arrParams["start_date"];
		if (isset($arrParams["end_date"]) && $arrParams["end_date"])
			$arrParams["created_max"] = $arrParams["end_date"];
		$arrTempParams = $arrParams;
		$arrTempParams["status"] = "Enrollment";
		unset($arrTempParams["start_date"]);
		unset($arrTempParams["end_date"]);
		$arrCampaignEnrolled = array_hash("user_id", $this->_user_campaigns_select($arrTempParams));
		if (!count($arrCampaignEnrolled))
			return array();
		$strSql = "
			SELECT
				MAX(task_increment) AS max_increment,
				user_campaigns.*
			FROM
				user_campaigns
			WHERE
				user_campaigns.user_id IN (" . join(",", array_keys($arrCampaignEnrolled)) . ")
			GROUP BY
				user_id";
		$arrResult = $this->_db->fetchAll($strSql);
		foreach ($arrResult as $intKey => $objResult)
		{
			$arrResult[$intKey]->current_line =
				intval($objResult->max_increment) < intval($objResult->line_offset)
				? intval($objResult->line_offset)
				: intval($objResult->max_increment);
		}
		return $arrResult;
	}

	public function rule_filter_campaign_object($arrCampaigns, $strInstitutionType=0)
	{
		$strInstitutionType = $strInstitutionType ? $strInstitutionType : $this->_user_session_data->template_style;
		$objRules = new Rules();
		$query = new QueryGen();
		$arrResult = array();
		$arrCampaignSchoolTypes = array_bubble_hash('campaign_id', 'school_type', $query->campaign_school_types__select(array(
			'campaign_id' => array_stack('campaign_id', $arrCampaigns)
		)));
		foreach ($arrCampaigns as $objCampaign)
		{
			if (
				!isset($arrCampaignSchoolTypes[$objCampaign->campaign_id])
				|| isset($arrCampaignSchoolTypes[$objCampaign->campaign_id][$strInstitutionType])
			)
				$arrResult[] = $objCampaign;
		}
		return $arrResult;
	}

	/*
	 * Required: user_id
	 */
	public function is_old_tanya_user($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		$strSql = "
			SELECT
				*
			FROM
				zzz_tanya_users_legacy
			WHERE
				user_id = " . $arrParams["user_id"];

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function select_user_campaign_created_epoch ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_campaign_id"	=> @$arrParams["user_campaign_id"],
			"user_id"			=> @$arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"campaign_id"		=> @$arrParams["campaign_id"],
			"mission_id"		=> @$arrParams["mission_id"],
			"mission_increment" => @$arrParams["mission_increment"],
 			"task_id"			=> @$arrParams["task_id"],
			"task_increment"	=> @$arrParams["task_increment"],
			"grade_hierarchy"	=> @$arrParams["grade_hierarchy"],
			"grade_velocity"	=> @$arrParams["grade_velocity"],
			"schedule_date"		=> @$arrParams["schedule_date"],
			"ladder"			=> @$arrParams["ladder"],
			"ladder_velocity"	=> @$arrParams["ladder_velocity"],
			"input_value"		=> @$arrParams["input_value"],
			"status"			=> @$arrParams["status"],
			"created"			=> @$arrParams["created"],
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				schedule_date, user_campaign_id
			FROM
				user_campaigns
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

		if (isset($arrParams["_ORDER"]))
		{
			$strSql .= "
				ORDER BY
					" . $arrParams["_ORDER"];
		}
		else
		{
			$strSql .= "
				ORDER BY
					task_increment + 0 DESC";
		}

		// Limits
		if (
			isset($arrParams["_LIMIT"])
			&& preg_match("/^[0-9,]+$/", $arrParams["_LIMIT"])
		) {
			$strSql .= "
				LIMIT
					" . $arrParams["_LIMIT"];
		}

		//print $strSql;exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function velocity_insert ($arrParams)
	{
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MC-VI101-ASD98A";
			exit;
		}
		// select current campaign
		$objCampaign = current($this->_campaigns_select(
			array(
				"campaign_id" => $arrParams["campaign_id"]
			)
		));
		$intInstitution = $objCampaign->institution_id;

		// load the grades
		$objGrades = new Grades();
		$arrGrades = $objGrades->_grades_select_hierarchal(
			array (
				"institution_id" => $intInstitution
			)
		);

		$intLaddersTotal = $objCampaign->ladder;

		// loop through the params for possible inserts
		$strDate = date("Y-m-d H:i:s");
		$strWhere = "campaign_id=" . $objCampaign->campaign_id;
		$this->_db->delete("velocity_grades", $strWhere);
		foreach ($arrGrades as $objGrade)
		{
			$strGradeValue = $arrParams["grade_" . $objGrade->grade_hierarchy];
			$arrGradesInsert = array(
				"campaign_id"		=> $objCampaign->campaign_id,
				"grade_hierarchy"	=> $objGrade->grade_hierarchy,
				"velocity"			=> $strGradeValue,
				"created"			=> $strDate,
				"created_by"		=> $this->_user_session_data->user_id
			);
			//var_dump($arrGradesInsert);
			$this->_db->insert("velocity_grades", $arrGradesInsert);
		}

		$strWhere = "campaign_id=" . $objCampaign->campaign_id;
		$this->_db->delete("velocity_ladders", $strWhere);
		for ($intLadder=0;$intLadder!=$intLaddersTotal;$intLadder++)
		{
			$strLadderValue = $arrParams["ladder_" . $intLadder];
			$arrLadderInsert = array(
				"campaign_id"			=> $objCampaign->campaign_id,
				"ladder"				=> $intLadder,
				"velocity"				=> $strLadderValue,
				"created"				=> $strDate,
				"created_by"			=> $this->_user_session_data->user_id
			);
			//var_dump($arrGradesInsert);
			$this->_db->insert("velocity_ladders", $arrLadderInsert);
		}
		print 1;
	}

	/*
	 * Collect the campaigns that the provided users are currently enrolled into.
	 * Required: user_ids
	 */
	public function user_enrolled_campaigns ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_ids"]))
		{
			print "Sorry, there was an error: MC-UEC101-678DFG";
			exit;
		}
		if (!is_array($arrParams["user_ids"]))
		{
			// user_ids should be an array of ids ex: array(345345,745345,458665,854454)
			print "Sorry, there was an error: MC-UEC102-FGH6GG";
			exit;
		}
		if (!count($arrParams["user_ids"]))
		{
			return array();
		}

		$strSql = "
			SELECT
				campaigns.*
			FROM
				campaigns,
				user_campaigns
			WHERE
				user_campaigns.user_id IN (" . join(",", $arrParams["user_ids"]) . ")
				AND user_campaigns.status = 'Enrollment'
				AND campaigns.campaign_id = user_campaigns.campaign_id
			GROUP BY
				campaigns.campaign_id";

		$arrResult = $this->_db->fetchAll($strSql);

		return $arrResult;
	}

	///// [ CAMPAIGNS_TABLE ] /////////////////////////////////////////////////////////////////////////////

	public function campaign_insert ($arrQuery)
	{

		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the insert
		if (!isset($arrQuery["installed_campaign_id"]))
			$arrQuery["installed_campaign_id"] = 0;

		$arrFeilds = array (
			"installed_campaign_id" => $arrQuery["installed_campaign_id"],
			"default_installed"   	=> @$arrQuery["default_installed"],
			"campaign_name"   		=> @$arrQuery["campaign_name"],
			"image_largemed"   		=> @$arrQuery["image_largemed"],
			"image_smallmed"   		=> @$arrQuery["image_smallmed"],
			"image_achievement"   	=> @$arrQuery["image_achievement"],
			"description"  			=> @$arrQuery["description"],
			"commitments"  			=> @$arrQuery["commitments"],
			"slogan"  				=> @$arrQuery["slogan"],
			"campaign_type"  		=> @$arrQuery["campaign_type"],
			"institution_id"  		=> @$arrQuery["institution_id"],
			"is_active"  			=> 1,
			"ladder"          		=> @$arrQuery["ladder"],
			"points"          		=> @$arrQuery["points"],
			"ranks"           		=> @$arrQuery["ranks"],
			"points"          		=> @$arrQuery["points"],
			"medals"          		=> @$arrQuery["medals"],
			"is_editable"     		=> @$arrQuery["is_editable"],
			"created"         		=> $intCurrentDate,
			"created_by"      		=> $this->_user_session_data->user_id
		);

		// Execute
		$boolResult = $this->_db->insert("campaigns", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function campaign_update ($arrQuery, $intId)
	{
		$intCurrentDate = date("Y-m-d H:i:S");
		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the update
		$arrFeilds = array ();
		if (isset($arrQuery["book_id"]))
			$arrFeilds["book_id"] = $arrQuery["book_id"];
		if (isset($arrQuery["campaign_name"]))
			$arrFeilds["campaign_name"] = $arrQuery["campaign_name"];
		if (isset($arrQuery["description"]))
			$arrFeilds["description"] = $arrQuery["description"];
		if (isset($arrQuery["campaign_type"]))
			$arrFeilds["campaign_type"] = $arrQuery["campaign_type"];
		if (isset($arrQuery["institution_id"]))
			$arrFeilds["institution_id"] = $arrQuery["institution_id"];
		if (isset($arrQuery["ladder"]))
			$arrFeilds["ladder"] = $arrQuery["ladder"];
		if (isset($arrQuery["is_editable"]))
			  $arrFeilds["is_editable"] = $arrQuery["is_editable"];
		if (isset($arrQuery["points"]))
			  $arrFeilds["points"] = $arrQuery["points"];
		if (isset($arrQuery["medals"]))
			  $arrFeilds["medals"] = $arrQuery["medals"];
		if (isset($arrQuery["ranks"]))
			  $arrFeilds["ranks"] = $arrQuery["ranks"];
		// Execute
		$intResult = $this->_db->update("campaigns", $arrFeilds, "campaign_id=" . $intId);
		return $intResult;
	}

	public function campaign_select_id ($intId)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				campaign_id=" . $intId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function campaign_select_name ($strName, $intInstitution)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				campaign_name=\"" . $strName . "\"
				AND institution_id=" . $intInstitution;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function campaigns_select_hosts()
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				host_id=0
				AND is_active = 1";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function campaigns_select_networks ($intHost=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				" . (
					$intHost
					? "host_id=$intHost"
					: "host_id!=0"
				) . "
				AND network_id=0
				AND is_active = 1";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function campaigns_select_institutions ($intHost=0, $intNetworks=0, $intInstitutions=0, $status=1)
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				" . (
					$intHost
					? "host_id=$intHost"
					: "host_id!=0"
				) . "
				 " . (
					$intNetworks
					? " AND network_id=$intNetworks"
					: "AND network_id!=0"
				) . "
				 " . (
					$intInstitutions
					? " AND institution_id=$intInstitutions"
					: "AND institution_id!=0"
				) . "
				AND is_active = ".$status;


		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/**
	 * Function selects all campaigns based on institution id.
	 *
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function campaigns_select_by_institution_id($institution_id)
	{

		if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM campaigns';
		}else{
			$utility = new Utilities();
			$childIds = $utility->getChildInstitutions($institution_id);
			$sql = 'SELECT * FROM campaigns WHERE institution_id IN ('.$childIds.')';
		}

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-CSBII-JHSGDT";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		//echo $sql; exit;
		return $result;
	}

	/**
	 * Function selects all campaigns that belong to either host, network or institution id.
	 *
	 * @param int $host_id
	 * @param int $network_id
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function campaigns_select_by_institution_ids($host_id, $network_id, $institution_id)
	{
		$sql = '
			SELECT * FROM campaigns
			WHERE institution_id IN
			(
				'.$host_id. ',
				'.$network_id.',
				'.$institution_id.'
			)';


		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-CSBII-JHSGDT";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}

		return $result;
	}

	public function campaigns_select_institution($intInstitution='', $active=-1)
	{
		if (!$intInstitution)
			$intInstitution = $this->_user_session_data->institution_id;

		if($active!=-1){
			$sqlXtra = ' AND is_active = '.$active;
		}
		else{
			$sqlXtra = '';
		}

		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				institution_id=" . $intInstitution . $sqlXtra;

		//echo $strSql; exit;

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function campaigns_select_templates($intHost, $intInstitution='', $active=0)
	{
		$strSql = "
			SELECT
				campaigns.*,
				ic.campaign_id AS installed
			FROM
				campaigns
			LEFT JOIN
				campaigns AS ic ON (
					campaigns.campaign_id=ic.installed_campaign_id
					AND ic.institution_id=" . $intInstitution . "
				)
			WHERE
				campaigns.institution_id=" . $intHost . "
				AND campaigns.is_active = 1";

		//echo $strSql; exit;

		try{
			$arrResult = $this->_tools->cleanSlashes($this->_db->fetchAll($strSql));
		}catch(Zend_Exception $e){
			echo "there was an error: MC-CST-123-DJHFGT";
			if(DEV_ENV=="devel") echo $strSql;
		}
		return $arrResult;
	}

	public function campaigns_install($intCampaign, $intInstituion)
	{
		$query = new QueryGen();
		$objTasks = new Tasks();
		$objMissions = new Missions();

		$intCampaign = intval($intCampaign);
		if (!$intCampaign)
		{
			print "Sorry, there was an error: CC-CI101-7SDFDD";
			exit;
		}

		// Load campaign
		$arrCampaign = (array) current($this->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$arrCampaign)
		{
			print "Sorry, there was an error: CC-CI102-SDF7DD";
			exit;
		}

		// Install campaign
		$arrCampaign["institution_id"] = $intInstituion;
		$arrCampaign["installed_campaign_id"]	= $arrCampaign["campaign_id"];
		$arrCampaign["campaign_id"] = false;
		$arrCampaign["created_by"] = $this->_user_session_data->user_id;
		$intCampaignAI = $query->campaigns__insert($arrCampaign);


		// Load mission
		$arrMission = (array) current($objMissions->_missions_select(array(
			"campaign_id" => $arrCampaign["campaign_id"]
		)));
		if (!$arrMission)
		{
			print "Sorry, there was an error: CC-CI103-345TER";
			exit;
		}

		// Install mission
		$arrMission["institution_id"] = $intInstituion;
		$arrMission["installed_mission_id"] = $arrMission["mission_id"];
		$arrMission["campaign_id"] = $intCampaignAI;
		$arrMission["mission_id"] = false;
		$arrMission["created_by"] = $this->_user_session_data->user_id;
		$intMission = $objMissions->mission_insert($arrMission);

		$arrTasks = $objTasks->_tasks_select(array(
			"campaign_id" => $intCampaign
		));
		foreach ($arrTasks as $objTask)
		{
			$arrTask = (array) $objTask;
			$arrTask["institution_id"] = $intInstituion;
			$arrTask["installed_task_id"] = $arrTask["task_id"];
			$arrTask["campaign_id"] = $intCampaignAI;
			$arrTask["mission_id"] = $intMission;
			$arrTask["task_id"] = false;
			$arrTask["created_by"] = $this->_user_session_data->user_id;
			$objTasks->task_insert($arrTask);
		}

		return $intCampaignAI;

	}

	public function campaigns_uninstall($intCampaignId, $intInstituion)
	{
		$intCampaignId = intval($intCampaignId);
		if (!$intCampaignId)
		{
			print "Sorry, there was an error: MC-UC102-7SDFD7";
			exit;
		}

		$objCampaign = current($this->_campaigns_select(array(
			"campaign_id" => $intCampaignId
		)));
		if (!$objCampaign)
		{
			print "Sorry, there was an error: MC-CU101-SD6D6D-" . $intCampaignId;
			exit;
		}

		if ($objCampaign)
		{
			$intTemplateCampaignId = $objCampaign->installed_campaign_id;

			$strSql = "campaign_id=" . $intCampaignId;
			$boolResult = $this->_db->delete("campaigns", $strSql);
			if (!$boolResult)
				$intTemplateCampaignId = 0;
		}
		if ($intTemplateCampaignId)
		{
			$strSql = "campaign_id=" . $intCampaignId;
			$boolResult = $this->_db->delete("missions", $strSql);
			if (!$boolResult)
			{
				// None fatal error
			}
			$strSql = "campaign_id=" . $intCampaignId;
			$boolResult = $this->_db->delete("tasks", $strSql);
			if (!$boolResult)
			{
				// None fatal error
			}
			return $intTemplateCampaignId;
		}


	}

	// $arrExtra
	public function campaigns_select ($boolStatus=1, $arrExtra=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				is_active=" . $boolStatus;

		/*
			Using arrExtra, an institution can be selected by id or using a host id
			and/or network id.
		*/

		$arrSql = array(); // All exceptions within this array will be OR joined
		if (
			isset($arrExtra["institution_id"])
			&& !isset($arrExtra["host_id"])
			&& !isset($arrExtra["network_id"])
		)
			$arrSql[] = "institution_id = " . $arrExtra["institution_id"];
		if (
			isset($arrExtra["host_id"])
			|| isset($arrExtra["network_id"])
		) {
			$strSubSql = "
				SELECT
					institution_id
				FROM
					institutions
				WHERE
					";
			$arrSubSql = array();
			if ( // Select host
				isset($arrExtra["host_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["host_id"]}
					AND host_id = 0
					AND network_id = 0
				)";
			}
			if ( // Select network
				isset($arrExtra["network_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["network_id"]}
					AND network_id = 0
					AND host_id != 0
				)";
			}
			if ( // Select institution
				isset($arrExtra["institution_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["institution_id"]}
					AND network_id != 0
					AND host_id != 0
				)";
			}
			$arrSql[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
		}
		if (count($arrSql)) {
			$strSql .= " AND (" . join(" OR ", $arrSql) . ")";
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function campaign_usercampaign_add($arrFields)
	{

		try{
			$intResult = $this->_db->insert("user_campaigns", $arrFields);
		}
		catch(Zend_Exception $e){
			echo "There was an error: MC-CUA-101-JFKDO9";
			if(DEV_ENV == "devel") print_r($arrFields);
		}

		return $intResult;
	}

	/**
	 * Deletes all records from user_campaigns that are associated with
	 * a given campaign_id / user_id
	 *
	 * @param int $user_id
	 * @param int $campaign_id
	 *
	 * @return bool
	 *
	 */
	public function campaign_usercampaign_delete($user_id, $campaign_id)
	{
		$sql = '
		DELETE FROM user_campaigns
		WHERE user_campaigns.campaign_id = '.$campaign_id.'
		AND user_campaigns.user_id = '.$user_id;

		try{
			$intResult = $this->_db->query($sql);
		}
		catch(Zend_Exception $e){
			echo "There was an error: MC-CUD-101-JK87TF";
			if(DEV_ENV == "devel") echo $sql;
		}

		return $intResult;
	}

	/**
	 *
	 * Selects all campaigns assigned to user_id
	 *
	 * @param int user_id
	 *
	 * @return arr arrResult
	 *
	 **/
	public function campaign_select_userId($user_id)
	{
		$strSql = '
		SELECT * FROM campaigns
		INNER JOIN user_campaigns
		ON campaigns.campaign_id = user_campaigns.campaign_id
		WHERE user_campaigns.user_id = '.$user_id;


		try{
			$arrResult = $this->_db->fetchAll($strSql);
		} catch(Zend_Exception $e){
			echo 'There was an error: MC-CSU101-JDH7EO';
			if(DEV_ENV == 'devel') echo $strSql;
		}

		return $arrResult;
	}

	/**
	 * Function returns either all the campaigns belonging to user_id
	 * or all campaigns that are not yet assigned to user_id
	 *
	 * @param:	int user_id
	 * @param:	int institution_id
	 * $param:	str mode specifies whether we query assigned or unassigned campaigns
	 * @para:	int active (1 active, 0 inactive)
	 *
	 * return: bool arrResult
	 */
	public function campaign_select_assigned($user_id, $institution_id, $showAssigned, $active)
	{


		$strWhere = ($showAssigned) ? " user_campaigns.user_id = ".$user_id : " user_campaigns.user_id != ".$user_id;
		$strSql = '
		SELECT * FROM campaigns
		INNER JOIN user_campaigns
		ON campaigns.campaign_id = user_campaigns.campaign_id
		WHERE '.$strWhere.'
		AND campaigns.institution_id = '.$institution_id.'
		AND campaigns.is_active = '.$active;

		try{
			$arrResult = $this->_db->fetchAll($strSql);
		} catch(Zend_Exception $e){
			echo 'There was an error: MC-CSA102-SDAFGG';
			if(DEV_ENV == 'devel') echo $strSql;
		}

		return $arrResult;
	}

	public function campaign_types_select() {
		$strSql = "
			SELECT
				*
			FROM
				 campaign_types
		";
		$arrResults = $this->_db->fetchAll($strSql);
		return $arrResults;
	}

	///// [ MISSIONS_TABLE ] /////////////////////////////////////////////////////////////////////////////

	public function mission_insert ($arrQuery) {

		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the insert
		$arrFeilds = array (
			"mission_name" => $arrQuery["mission_name"],
			"mission_type" => $arrQuery["mission_type"],
			"campaign_id" => $arrQuery["campaign_id"],
			"institution_id" => $arrQuery["institution_id"],
			"grade" => $arrQuery["grade"],
			"ladder" => $arrQuery["ladder"],
			"start_date" => $arrQuery["start_date"],
			"end_date" => $arrQuery["end_date"],
			"points_up" => $arrQuery["points_up"],
			"medal_up" => $arrQuery["medal_up"],
			"rank_up" => $arrQuery["rank_up"],
			"sequence" => $arrQuery["sequence"],
			"created" => $intCurrentDate,
			"created_by" => $this->_user_session_data->user_id
		);

		// Execute
		$intResult = $this->_db->insert("missions", $arrFeilds);
		return $intResult;
	}

	public function mission_update ($arrQuery, $intId) {

		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the update
		$arrFeilds = array ();
		if (isset($arrQuery["mission_name"]))
			$arrFeilds["mission_name"] = $arrQuery["mission_name"];
		if (isset($arrQuery["mission_type"]))
			$arrFeilds["mission_type"] = $arrQuery["mission_type"];
		if (isset($arrQuery["campaign_id"]))
			$arrFeilds["campaign_id"] = $arrQuery["campaign_id"];
		if (isset($arrQuery["institution_id"]))
			$arrFeilds["institution_id"] = $arrQuery["institution_id"];
		if (isset($arrQuery["grade"]))
			$arrFeilds["grade"] = $arrQuery["grade"];
		if (isset($arrQuery["ladder"]))
			$arrFeilds["ladder"] = $arrQuery["ladder"];
		if (isset($arrQuery["start_date"]))
			$arrFeilds["start_date"] = $arrQuery["start_date"];
		if (isset($arrQuery["end_date"]))
			$arrFeilds["end_date"] = $arrQuery["end_date"];
		if (isset($arrQuery["points_up"]))
			$arrFeilds["points_up"] = $arrQuery["points_up"];
		if (isset($arrQuery["medal_up"]))
			$arrFeilds["medal_up"] = $arrQuery["medal_up"];
		if (isset($arrQuery["rank_up"]))
			$arrFeilds["rank_up"] = $arrQuery["rank_up"];
		if (isset($arrQuery["sequence"]))
			$arrFeilds["sequence"] = $arrQuery["sequence"];

		// Execute
		$intResult = $this->_db->update("missions", $arrFeilds, "mission_id=" . $intId);
		return $intResult;
	}

	public function mission_select_campaign_institution_id($intCampaignParam, $intInstitutionParam)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				" . (
					$intCampaignParam
					? "campaign_id=$intCampaignParam"
					: "campaign_id!=0"
				) . "
				AND
				" .(
					$intInstitutionParam
					? "institution_id=$intInstitutionParam"
					: "institution_id!=0"
				) . " AND is_active = 1";

		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}
	public function mission_select_campaigns ($intInstitutions=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				" . (
					$intInstitutions
					? "institution_id=$intInstitutions"
					: "institution_id!=0"
				) . "
				AND is_active = 1";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function missions_select($boolStatus=1, $arrExtra=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				missions
			WHERE
				is_active=" .$boolStatus;

		/*
			Using arrExtra, an institution can be selected by id or using a host id
			and/or network id.
		*/

		$arrSql = array(); // All exceptions within this array will be AND joined
		if (
			isset($arrExtra["institution_id"])
			&& !isset($arrExtra["host_id"])
			&& !isset($arrExtra["network_id"])
			&& !isset($arrExtra["campaign_id"])
		)
			$arrSql[] = "institution_id = " . $arrExtra["institution_id"];
		if(isset($arrExtra["campaign_id"]))
		{
			$arrSql[] = "campaign_id = " . $arrExtra["campaign_id"];
		}
		if(!empty($arrSql))
		{
			$strSql .= " AND (" . join(" AND ", $arrSql) . ")";
		}

		$arrSql = array(); // All exceptions within this array will be OR joined
		if (
			isset($arrExtra["host_id"])
			|| isset($arrExtra["network_id"])
		) {
			$strSubSql = "
				SELECT
					institution_id
				FROM
					institutions
				WHERE
					";
			$arrSubSql = array();
			if ( // Select host
				isset($arrExtra["host_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["host_id"]}
					AND host_id = 0
					AND network_id = 0
				)";
			}
			if ( // Select network
				isset($arrExtra["network_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["network_id"]}
					AND network_id = 0
					AND host_id != 0
				)";
			}
			if ( // Select institution
				isset($arrExtra["institution_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["institution_id"]}
					AND network_id != 0
					AND host_id != 0
				)";
			}
			$arrSql[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
		}
		if (count($arrSql)) {
			$strSql .= " AND (" . join(" OR ", $arrSql) . ")";
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function mission_select_id($intId) {
		if (!$intId) {
			return 0;
		}
		$strSql = "
			SELECT
				*
			FROM
				missions
			WHERE
				mission_id=" . $intId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function mission_select_name($strName, $intInstitution, $intCampaign) {
		$strSql = "
			SELECT
				*
			FROM
				missions
			WHERE
				mission_name=\"" . $strName . "\"
				AND institution_id=" . $intInstitution . "
				AND campaign_id=" . $intCampaign;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function mission_types_select() {
		$strSql = "
			SELECT
				*
			FROM
				 mission_types
		";
		$arrResults = $this->_db->fetchAll($strSql);
		return $arrResults;
	}
	public function mission_select_campaign_id($arrExtra=0)
	{
		$strSql = "
				Select
					*
				from
					missions
				where
					is_active=1";

		/*
			Using arrExtra, an institution can be selected by id or using a host id
			and/or network id.
		*/
		$arrSql = array(); // All exceptions within this array will be OR joined
		if (
			isset($arrExtra["institution_id"])
			&& !isset($arrExtra["host_id"])
			&& !isset($arrExtra["network_id"])
			&& !isset($arrExtra["campaign_id"])
		)
			$arrSql[] = "institution_id = " . $arrExtra["institution_id"];
			$arrSql[].= (isset($arrExtra["campaign_id"]) ? "campaign_id = " . $arrExtra["campaign_id"] : "campaign_id!=0");
			//$arrSql[] .= "campaign_id = " . $arrExtra["campaign_id"];
		if (
			isset($arrExtra["host_id"])
			|| isset($arrExtra["network_id"])
		) {
			$strSubSql = "
				SELECT
					institution_id
				FROM
					institutions
				WHERE
					";
			$arrSubSql = array();
			if ( // Select host
				isset($arrExtra["host_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["host_id"]}
					AND host_id = 0
					AND network_id = 0
				)";
			}
			if ( // Select network
				isset($arrExtra["network_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["network_id"]}
					AND network_id = 0
					AND host_id != 0
				)";
			}
			if ( // Select institution
				isset($arrExtra["institution_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["institution_id"]}
					AND network_id != 0
					AND host_id != 0
				)";
			}
			$arrSql[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
		}
		if (count($arrSql)) {
			$strSql .= " AND (" . join(" OR ", $arrSql) . ")";
		}

		$arrResults = $this->_db->fetchAll($strSql);
		return $arrResults;
	}
	public function mission_select_institution_id($intCampaignParam=0, $intInstitutionParam=0)
	{
		$strSql = "
				Select
					*
				from
					missions
				where " . (
					$intCampaignParam
					? "campaign_id=$intCampaignParam"
					: "campaign_id!=0"
				). " AND " .(
					$intInstitutionParam
					? "institution_id=$intInstitutionParam"
					: "institution_id!=0"
				). " AND is_active=1";

		$arrResults = $this->_db->fetchRow($strSql);
		return $arrResults;
	}

	///// [ TASKS_TABLE ] /////////////////////////////////////////////////////////////////////////////


	public function tasks_scale_get_ladders($campaign_id, $grade)
	{
		$sql = '
		SELECT DISTINCT(ladder) FROM tasks_scale
		WHERE grade = '.$grade.'
		AND campaign_id = '.$campaign_id.'
		ORDER BY ladder ASC';

		try{
			$result = $this->_db->fetchAll($sql);
		} catch(Zend_Exception $e){
			echo "There was an error: MC-TSGL-AKJU76";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}

		return $result;
	}

	public function task_select_campaign_id($intCampaignParam)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				campaign_id='$intCampaignParam'
				AND	is_active = 1";
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function task_select_mission_id($intCampaign,$intMission,$intInstitution)
	{
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE " .(
				$intCampaignParam
				? "campaign_id=$intCampaignParam"
				: "campaign_id!=0"
				). "
				AND " .(
				$intMissionParam
				? "mission_id=$intMissionParam"
				: "mission_id=!0"
				). "
				AND " .(
				$intInstitutionParam
				? "institution_id=$intInstitutionParam"
				: "institution_id!=0"
				). "
				AND	is_active = 1";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function task_select_institution_id($intInstitutionParam)
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				institution_id='$intInstitutionParam'";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function task_select_missions($intInstitution=0, $intCampaign=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				missions
			WHERE
				" .(
					$intInstitution
					? "institution_id=$intInstitution"
					: "institution_id!=0"
				) . "
				AND "
				  .(
				  	$intCampaign
					? "campaign_id=$intCampaign"
					: "campaign_id!=0"
				). "
				AND is_active = 1";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function task_insert ($arrQuery) {
		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the insert
		$arrFeilds = array (
			"task_name" => $arrQuery["task_name"],
			"mission_id" => $arrQuery["mission_id"],
			"campaign_id" => $arrQuery["campaign_id"],
			"institution_id" => $arrQuery["institution_id"],
			"points" => $arrQuery["points"],
			"frequency" => $arrQuery["frequency"],
			"start_date" => $arrQuery["start_date"],
			"end_date" => $arrQuery["end_date"],
			"start_time" => $arrQuery["start_time"],
			"end_time" => $arrQuery["end_time"],
			"sequence" => $arrQuery["sequence"],
			"created" => $intCurrentDate,
			"created_by" => $this->_user_session_data->user_id
		);

		// Execute
		$intResult = $this->_db->insert("tasks", $arrFeilds);
		return $intResult;
	}


	public function task_update ($arrQuery, $intId) {

		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the update
		$arrFeilds = array ();
		if (isset($arrQuery["task_name"]))
			$arrFeilds["task_name"] = $arrQuery["task_name"];
		if (isset($arrQuery["mission_id"]))
			$arrFeilds["mission_id"] = $arrQuery["mission_id"];
		if (isset($arrQuery["campaign_id"]))
			$arrFeilds["campaign_id"] = $arrQuery["campaign_id"];
		if (isset($arrQuery["institution_id"]))
			$arrFeilds["institution_id"] = $arrQuery["institution_id"];
		if (isset($arrQuery["points"]))
			$arrFeilds["points"] = $arrQuery["points"];
		if (isset($arrQuery["frequency"]))
			$arrFeilds["frequency"] = $arrQuery["frequency"];
		if (isset($arrQuery["start_date"]))
			$arrFeilds["start_date"] = $arrQuery["start_date"];
		if (isset($arrQuery["end_date"]))
			$arrFeilds["end_date"] = $arrQuery["end_date"];
		if (isset($arrQuery["start_time"]))
			$arrFeilds["start_time"] = $arrQuery["start_time"];
		if (isset($arrQuery["end_time"]))
			$arrFeilds["end_time"] = $arrQuery["end_time"];
		if (isset($arrQuery["sequence"]))
			$arrFeilds["sequence"] = $arrQuery["sequence"];

		// Execute
		$intResult = $this->_db->update("tasks", $arrFeilds, "task_id=" . $intId);
		return $intResult;
	}

	public function tasks_select($boolStatus=1, $arrExtra=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE
				is_active=" .$boolStatus;

		/*
			Using arrExtra, an institution can be selected by id or using a host id
			and/or network id.
		*/

		$arrSql = array(); // All exceptions within this array will be AND joined
		if (
			isset($arrExtra["institution_id"])
			&& !isset($arrExtra["host_id"])
			&& !isset($arrExtra["network_id"])
			&& !isset($arrExtra["campaign_id"])
			&& !isset($arrExtra["mission_id"])
		)
			$arrSql[] = "institution_id = " . $arrExtra["institution_id"];
		if(isset($arrExtra["campaign_id"]))
		{
			$arrSql[] = "campaign_id = " . $arrExtra["campaign_id"];
		}
		if(isset($arrExtra["mission_id"]))
		{
			$arrSql[] = "mission_id = " . $arrExtra["mission_id"];
		}
		if(!empty($arrSql))
		{
			$strSql .= " AND (" . join(" AND ", $arrSql) . ")";
		}
		$arrSql = array(); // All exceptions within this array will be OR joined
		if (
			isset($arrExtra["host_id"])
			|| isset($arrExtra["network_id"])
		) {
			$strSubSql = "
				SELECT
					institution_id
				FROM
					institutions
				WHERE
					";
			$arrSubSql = array();
			if ( // Select host
				isset($arrExtra["host_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["host_id"]}
					AND host_id = 0
					AND network_id = 0
				)";
			}
			if ( // Select network
				isset($arrExtra["network_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["network_id"]}
					AND network_id = 0
					AND host_id != 0
				)";
			}
			if ( // Select institution
				isset($arrExtra["institution_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["institution_id"]}
					AND network_id != 0
					AND host_id != 0
				)";
			}
			$arrSql[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
		}
		if (count($arrSql)) {
			$strSql .= " AND (" . join(" OR ", $arrSql) . ")";
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function task_select_id ($intId) {
		if (!$intId) {
			return 0;
		}
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE
				task_id=" . $intId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function task_select_name ($strName, $intInstitution, $intCampaign, $intMission) {
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE
				task_name=\"" . $strName . "\"
				AND institution_id=" . $intInstitution . "
				AND campaign_id=" . $intCampaign . "
				AND mission_id=" . $intMission;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

    public function get_inactive_missions_by_campaign($intCampaignId)
    {
        $strSql = "SELECT * ";
        $strSql = $strSql . "FROM missions ";
        $strSql = $strSql . "WHERE campaign_id=" . $intCampaignId . " ";
        $strSql = $strSql . "AND is_active=0 ";
        $strSql = $strSql . "ORDER BY mission_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
    }

    public function get_active_missions_by_campaign($intCampaignId)
    {
        $strSql = "SELECT * ";
        $strSql = $strSql . "FROM missions ";
        $strSql = $strSql . "WHERE campaign_id=" . $intCampaignId . " ";
        $strSql = $strSql . "AND is_active=1 ";
        $strSql = $strSql . "ORDER BY mission_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
    }

    public function get_campaign_name($intCampaignId)
    {
		$strSql = "SELECT campaign_name FROM campaigns WHERE campaign_id=" . $intCampaignId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult->campaign_name;
    }

    public function get_active_tasks_by_campaign_id($intCampaignId)
    {
        $strSql = "SELECT * ";
        $strSql = $strSql . "FROM tasks AS t ";
        $strSql = $strSql . "JOIN missions AS m USING (mission_id) ";
        $strSql = $strSql . "WHERE m.campaign_id=" . $intCampaignId . " ";
        $strSql = $strSql . "AND t.is_active=1 ";
        $strSql = $strSql . "ORDER BY t.task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
    }

    public function get_inactive_tasks_by_campaign_id($intCampaignId)
    {
        $strSql = "SELECT * ";
        $strSql = $strSql . "FROM tasks AS t ";
        $strSql = $strSql . "JOIN missions AS m USING (mission_id) ";
        $strSql = $strSql . "WHERE m.campaign_id=" . $intCampaignId . " ";
        $strSql = $strSql . "AND t.is_active=0 ";
        $strSql = $strSql . "ORDER BY t.task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
    }

    public function get_host_and_network_id_by_campaign_id($intCampaignId)
    {
        $strSql = "SELECT i.host_id, i.network_id ";
        $strSql = $strSql . "FROM campaigns AS c ";
        $strSql = $strSql . "JOIN institutions AS i ON (c.institution_id=i.institution_id) ";
        $strSql = $strSql . "WHERE c.campaign_id=" . $intCampaignId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
    }

    public function get_campaign($intCampaignId)
    {
        $strSql = "SELECT * FROM campaigns WHERE campaign_id=" . $intCampaignId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
    }


    public function get_user_campaign($intUserId, $intCampaignId)
    {
        $strSql = "SELECT count(*) AS registered ";
        $strSql = $strSql . "FROM user_campaigns ";
        $strSql = $strSql . "WHERE user_id=" . $intUserId . " AND campaign_id=" . $intCampaignId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult->registered;

    }

    public function set_task_to_completed($arrFeilds)
    {
		if ($this->_db->insert("user_campaigns", $arrFeilds))
            return $this->_db->lastInsertId();
        else
            return 0;
    }

    public function remove_user_campaign($intUserCampaignId)
    {
		$strSql = "user_campaign_id=" . $intUserCampaignId;
		$boolResult = $this->_db->delete("user_campaigns", $strSql);

		if ($boolResult)
			return 1;
        else
           return 0;
    }

    public function set_campaign_photo($intCampaignId, $strPhotoType, $strRGB, $photo)
    {
		$strSql = "SELECT count(*) AS no_of_photos FROM campaign_photos WHERE campaign_id=" . $intCampaignId;
        //echo $strSql . "<br />";
		$objResult = $this->_db->fetchRow($strSql);

        echo "CAMPAIGN ID:" . $intCampaignId . "<br />";
        echo "PHOTO TYPE:" . $strPhotoType . "<br />";
        echo "RGB:" . $strRGB . "<br />";
		echo "no_of_photos:" . $objResult->no_of_photos . "<br />";

        if ($strRGB == "r")
        {
            $arrFeilds = array ("campaign_id"   => $intCampaignId,
                                "photo"         => file_get_contents($photo['tmp_name']),
                                "photo_type"    => $strPhotoType,
                                "created"       => date("Y-m-d H:i:S"));
        }
        elseif ($strRGB == "g")
        {
            $arrFeilds = array ("campaign_id"       => $intCampaignId,
                                "gold_photo"        => file_get_contents($photo['tmp_name']),
                                "gold_photo_type"   => $strPhotoType,
                                "created"           => date("Y-m-d H:i:S"));
        }
        elseif ($strRGB == "b")
        {
            $arrFeilds = array ("campaign_id"       => $intCampaignId,
                                "black_photo"       => file_get_contents($photo['tmp_name']),
                                "black_photo_type"  => $strPhotoType,
                                "created"           => date("Y-m-d H:i:S"));
        }

		if ($objResult->no_of_photos == 0)
        {
            $this->_db->insert("campaign_photos", $arrFeilds);
        }
        else
        {
            $this->_db->update("campaign_photos", $arrFeilds, "campaign_id=" . $intCampaignId);
        }
    }


    public function prizes_select()
    {
        $strSql = "SELECT * FROM prizes WHERE institution_id=5";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
    }

    public function ranks_select()
    {
        $strSql = "SELECT * FROM ranks";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
    }

    public function set_rank_photo($intRankId, $rbp, $strPhotoType, $photo)
    {
        //echo "RANK ID:" . $intRankId . "<br />";
        if ($rbp == "r")
        {
            $arrFeilds = array ("photo"         => file_get_contents($photo['tmp_name']),
                                "photo_type"    => $strPhotoType);
        }
        elseif ($rbp == "b")
        {
            $arrFeilds = array ("background_photo"         => file_get_contents($photo['tmp_name']),
                                "background_photo_type"    => $strPhotoType);
        }
        elseif ($rbp == "p")
        {
            $arrFeilds = array ("prof_photo"         => file_get_contents($photo['tmp_name']),
                                "prof_photo_type"    => $strPhotoType);
        }


        $this->_db->update("ranks", $arrFeilds, "rank_id=" . $intRankId);
    }

    public function get_template_campaigns_by_host_id($host_id, $institution_id)
    {
        $sql = "SELECT c.* , ic.campaign_id AS installed ";
        $sql = $sql . "FROM campaigns AS c ";
        $sql = $sql . "LEFT JOIN campaigns AS ic ON (ic.installed_campaign_id=c.campaign_id AND ic.institution_id=" . $institution_id . ") ";
        $sql = $sql . "WHERE c.institution_id=" . $host_id;

		//echo $sql; exit;

		$campaigns = $this->_db->fetchAll($sql);
		return $campaigns;
    }

    public function get_all_active_campaigns()
    {
        $sql = "SELECT * FROM campaigns WHERE is_active=1";
		$campaigns = $this->_db->fetchAll($sql);
		return $campaigns;
    }

	/**
	 * Gets all campaigns for any of the passed parameters. Used in front-end
	 *
	 * @param int $host_id
	 * @param int network_id
	 * @param int institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function get_campaigns_for_kiosk($host_id=0, $network_id=0, $institution_id=0, $user_id)
	{
		//echo 'host: ' . $host_id . ' network: ' . $network_id . ' institution: ' . $institution_id . ' user: ' . $user_id; exit;
		//find out which grade the student is in - user_class query
		$sql = '
		SELECT * FROM user_classes
		WHERE user_classes.user_id = '. $user_id;

		//echo $sql; exit;

		$result = $this->_db->fetchAll($sql);
		foreach($result as $r){
			$buffer[] = ' class_id=' . $r->class_id;
		}
		if(count($buffer)>0){
			$or = join(" OR ", $buffer);
		} else {
			$or = '1';
		}
		unset($buffer);


		//get grades from classes that will be used to pull out tasks with given
		//grades
		$sql = 'SELECT * FROM classes WHERE '. $or;

		//echo $sql; exit;
		$result = $this->_db->fetchAll($sql);

		foreach($result as $r){
			$buffer[]=' tasks_scale.grade="'.$r->grade.'"';
		}

		$and = join(" OR ", $buffer);

		if(count($buffer)>0){
			$and = " AND (".$and.")";
		} else {
			$and = '';
		}

		//get tasks that have a given
		$sql = '
		SELECT DISTINCT(campaigns.campaign_id) FROM campaigns
		INNER JOIN tasks_scale
		ON campaigns.campaign_id = tasks_scale.campaign_id
		WHERE
			tasks_scale.task_id <> 0
			AND (
				campaigns.institution_id = '.$host_id.'
				OR campaigns.institution_id = '.$network_id.'
				OR campaigns.institution_id = '.$institution_id.'
			)';

		$sql .= $and;

		//echo $sql; exit;

		try{
			$result = $this->_db->fetchAll($sql);
		}catch(Zend_Exception $e){
			echo "There was an error MC-GCFK-KJU76E";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}

		//get campaign records for all the campaign ids we just pulled out
		//return if result is empty
		$r='';
		unset($buffer);
		if(count($result)>0){
			foreach($result as $r){
			$buffer[] = " campaign_id=".$r->campaign_id;
			}
		} else {
			return $result;
		}


		$or = (count($buffer)>0) ? join(" OR ", $buffer) : ' 1 ';
		unset($buffer);

		$sql = ' SELECT * FROM campaigns WHERE ' . $or;
		echo $sql; exit;
		$result = $this->_db->fetchAll($sql);

		return $result;
	}

	///////////////////// MEDALS TABLE /////////////////////

	/**
	 * Gets the sum of medals_value for a campaign id
	 *
	 * @param int $campaign_id
	 *
	 * @return * int $total_number_of_medals
	 *
	 */
	public function get_medals_total_value_by_campaign_id($campaign_id)
	{
		$sql = '
		SELECT SUM(medal_value) AS total FROM medals WHERE campaign_id = '.$campaign_id;
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-GMTVBCI-LKJ73W";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}

		$total = $result[0]->total;
		return $total;
	}

	/**
	 * Gets the highest hierarchy of medals for a given campaign
	 *
	 * @param int $campaign_id
	 * @return int $top_hierarcy
	 *
	 */
	public function get_next_hierarchy($campaign_id)
	{
		$sql = '
		SELECT * FROM medals WHERE campaign_id = '. $campaign_id.'
		ORDER BY medal_hierarchy DESC
		LIMIT 0, 1';

		//echo $sql; exit;

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-GNH-KJ8SA3";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		if(count($result)>0){
			$top_hierarcy = $result[0]->medal_hierarchy;
		} else {
			$top_hierarcy = 0;
		}

		return $top_hierarcy;
	}

	/**
	 * Inserts a new medal
	 *
	 * @param arr $arrInsert
	 * @return int $result
	 *
	 */
	public function insert_medal($arrInsert)
	{

		try{
			$result = $this->_db->insert("medals", $arrInsert);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-IM-JH76SR";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		return $result;
	}

	/**
	 * Gets all medals for a given campaign_id
	 *
	 * @param arr $campaign_id
	 * @return int $result
	 *
	 */
	public function get_medals_by_campaign($campaign_id)
	{
		$sql = 'SELECT * FROM medals WHERE campaign_id ='.$campaign_id;
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-GMBC-JKH765";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		return $result;
	}

	/**
	 * Gets all medals for a given campaign_id and name
	 *
	 * @param int $campaign_id
	 * @param str $name
	 * @return int $result
	 *
	 */
	public function get_medals_by_name($campaign_id, $name)
	{
		$sql = '
		SELECT * FROM medals
		WHERE campaign_id ='.$campaign_id.'
		AND medal_name = "'. $name.'"';

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-GMBN-LAKJS7";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		return $result;
	}

	/*
	 * Select all the campaigns that can be available to a user based on the task_scale table.
	 * Whatever grade(s) a user is in is searched on the task_scale table.
	 */
	public function campaigns_select_all_available($arrParams)
	{
		if (!isset($arrParams["user_id"]) || !$arrParams["user_id"])
		{
			print "Sorry, there was an error: MC-CSAA101-23JK34";
			exit;
		}
		$strSql = "
			SELECT
				distinct campaigns.*
			FROM
				user_classes,
				classes,
				tasks_scale,
				campaigns
			WHERE
				user_classes.user_id = " . $arrParams["user_id"] . "
				AND classes.class_id = user_classes.class_id
				AND tasks_scale.grade = classes.grade
				AND tasks_scale.campaign_id = campaigns.campaign_id
				AND tasks_scale.task_id != 0";
		if (isset($arrParams["institution_id"]) && $arrParams["institution_id"])
			$strSql .= "
				AND tasks_scale.institution_id = " . $arrParams["institution_id"];
		$arrCampaigns = $this->_db->fetchAll($strSql);
		return $arrCampaigns;
	}

    public function insert_achievement_card($arrParams)
    {
        if(is_array($arrParams))
        {
            if(isset($arrParams["mode"]) && $arrParams["mode"]=="template"){
                $arrFields = array (
                            "institution_id" => @$arrParams["institution_id"],
                            "campaign_id" => @$arrParams["campaign_id"],
                            "task_id" => @$arrParams["task_id"],
							"class_id" => @$arrParams["class_id"],
                            "card_points"   =>@$arrParams["card_points"],
                            "card_type" => "Template",
                            "campaign_image_id" => @$arrParams["campaign_image_id"],
                            "achievement" => @$arrParams["achievement"],
                            "created" => @$arrParams["created"],
                            "created_by"  => @$arrParams["created_by"]
                        );

                //var_dump($arrFields); exit;
                return $intAI = $this->_db->insert("achievement_cards",$arrFields);
            }else{
                return $intAI = $this->_db->insert("achievement_cards",$arrParams);
            }
        }
    }
    public function get_campaign_image_id($intCampaign)
    {
        $strSql = "Select image_smallmed from campaigns where campaign_id=". $intCampaign;
        $result = $this->_db->fetchRow($strSql);
        return $result;
    }
    public function select_achievement_card_templates($arrParams)
    {
        $strSql = "
            SELECT
                *
            FROM
                achievement_cards
            INNER JOIN campaigns on achievement_cards.campaign_id=campaigns.campaign_id
            WHERE achievement_cards.institution_id=".$arrParams["institution_id"]."
            AND achievement_cards.created_by=".$arrParams["created_by"]. "
            AND achievement_cards.card_type=\"".$arrParams["card_type"]."\"";
        //print $strSql; exit;
        try{
			$result = $this->_db->fetchAll($strSql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-SACT-DRF768";
			if(DEV_ENV=='devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		return $result;
    }
    public function select_achievement_card_template($arrParams)
    {
        $strSql = "
            SELECT
                *
            FROM
                achievement_cards
            WHERE
                achievement_card_id=" . $arrParams["achievement_card_id"];
        //print $strSql; exit;
        try{
			$result = $this->_db->fetchRow($strSql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-SACT-DRF768";
			if(DEV_ENV=='devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		return $result;
    }
}
?>