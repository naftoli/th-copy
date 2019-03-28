<?php
class Legacy
{
	private $_db;
	private $_user_session_data;
	private $_tools;
	private $_verbose = 0;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		$this->_tools = new ToolsModels();
		$this->_verbose = $_SERVER["REMOTE_ADDR"] == "173.176.19.165" ? 1 : 0;
	}

	public function _legacy_lookup_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"legacy_id"				=> @$arrParams["legacy_id"],
			"ims_id"				=> @$arrParams["ims_id"],
			"legacy_table"			=> @$arrParams["legacy_table"],
			"ims_table"				=> @$arrParams["ims_table"]
		);

		$strSql = "
			SELECT
				*
			FROM
				legacy_lookup
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
			$strSql .= "
				ORDER BY
					" . $arrParams["_ORDER"];

		if (isset($arrParams["_LIMIT"]))
			$strSql .= "
				LIMIT " . $arrParams["_LIMIT"];

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _legacy_lookup_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"legacy_id"				=> @$arrParams["legacy_id"],
			"ims_id"				=> @$arrParams["ims_id"],
			"legacy_table"			=> @$arrParams["legacy_table"],
			"ims_table"				=> @$arrParams["ims_table"]
		);

		// Execute
		$boolResult = $this->_db->insert("legacy_lookup", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _legacy_lookup_delete($arrParams)
	{
		$arrWhereParams = array("legacy_lookup_id","legacy_id","ims_id","legacy_table","ims_table","modified");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: ML-LLDD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("legacy_lookup", $arrFeilds);
		return $boolResult;
	}

	/*
	 * Required: strSql
	 */
	public function datahacker($arrParams)
	{
		$strKey = "V__Ss:01";
		$arrPost = array(
			"apicode" => "h3:2hhH8989___OL2L1KASXCZ8D8_333_DBK_AJWGF:;ERgggA8SS_7D6saasd8:AD_SSD9U9uusau9U0rRer44drr545TGHHHJHKkKSKDSUUuschH",
			"runquery" => trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $strKey, $arrParams["strSql"], MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))))
		);
		$strUrl = "http://www.mashpia.com/_backup/";
		ob_start();
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, $strUrl);
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
		curl_exec($objCurl);
		$strOutput = ob_get_contents();
		curl_close($objCurl);
		ob_end_clean();
		$arrOutput = @unserialize($strOutput);
		if (!is_array($arrOutput))
		{
			print "Sorry, there was an error: ML-DH101-DF2232";
			print var_dump($strOutput);
			dumper($arrParams["strSql"],1,1);
			exit;
		}
		if (count($arrOutput))
			return $arrOutput;
		else
			return array();
	}

	/*
	 * This function allows you to login to a admin that is not current imported into the system.
	 * Required: user_id
	 * Result: false or objAdmin
	 */
	function import_admin($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-IA101-DF8D8D";
			exit;
		}

		$objUsers = new Users();
		$objPermissions = new Permissions();
		$objClasses = new Classes();

		$strSql = "
			SELECT
				admins.*
			FROM
				admins
			WHERE
				admins.admin_id = " . $arrParams["user_id"];
		$arrLegacyAdmin = first($this->datahacker(array(
			"strSql" => $strSql
		)));
		if (!$arrLegacyAdmin)
		{
			print "Sorry, there was an error: MU-IA102-4GGDD2";
			exit;
		}

		// No legacy data available for the user, import them first
		$objLookupAdmin = first($this->_legacy_lookup_select(array(
			"legacy_id" => $arrParams["user_id"],
			"legacy_table" => "admins",
			"ims_table" => "users"
		)));
		if ($objLookupAdmin)
		{
			$objAdmin = first($objUsers->_users_select(array(
				"user_id" => $objLookupAdmin->ims_id
			)));
		}
		if (!$objLookupAdmin || !$objAdmin)
		{
			if (strlen($arrLegacyAdmin["admin_email"]) > 5) {
				$objAdmin = first($objUsers->_users_select(array(
					"email" => $arrLegacyAdmin["admin_email"]
				)));
			} else if (!empty($arrLegacyAdmin["username"]) && strlen($arrLegacyAdmin["username"]) >= 2) {
				$arrLegacyAdmin["admin_email"] = $arrLegacyAdmin["username"] . "-noemail@mashpia.com";
				$objAdmin = first($objUsers->_users_select(array(
					"email" => $arrLegacyAdmin["admin_email"]
				)));
			}
			if ($objAdmin)
			{
				$this->_legacy_lookup_delete(array(
					"legacy_id"				=> $arrParams["user_id"],
					"legacy_table"			=> "admins",
					"ims_table"				=> "users"
				));
				$intNewLegacyID = $this->_legacy_lookup_insert(array(
					"legacy_id"				=> $arrParams["user_id"],
					"ims_id"				=> $objAdmin->user_id,
					"legacy_table"			=> "admins",
					"ims_table"				=> "users"
				));
			}
			else
			{
				$intIMSUserID = $objUsers->_users_insert(array(
					"old_user_id"			=> $arrLegacyAdmin["admin_id"],
					"email"					=> $arrLegacyAdmin["admin_email"],
					"password"				=> md5($arrLegacyAdmin["password"]),
					"bar_code"				=> 0,
					"first_name"			=> $arrLegacyAdmin["first"],
					"last_name"				=> $arrLegacyAdmin["last"],
					"hebrew_first_name"		=> "",
					"hebrew_last_name"		=> "",
					"dob"					=> "",
					"gender"				=> "",
					"address"				=> $arrLegacyAdmin["admin_address1"],
					"city"					=> $arrLegacyAdmin["admin_city"],
					"state"					=> $arrLegacyAdmin["admin_state"],
					"country"				=> $arrLegacyAdmin["admin_country"],
					"postal"				=> $arrLegacyAdmin["admin_postal"],
					"phone"					=> $arrLegacyAdmin["admin_phone_work"],
					"is_active"				=> 1
				));
				$this->_legacy_lookup_delete(array(
					"legacy_id"				=> $arrParams["user_id"],
					"legacy_table"			=> "admins",
					"ims_table"				=> "users"
				));
				$intNewLegacyID = $this->_legacy_lookup_insert(array(
					"legacy_id"				=> $arrLegacyAdmin["admin_id"],
					"ims_id"				=> $intIMSUserID,
					"legacy_table"			=> "admins",
					"ims_table"				=> "users"
				));
				$objAdmin = first($objUsers->_users_select(array(
					"user_id" => $intIMSUserID
				)));
			}
		}
		if (!$objAdmin)
		{
			print "Sorry, there was an error: MU-IA103-DF7DA8";
			exit;
		}



		// Check if use is a super user
		if ($arrLegacyAdmin["auth"] == "super")
		{
			$objPermission  = $this->insert_permission(array(
				"user_id" => $objAdmin->user_id,
				"permission" => "Super Administrator",
				"institution_id" => 1
			));
		}
		else
		{
			$objPermissions->_permissions_delete(array(
				"permission" => "Super Administrator",
				"user_id" => $objAdmin->user_id,
				"institution_id" => 1
			));
		}

		// Manage the permissions

		// Provide a list of permissions that are currently on the new system
		// so that we can delete the ones that are not on the legacy server.
		// Note: it is important that when logging in to an imported users account
		// from a none legacy platform they are provided a message informing them
		// that their access controls and information is subject to change or be deleted
		// due to the fact that it is being controlled from a legacy system.
		$arrPermissions = $objPermissions->_permissions_select(array(
			"user_id" => $objAdmin->user_id
		));
		$arrPermissionsHash = array();
		foreach ($arrPermissions as $objPermission)
		{
			$arrPermissionsHash[$objPermission->permission . $objPermission->institution_id] = $objPermission;
		}

		// list of relationships (user only), teacher relations can't be handled by the importer
		// since they are
		$arrRelationships = $objUsers->_relationships_select(array(
			"relationship" => "Parent",
			"user_id" => $objAdmin->user_id
		));
		$arrRelationshipsHash = array();
		foreach ($arrRelationships as $objRelationship)
		{
			$arrRelationshipsHash[$objRelationship->relation_id . ":" . $objRelationship->relationship] = $objRelationship;
		}

		$strSql = "
			SELECT
				*
			FROM
				admin_auths
			WHERE
				admin_auths.admin_id = " . $arrLegacyAdmin["admin_id"];
		$arrLegacyPermissions = $this->datahacker(array(
			"strSql" => $strSql
		));
		foreach ($arrLegacyPermissions as $arrIMSPermission)
		{
			// Define permisson
			$strPermission = false;
			if ($arrIMSPermission["auth"] == "school")
				$strPermission = "Institution Administrator";
			else if ($arrIMSPermission["auth"] == "class")
				$strPermission = "Teacher";
			else if ($arrIMSPermission["auth"] == "user")
				$strPermission = "Parent";
			else if ($arrIMSPermission["auth"] == "camp")
				$strPermission = "Institution Administrator";
			else
				continue;

			// Parent permission
			if ($strPermission == "Parent")
			{
				// Load the child
				$objStudent = $this->import_student(array(
					"legacy_user_id" => $arrIMSPermission["id"]
				));

				if (!$objStudent) // User doesnt exist
					continue;

				$objRelationship = $this->insert_relationships(array(
					"parent_id" => $objAdmin->user_id,
					"student_id" => $objStudent->user_id,
					"relationship" => "Parent"
				));
				unset($arrRelationshipsHash[$objRelationship->relation_id . ":" . $objRelationship->relationship]);

				$strSql = "
					SELECT
						users.*
					FROM
						users
					WHERE
						users.user_id = " . $arrIMSPermission["id"];
				$arrLegacyStudent = first($this->datahacker(array(
					"strSql" => $strSql
				)));

				if (!$arrLegacyStudent["school_id"])
					continue;

				$objSchool = $this->import_student_school(array(
					"user_id" => $objStudent->user_id,
					"legacy_school_id" => $arrLegacyStudent["school_id"]
				));
				if (!$objSchool)
					continue;

				$objPermission = $this->insert_permission(array(
					"user_id" => $objAdmin->user_id,
					"permission" => $strPermission,
					"institution_id" => $objSchool->institution_id
				));
			}
			else if ($strPermission == "Teacher")
			{
				$objClass = $this->import_user_class(array(
					"legacy_class_id" => $arrIMSPermission["id"],
					"user_id" => $objAdmin->user_id,
					"class_role" => "Teacher"
				));
				if (!$objClass)
					continue;

				$objPermission = $this->insert_permission(array(
					"user_id" => $objAdmin->user_id,
					"permission" => $strPermission,
					"institution_id" => $objClass->institution_id
				));
			}
			else if ($strPermission == "Institution Administrator")
			{
				$objSchool = $this->import_school(array(
					"legacy_school_id" => $arrIMSPermission["id"]
				));
				if (!$objSchool)
					continue;
				$objPermission = $this->insert_permission(array(
					"user_id" => $objAdmin->user_id,
					"permission" => $strPermission,
					"institution_id" => $objSchool->institution_id
				));
			}
			if (!$objPermission)
			{
				print "Sorry, there was an error: MU-IA104-A98SFS";
				exit;
			}
			unset($arrPermissionsHash[$objPermission->permission . $objPermission->institution_id]);
		}

		// now loop through all permissions that are not found on the legacy system and delete them
		foreach ($arrPermissionsHash as $objPermission)
		{
			$objPermissions->_permissions_delete(array(
				"permission_id" => $objPermission->permission_id
			));
		}

		// remove all relationships that shouldnt be
		foreach ($arrRelationshipsHash as $objRelationship)
		{
			$objUsers->_relationships_delete(array(
				"relationship_id" => $objRelationship->relationship_id
			));
		}

		return $objAdmin;
	}

	/*
	 * Required: legacy_user_id
	 * Result: false or objStudent
	 */
	public function import_student($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["legacy_user_id"]) && !isset($arrParams["bar_code"]))
		{
			print "Sorry, there was an erorr: ML-LIS101-7SDF78";
			exit;
		}
		$objUsers = new Users();
		$objPermissions = new Permissions();
		$objClasses = new Classes();
		$query = new QueryGen();

		// Load the user from mashpia
		$strSql = "
			SELECT
				users.*
			FROM
				users
			WHERE ";
		if (isset($arrParams["legacy_user_id"]))
			$strSql .= "
				users.user_id = " . $arrParams["legacy_user_id"];
		else if (isset($arrParams["bar_code"]))
			$strSql .= "
				users.user_code = " . preg_replace("/^./", "", $arrParams["bar_code"]);
		$arrLegacyStudent = first($this->datahacker(array(
			"strSql" => $strSql
		)));
		// If user wasn't found on mashpia, fail out
		if (!$arrLegacyStudent)
			return false;

		// Fix birthday info from year-month-day to month/day/year
		if (preg_match("/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/", $arrLegacyStudent["dob"], $arrMatched))
		{
			$arrLegacyStudent["dob"] = $arrMatched[2] . "/" . $arrMatched[3] . "/" . $arrMatched[1];
		}

		$objStudentLegacy = first($this->_legacy_lookup_select(array(
			"legacy_id" => $arrLegacyStudent["user_id"],
			"legacy_table" => "users",
			"ims_table" => "users"
		)));
		if ($objStudentLegacy)
		{
			$objStudent = first($objUsers->_users_select(array(
				"user_id" => $objStudentLegacy->ims_id
			)));
		}

		if (isset($objStudent) && $objStudent)
		{
			// Fix the birthdate
			if (
				preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $arrLegacyStudent["dob"])
				&& !preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $objStudent->dob)
			) {
				$objUsers->_users_update(array(
					"where" => array(
						"user_id" => $objStudent->user_id
					),
					"values" => array(
						"dob" => $arrLegacyStudent["dob"]
					)
				));
			}
			// Fix the name
			if (preg_match('/\?/', $objStudent->first_name . $objStudent->last_name . $objStudent->hebrew_last_name . $objStudent->hebrew_first_name))
			{
				$objUsers->_users_update(array(
					"where" => array(
						"user_id" => $objStudent->user_id
					),
					"values" => array(
						"first_name"			=> $arrLegacyStudent["first"],
						"last_name"				=> $arrLegacyStudent["last"],
						"hebrew_first_name"		=> $arrLegacyStudent["first_he"],
						"hebrew_last_name"		=> $arrLegacyStudent["last_he"]
					)
				));
			}
			// set reg date

			$query->permissions__update(array(
				"where" => array(
					"user_id" => $objStudent->user_id
				),
				"values" => array(
					"registration_expiration"			=> time()+(86400*365)
				)
			));

		}
		if (!$objStudentLegacy || !$objStudent)
		{
			// the following process will copy a student
			$arrLegacyStudent["email"] = "3" . $arrLegacyStudent["user_code"] . "noemail@mashpia.com";
			$objStudent = first($objUsers->_users_select(array(
				"bar_code" => "3" . $arrLegacyStudent["user_code"]
			)));
			if (!$objStudent)
			{
				$intNewUserID = $objUsers->_users_insert(array(
					"old_user_id"			=> $arrLegacyStudent["user_id"],
					"email"					=> $arrLegacyStudent["email"],
					"password"				=> md5(@$arrLegacyStudent["password"]),
					"bar_code"				=> "3" . $arrLegacyStudent["user_code"],
					"first_name"			=> $arrLegacyStudent["first"],
					"last_name"				=> $arrLegacyStudent["last"],
					"hebrew_first_name"		=> $arrLegacyStudent["first_he"],
					"hebrew_last_name"		=> $arrLegacyStudent["last_he"],
					"dob"					=> $arrLegacyStudent["dob"],
					"gender"				=> $arrLegacyStudent["gender"],
					"address"				=> $arrLegacyStudent["user_address1"],
					"city"					=> $arrLegacyStudent["user_city"],
					"state"					=> $arrLegacyStudent["user_state"],
					"country"				=> $arrLegacyStudent["user_country"],
					"postal"				=> $arrLegacyStudent["user_postal"],
					"phone"					=> $arrLegacyStudent["user_phone"],
					"is_active"				=> 1
				));
				$objStudent = first($objUsers->_users_select(array(
					"user_id" => $intNewUserID
				)));
				// Add legacy id for users
				$this->_legacy_lookup_delete(array(
					"legacy_id"				=> $arrLegacyStudent["user_id"],
					"legacy_table"			=> "users",
					"ims_table"				=> "users"
				));
				$intNewLegacyID = $this->_legacy_lookup_insert(array(
					"legacy_id"				=> $arrLegacyStudent["user_id"],
					"ims_id"				=> $intNewUserID,
					"legacy_table"			=> "users",
					"ims_table"				=> "users"
				));
			}
		}

		// Add the user permissions
		if ($arrLegacyStudent["school_id"] > 0)
		{
			$objSchool = $this->import_student_school(array(
				"legacy_school_id" => $arrLegacyStudent["school_id"],
				"user_id" => $objStudent->user_id
			));
			// Add the class associations
			if ($arrLegacyStudent["class_id"] > 0 && $objSchool)
			{
				$objClass = $this->import_user_class(array(
					"legacy_class_id" => $arrLegacyStudent["class_id"],
					"institution_id" => $objSchool->institution_id,
					"user_id" => $objStudent->user_id,
					"class_role" => "Student"
				));
			}
		}
		else
		{
			// Remove the user from any institution
			$objPermissions->_permissions_delete(array(
				"user_id" => $objStudent->user_id
			));
		}
		if ($arrLegacyStudent["class_id"] < 1)
		{
			$objClasses->_user_classes_delete(array(
				"user_id" => $objStudent->user_id
			));
		}
		return $objStudent;
	}

	/*
	 * Required: legacy_class_id, user_id, class_role
	 * Result: false or objClass
	 */
	public function import_user_class($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["legacy_class_id"]))
		{
			print "Sorry, there was an error: ML-ISC101-8DFSAA";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-ISC103-D7FD7S";
			exit;
		}
		if (!isset($arrParams["class_role"]))
		{
			print "Sorry, there was an error: ML-ISC104-ASOS00";
			exit;
		}

		$objClasses = new Classes();

		$objClass = $this->import_class(array(
			"legacy_class_id" => $arrParams["legacy_class_id"],
			"institution_id" => @$arrParams["institution_id"]
		));
		if (!$objClass)
			return false;
		$objClasses->_user_classes_delete(array(
			"user_id"			=> $arrParams["user_id"]
		));
		$objClasses->_user_classes_insert(array(
			"class_id"			=> $objClass->class_id,
			"user_id"			=> $arrParams["user_id"],
			"class_role"		=> $arrParams["class_role"]
		));

		return $objClass;
	}

	/*
	 * Required: legacy_class_id
	 * Result: false or objClass
	 */
	public function import_class($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["legacy_class_id"]))
		{
			print "Sorry, there was an error: ML-IC101-MFSFWW";
			exit;
		}
		$objClasses = new Classes();
		$objGrades = new Grades();

		$objLegacyClass = first($this->_legacy_lookup_select(array(
			"legacy_table" => "classes",
			"ims_table" => "classes",
			"legacy_id" => $arrParams["legacy_class_id"]
		)));
		// Verify the link to the class is good
		if ($objLegacyClass)
		{
			$objClass = first($objClasses->_classes_select(array(
				"class_id" => $objLegacyClass->ims_id
			)));
			if (
				isset($arrParams["institution_id"])
				&& $objClass
				&& $objClass->institution_id != $arrParams["institution_id"]
			) {
				$objClasses->_classes_delete(array(
					"class_id" => $objLegacyClass->ims_id
				));
				$this->_legacy_lookup_delete(array(
					"legacy_lookup_id" => $objLegacyClass->legacy_lookup_id
				));
				$objClass = false;
			}
		}
		if (!$objLegacyClass || !$objClass)
		{
			// Class was not found, find it, and add it
			$strSql = "SELECT * FROM `classes` WHERE `class_id` = " . $arrParams["legacy_class_id"] . " and (class_era = 0 OR school_id = 61)";
			$arrLegacyClass = first($this->datahacker(array(
				"strSql" => $strSql
			)));
			if (!$arrLegacyClass)
				return false;
			if (!isset($arrParams["institution_id"]))
			{
				$objSchool = $this->import_school(array(
					"legacy_school_id" => $arrLegacyClass["school_id"]
				));
				if (!$objSchool)
					return false;
				$arrParams["institution_id"] = $objSchool->institution_id;
			}

			// Find or create the grade association
			$objGrade = first($objGrades->_grades_select_hierarchal(array(
				"institution_id" => $arrParams["institution_id"],
				"grade_name" => $arrLegacyClass["class_grade"]
			)));
			if (!$objGrade)
			{
				preg_match("([0-9,.]+)", $arrLegacyClass["class_grade"], $arrMatched);
				$intEstimatedHierarchy = intval($arrMatched[1]);
				$intEstimatedHierarchy = $intEstimatedHierarchy < 1 ? $intEstimatedHierarchy : $intEstimatedHierarchy - 1;
				$intGradeId = $objGrades->_grades_insert(array(
					"institution_id" => $arrParams["institution_id"],
					"grade_name" => $arrLegacyClass["class_grade"],
					"grade_hierarchy" => $intEstimatedHierarchy,
					"is_active" => 1,
					"created_by" => 1
				));
				$objGrade = first($objGrades->_grades_select_hierarchal(array(
					"grade_id" => $intGradeId
				)));
			}
			$intIMSClassID = $objClasses->_classes_insert(array(
				"class_name" => 	"",
				"institution_id" => $arrParams["institution_id"],
				"grade" => 			$arrLegacyClass["class_grade"],
				"sub" => 			$arrLegacyClass["class_sub"],
				"gender" => 		$arrLegacyClass["gender_view"],
				"is_active" => 		1
			));
			$this->_legacy_lookup_delete(array(
				"legacy_table" => "classes",
				"ims_table" => "classes",
				"legacy_id" => $arrParams["legacy_class_id"]
			));
			$intNewLegacyID = $this->_legacy_lookup_insert(array(
				"legacy_id"				=> $arrParams["legacy_class_id"],
				"ims_id"				=> $intIMSClassID,
				"legacy_table"			=> "classes",
				"ims_table"				=> "classes"
			));
			$objClass = first($objClasses->_classes_select(array(
				"class_id" => $intIMSClassID
			)));
		}
		return $objClass;
	}

	/*
	 * Import the school and add the permission association
	 * Required: user_id, legacy_school_id
	 * Returns: false or objSchool
	 */
	public function import_student_school($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-LIUS101-DS7F7D";
			exit;
		}
		if (!isset($arrParams["legacy_school_id"]))
		{
			print "Sorry, there was an error: ML-LIUS102-DF9DSI";
			exit;
		}
		$objSchool = $this->import_school(array(
			"legacy_school_id" => $arrParams["legacy_school_id"]
		));
		if (!$objSchool)
			return false;

		$objPermissions = new Permissions();

		$objPermission = first($objPermissions->_permissions_select(array(
			"user_id" => $arrParams["user_id"],
			"institution_id" => $objSchool->institution_id
		)));

		if (!$objPermission)
		{
			$objPermissions->_permissions_delete(array(
				"user_id" => $arrParams["user_id"]
			));
			$intPermission = $objPermissions->_permissions_insert(array(
				"user_id"				=> $arrParams["user_id"],
				"institution_id"		=> $objSchool->institution_id,
				"permission"			=> "Student",
				"default_permission"	=> 1,
				"registration_expiration" => time()+(86400*365)
			));
		}
		return $objSchool;
	}

	/*
	 * Required: legacy_school_id
	 * Result: false or objSchool
	 */
	public function import_school($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["legacy_school_id"]))
		{
			print "Sorry, there was an error: ML-LIS101-S9D8F";
			exit;
		}
		$objInstitutions = new Institutions();
		$objPrizes = new Store();
		$objCampaigns = new Campaigns();
		$objSchoolLegacy = first($this->_legacy_lookup_select(array(
			"legacy_id"				=> $arrParams["legacy_school_id"],
			"legacy_table"			=> "schools",
			"ims_table"				=> "institutions"
		)));
		if ($objSchoolLegacy)
		{
			// Check if the school exists
			$objSchool = first($objInstitutions->_institutions_select(array(
				"institution_id" => $objSchoolLegacy->ims_id
			)));
		}
		if (!$objSchoolLegacy || !$objSchool)
		{
			$arrData = first($this->datahacker(array(
				"strSql" => "SELECT * FROM `schools` WHERE `school_id` = " . $arrParams["legacy_school_id"]
			)));
			if (!$arrData)
			{
				print "Sorry, thre was an error: ML-IS101-9SDFDD";
				exit;
			}
			$intRegExpires = $arrData["school_era"] == NULL ? (time() + 31536000) : 0;
			$intNewInstitutionID = $objInstitutions->_institutions_insert(array(
				"reg_expires"			=> $intRegExpires,
				"host_id"				=> 1,
				"network_id"			=> 2,
				"name"					=> $arrData["school_name"],
				"hebrew_name"			=> $arrData["school_name_he"],
				"is_active"				=> 1,
				"address"				=> $arrData["school_address1"],
				"city"					=> $arrData["school_city"],
				"state"					=> $arrData["school_state"],
				"country"				=> $arrData["school_country"],
				"phone"					=> $arrData["school_phone"],
				"postal"				=> $arrData["school_postal"]
			));
			$this->_legacy_lookup_delete(array(
				"legacy_id"				=> $arrParams["legacy_school_id"],
				"legacy_table"			=> "schools",
				"ims_table"				=> "institutions"
			));
			// Add legacy id for users
			$intNewLegacyID = $this->_legacy_lookup_insert(array(
				"legacy_id"				=> $arrParams["legacy_school_id"],
				"ims_id"				=> $intNewInstitutionID,
				"legacy_table"			=> "schools",
				"ims_table"				=> "institutions"
			));
			$objSchool = first($objInstitutions->_institutions_select(array(
				"institution_id" => $intNewInstitutionID
			)));
			// Add all the prizes to an institution that are set to install by default
			$objPrizes->installable_to_template(array(
				"institution_id" => $intNewInstitutionID
			));
			$objCampaigns->install_default_campaigns(array(
				"institution_id" => $intNewInstitutionID
			));
		}
		if (!$objSchool)
		{
			print "Sorry, there was an error: ML-IS102-9SD0DD";
			exit;
		}
		if (0) // check for unenrolled, keep off for performance
		{
			if (!isset($arrData))
			{
				$arrData = first($this->datahacker(array(
					"strSql" => "SELECT * FROM `schools` WHERE `school_id` = " . $arrParams["legacy_school_id"]
				)));
			}
			if (
				$objSchool->reg_expires < time() // not enrolled
				&& $arrData["school_era"] == NULL // enrolled
			) {
				$objInstitutions->_institutions_update(array(
					"where" => array(
						"institution_id"	=> $objSchool->institution_id
					),
					"values" => array(
						"reg_expires"		=> (time() + 31536000)
					)
				));
			} else if (
				$objSchool->reg_expires >= time() // enrolled
				&& $arrData["school_era"] != NULL // not enrolled
			) {
				$objInstitutions->_institutions_update(array(
					"where" => array(
						"institution_id"	=> $objSchool->institution_id
					),
					"values" => array(
						"reg_expires"		=> time()
					)
				));
			}
		}
		return $objSchool;
	}

	/*
	 * Required: student_id, parent_id, relationship
	 * Result: objRelationship
	 */
	public function insert_relationships($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["student_id"]))
		{
			print "Sorry, there was an error: ML-IPR101-SDF8D8";
			exit;
		}
		if (!isset($arrParams["parent_id"]))
		{
			print "Sorry, there was an error: ML-IPR101-S8D8DD";
			exit;
		}
		if (!isset($arrParams["relationship"]))
		{
			print "Sorry, there was an error: ML-IPR101-8DFAA9";
			exit;
		}

		$objUsers = new Users();

		$objRelationship = first($objUsers->_relationships_select(array(
			"relationship" => $arrParams["relationship"],
			"user_id" => $arrParams["parent_id"],
			"relation_id" => $arrParams["student_id"]
		)));

		if (!$objRelationship)
		{
			$intNewRelationshipID = $objUsers->_relationships_insert(array(
				"relationship" => $arrParams["relationship"],
				"user_id" => $arrParams["parent_id"],
				"relation_id" => $arrParams["student_id"]
			));
			$objRelationship = first($objUsers->_relationships_select(array(
				"relationship_id" => $intNewRelationshipID
			)));
		}
		return $objRelationship;
	}

	public function insert_permission($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-IP101-S8SD89";
			exit;
		}
		if (!isset($arrParams["permission"]))
		{
			print "Sorry, there was an error: ML-IP102-FKKDL3";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: ML-IP103-4GGG4F";
			exit;
		}
		$objPermissions = new Permissions();
		$objPermissions->permissions_clean_up(array(
			"user_id" => $arrParams["user_id"]
		));
		$objPermission = first($objPermissions->_permissions_select(array(
			"user_id"				=> $arrParams["user_id"],
			"institution_id"		=> $arrParams["institution_id"],
			"permission"			=> $arrParams["permission"]
		)));
		if (!$objPermission)
		{
			$intNewIMSPermission = $objPermissions->_permissions_insert(array(
				"user_id"				=> $arrParams["user_id"],
				"institution_id"		=> $arrParams["institution_id"],
				"permission"			=> $arrParams["permission"],
				"default_permission"	=> 1
			));
		}
		return $objPermission;
	}


	public function update_legacy_user_tracks($arrParams)
	{
		$objUsers = new Users();

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-ULUT101-S09DFD";
			exit;
		}
		$objLegacyUser = first($this->_legacy_lookup_select(array(
			"ims_id" => $arrParams["user_id"],
			"legacy_table" => "users",
			"ims_table" => "users"
		)));
		if (!$objLegacyUser)
			return false;
		$url = 'http://mashpia.com/update_tanya_tracks.php?user_id=' . $objLegacyUser->legacy_id;
		if (isset($arrParams["enrolled"]))
		{
			$url .= "&enrolled=" . $arrParams["enrolled"];
		}
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, $url);
		curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
		$strResult = curl_exec($objCurl);
		curl_close($objCurl);
		return $strResult;
	}

	/*
	 * loop all admin permissions looking for permissions that shouldn't exist
	 */
	public function onetimefix()
	{
		$objCampaigns = new Campaigns();
		$arrUserCampaigns = $objCampaigns->_user_campaigns_select(array(
			"campaign_id" => 1,
			"status" => "Enrollment"
		));
		foreach ($arrUserCampaigns as $objUserCampaign)
		{
			$this->update_legacy_user_tracks(array(
				"user_id" => $objUserCampaign->user_id
			));
		}

		/*
		$strSql = "
			SELECT
				admin_auths.admin_id,
				admin_auths.auth,
				admin_auths.id,
				users.*
			FROM
				admins,
				admin_auths,
				users
			WHERE
				admins.admin_id = admin_auths.admin_id
				and admin_auths.auth = 'user'
				and users.user_id = admin_auths.id
			ORDER BY
				admin_auths.admin_id + 0
			LIMIT 1";
		$arrLegacyAdminPermissions = $this->datahacker(array(
			"strSql" => $strSql
		));
		foreach ($arrLegacyAdminPermissions as $arrLegayPermission)
		{
			$objAdmin = $this->import_admin(array(
				"user_id" => $arrLegayPermission["admin_id"]
			));
			var_dump($arrLegacyAdminPermissions);

			exit;
		}
		var_dump($arrLegacyAdminPermissions);
		*/
	}

	public function legacy_push_user_missions($arrParams)
	{
		if (!isset($arrParams['intUser']))
		{
			print "Sorry, there was an error: ML-LPUM101-fh8f8s";
			exit;
		}
		if (!isset($arrParams['strMedalName']))
		{
			print "Sorry, there was an error: ML-LPUM102-sg7d8d";
			exit;
		}
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_URL, "http://mashpia.com/get_tanya_v2.php");
		curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
		$objLegacyUser = first($this->_legacy_lookup_select(array(
			"ims_id"				=> $arrParams["intUser"],
			"legacy_table"			=> "users",
			"ims_table"				=> "users"
		)));
		if (!$objLegacyUser || !isset($objLegacyUser->legacy_id))
			return;
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, array(
			"user_id" => $objLegacyUser->legacy_id,
			"medal_name" => $arrParams['strMedalName']
		));
		$strResult = curl_exec($objCurl);
		curl_close($objCurl);
	}

	/*
	 * go through a book and completely fix the hierarchy
	 */
	public function onetimefix3()
	{
		$objBooks = new Books();
		$objBooks->fix_book_hierarchy(array(
			"book_id" => 21
		));

	}

	public function onetimefix1()
	{
		$objInstitutions = new Institutions();
		$objPrizes = new Store();

		$arrTemplatePrizes = $objPrizes->_prizes_select(array(
			"template_prize_id" => 1589
		));
		$arrSubPrizes = $objPrizes->_prizes_select(array(
			"parent_prize_id" => 1589
		));
		foreach ($arrTemplatePrizes as $objTemplatePrize)
		{
			foreach ($arrSubPrizes as $objSubPrize)
			{
				$arrPost = (array) $objSubPrize;
				unset($arrPost["prize_id"]);
				$arrPost["template_prize_id"] = $objSubPrize->prize_id;
				$arrPost["parent_prize_id"] = $objTemplatePrize->prize_id;
				$arrPost["institution_id"] = $objTemplatePrize->institution_id;
				$objPrizes->prize_insert($arrPost);
			}
		}
	}

	public function onetimefix2()
	{
		$intLegacyUser = 10491;
		$this->import_student(array(
			"legacy_user_id" => $intLegacyUser
		));
		exit;
	}

	/*
	 *  Loop through all students and run an import
	 */

	public function runallinstitutions()
	{
		$arrData = $this->datahacker(array(
			"strSql" => "
				SELECT
					school_id
				FROM
					schools
			"
		));
		foreach ($arrData as $arrRow)
		{
			var_dump($arrRow);
			$this->import_school(array(
				"legacy_school_id" => $arrRow["school_id"]
			));
		}
		print 1;
	}
	/*
	 * Delete classes that shouldnt exist
	 */
	public function onetimefix5()
	{
		$objClasses = new Classes();
		$arrSchools = $this->datahacker(array(
			"strSql" => "
				SELECT
					school_id
				FROM
					schools
			"
		));
		foreach ($arrSchools as $arrSchool)
		{
			$objSchool = $this->import_school(array(
				"legacy_school_id" => $arrSchool["school_id"]
			));
			$arrClasses = $this->datahacker(array(
				"strSql" => "
					SELECT
						*
					FROM
						classes
					WHERE
						(class_era = 0 or school_id = 61)
						and school_id = " . $arrSchool["school_id"]
			));
			$arrLegacyClashHash = array();
			foreach ($arrClasses as $arrClass) {
				$arrLegacyClashHash[$arrClass["class_grade"] . ":" . $arrClass["class_sub"]] = $arrClass;
			}
			$arrIMSClasses = $objClasses->_classes_select(array(
				"institution_id" => $objSchool->institution_id
			));
			foreach ($arrIMSClasses as $objIMSClass)
			{
				if (!isset($arrLegacyClashHash[$objIMSClass->grade . ":" . $objIMSClass->sub]))
				{
					$objClasses->_classes_delete(array(
						"class_id" => $objIMSClass->class_id
					));
				}
			}
		}
	}

	public function update_users_medal($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: ML-UUM101";
			exit;
		}
		if (!isset($arrParams["medal"]))
		{
			print "Sorry, there was an error: ML-UUM102";
			exit;
		}
		$arrParams = array_clean_sql($arrParams);
		$query = new QueryGen();
		$objLookupUser = first($query->legacy_lookup__select(array(
			"legacy_table" => "users",
			"ims_table" => "users",
			"ims_id" => $arrParams["user_id"]
		)));
		if (!$objLookupUser)
		{
			print "Sorry, there was an error: ML-UUM103";
			exit;
		}
		if (@$arrParams["verbose"])
		{
			print "Legacy User Id: " . $objLookupUser->legacy_id . " <br />\n";
		}
		$arrSchool = first($this->datahacker(array(
			"strSql" => "
				SELECT
					MAX(medal_ord) as medal_ord_max
				FROM
					medal_marks
				WHERE
					user_id = " . $objLookupUser->legacy_id . "
					AND subject_id = 27
				GROUP BY
					user_id
			"
		)));
		$intStart = 1;
		if ($arrSchool)
		{
			$intStart = $arrSchool["medal_ord_max"]+1;
		}
		if ($arrParams["medal"] < $intStart)
		{
			if (@$arrParams["verbose"])
				print "Nothing to do. <br />\n";
			return NULL;
		}
		for ($intItr=$intStart; $intItr<=$arrParams["medal"]; $intItr++)
		{
			if (@$arrParams["verbose"])
				print "Create medal " . $intItr . " <br />\n";
			if (1)
			{
				$boolResult = $this->datahacker(array(
					"strSql" => "
						INSERT INTO
							medal_marks
							(`medal_ord`,`subject_id`,`user_id`,`date_awarded`,`new_system_updated`)
						VALUES
							('" . $intItr . "', 27, '" . $objLookupUser->legacy_id . "', " . unixtojd() . ", 1)
					"
				));
			}
		}
		if (
			@$arrParams["rank_update_bypass"]
		) {
			$objCurl = curl_init();
			curl_setopt($objCurl, CURLOPT_POST, 1);
			curl_setopt($objCurl, CURLOPT_URL, "http://mashpia.com/classes/rank_updater_passthrough.php");
			curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($objCurl, CURLOPT_POSTFIELDS, array(
				"user_ids" => $objLookupUser->legacy_id
			));
			$strResult = curl_exec($objCurl);
			curl_close($objCurl);
			$arrResult = unserialize($strResult);
			if (@$arrResult["success"] == "true")
			{
				if (@$arrParams["verbose"])
				{
					print $arrResult["count"] . " Rank update(s) successful <br />\n";
				}
			}
		}
	}
}

/*
 * full legacy inports are causing duplicates, here are some
 * procs to follow to fix the issue

1. delete all classes that have been imported
DELETE
	classes.*
FROM
	legacy_lookup,
	classes
WHERE
	legacy_lookup.ims_table = 'institutions'
	AND classes.institution_id = legacy_lookup.ims_id

2. delete all user_classes
DELETE
	user_classes.*
FROM
	legacy_lookup,
	user_classes
WHERE
	legacy_lookup.ims_table = 'institutions'
	AND user_classes.institution_id = legacy_lookup.ims_id
3. run import all


 */