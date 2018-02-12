<?php

/*
	// Table of contents

*/

class Addons
{
	private $_db;
	private $_user_session_data;
	private $_roles;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
   	}	
	
	/**
	 * Inserts one item into addon_item table
	 *
	 * @param arr arrInsert
	 * @return int result
	 * 
	 */
	public function addon_item_insert($arrInsert)
	{
		try{
			$result = $this->_db->insert("addon_items", $arrInsert);
		}catch(Zend_Exception $e){
			echo "There was an error: MA-AII1001-DJHF87";
			if(DEV_ENV=="devel") print_r($arrInsert);
		}		
		return $result;
	}
	
	/**
	 * Inserts item into addons table
	 *
	 * @param arr arrInsert
	 * @return int last_insert_id
	 * 
	 */
	public function addon_insert($arrInsert)
	{
		try{
			$result = $this->_db->insert("addons", $arrInsert);
			$last_insert_id = $this->_db->lastInsertId();
		}catch(Zend_Exception $e){
			echo "There was an error: MA-AI1001-DJ2543";
			if(DEV_ENV=="devel") print_r($arrInsert);
		}		
		return $last_insert_id;
	}
	
	/**
	 * Inserts multiple package items into the package_combo table
	 *
	 * @param int last_insert
	 * @param arr items
	 *
	 * @return bool
	 *
	 */
	public function addon_combo_insert($arrItems, $last_insert)
	{
		$date = date("Y-m-d H:i:s");
		$created_by = $this->_user_session_data->user_id;
		
		for($i=0; $i<count($arrItems); $i++){
			
			$arrInsert = array(
					'addon_id' => $last_insert,
					'addon_item_id' => $arrItems[$i],
					'is_active'	=> 1,
					'created'	=> $date,
					'created_by'	=> $created_by
			);
			
			$result = $this->_db->insert("addon_combos", $arrInsert);
		}
		
		return true;
	}
	
	public function addon_get_items($institution_id)
	{
		$strSql = '
		SELECT * FROM institutions
		WHERE institution_id = '.$institution_id;
				
		$institution = $this->_db->fetchRow($strSql);
		
		$strWhere = " WHERE institution_id = 0 ";
		if($institution->institution_type=="Host"){
			$strWhere .= " OR institution_id = " .$institution->institution_id;
			//get all networks for this host
			$strSql = '
			SELECT * FROM institutions
			WHERE institutions.host_id = '.$institution->institution_id.'
			AND institutions.institution_type="Network"';
			
			$arrInst = $this->_db->fetchAll($strSql);
			foreach($arrInst as $r){
				$strWhere .= " OR institution_id = " .$r->institution_id;
			}
		}
		
		$strSql = 'SELECT * FROM addon_items '. $strWhere;
		//echo $strSql; exit;
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_Exception $e){
			echo "There was an error: MA-AGI-101-ASDO546";
			if(DEV_ENV=="devel") echo $strSql;
		}
		return $arrResult;
	}
	public function addons_list($strStatus, $arrExtra=0)
	{		
		//get host_id, network_id for packages.institution_id
		$strSql = "
			SELECT 
				*
			FROM
				addons
			JOIN 
				institutions on institutions.institution_id=addons.institution_id
			";

			$arrSqlWhere = array();
			if(isset($arrExtra["host_id"]))
			{
				$arrSqlWhere[] = "(institutions.institution_id= ".$arrExtra["host_id"].")";
			}
			if(isset($arrExtra["network_id"]))
			{
				$arrSqlWhere[] ="(institutions.institution_id= ".$arrExtra["network_id"].")";
			}
			if(count($arrSqlWhere))
			{
				$strSql .= " WHERE " .join(" OR ", $arrSqlWhere) . " AND 
				".(isset($strStatus) == "1"
				? "addons.is_active = 1"
				: "addons.is_active = 0")." ORDER BY addon_name ASC";
				print $strSql; //exit;
				$arrResult = $this->_db->fetchAll($strSql);
				return $arrResult;
			}
			else
			{
				$arrResult = $this->_db->fetchAll($strSql);
				return $arrResult;
			}
				
			
	}
	public function addons_select_id($intId)
	{
		$strSql = "
			SELECT
				*
			FROM
				addons
			WHERE
				addon_id=".$intId;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;		
	}
	public function addon_update($arrUpdate, $intId)
	{
		$intCurrentDate = date("Y-m-d H:i:S");
		// Filter everything for the query
		foreach ($arrUpdate as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrUpdate[$intKey] = trim($strValue);
		}

		// Build the update
		$arrFeilds = array ();
		if (isset($arrUpdate["addon_name"]))
			$arrFeilds["addon_name"] = $arrUpdate["addon_name"];
		if (isset($arrUpdate["addon_description"]))
			$arrFeilds["addon_description"] = $arrUpdate["addon_description"];
		if (isset($arrUpdate["price"]))
			$arrFeilds["price"] = $arrUpdate["price"];		
		if (isset($arrUpdate["discount_price"]))
			$arrFeilds["discount_price"] = $arrUpdate["discount_price"];
		if (isset($arrUpdate["currency"]))
			$arrFeilds["currency"] = $arrUpdate["currency"];
  		// Execute
		$intResult = $this->_db->update("addons", $arrFeilds, "addon_id=" . $intId);
		return $intResult;
	}
	public function addon_items_list($strStatus)
	{
		$strSql = "
			Select
				*
			FROM
				addon_items
			where "
			. ($strStatus == "active"
			? "is_active = 1"
			: "is_active = 0");
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;	
	}
	public function addon_items_select_id($intId)
	{
		$strSql = "
			SELECT
				*
			FROM
				addon_items
			WHERE
				addon_item_id=".$intId;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;		
	}
	public function addon_item_update($arrUpdate, $intId)
	{
		$intCurrentDate = date("Y-m-d H:i:S");
		// Filter everything for the query
		foreach ($arrUpdate as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrUpdate[$intKey] = trim($strValue);
		}
		// Build the update
		$arrFeilds = array ();
		if (isset($arrUpdate["item_name"]))
			$arrFeilds["item_name"] = $arrUpdate["item_name"];
		if (isset($arrUpdate["item_description"]))
			$arrFeilds["item_description"] = $arrUpdate["item_description"];
		if (isset($arrUpdate["price"]))
			$arrFeilds["price"] = $arrUpdate["price"];
		if (isset($arrUpdate["currency"]))
			$arrFeilds["currency"] = $arrUpdate["currency"];
  		// Execute
		$intResult = $this->_db->update("addon_items", $arrFeilds, "addon_item_id=" . $intId);
		return $intResult;
	}
}
?>