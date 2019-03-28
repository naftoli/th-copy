<?php
class AchievementCards
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
	
	public function _achievement_cards_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		$arrColNames = array("achievement_card_id","institution_id","campaign_id","mission_id","task_id","class_id","card_serial","card_points","card_type","left_circle","right_circle","campaign_image_id","achievement","status","created","modified","created_by");
		
		// Possible column selections
		$arrColumns = array ();
		foreach ($arrColNames as $strColName)
		{
			if (isset($arrParams[$strColName]))
				$arrColumns[$strColName] = $arrParams[$strColName];
		}

		$strSql = "
			SELECT
				*
			FROM
				achievement_cards
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
				modified DESC, created DESC";
		
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	public function _achievement_cards_update($arrParams)
	{
		$arrValuesParams = array("institution_id","campaign_id","mission_id","task_id","class_id","card_serial","card_points","card_type","left_circle","right_circle","campaign_image_id","achievement","status","created_by");
		$arrWhereParams = array("achievement_card_id","institution_id","campaign_id","mission_id","task_id","class_id","card_serial","card_points","card_type","left_circle","right_circle","campaign_image_id","achievement","status","created","modified","created_by");
		
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
		$boolResult = $this->_db->update("achievement_cards", $arrValues, $arrWhere);
		return $boolResult;
	}
	
    public function achievement_card_validation($arrParams)
    {
        $strSql = "
            SELECT
                *
            FROM
                achievement_cards
            WHERE
                card_serial='". $arrParams["card_serial"]."'";
        //print $strSql; exit;
        try{
			$result = $this->_db->fetchAll($strSql);
		} catch (Zend_Exception $e){
			echo "There was an error: MAC-ACV101-TGY125";
			if(DEV_ENV=='devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		return $result;  
    }
    public function deposit_points($arrParams)
    {
		$arrParams = $this->_tools->rsqlclean($arrParams);
        $created = date("Y-m-d H:i:S");
		$arrDepositPoints = array(
						"user_id"           => $arrParams["user_id"],
						"institution_id"    => $arrParams["institution_id"],
						"campaign_id"       => $arrParams["campaign_id"],
						"class_id"      	=> $arrParams["class_id"],
						"points"            => $arrParams["card_points"],
						"resource_name"     => "specific achievement card",
						"created"           => $created,
						"modified"          => "",
						"created_by"        => $arrParams["user_id"]
						);
		$intAI = $this->_db->insert("user_points", $arrDepositPoints);
		$strSqlChangeStatus = "
			UPDATE
				achievement_cards
			SET
				status='scanned'
			WHERE
				card_serial='". $arrParams["card_serial"]."'";
		$this->_db->query($strSqlChangeStatus);
		return $intAI;
    }
	
	public function _scratch_cards_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"scratch_card_id"		=> @$arrParams["scratch_card_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"card_serial"			=> @$arrParams["card_serial"],
			"card_control"			=> @$arrParams["card_control"],
			"card_points"			=> @$arrParams["card_points"],
			"user_point_id"			=> @$arrParams["user_point_id"],
			"status"				=> @$arrParams["status"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				scratch_cards
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
	
	public function _scratch_cards_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}
		
		$arrFeilds = array (
			"scratch_card_id"		=> @$arrParams["scratch_card_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"card_serial"			=> @$arrParams["card_serial"],
			"card_control"			=> @$arrParams["card_control"],
			"card_points"			=> @$arrParams["card_points"],
			"user_point_id"			=> @$arrParams["user_point_id"],
			"status"				=> @$arrParams["status"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("scratch_cards", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}
	
	public function _scratch_cards_update($arrParams)
	{
		$arrValuesParams = array("institution_id","card_serial","card_control","card_points","user_point_id","status","created","modified","created_by");
		$arrWhereParams = array("scratch_card_id","institution_id","card_serial","card_control","card_points","user_point_id","status","created","modified","created_by");
		
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
		$boolResult = $this->_db->update("scratch_cards", $arrValues, $arrWhere);
		return $boolResult;
	}

}
?>