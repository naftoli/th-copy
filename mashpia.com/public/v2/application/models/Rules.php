<?php
class Rules
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
	public function _rules_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"rule_id"	         => @$arrParams["rule_id"],			
			"rule_type"			 => @$arrParams["rule_type"],
			"rule_applies_to"	 => @$arrParams["rule_applies_to"],
			"rule"				 => @$arrParams["rule"],
			"institution_id"	 => @$arrParams["institution_id"],
			"campaign_id"	 	 => @$arrParams["campaign_id"],
			"prize_id"			 => @$arrParams["prize_id"],
			"is_active"		 	 => @$arrParams["is_active"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);
		
		$strSql = "
			SELECT
				*
			FROM
				rules
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
			ORDER BY created+0 DESC";
		
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
		
	}
	
	public function _rules_select_hierarchy ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"rule_id"	         => @$arrParams["rule_id"],			
			"rule_type"			 => @$arrParams["rule_type"],
			"rule_applies_to"	 => @$arrParams["rule_applies_to"],
			"rule"				 => @$arrParams["rule"],
			"campaign_id"	 	 => @$arrParams["campaign_id"],
			"prize_id"			 => @$arrParams["prize_id"],
			"is_active"		 	 => @$arrParams["is_active"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);
		
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
				rules
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
			ORDER BY created+0 DESC";
		
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
		
	}
	
	public function _rules_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}
		
		$arrFeilds = array (
			"rule_id"			=> @$arrParams["rule_id"],
			"rule_type"			=> @$arrParams["rule_type"],
			"rule_applies_to"	=> @$arrParams["rule_applies_to"],
			"rule"				=> @$arrParams["rule"],
			"user_id"			=> @$arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"campaign_id"		=> @$arrParams["campaign_id"],
			"prize_id"			=> @$arrParams["prize_id"],
			"is_active"			=> @$arrParams["is_active"],
			"created"			=> date("Y-m-d H:i:S"),
			"created_by"		=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("rules", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}
	
	public function _rules_delete($arrParams)
	{
		$arrWhereParams = array("rule_id","rule_type","rule_applies_to","rule","user_id","institution_id","campaign_id","prize_id","is_active","created","modified","created_by");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MR-RD101-456TRG";
			exit;
		}
		$boolResult = $this->_db->delete("rules", $arrFeilds);
		return $boolResult;
	}
	// Generic functions end

	public function rule_process_param ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!is_array($arrParams))
		{
			print "Sorry, there was an error: MR-RPP101-23J33J";
			exit;
		}
		$arrResult = array();
		// start_age_limit
		if (
			isset($arrParams["start_age_limit"])
			&& preg_match("/[0-9]/", $arrParams["start_age_limit"]) // Has a number in it
		) {
			$arrResult[] = "_age_>=" . intval($arrParams["start_age_limit"]);
		}
		// end_age_limit
		if (
			isset($arrParams["end_age_limit"])
			&& preg_match("/[0-9]/", $arrParams["end_age_limit"]) // Has a number in it
		) {
			$arrResult[] = "_age_<=" . intval($arrParams["end_age_limit"]);
		}
		// gender_limit
		$arrPossible = array("Male Only", "Female Only");
		if (
			isset($arrParams["gender_limit"])
			&& in_array($arrParams["gender_limit"], $arrPossible)
		) {
			$arrResult[] = "_gender_==" . preg_replace("/ .+/", "", $arrParams["gender_limit"]);
		}
		// institution limit
		$arrPossible = array("Camps Only", "Schools Only");
		if (
			isset($arrParams["institution_limit"])
			&& in_array($arrParams["institution_limit"], $arrPossible)
		) {
			$arrResult[] = "_institution type_==" . preg_replace("/ .+/", "", $arrParams["institution_limit"]);
		}
		
		//var_dump($arrResult);
		return $arrResult;
	}
	
	/*
	 * Use the "rule_params" param to operate a "age=11 && gender=male" syntax.
	 * Use the "is" param to define what type of rule you are evalulating
	 * Note: the intention of this function will be to adventually add to the logical
	 * delimiters with the " || " and "()" syntax (if needed)
	 * Required: is, rule_params, campaign_id, institution_id
	 * Potential optimization: This function is often used within a loop so a
	 * strong optimization would be to allow the object to cache the query
	 * results to be reused rather than queryed over and over.
	 */
	public function rule_is_allowed($arrParams)
	{
		$_VERBOSE = 0;
		if (!isset($arrParams["rule_params"]))
		{
			print "Sorry, there was an error: MR-RQ101-DF8G7F";
			exit;
		}
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MR-RQ102-FDGFBV";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MR-RQ103-DFDFDF";
			exit;
		}
		
		$arrParamRules = preg_split("/ +&& +/", $arrParams["rule_params"]);
		
		$arrRules = $this->_rules_select_hierarchy($arrParams);
		
		$boolRulesResult = false;
		// Loop through the rows and apply the logic
		foreach ($arrRules as $objRule)
		{
			$boolRuleFailed = false;
			$arrRowRules = explode(";", $objRule->rule);
			// Each individule rule in the row
			foreach ($arrRowRules as $strRowRule)
			{
				// Each param rule
				foreach ($arrParamRules as $strParamRule)
				{
					if (preg_match("/^([^!=]+)([!=]{1,2})([^!=]+)$/", $strParamRule, $arrParamMatched))
					{
						if (count($arrParamMatched) != 4)
						{
							// One of the supplied params as invalid
							print "Sorry, there was an error: MR-RQ105-DF76GF";
							exit;
						}
						// Match the pattern delimited into the rule column (rules table)
						if (preg_match("/^_([^<>=!]+)_([<>=!]{1,2})([^<>=!]+)$/", $strRowRule, $arrRowMatched))
						{
							// Search the provided params for a matching rule
							if (strtolower($arrParamMatched[1]) == strtolower($arrRowMatched[1]))
							{
								// Parse the logic
								if (preg_match("/^([<>=!])(=?)$/", $arrRowMatched[2], $arrLogicMatch))
								{
									if ($_VERBOSE)
										print $strRowRule . " - " . $strParamRule . "\n";
									if (
										!(
											(
												$arrLogicMatch[1] == "="
												&& $arrParamMatched[3] == $arrRowMatched[3]
											) || (
												$arrLogicMatch[1] == "!"
												&& $arrParamMatched[3] != $arrRowMatched[3]
											) || (
												$arrLogicMatch[1] == "<"
												&& $arrLogicMatch[2] == "="
												&& $arrParamMatched[3] <= $arrRowMatched[3]
											) || (
												$arrLogicMatch[1] == "<"
												&& $arrParamMatched[3] < $arrRowMatched[3]
											) || (
												$arrLogicMatch[1] == ">"
												&& $arrLogicMatch[2] == "="
												&& $arrParamMatched[3] >= $arrRowMatched[3]
											) || (
												$arrLogicMatch[1] == ">"
												&& $arrParamMatched[3] > $arrRowMatched[3]
											)
										)
									) {
										if ($_VERBOSE)
											print "\$boolRuleFailed = 1;\n";
										$boolRuleFailed = 1;
									}
								}
							}
						}
					}
				}
			}
			if ($_VERBOSE)
				print "LOOP\n";
			// Handle the allow / deny logic
			if (
				(
					!$boolRuleFailed
					&& $objRule->rule_type == "Allow"
				) || 
				(
					$boolRuleFailed
					&& $objRule->rule_type == "Deny"
				)
			)
				$boolRulesResult = true;
		}
		return $boolRulesResult;
	}
	
	public function rule_delete_id ($intId)
	{
		if (!isset($intId)) {
			print "Sorry, there was an error: MR-RDI101-C4BHF5";
			exit;
		}
		$strSql = "rule_id = " . $intId;
		$boolResult = $this->_db->delete("rules", $strSql);
		return $boolResult;
	}

	public function rules_select ()
	{
		$strSql = "
			SELECT
				*
			FROM
				rules";
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	public function rules_select_r_institutions ($arrQuery=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				rules
			WHERE
				1";
		if (is_array($arrQuery))
		{
			if (isset($arrQuery["prize_id"]))
			{
				$strSql .= "
				AND prize_id=" . $arrQuery["prize_id"];
			}
		}
		
		$arrRules = $this->_db->fetchAll($strSql);
		$arrInstitutions = array();
		foreach ($arrRules as $objRule) {
			$arrInstitutions[$objRule->institution_id] = 1;
		}
		$arrResult = array();
		if (count($arrInstitutions)) {
			$strSql = "
				SELECT
					*
				FROM
					institutions
				WHERE
					institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")";
			$arrInstitutions = $this->_db->fetchAll($strSql);
			foreach ($arrRules as $intKey => $objRule) {
				foreach ($arrInstitutions as $objInstitution) {
					if ($objRule->institution_id == $objInstitution->institution_id) {
						$arrResult[$intKey]["objInstitution"] = $objInstitution;
						$arrResult[$intKey]["objRule"] = $objRule;
						break;
					}
				}
			}
		}
		return $arrResult;
	}

	public function rule_select_id ($intId)
	{
		if (!isset($intId)) {
			print "Sorry, there was an error: MR-RSI101-FG56H4";
		}
		$strSql = "
			SELECT
				*
			FROM
				rules
			WHERE
				rule_id = " . $intId;
		$objResult = $this->_db->fetchRow($strSql);
		return $objResult;
	}

   	public function rule_insert($arrQuery)
	{
	    $intDate = date("Y-m-d H:i:S");

	    // Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue)
		{
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

	    // Build the insert
	    $arrFields = array (
			"institution_id"	=> $arrQuery["institution_id"],
			"network_id"		=> $arrQuery["network_id"],
			"host_id"			=> $arrQuery["host_id"],
			"prize_id"			=> $arrQuery["prize_id"],
			"rule_type"			=> $arrQuery["rule_type"],
			"rule_applies_to" 	=> $arrQuery["applies_to"],
			"rule" 				=> $arrQuery["rules"],
			"created"   	    => $intDate,
			"created_by"	    => $this->_user_session_data->user_id
	    );

	    // Execute
		$boolResult = $this->_db->insert("rules", $arrFields);
		if ($boolResult) {
			$intResult = $this->_db->lastInsertId();
			return $intResult;
		}
	}

   	public function rule_update($arrQuery)
	{
		$intDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue)
		{
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the update
		$arrFields = array (
			"prize_id"			=> $arrQuery["prize_id"],
			"rule_type"			=> $arrQuery["rule_type"],
			"rule_applies_to" 	=> $arrQuery["applies_to"],
			"rule" 				=> $arrQuery["rules"],
			"created"			=> $intDate,
			"created_by"		=> $this->_user_session_data->user_id
		);

		$strWhere = "rule_id=" . $arrQuery["rule_id"];

		// Execute
		$boolResult = $this->_db->update("rules", $arrFields, $strWhere);

		if ($boolResult) {
			return $arrQuery["rule_id"];
		}
	}
}
?>