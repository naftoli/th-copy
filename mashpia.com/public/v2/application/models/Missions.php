<?php
class Missions
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
	public function _missions_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible selections
		$arrColumns = array (
			"mission_id"          => @$arrParams["mission_id"],
			"mission_name"          => @$arrParams["mission_name"],
			"mission_type"          => @$arrParams["mission_type"],
			"campaign_id"           => @$arrParams["campaign_id"],
			"percentage_required"   => @$arrParams["percentage"],
			"start_date"            => @$arrParams["start_date"],
			"end_date"              => @$arrParams["end_date"],
			"points_up"             => @$arrParams["points_up"],
			"medal_up"              => @$arrParams["medal_up"],
			"rank_up"               => @$arrParams["rank_up"],
			"sequence"              => @$arrParams["sequence"],
			"default_velocity"		=> @$arrParams["default_velocity"],
			"is_active"				=> @$arrParams["is_active"],
			"created"               => @$arrParams["created"],
			"created_by"            => @$arrParams["created_by"]
		);
		
		$strSql = "
			SELECT";
		// Switch the result to a count if specified
		if (isset($arrParams["count"]))
		{
			$strSql .= "
				COUNT(mission_id)";
		}
		else
		{
			$strSql .= "
				*";
		}
		$strSql .= "
			FROM
				missions
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
						AND institution_type = 'School'
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
						AND institution_type = 'School'
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
			if (isset($arrParams["institution_id"]))
			$strSql .= "
				AND institution_id = " . $arrParams["institution_id"];
		}
		//print $strSql;exit;
		$arrResult = $this->_tools->cleanSlashes($this->_db->fetchAll($strSql));
		return $arrResult;
		
	}
	
	public function _missions_update($arrParams)
	{
		$arrValuesParams = array("mission_name","mission_type","campaign_id","book_id","book_measurement","institution_id","start_date","end_date","points_up","medal_up","rank_up","sequence","is_active","percentage_required","default_velocity");
		$arrWhereParams = array("mission_id","mission_name","mission_type","campaign_id","book_id","institution_id","start_date","end_date","points_up","medal_up","rank_up","sequence","is_active","percentage_required","default_velocity","created","modified","created_by");
		
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
		$boolResult = $this->_db->update("missions", $arrValues, $arrWhere);
		return $boolResult;
	}
	
	public function missions_select_hierarchy ($arrParams)
	{
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MM-MSH101-DF87S9";
			exit;
		}
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
		
		
		$arrColumns = array (
			"mission_id"          => @$arrParams["mission_id"],
			"mission_name"          => @$arrParams["mission_name"],
			"mission_type"          => @$arrParams["mission_type"],
			"campaign_id"           => @$arrParams["campaign_id"],
			"percentage_required"   => @$arrParams["percentage"],
			"start_date"            => @$arrParams["start_date"],
			"end_date"              => @$arrParams["end_date"],
			"points_up"             => @$arrParams["points_up"],
			"medal_up"              => @$arrParams["medal_up"],
			"rank_up"               => @$arrParams["rank_up"],
			"sequence"              => @$arrParams["sequence"],
			"default_velocity"		=> @$arrParams["default_velocity"],
			"is_active"				=> @$arrParams["is_active"],
			"created"               => @$arrParams["created"],
			"created_by"            => @$arrParams["created_by"]
		);
		
		$strSql = "
			SELECT
				*
			FROM
				missions
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
		
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	// Generic functions end
	
	/*
	 * Update all the missions with book associations under a campaign with the
	 * provided book measurement.
	 */
	public function missions_update_book_measurement ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Required
		if (!isset($arrParams["campaign_id"]))
		{
			print "Sorry, there was an error: MM-MUBM101-SDF789";
			exit;
		}
		if (!isset($arrParams["book_measurement"]))
		{
			print "Sorry, there was an error: MM-MUBM102-FD0F0D";
			exit;
		}
		
		$strWhere = "campaign_id = " . $arrParams["campaign_id"];
		
		$arrValues = array(
			"book_measurement" => $arrParams["book_measurement"],
			"modified" => date("Y-m-d H:i:S")
		);
		$boolResult = $this->_db->update("missions", $arrValues, $strWhere);
		return $boolResult;
	}

	public function missions_select_join_insitutions ($arrParams)
	{
		$strSql2 = "";
		if (isset($arrParams["host_id"])) {
			$strSql2 .= "
				AND (
					host_id = {$arrParams["host_id"]}
					AND institution_type = 'School'
				)";
		}
		if (isset($arrParams["network_id"])) {
			$strSql2 .= "
				AND (
					network_id = {$arrParams["network_id"]}
					AND institution_type = 'School'
				)";
		}
		if (isset($arrParams["institution_id"])) {
			$strSql2 .= "
				AND (
					institution_id = {$arrParams["institution_id"]}
					AND institution_type = 'School'
				)";
		}
		$strSql = "
			SELECT
				missions.*
			FROM
				institutions,
				missions
			WHERE
				1";
		if (strlen($strSql2))
		{
			$strSql .= $strSql2 . "
				AND missions.institution_id = institutions.institution_id";
		}
		print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}



	public function mission_insert ($arrQuery) {

		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		if (!isset($arrQuery["installed_mission_id"]))
			$arrQuery["installed_mission_id"] = 0;
		
		// Build the insert
		$arrFeilds = array (
			"installed_mission_id"  => $arrQuery["installed_mission_id"],
			"mission_name"          => @$arrQuery["mission_name"],
			"mission_type"          => @$arrQuery["mission_type"],
			"campaign_id"           => @$arrQuery["campaign_id"],
			"book_id"				=> @$arrQuery["book_id"],
			"book_measurement"		=> @$arrQuery["book_measurement"],
			"institution_id"        => @$arrQuery["institution_id"],
			"percentage_required"   => @$arrQuery["percentage_required"],
			"start_date"            => @$arrQuery["start_date"],
			"default_velocity"      => @$arrQuery["default_velocity"],
			"end_date"              => @$arrQuery["end_date"],
			"points_up"             => @$arrQuery["points_up"],
			"medal_up"              => @$arrQuery["medal_up"],
			"rank_up"               => @$arrQuery["rank_up"],
			"sequence"              => @$arrQuery["sequence"],
			"is_active"             => @$arrQuery["is_active"],
			"created"               => $intCurrentDate,
			"created_by"            => $this->_user_session_data->user_id
		);

		// Execute
		$boolResult = $this->_db->insert("missions", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
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
		if (isset($arrQuery["installed_mission_id"]))
			$arrFeilds["installed_mission_id"] = $arrQuery["installed_mission_id"];
		if (isset($arrQuery["mission_name"]))
			$arrFeilds["mission_name"] = $arrQuery["mission_name"];
		if (isset($arrQuery["mission_type"]))
			$arrFeilds["mission_type"] = $arrQuery["mission_type"];
		if (isset($arrQuery["campaign_id"]))
			$arrFeilds["campaign_id"] = $arrQuery["campaign_id"];
		if (isset($arrQuery["institution_id"]))
			$arrFeilds["institution_id"] = $arrQuery["institution_id"];
      if (isset($arrQuery["percentage_required"]))
			$arrFeilds["percentage_required"] = $arrQuery["percentage_required"];
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
	
	public function missions_select ($boolStatus=1, $arrExtra=0)
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
		
                if(count($arrSql)){
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
	
	/**
	 * Function selects all missions based on institution id. 
	 *
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function missions_select_by_institution_id($institution_id)
	{
		
		if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM missions';
		}else{
			$utility = new Utilities();
			$childIds = $utility->getChildInstitutions($institution_id);
			$sql = 'SELECT * FROM missions WHERE institution_id IN ('.$childIds.')';
		}

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MM-CSBII-JSHGD6";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		//echo $sql; exit;
		return $result;
	}
	
	/**
	 * gets all the missions that belong to a given campaign
	 *
	 * @para int $campaign_id
	 *
	 * @return arr $arrResult
	 *
	 */
	public function get_missions_by_campaign_id($campaign_id)
	{
		$sql = '
		SELECT * FROM missions WHERE missions.campaign_id = '. $campaign_id;
		try{
			$result = $this->_db->fetchAll($sql);
		} catch(Zend_Exception $e){
			echo "There was an error MM-GMBCI-KJ872H";
			if(DEV_ENV=='devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		return $result;
	}
	
	public function get_active_tasks_by_mission_id($intMissionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE mission_id=" . $intMissionId . " ";
		$strSql = $strSql . "AND is_active=1 ";
		$strSql = $strSql . "ORDER BY task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}
	
	public function get_inactive_tasks_by_mission_id($intMissionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE mission_id=" . $intMissionId . " ";
		$strSql = $strSql . "AND is_active=0 ";
		$strSql = $strSql . "ORDER BY task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}

	public function get_active_tasks_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks  ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId . " ";
		$strSql = $strSql . "AND is_active=1 ";
		$strSql = $strSql . "ORDER BY task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}
	
	public function get_inactive_tasks_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks  ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId . " ";
		$strSql = $strSql . "AND is_active=0 ";
		$strSql = $strSql . "ORDER BY task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}

	public function get_active_tasks_by_network_id($intNetworkId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS n ON (t.institution_id=n.institution_id AND n.network_id=" . $intNetworkId . ") ";
		$strSql = $strSql . "WHERE t.is_active=1 ";
		$strSql = $strSql . "ORDER BY t.task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}
	
	public function get_inactive_tasks_by_network_id($intNetworkId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS n ON (t.institution_id=n.institution_id AND n.network_id=" . $intNetworkId . ") ";
		$strSql = $strSql . "WHERE t.is_active=0 ";
		$strSql = $strSql . "ORDER BY t.task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}
	
	public function get_active_tasks_by_host_id($intHostId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS n ON (t.institution_id=n.institution_id) ";
		$strSql = $strSql . "JOIN institutions AS h ON (n.network_id=h.institution_id AND h.host_id=" . $intHostId . ") ";
		$strSql = $strSql . "WHERE t.is_active=1 ";
		$strSql = $strSql . "ORDER BY t.task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}
	
	public function get_inactive_tasks_by_host_id($intHostId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS n ON (t.institution_id=n.institution_id) ";
		$strSql = $strSql . "JOIN institutions AS h ON (n.network_id=h.institution_id AND h.host_id=" . $intHostId . ") ";
		$strSql = $strSql . "WHERE t.is_active=0 ";
		$strSql = $strSql . "ORDER BY t.task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        		
	}
	
	public function get_all_active_campaigns()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE is_active=1 ";
		$strSql = $strSql . "ORDER BY task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        				
	}
	
	public function get_all_inactive_campaigns()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE is_active=0 ";
		$strSql = $strSql . "ORDER BY task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        				
	}
	
	public function get_active_tasks_and_status_by_mission_id($intUserId, $intMissionId)
	{
		$strSql = "SELECT t.*, uc.user_campaign_id AS completed ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "LEFT JOIN user_campaigns AS uc ON (uc.user_id=" . $intUserId . " AND uc.task_id=t.task_id AND uc.status='Completed') ";
		$strSql = $strSql . "WHERE t.mission_id=" . $intMissionId . " ";
		$strSql = $strSql . "AND t.is_active=1 ";
		$strSql = $strSql . "ORDER BY t.task_id ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;        				
	}
	
	public function get_mission_name($intMissionId)
	{
		$strSql = "SELECT mission_name FROM missions WHERE mission_id=" . $intMissionId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult->mission_name;        				
	}
}

?>