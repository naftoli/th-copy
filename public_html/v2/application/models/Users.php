<?php
class Users
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
	
	public function getMashpiaAdmin() {
		$strSql = "select first, last from mashpiadb.admins a where admin_id = " . $this->_user_session_data->user_id;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	public function getMashpiaTeacher() {
		$strSql = "select a.first, a.last, c.class_id, c.class_grade, c.class_sub
					from mashpiadb.admins a
					join mashpiadb.admin_auths aa using (admin_id)
					join mashpiadb.classes c on c.class_id = aa.id 
					where a.admin_id = " . $this->_user_session_data->user_id . "
					and aa.auth = 'class'";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	public function getMashpiaUser($code) {
		if (!$code) return array();
		$strSql = "select * from mashpiadb.users where user_code = " . $code;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	public function getMashpiaUserById(array $users) {
		if (!empty($users)) {
			$strSql = "select * from mashpiadb.users where user_id in (" . implode(',', $users) . ")";
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		} else {
			return array();
		}
	}
	
	public function getClassUsers(array $classes) {
		$strSql = "select user_id, first, last from mashpiadb.users where class_id in (" . implode(',', $classes) . ") and user_registered > 0";
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	public function getLadders(array $users, array $subjects) {
		$strSql = "select level from mashpiadb.user_tracks where user_id in (" . implode(',', $users) . ") and subject_id in (" . implode(',', $subjects) . ") and level > 0 group by level";
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		//echo "<pre>"; print_r($arrResult); exit;
		return $arrResult;
	}

	/* Function Authenticate
	 * @Params: email,password
	 * @Return: 1 for Authenticated, Error Code for failure
	 * Authenticates a user by checking their email/password in the users table
	 */

	function Authenticate($email, $password, $strPermission=false, $strTstyle=NULL)
	{
		$query = new QueryGen();
		Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
		Zend_Auth::getInstance()->clearIdentity();

		if($email == '')
		{
			$this->redirect('/');
			break;
		}
		// setup Zend_Auth adapter for a database table
		$db = Zend_Registry::get('db');
		$authAdapter = new Zend_Auth_Adapter_DbTable($db,'users','email','password');

		// Set the input credential values to authenticate against
		$authAdapter->setIdentity($email);
		$strPassword = md5($password);
		if ($password == MASTER_PASSWORD_X32G0SS8P)
		{
			$objPassword = first($this->_users_select(array(
				"email" => $email
			)));
			if (!$objPassword) {
				return -2;
			}
			$strPassword = $objPassword->password;
		}
		$authAdapter->setCredential($strPassword);

		// do the authentication
		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($authAdapter);

		// Check if the user is valid
		if ($result->isValid())
		{
			$data = $authAdapter->getResultRowObject(null,'password');
			$auth->getStorage()->write($data);
			$auth = Zend_Auth::getInstance();

			// Does the user have an identity?
			if ($auth->hasIdentity())
			{
				$user_id = $auth->getIdentity()->user_id;
				$is_user_active = $auth->getIdentity()->is_active;

				// check if user is active
				if ($is_user_active)
				{
					// create a new zend session
					$user_session = new Zend_Session_Namespace('user_session_data');

					$user_session->original = true;
					$user_session->frommashpia = false;

					// set its expiry to 5 minutes
					$user_session->setExpirationSeconds(9999999999);

					// set user_id and status in the session
					$user_session->user_id = $user_id;
					$user_session->is_user_active = $is_user_active;

					$objInstitutions = new Institutions();
					$objPermissions = new Permissions();

					$intPermission = first(explode(":", $strPermission));
					if ($intPermission > 0)
					{
						$objPermission = first($query->permissions__select(array(
							"permission_id" => $intPermission,
							"template_style" => $strTstyle
						)));
						if (!$objPermission)
							$objPermission = first($query->permissions__select(array(
								"permission_id" => $intPermission,
								"permission" => "Super Administrator"
							)));
					}
					else
					{
						$arrPermissions = $query->permissions__select(array(
							"user_id" => $user_id,
							"template_style" => $strTstyle
						));
						if (!$arrPermissions)
							$arrPermissions = $query->permissions__select(array(
								"user_id" => $user_id,
								"permission" => "Super Administrator"
							));
						$arrKeyOrder = array(
							"Institution Administrator" => "a",
							"Super Administrator" => "b",
							"Network" => "c",
							"Teacher" => "d",
							"Parent" => "e",
							"Student" => "f"
						);
						$arrResult = array();
						foreach ($arrPermissions as $objPermission)
						{
							if (isset($arrKeyOrder[$objPermission->permission]))
								$arrResult[$arrKeyOrder[$objPermission->permission] . $objPermission->permission_id] = $objPermission;
						}
						ksort($arrResult);
						$objPermission = reset($arrResult);
					}
					if (!$objPermission)
					{
						return -4;
					}

					$objInstitution = first($objInstitutions->_institutions_select(array(
						"institution_id" => $objPermission->institution_id
					)));

					if ($objPermission && $objInstitution)
					{
						if (preg_match("/:relation([0-9]+)$/", $strPermission, $arrMatched))
						{
							$user_session->relation_id = $arrMatched[1];
						}
						$user_session->institution_id = $objPermission->institution_id;
						$user_session->permission = $objPermission->permission;
						$user_session->permission_id = $objPermission->permission_id;
						$user_session->template_style = $objPermission->template_style;
						$user_session->institution_name = $this->get_user_institution_name($user_id,$objPermission->permission_id);
						$user_session->image_id = $objInstitution->image_id;
						$user_session->language_id = $this->language_id($user_id, $objPermission->institution_id);
						$user_session->institution_type = 'Camp';
						$objUser = first($query->users__select(array(
							'user_id' => $user_id
						)));
						$objNetwork = first($query->networks__select(array(
							'network_keyword' => $objPermission->template_style,
							'network_email' => $objUser->email
						)));
						if ($objNetwork)
						{
							//$user_session->institution_type = 'Network';
							//$user_session->permission = 'Network';
							$user_session->network_id = $objNetwork->network_id;
							/*$user_session->institution_id = $objNetwork->institution_id;
							$objNetworkPermission = first($query->permissions__select(array(
								'institution_id' => $objNetwork->institution_id,
								'permission' => 'Network',
								'user_id' => $user_id
							)));
							$user_session->permission_id = $objNetworkPermission->permission_id;*/
						}
						return 1;
					}
					else {
						// user is not active
						Zend_Auth::getInstance()->clearIdentity();
						Zend_Registry::_unsetInstance();
						Zend_Session::destroy();
						return -999;
					}
				}
				else
				{
					// user is not active
					Zend_Auth::getInstance()->clearIdentity();
					Zend_Registry::_unsetInstance();
					Zend_Session::destroy();
					return -999;
				}
			}
		}
		else
		{
			// Not a valid user, get the error code & forward to failed login page
			switch ($result->getCode())
			{
				case 0:
					return 0;
					break;
				case -1:
					return -1;
					break;
				case -3:
					return -3;
					break;
				default:
					return 0;
					break;
			}
		}
	}

	public function language_id($intUser, $intInstitution, $boolKiosk=0)
	{
		$objConfig = new Config();
		if (!$boolKiosk)
		{
			$arrUserOptions = $objConfig->load(array(
				"set" => array("admin", "institution"),
				"key" => "language",
				"institution_id" => $intInstitution,
				"user_id" => $intUser
			));
			if (
				isset($arrUserOptions["admin"]["language"])
				&& $arrUserOptions["admin"]["language"]
			)
				return $arrUserOptions["admin"]["language"];
			return $arrUserOptions["institution"]["language"];
		}
		else
		{
			// kiosk
			$arrUserOptions = $objConfig->load(array(
				"set" => "kiosk",
				"key" => "language",
				"institution_id" => $intInstitution,
				"user_id" => $intUser
			));
			return $arrUserOptions["kiosk"]["language"];
		}

	}

	function KioskAuthenticate($serial)
	{

		if($serial == '') return 0;
		if(!is_numeric($serial)) return 0;

		$sql = 'SELECT * FROM users WHERE bar_code="'.$serial.'"';
		$result = $this->_db->fetchRow($sql);

		//echo $sql; exit;

		if($result){
			//get institution data
			$sql = '
			SELECT * FROM permissions
			INNER JOIN institutions
			ON permissions.institution_id = institutions.institution_id
			WHERE permissions.user_id = '.$result->user_id;
			$institution = $this->_db->fetchRow($sql);
			if (!$institution)
			{
				print "Sorry, there was an error: MU-KA101-D8DFS9";
				exit;
			}

			//show base average
			$strSelectAllStudents = "
			SELECT
					*
				FROM
					user_classes
			INNER JOIN classes on user_classes.class_id=classes.class_id
			WHERE classes.institution_id=".$institution->institution_id."
			AND user_classes.class_role='Student'";

			$arrAllStudents = $this->_db->fetchAll($strSelectAllStudents);
			$intAllStudentId = array();
			foreach ($arrAllStudents as $objAllStudents)
			{
				$intAllStudentId[] = $objAllStudents->user_id;
			}
			$base_avg_points = 0;
			if (count($intAllStudentId))
			{
				$strAllStudentIds = join(",",$intAllStudentId);
				//calculate the base average
				$strBaseAverage = "
					SELECT AVG(points) as base_avg_points
					FROM user_points where user_id IN (".$strAllStudentIds.")";
				$objAllStudentAverage = $this->_db->fetchRow($strBaseAverage);
				$base_avg_points = $objAllStudentAverage->base_avg_points;
			}
		  //print $objTeachersName->first_name ." " . $objTeachersName->last_name; exit;
		  if(!$institution) return 0;
		  //populate session data
		  //user data
		  $kiosk_session_data = new Zend_Session_Namespace('kiosk_user_session_data');
		  $kiosk_session_data->setExpirationSeconds(9999999999);
		  //user information
		  $kiosk_session_data->user_id = $result->user_id;
		  $kiosk_session_data->first_name = $result->first_name;
		  $kiosk_session_data->last_name = $result->last_name;
		  $kiosk_session_data->user_picture = $result->image_id;
		  $kiosk_session_data->user_serial = $result->user_serial;
		  $kiosk_session_data->permission_id = $institution->permission_id;
		  $kiosk_session_data->bar_code = $serial;
		  //institution data
		  $kiosk_session_data->host_id = $institution->host_id;
		  $kiosk_session_data->network_id = $institution->network_id;
		  $kiosk_session_data->institution_id = $institution->institution_id;
		  $kiosk_session_data->institution_name = $institution->name;
		  $kiosk_session_data->institution_logo = $institution->image_id;
		  $kiosk_session_data->gender = $result->gender;
		  return 1;
		} else{
		  return 0;
		}
		// @TODO - ADD A LOGIN EVENT IN THE ACTIVITY FEED
	}

	// Generic functions
	public function _users_select ($arrParams)
	{		
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_id"				=> @$arrParams["user_id"],
			"old_user_id"			=> @$arrParams["old_user_id"],
			"bar_code"				=> @$arrParams["bar_code"],
			"email"					=> @$arrParams["email"],
			"student_email"			=> @$arrParams["student_email"],
			"password"				=> @$arrParams["password"],
			"first_name"			=> @$arrParams["first_name"],
			"last_name"				=> @$arrParams["last_name"],
			"hebrew_first_name"		=> @$arrParams["hebrew_first_name"],
			"hebrew_last_name"		=> @$arrParams["hebrew_last_name"],
			"dob"					=> @$arrParams["dob"],
			"gender"				=> @$arrParams["gender"],
			"is_active"				=> @$arrParams["is_active"],
			"address"				=> @$arrParams["address"],
			"city"					=> @$arrParams["city"],
			"state"					=> @$arrParams["state"],
			"country"				=> @$arrParams["country"],
			"postal"				=> @$arrParams["postal"],
			"phone"					=> @$arrParams["phone"],
			"cell"					=> @$arrParams["cell"],
			"image_id"				=> @$arrParams["image_id"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				users
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (is_array($Value) && !count($Value))
				return array();
			if (
				$Value === 0
				|| $Value
			) {
				if (is_array($Value) && !count($Value))
					return array();
				if (is_array($Value)) {
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
				else if (!is_array($Value))
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

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _users_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"old_user_id"			=> @$arrParams["old_user_id"],
			"email"					=> @$arrParams["email"],
			"student_email"					=> @$arrParams["student_email"],
			"password"				=> @$arrParams["password"],
			"bar_code"				=> @$arrParams["bar_code"],
			"first_name"			=> @$arrParams["first_name"],
			"last_name"				=> @$arrParams["last_name"],
			"hebrew_first_name"		=> @$arrParams["hebrew_first_name"],
			"hebrew_last_name"		=> @$arrParams["hebrew_last_name"],
			"dob"					=> @$arrParams["dob"],
			"gender"				=> @$arrParams["gender"],
			"address"				=> @$arrParams["address"],
			"city"					=> @$arrParams["city"],
			"state"					=> @$arrParams["state"],
			"country"				=> @$arrParams["country"],
			"postal"				=> @$arrParams["postal"],
			"phone"					=> @$arrParams["phone"],
			"cell"					=> @$arrParams["cell"],
			"image_id"				=> @$arrParams["image_id"],
			"is_active"				=> @$arrParams["is_active"],
			"created"				=> @$arrParams["created"],
			"created_by"			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("users", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _users_update($arrParams)
	{
		$arrValuesParams = array("old_user_id","email","student_email","password","bar_code","first_name","last_name","hebrew_first_name","hebrew_last_name","dob","gender","address","city","state","country","postal","phone","cell","image_id","is_active","created");
		$arrWhereParams = array("user_id","old_user_id","email","student_email","password","bar_code","first_name","last_name","hebrew_first_name","hebrew_last_name","dob","gender","address","city","state","country","postal","phone","cell","image_id","is_active","created","modified","created_by");

		$arrParams = $this->_tools->rsqlclean($arrParams);


		$arrValues = array();
		$arrWhere = array();

		// Values
		foreach ($arrValuesParams as $strKey)
		{
			if (isset($arrParams["values"][$strKey]))
				$arrValues[$strKey] = $arrParams["values"][$strKey];
		}
		$arrValues["modified"] = date("Y-m-d H:i:S");

		// Where

		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams["where"][$strKey]);
		}

		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MU-UU101-SD8SD8";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("users", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _users_delete($arrParams)
	{
		$arrWhereParams = array("user_id","old_user_id","email","student_email","password","bar_code","first_name","last_name","hebrew_first_name","hebrew_last_name","dob","gender","address","city","state","country","postal","phone",'cell',"image_id","is_active","created","modified","created_by");
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
		$boolResult = $this->_db->delete("users", $arrFeilds);
		return $boolResult;
	}

	// This function will find the institutions under a host or network and drill down by permission
	// Special addaptations include: host_id, network_id and class_id
	public function _users_select_hierarchal ($arrParams)
	{
		$query = new QueryGen();
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Create inline join statement depending on the hierarchy of the ids provided
		// The results only need to be narrowed down by the lowest point of hierarchy
		if (!isset($arrParams["institution_id"]) && !isset($arrParams["permission"]))
		{
			print "Sorry, there was an error: MU-USH101-SD87DS";
			exit;
		}
		if (
			isset($arrParams["institution_id"])
			&& (
				$arrParams["institution_id"] === 0
				|| $arrParams["institution_id"]
			)
		) {
			$strSqlInstitutions = "permissions.institution_id = " . $arrParams["institution_id"];
		} else if (
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
				)
				AND permissions.institution_id = institutions.institution_id";
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
				)
				AND permissions.institution_id = institutions.institution_id";
		}
		if (
			isset($arrParams["class_id"])
			&& (
				$arrParams["class_id"] === 0
				|| (is_array($arrParams["class_id"]) && count($arrParams["class_id"]))
				|| $arrParams["class_id"]
			)
		) {
			if (is_array($arrParams["class_id"]))
			{
				$strSqlClasses = "
					user_classes.class_id IN (" . join(",", $arrParams["class_id"]) . ")
					AND users.user_id = user_classes.user_id";
			}
			else
			{
				$strSqlClasses = "
					user_classes.class_id = " . $arrParams["class_id"] . "
					AND users.user_id = user_classes.user_id";
			}
		}
		$strSql = "
			SELECT
				DISTINCT users.*, permissions.permission
			FROM
				users";
		if (isset($strSqlInstitutions))
			$strSql .= ",institutions";
		if (isset($strSqlClasses))
			$strSql .= ",user_classes";
		if (
			isset($strSqlInstitutions)
			|| (
				isset($arrParams["permission"])
				&& $arrParams["permission"]
			)
		) {
			$strSql .= ",permissions";
		}
		$strSql .= "
			WHERE
				1";
		if (isset($strSqlInstitutions))
		{
			$boolPermission = 1;
			$strSql .= "
				AND " . $strSqlInstitutions;
		}
		if (isset($strSqlClasses))
		{
			$strSql .= "
				AND " . $strSqlClasses;
		}
		// Set the permission
		if (
			isset($arrParams["permission"])
			&& $arrParams["permission"]
		) {
			$boolPermission = 1;
			if (is_array($arrParams["permission"]) && count($arrParams["permission"]))
			{
				$strSql .= "
					AND permissions.permission IN (\"" . join("\",\"", $arrParams["permission"]) . "\")";
			}
			else
			{
				$strSql .= "
					AND permissions.permission = '" . $arrParams["permission"] . "'";
			}
		}

		if (isset($boolPermission) && $boolPermission)
		{
			// Connect the users to the permissions
			$strSql .= "
				AND permissions.user_id = users.user_id";
		}

		// Possible column selections

		$arrColumns = array();
		$arrNotColumns = array();
		$arrAllowed = array("user_id","bar_code","old_user_id","email","student_email","first_name","last_name","hebrew_first_name","hebrew_last_name",
			"is_active","address","city","state","country","postal","phone",'cell',"image_id","created","modified","created_by");

		foreach ($arrAllowed as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrColumns["users.".$strKey] = $arrParams[$strKey];
		}
		if (count($arrColumns))
		{
			$strSqlTemp = $query->_gen_where_string($arrColumns, " AND ");
			if (is_null($strSqlTemp))
				return array();
			$strSql .= $strSqlTemp;
		}

		foreach ($arrAllowed as $strKey)
		{
			if (isset($arrParams["_NOT"][$strKey]))
				$arrNotColumns["users.".$strKey] = $arrParams["_NOT"][$strKey];
		}
		if (count($arrNotColumns))
		{
			$strSqlTemp = $query->_gen_where_string($arrNotColumns, " AND !(", ")");
			if (is_null($strSqlTemp))
				return array();
			$strSql .= $strSqlTemp;
		}

		if (isset($arrParams["created_min"]))
			$strSql .= "
				AND unix_timestamp(created) >= " . intval($arrParams["created_min"]);
		if (isset($arrParams["created_max"]))
			$strSql .= "
				AND unix_timestamp(created) <= " . intval($arrParams["created_max"]);

		$strSql .= "
			ORDER BY users.is_active+0 DESC, users.last_name DESC, users.first_name DESC";
		if (isset($arrParams["_LIMIT"]))
			$strSql .= '
			LIMIT ' . $arrParams["_LIMIT"];
		else
			$strSql .= '
			LIMIT 1000';

		$arrResult = array_clean_slashes($this->_db->fetchAll($strSql));
		return $arrResult;
	}
	// Generic functions end

	public function _relationships_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"user_id"				=> @$arrParams["user_id"],
			"relation_id"			=> @$arrParams["relation_id"],
			"relationship"			=> @$arrParams["relationship"],
			"created"				=> date("Y-m-d H:i:S"),
			"created_by"			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("relationships", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _relationships_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"relationship_id"		=> @$arrParams["relationship_id"],
			"user_id"				=> @$arrParams["user_id"],
			"relation_id"			=> @$arrParams["relation_id"],
			"relationship"			=> @$arrParams["relationship"],
			"created"				=> @$arrParams["created"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				relationships
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

		$strSql .= "
			ORDER BY
				modified DESC, created DESC";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _relationships_delete($arrParams)
	{
		$arrWhereParams = array("relationship_id","user_id","relation_id","relationship","created","modified","created_by");
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
		$boolResult = $this->_db->delete("relationships", $arrFeilds);
		return $boolResult;
	}

	/*
	 * Get all the children of a given user
	 */
	public function parents_children ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-PC101-78SD6F";
			exit;
		}

		$strSql = "
			SELECT
				users.*
			FROM
				relationships,
				users
			WHERE
				relationships.user_id = " . $arrParams["user_id"] . "
				AND users.user_id = relationships.relation_id
		";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/*
	 * Get the unix epoch of the users next birth date.
	 */
	public function user_next_birthdate_epoch($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-UAIE101-S87DFD";
			exit;
		}

		$intAgeEpoch = $this->user_age_in_epoch(array(
			"user_id" => $arrParams["user_id"]
		));

		$intTime = mktime(0, 0, 0, date("n", $intAgeEpoch), date("j", $intAgeEpoch), date("Y"));
		if ($intTime < time())
			$intTime = mktime(0, 0, 0, date("n", $intAgeEpoch), date("j", $intAgeEpoch), date("Y")+1);
		return $intTime;
	}

	/*
	 * Get the unix epoch of the users birth date.
	 */
	public function user_batbar_in_epoch ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-UAIE101-S87DFD";
			exit;
		}

		$strSql = "
			SELECT
				*
			FROM
				users
			WHERE
				user_id = " . $arrParams["user_id"];
		$objUser = $this->_db->fetchRow($strSql);
		if (!$objUser)
		{
			print "Sorry, there was an error: MU-UBIE102-S97DFD";
			exit;
		}

		$intAgeEpoch = $this->user_age_in_epoch(array(
			"user_id" => $arrParams["user_id"],
			"dob" => $objUser->dob
		));

		$intElapseableYears = $objUser->gender == "M" ? 13 : 12;

		return mktime(date("H", $intAgeEpoch), date("i", $intAgeEpoch), date("s", $intAgeEpoch), date("n", $intAgeEpoch), date("j", $intAgeEpoch), date("Y", $intAgeEpoch) + $intElapseableYears);
	}

	public function user_age_at_epoch ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-UAAE101-S87DFD";
			exit;
		}
		if (!isset($arrParams["epoch"]))
		{
			print "Sorry, there was an error: MU-UAAE102-DFGFGF";
			exit;
		}

		$intStartAge = $this->user_age_in_epoch(array(
			"user_id" => $arrParams["user_id"]
		));
		$intStartYear = date("Y", $intStartAge);
		$intEndYear = date("Y", $arrParams["epoch"]);
		return $intEndYear - $intStartYear;
	}
	/*
	 * Retreive an epoch of a users date of birth.
	 * Return: (integer) epoch
	 */
	public function user_age_in_epoch ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-UAIE101-S87DFD";
			exit;
		}
		if (!isset($arrParams["dob"]))
		{
			$strSql = "
				SELECT
					*
				FROM
					users
				WHERE
					user_id = " . $arrParams["user_id"];
			$objUser = $this->_db->fetchRow($strSql);
			if (!$objUser)
			{
				print "Sorry, there was an error: MU-UAIE102-789SDF";
				exit;
			}
			$arrParams["dob"] = $objUser->dob;
		}
		if (!preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/", $arrParams["dob"], $arrMatched))
		{
			print "You must have a valid date of birth entered into the system for ladders to become available to you.";
			//print "Sorry, there was an error: MU-UAIE101-SD7F6D";
			exit;
		}
		return mktime(0, 0, 0, $arrMatched[1], $arrMatched[2], $arrMatched[3]);
	}

	///// [ PERMISSION_TABLE ] /////////////////////////////////////////////////////////////////////////////

	function permission_select_id($intPermission=0)
	{
		$intPermission = mysql_real_escape_string(trim($intPermission));
		if (
			!$intPermission
			|| !preg_match("/^[0-9]+$/", $intPermission)
		) {
			print "There was an error: MU-PSI101-GHJ456";
			exit;
		}
		$strSql = "
			SELECT
				*
			FROM
				permissions
			WHERE
				permission_id=" . $intPermission;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	// Remove a permission by id
	function permissions_delete_id($intPermission=0)
	{
		$intPermission = mysql_real_escape_string(trim($intPermission));
		if (
			!$intPermission
			|| !preg_match("/^[0-9]+$/", $intPermission)
		) {
			print "There was an error: MU-PDI101-FG564H";
			exit;
		}
		// Select the permission in question
		$strSql = "
			SELECT
				*
			FROM
				permissions
			WHERE
				permission_id=" . $intPermission;
		$objPermission = $this->_db->fetchRow($strSql);
		if ($objPermission)
		{
			// Delete it
			$strSql = "permission_id=" . $intPermission;
			if ($this->_db->delete("permissions", $strSql))
			{
				// Add a random default if there is currently no default permission
				$strSql = "
					SELECT
						*
					FROM
						permissions
					WHERE
						user_id=" . $objPermission->user_id . "
						AND default_permission = 1";
				if (!$this->_db->fetchRow($strSql))
				{
					$strSql = "
						UPDATE
							permissions
						SET
							default_permission = 1
						WHERE
							user_id=" . $objPermission->user_id . "
						LIMIT 1";
					$this->_db->query($strSql);
				}
				// Deactivate user if there are no permissions remaining
				$strSql = "
					SELECT
						*
					FROM
						permissions
					WHERE
						user_id=" . $objPermission->user_id;
				if (!$this->_db->fetchRow($strSql))
				{
					$this->user_update_status($objPermission->user_id, 0);
				}
			}
		}
		return 1;
	}

	// Select all the permissions by user id and with the option of institution id
	function permissions_select_userid($intUser, $intInstituition=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				permissions
			WHERE
				user_id=" . $intUser;
		if ($intInstituition)
			$strSql .= "
				AND institution_id = " . $intInstituition;
		$arrPermissions = $this->_db->fetchAll($strSql);
		return $arrPermissions;
	}

	function permission_select_userid_default($intUser=0) {
		if (!$intUser)
		{
			print "Sorry, there was an error: MU-PSUD101-VBN123";
			exit;
		}
		$strSql = "
			SELECT
				*
			FROM
				permissions
			WHERE
				user_id=" . $intUser . "
				AND default_permission=1";
		$arrPermissions = $this->_db->fetchRow($strSql);
		return $arrPermissions;
	}
	// Select permissions by user id and then merge the institutions with the associated records
	// We do this so we get an object for permissions and an object for institutions on in iterated array
	function permissions_select_userid_r_institutions($intUser)
	{
		$strSql = "
			SELECT
				*
			FROM
				permissions
			WHERE
				user_id=" . $intUser;
		$arrPermissions = $this->_db->fetchAll($strSql);
		$arrInstitutions = array();
		foreach ($arrPermissions as $objPermission) {
			$arrInstitutions[$objPermission->institution_id] = 1;
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
			foreach ($arrPermissions as $intKey => $objPermission) {
				foreach ($arrInstitutions as $objInstitution) {
					if ($objPermission->institution_id == $objInstitution->institution_id) {
						$arrResult[$intKey]["objInstitution"] = $objInstitution;
						$arrResult[$intKey]["objPermission"] = $objPermission;
						break;
					}
				}
			}
		}
		return $arrResult;
	}

	/* Function permissions_insert
	 * @Params: array(
	 				intUser=>VALUE,
	 				intInstitutionId=>VALUE,
	 				strPermissionType=>VALUE,
	 				intDefaultPermission=>VALUE
	 			)
	 * @Return: Creates a new entry into the permissions table
	 */
	function permissions_insert($arrValues)
	{
		if (
			!isset($arrValues["intUser"])
			|| !$arrValues["intUser"]
		) {
			print "There was an error: MU-UPI101-D456FG";
			exit;
		}
		$user_session = new Zend_Session_Namespace('user_session_data');
		$arrPermissions = $this->permissions_select_userid($arrValues["intUser"]);
		// If setting a default permission set all to 0
		if (
			isset($arrValues["intDefaultPermission"])
			&& $arrValues["intDefaultPermission"]
			&& count($arrPermissions)
		) {
			$arrSql = array(
				"default_permission" => 0
			);
			$this->_db->update("permissions", $arrSql, "user_id=" . $arrValues["intUser"]);
		}
		// There is only 1 permission remaining it will always be default
		$intDefaultPermission =
			count($arrPermissions) > 1
			? $arrValues["intDefaultPermission"]
			: 1;
		// Insert permission
		$arrSql = array(
			"user_id" => $arrValues["intUser"],
			"institution_id" => $arrValues["intInstitution"],
			"permission" => $arrValues["strPermissionType"],
			"default_permission" => $intDefaultPermission,
			"created" => date("Y-m-d H:i:S"),
			"created_by" => $user_session->user_id
		);
		$intResult = $this->_db->insert("permissions", $arrSql);
		return $intResult;
	}

	/* Function update_permissions
	 * @Params: array(
	 				intPermission=>VALUE,
	 				intUser=>VALUE,
	 				intInstitution=>VALUE,
	 				strPermissionType=>VALUE,
	 				intDefaultPermission=>VALUE
	 			)
	 * @Return: Update an entry on the permissions table
	 */
	function permissions_update($arrValues)
	{
		$user_session = new Zend_Session_Namespace('user_session_data');
		// If setting a default permission set all to 0
		if (
			isset($arrValues["intDefaultPermission"])
			&& $arrValues["intDefaultPermission"]
		) {
			$arrSql = array(
				"default_permission" => 0
			);
			$this->_db->update("permissions", $arrSql, "user_id=" . $arrValues["intUser"]);
		}
		// Insert permission
		$arrSql = array(
			"permission" => $arrValues["strPermissionType"],
			"default_permission" => $arrValues["intDefaultPermission"],
			"created_by" => $user_session->user_id
		);
		$intResult = $this->_db->update("permissions", $arrSql, "permission_id=" . $arrValues["intPermission"]);
		return $intResult;
	}

	///// [ USER_BACKGROUND_TABLE ] ////////////////////////////////////////////////////////////////////////

	function background_select_permission($intPermission)
	{
		if (
			!isset($intPermission)
			&& !$intPermission
		) {
			print "There was an error: MU-BSP101-DF56G4";
		}
		$strSql = "
			SELECT
				*
			FROM
				user_backgrounds
			WHERE
				permission_id=" . $intPermission;
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	function background_delete_id($intId)
	{
		if (
			!isset($intId)
			&& !$intId
		) {
			print "There was an error: MU-BSI101-DFGH4S";
		}
		$strSql = "user_background_id=" . $intId;
		$strResult = $this->_db->delete("user_backgrounds", $strSql);
		return $strResult;
	}

	function background_select_id($intId)
	{
		if (
			!isset($intId)
			&& !$intId
		) {
			print "There was an error: MU-BSI101-DFGH4S";
		}
		$strSql = "
			SELECT
				*
			FROM
				user_backgrounds
			WHERE
				user_background_id=" . $intId;
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	function background_insert($arrQuery)
	{
		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue)
		{
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the insert
		$arrFeilds = array (
			"permission_id" => $arrQuery["permission_id"],
			"user_id" => $arrQuery["user_id"],
			"background_type" => $arrQuery["background_type"],
			"created" => $intCurrentDate,
			"created_by" => $this->_user_session_data->user_id
		);

		// Execute
		$intResult = $this->_db->insert("user_backgrounds", $arrFeilds);
		return $intResult;
	}

	function background_update($arrQuery)
	{
		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue)
		{
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the insert
		$arrFeilds = array (
			"permission_id" => $arrQuery["permission_id"],
			"user_id" => $arrQuery["user_id"],
			"background_type" => $arrQuery["background_type"],
		);

		// Execute
		$intResult = $this->_db->update("user_backgrounds", $arrFeilds, "user_background_id=" . $arrQuery["user_background_id"]);
		return $intResult;
	}

	///// [ BACKGROUND_TYPES_TABLE ] ////////////////////////////////////////////////////////////////////////

	/* Function background_types_select
	 * @Params:
	 */
	function background_types_select()
	{
		$strSql = "
			SELECT
				*
			FROM
				background_types;";
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	/* Function get_background_types
	* @Params:
	* @Return: all background types
	*/

	/* Redundant */ function get_background_types()
	{
		$strSql = "Select * from background_types";
		$arrResult = $this->_db->fetchAll($strSql);
		if($arrResult)
		{
			return $arrResult;
		}
		return 0;
	}

	///// [ PERMISSION_TYPES_TABLE ] /////////////////////////////////////////////////////////////////////////////

	/* Redundant Function get_permission_types
	 * @Params:
	 */
	function get_permission_types()
	{
		$strSql = "
			SELECT
				*
			FROM
				permission_types;";
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	/* Function permission_types_select
	 * @Params:
	 */
	function permission_types_select()
	{
		$strSql = "
			SELECT
				*
			FROM
				permission_types;";
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	///// [ USERS_TABLE ] /////////////////////////////////////////////////////////////////////////////

	function users_student_select_class($intClass)
	{
		if (!isset($intClass))
		{
			print "Sorry, there was an error: MU-USC101-DF45GF";
			exit;
		}
		$strSql = "
			SELECT
				users.*
			FROM
				users,
				user_classes
			WHERE
				user_classes.class_id = " . $intClass . "
				AND user_classes.class_role = 'Student'
				AND user_classes.user_id = users.user_id";

		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	function user_select_parentids($intUser)
	{
		/*
		 * Very temporarily commenting out permission select because we never imported
		 * the institutions correctly because of bs that we didnt know about the bs table
		 * that we imported from.
		 *
		 *
		$objPermission = $this->permission_select_userid_default($intUser);
		if (!$objPermission)
		{
			print "Sorry, there was an error: MU-USH101-FG56H4";
			exit;
		}
		$strSql = "
			SELECT
				institution_id, host_id, network_id
			FROM
				institutions
			WHERE
				institution_id=" . $objPermission->institution_id;
		*/
		$strSql = "
			SELECT
				institution_id, host_id, network_id
			FROM
				institutions
			WHERE
				institution_id=" . $this->_user_session_data->institution_id;
		$objInstitution = $this->_db->fetchRow($strSql);
		if (!$objInstitution)
		{
			print "Sorry, there was an error: MU-USH102-F6G45F";
			exit;
		}
		if ($objInstitution->network_id > 0) {
			return array(
				"network_id" => $objInstitution->network_id,
				"host_id" => $objInstitution->host_id,
				"institution_id" => $objInstitution->institution_id
			);
		} else if ($objInstitution->host_id > 0) {
			return array(
				"host_id" => $objInstitution->host_id,
				"network_id" => $objInstitution->institution_id
			);
		} else {
			// This was left blank because this institution doesn't have
			// any parent institutions
		}
	}

	// Model version of use update status
	// - Implamented in setting permissions
	function user_update_status($intUser, $boolStatus)
	{
		$intUser = mysql_real_escape_string(trim($intUser));
		$boolStatus = mysql_real_escape_string(trim($boolStatus));
		if (
			!isset($intUser)
			|| !isset($boolStatus)
			|| empty($intUser)
		) {
			print "There was an error: MU-UUS101-SDG564-$intUser-$boolStatus";
			exit;
		}
		$this->_db->update("users", array("is_active" => $boolStatus), "user_id=" . $intUser);
	}

	/* Function user_edit
	 * @Params: array, user_id
	 */
	function user_edit($arrSql,$intId)
	{
		if(
			isset($arrSql)
			&& is_array($arrSql)
		) {
			$strSql = 'user_id=' .$intId;
			$update = $this->_db->update("users", $arrSql, $strSql);
			return $update;
		 }
		 return 0;
	}

	/* Function list_all_users
	 * @Params: is_active
	 * @Returns: object records of all active users
	 * Returns all users from the users table
	*/
	function fetch_users_from_status($boolActive) {
		$strSql = "
			SELECT
				*
			FROM
				users
			WHERE
				is_active="
			. (
				$boolActive
				? 1
				: 0
			);
		$strSql .= " limit 200";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}


	/* Function reset_password
	 * @Params: user_id
	 */
	function reset_password($user_id)
	{
		$intTime = time();
		$strPassword = md5($intTime);
		$arrSql = array(
			'password' => $strPassword
		);
		if (!empty($user_id))
		{
			$this->_db->update('users',$arrSql,'user_id ='.$user_id);
			return $password;
		}

	return 0;
	}

	/* Function get_user_from_email
	 * @Params: email
	 */
	function get_user_from_email($email)
	{
		if ($email)
		{
			$sql = "select * from users where email = '$email'";
			$result = $this->_db->fetchRow($sql);
			if ($result)
			{
				return $result;
			}
		}
		return 0;
	}

	/* Function get_user_by_id
	 * @Params: intId
	 */

	 function get_user_by_id($intId)
	 {
	 	$strSql = "select * from users where user_id=$intId";
		$arrResult = $this->_db->fetchRow($strSql);
		if($arrResult)
		{
			return $arrResult;
		}
		return 0;
	 }

	 /**
	  * Gets extended info about a user including permissions and institution_id
	  *
	  * @param: $user_id
	  * @return: $arrResult
	  *
	  */
	 function get_user_extended_info_by_id($intId)
	 {
	 	$strSql = "
		SELECT * FROM
			users LEFT OUTER JOIN permissions
		ON
			users.user_id = permissions.user_id
		WHERE
			users.user_id=$intId";

		$arrResult = $this->_db->fetchRow($strSql);
		if($arrResult)
		{
			return $arrResult;
		}
		return 0;
	 }

	/* Function is_user_active
	 * @Params: user_id
	 * @Return: 1 for active, 0 for not active
	 * Checks whether a user is active
	 */

	function is_user_active($user_id)
	{
		// We will use this throughout every single function to ensure that we get no SQL errors
		// Always check that the required values being passed in the SQL query are available
		if ($user_id)
		{
			$sql = "select is_active from users where user_id = '$user_id'";
			$result = $this->_db->fetchRow($sql);
			if ($result)
			{
				// This will return 1
				return $result->is_active;
			}
		}
		return 0;
	}

	/* Function list_all_users
	 * @Params: is_active
	 * @Returns: object records of all active users
	 * Returns all users from the users table
	*/
	function list_all_users($is_active=NULL)
	{
		$strSql = "Select * from users where 1";
		if($is_active > -1)
		{
			$strSql .= " and is_active = '$is_active'";
		}
		$arrResult = $this->_db->fetchAll($strSql);
        if($arrResult)
		{
			return $arrResult;
		}
		 return 0;
	}

	/* Function get_all_users
	 * @Params:
	 * @Return: Object records of all the users
	 * Returns all the users from the users table
	 */

	function get_all_users()
	{
		$sql = "select * from users";
		$result = $this->_db->fetchAll($sql);
		if ($result)
		{
			return $result;
		}
		return 0;
	}

	/* Function get_user_permission
	 * @Params: user_id
	 * @Return: Entire record as an object
	 * Returns the permission type of a user. If a user has multiple permissions, the default one is returned.
	 */

	function get_user_permission($user_id)
	{
		if ($user_id)
		{
			$sql = "SELECT * FROM permissions WHERE user_id=" . $user_id . " AND default_permission=1";
			$result = $this->_db->fetchRow($sql);
			if ($result)
			{
				return $result;
			}
		}
		return 0;
	}

	/* Function get_user_permission_by_institution_id
	 * @Params: user_id,$institution_id
	 * @Return: Entire record as an object
	 * Returns the permission type of a user relative to an institution_id
	 */

	function get_user_permission_by_institution_id($user_id,$institution_id)
	{
		if ($user_id && $institution_id)
		{
			$sql = "SELECT * FROM permissions WHERE user_id=" . $user_id . " AND institution_id=$institution_id";
			$result = $this->_db->fetchRow($sql);
			if ($result)
			{
				return $result;
			}
		}
		return 0;
	}

	/* Function get_user_full_name
	 * @Params: user_id
	 * @Return: First Name Last Name
	 * Returns the user's full name
	 */

	function get_user_full_name($user_id)
	{
		if ($user_id)
		{
			$sql = "select first_name, last_name from users where user_id = '$user_id'";
			$result = $this->_db->fetchRow($sql);
			if ($result)
			{
				return $result->first_name . " " . $result->last_name;
			}
		}
		return 0;
	}


	///// [ INSTITUTIONS_TABLE ] /////////////////////////////////////////////////////////////////////////////

	/* Function get_all_institution
	 * @Params:
	 */
	function get_all_institution()
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions;";
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}


	/* Function get_user_institution_name
	 * @Params: user_id,permission_id
	 * @Return: Institution Name
	 * Returns the user's institution name they have permission on
	 */
	function get_user_institution_name($user_id,$permission_id)
	{
		if ($user_id && $permission_id)
		{
			$sql = "select i.name from institutions as i join permissions as p on (p.institution_id = i.institution_id) where p.user_id = '$user_id' and p.permission_id = '$permission_id'";
			$result = $this->_db->fetchRow($sql);
			if ($result)
			{
				return $result->name;
			}
		}
		return 0;
	}





	function authenticate_by_old_user_id($user_id)
	{
		if($user_id == '')
		{
			$this->redirect('/');
			break;
		}
		$strSql = '
		SELECT ims_id FROM legacy_lookup
		WHERE legacy_id = '.$user_id.'
		AND legacy_table = "admins"
		AND ims_table ="users"';

		$objResult = $this->_db->fetchRow($strSql);

		// Does the user have an identity?
		if ($objResult)
		{
			$user_id = $objResult->ims_id;
			$is_user_active = $this->_db->fetchRow('SELECT is_active FROM users WHERE user_id='.$user_id);

			// check if user is active
			if ($is_user_active)
			{
				// create a new zend session
				$user_session = new Zend_Session_Namespace('user_session_data');

				// set its expiry to 5 minutes
				$user_session->setExpirationSeconds(9999999999);

				// set user_id and status in the session
				$user_session->user_id = $user_id;
				$user_session->is_user_active = $is_user_active;

				// get the user's institution & permission type
				$objPermission = $this->get_user_permission($user_id);
				//echo $objPermission->permission. '===' .$objPermission->institution_id; exit;
				$objInstitutions = new Institutions();
				$objInstitution = $objInstitutions->get_institution_info($objPermission->institution_id);
				if ($objPermission)
				{
						// store it in the user's session
						$user_session->institution_id = $objPermission->institution_id;
						$user_session->host_id = $objInstitution->host_id;
						$user_session->network_id = $objInstitution->network_id;
						$user_session->permission = $objPermission->permission;
						$user_session->user_id = $objPermission->user_id;


						// get the user's full name & instiution name & store it
						$user_session->full_name = $this->get_user_full_name($user_id);
						$user_session->institution_name = $this->get_user_institution_name($user_id,$objPermission->permission_id);

						// Check if the user has access to multiple institutions
						$account_info = $this->getMultipleAccountInfo($user_id);
						if (count($account_info)>1)
						{
							// He does - Save in the session the name & id of the insitutions + the permission this user has on each one
							$counter = 0;
							foreach ($account_info as $a)
							{
								$counter++;
								$institution_id = 'multi_access_instituion_id_'.$counter;
								$permission = 'multi_access_permission_'.$counter;
								$institution_name = 'multi_access_instituion_name_'.$counter;
								$user_session->$institution_id = $a->institution_id;
								$user_session->$permission = $a->permission;
								$user_session->$institution_name = $a->name;
							}
							$user_session->multiple_institution_access = $counter;
						}
						else
						{
							$user_session->multiple_institution_access = 0;
						}
						return 1;
				}
				else
				{
						// user is not active
						Zend_Auth::getInstance()->clearIdentity();
						Zend_Registry::_unsetInstance();
						Zend_Session::destroy();
						return 'Your account does not have any privileges - Please contact customer support';
				}

					// @TODO - ADD A LOGIN EVENT IN THE ACTIVITY FEED

					return 1;
			} else{
					// user is not active
					Zend_Auth::getInstance()->clearIdentity();
					Zend_Registry::_unsetInstance();
					Zend_Session::destroy();

					return 'Your account is inactive - Please contact customer support';
				}
		} else{
			return 'Authentication Failed';
			break;
		/*
			// Not a valid user, get the error code & forward to failed login page
			switch ($objResult->getCode())
			{
				case 0:
					return 'Authentication Failed';
					break;
				case -1:
					return 'Invalid Email';
					break;
				case -3:
					return 'Invalid Password';
					break;
				default:
					return 'Authentication Error';
				break;
			}
		*/
		}

	}
	///// [ MIXED_TABLES ] /////////////////////////////////////////////////////////////////////////////

	/* Function change_status
	 */
	function change_status($strTable, $arrSql, $strWhere)
	{
		$boolResult = $this->_db->update($strTable, $arrSql, $strWhere);
		return $boolResult;
	}

	function getNewUserId($intOldAdminId)
	{
		$strSql = "SELECT * FROM users WHERE old_user_id=" . $intOldAdminId;
		$objUser = $this->_db->fetchRow($strSql);
		return $objUser->user_id;
	}

	function get_new_user_id_and_email($intOldAdminId)
	{
		$strSql = "SELECT user_id, email FROM users WHERE old_user_id=" . $intOldAdminId;
		$objUser = $this->_db->fetchRow($strSql);
		return $objUser;
	}

	public function get_user($intUserId)
	{
		$strSql = "SELECT * FROM users WHERE user_id=" . $intUserId;
		$objUser = $this->_db->fetchRow($strSql);
		return $objUser;
	}

	public function enroll_user_to_campaign($intUserId, $intInstitutionId, $intCampaignId)
	{
		$intUserCampaignId = 0;

		$arrFields = array ("user_id"			=> $intUserId,
							"institution_id"	=> $intInstitutionId,
							"campaign_id"		=> $intCampaignId,
							"status"			=> 'In Progress',
							"created"    		=> date("Y-m-d H:i:S"));


		$boolResult = $this->_db->insert("user_campaigns", $arrFields);

		if ($boolResult)
		{
			$intUserCampaignId = $this->_db->lastInsertId();
		}

		return $intUserCampaignId;
	}

	public function get_teachers_by_host_id($host_id)
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN permissions AS p USING (user_id) ";
		$strSql = $strSql . "JOIN institutions AS n ON (p.institution_id=n.institution_id) ";
		$strSql = $strSql . "JOIN institutions AS h ON (n.network_id=h.institution_id) ";
		$strSql = $strSql . "WHERE h.host_id=" . $host_id . " AND p.permission='Teacher' ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_students_by_host_id($host_id)
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN permissions AS p USING (user_id) ";
		$strSql = $strSql . "JOIN institutions AS n ON (p.institution_id=n.institution_id) ";
		$strSql = $strSql . "JOIN institutions AS h ON (n.network_id=h.institution_id) ";
		$strSql = $strSql . "WHERE h.host_id=" . $host_id . " AND p.permission='Student' ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_teachers_by_class_id($class_id)
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN user_classes AS uc ON (u.user_id=uc.user_id AND uc.class_id=" . $class_id ." AND uc.class_role='Teacher') ";
		$strSql = $strSql . "ORDER BY u.first_name, u.last_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_students_by_class_id($class_id)
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN user_classes AS uc ON (u.user_id=uc.user_id AND uc.class_id=" . $class_id ." AND uc.class_role='Student') ";
		$strSql = $strSql . "ORDER BY u.first_name, u.last_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_users_by_class_id($class_id)
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN user_classes AS uc ON (u.user_id=uc.user_id AND uc.class_id=" . $class_id .") ";
		$strSql = $strSql . "ORDER BY u.first_name, u.last_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_active_teachers()
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN permissions AS p USING (user_id) ";
		$strSql = $strSql . "WHERE p.permission='Teacher' ";
		$strSql = $strSql . "AND u.is_active=1 ";
		$strSql = $strSql . "ORDER BY u.first_name, u.last_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_inactive_teachers()
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN permissions AS p USING (user_id) ";
		$strSql = $strSql . "WHERE p.permission='Teacher' ";
		$strSql = $strSql . "AND u.is_active=0 ";
		$strSql = $strSql . "ORDER BY u.first_name, u.last_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_active_students()
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN permissions AS p USING (user_id) ";
		$strSql = $strSql . "WHERE p.permission='Student' ";
		$strSql = $strSql . "AND u.is_active=1 ";
		$strSql = $strSql . "ORDER BY u.first_name, u.last_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function get_inactive_students()
	{
		$strSql = "SELECT u.* ";
		$strSql = $strSql . "FROM users AS u ";
		$strSql = $strSql . "JOIN permissions AS p USING (user_id) ";
		$strSql = $strSql . "WHERE p.permission='Student' ";
		$strSql = $strSql . "AND u.is_active=0 ";
		$strSql = $strSql . "ORDER BY u.first_name, u.last_name ";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/**
	 * Function checks if there is more than one permission associated with the
	 * user id and returns all data associated with that id.
	 *
	 * @param int user_id
	 * @return array $result
	 */
	public function getMultipleAccountInfo($user_id)
	{
		$sql = '
		SELECT * FROM permissions INNER JOIN institutions
		ON permissions.institution_id = institutions.institution_id
		WHERE permissions.user_id = '.$user_id;

		try {
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MU-GMAI-KKJHFT";
			if(DEV_ENV == "devel") echo $e->getMessage();
		}
		return $result;
	}

	/**
	 * This function is used when a user has multiple account. At login we provide
	 * him with a variety of permission_id's to choose from, once he sselects it
	 * we will pass this permission_id here, retreive the permission and refresh
	 * the session data (user_session_data) with the new data
	 *
	 * @param int $permission_id
	 * @return int $result
	 *
	 */
	public function reset_user_session($permission_id)
	{

		$sql = 'SELECT * FROM permissions WHERE permission_id = '.$permission_id;

		try{
			$rs = $this->_db->fetchRow($sql);
		} catch (Zend_Exception $e){
			echo "There was an error MU-RUS-LSKDFU";
			if(DEV_ENV == 'devel'){
				echo $e->getMessage();
			}
		}

		$this->_user_session_data->institution_id = $rs->institution_id;
		$this->_user_session_data->permission = $rs->permission;

		return 1;
	}

	/**
	 * Function user_import, this function will take the uploaded csv file
	 * then will build an array and pass it back to the controller. The array also
	 * contains all the institituition IDs adn names of the schools that belong
	 * to this administrator.
	 *
	 * @param int $file_name - the csv to parse
	 * @return arr $studentData
	 *
	 * */
	public function user_import($file_name)
	{
		$utility = new Utilities();
		$strFilePath = IMAGE_UPLOADER_DIRECTORY . '/student_list_' . $this->_user_session_data->user_id . ".csv";
		$file = fopen($strFilePath, 'r');

		$i = 0;

		$msg_error = "
		<p>Your file does not seem to adhere to our requirements. </p>
		<p>The first line of your CSV file must include the following line EXACTLY as it appears below:</p>
		<p><strong>first name,last name,gender,dob,address,city,state,country,zip/postal,phone,bunk</strong></p>
		<p>Please note, that you can download our CSV template file by clicking <a href='/administration/downloadtemplate'>here</a></p>";

		$verified = false;
        while(($data = fgetcsv($file, 1000, ',')) !== false) {
			if (empty($data[0]) || empty($data[1])) continue;
		//get first line of csv data to ensure that the file being uploaded adheres
		//to our standards
		//print_r($data);
		if(!$verified){
			if(
				$data[0] != "first name" ||
				$data[1] != "last name" ||
				$data[2] != "gender m/f" ||
				$data[3] != "dob dd/mm/yyyy" ||
				$data[4] != "address" ||
				$data[5] != "city" ||
				$data[6] != "state" ||
				$data[7] != "country" ||
				$data[8] != "zip/postal" ||
				$data[9] != "phone"){
						$studentData['result'] = "0";
						$studentData['message'] = $msg_error;
						return $studentData;
			}else{
					$verified = true;
					continue;
			}
		}

            $created = date("Y-m-d H:i:S");

            // create new students in users table and return last insert id,
            //and use that last isert id for permissions and user_classes
			$studentData['data'][$i]['first_name']		= stripslashes(@$data[0]);
            $studentData['data'][$i]['last_name'] 		= stripslashes(@$data[1]);
			$studentData['data'][$i]['gender'] 		    = stripslashes(@$data[2]);
			$studentData['data'][$i]['dob'] 		    = stripslashes(@$data[3]);
            $studentData['data'][$i]['address'] 		= stripslashes(@$data[4]);
            $studentData['data'][$i]['city'] 			= stripslashes(@$data[5]);
			$studentData['data'][$i]['state'] 			= stripslashes(@$data[6]);
			$studentData['data'][$i]['country'] 		= stripslashes(@$data[7]);
			$studentData['data'][$i]['postal'] 			= stripslashes(@$data[8]);
			$studentData['data'][$i]['phone'] 			= stripslashes(@$data[9]);
			$studentData['data'][$i]['class'] 			= stripslashes(@$data[10]);
			$i++;
        }

		//get all ids that belong to this admin
		$childIds = $utility->getChildInstitutions($this->_user_session_data->institution_id);
		$sql = '
		SELECT * FROM institutions
		WHERE institution_id IN ('.$childIds.')
		AND network_id > 0
		AND host_id > 0
		ORDER BY name';

		try{
			$result = $this->_db->fetchAll($sql);
		}catch (Zend_Exception $e){
			echo "There was an error: MU-UI:HHGFTR";
		}

		$i = 0;
		foreach($result as $r){
			$studentData['institution'][$i]['institution_id'] = $r->institution_id;
			$studentData['institution'][$i]['name'] = stripslashes($r->name);
			$i++;
		}

		if($i > 0){
			$studentData['result'] = "1";
			$studentData['message'] = "Parsed succesfully";
		}else{
			$studentData['result'] = "0";
			$studentData['message'] = "It seems like your file is not properly formatted.";
		}

		//print_r($studentData);  exit;
		return $studentData;
        //unset($arrParams);
	}

	/*
	 * Insert users from uploaded csv file.
	 * Required: instituion_id, arrUsersInfo, class_id
	 */
	public function batch_user_import($arrParams)
	{
		$_VERBOSE = 0;
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["institution_id"]))
		{
			print text("Sorry, there was an error") . ": MU-BUI101-8DFG6S";
			exit;
		}
		if (
			!isset($arrParams["arrUsersInfo"])
			|| !is_array($arrParams["arrUsersInfo"])
			|| !count($arrParams["arrUsersInfo"])
		) {
			print text("Sorry, there was an error") . ": MU-BUI102-S7DFD7";
			exit;
		}

		$objPermissions = new Permissions();
		$objClasses = new Classes();
		$query = new QueryGen();

		$intTotalChanges = 0;
		$intInsertedUsers = $intUpdatedUsers = array();
		foreach($arrParams["arrUsersInfo"] as $arrUser)
		{
			$intBarCode = rand_num_string(16);
			if(!isset($arrUser['email']) || empty($arrUser['email']))
				$arrUser['email'] = $intBarCode . "@nomail.com";
			// Check if item already exists
			$objUser = false;/*first($this->_users_select_hierarchal(array(
				"first_name" => $arrUser["first_name"],
				"last_name" => $arrUser["last_name"],
				"gender" => $arrUser["gender"],
				"dob" => $arrUser["dob"],
				"institution_id" => $arrParams["institution_id"]

			)));*/
			if ($objUser)
			{
				if ($_VERBOSE)
					print $objUser->user_id . " " . text("already exists") . " <br>\n";
				$boolUserUpdate = $this->_users_update(array(
					"where" => array(
						"user_id" => $objUser->user_id
					),
					"values" => array(
						"first_name" => $arrUser["first_name"],
						"last_name" => $arrUser["last_name"],
						"email" => $arrUser["email"],
						"gender" => $arrUser["gender"],
						"dob" => $arrUser["dob"],
						"address" => $arrUser["address"],
						"city" => $arrUser["city"],
						"state" => $arrUser["state"],
						"country" => $arrUser["country"],
						"postal" => $arrUser["postal"],
						"phone" => $arrUser["phone"]
					)
				));
				// add permission if it doesnt already exist
				$objPermission = first($objPermissions->_permissions_select(array(
					"user_id" => $objUser->user_id,
					"institution_id" => $arrParams["institution_id"],
					"permission" => "Student",
					"template_style" => $arrParams["template_style"]
				)));
				if (!$objPermission)
				{
					$objPermissions->_permissions_update(array(
						"where" => array(
							"user_id" => $objUser->user_id
						),
						"values" => array(
							"default_permission" => 0
						)
					));
					$intPermissionID = $objPermissions->_permissions_insert(array(
						"user_id" => $objUser->user_id,
						"institution_id" => $arrParams["institution_id"],
						"template_style" => $arrParams["template_style"],
						"permission" => "Student",
						"default_permission" => 1
					));
				}
				if (@$arrUser["class_id"] > 0)
				{
					$objClass = first($objClasses->user_classes_select(array(
						"class_id" => $arrUser["class_id"],
						"user_id" => $objUser->user_id,
						"class_role" => "Student"
					)));
					if (!$objClass)
					{
						$query->user_classes__insert(array(
							"class_id" => $arrUser["class_id"],
							"user_id" => $objUser->user_id,
							"class_role" => "Student",
							"institution_id" => $this->_user_session_data->institution_id
						));
					}
				}
				$intTotalChanges++;
			}
			else
			{
				$intUserID = $this->_users_insert(array(
					"first_name" => $arrUser["first_name"],
					"last_name" => $arrUser["last_name"],
					"email" => $arrUser["email"],
					"gender" => $arrUser["gender"],
					"dob" => $arrUser["dob"],
					"address" => $arrUser["address"],
					"city" => $arrUser["city"],
					"state" => $arrUser["state"],
					"country" => $arrUser["country"],
					"postal" => $arrUser["postal"],
					"phone" => $arrUser["phone"],
					"bar_code" => $intBarCode,
					"is_active" => 1,
					"image_id" => ""
				));
				if ($_VERBOSE)
					print "Created new user: " . $intUserID . " <br>\n";
				$intPermissionID = $objPermissions->_permissions_insert(array(
					"user_id" => $intUserID,
					"institution_id" => $arrParams["institution_id"],
					"permission" => "Student",
					"default_permission" => 1,
					"template_style" => $arrParams["template_style"]
				));
				if ($_VERBOSE)
					print "New permission created: " . $intPermissionID . " <br>\n";
				if (@$arrUser["class_id"] > 0)
				{
					$intClassID = $objClasses->user_classes_insert(array(
						"class_id" => $arrUser["class_id"],
						"user_id" => $intUserID,
						"class_role" => "Student",
						"institution_id" => $arrParams["institution_id"]
					));
				}
				if ($_VERBOSE)
					print "New class association created: " . $intClassID . " <br>\n";
				$intTotalChanges++;
			}
		}
		return $intTotalChanges;
	}

	/** Function get_user_extended_info
	 * @param: $arrParams
	 * @return: $arrResult
	 * this function will display add-ons that were bought by the student.
	 * it will also check depending on what parameter is passed in the array,
	 * if an add-on was configured for a specific class or all classes in the school
	 */
	public function get_user_extended_info($arrParams)
	{
		if(isset($arrParams["class_id"]))
		{
			$strWhere[] = "user_classes.class_id=" . $arrParams["class_id"];
		}
		if(isset($arrParams["institution_id"]))
		{
			$strWhere[] = "classes.institution_id=" . $arrParams["institution_id"];
		}
		$strSql="
			SELECT
				users.user_id,
				student_purchases.price,
				student_purchases.package_item_id,
				users.last_name,
				users.first_name,
				classes.class_id
			FROM
				classes,user_classes,users
			LEFT JOIN student_purchases on users.user_id=student_purchases.user_id
			WHERE
				user_classes.user_id=users.user_id
				AND classes.class_id=user_classes.class_id
				AND user_classes.class_role='Student'";

		if(count($strWhere))
		{
			$strWhere = " AND " . join (" AND " , $strWhere);
		}
		$strSql .= $strWhere . " GROUP BY users.user_id
			ORDER BY
				users.first_name,
				users.last_name";
		//print $strSql; exit;
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		} catch (Zend_Exception $e){
			echo "There was an error MU-GUEI-ERT567";
			if(DEV_ENV == 'devel'){
				echo $e->getMessage();
			}
		}
		return $arrResult;
	}
	private function _camp_registration_config($arrParams)
	{
		if(is_array($arrParams))
		{
			$strSql = "
				SELECT
					purchase_details.pack_item_id,
					purchase_details.item_name,
					purchase_details.pack_item_price,
					purchase_details.pack_item_type,
					purchases.institution_id,
					purchases.credit
				FROM
					purchases
				INNER JOIN purchase_details USING(purchase_id)
				WHERE purchases.institution_id=".$arrParams["institution_id"];
			//print $strSql; exit;
			try{
				$arrResult = $this->_db->fetchAll($strSql);
			} catch (Zend_Exception $e){
				echo "There was an error MU-ABI-ERT567";
				if(DEV_ENV == 'devel'){
					echo $e->getMessage();
				}
			}
			return $arrResult;
		}
	}

	public function generate_user_barcodes($intDigits=20, $strBarCode="")
	{
		return rand_num_string($intDigits);
	}

	public function addon_configurations($arrParams)
	{
		if(isset($arrParams["institution_id"]))
		{
			$strWhere[] = "addons_config.institution_id=".$arrParams["institution_id"];
		}
		if(isset($arrParams["class_id"]))
		{
			$strWhere[] = "addons_config.class_id=".$arrParams["class_id"];
		}
		$strSql="
			SELECT
				*
			FROM
				addons_config
			INNER JOIN
				package_items on addons_config.addon_id=package_items.package_item_id
			WHERE 1";
		if(count($strWhere))
		{
			$strWhere = " AND " . join(" AND ", $strWhere);
		}
		$strSql .= $strWhere . " GROUP BY package_items.package_item_id";
		//print $strSql; exit;
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		} catch (Zend_Exception $e){
			echo "There was an error MU-ABI-ERT567";
			if(DEV_ENV == 'devel'){
				echo $e->getMessage();
			}
		}
		return $arrResult;
	}
	public function get_student_addons($arrParams)
	{
		if(isset($arrParams["mode"]) && $arrParams["mode"] =="camp")
		{
			try{
				$arrResult = $this->_camp_registration_config($arrParams);
			} catch (Zend_Exception $e){
				echo "There was an error MU-GSA-RTR234";
				if(DEV_ENV == 'devel'){
					echo $e->getMessage();
				}
			}
			return $arrResult;

		}
		else
		{
			if(isset($arrParams["class_id"]))
			{
				$strWhere[] = " addons_config.class_id=" . $arrParams["class_id"];
			}
			if(isset($arrParams["institution_id"]))
			{
				$strWhere[] = " addons_config.institution_id=" . $arrParams["institution_id"];
			}
			if(count($strWhere))
			{
				$strWhere = " AND " . join(" AND ", $strWhere);
			}

			$strSql = "
				SELECT
					package_items.package_item_id,
					package_items.name,
					package_items.student_price,
					permissions.user_id as 'student_id',
					purchase_details.pack_item_type,
					users.first_name,
					users.last_name,
					addons_config.addon_id,
					addons_config.is_mandatory
				FROM package_items, permissions, users, addons_config,purchase_details
				WHERE
					users.user_id=permissions.user_id
					AND package_items.package_item_id=addons_config.addon_id
					AND permissions.institution_id= ". $arrParams["institution_id"] ."
					AND permissions.permission='Student'
					AND purchase_details.pack_item_type='add-on'
					AND package_items.package_item_type='add-on'
					AND addons_config.addon_id NOT IN  (
						SELECT
							student_purchases.package_item_id
						FROM
							student_purchases
						WHERE
							student_purchases.user_id=" . $arrParams["user_id"] . "
					)
				";
			$strSql .= $strWhere . " GROUP BY addons_config.addon_id ORDER BY permissions.user_id";
			//print $strSql; exit;
			try{
				$arrResult = $this->_db->fetchAll($strSql);
			} catch (Zend_Exception $e){
				echo "There was an error MU-GSA-RTR234";
				if(DEV_ENV == 'devel'){
					echo $e->getMessage();
				}
			}
			return $arrResult;
		}
	}
}
?>
