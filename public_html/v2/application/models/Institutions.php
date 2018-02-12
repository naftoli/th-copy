<?php

class Institutions
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
	public function _institutions_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$strSql = "select * from mashpiadb.schools
					where school_id = " . $arrParams["institution_id"];
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
		/*
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"institution_id"	 => @$arrParams["institution_id"],
			"reg_expires"		 => @$arrParams["reg_expires"],
			"institution_type"	 => @$arrParams["institution_type"],
			"template_style"	 => @$arrParams["template_style"],
			"host_id"		 	 => @$arrParams["host_id"],
			"network_id"		 => @$arrParams["network_id"],
			"name"				 => @$arrParams["name"],
			"hebrew_name"		 => @$arrParams["hebrew_name"],
			"is_active"			 => @$arrParams["is_active"],
			"address"			 => @$arrParams["address"],
			"city"				 => @$arrParams["city"],
			"state"				 => @$arrParams["state"],
			"country"			 => @$arrParams["country"],
			"phone"				 => @$arrParams["phone"],
			"postal"			 => @$arrParams["postal"],
			"email"				 => @$arrParams["email"],
			"website"			 => @$arrParams["website"],
			"image_id"			 => @$arrParams["image_id"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& !is_null($Value)
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

		if (isset($arrParams["_GREATER"]))
		{
			foreach ($arrParams["_GREATER"] as $intKey => $intVal)
			{
				$strSql .= "
					AND " . $intKey . " > " . $intVal . "
				";
			}
		}
		$strSql = $strSql . " ORDER BY name ASC";


		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
		*/
	}

	public function _institutions_update($arrParams)
	{
		$arrValuesParams = array("institution_type","reg_expires","template_style","host_id","network_id","name","hebrew_name","is_active","address","city","state","country","phone","postal","email","website","image_id","created_by");
		$arrWhereParams = array("institution_id","institution_type","reg_expires","template_style","host_id","network_id","name","hebrew_name","is_active","address","city","state","country","phone","postal","email","website","image_id","created","modified","created_by");

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
			print "Sorry, there was an error: MI-IU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("institutions", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _institutions_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"institution_type"		=> @$arrParams["institution_type"],
			"reg_expires"		 => @$arrParams["reg_expires"],
			"template_style"		=> @$arrParams["template_style"],
			"host_id"		=> @$arrParams["host_id"],
			"network_id"		=> @$arrParams["network_id"],
			"name"		=> @$arrParams["name"],
			"hebrew_name"		=> @$arrParams["hebrew_name"],
			"is_active"		=> @$arrParams["is_active"],
			"address"		=> @$arrParams["address"],
			"city"		=> @$arrParams["city"],
			"state"		=> @$arrParams["state"],
			"country"		=> @$arrParams["country"],
			"phone"		=> @$arrParams["phone"],
			"postal"		=> @$arrParams["postal"],
			"email"		=> @$arrParams["email"],
			"website"		=> @$arrParams["website"],
			"image_id"		=> @$arrParams["image_id"],
			"created"		=> date("Y-m-d H:i:S"),
			"created_by"		=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("institutions", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	// Generic functions end

	/*
	 * Select only hosts that have permissions for this user
	 * Defualt user id will be passed by the session
	 */
    public function hosts_select_userid ($intUser=0)
	{

		if (!$intUser)
			$intUser = $this->_user_session_data->user_id;
		$strSql = "
			SELECT
				institution_id
			FROM
				permissions
			WHERE
				user_id = " . $intUser;
		$arrResult = $this->_db->fetchAll($strSql);
		$arrInstitutions = array();
		foreach ($arrResult as $objPermission) {
			$arrInstitutions[$objPermission->institution_id] = 1;
		}
		// Drill into any possible institutions or networks for host associated to this permission to apply hierarchy
		if (count($arrInstitutions)) {
			$strSql = "
				SELECT
					*
				FROM
					institutions
				WHERE
					is_active = 1
					AND (
						institution_id IN (
							SELECT
								DISTINCT host_id
							FROM
								institutions
							WHERE
								host_id!=0
								AND institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")
						)
						OR (
							institution_type = 'Host'
							AND institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")
						)
					)
			";
			$arrResult = $this->_db->fetchAll($strSql);
		}
		return $arrResult;
	}

	function hosts_select($boolActive="active")
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				institution_type = 'Host'";
		if ($boolActive)
			$strSql .= "
				AND is_active = " . (
					$boolActive == "active"
					? 1
					: 0
				);
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	function networks_select($intHost=0, $boolActive="active")
	{
		// Query
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				institution_type = 'Network'";
		if ($boolActive)
			$strSql .= "
				AND is_active = " . (
					(
						$boolActive == "active"
						|| $boolActive == 1
					)
					? 1
					: 0
				);
		if (isset($intHost) && $intHost > 0)
			$strSql .= "
				AND host_id=$intHost";

		// Execute
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	function institutions_select($intHost=0, $intNetwork=0, $boolActive=1)
	{
		// Validation
		/*if (!preg_match("/^[0-9]+$/", $intHost))
		{
            print "Sorry, there was an error: MI-IS101-GH456J";
			exit;
		}
		if (!preg_match("/^[0-9]+$/", $intNetwork))
		{
            print "Sorry, there was an error: MI-IS102-DF45FG";
			exit;
		}*/
		// Query
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				institution_type <> 'Network'
				and institution_type <> 'Host'";
		/*
		if ($boolActive)
			$strSql .= "
				AND is_active = " . (
					(
						$boolActive == "active"
						|| $boolActive == 1
					)
					? 1
					: 0
				);
		*/
		if ($intHost > 0)
		{
			$strSql.= "
				AND host_id=$intHost";
		}
		if($intNetwork > 0)
		{
			$strSql.= "
				AND network_id=$intNetwork";
		}
		else
		{
			$strSql.= " AND host_id!=0 AND network_id!=0";
		}
		//print $strSql;		exit;
		// Result
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/* Redundent - Funcation has been renamed to hosts_select_userid */
	public function permissions_select_hosts_userid ($intUser)
	{
		$strSql = "
			SELECT
				institution_id
			FROM
				permissions
			WHERE
				user_id = " . $intUser;
		$arrResult = $this->_db->fetchAll($strSql);
		$arrInstitutions = array();
		foreach ($arrResult as $objPermission) {
			$arrInstitutions[$objPermission->institution_id] = 1;
		}
		// Drill into any possible institutions or networks for host associated to this permission to apply hierarchy
		if (count($arrInstitutions)) {
			$strSql = "
				SELECT
					*
				FROM
					institutions
				WHERE
					is_active = 1
					AND (
						institution_id IN (
							SELECT
								DISTINCT host_id
							FROM
								institutions
							WHERE
								host_id!=0
								AND institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")
						)
						OR (
							institution_type = 'Host'
							AND institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")
						)
					)
			";

			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
	}

    /**
     * Selects all admins for a given permission typ
     *
     * @param: str permissionType
     *
     * @return arr arrResult
     *
     */
    public function permissions_select_all_admins($permissionType, $institutionId=0)
    {
      if($institutionId!=0){
		$sqlAnd = 'AND permissions.institution_id = '.$institutionId;
	  } else{
		$sqlAnd = '';
	  }

	  $strSql = '
      SELECT * FROM permissions
	  INNER JOIN users
	  ON permissions.user_id = users.user_id
      WHERE permissions.permission LIKE "'.$permissionType.'" ' . $sqlAnd.'
	  ORDER BY users.last_name ASC';

      try{
        $arrResult = $this->_db->fetchAll($strSql);
      } catch(Zend_Exception $e){
        echo "There was an error: MI-PSAA-101-FJDK87";
        if(DEV_ENV == "devel") echo $strSql;
      }
      return $arrResult;
    }


	public function permissions_select_networks_userid ($intUser,$intHost=-1) {
		$strSql = "
			SELECT
				institution_id
			FROM
				permissions
			WHERE
				user_id = " . $intUser;
		$arrResult = $this->_db->fetchAll($strSql);
		$arrInstitutions = array();
		foreach ($arrResult as $objPermission) {
			$arrInstitutions[$objPermission->institution_id] = 1;
		}
		if (count($arrInstitutions)) {
			$strSqlExtra = "";
			if ($intHost > 0)
				$strSqlExtra .= "host_id=$intHost AND ";
			$strSql = "
				SELECT
					*
				FROM
					institutions
				WHERE
					is_active = 1
					AND (
						institution_id IN (
							SELECT
								DISTINCT network_id
							FROM
								institutions
							WHERE
								" . $strSqlExtra . "
								(
									institution_type != 'Host'
									AND institution_type != 'Network'
									AND institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")
								)
						) OR (
							" . $strSqlExtra . "
							institution_type = 'Network'
							AND institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")
						)
					)
			";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
	}

	public function permissions_institutions_select_userid ($intUser,$intHost=-1,$intNetwork=-1) {
		$strSql = "
			SELECT
				institution_id
			FROM
				permissions
			WHERE
				user_id = " . $intUser;
		$arrResult = $this->_db->fetchAll($strSql);
		$arrInstitutions = array();
		foreach ($arrResult as $objPermission) {
			$arrInstitutions[$objPermission->institution_id] = 1;
		}
		if (count($arrInstitutions)) {
			$strSqlExtra = "";
			if ($intHost > 0)
				$strSqlExtra .= "host_id=$intHost AND ";
			if ($intNetwork > 0)
				$strSqlExtra .= "network_id=$intNetwork AND ";
			$strSql = "
				SELECT
					*
				FROM
					institutions
				WHERE
					is_active = 1
					AND (
						" . $strSqlExtra . "
						institution_type != 'Host'
						AND institution_type != 'Network'
						AND institution_id IN (" . join(",", array_keys($arrInstitutions)) . ")
					)
			";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
	}

    /* Function get_all_hosts
	* @Params: none
	* @Return: Object which will include the entire list of hosts
	*/

	function get_all_hosts()
	{
		$strSql = "select DISTINCT (name), is_active, host_id, institution_id, network_id, email from institutions where institution_type = 'Host'";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}


	/* Function get_host_info
	* @Params: institution_id
	* @Return: Object which will include the entire record of a host
	*/
	function get_host_info($institution_id)
	{
		$strSql = "Select * from institutions where institution_id='$institution_id' and institution_type='Host'";
		$arrResult = $this->_db->fetchAll($strSql);
		if($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function activate_host
	* @Params: array, institution_id
	* @Return: Object which will include the entire record of a host
	*/
	function activate_host($array, $institution_id)
	{
		if($array)
		{
			$strSqlWhere='institution_type="Host" and institution_id='.$institution_id;
			$this->_db->update('institutions', $array, $strSqlWhere);
		}
		return 0;
	}

	/* Function delete_host
	* @Params: array, institution_id
	* @Return: Object which will include the entire record of a host
	*/

	function delete_host($array, $institution_id)
	{
		if($array)
		{
			$strSqlWhere='institution_type="Host" and institution_id='.$institution_id;
			$this->_db->update('institutions', $array, $strSqlWhere);
		}
		return 0;
	}


	/* Function update_host
	* @Params: institution_id
	* @Return: Object which will include the entire record of a host
	*/
	function update_host($arrSql,$intInstitutionId)
	{
		if($arrSql)
		{
			$strSqlWhere='institution_type="Host" and institution_id='.$intInstitutionId;
			$boolInsert = $this->_db->update('institutions', $arrSql, $strSqlWhere);
			return $boolInsert;
		}
		return 0;
	}

	/* Function get_host_by_institution_id
	* @Params: institution_id_value
	* @Return: Object which will include the entire record of a host
	*/

	function get_host_by_institution_id($institution_id_value)
	{
		$strSql = "select DISTINCT (name), host_id, network_id from institutions where institution_id='$institution_id_value' and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}
	/* Function get_host_id_by_institution_id
	* @Params: institution_id
	* @Return: Object which will include the entire record of a host
	*/

	function get_host_id_by_institution_id($intInstitutionId)
	{
		$strSql = "
			select
				*
			from
				institutions
			where
				host_id='$intInstitutionId'
				and is_active=1
				and institution_type = 'Network'";
		$arrResult = $this->_db->fetchAll($strSql);
		return 	$arrResult;
	}

	/* Function get_host_id
	* @Params: host_id
	* @Return: Object which will include host_id of the institution_type HOT from the institutions table
	* Checks whether a user exists with a networok_id exists in the institutions table
	*/



	function get_host_id()
	{
		$strSql = "select host_id, name from institutions where institution_type = 'Host' and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}



	function get_host_id_by_host_id($host_id)
	{
		$strSql = "select
					host_id,
					institution_id,
					name
				from
				institutions
				where
					host_id = '$host_id'
					and
					institution_type='Host'
					and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function get_institution_by_id
	* Params: intInstitutionId
	* @Return: Object which will include host_id,institution_id, network_id,
     * of the institution_type HOST from the institutions table.
	*/

	function get_institution_by_id($intInstitutionId)
	{
		$strSql = "
				select
					institution_id
				from
					institutions
				where
					institution_id = '$intInstitutionId'";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function get_institution_id
	* @Return: Object which will include host_id,institution_id, network_id,
     * of the institution_type HOST from the institutions table.
	*/

	function get_institution_id()
	{
		$strSql = "
				select
					host_id,
					institution_id,
					network_id,
					name
				from
					institutions
				where
					institution_type = 'Host'
					and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function get_insitution_info()
	* @Params: institution_id
	* @Return: institution record from the institutions table if paramater condition is met
	*/
	function get_institution_info($intInstitutionId)
	{
		$strSql = "
				select
					*
				from
					institutions
				WHERE
					institution_id = '$intInstitutionId'";
		$arrResult = $this->_db->fetchRow($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function get_network_info()
	* @Params: institution_id
	* @Return: networks record from the institutions table if paramater condition is met
	*/

	function get_network_info($intHostId)
	{
		$strSql = "
				select
					*
				from
					institutions
				WHERE
					host_id='$intHostId'
					and institution_type='Network'";
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}


	/* Function get_network_id
	* @Params: institution_id
	* @Return: Object which will include the entire user record from the institutions table
	* Checks whether a user exists with a networok_id exists in the institutions table
	*/

	function get_network_id($institution_id)
	{
		$strSql = "select DISTINCT (network_id), institution_id, host_id, name from institutions WHERE host_id='$institution_id' and institution_type='Network' and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}

		return 0;
	}

	/* Function get_network_id_by_institution_id
	* @Params: $institution_id
	* @Return: Object which will include the entire user record from the institutions table
	* Checks whether a user exists with a networok_id exists in the institutions table
	*/

	function get_network_id_by_institution_id($institution_id)
	{
		$strSql = "select * from institutions WHERE host_id='$institution_id' and institution_type='Network' and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}

		return 0;
	}

	/* Function get_network_id_by_host_id
	* @Params: $institution_id_value
	* @Return: Object which will include the entire user record from the institutions table
	* Checks whether a user exists with a networok_id exists in the institutions table
	*/

	function get_network_id_by_host_id($institution_id)
	{
		$strSql = "select network_id, institution_id, host_id, name from institutions WHERE host_id='$institution_id' and institution_type='Network'";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/* Function get_network_id_by_institution_id
	* @Params: $institution_id_value
	* @Return: Object which will include the entire user record from the institutions table
	* Checks whether a user exists with a networok_id exists in the institutions table
	*/

	function get_network_id_by_institution_id_host_id($institution_id_value,$host_id_value)
	{
		$strSql = "select name, network_id, institution_type from institutions WHERE institution_id='$institution_id_value' and host_id='$host_id_value' and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}

		return 0;
	}

	/* Function insert_new_host
	* @Params: array
	* @Return: array of data
	*/

	function insert_new_host($arrSql)
	{
		if($arrSql)
		{
			$intAi = $this->_db->insert('institutions', $arrSql);
			return $intAi;
		}
		return 0;
	}

	/* Function insert_new_network
	* @Params: array
	* @Return: array of data
	*/

	function insert_new_network($arrSql)
	{
		if($arrSql)
		{
			$intAi = $this->_db->insert('institutions', $arrSql);
			return $intAi;
		}
		return 0;
	}

	/* Function insert_new_institution
	* @Params: arrSql
	* @Return: array of data
	*/

	function insert_new_institution($arrSql)
	{
		if($arrSql)
		{
			$boolResult = $this->_db->insert('institutions', $arrSql);
			if ($boolResult)
			{
				return $this->_db->lastInsertId();
			}
		}
		return 0;
	}

	/* Function update_institution
	* @Params: array
	* @Return: array of data
	*/
	//function update_institution($array, $institution_id, $network_id, $institution_name)
	function update_institution($array, $intInstitutionId)
	{
		if($array)
		{
			$strWhereSql = 'institution_id='.$intInstitutionId;
			$arrResult = $this->_db->update('institutions', $array, $strWhereSql);
			return $arrResult;
		}
		return 0;
	}

	/* Function update_network
	* @Params: array
	* @Return: array of data
	*/
	function update_network($arrSql, $intInstitutionId)
	{
		if($arrSql)
		{
			$strWhereSql = 'institution_type= "Network" and institution_id='.$intInstitutionId;
			$arrResult = $this->_db->update('institutions', $arrSql,$strWhereSql);
			return $arrResult;
		}
		return 0;
	}

	/* Function get_institution_types
	* @Return: entire record from the institution_types table
	*/

	function get_institution_types()
	{
		$strSql = "select DISTINCT * from institution_types where institution_type ='Network'";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function new_institution_type
	* @Return: entire record from the institution_types table
	*/

	function new_institution_type()
	{
		$strSql = "select DISTINCT * from institution_types where institution_type <> 'Host' and institution_type <>'Network'";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function get_institution_type
	* @Return: entire record from the institution_types table
	*/

	function get_institution_type($network_id_value)
	{
		$strSql = "select  DISTINCT institution_type from institutions where network_id = '$network_id_value' and institution_type <>'Network' and institution_type <>'Host'";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function get_network_name
	* @Param: institution_type
	* @Return: entire record from the institution_types table
	*/

	function get_network_name($network_id_value, $host_id_value)
	{
		$strSql = "select DISTINCT name from institutions where network_id='$network_id_value' and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

    /* Function get_institution_name
	* @Param: institution_type
	* @Return: entire record from the institution_types table
	*/

	function get_institution_name($institution_type, $network_id, $host_id)
	{
		$strSql = "select DISTINCT name from institutions where institution_type='$institution_type' and network_id='$network_id' and host_id='$host_id' and institution_type <> 'Network' and institution_type <> 'Host' and is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	/* Function delete_network
	* @Param: host_id = institution_id,
	* @Return: set is_active to 0 when deleting a network, don't delete the actual record.
	*/

	function delete_network($array, $institution_id, $network_id)
	{
		if($array)
		{
			$strWhereSql = 'institution_type="Network" and host_id='.$institution_id.' and institution_id='.$network_id;
			$arrResult = $this->_db->update('institutions', $array, $strWhereSql);
		}
		return 0;
	}

	/* Function delete_institution
	* @Param: host_id = institution_id,
	* @Return: set is_active to 0 when deleting a network, don't delete the actual record, If network is_active set to zero(0), then status (is_active) of all institutions that are under this network will be set to zero (0)
	*/
	function delete_institution($array,$institution_id,$network_id,$intitution_type,$institution_name)
	{
		if($array)
		{
			$strWhereSql = 'institution_type="'.$intitution_type.'" and host_id='.$institution_id .' and network_id='.$network_id.' and name="'.$institution_name.'"';
			$arrResult = $this->_db->update('institutions', $array, $strWhereSql);
		}
		return 0;
	}

	/* Function is_active_Institutions
	* @Param: null
	* @Return: networks, institutions, users that are active.
	*/

	function is_active_Institutions()
	{
		$strSqlInstitutions = 'Select * from institutions where is_active=1';
		$arrResultInstitutions = $this->_db->fetchAll($strSqlInstitutions);
		if(arrResultInstitutions)
		{
			return $strSqlInstitutions;
		}
		return 0;
	}

	/* Function is_active_Users
	* @Param: null
	* @Return: networks, institutions, users that are active.
	*/

	function is_active_Users()
	{
	$strSqlUsers = 'Select * from users where is_active=1';
		$arrResultUsers = $this->_db->fetchAll($strSqlUsers);
		if($arrResultUsers)
		{
			return $strSqlUsers;
		}
		return 0;
	}

	/* Functions list_all_hosts
	* @Param: null
	* @Return: list of hosts active and inactive.
	*/
	function list_all_hosts($boolActive=NULL)
	{
		if($boolActive!=NULL){
			$strAnd = ' AND is_active = '
					.(
						$boolActive == 'active'
						? 1
						: 0
					);
		}else{
			$strAnd = '';
		}

		$strSql = "
				Select
					*
				from
					institutions
				where
					institution_type='Host'" . $strAnd;

		$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;

	}

	function paginator_adapter($is_active=NULL)
	{
		return $this->_db->select()->from('institutions')->where('institution_type="Host" and is_active='.$is_active);
	}

	function get_db()
	{
		return $this->_db;
	}

	function list_all_networks($boolActive, $intInstitutionId)
	{
		$strSql ="
				SELECT
					*
				FROM
					institutions a
				where
					institution_type = 'Network'
					and host_id IN
					(Select
						institution_id
					from
						 institutions b
					where
						 a.host_id = ".$intInstitutionId.")";
		/*$strSql = "
				Select
					*
				from
					institutions
				where
					institution_type='Network'
					and host_id = '$intInstitutionId'
					and is_active = "
					.(
						$boolActive == 'active'
						? 1
						: 0
					);*/
		$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
	}
	function network_list($intHost=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				network_id=0
				AND "
				.(
				 $intHost >0
				 ? "host_id='$intHost'"
				 : "host_id!=0"
				);

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/* Function get_all_networks
	* @Params: intHostId
	* @Returns: all networks associated with host id
	*/

	function get_all_networks($intHostId)
	{
		$strSql = "
				Select
					*
				from
					institutions
				where
					institution_type = 'Network'
					and host_id= '$intHostId'";
		$arrResult = $this->_db->fetchAll($strSql);
		if($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	function institutions_select_id($intId) {
		if (
			!isset($intId)
			|| empty($intId)
		) {
			// Error must provide an id
			return 0;
		}
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				institution_id=" . $intId;
		$arrResults = $this->_db->fetchRow($strSql);
		return $arrResults;
	}
	function institution_types_select() {
		$strSql = "
			SELECT
				*
			FROM
				institution_types
		";
		$arrResults = $this->_db->fetchAll($strSql);
		return $arrResults;
	}
	function institutions_select_name($strName,$strType,$intKey)
	{
		$strSql = "
			Select
				name
			from
				institutions
			where
				name='$strName'
				and institution_type='$strType'
				";
		switch ($strType):
			case "Host":
				$strSql.=" and host_id=0";
				break;
			case "Network":
				$strSql.="and host_id=".$intKey;
				break;
			case "Institution":
				$strSql.="and network_id=".$intKey;
				break;
		endswitch;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	function host_select_name($intHost=0)
	{
		$strSql = "Select
					 name
				   from
				   	 institutions
				   where
				   	 institution_type='Host'"
					 .($intHost
					 ? "and institution_id=".$intHost
					 : "");
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/* Function resource_navigation
	* Params: arrExtra
	* Return: arrResult that will hold the information about
	* what was passed in the arrExtra
	*/
	function resource_navigation($arrExtra=0, $strResource='')
	{
		if ($strResource == "navigation")
		{
			//if any parameter is passed select appropriate tables
			$strSql = "
					SELECT";
					if(isset($arrExtra["campaign_id"]))
					{		$strSql .= " campaigns.campaign_id,
							campaigns.campaign_type,
							campaigns.campaign_name FROM ";
					}
					if(isset($arrExtra["host_id"]) || isset($arrExtra["network_id"]) || isset($arrExtra["institution_id"]))
					{
							$strSql .= " institutions.institution_id,
										institutions.institution_type,
										institutions.name FROM ";
					}

			$arrTable = array();
			if(
				isset($arrExtra["institution_id"])
				|| isset($arrExtra["network_id"])
				|| isset($arrExtra["host_id"])
			){
				$arrTable[] = "institutions";
			}
			else if(
				isset($arrExtra["class_id"])
			){
				$arrTable[] = "classes";
			}
			else if(
				isset($arrExtra["user_id"])
			){
				$arrTable[] = "users";
			}
			else if(
				isset($arrExtra["campaign_id"])
			){
				$arrTable[] = "campaigns";
			}
			else if(
				isset($arrExtra["mission_id"])
			){
				$arrTable[] = "missions";
			}
			else
			{
				$arrTable[] = " * FROM institutions,campaigns,missions";
			}
			$strSql .= join(",", $arrTable);
		}
		else
		{
			print "Sorry, there was an error: MI-RS101-SDJ383";
			exit;
		}
		//sub queries
		$arrSql = array();
		if (
			isset($arrExtra["institution_id"])
		){
			$arrSql[] = "(institutions.institution_id = " . $arrExtra["institution_id"].")";
		}
		if (
			isset($arrExtra["network_id"])
		){
			$arrSql[] = "
				(
					institutions.institution_id = " . $arrExtra["network_id"] . "
					AND institutions.institution_type = 'Network'
				)";
		}
		if (
			isset($arrExtra["host_id"])
		){
			$arrSql[] = "
				(
					institutions.institution_id = " . $arrExtra["host_id"] . "
					AND institutions.institution_type = 'Host'
				)";
		}
		if (
			isset($arrExtra["campaign_id"])

		){
			$arrSql[] = "(
				campaigns.campaign_id = " . $arrExtra["campaign_id"]."
			)";
		}
		if (
			isset($arrExtra["mission_id"])
		){
			$arrSql[] = "(
				(missions.mission_id = " . $arrExtra["mission_id"]."
			)";
		}

		if (
			count($arrSql)
			&& count($arrTable)
		){
			$strSql .= " WHERE " . join(" OR ", $arrSql).";";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
		else
		{
			print "Sorry, there was an error: MI-RS101-D6554DH";
			exit;
		}
	}

	/**
	* Function resource_count
	* @params: arrExtra
	* @return: arrResult that will hold the count of itema based on
	* what was passed in the arrExtra
	*/
	function resource_count($arrExtra=0)
	{
		$strSql = "
			SELECT
				COUNT(1) as row_count
			FROM
			";
		//if any parameter is passed select appropriate tables
		$arrTable = array();
		if(
			isset($arrExtra["institution_id"])
			&& !isset($arrExtra["network_id"])
			&& !isset($arrExtra["host_id"])
		){
			$arrTable[] = "campaigns";
		}
		else if(
			isset($arrExtra["host_id"])
			|| isset($arrExtra["network_id"])
		){
			$arrTable[] = "institutions";
		}
		else if(
			isset($arrExtra["class_id"])
		){
			$arrTable[] = "classes";
		}
		else if(
			isset($arrExtra["user_id"])
		){
			$arrTable[] = "users";
		}
		else if(
			isset($arrExtra["campaign_id"])
		){
			$arrTable[] = "missions";
		}
		else if(
			isset($arrExtra["mission_id"])
		){
			$arrTable[] = "tasks";
		}
		else
		{
			$arrTable[] = "institutions,classes,users,campaigns,missions,tasks";
		}

		$arrSql= array();
		//see how many networks under a host
		if (
			isset($arrExtra["host_id"])
		){
			$arrSql[] = "
				(
					institutions.host_id = " . $arrExtra["host_id"] . "
					AND institution_type='Network'
				)";
		}
		//see how many institutions under a network
		if (
			isset($arrExtra["network_id"])
		){
			$arrSql[] = "
				(
					institutions.network_id = " . $arrExtra["network_id"] . "
					AND institution_type!='Host'
					AND institution_type!='Network'
				)";
		}
		//see how many campaigns under an institution
		if (
			isset($arrExtra["institution_id"])
		){
			$strSqlCampaign = "
			SELECT
				COUNT(1) as campaign_count
			FROM
				campaigns
			WHERE
				";
			/*
				Using arrExtra, an institution can be selected by using a host id
				and/or network id.
			*/
/***************** SUB QUESRIES FOR CAMPAIGNS, MISSIONS AND TASKS **************/

			//start campaign_count handler
			$arrSqlCampaigns = array(); // All exceptions within this array will be OR joined
			if (
				isset($arrExtra["institution_id"])
				&& !isset($arrExtra["host_id"])
				&& !isset($arrExtra["network_id"])
			)
				$arrSqlCampaigns[] = "institution_id = " . $arrExtra["institution_id"];
			if (
				isset($arrExtra["host_id"])
				|| isset($arrExtra["network_id"])
			){
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
				else
				{
					$arrSubSql[] = "(
						institution_id!=0
					)";
				}
				$arrSqlCampaigns[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
			}
			if (count($arrSqlCampaigns)) {
				$strSqlCampaign .= " (" . join(" OR ", $arrSqlCampaigns) . ")";
			}
			$arrResult = $this->_db->fetchAll($strSqlCampaign);
			return $arrResult;
			exit;
		//end of campaign_count handler
		}

		//see see how many missions under a campaign
		if (
			isset($arrExtra["campaign_id"])
			&& !isset($arrExtra["institution_id"])
			&& !isset($arrExtra["network_id"])
			&& !isset($arrExtra["host_id"])
		){
			//start mission_count handler
			$arrSqlMissions = array(); // All exceptions within this array will be AND joined
			$arrSqlMissions[] = "campaign_id = " . $arrExtra["campaign_id"];

			$strSqlMissions = "
			SELECT
				COUNT(1) as mission_count
			FROM
				missions
			WHERE
			";
			/*
				Using arrExtra, an institution can be selected by id or using a host id
				and/or network id.
			*/
			if (
				isset($arrExtra["institution_id"])
				&& !isset($arrExtra["host_id"])
				&& !isset($arrExtra["network_id"])
			){
				$arrSqlMissions[] = "(institution_id = " . $arrExtra["institution_id"].")";
			}
			if (
				isset($arrExtra["host_id"])
				|| isset($arrExtra["network_id"])
			){
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
				if(!empty($arrSqlMissions))
				{
					$arrSqlMissions[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
				}
			}
			if (count($arrSqlMissions))
			{
				$strSqlMissions .= " (" . join(" OR ", $arrSqlMissions) . ")";
			}
			$arrResult = $this->_db->fetchAll($strSqlMissions);
			return $arrResult;
			exit;
			//end of mission_count handler
		}

		//see how many tasks under a mission
		if (
			isset($arrExtra["mission_id"])
			&& !isset($arrExtra["campaign_id"])
			&& !isset($arrExtra["institution_id"])
			&& !isset($arrExtra["network_id"])
			&& !isset($arrExtra["host_id"])
		){
			//start task_count handler
			$arrSqlTasks = array();
			$arrSqlTasks[] = "
				(
					tasks.mission_id = " . $arrExtra["mission_id"]."
				)";

			$strSqlTasks = "
			SELECT
				COUNT(1) as task_count
			FROM
				tasks
			WHERE
				";
			/*
				Using arrExtra, an institution can be selected by id or using a host id
				and/or network id.
			*/

			if (
				isset($arrExtra["institution_id"])
				&& !isset($arrExtra["host_id"])
				&& !isset($arrExtra["network_id"])
			)
				$arrSqltasks[] = "(institution_id = " . $arrExtra["institution_id"].")";
			if(isset($arrExtra["campaign_id"]))
			{
				$arrSqltasks[] = "(campaign_id = " . $arrExtra["campaign_id"].")";
			}
			if(isset($arrExtra["mission_id"]))
			{
				$arrSqltasks[] = "(mission_id = " . $arrExtra["mission_id"].")";
			}
			if(!empty($arrSqltasks))
			{
				$strSqlTasks .= " (" . join(" AND ", $arrSqltasks) . ")";
			}
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
				$arrSqltasks[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
			}
			if (count($arrSqltasks)) {
				$strSqlTasks .= " AND (" . join(" OR ", $arrSqltasks) . ")";
			}

			$arrResult = $this->_db->fetchAll($strSqlTasks);
			return $arrResult;
			exit;
			//end of task_count handler
		}
		if(//if no tables are selected throw an error
			count($arrSql)
			&& count($arrTable)
		){
			$strSql .= join(" , ",$arrTable) ." WHERE " .join(" OR " , $arrSql).";";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
		else
		{
			print "Sorry, there was an error: MI-RC101-123KJG";
		}
	}
	public function get_institution_name_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT name FROM institutions WHERE institution_id=" . $intInstitutionId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult->name;
	}

	public function get_all_of_the_networks()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "WHERE institution_type='Network'";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_of_the_institutions()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "WHERE host_id > 0 ";
		$strSql = $strSql . "AND network_id > 0 ";
		$strSql = $strSql . "ORDER BY name";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_institutions_by_network_id($intNetworkId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "WHERE network_id=" . $intNetworkId . " ";
		$strSql = $strSql . "ORDER BY name ";
		$arrResult = $this->_db->fetchAll($strSql);

		return $arrResult;
	}

	public function get_institutions_by_host_id($intHostId)
	{
		$strSql = "SELECT i.* ";
		$strSql = $strSql . "FROM institutions AS i ";
		$strSql = $strSql . "JOIN institutions AS n ON (i.network_id=n.institution_id) ";
		$strSql = $strSql . "WHERE n.host_id=" . $intHostId. " ";
		$strSql = $strSql . "AND i.is_active=1 ";
		$strSql = $strSql . "ORDER BY i.name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_institutions()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "ORDER BY name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_active_institutions()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "WHERE is_active=1 ";
		$strSql = $strSql . "ORDER BY name ";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_inactive_institutions()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "WHERE is_active=0 ";
		$strSql = $strSql . "ORDER BY name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_active_hosts()
	{
		$strSql = "SELECT * FROM institutions WHERE institution_type='Host' ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_networks_by_host_id($intHostId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "WHERE institution_type='Network' ";
		$strSql = $strSql . "AND host_id=" . $intHostId. " ";
		$strSql = $strSql . "AND is_active=1 ";
		$strSql = $strSql . "ORDER BY name";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/*public function get_active_tasks_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId . " ";
		$strSql = $strSql . "AND is_active=1 ";
		$strSql = $strSql . "ORDER BY task_name";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function get_inactive_tasks_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId . " ";
		$strSql = $strSql . "AND is_active=0 ";
		$strSql = $strSql . "ORDER BY task_name";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}*/

	public function get_hosts()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM institutions ";
		$strSql = $strSql . "WHERE institution_type='Host' ";
		$strSql = $strSql . "AND is_active=1 ";
		$strSql = $strSql . "ORDER BY name";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_campaigns()
	{
		$strSql = "SELECT * FROM campaigns ORDER BY campaign_name";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_campaigns_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM campaigns ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId . " ";
		$strSql = $strSql . "ORDER BY campaign_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function get_campaigns_by_network_id($intNetworkId)
	{
		$strSql = "SELECT c.* ";
		$strSql = $strSql . "FROM campaigns AS c ";
		$strSql = $strSql . "JOIN institutions AS i ON (c.institution_id=i.institution_id AND i.network_id=" . $intNetworkId . ") ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function get_campaigns_by_host_id($intHostId)
	{
		$strSql = "SELECT c.* ";
		$strSql = $strSql . "FROM campaigns AS c ";
		$strSql = $strSql . "JOIN institutions AS n USING (institution_id) ";
		$strSql = $strSql . "JOIN institutions AS h ON (n.network_id=h.institution_id AND h.host_id=" . $intHostId . ") ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_campaigns_by_status($intStatus)
	{
		$strSql = "SELECT * FROM campaigns WHERE is_active=" . $intStatus;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_missions()
	{
		$strSql = "SELECT * FROM missions";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_missions_by_campaign_id($intCampaignId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM missions ";
		$strSql = $strSql . "WHERE campaign_id=" . $intCampaignId;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_missions_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM missions ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function get_missions_by_network_id($intNetworkId)
	{
		$strSql = "SELECT m.* ";
		$strSql = $strSql . "FROM missions AS m ";
		$strSql = $strSql . "JOIN institutions AS i ON (m.institution_id=i.institution_id AND i.network_id=" . $intNetworkId . ") ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function get_missions_by_host_id($intHostId)
	{
		$strSql = "SELECT m.*";
		$strSql = $strSql . "FROM institutions AS h ";
		$strSql = $strSql . "JOIN institutions AS n ON (h.institution_id=n.host_id) ";
		$strSql = $strSql . "JOIN institutions AS i ON (n.institution_id=i.network_id) ";
		$strSql = $strSql . "JOIN missions AS m ON (i.institution_id=m.institution_id) ";
		$strSql = $strSql . "WHERE h.institution_id=" . $intHostId;

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_missions_by_status($intStatus)
	{
		$strSql = "SELECT * FROM missions WHERE is_active=" . $intStatus;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}


	public function get_active_tasks_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId . " ";
		$strSql = $strSql . "AND is_active=1 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function get_inactive_tasks_by_institution_id($intInstitutionId)
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE institution_id=" . $intInstitutionId . " ";
		$strSql = $strSql . "AND is_active=0 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_active_tasks_by_network_id($intNetworkId)
	{
		$strSql = "SELECT t.* ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS i ON (t.institution_id=i.institution_id AND i.network_id=" . $intNetworkId .") ";
		$strSql = $strSql . "AND t.is_active=1 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function get_inactive_tasks_by_network_id($intNetworkId)
	{
		$strSql = "SELECT t.* ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS i ON (t.institution_id=i.institution_id AND i.network_id=" . $intNetworkId .") ";
		$strSql = $strSql . "AND t.is_active=0 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_active_tasks_by_host_id($intHostId)
	{
		$strSql = "SELECT t.* ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS i ON (t.institution_id=i.institution_id AND i.host_id=" . $intHostId .") ";
		$strSql = $strSql . "AND t.is_active=1 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function get_inactive_tasks_by_host_id($intHostId)
	{
		$strSql = "SELECT t.* ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS i ON (t.institution_id=i.institution_id AND i.host_id=" . $intHostId .") ";
		$strSql = $strSql . "AND t.is_active=0 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_active_tasks()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE is_active=1 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function get_all_inactive_tasks()
	{
		$strSql = "SELECT * ";
		$strSql = $strSql . "FROM tasks ";
		$strSql = $strSql . "WHERE is_active=0 ";
		$strSql = $strSql . "ORDER BY task_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_of_the_campaigns()
	{
		$strSql = "SELECT * FROM campaigns";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_all_of_the_missions()
	{
		$strSql = "SELECT * FROM missions";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_network_by_institution($intInstitutionId)
	{
		$strSql = "SELECT network_id FROM institutions WHERE institution_id=" . $intInstitutionId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function get_host_id_by_network_id($intNetworkId)
	{
		$strSql = "SELECT host_id FROM institutions WHERE institution_id=" . $intNetworkId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	function get_institution($intInstitutionId)
	{
		$strSql = "SELECT * FROM institutions WHERE institution_id=" . $intInstitutionId;
		$arrResult = $this->_db->fetchRow($strSql);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	function get_all_of_the_hosts()
	{
		$sql = "SELECT * FROM institutions WHERE institution_type='Host'";
		$hosts = $this->_db->fetchAll($sql);
		return $hosts;
	}

	public function get_classes_by_institution_id($institution_id)
	{
		$sql = "SELECT * FROM classes WHERE institution_id=" . $institution_id;
		$classes = $this->_db->fetchAll($sql);
		return $classes;
	}

    /**
	 * Function selects all institutions based on institution id. Function will
	 * return all institution_ids that belong to a host/network in case the institution_id
	 * that we passed was a that of a host or network
	 *
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function institutions_select_by_institution_id($institution_id)
	{

		if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM institutions';
		}else{
			$utility = new Utilities();
			$childIds = $utility->getChildInstitutions($institution_id);
			$sql = 'SELECT * FROM institutions WHERE institution_id IN ('.$childIds.')';
		}

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-CSBII-JHSGDT";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		return $result;
	}

    /* Get institution profile inforamtion */
   public function institution_profile($arrParams)
    {
        if(is_array($arrParams))
        {
            $strSqlProfile = "
                SELECT
                    institutions.name,
                    institutions.address,
                    institutions.city,
                    institutions.postal,
                    institutions.state,
                    institutions.country,
                    institutions.phone,
                    institutions.image_id as 'institution_image_id',
                    users.first_name,
                    users.last_name
                    from institutions
                inner join permissions on institutions.institution_id=".$arrParams["institution_id"]."
                inner join users on permissions.user_id=".$arrParams["user_id"]."
                where users.user_id=".$arrParams["user_id"]."
                group by permissions.user_id";
            //print $strSqlProfile; exit;
            try{
                $result = $this->_db->fetchAll($strSqlProfile);
            } catch (Zend_Exception $e){
                echo "There was an error: MI-Inst_Profile-123GDT";
                if(DEV_ENV == 'devel'){
                    echo $strSqlProfile;
                    echo $e->getMessage();
                }
            }
            return $result;
        }
    }
    public function profile_edit($arrParams)
    {
        //var_dump($arrParams); exit;
        if(is_array($arrParams))
        {
            //prepare the array.Information that array will contain is only about institutiom, like address, postal, city, phone, state, country.
            $strWhereInstitution = "institution_id=".$arrParams["institution_id"];
            $strWhereUser = "user_id=".$arrParams["user_id"];
            $arrFieldsInstitution = array(
                "name"      => $arrParams["name"],
                "address"   => $arrParams["address"],
                "city"   => $arrParams["city"],
                "state"   => $arrParams["state"],
                "country"   => $arrParams["country"],
                "postal"   => $arrParams["postal"],
                "phone"   => $arrParams["phone"]
            );
            $arrFieldsUser = array (
                "first_name" => $arrParams["first_name"],
                "last_name" => $arrParams["last_name"]
            );

            $boolResult = $this->_db->update("institutions", $arrFieldsInstitution, $strWhereInstitution);
            $boolResult = $this->_db->update("users", $arrFieldsUser, $strWhereUser);
            return $boolResult;
        }
    }
    public function update_institution_images($arrUpdate)
    {
        //var_dump($arrUpdate); exit;
		if(is_array($arrUpdate))
		{
			if($arrUpdate["mode"] == "add")
			{
				$sqlUpdate = "UPDATE institutions set image_id=". $arrUpdate["image_id"]." WHERE institution_id=".$arrUpdate["institution_id"];
				$result = $this->_db->query($sqlUpdate);
			}
			if($arrUpdate["mode"] == "delete")
			{
				$sqlUpdate = "UPDATE institutions set image_id=null WHERE image_id=".$arrUpdate["image_id"]." AND institution_id=".$arrUpdate["institution_id"];
				$result = $this->_db->query($sqlUpdate);
			}
		return $result;
		}
    }

}
?>
