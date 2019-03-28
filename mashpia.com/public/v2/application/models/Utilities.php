<?php
class Utilities
{
	private $_db;

	// Used in function epoch_parshos
	private $arrParshos;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
	}

	public function dispatch_helper($arrParams)
	{
		$objCookies = new Zend_Session_Namespace('user_session_data');
		$query = new QueryGen();
		$objConfig = new Config();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		if (
			!$objCookies->user_id
			|| !$objCookies->permission_id
			|| !$objCookies->permission
			|| !$objCookies->institution_id
		)
			header('Location: ' . WEB_ROOT . '/logout/index/' . $strParam);
		$objPermission = first($query->permissions__select(array(
			"user_id" => $objCookies->user_id,
			"permission_id" => $objCookies->permission_id,
			"permission" => $objCookies->permission,
			"institution_id" => $objCookies->institution_id
		)));
		if (!$objPermission)
			header('Location: ' . WEB_ROOT . 'logout/index/' . $strParam);
		$arrUserOptions = first($objConfig->load(array(
			"set" => "system",
			"key" => "terminology",
			"institution_id" => $objCookies->institution_id
		)));
		if ($arrUserOptions) {
			$strTerminology = reset($arrUserOptions);
			global $arrAppDetails;
			if (isset($arrAppDetails[$objPermission->template_style]))
			{
				$arrDetails = &$arrAppDetails[$objPermission->template_style];
				$arrDetails['terminology'] = $strTerminology;
			}
		}
		return $objPermission;
	}

	public function dispatch_helper_hebrewschools($arrParams)
	{
		$objCookies = new Zend_Session_Namespace('hebrewschools');
		$query = new QueryGen();
		$objConfig = new Config();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		if (
			!$objCookies->user_id
			|| !$objCookies->permission_id
			|| !$objCookies->permission
			|| !$objCookies->institution_id
		)
			header('Location: ' . WEB_ROOT . 'hebrewschools/index/' . $strParam);
		$objPermission = first($query->permissions__select(array(
			"user_id" => $objCookies->user_id,
			"permission_id" => $objCookies->permission_id,
			"permission" => $objCookies->permission,
			"institution_id" => $objCookies->institution_id
		)));
		if (!$objPermission)
			header('Location: ' . WEB_ROOT . 'hebrewschools/index/' . $strParam);
		$arrUserOptions = first($objConfig->load(array(
			"set" => "system",
			"key" => "terminology",
			"institution_id" => $objCookies->institution_id
		)));
		if ($arrUserOptions) {
			$strTerminology = reset($arrUserOptions);
			global $arrAppDetails;
			if (isset($arrAppDetails[$objPermission->template_style]))
			{
				$arrDetails = &$arrAppDetails[$objPermission->template_style];
				$arrDetails['terminology'] = $strTerminology;
			}
		}
		return $objPermission;
	}

	public function sliced_to_stacked_array_converter($arrInput)
	{
		$arrResult = array();
		foreach ($arrInput as $intKey => $arrItems)
		{
			if (is_array($arrItems))
			{
				foreach ($arrItems as $intItem => $strItem)
				{
					$arrResult[$intItem][$intKey] = $strItem;
				}
			}
		}
		return $arrResult;
	}

	public function FrequencyTextToSingular ($strText)
	{
		$arrText = array(
			"yearly" => "year",
			"monthly" => "month",
			"weekly" => "week",
			"daily" => "day"
		);
		return $arrText[strtolower($strText)];
	}

	public function epoch_to_institution_week_start($intTimestamp)
	{
		while (date("D", $intTimestamp) != "Sat")
		{
			$intTimestamp -= 86400;
		}
		return $intTimestamp;
	}

	/*
	 * Convert a unix timestamp to a parshos
	 */
	public function epoch_parshos($intTimestamp)
	{
		$_DEBUG = 0;

		$intTimestamp = $this->epoch_to_institution_week_start(intval($intTimestamp));
		if (!$intTimestamp)
		{
			print "Sorry, there was an error: MU-EP101-7DSFDF";
			exit;
		}

		$intJDTime = floor(unixtojd($intTimestamp+(86400*7)));
		//$intJDTime = floor(unixtojd($intTimestamp)+($intTimestamp%60*60*24)/60*60*24);

		// Query and store the parshos if first time used
		if (!$this->arrParshos)
		{
			$strSql = "
				SELECT
					*
				FROM
					dataset_parshos";
			$arrParshos = $this->_db->fetchAll($strSql);
			$this->arrParshos = $arrParshos;
		}

		// Validate cache
		if (
			!isset($this->arrParshos)
			|| !is_array($this->arrParshos)
		) {
			print "Sorry, there was an error: MU-EP102-3GRGRE";
			exit;
		}

		if ($_DEBUG)
			print "Time to match: " . $intJDTime . " <br>\n";
		foreach ($this->arrParshos as $objParsha)
		{
			if ($_DEBUG)
				print "(
					{$objParsha->start} <= $intJDTime <br>
					&& {$objParsha->end} >= $intJDTime <br>
				)";
			if (
				$objParsha->start <= $intJDTime
				&& $objParsha->end >= $intJDTime
			) {
				$objParsha->gragorian_from_jd = jdtogregorian($intJDTime);
				return $objParsha;
			}
		}
		return false;
	}

	public function parseDateTime($strStartDate=0, $strEndDate=0, $strStartTime=0, $strEndTime=0) {
		// Parse the start and end, date and time

		$intStartMonth = $intStartDay = $intStartYear = $intStartHour = $intStartMinute = $intStartSecond = 0;
		if (
			isset($strStartDate)
			&& !empty($strStartDate)
			&& $strStartDate
		) {
			if (preg_match("/([0-9]{2}) *[\/-] *([0-9]{2}) *[\/-] *([0-9]{4})/", $strStartDate, $arrMatched)) {
				list($strStartDate, $intStartMonth, $intStartDay, $intStartYear) = $arrMatched;
				$strStartDate = $intStartYear . "-" . $intStartMonth . "-" . $intStartDay;
			} else {
				return array(
					"start_date" => "",
					"end_date" => "",
					"start_time" => "",
					"end_time" => "",
					"error" => "The start date was invalid."
				);
			}
		}
		if (
			isset($strStartTime)
			&& !empty($strStartTime)
			&& $strStartTime
		) {
			if (preg_match("/0?([0-9]{1,2}):0?([0-9]{1,2}):0?([0-9]{1,2})/", $strStartTime, $arrMatched)) {
				list($strStartTime, $intStartHour, $intStartMinute, $intStartSecond) = $arrMatched;
				$strStartTime = $intStartHour . ":" . $intStartMinute . ":" . $intStartSecond;
			} else {
				return array(
					"start_date" => "",
					"end_date" => "",
					"start_time" => "",
					"end_time" => "",
					"error" => "The start time was invalid."
				);
			}
		}

		$intEndMonth = $intEndDay = $intEndYear = $intEndHour = $intEndMinute = $intEndSecond = 0;
		if (
			isset($strEndDate)
			&& !empty($strEndDate)
			&& $strEndDate
		) {
			if (preg_match("/([0-9]{2}) *[\/-] *([0-9]{2}) *[\/-] *([0-9]{4})/", $strEndDate, $arrMatched)) {
				list($strEndDate, $intEndMonth, $intEndDay, $intEndYear) = $arrMatched;
				$strEndDate = $intEndYear . "-" . $intEndMonth . "-" . $intEndDay;
			} else {
				return array(
					"start_date" => "",
					"end_date" => "",
					"start_time" => "",
					"end_time" => "",
					"error" => "The end date was invalid."
				);
			}
		}
		if (
			isset($strEndTime)
			&& !empty($strEndTime)
			&& $strEndTime
		) {
			if (preg_match("/0?([0-9]{1,2}):0?([0-9]{1,2}):0?([0-9]{1,2})/", $strEndTime, $arrMatched)) {
				list($strEndTime, $intEndHour, $intEndMinute, $intEndSecond) = $arrMatched;
				$strEndTime = $intEndHour . ":" . $intEndMinute . ":" . $intEndSecond;
			} else {
				return array(
					"start_date" => "",
					"end_date" => "",
					"start_time" => "",
					"end_time" => "",
					"error" => "The end time was invalid."
				);
			}
		}

		$intUnixStart = mktime($intStartHour,$intStartMinute,$intStartSecond,$intStartMonth,$intStartDay,$intStartYear);
		$intUnixEnd = mktime($intEndHour,$intEndMinute,$intEndSecond,$intEndMonth,$intEndDay,$intEndYear);
		if (
			isset($intUnixStart)
			&& $intUnixStart
			&& isset($intUnixEnd)
			&& $intUnixEnd
			&& $intUnixStart > $intUnixEnd
		) {
			//return "The end date cannot be sooner than the start date.";
			return array(
				"start_date" => "",
				"end_date" => "",
				"start_time" => "",
				"end_time" => "",
				"error" => "The end date cannot be sooner than the start date."
			);
		}

		/*
		//check to see if the date is not before today's date
		if (
			$intUnixEnd < time()
		) {
			return array(
				"start_date" => "",
				"end_date" => "",
				"start_time" => "",
				"end_time" => "",
				"error" => "This date already past today's date."
			);
		}
		*/

		$arrResult = array();
		if ($strStartDate)
			$arrResult["start_date"] = $strStartDate;
		if ($strEndDate)
			$arrResult["end_date"] = $strEndDate;
		if ($strStartTime)
			$arrResult["start_time"] = $strStartTime;
		if ($strEndTime)
			$arrResult["end_time"] = $strEndTime;

		return $arrResult;
	}

	public function convertDateToUS($date="0000-00-00")
	{
		//TODO
		//handles date conversion from yyyy-mm-dd to mm/dd/yyyy
		$buffer = explode("-", $date);
		if (count($buffer) == 3)
			return($buffer[1] . "/" . $buffer[2] . "/" . $buffer[0]);
	}

	public function convertDateToDatabaseFormat($date="00/00/0000")
	{
		// formats date to be inserted into the database.
		// Input: mm/dd/yyyy
		// Output: yyyy-mm-dd
		$buffer = explode("/", $date);

		//echo "asdasdaS" . $date; exit;
		//echo $buffer[2] . "/" . $buffer[0] . "/" . $buffer[1]; exit;
		if (count($buffer) == 3)
			return($buffer[2] . "/" . $buffer[0] . "/" . $buffer[1]);

	}

	public function get_new_admins()
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM mashpia_old.admins ";
		$sql = $sql . "WHERE first <> '' ";
		$sql . $sql . "AND last <> '' ";
		$sql . $sql . "been_added=0 ";
		$sql = $sql . "ORDER BY admin_id";
		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;
	}

	public function get_old_admins()
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM mashpia_old.admins ";
		$sql = $sql . "WHERE first <> '' ";
		$sql . $sql . "AND last <> '' ";
		$sql = $sql . "ORDER BY admin_id";
		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;
	}

	public function get_old_admins_auths($intAdminId)
	{
		$sql = "
			SELECT
				aa.id,
				aa.auth,
				r.role_name
			FROM
				mashpia_old.admin_auths
					AS aa JOIN mashpia_old.roles
					AS r ON (aa.role_id=r.role_id)
			WHERE aa.admin_id=" . $intAdminId;
		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;
	}

	public function insert_new_admin($old_admin)
	{
		if ($old_admin->admin_email == "")
			$email = $old_admin->first . "@" . $old_admin->last . ".com";
		else
			$email = $old_admin->admin_email;

		$strSql = "SELECT email FROM users WHERE email='" . $email . "'";
		$objResult = $this->_db->fetchRow($strSql);

		if ($objResult->email == "")
		{
			$arrFields = array (
				   "old_user_id" => $old_admin->admin_id,
				   "email"	 => $email,
				   "password"	 => $old_admin->password,
				   "first_name"	 => $old_admin->first,
				   "last_name"	 => $old_admin->last,
				   "is_active"	 => 1,
				   "address"	 => $old_admin->admin_address1,
				   "city"	 => $old_admin->admin_city,
				   "state"	 => $old_admin->admin_state,
				   "country"	 => $old_admin->admin_country,
				   "postal"	 => $old_admin->admin_postal,
				   "phone"	 => $old_admin->admin_phone_work,
				   "created"     => date("Y-m-d H:i:S"),
				   "created_by"  => 1);

			//var_dump($arrFields);

			$boolResult = $this->_db->insert("users", $arrFields);

			if ($boolResult) {
				$intResult = $this->_db->lastInsertId();
				return $intResult;
			}

		}

	}

	public function insert_permission($intUserId, $intInstitutionId, $permission)
	{
		$arrFields = array (
			   "user_id"		=> $intUserId,
			   "institution_id"	=> $intInstitutionId,
			   "permission"		=> $permission,
			   "created"    	=> date("Y-m-d H:i:S"),
			   "created_by" 	=> 1);

		//echo "<br />";
		//var_dump($arrFields);

		$boolResult = $this->_db->insert("permissions", $arrFields);

		//if ($boolResult) {
		//	$intResult = $this->_db->lastInsertId();
		//	return $intResult;
		//}
	}

	//** Get all the legacy users that have not been moved over to the new system ** //
	public function get_new_users()
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM mashpia_old.users ";
		$sql = $sql . "WHERE first <> '' ";
		$sql = $sql . "AND last <> '' ";
		$sql = $sql . "AND school_id > 0 ";
		$sql = $sql . "AND been_added=0 ";
		$sql = $sql . "ORDER BY user_id";

		$arrResult = $this->_db->fetchAll($sql);

		return $arrResult;
	}

	public function insert_new_user($new_user)
	{

		if ($new_user->user_registered > 0)
			$is_active = 1;
		else
			$is_active = 0;

		$email = $new_user->first . $new_user->last;

		$email_found = true;
		$counter = 0;
		do {
			if ($counter > 0)
				$new_email = str_replace("'", "\'", $email) . $counter;
			else
				$new_email = str_replace("'", "\'", $email);

			$strSql = "SELECT count(*) AS no_of_emails FROM users WHERE email='" . $new_email . "'";
			$objResult = $this->_db->fetchRow($strSql);

			if ($objResult->no_of_emails == 0)
				$email_found = false;

			$counter++;
		} while ($email_found == true && $counter < 5);

		$arrFields = array ("old_user_id" 	=> $new_user->user_id,
							"email" 		=> $new_email,
							"password"	 	=> MD5($new_user->password),
							"first_name"	=> $new_user->first,
							"last_name"	 	=> $new_user->last,
							"is_active"	 	=> $is_active,
							"address"	 	=> $new_user->user_address1,
							"city"	     	=> $new_user->user_city,
							"state"	     	=> $new_user->user_state,
							"country"	 	=> $new_user->user_country,
							"postal"	    => $new_user->user_postal,
							"phone"	     	=> $new_user->user_phone,
							"created"     	=> date("Y-m-d H:i:S"));

		$boolResult = $this->_db->insert("mashpia_production.users", $arrFields);

		if ($boolResult) {
			$intResult = $this->_db->lastInsertId();

			$arrFields = array ("been_added" => 1);
			$strWhere = "user_id=" . $new_user->user_id;
			$this->_db->update("mashpia_old.users", $arrFields, $strWhere);

			return $intResult;
		}

	}

	public function insert_student_permission($new_user_id, $new_user)
	{
		$arrFields = array ("user_id" 				=> $new_user_id,
							"institution_id"		=> $new_user->school_id,
							"permission"			=> "Student",
							"default_permission"	=> 1,
							"created"     			=> date("Y-m-d H:i:S"));

		$boolResult = $this->_db->insert("permissions", $arrFields);

		if ($boolResult) {
			$intResult = $this->_db->lastInsertId();
			return $intResult;
		}
	}

	//** Get all the legacy users that have not been moved over to the new system ** //
	public function get_old_teachers()
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM mashpia_old.admin_auths AS aa ";
		$sql = $sql . "JOIN mashpia_old.admins AS a USING (admin_id) ";
		$sql = $sql . "JOIN mashpia_old.roles AS r USING (role_id) ";
		$sql = $sql . "WHERE aa.auth='class' AND r.role_id=13";

		$arrResult = $this->_db->fetchAll($sql);

		return $arrResult;
	}

	public function get_new_user_id($intOldUserId)
	{
		$strSql = "SELECT * FROM users WHERE old_user_id=" . $intOldUserId;
		$objUser = $this->_db->fetchRow($strSql);
		if ($objUser)
			return $objUser->user_id;
		else
			return 0;
	}

	public function insert_teacher_class($class_id, $user_id)
	{
		$arrFields = array ("class_id"		=> $class_id,
							"user_id"		=> $user_id,
							"class_role"	=> "Teacher",
							"created"     	=> date("Y-m-d H:i:S"));

		$boolResult = $this->_db->insert("mashpia_production.user_classes", $arrFields);

		if ($boolResult) {
			$intResult = $this->_db->lastInsertId();
			return $intResult;
		}

	}

	public function get_new_students()
	{
		// ** Get all the students that have not been added already ** //
		$strSql = "SELECT * FROM mashpia_old.users WHERE been_added=0";
		$arrNewStudents = $this->_db->fetchAll($strSql);
		echo "# OF NEW STUDENTS:" . count($arrNewStudents) . "<br />";
		return $arrNewStudents;

	}

	public function add_new_students($objNewStudent)
	{
		$strSql = "SELECT count(*) AS no_of_users FROM mashpia_production.users WHERE old_user_id=" . $objNewStudent->user_id;
		$objUser = $this->_db->fetchRow($strSql);

		if ($objUser->no_of_users == 0)
		{

			$email = $this->get_email($objNewStudent);

			if ($objNewStudent->user_registered > 0)
				$intIsActive = 1;
			else
				$intIsActive = 0;

			$arrFields = array ("old_user_id"		=> $objNewStudent->user_id,
								"email"				=> $email,
								"password"			=> MD5($objNewStudent->password),
								"first_name"		=> $objNewStudent->first,
								"last_name"			=> $objNewStudent->last,
								"hebrew_first_name"	=> $objNewStudent->first_he,
								"hebrew_last_name"	=> $objNewStudent->last_he,
								"is_active"			=> $intIsActive,
								"address"			=> $objNewStudent->user_address1,
								"city"				=> $objNewStudent->user_city,
								"state"				=> $objNewStudent->user_state,
								"country"			=> $objNewStudent->user_country,
								"postal"			=> $objNewStudent->user_postal,
								"phone"				=> $objNewStudent->user_phone);


			$boolResult = $this->_db->insert("mashpia_production.users", $arrFields);

			if ($boolResult)
			{
				if ($objNewStudent->school_id > 0)
				{
					$intNewUserId = $this->_db->lastInsertId();
					$this->insert_student_permission($intNewUserId, $objNewStudent);
				}

				$arrFields = array("been_added" => 1);
				$strWhere = "user_id=" . $objNewStudent->user_id;
				$this->_db->update("mashpia_old.users", $arrFields, $strWhere);
			}

		}
		else
		{
			$arrFields = array("been_added" => 1);
			$strWhere = "user_id=" . $objNewStudent->user_id;
			$this->_db->update("mashpia_old.users", $arrFields, $strWhere);
		}
	}

	public function get_email($objNewStudent)
	{
		$email = $objNewStudent->first . $objNewStudent->last;
		$new_email = "";

		$email_found = true;
		$counter = 0;
		do {
			if ($counter > 0)
				$new_email = str_replace("'", "\'", $email) . $counter;
			else
				$new_email = str_replace("'", "\'", $email);

			$strSql = "SELECT count(*) AS no_of_emails FROM users WHERE email='" . $new_email . "'";
			$objResult = $this->_db->fetchRow($strSql);

			if ($objResult->no_of_emails == 0)
				$email_found = false;

			$counter++;
		} while ($email_found == true && $counter < 5);

		return $new_email;
	}

	public function get_parents()
	{
		$strSql = "SELECT * FROM permissions p WHERE permission='Parent'";
		$arrParents = $this->_db->fetchAll($strSql);

		foreach ($arrParents as $objParent) {
			$arrFields = array ("user_id"		=> $objParent->user_id,
								"relation_id"	=> $objParent->institution_id,
								"relationship"	=> 'parent');
			$boolResult = $this->_db->insert("mashpia_production.relationships", $arrFields);
		}

	}

	public function move_user_ranks()
	{
		$sql = "SELECT * FROM rank_marks";
		$rank_marks = $this->_db->fetchAll($sql);

		//find a matching user_id in the legacy_lookup table, since user_id have been change during the import, and they do not correspond to the correct user_ids
		// that we have now. So we need to find the right user_id in the legacy_lookup table.
		$counter=0;
		$objUserId = new Import();

		foreach ($rank_marks as $objRanks)
		{
			// select from legacy_lookup table, look for the real user_id, by providing the old user id
			$result = $objUserId->lookupId($objRanks->user_id, 'users','users');
			$user_id = $result;
			//print "user_id: " .$user_id ."<br/>"; //exit;
			if($user_id!=0)
			{
				$counter++;
					$date = jdtogregorian($objRanks->date_promoted);
					$pieces = explode("/", $date);
					$month = $pieces[0];
					if (strlen($month) == 1)
						$month = "0" . $month;
					$day = $pieces[1];
					$year = $pieces[2];
					$timestamp = $year . "-" . $month . "-" . $day . " 00:00:00";

				$arrFields = array ("user_id"				=> $user_id,
									"rank_id"				=> $objRanks->rank_ord,
									"date_promoted"			=> $timestamp,
									"date_printed"			=> $objRanks->date_printed,
									"date_book_received"	=> $objRanks->date_book_received,
									"date_card_received"	=> $objRanks->date_card_received,
									"created"     			=> date("Y-m-d H:i:S"));
				//var_dump($arrFields); //exit;
				$boolResult = $this->_db->insert("user_ranks", $arrFields);
			}
		}
		echo "DONE<br />" . $counter;
	}

	public function insert_user_photo($photo_type, $photo)
	{
        $arrFields = array("photo"         	=> file_get_contents($photo['tmp_name']),
							"photo_type"	=> $photo_type);

		$this->_db->insert("images", $arrFields);
	}

	/**
	 * Inserts data into specified table
	 *
	 * @param str $table
	 * @param arr arrInsert
	 *
	 * @return int last_insert
	 *
	 */
	public function insert_db($table, $arrInsert)
	{
		//print_r($arrInsert); exit;

		try{
			$result = $this->_db->insert($table, $arrInsert);
			$last_insert = $this->_db->lastInsertId();
		}catch(Zend_exception $e){
			echo "There was an error: MU-ID101-KJDHY6";
			if(DEV_ENV=="devel") {
				echo " TABLE: " . $table;
				print_r($arrInsert);
				echo $e->getMessage();
				echo $this->_db->insert->__toString();
			}
		}

		return $last_insert;
	}

	/**
	 * Selects records from table
	 *
	 * @param str $table
	 * @para, str $where
	 *
	 * @return int num_of_records
	 *
	 */
	public function select_db($table, $where)
	{
		$strSql ='
		SELECT * FROM '.$table.'
		WHERE '.$where;

		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_exception $e){
			echo "There was an error: MU-ID101-SDF56K";
			if(DEV_ENV=="devel") {
				echo $strSql;
				echo $e->getMessage();
			}
		}
		$num_of_recs = count($arrResult);

		return $num_of_recs;
	}

	/**
	 * Updates records in table
	 *
	 * @param str $table
	 * @param array arrUpdate
	 * @para, str $where
	 *
	 * @return NULL
	 *
	 */
	public function update_db($table, $arrUpdate, $where)
	{
		try{
			$result = $this->_db->update($table, $arrUpdate, $where);
		}catch(Zend_Exception $e){
			echo "There was an error: MU-UD101-JHSGT5";
			if(DEV_ENV=='devel'){
				echo $e->getMessage();
				echo "<br>";
				echo $where;
				echo "<br>";
				print_r($arrUpdate);
				echo "<br>";
			}
		}
	}

	/**
	 * Executes query
	 *
	 * @param str $sql
	 *
	 * @return $result
	 *
	 */
	public function query_db($sql)
	{
		$result = $this->_db->query($sql);
		return $result;
	}

	/**
	 * Description: takes institution_id as argument, examines if it is a host,
	 * network or institution id and returns child institutions accordingly.
	 *
	 * @param int institution_id
	 *
	 * @return arrresult
	 *
	 */
	public function getChildInstitutions($institution_id)
	{
		$sql = '
		SELECT * FROM institutions
		WHERE institution_id = '.$institution_id;

		try{
			$rs = $this->_db->fetchRow($sql);
		}catch(Zend_exception $e){
			echo "There was an error: MU-GCI101-AHGSF5";
			if(DEV_ENV=="devel") {
				echo $sql;
				echo $e->getMessage();
			}
		}

		switch($rs->institution_type){
			case "Host":
				$sql = '
					SELECT b.institution_id, b.name, b.network_id, b.host_id FROM institutions AS a
					INNER JOIN institutions AS b
					ON a.institution_id = b.host_id
					WHERE a.institution_id = '.$institution_id;
				break;
			case "Network":
				$sql = '
					SELECT b.institution_id, b.name, b.network_id, b.host_id FROM institutions AS a
					INNER JOIN institutions AS b
					ON a.institution_id = b.network_id
					WHERE a.institution_id = '.$institution_id;
				break;
			default:
				$arrResult[] = '';
		}

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GCBN-KLIO87";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}

		foreach($result as $r){
			$buffer[] = $r->institution_id;
		}

		$buffer[] = $institution_id; //make sure we return the input institution_id as well

		$result = join(",", $buffer);
		//echo ($result); exit;
		return $result;

	}
	public function institution_reverse_lookup($institution_id)
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				institution_id=". $institution_id;

		try{
			$objResult = $this->_db->fetchRow($strSql);
		}catch(Zend_exception $e){
			echo "There was an error: MU-GCI101-AHGSF5";
			if(DEV_ENV=="devel") {
				echo $strSql;
				echo $e->getMessage();
			}
		}
		if($objResult->institution_type !="Host" && $objResult->institution_type !="Network")
		{
			$strSql1 = "SELECT host_id, network_id FROM institutions where institution_id=". $objResult->institution_id;
			try{
			$objResult1 = $this->_db->fetchRow($strSql1);
			}catch(Zend_exception $e){
				echo "There was an error: MU-IRL101-453SF5";
				if(DEV_ENV=="devel") {
					echo $strSql1;
					echo $e->getMessage();
				}
			}
			$buffer[] = $objResult1->host_id;
			$buffer[] = $objResult1->network_id;
			$buffer[] = $institution_id; //make sure we return the input institution_id as well

			$result = join(",", $buffer);
			//echo ($result); exit;
			return $result;
		}
	}

	public function generateSerial()
	{
		$floor		= 1000000000;
		$ceiling	= 9999999999;
		$chunks		= 1;  //format: 111-222-333
		$serial 		= "";

		// seed with microseconds
		for($i=0; $i<$chunks; $i++){
			$serial .= "-";
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * 100000);

			//seed the random number generator
			srand($seed);
			$serial .= rand($floor, $ceiling);
		}
		return substr($serial, 1);

	}

	/**
	 *
	 * Generates a 20 digit barcode that is used for students' cards and login
	 *
	 * @param int $numOfDigits => the number of digits to generate
	 * @return int $number => the generated number
	 *
	 */
	public function generateStudentBarcode($numOfDigits=20)
	{

		// seed with microseconds
		for($i=0; $i<$numOfDigits; $i++){
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * 100000);

			//seed the random number generator
			srand($seed);
			$serial = rand($floor, $ceiling);
			$number .= $serial;
		}

		return $number;

	}
}

?>
