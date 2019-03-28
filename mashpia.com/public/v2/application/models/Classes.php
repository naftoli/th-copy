<?php
/*
	// Table of contents
*/

class Classes
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
	
	public function getMashpiaUsers(array $classes) {
		if (!empty($classes)) {
			$strSql = "select class_id, user_id from mashpiadb.users where class_id in (" . implode(',', $classes) . ") and user_registered > 0";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		} else {
			return array();
		}
	}
	
	public function getMashpiaClasses(array $users) {
		if (!empty($users)) {
			$ids = array();
			foreach ($users as $user => $val) {
				$ids[] = $user;
			}
			$strSql = "select class_id, user_id from mashpiadb.users where user_id in (" . implode(',', $ids) . ")";
			//echo $strSql; exit;
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		} else {
			return array();
		}
	}

	// Generic functions
	public function _classes_select ($arrParams)
	{
		if (isset($arrParams["class_id"])) {
			if (!empty($arrParams["class_id"])) {
				if (!is_array($arrParams["class_id"])) {
					$arrParams["class_id"] = array($arrParams["class_id"]);
				}
				$strSql = "select * from mashpiadb.classes where class_id in (" . implode(',', $arrParams["class_id"]) . ")";
			} else {
				return array();
			}
		} else {
			if (! $this->_user_session_data->institution_id) {
				$this->_redirect('logout');
			}
			$strSql = "select * from mashpiadb.classes where class_era = 0 and school_id = " . $this->_user_session_data->institution_id;
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
		
		/*
		$query = new QueryGen();
		if (!isset($arrParams["_ORDER"]))
			$arrParams["_ORDER"] = "class_hierarchy+0";
		$arrResult = $query->classes__select($arrParams);
		$arrTempResult = array();
		foreach ($arrResult as $objItem)
		{
			$objItem->custom_name1 = strtoupper($objItem->grade . " " . $objItem->sub);
			$arrTempResult[] = $objItem;
		}
		return $arrTempResult;
		*/
	}

	public function _classes_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"class_name" => 	@$arrParams["class_name"],
			"institution_id" => @$arrParams["institution_id"],
			"grade" => 			@$arrParams["grade"],
			"grade_id" => 		@$arrParams["grade_id"],
			"sub" => 			@$arrParams["sub"],
			"gender" => 		@$arrParams["gender"],
			"is_active" => 		@$arrParams["is_active"],
			"created"			=> date("Y-m-d H:i:S"),
			"created_by"		=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("classes", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _classes_update($arrParams)
	{
		$arrValuesParams = array("class_name", "sub", "institution_id", "grade", "grade_id", "gender", "is_active");
		$arrWhereParams = array("class_id", "class_name", "sub", "institution_id", "grade", "gender", "grade_id", "is_active", "created", "modified", "created_by");

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
		$boolResult = $this->_db->update("classes", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _classes_delete($arrParams)
	{
		$arrWhereParams = array("class_id","class_name","institution_id","grade","grade_id","sub","gender","is_active","created","modified","created_by");
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
		$boolResult = $this->_db->delete("classes", $arrFeilds);
		return $boolResult;
	}

	public function _user_classes_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_class_id"		=> @$arrParams["user_class_id"],
			"class_id"			=> @$arrParams["class_id"],
			"user_id"			=> @$arrParams["user_id"],
			"class_role"		=> @$arrParams["class_role"],
			"created"			=> @$arrParams["created"],
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				user_classes
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
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function _user_classes_update($arrParams)
	{
		$arrValuesParams = array("class_id","user_id","class_role");
		$arrWhereParams = array("user_class_id","class_id","user_id","class_role","created","modified","created_by");

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
		$boolResult = $this->_db->update("user_classes", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _user_classes_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"class_id"			=> @$arrParams["class_id"],
			"user_id"			=> @$arrParams["user_id"],
			"class_role"		=> @$arrParams["class_role"],
			"created"			=> date("Y-m-d H:i:S"),
			"created_by"		=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("user_classes", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	// This function will find the institutions under a host or network and drill down by permission
	public function _classes_select_hierarchal ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Create inline join statement depending on the hierarchy of the ids provided
		// The results only need to be narrowed down by the lowest point of hierarchy
		if (
			isset($arrParams["network_id"])
			&& (
				$arrParams["network_id"] === 0
				|| $arrParams["network_id"]
			)
		) {
			$strSqlInstitutions = "
				(
					institutions.network_id = " . $arrParams["network_id"] . "
					OR institutions.institution_id = " . $arrParams["network_id"] . "
				)";
		} else if (
			isset($arrParams["host_id"])
			&& (
				$arrParams["host_id"] === 0
				|| $arrParams["host_id"]
			)
		) {
			$strSqlInstitutions = "
				(
					institutions.host_id = " . $arrParams["host_id"] . "
					OR institutions.institution_id = " . $arrParams["host_id"] . "
				)";
		}
		$strSql = "
			SELECT
				DISTINCT classes.*
			FROM
				classes";
		if (isset($strSqlInstitutions))
			$strSql .= ",institutions";
		$strSql .= "
			WHERE
				1";
		if (isset($strSqlInstitutions))
		{
			$strSql .= "
				AND " . $strSqlInstitutions . "
				AND institutions.institution_id = classes.institution_id";
		}

		// Possible column selections
		$arrColumns = array (
			"classes.class_id"			=> @$arrParams["class_id"],
			"classes.class_name"		=> @$arrParams["class_name"],
			"classes.sub"				=> @$arrParams["sub"],
			"classes.institution_id"	=> @$arrParams["institution_id"],
			"classes.grade"				=> @$arrParams["grade"],
			"classes.gender"			=> @$arrParams["gender"],
			"classes.is_active"			=> @$arrParams["is_active"],
			"classes.created"			=> @$arrParams["created"],
			"classes.modified"			=> @$arrParams["modified"],
			"classes.created_by"		=> @$arrParams["created_by"]
		);

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
					AND " . $strColumn . " = " . $Value . "
				";
			}
		}
		$strSql .= "
			ORDER BY classes.class_name ASC
			LIMIT 1000
		";
		//print $strSql;exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _user_classes_delete($arrParams)
	{
		$arrWhereParams = array("user_class_id","class_id","user_id","class_role","created","modified","created_by");
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
		$boolResult = $this->_db->delete("user_classes", $arrFeilds);
		return $boolResult;
	}
	// Generic functions end

	/*
	 * Select users from the user table based on their association made by the
	 * user_classes table.
	 */
	public function user_classes_select_user($arrParams)
	{
		if (!is_array($arrParams) || !count($arrParams))
		{
			print "Sorry, there was an error: MC-UCSU101-SD78FD";
			exit;
		}
		$arrUserClasses = $this->_user_classes_select($arrParams);
		$arrUsers = array();
		foreach ($arrUserClasses as $objUserClass)
		{
			$arrUsers[] = $objUserClass->user_id;
		}
		// Select all the users from the associations
		if (count($arrUsers))
		{
			$strSql = "
				SELECT
					*
				FROM
					users
				WHERE
					user_id IN (" . join(",", $arrUsers) . ")";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
		// Nothing found
		return array();
	}

	/*
	 * Derive the grade(s) of the user from the current classes the indiviual is enrolled to
	 * Params: user_id
	 */
	public function user_grades_select($arrParams)
	{
		if (!isset($arrParams["user_id"]) || !$arrParams["user_id"])
		{
			print "Sorry, there was an error: MC-UGS101-D7GFF8";
			exit;
		}
		$strSql = "
			SELECT
				classes.grade
			FROM
				user_classes,
				classes
			WHERE
				user_classes.user_id=" . $arrParams["user_id"] . "
				AND classes.class_id=user_classes.class_id";
		$arrResult = $this->_db->fetchAll($strSql);
		// The user must be enrolled into a class to continue
		$arrGrade = array();
		if (count($arrResult))
		{
			foreach ($arrResult as $objClass)
			{
				$arrGrade[] = $objClass->grade;
			}
		}
		return $arrGrade;
	}

	public function user_classes_delete($arrExtra=0)
	{
		if (
			is_array($arrExtra)
			&& count($arrExtra)
		) {
			$arrQuery = array();

			if (isset($arrExtra["user_id"]))
				$arrQuery[] = "user_id=" . $arrExtra["user_id"];
			if (isset($arrExtra["class_id"]))
				$arrQuery[] = "class_id=" . $arrExtra["class_id"];
			if (isset($arrExtra["user_class_id"]))
				$arrQuery[] = "user_class_id=" . $arrExtra["user_class_id"];
			if (count($arrQuery))
			{
				//var_dump($arrQuery); exit;
				$boolResult = $this->_db->delete("user_classes", join(" AND ", $arrQuery));
				return $boolResult;
			}
		}
	}

	public function user_classes_select($arrExtra=0)
	{
		// Query
		$strSql = "
			SELECT
				*
			FROM
				user_classes
			INNER JOIN permissions USING (user_id)
			WHERE
				1";
		if (is_array($arrExtra))
		{
			if (isset($arrExtra["user_id"]))
				$strSql .= "
				AND user_id = " . $arrExtra["user_id"];
			if (isset($arrExtra["user_class_id"]))
				$strSql .= "
				AND user_class_id = " . $arrExtra["user_class_id"];
			if (isset($arrExtra["class_id"]))
				$strSql .= "
				AND class_id = " . $arrExtra["class_id"];
		}
		//print $strSql; exit;
		// Result
		$arrClasses = $this->_db->fetchAll($strSql);
		return $arrClasses;
	}

	/**
	 * Selects teachers and students that belong to a particular class
	 *
	 * @parma: int class_id
	 *
	 * @return arr arrResult
	 *
	 */
	public function user_classes($class_id)
	{
		if($class_id){
			$sqlWhere = 'WHERE user_classes.class_id = '.$class_id;
		}else{
			$sqlWhere = '';
		}
		$strSql = '
		SELECT * FROM user_classes
		INNER JOIN users
		ON user_classes.user_id = users.user_id
		'.$sqlWhere.'
		ORDER BY user_classes.class_role DESC, users.last_name ASC LIMIT 100';

		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_Exception $e){
			echo "There was an error: MC-UCU-101-DHSJK8";
			if(DEV_ENV == 'devel') echo $strSql;
		}
		return $arrResult;
	}

	/**
	 * Selects teachers and students which belong to classes of a given institution
	 *
	 * @parma: int class_id
	 *
	 * @return arr arrResult
	 *
	 */
	public function user_classes_by_institution($institution_id)
	{
		$strSql = '
		SELECT * FROM classes WHERE classes.institution_id = '.$institution_id;

		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_Exception $e){
			echo "There was an error: MC-MCBI-103-DLFO85";
			if(DEV_ENV == 'devel') echo $strSql;
		}

		$sqlWhere = '';
		foreach($arrResult as $r){
			$sqlWhere .= ' user_classes.class_id = '.$r->class_id. ' OR ';
		}
		//truncate string
		$sqlWhere = substr($sqlWhere, 0, strlen($sqlWhere)-4);
		$sqlWhere = " WHERE (".$sqlWhere.")";

		$strSql = '
		SELECT * FROM user_classes
		INNER JOIN users
		ON user_classes.user_id = users.user_id';

		$strSql = $strSql . $sqlWhere;

		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_Exception $e){
			echo "There was an error: MC-MCBI-111-GHJSD9";
			if(DEV_ENV == 'devel') echo $strSql;
		}
		return $arrResult;
	}

	public function user_classes_insert($arrQuery)
	{
		// Filter
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Validate
		if (!in_array($arrQuery["class_role"], array("Student", "Teacher")))
		{
			print "Sorry, there was an error: MC-UCI101-89ET7Y";
			exit;
		}
		if (!isset($arrQuery["class_id"]))
		{
			print "Sorry, there was an error: MC-UCI102-T4YUYJ4";
			exit;
		}
		if (!isset($arrQuery["user_id"]))
		{
			print "Sorry, there was an error: MC-UCI103-XC4VX78";
			exit;
		}

		// Build data to insert
		$arrFields = array (
			"class_id" => $arrQuery["class_id"],
			"user_id" => $arrQuery["user_id"],
			"class_role" => $arrQuery["class_role"],
			"created" => date("Y-m-d H:i:S"),
			"created_by" => $this->_user_session_data->user_id
		);

		// Execute
		$boolResult = $this->_db->insert("user_classes", $arrFields);
		return $boolResult;
	}

	public function user_classes_update($arrQuery)
	{
		// Filter
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Validate
		if (!in_array($arrQuery["class_role"], array("Student", "Teacher")))
		{
			print "Sorry, there was an error: MC-UCU101-G32H1J";
			exit;
		}
		if (!isset($arrQuery["class_id"]))
		{
			print "Sorry, there was an error: MC-UCU102-VBN414";
			exit;
		}
		if (!isset($arrQuery["user_id"]))
		{
			print "Sorry, there was an error: MC-UCU103-48SDFS";
			exit;
		}
		if (!isset($arrQuery["user_class_id"]))
		{
			print "Sorry, there was an error: MC-UCU104-ER4T8R";
			exit;
		}

		// Build data to insert
		$arrFields = array (
			"class_id" => $arrQuery["class_id"],
			"user_id" => $arrQuery["user_id"],
			"class_role" => $arrQuery["class_role"],
			"created" => date("Y-m-d H:i:S"),
			"created_by" => $this->_user_session_data->user_id
		);

		$strSql = "user_class_id=" . $arrQuery["user_class_id"];

		// Execute
		$boolResult = $this->_db->update("user_classes", $arrFields, $strSql);
		return $boolResult;
	}

	public function classes_insert($arrQuery)
	{
		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the insert
		$arrFields = array (
			"class_name" => $arrQuery["class_name"],
			"sub" => $arrQuery["sub"],
			"institution_id" => $arrQuery["institution_id"],
			"grade" => $arrQuery["grade"],
			"gender" => $arrQuery["gender"],
			"created" => $arrQuery["created"],
			"created_by" => $this->_user_session_data->user_id
		);
		// Execute
		$intResult = $this->_db->insert("classes", $arrFields);
		return $intResult;

	}

	/**
	 * Function returns class information and teacher names that are assigned to
	 * the class in question.
	 *
	 * @param $arrExtra - holds institution ids
	 *
	 * @return $arrAllResults
	 *
	 * data structure:
	 * Array
	 *	(
	 *		[0] => stdClass Object
	 *		(
	 *			[class_id] 		=>
	 *			[class_name] 	=>
	 *			[sub] 			=>
	 *			[grade] 		=>
	 *			[teacher] 		=>
	 *		)
	 *	)
	 */
	public function classes_select($arrExtra)
	{

		$strSql = "
			SELECT
				*
			FROM
				classes
			WHERE
				1";
		if (count($arrExtra))
		{
			if (isset($arrExtra["institution_id"]) && $arrExtra["institution_id"]!=0)
				$strSql .= "
				AND institution_id=" . $arrExtra["institution_id"];
			if (isset($arrExtra["active"]))
				$strSql .= "
				AND is_active=" . $arrExtra["active"];
			if (isset($arrExtra["class_id"]))
				$strSql .= "
				AND class_id=" . $arrExtra["class_id"];
		}
		$strSql .=" ORDER BY class_id, grade, sub ASC";
		$arrResult = $this->_db->fetchAll($strSql);
		//print $strSql; exit;
		$i = 0;
		if(count($arrResult) > 0)
		{
			foreach($arrResult as $r )
			{
				$sql = '
				SELECT * FROM user_classes
				INNER JOIN users
				ON user_classes.user_id = users.user_id
				WHERE user_classes.class_id = '.$r->class_id.'
				AND user_classes.class_role = "Teacher"';
				$teacherBuffer = array();
				$arrTeachers = $this->_db->fetchAll($sql);
				foreach($arrTeachers as $t){
					$teacherBuffer[] = $t->first_name . ' ' . $t->last_name;
				}

				$objTeachers 				= new stdClass();
				$objTeachers->class_id 		= $r->class_id;
				$objTeachers->is_active 	= $r->is_active;
				$objTeachers->class_name 	= $r->class_name;
				$objTeachers->sub 			= $r->sub;
				$objTeachers->grade 		= $r->grade;
				$objTeachers->teacher 		=join(", ", $teacherBuffer);

				$arrAllResult[$i] = $objTeachers;

				unset($objTeachers);
				unset($teacherBuffer);
				$i++;
			}
			if(count($arrAllResult) > 0)
			{
				return $arrAllResult;
			}
			else{
				return $arrAllResult = array();
			}
		}
		else{
			return $arrAllResult = array();
		}
	}


	public function classes_select_by_role($user_id, $role, $status=1)
	{
		$strSql = '
		SELECT * FROM
			user_classes
		INNER JOIN
			classes
		ON
			user_classes.class_id = classes.class_id
		INNER JOIN
			users
		ON
			users.user_id = user_classes.user_id
		WHERE
			user_classes.class_role = "'.$role.'"
			AND
			user_classes.user_id = '.$user_id.'
			AND classes.is_active = ' . $status
			;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/*
	 * Descrition: Select all the classes a user is assigned to
	 * Default user is the user id in the current session
	 */
	public function classes_select_user($intUser=0, $strPermission='', $intInstitutionId=0)
	{
		// Validation
		if (!$intUser)
			$intUser = $this->_user_session_data->user_id;

		if ($strPermission == "Institution Administrator")
		{
			$strSql = "SELECT * FROM classes WHERE institution_id=" . $intInstitutionId;
			$arrClasses = $this->_db->fetchAll($strSql);
			return $arrClasses;
		}
		else
		{
			// Query
			$strSql = "
				SELECT
					classes.*
				FROM
					user_classes,
					classes
				WHERE
					user_classes.user_id = " . $intUser . "
					AND user_classes.user_id = " . $intUser . "
					AND classes.class_id = user_classes.class_id;";

			// Result
			$arrClasses = $this->_db->fetchAll($strSql);
			return $arrClasses;
		}
	}

	/*
	 * Select all classes available for a user
	 */
	public function classes_permissions_select($intUser)
	{
		// Validation
		if (
			!isset($intUser)
			|| !$intUser
			|| !preg_match("/^[0-9]+$/", $intUser)
		) {
			print "Sorry, there was an error: MC-CPS101-SD6F54";
			exit;
		}

		// Query
		$strSql = "
			SELECT
				classes.*
			FROM
				permissions,
				classes
			WHERE
				permissions.user_id = " . $intUser . "
				AND permissions.institution_id = classes.institution_id;";

		// Result
		$arrClasses = $this->_db->fetchAll($strSql);
		return $arrClasses;
	}

	/*
	 * Select all classes available within this session
	 * Default institution is the current institution in the session
	 */
	public function classes_select_institution($intInstitution=0)
	{
		// Validation
		if($this->_user_session_data->permission == "Super Administrator" && !$intInstitution){
			$sqlWhere = '';
		} elseif(!$intInstitution) {
			$intInstitution = $this->_user_session_data->institution_id;
			$sqlWhere = " WHERE institution_id = " . $intInstitution;
		}else{
			$sqlWhere = " WHERE institution_id = " . $intInstitution;
		}

		// Query
		$strSql = "
			SELECT
				*
			FROM
				classes";


		// Result
		$strSql = $strSql . $sqlWhere . " ORDER BY grade,sub ASC";
		//print $strSql ; exit;
		$arrClasses = $this->_db->fetchAll($strSql);
		return $arrClasses;
	}

	/**
	 * Selects all classes belonging to a host
	 *
	 * @param: int host_id
	 *
	 * @return: arr arrResult
	 *
	 */
	public function classes_select_by_institution($arrParam=0)
	{
		//get host_id for institution_id
		$strSql = "
			SELECT
				*
			FROM
				classes
			JOIN
				institutions on institutions.institution_id=classes.institution_id
			";
			if(
				!isset($arrParam["host_id"])
				&& !isset($arrParam["network_id"])
				&& !isset($arrParam["institution_id"])
			){
				$strSql .= " ORDER BY classes.grade ASC";
				$arrResult = $this->_db->fetchAll($strSql);
				return $arrResult;
				exit;
			}

			$arrSqlWhere = array();
			if(isset($arrParam["host_id"]))
			{
				$arrSqlWhere[] = "(institutions.host_id= ".$arrParam["host_id"].")";
			}
			if(isset($arrParam["network_id"]))
			{
				$arrSqlWhere[] ="(institutions.network_id= ".$arrParam["network_id"].")";
			}
			if(isset($arrParam["institution_id"]))
			{
				$arrSqlWhere[] = "(institutions.institution_id= ".$arrParam["institution_id"].")";
			}

			$strSql .= " WHERE " .join(" AND ", $arrSqlWhere) . " ORDER BY classes.grade ASC";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;

		/*$strSql = '
		SELECT * FROM institutions
		WHERE institutions.host_id = {arrParam["host_id"]}';

		try{
			$arrInstitutions = $this->_db->fetchAll($strSql);
		} catch(Zend_Exception $e){
			echo "There was an error: MC-CSBH-101-DKJFIU";
			if(DEV_ENV == "devel") echo $strSql;
		}

		$sqlWhere = 'WHERE ';
		foreach($arrInstitutions as $r){
			$sqlWhere .= " institution_id = ".$r->institution_id ." OR ";
		}
		$sqlWhere = substr($sqlWhere, 0, strlen($strSql)-4);

		$strSql = '
		SELECT * FROM classes WHERE '.$strSql;

		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}
		catch(Zend_Exception $e){
			echo 'There was an error: MC-CSBH-102-G98EH3';
			if(DEV_ENV == 'devel') echo $strSql;
		}

		return $arrResult;
		*/
	}

	public function classes_select_by_name($strClassName, $strGender, $strGrade, $strSub, $intInstId)
	{
		$strSql = '
			SELECT
				*
			FROM
				classes
			WHERE
				class_name = "' . $strClassName.'"
			AND
				gender = "' . $strGender.'"
			AND
				grade = "' . $strGrade.'"
			AND
				sub = "' . $strSub.'"
			AND
				institution_id = ' . $intInstId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function classes_update($arrQuery, $intId)
	{
		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue)
		{
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		$arrFeilds = array("class_name"	=> $arrQuery['class_name'],
							"sub"     		=> $arrQuery['sub'],
                            "gender"        => strtolower($arrQuery['gender']),
                            "grade"         => $arrQuery['grade']);

		// Execute
		$intResult = $this->_db->update("classes", $arrFeilds, "class_id=" . $intId);
		return $intResult;
	}

	public function classes_delete($arrParams)
	{
		if(is_array($arrParams))
		{
			if(isset($arrParams["class_id"]))
			{
				$intResult = $this->_db->delete("classes", "class_id=" . $arrParams["class_id"]);
				return $intResult;
			}
		}
	}
	public function classes_select_by_id($class_id)
	{
		$strSql = '
         SELECT
                  *
         FROM
                  classes
         WHERE
                  class_id = '.$class_id;

		$result = $this->_db->fetchRow($strSql);
		return $result;
	}

	/**
	 * Function selects all classes based on institution id.
	 *
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function classes_select_by_institution_id($institution_id)
	{

		if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM classes';
		}else{
			$utility = new Utilities();
			$childIds = $utility->getChildInstitutions($institution_id);
			$sql = 'SELECT * FROM classes WHERE institution_id IN ('.$childIds.')';
		}

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-CSBII-JHDGF6";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		//echo $sql; exit;
		return $result;
	}

	public function get_all_active_classes()
	{
		$strSql = "SELECT * FROM classes WHERE is_active=1";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_active_classes_by_user_id($user_id)
	{
		$strSql = "
		SELECT * FROM classes
		INNER JOIN user_classes
		ON classes.class_id = user_classes.class_id
		WHERE is_active=1 AND user_classes.user_id=" . $user_id;
		$arrResult = $this->_db->fetchAll($strSql);
		try{
			$result = $this->_db->fetchAll($strSql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-GACBUI-F6SF56";
			if(DEV_ENV == 'devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		//echo $strSql; exit;
		return $result;
	}

	public function get_active_classes_by_institution_id($institution_id)
	{
		$strSql = "SELECT * FROM classes WHERE is_active=1 AND institution_id=" . $institution_id;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_inactive_classes_by_institution_id($institution_id)
	{
		$strSql = "SELECT * FROM classes WHERE is_active=0 AND institution_id=" . $institution_id;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_classes_by_institution_id($institution_id, $status=1)
	{
		$strSql = "
		SELECT * FROM classes
		WHERE institution_id=" . $institution_id;
		//AND is_active = " . $status;
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_classes_by_network_id($network_id, $status=1)
	{
		$strSql = "SELECT c.* ";
		$strSql = $strSql . "FROM classes AS c ";
		$strSql = $strSql . "JOIN institutions AS i ON (c.institution_id=i.institution_id) ";
		$strSql = $strSql . "WHERE i.network_id=" . $network_id;
		//$strSql = $strSql . " AND c.is_active=" . $status;

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_active_classes_by_host_id($host_id)
	{
		$strSql = "SELECT c.* ";
		$strSql = $strSql . "FROM classes AS c ";
		$strSql = $strSql . "JOIN institutions AS i ON (c.institution_id=i.institution_id) ";
		$strSql = $strSql . "JOIN institutions AS n ON (i.network_id=n.institution_id) ";
		$strSql = $strSql . "WHERE n.host_id=" . $host_id;
		$strSql = $strSql . " AND c.is_active=1";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_inactive_classes_by_host_id($host_id)
	{
		$strSql = "SELECT c.* ";
		$strSql = $strSql . "FROM classes AS c ";
		$strSql = $strSql . "JOIN institutions AS i ON (c.institution_id=i.institution_id) ";
		$strSql = $strSql . "JOIN institutions AS n ON (i.network_id=n.institution_id) ";
		$strSql = $strSql . "WHERE n.host_id=" . $host_id;
		$strSql = $strSql . " AND c.is_active=0";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_classes_by_host_id($host_id, $status=1)
	{
		$strSql = "SELECT c.* ";
		$strSql = $strSql . "FROM classes AS c ";
		$strSql = $strSql . "JOIN institutions AS i ON (c.institution_id=i.institution_id) ";
		$strSql = $strSql . "JOIN institutions AS n ON (i.network_id=n.institution_id) ";
		$strSql = $strSql . "WHERE n.host_id=" . $host_id;
		//$strSql = $strSql . " AND c.is_active=" . $status;
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

/**************************************************************************************************
	This functions are used ONLY during the import process where we migrate data from the old system
	into the new one.
**************************************************************************************************/

	/** Function insert classes from an imported classes_sql file.
	 * This file contains classes from the old database but for the current school year.
	 *
	 */
	public function inject_classes()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/old_classes.csv", 'r');
		$this->_db->query("SET NAMES `UTF8`");
		$arrParams = array();
		$arrParams2 = array();
		while(($data = fgetcsv($file, 1000, ",")) !== false)
		{
			$class_id = $data[0];
			$class_id = preg_replace("/'/", "", $class_id);
			$arrParams["old_class_id"] = $class_id;

			$school_id = $data[1];
			$school_id = preg_replace("/'/", "", $school_id);
			$arrParams["institution_id"] = $school_id;

			$class_grade = $data[2];
			$class_grade = preg_replace("/'/", "", $class_grade);
			$arrParams["grade"] = $class_grade;

			$class_sub = $data[3];
			$class_sub = preg_replace("/'/", "", $class_sub);
			$arrParams["sub"] = $class_sub;

			$class_teacher = $data[4];
			$class_teacher = preg_replace("/'/", "", $class_teacher);
			$arrParams["class_name"] = $class_teacher;

			$default_level = $data[5];
			$default_level = preg_replace("/'/", "", $default_level);

			$gender_view = $data[6];
			$gender_view = preg_replace("/'/", "", $gender_view);
			$arrParams["gender"] = "mixed";

			$arrParams["is_active"] = 1;
			$arrParams["created"] = date ("Y-m-d H:i:S");;
			$arrParams["modified"] = '';
			$arrParams["created_by"] = '';

			if($class_teacher !="class_teacher")
			{

				$intAI = $this->_db->insert("classes", $arrParams);
				if($intAI)
				{
					$new_class_id = $this->_db->lastInsertId();
				}
				if($arrParams["class_name"] != "")
				{
					$sqlStr = "
						SELECT
							users.user_id
						FROM
							users
						WHERE
							first_name='$class_teacher'";

					$arrResult = $this->_db->fetchRow($sqlStr);

					$arrParams2["class_id"] = $new_class_id;
					$arrParams2["user_id"] = $arrResult->user_id;
					$arrParams2["class_role"] = 'Teacher';
					$arrParams2["created"] = date ("Y-m-d H:i:S");;
					$arrParams2["modified"] = '';
					$arrParams2["created_by"] = '';

					if($arrParams2["user_id"]!="")
					{
						$this->_db->insert("user_classes", $arrParams2);
					}
				}
			}
		}
	}


	public function injectinstitutions()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/old_schools.csv", 'r');
		$this->_db->query("SET NAMES `UTF8`");
		if(DEV_ENV == "production")
		{
			$this->_db->query("TRUNCATE TABLE institutions;");
			$this->_db->query("TRUNCATE TABLE legacy_lookup;");
			$this->_db->query("INSERT INTO institutions (institution_type,host_id,network_id,name,is_active,address,city,state,country,website,created,created_by) values ('Host',0,0,'IMS Host',1,'5111 De Courtrai','Montreal','Quebec','Canada','http://www.mashpia.com',now(),1);");
			$host_id = $this->_db->lastInsertId();
			$this->_db->query("INSERT INTO institutions (institution_type,host_id,network_id,name,is_active,address,city,state,country,website,created,created_by) values ('Network',1,0,'IMS Network',1,'5111 De Courtrai','Montreal','Quebec','Canada','http://www.mashpia.com',now(),1);");
			$network_id = $this->_db->lastInsertId();
			while(($data = fgetcsv($file, 1000, ",")) !== false)
			{
				$old_school_id = $data[0];
				$old_school_id = preg_replace("/'/", "", $old_school_id);
				//$arrParams["old_school_id"] = $old_school_id;

				$name = $data[1];
				$name = preg_replace("/'/", "", $name);
				$arrParams["name"] = $name;

				$hebrew_name = $data[2];
				$hebrew_name = preg_replace('/"/', "", $hebrew_name);
				$arrParams["hebrew_name"] = $hebrew_name;

				$address = $data[13];
				$address = preg_replace("/'/", "", $address);
				$arrParams["address"] = $address;

				$city = $data[15];
				$city = preg_replace("/'/", "", $city);
				$arrParams["city"] = $city;

				$state = $data[16];
				$state = preg_replace("/'/", "", $state);
				$arrParams["state"] = $state;

				$country = $data[17];
				$country = preg_replace("/'/", "", $country);
				$arrParams["country"] = $country;

				$postal = $data[18];
				$postal = preg_replace("/'/", "", $postal);
				$arrParams["postal"] = $postal;

				$phone = $data[19];
				$phone = preg_replace("/'/", "", $phone);
				$arrParams["phone"] = $phone;

				$arrParams["is_active"] = 1;

				$created = $data[44];
				$created = preg_replace("/'/", "", $created);
				$arrParams["created"] = $created;
				$arrParams["host_id"] = $host_id;
				$arrParams["network_id"] = $network_id;
				$arrParams["institution_type"] = "School";
				$arrParams["modified"] = date("Y-m-d H:i:S");
				$arrParams["created_by"] = "";
				if($old_school_id !='school_id')
				{
					$intAI = $this->_db->insert("institutions", $arrParams);
					if($intAI)
					{
						$new_school_id = $this->_db->lastInsertId();
					}
					$arrParam2 = array();
					$arrParam2["legacy_id"] = $old_school_id;
					$arrParam2["ims_id"] = $new_school_id;
					$arrParam2["type"] = "institutions";
					$this->_db->insert("legacy_lookup", $arrParam2);
				}
				unset($arrParam2);
				unset($arrParams);
			}
		}
		else
		{
			print "Sorry, you cannot run this script.";
		}
	}
	public function get_class_id_by_user_id($user_id)
	{
		$strSql = "Select class_id from user_classes where user_id=". $user_id;
		$result = $this->_db->fetchAll($strSql);
		return $result;
	}

	/**
	 * Imports students and sets up permissions
	 *
	 */
	/*public function importStudents()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/students.csv", 'r');
		$this->_db->query("SET NAMES `UTF8`");

		//clear all previous data
		$this->_db->query("TRUNCATE TABLE users;");
		$this->_db->query("TRUNCATE TABLE permissions;");

		if(DEV_ENV == "production")
		{
			while(($data = fgetcsv($file, 1000, ",")) !== false)
			{
				if($data[0] == "school_id") continue;

				$arrStudentInsert = array("email"	=> "",
										  "password"	=> "",
										  "bar_code"	=> $data[],
										  "first_name"	=> $data[],
										  "last_name"	=> $data[],
										  "hebrew_first_name"	=> $data[],
										  "hebrew_last_name"	=> $data[],
										  "dob"	=> $data[29],
										  "is_active"	=> $data[],
										  "email"	=> $data[],
										  "email"	=> $data[],
										  "email"	=> $data[],
										  "email"	=> $data[],
										  "email"	=> $data[],
										  "email"	=> $data[],
										  "email"	=> $data[],
										  "email"	=> $data[],)
			}
		}
		else
		{
			print "Sorry, you cannot run this script.";
		}
	}*/
}
?>
