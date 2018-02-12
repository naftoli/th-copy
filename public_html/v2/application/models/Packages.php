<?php

/*
	// Table of contents

*/

class Packages
{
	private $_db;
	private $_user_session_data;
	private $_roles;
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
	public function _packages_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"package_id"	         => @$arrParams["package_id"],			
			"institution_id"	 => @$arrParams["institution_id"],
			"name"		 		 => @$arrParams["name"],
			"description"	 => @$arrParams["description"],
			"price"			 => @$arrParams["price"],
			"currency"		 => @$arrParams["currency"],
			"discount_price"	 => @$arrParams["discount_price"],
			"is_active"			 => @$arrParams["is_active"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);
		
		$strSql = "
			SELECT
				*
			FROM
				packages
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
	// Generic functions end	
	
	/**
	 * Inserts one item into package_item table
	 *
	 * @param arr arrInsert
	 * @return int result
	 * 
	 */
	public function package_item_insert($arrInsert)
	{
		try{
			$result = $this->_db->insert("package_items", $arrInsert);
		}catch(Zend_Exception $e){
			echo "There was an error: MP-PII1001-DJHF87";
			if(DEV_ENV=="devel") print_r($arrInsert);
		}		
		return $result;
	}
	
	/**
	 * Inserts item into package_item table
	 *
	 * @param arr arrInsert
	 * @return int last_insert_id
	 * 
	 */
	public function package_insert($arrInsert)
	{
		try{
			$result = $this->_db->insert("packages", $arrInsert);
			$last_insert_id = $this->_db->lastInsertId();
		}catch(Zend_Exception $e){
			echo "There was an error: MP-PI1001-DJ2543";
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
	public function package_insert_items($arrItems, $last_insert)
	{
		$date = date("Y-m-d H:i:s");
		$created_by = $this->_user_session_data->user_id;
		foreach($arrItems['item_id'] as $key => $value)
		{	
			$arrInsert = array(
					'package_id' => $last_insert,
					'package_item_id' => $value,
					'type'		=>	$arrItems['type'][$key],
					'is_active'	=> 1,
					'created'	=> $date,
					'created_by'	=> $created_by
			);
			
			$result = $this->_db->insert("package_combos", $arrInsert);
		}
		return true;
	}
	
	public function package_get_items($arrParams)
	{
		if(isset($arrParams["institution_id"]))
		{
			$strSql = '
			SELECT * FROM institutions
			WHERE institution_id = '.$arrParams["institution_id"];			
			$objInstitution = $this->_db->fetchRow($strSql);
			
			$strWhere = " WHERE institution_id = 0 ";
			if($objInstitution->institution_type=="Host"){
				print $objInstitution->institution_id;
				$strWhere .= " OR institution_id = " .$objInstitution->institution_id;
				//get all networks for this host
				$strSql = '
				SELECT * FROM institutions
				WHERE institutions.host_id = '.$objInstitution->institution_id.'
				AND institutions.institution_type="Network"';
				
				$arrInst = $this->_db->fetchAll($strSql);
				foreach($arrInst as $r){
					$strWhere .= " OR institution_id = " .$r->institution_id;
				}
			}
			else
			{ // it's a network id
				$strWhere .= " OR institution_id = " .$arrParams["institution_id"];
			}
		}
		else{
			$strWhere="";
		}
		
		$strSql = 'SELECT * FROM package_items '. $strWhere;
		//print $strSql; exit;
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_Exception $e){
			echo "There was an error: MP-PGT-101-JKSO96";
			if(DEV_ENV=="devel") echo $strSql;
		}
		return $arrResult;
	}
	
	/**
	 *
	 * Returns list of available packages for a given network or host.Function
	 * returns all packages regardless of wheather it is active or inactive. Although
	 * it takes the param $active, it is now commented out.
	 *
	 * @param bool active
	 * @param int institution_id - this can be either a host or network id
	 * @param int type - specifies whether we are pulling recs by host or network id
	 *
	 * @return arr $arrResult
	 *
	 */
	public function packages_list($active, $institution_id=0, $type)
	{
		if($institution_id != 0 || $institution_id != null || !empty($institution_id))
		{
			$utility = new Utilities();
			$childIds = $utility->getChildInstitutions($institution_id);
			$strWhere = 'where institution_id IN ('.$childIds.')';
		}	
		else
		{
			$strWhere= '';
		}
		$strSql = 'SELECT * FROM packages '. $strWhere;
		//print $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;	
	}	
	
	public function packages_select_id($intId)
	{
		if(is_array($intId))
		{
			return 0;
		}
		else{
			$strSql = "
				SELECT
					packages.package_id as 'pack_id',
					packages.*,
					packages.is_active as 'active_pack',
					package_combos.*
				FROM
					packages
				LEFT JOIN package_combos ON
					packages.package_id=package_combos.package_id			
				WHERE
					packages.package_id=".$intId;
		}
		//print $strSql; //exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function package_update($arrUpdate, $intId)
	{
		$intCurrentDate = date("Y-m-d H:i:S");
		// Filter everything for the query
		foreach ($arrUpdate as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrUpdate[$intKey] = trim($strValue);
		}

		// Build the update
		$arrFeilds = array ();
		if (isset($arrUpdate["name"]))
			$arrFeilds["name"] = $arrUpdate["name"];
		if (isset($arrUpdate["description"]))
			$arrFeilds["description"] = $arrUpdate["description"];
		if (isset($arrUpdate["price"]))
			$arrFeilds["price"] = $arrUpdate["price"];		
		if (isset($arrUpdate["discount_price"]))
			$arrFeilds["discount_price"] = $arrUpdate["discount_price"];
		if (isset($arrUpdate["currency"]))
			$arrFeilds["currency"] = $arrUpdate["currency"];
  		// Execute
		$intResult = $this->_db->update("packages", $arrFeilds, "package_id=" . $intId);
		return $intResult;
	}
	
	
	public function package_items_list($strStatus)
	{
		$strSql = "
			Select
				*
			FROM
				package_items";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;	
	}
	public function package_items_select_id($intId)
	{
		$strSql = "
			SELECT
				*
			FROM
				package_items
			WHERE
				package_item_id=".$intId;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;		
	}
	public function package_item_update($arrUpdate, $intId)
	{
		$intCurrentDate = date("Y-m-d H:i:S");
		// Filter everything for the query
		foreach ($arrUpdate as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrUpdate[$intKey] = trim($strValue);
		}
		// Build the update
		$arrFeilds = array ();
		if (isset($arrUpdate["name"]))
			$arrFeilds["name"] = $arrUpdate["name"];
		if (isset($arrUpdate["description"]))
			$arrFeilds["description"] = $arrUpdate["description"];
		if (isset($arrUpdate["price"]))
			$arrFeilds["price"] = $arrUpdate["price"];
		if(isset($arrUpdate["student_price"]))
			$arrFeilds["student_price"] = $arrUpdate["student_price"];
		if (isset($arrUpdate["currency"]))
			$arrFeilds["currency"] = $arrUpdate["currency"];
  		// Execute
		$intResult = $this->_db->update("package_items", $arrFeilds, "package_item_id=" . $intId);
		return $intResult;
	}
	
	public function package_items_by_host($host_id=null)
	{
		$strSqlInst = '
		SELECT b.institution_id AS inst_id FROM institutions as a
		INNER JOIN institutions as b
		ON a.institution_id = b.host_id
		WHERE a.institution_id = '.$host_id;
		
		//echo $strSqlInst; exit;
		
		$arrInst = $this->_db->fetchAll($strSqlInst);
		foreach($arrInst as $r){
			$where[] = $r->inst_id;
		}
		$strWhere = join(' OR institution_id=', $where);
		
		$strSql = '
		SELECT * FROM package_items WHERE package_items.institution_id = '.$host_id . ' OR institution_id=' . $strWhere;
		
		//echo $strSql; exit;
		
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		} catch(Zend_Exception $e){
			echo 'There was an error: MP-PIBH-JHSG65';
			if(DEV_ENV=='devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		return $arrResult;
		
	}
	
	public function package_items_by_network($network_id=null)
	{
		$strSql = '
		SELECT * FROM package_items WHERE package_items.institution_id = '.$network_id;
		
		//echo $strSql; exit;
		
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		} catch(Zend_Exception $e){
			echo 'There was an error: MP-PIBN-K0KH56';
			if(DEV_ENV=='devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		return $arrResult;
		
	}
	
	/**
	 * Retrieves all packages for the registration form based on the network_id
	 * and the host_id
	 * 
	 * @param int host_id
	 * @param int network_id
	 *
	 * @return arr arrResult
	 */
	public function packages_for_registration($host_id, $network_id)
	{
		$strSql = '
		SELECT * FROM packages
		WHERE packages.package_type="institutions"
		AND (packages.institution_id = '.$network_id.'
		OR packages.institution_id = '.$host_id.')
		ORDER BY packages.name ASC';
		
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_exception $e){
			echo "There was an error: MP-PFR100-JDHFYT";
			if(DEV_ENV=='devel') echo $strSql;
		}		
		return $arrResult;
	}
	
	
	/**
	 * Pulls all the information from package_items by package_id
	 *
	 * @param int package_id
	 *
	 * @return arr arrResult
	 *
	 */
	public function package_items_for_registration($package_id)
	{
		$strSql = '
		SELECT * FROM package_combos
		INNER JOIN package_items ON package_combos.package_item_id = package_items.package_item_id 
		WHERE package_combos.package_id = '.$package_id. '
		ORDER BY package_items.name ASC';
		
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_exception $e){
			echo "There was an error: MP-PIFR100-KJ86F3";
			if(DEV_ENV=='devel') echo $strSql;
		}
		
		return $arrResult;
	}
	
	/**
	 * Selects and returns add-ons only for a given host and institution
	 * id. Used at registration to select addons after a package is selected.
	 *
	 * @param $host_id
	 * @param $network_id
	 *
	 * @return array $result
	 */
	public function addons_for_registration($host_id, $network_id)
	{
		$sql = '
		SELECT * FROM package_items
		WHERE institution_id IN ('. $host_id. ','.$network_id.')
		AND package_item_type = "add-on"';
		//print $sql; exit;
		try{
			$result = $this->_db->fetchAll($sql);
		}catch(Zend_Exception $e){
			echo "There was an error: MP-AFR:AHHGTR";
			if(DEV_ENV == 'devel'){
				echo $e->getMessage();
				echo $sql;
			}
		}
		return $result;
	}
	
	public function package_combos_update($arrItems, $arrPackageId)
	{
		$date = date("Y-m-d H:i:s");
		$created_by = $this->_user_session_data->user_id;
		foreach($arrPackageId as $key => $value1)
		{
			$arrIds[] = $value1;
		}
		$intPackageId = join(",", $arrIds);
		
		//check is this package_id exists in the table
		$strSelect="
			SELECT
				*
			FROM
				package_combos
			WHERE package_id IN (".$intPackageId.")";
			//print $strSelect;
		$result = $this->_db->query($strSelect);
		if(!empty($result))
		{
			$strDelete = "
				DELETE FROM package_combos WHERE package_id IN($intPackageId);";
				//print $strDelete;
			$this->_db->query($strDelete);
			
			foreach($arrItems['item_id'] as $key => $value1)
			{	
				$arrInsert = array(
						'package_id'		=> $arrItems['package_id'][$key],
						'package_item_id'	=> $arrItems['item_id'][$key],
						'type'				=>	$arrItems['type'][$key],
						'is_active'			=> 1,
						'created'			=> $date,
						'created_by'		=> $created_by
				);
				
				$result = $this->_db->insert("package_combos", $arrInsert);
			}
			return true;
		}
		else{
			
			//insert a new package combo
			foreach($arrItems['item_id'] as $key => $value2)
			{
				//construct an array with new records
				$arrInsert = array(
						'package_id' 		=> $arrItems['package_id'][$key],
						'package_item_id' 	=> $arrItems['item_id'][$key],
						'type'				=>	$arrItems['type'][$key],
						'is_active'			=> 1,
						'created'			=> $date,
						'created_by'		=> $created_by
				);
				$this->_db->insert("package_combos", $arrInsert);
			}
		}
	}
	public function school_purchased_addons($intInstitution)
	{
		if($intInstitution > 0)
		{
			$strSql = "
				SELECT
					purchase_details.pack_item_id,
					purchase_details.pack_item_price,
					purchase_details.item_name,
					purchases.institution_id
				FROM
					purchase_details, purchases
				WHERE
					purchases.purchase_id = purchase_details.purchase_id
					AND purchases.institution_id=".$intInstitution."
					AND purchase_details.pack_item_type='add-on'";
			//print $strSql; exit;
			try{
				$result = $this->_db->fetchAll($strSql);
			}catch(Zend_Exception $e){
				echo "There was an error: MP-SPA101-123GTR";
				if(DEV_ENV == 'devel'){
					echo $e->getMessage();
					echo $strSql;
				}
			}
			return $result;
		}
		else{
			echo "There was an error: MP-SPA102-567GTR";
		}
	}
	public function check_addon_config($arrCheckAddonConfig)
	{
		if(is_array($arrCheckAddonConfig))
		{
			$strSql = "
				SELECT
					*
				FROM
					addons_config
				WHERE
					class_id =" .$arrCheckAddonConfig["class_id"] ."
					AND institution_id =". $arrCheckAddonConfig["institution_id"]."
					AND addon_id =". $arrCheckAddonConfig["addon_id"];
			try{
				$result = $this->_db->fetchAll($strSql);
			}catch(Zend_Exception $e){
				echo "There was an error: MP-SPA101-123GTR";
				if(DEV_ENV == 'devel'){
					echo $e->getMessage();
					echo $strSql;
				}
			}
			return $result;
		}
		else{
			echo "There was an error: MP-CAC101-234THU";
		}
	}
	public function insert_addon_configurations($arrInsert)
	{
		if(is_array($arrInsert))
		{
			$boolInsert = $this->_db->insert("addons_config" ,$arrInsert);
			return $boolInsert;
		}
	}
	public function select_addon_config($arrParams)
	{
		if(isset($arrParams["institution_id"]))
		{
			$strSql = "
				SELECT
					*
				FROM
					addons_config
				INNER JOIN classes on addons_config.class_id = classes.class_id
				WHERE
					addons_config.institution_id=".$arrParams["institution_id"]."
					AND addons_config.addon_id = " . $arrParams["addon_id"];
				
			
			try{
				$result = $this->_db->fetchAll($strSql);
			}catch(Zend_Exception $e){
				echo "There was an error: MP-SAC101-999GTR";
				if(DEV_ENV == 'devel'){
					echo $e->getMessage();
					echo $strSql;
				}
			}
			return $result;
		}
		else{
			echo "There was an error: MP-SAC101-345RAW";
		}
	}
	public function delete_addon_config($intAddonConfigId)
	{
		if(is_numeric($intAddonConfigId) && $intAddonConfigId > 0)
		{
			$strSqlDelete = "DELETE FROM addons_config where addon_config_id=".$intAddonConfigId;
			$boolResult = $this->_db->query($strSqlDelete);
			return $boolResult;
		}
	}
	public function addons_select($intAddonsId)
	{
		$strSql = "
			SELECT
				*
			FROM
				package_items
			WHERE package_item_id NOT IN(".$intAddonsId.")
			AND package_item_type='add-on'";
		//print $strSql; exit;
		try
		{
			$arrResult = $this->_db->fetchAll($strSql);
		}
		catch(Zend_Exception $e)
		{
			echo "There was an error: MP-AS101-333ADS";
			if(DEV_ENV == 'devel')
			{
				echo $e->getMessage();
				echo $strSql;
			}
		}
		return $arrResult;
	}
	public function insert_institution_addons_purchase($arrParams)
	{		
		try{
			$result = $this->_db->insert("purchases", $arrParams);
			$last_insert_id = $this->_db->lastInsertId();
		}catch(Zend_Exception $e){
			echo "There was an error: MP-IIAP101-ASD543";
			if(DEV_ENV=="devel") print_r($arrParams);
		}		
		return $last_insert_id;
	}
	public function insert_institution_addons_purchase_deteails($arrParams)
	{
		try{
			$result = $this->_db->insert("purchase_details", $arrParams);
			$last_insert_id = $this->_db->lastInsertId();
		}catch(Zend_Exception $e){
			echo "There was an error: MP-IIAPD101-ZXC503";
			if(DEV_ENV=="devel") print_r($arrParams);
		}		
		return $last_insert_id;
	}

}
?>