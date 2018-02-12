<?PHP
class Tanya {
	protected $VERBOSE;
	public $objDBIHandle;
	public $objUserHandle;
	protected $arrParshos;

	public function __construct($VERBOSE = false) {
		$this->VERBOSE = $VERBOSE;

		// Check if the object already exists before opening a new one
		global $objDBIHandle;
		if (is_object($objDBIHandle)) {
			$this->objDBIHandle = $objDBIHandle;
		} else {
			$this->objDBIHandle = new DBI($this->VERBOSE);
		}
		if ($this->VERBOSE)
			print "Tanya activated<br>\n";
	}

	/* Function loadUser
	 * @Params: user_id
	 * @Return: Object handle to TanyaUser with user info
	 * Loads the user details of a child from the database
	 */
	public function loadUser() {
		global $user_row;
		// Load the user details from the database into TanyaUser object and return it
		$this->objDBIHandle->open();
		$strSql = "SELECT * FROM " . tanya_user_table . " WHERE `id`=" . $user_row["user_id"];
		$objResult = mysql_query ($strSql, $this->objDBIHandle->objHandle) or die(mysql_error());
		if ($objResult && mysql_num_rows($objResult)) {
			if ($this->VERBOSE)
				print "User `" . $user_row["user_id"] . "` has been loaded.<br>\n";
		}
		// Create a new tanya user handler
		$this->objUserHandle = new TanyaUser($this->VERBOSE);
		$objRow = mysql_fetch_assoc($objResult);
		// Migrate the columns to the object
		$this->objUserHandle->intTableID = $user_row["user_id"];
		$this->objUserHandle->strFirstName = $user_row["first_he"] ? $user_row["first_he"] : $user_row["first"];
		$this->objUserHandle->strLastName = $user_row["last_he"] ? $user_row["last_he"] : $user_row["last"];
		$this->objUserHandle->intLinesBeforeEnrollment = $objRow["lines_before_enrollment"];
		$this->objUserHandle->intLinesAfterEnrollment = $objRow["lines_after_enrollment"] ? $objRow["lines_after_enrollment"] : 0;
		$this->objUserHandle->strDesiredChapterGoal = $objRow["desired_chapter_goal"];
		$this->objUserHandle->intLadder = $objRow["ladder"];
		$this->objUserHandle->intEnrolled = $objRow["enrolled"];
		$this->objUserHandle->intEnrolledDate = $objRow["enrolled_date"];
		list($intYear, $intMonth, $intDay) = split("-", $user_row["dob"]);
		$this->objUserHandle->intBirthDate = mktime(0,0,0,$intMonth,$intDay,$intYear);
		$this->objUserHandle->intDateCreated = $objRow["date_created"];
		$this->objDBIHandle->close();
		if ($this->VERBOSE)
			print "Child loaded<br>\n";
		return $this->objUserHandle;
	}

	/* Function insertUser
	 * @Params: TanyaUser_object_handle
	 * @Return: Forwards to location of base path
	 * Saves a user to the database using TanyaUser object
	 */
	public function insertUser(&$objHandle=0) {
		if (!isset($objHandle) || !$objHandle) // If no object is provided, check if one has been provided within the scope
			$objHandle=$this->objUserHandle;
		if (!is_object($objHandle)) {
			die("Fatal error; Invalid insertion operation.");
		}
		$this->objDBIHandle->open();
		// Insert the TanyaUser data to the users table
		$intDateToday = mktime(0,0,0,date("m"),date("d"),date("y"));
		$intEnrolledDate = $intDateToday+((7-date("N"))*86400);
		$strSql = "
			INSERT INTO " . tanya_user_table . " (
				`id`,
				`lines_before_enrollment`,
				`lines_after_enrollment`,
				`desired_chapter_goal`,
				`ladder`,
				`enrolled`,
				`enrolled_date`,
				`date_created`
			) VALUES (
				'" . $objHandle->intTableID . "',
				'" . mysql_real_escape_string($objHandle->intLinesBeforeEnrollment, $this->objDBIHandle->objHandle) . "',
				'" . mysql_real_escape_string($objHandle->intLinesAfterEnrollment, $this->objDBIHandle->objHandle) . "',
				'" . mysql_real_escape_string($objHandle->strDesiredChapterGoal, $this->objDBIHandle->objHandle) . "',
				'" . mysql_real_escape_string($objHandle->intLadder, $this->objDBIHandle->objHandle) . "',
				1,
				" . $intEnrolledDate . ",
				UNIX_TIMESTAMP()
			);
		";
		if ($this->VERBOSE)
			print $strSql;
		if ($this->VERBOSE)
			print $strSql . "\n<br>";
		if ($this->objDBIHandle->query($strSql))
			$objHandle->intTableID = mysql_insert_id($this->objDBIHandle->objHandle);
		else
			dir("There was an error inserting this user.<br>\n");
		$this->objDBIHandle->close();
		if ($this->VERBOSE)
			print "<a href='./demo.php'>./demo.php</a><br>\n";
		return 1;
	}

	/* Function updateUser
	 * @Params: TanyaUser_object_handle
	 * @Return: Forwards to location of base path
	 * Saves a user to the database using TanyaUser object
	 */
	public function updateUser(&$objHandle=0) {
		if (!isset($objHandle) || !$objHandle) // If no object is provided, check if one has been provided within the scope
			$objHandle=$this->objUserHandle;
		if (
			!is_object($objHandle)
			|| !$objHandle->intTableID
			|| !is_numeric($objHandle->intTableID)
		) {
			var_dump($objHandle);
			die("Fatal error; Invalid insertion operation.");
		}
		$this->objDBIHandle->open();
		$strSql = "
			UPDATE
				" . tanya_user_table . "
			SET
				`first_name` = '" . mysql_real_escape_string($objHandle->strFirstName, $this->objDBIHandle->objHandle) . "',
				`last_name` = '" . mysql_real_escape_string($objHandle->strLastName, $this->objDBIHandle->objHandle) . "',
				`lines_before_enrollment` = '" . mysql_real_escape_string($objHandle->intLinesBeforeEnrollment, $this->objDBIHandle->objHandle) . "',
				`lines_after_enrollment` = '" . mysql_real_escape_string($objHandle->intLinesAfterEnrollment, $this->objDBIHandle->objHandle) . "',
				`desired_chapter_goal` = '" . mysql_real_escape_string($objHandle->strDesiredChapterGoal, $this->objDBIHandle->objHandle) . "',
				`ladder` = '" . mysql_real_escape_string($objHandle->intLadder, $this->objDBIHandle->objHandle) . "',
				`enrolled` = '" . mysql_real_escape_string($objHandle->intEnrolled, $this->objDBIHandle->objHandle) . "',
				`enrolled_date` = '" . mysql_real_escape_string($objHandle->intEnrolledDate, $this->objDBIHandle->objHandle) . "',
				`birth_date` = '" . mysql_real_escape_string($objHandle->intBirthDate, $this->objDBIHandle->objHandle) . "'
			WHERE
				`id`=" . $objHandle->intTableID;
		if ($this->objDBIHandle->query($strSql)) {
			$this->objDBIHandle->close();
			return 1;
		}
		$this->objDBIHandle->close();
	}
	
	public function procTasksPerMission($intLines)
	{
		return round($intLines / 416, 2);
	}
	
	public function procEnrollDate()
	{
		$intDateToday = mktime(0,0,0,date("m"),date("d"),date("y"));
		$intEnrolledDate = $intDateToday+((7-date("N"))*86400);
		return $intEnrolledDate;
	}
	
	public function setEnrollDate($intDate=0)
	{
		if (!$intDate || !preg_match("/^[0-9]+$/", $intDate))
		{
			print "Sorry, there was an error: TSCT-SED101-SDF897";
			exit;
		}
		$this->objDBIHandle->open();
		$this->objUserHandle->intEnrolledDate = $intDate;
		$strSql = "
			UPDATE
				" . tanya_user_table . "
			SET
				enrolled_date=" . $intDate . "
			WHERE
				id=" . $this->objUserHandle->intTableID;
		if ($this->objDBIHandle->query($strSql)) {
			$this->objDBIHandle->close();
			return 1;
		}
		$this->objDBIHandle->close();
	}
	
	public function setLadder($intLadder=0)
	{
		if (!$intLadder || !preg_match("/^[0-9]+$/", $intLadder))
		{
			print "Sorry, there was an error: TSCT-SL101-9S78DF";
			exit;
		}
		$this->objUserHandle->intLadder = $intLadder;
		$this->objDBIHandle->open();
		$strSql = "
			UPDATE
				" . tanya_user_table . "
			SET
				ladder=" . $intLadder . "
			WHERE
				id=" . $this->objUserHandle->intTableID;
		if ($this->objDBIHandle->query($strSql)) {
			$this->objDBIHandle->close();
			return 1;
		}
		$this->objDBIHandle->close();
	}

	public function setLinesBefore($intLines=0)
	{
		if (!preg_match("/^[0-9]+$/", $intLines))
		{
			print "Sorry, there was an error: TSCT-SLB101-9S78DF";
			exit;
		}
		$this->objUserHandle->intLinesBeforeEnrollment = $intLines;
		$this->objDBIHandle->open();
		$strSql = "
			UPDATE
				" . tanya_user_table . "
			SET
				lines_before_enrollment=" . $intLines . "
			WHERE
				id=" . $this->objUserHandle->intTableID;
		//print $strSql;
		if ($this->objDBIHandle->query($strSql)) {
			$this->objDBIHandle->close();
			return 1;
		}
		$this->objDBIHandle->close();
	}

	public function setLinesAfter($intLines=0)
	{
		if (!preg_match("/^[0-9]+$/", $intLines))
		{
			print "Sorry, there was an error: TSCT-SLA101-9S78DF";
			exit;
		}
		$this->objUserHandle->intLinesAfterEnrollment = $intLines;
		$this->objDBIHandle->open();
		$strSql = "
			UPDATE
				" . tanya_user_table . "
			SET
				lines_after_enrollment = " . $intLines . "
			WHERE
				id=" . $this->objUserHandle->intTableID;
		//print $strSql;
		if ($this->objDBIHandle->query($strSql)) {
			$this->objDBIHandle->close();
			return 1;
		}
		$this->objDBIHandle->close();
	}
	
	public function deleteAllMissions()
	{
		$this->objDBIHandle->open();
		$strSql = "
			DELETE
				FROM " . tanya_missions_table . "
			WHERE
				user_id = " . $this->objUserHandle->intTableID;
		if ($this->objDBIHandle->query($strSql)) {
			$this->objDBIHandle->close();
			return 1;
		}
		$this->objDBIHandle->close();
	}
	
	public function epochToParshos($intTimestamp)
	{
		$DEBUG = 0;
		$intTimestamp = round(unixtojd($intTimestamp));
		if (!preg_match("/^[0-9]+$/", $intTimestamp))
		{
			print "Sorry, there was an error: TSCT-ETP101-234IHI";
			exit;
		}
		if (!$this->arrParshos)
		{
			$strSql = "
				SELECT
					*
				FROM
					mashpia.parshos";
			$this->objDBIHandle->open();
			$objResult = $this->objDBIHandle->query($strSql);
			$this->arrParshos = array();
			while ($arrRow = mysql_fetch_assoc($objResult))
			{
				array_push($this->arrParshos, $arrRow);
			}
			$this->objDBIHandle->close();
		}
		if (
			!isset($this->arrParshos)
			|| !is_array($this->arrParshos)
		) {
			print "Sorry, there was an error: TSCT-ETP102-67DSFD5";
			exit;
		}
		if ($DEBUG)
			print "Time to match: " . $intTimestamp . " <br>\n";
		foreach ($this->arrParshos as $arrRow)
		{
			if ($DEBUG)
				print "(
					{$arrRow["start_date"]} <= $intTimestamp <br>
					&& {$arrRow["end_date"]} >= $intTimestamp <br>
				)";
			if (
				$arrRow["start_date"] <= $intTimestamp
				&& $arrRow["end_date"] >= $intTimestamp
			) {
				return $arrRow["parsha"];
			}
		}
		print "Sorry, there was an error: TSCT-ETP103-2323ED";
		exit;
	}
}

class TanyaUser {
	// This class must be used to create and interface with user details
	protected $VERBOSE;
	public $objDBIHandle;

	public $intTableID;
	public $strFirstName;
	public $strLastName;
	public $intLinesBeforeEnrollment = 0;
	public $intLinesAfterEnrollment = 0;
	public $strDesiredChapterGoal = "";
	public $intLadder = 0;
	public $intEnrolled = 0;
	public $intEnrolledDate;
	public $intBirthDate;
	public $intDateCreated;
	public $intLadderEstimation;

	public function __construct($VERBOSE = false) {
		$this->VERBOSE = $VERBOSE;
		if ($this->VERBOSE)
			print "New user instance created<br>\n";
		global $objDBIHandle;
		if (is_object($objDBIHandle)) {
			$this->objDBIHandle = $objDBIHandle;
		} else {
			$this->objDBIHandle = new DBI(1);
		}
	}

	public function toHash() {
		return array (
			"strFirstName" => $this->strFirstName,
			"strLastName" => $this->strLastName,
			"intLinesBeforeEnrollment" => $this->intLinesBeforeEnrollment,
			"intLinesAfterEnrollment" => $this->intLinesAfterEnrollment,
			"strDesiredChapterGoal" => $this->strDesiredChapterGoal,
			"intLadder" => $this->intLadder,
			"intEnrolled" => $this->intEnrolled,
			"intEnrolledDate" => $this->intEnrolledDate,
			"intBirthDate" => $this->intBirthDate,
			"intDateCreated" => $this->intDateCreated
		);
	}

	public function setFirstName($strFirstName) {
		$this->strFirstName = $strFirstName;
		return 1;
	}

	public function setLastName($strLastName) {
		$this->strLastName = $strLastName;
		return 1;
	}

	public function setLinesBeforeEnrollment($intLinesBeforeEnrollment) {
		$this->intLinesBeforeEnrollment = $intLinesBeforeEnrollment;
		return 1;
	}

	public function procRemainingDays() {
		if (
			!isset($this->intBirthDate)
			|| !is_numeric($this->intBirthDate)
		) {
			die("Cannot run know the remaining years unless a user is loaded with a valid birthdate.<br>\n");
		}
		$intSecondsAlive = time() - $this->intBirthDate;
		$intSecondsRemaining = 13 * 365.25 * 86400 - $intSecondsAlive;
		$intDaysRemaining = $intSecondsRemaining / 86400;
		return $intDaysRemaining;
	}

	/* Function setChapterGoal
	 * @Params: intDesiredChapterGoal
	 * @Return: Object handle to TanyaUser with user info
	 * Loads the user details of a child from the database
	 */
	public function setChapterGoal($strDesiredChapterGoal, $boolFromIndex=0) {
		if ($boolFromIndex == 1) { // Selection from chapter list
			$this->objDBIHandle->open();
			$strSql = "
				SELECT
					`line`
				FROM
					" . tanya_chapters_table;
			$objResult = $this->objDBIHandle->query($strSql);
			if ($this->VERBOSE)
				print "strDesiredChapterGoal: $strDesiredChapterGoal<br>\n";
			if ($objResult) {
				$strDesiredChapterGoal = mysql_result($objResult, $strDesiredChapterGoal-1) - $this->intLinesBeforeEnrollment - $this->intLinesAfterEnrollment;
			}
			if ($this->VERBOSE)
				print "strDesiredChapterGoal: $strDesiredChapterGoal<br>\n";
			$this->objDBIHandle->close();
		} else if ($boolFromIndex == 2) { // Selection form ladder list
			$this->objDBIHandle->open();
			$strSql = "
				SELECT
					`year`
				FROM
					" . tanya_goals_table;
			$objResult = $this->objDBIHandle->query($strSql);
			if ($objResult) {
				$strDesiredChapterGoal1 = mysql_result($objResult, $strDesiredChapterGoal);
				$intCurrentLine = $this->objUserHandle->intLinesBeforeEnrollment + $this->objUserHandle->intLinesAfterEnrollment;
				$intRemainingWeeks = ceil($this->procRemainingDays()/7);
				$strDesiredChapterGoal = round($intCurrentLine + (($strDesiredChapterGoal1 / 416) * $intRemainingWeeks));
			}
			$this->objDBIHandle->close();
		}
		$this->strDesiredChapterGoal = $strDesiredChapterGoal;
		return 1;
	}

	/* Function setLadderRequest
	 * @Params: $_POST["line_goal"], $_POST["ladder"]
	 * @Return: BOOL
	 * Set a ladder requests as pending
	 */
	public function setLadderRequest($intLineGoal, $intLadder) {
		//print "intLadder: $intLadder<br>\nintLineGoal: $intLineGoal<br>\nintTableID: " . $this->intTableID . "<br>\n";
		if (
			isset($intLadder)
			&& is_numeric($intLadder)
			&& isset($intLineGoal)
			&& is_numeric($intLineGoal)
			&& isset($this->intTableID)
			&& is_numeric($this->intTableID)
		) {
			$strSql = "
				INSERT
					INTO " . tanya_requests_table . "
					(user_id, line_goal, to_ladder)
				VALUES
					(" . $this->intTableID . ", " . $intLineGoal . ", " . $intLadder . ")
				ON DUPLICATE KEY UPDATE
					line_goal=VALUES(line_goal),
					to_ladder=VALUES(to_ladder);";
			$this->objDBIHandle->open();
			$this->objDBIHandle->query($strSql);
			$intRequestKey = mysql_insert_id($this->objDBIHandle->objHandle);
			$this->objDBIHandle->close();
			return $intRequestKey;
		}
	}

	public function getEnrolledElapsedDays() {
		if (
			isset($this->intEnrolledDate)
			&& $this->intEnrolledDate
		) {
			return (time()-$this->intEnrolledDate)/86400;
		}
	}

	public function setBirthDate($intBirthDate) {
		if (!is_numeric($intBirthDate))
			$intBirthDate = $this->parseDate($intBirthDate);
		if (is_numeric($intBirthDate)) {
			$this->intBirthDate = $intBirthDate;
			return 1;
		} else {
			return 0;
		}
	}

	public function setLadder($intLadder) {
		if (
			isset($intLadder)
			&& is_numeric($intLadder)
		) {
			$this->intLadder = $intLadder;
		}
	}

	protected function parseDate($strDate) {
		$arrDate = split("/", $strDate);
		if (
			isset($arrDate[0])
			&& is_numeric($arrDate[0])
			&& isset($arrDate[1])
			&& is_numeric($arrDate[1])
			&& isset($arrDate[2])
			&& is_numeric($arrDate[2])
		) {
			return mktime(0,0,0,$arrDate[1],$arrDate[0],$arrDate[2]);
		}
	}

	public function validate() {
		if (
			!isset($this->strFirstName)
			|| !strlen($this->strFirstName)
		) {
			return "Validation failed; What is your first name?";
		}
		if (
			!isset($this->strLastName)
			|| !strlen($this->strLastName)
		) {
			return "Validation failed; What is your last name?";
		}
		if (
			!isset($this->intBirthDate)
			|| !is_numeric($this->intBirthDate)
		) {
			return "Validation failed; No birthdate has been set.";
		}
		return "Validation: Passed";
	}

	public function chapterOptionList() {
		$this->objDBIHandle->open();
		$this->objDBIHandle->query("SET NAMES 'utf8'");
		$strSql = "
			SELECT
				`name`, `line`
			FROM
				" . tanya_chapters_table;
		$objResult = $this->objDBIHandle->query($strSql);
		$strOutput = "";
		while ($objRow = mysql_fetch_assoc($objResult)) {
			$strOutput .= "<option value='{$objRow['line']}'>{$objRow['name']}</option>";
		}
		return $strOutput;
	}

	public function procLadderFromGoal() {
		if (
			!isset($this->strDesiredChapterGoal)
			|| !strlen($this->strDesiredChapterGoal)
		) {
			die("There must be a selected goal to process a ladder from.<br>\n");
		}
		if ($this->VERBOSE)
			print "strDesiredChapterGoal: {$this->strDesiredChapterGoal}<br>\n";
		$strSql = "
			SELECT
				id, year
			FROM
				" . tanya_goals_table;
		$this->objDBIHandle->open();
		$objResult = $this->objDBIHandle->query($strSql);
		$intLadder = 1;
		$intRemainingYears = ceil($this->procRemainingDays()/365.25);
		while ($objRow = mysql_fetch_assoc($objResult)) {
			if ($objRow["year"]/8 >= $this->strDesiredChapterGoal/($this->procRemainingDays()/365.25)) {
				break;
			}
			$intLadder = $objRow["id"];
		}
		$this->objDBIHandle->close();
		$this->intLadderEstimation = $intLadder;
		if ($this->VERBOSE)
			print "intLadderEstimation: {$this->intLadderEstimation}<br>\n";
		return $intLadder;
	}

	public function enrollUser($boolAction) {
		if (
			is_numeric($boolAction)
			&& $this->intTableID
		) {
			$this->objDBIHandle->open();
			$strSql = "UPDATE  " . tanya_user_table . " SET enrolled=" . $boolAction . " WHERE id=" . $this->intTableID;
			if ($this->objDBIHandle->query($strSql)) {
				$this->objDBIHandle->close();
				$this->intEnrolled = $boolAction;
				return 1;
			}
			$this->objDBIHandle->close();
		} else if ($this->VERBOSE)
			die("The arg supplied to enrollUser was invalid.<br>\n");
	}
	
	public function ladderLines($intLadder = 0) {
		if (!$intLadder)
		{
			$intLadder = $this->intLadder;
		}
		if ($intLadder) {
			$strSql = "
				SELECT
					`year`
				FROM
					" . tanya_goals_table . "
				WHERE
					`id`=" . $intLadder;
			$this->objDBIHandle->open();
			$objResult = $this->objDBIHandle->query($strSql);
			if (
				$objResult
				&& mysql_num_rows($objResult)
			) {
				$intLadderLines = mysql_result($objResult, 0);
				$this->objDBIHandle->close();
				return $intLadderLines;
			}
		}
	}
}

class TanyaMissions {
	protected $VERBOSE;
	public $objDBIHandle;
	public $objUserHandle;

	public $intCurrentLine;
	public $intMissionId;
	public $intUserId;
	public $intMissionNumber;
	public $intLadder;
	public $intTested;
	public $intTestedDate;
	public $intReal;
	public $intSum = 0;
	public $intVirtual = 0;
	public $intVirtualSum = 0;
	public $intRemainder = 0;
	public $intDateCreated;

	public function __construct($objUserHandle, $VERBOSE = 0) {
		$this->VERBOSE = $VERBOSE;
		if ($this->VERBOSE)
			print "New mission instance created<br>\n";
		global $objDBIHandle;
		if (is_object($objDBIHandle)) {
			$this->objDBIHandle = $objDBIHandle;
		} else {
			$this->objDBIHandle = new DBI(1);
		}
		$this->objUserHandle = $objUserHandle;
	}

	public function procAvailableMission() {
		$intEnrollmentOffset = $this->objUserHandle->intEnrolledDate-($this->objUserHandle->intBirthDate+5*86400*365.25);
		return ceil(416 - $intEnrollmentOffset / 86400 / 7.019230769230769);
	}

	public function procTotalMedals() {
		global $arrMedalData;
		$intMissionEnd = $this->procAvailableMission();
		$intMission = 1;
		for ($intItr=1;$intItr!=11;$intItr++) {
			if ($arrMedalData[$intItr] <= $intMissionEnd)
				$intMission++;
		}
		return $intMission;
	}

	/* Function mergeMissions
	 * @Params: arrMissionRanges
	 * @Return: 1 if successful
	 * Sets missions as pending or incomplete
	 */
	public function mergeMissions($arrMissionRanges) {
		if (
			!$this->objUserHandle->intTableID
		) {
			if ($this->VERBOSE)
				die("Could not load user id to save tasks<br>\n");
			return 0;
		}
		$arrMissions = array();
		foreach ($arrMissionRanges as $arrRange) {
			$arrMissions[$arrRange["mission_start"]] = $arrRange["line_count"];
		}
		// Check what missions are completed
		$strSql = "
			SELECT
				mission, COUNT(line_number), foreign_mission_id
			FROM
				" . tanya_tasks_table . "
			WHERE
				user_id=" . $this->objUserHandle->intTableID . "
				AND mission IN (" . join(",", array_keys($arrMissions)) . ")
			GROUP BY
				mission
		";
		$this->objDBIHandle->open();
		if ($this->VERBOSE)
			print $strSql . "<br>\n";
		$objResult = $this->objDBIHandle->query($strSql);
		$arrMissions = array();
		$intMissionInsertionFlag = 1; // Only allow new missions to be inserted if the previous mission has been inserted
		while ($objRow = mysql_fetch_row($objResult)) {
			foreach ($arrMissionRanges as $arrRange) {
				if ($objRow[0] == $arrRange["mission_start"]) {
					if ($objRow[1] == $arrRange["line_count"]) {
						if (!$objRow[2]) { // Create if no foreign key is associated
							if ($intMissionInsertionFlag)
								$arrMissions[1][] = $objRow[0];
							else if ($objRow[2] > 0) // Delete any mission if there was a broken link in the chain
								$arrMissions[0][] = $objRow[0];
						}
					} else {
						$intMissionInsertionFlag = 0;
						if ($objRow[2] > 0) { // Delete if a foreign key is associated
							$arrMissions[0][] = $objRow[0];
						}
					}
					break;
				}
			}
		}
		$this->objDBIHandle->free();
		$arrMissionRanges2 = $this->missionEntryRangeStyle2($arrMissionRanges);
		$arrMissionRanges3 = $this->missionEntryRangeStyle3($arrMissionRanges);
		if (isset($arrMissions[1])) { // Insert mission, then associate a foreign key
			$arrSql = array();
			$strSql = "
				INSERT IGNORE INTO
					" . tanya_missions_table . "
					(user_id, mission_number, ladder, `real`, sum, virtual_sum, mission_date, date_created)
				VALUES ";
			foreach ($arrMissions[1] as $intMission) {
				$arrSql = array();
				foreach ($arrMissionRanges3[$intMission] as $intMission => $arrMissionBlock) {
					$arrSql[] = "("
						. $this->objUserHandle->intTableID
						. "," . $intMission
						. "," . $this->objUserHandle->intLadder
						. "," . $arrMissionBlock["real"]
						. "," . $arrMissionBlock["sum"]
						. "," . $arrMissionBlock["virtual_sum"]
						. "," . $arrMissionBlock["mission_dates"][0]
						. ", UNIX_TIMESTAMP())";
				}
				$strSql2 = $strSql . join(",", $arrSql);
				if ($this->VERBOSE)
					print $strSql2 . "<br>\n";
				$this->objDBIHandle->query($strSql2);
				$intId = mysql_insert_id($this->objDBIHandle->objHandle);
				$strSql2 = "
					SELECT
						line_number
					FROM
						" . tanya_tasks_table . "
					WHERE
						user_id=" . $this->objUserHandle->intTableID . "
						AND mission=" . $intMission;
				$arrLines = array();
				$objResult = $this->objDBIHandle->query($strSql2);
				while ($intLine = mysql_fetch_assoc($objResult)) {
					$arrLines[] = $intLine["line_number"];
				}
				$this->objDBIHandle->free();
				// Apply a mission foreign key to the task items
				$strSql2 = "
					UPDATE
						" . tanya_tasks_table . "
					SET
						foreign_mission_id=" . $intId . "
					WHERE
						user_id=" . $this->objUserHandle->intTableID . "
						AND line_number IN (" . join(",", $arrLines) . ")
				";
				//print $strSql2 . "<br>\n";exit;
				if ($this->VERBOSE)
					print $strSql2 . "<br>\n";
				$this->objDBIHandle->query($strSql2);
			}
		}
		if (isset($arrMissions[0])) { // Remove a mission, then disassociate the foreign key
			$strSql = "
				DELETE FROM
					" . tanya_missions_table . "
				WHERE
					user_id = " . $this->objUserHandle->intTableID . "
					AND mission_number IN (" . join(",", $arrMissions[0]) . ")
			";
			if ($this->VERBOSE)
				print $strSql . "<br>\n";
			$this->objDBIHandle->query($strSql);
			$strSql = "
				UPDATE
					" . tanya_tasks_table . "
				SET
					foreign_mission_id=0
				WHERE
					user_id = " . $this->objUserHandle->intTableID . "
					AND mission IN (" . join(",", $arrMissions[0]) . ")";
			if ($this->VERBOSE)
				print $strSql . "<br>\n";
			$this->objDBIHandle->query($strSql);
		}
		$this->objDBIHandle->close();
		return 1;
	}

	function missionEntryRangeStyle2(&$arrMissionRanges) {
		$arrReturn = array();
		foreach ($arrMissionRanges as $arrRange) {
			for ($intItr=$arrRange["mission_start"]; $intItr!=$arrRange["mission_start"]+$arrRange["mission_count"]; $intItr++) {
				$arrReturn[$intItr] = $arrRange;
			}
		}
		return $arrReturn;
	}

	function missionEntryRangeStyle3(&$arrMissionRanges) {
		$arrReturn = array();
		foreach ($arrMissionRanges as $arrRange) {
			for ($intItr=$arrRange["mission_start"]; $intItr!=$arrRange["mission_start"]+$arrRange["mission_count"]; $intItr++) {
				$arrReturn[$arrRange["mission_start"]][$intItr] = $arrRange;
			}
		}
		return $arrReturn;
	}
	
	function missionEntryRangeStyle4(&$arrMissionRanges) {
		$arrReturn = array();
		foreach ($arrMissionRanges as $arrRange) {
			for ($intItr=$arrRange["mission_start"]; $intItr!=$arrRange["mission_start"]+$arrRange["mission_count"]; $intItr++) {
				$arrReturn[$arrRange["mission_start"]][$intItr] = $arrRange;
			}
		}
		var_dump($arrReturn);
		exit;
		return $arrReturn;
	}

	/* Function mergeTasks
	 * @Params: array(
	 *				"0" => array("mission id" => array("line",[,...])),
	 *				"1" => array("mission id" => array("line",[,...])),
	 *			)
	 * @Return: 1 if successful
	 * Save or remove tasks from the database. The parent keys 0 and 1 in the example refer to insert or remove.
	 */
	public function mergeTasks($arrParams) {
		if (
			!$this->objUserHandle->intTableID
		) {
			if ($this->VERBOSE)
				die("Could not load user id to save tasks<br>\n");
			return 0;
		}
		$this->objDBIHandle->open();
		if (isset($arrParams[0])) {
			$arrSql = array();
			foreach ($arrParams[0] as $intMission => $arrLines) {
				foreach ($arrLines as $intLine) {
					$arrSql[] = "(mission=" . $intMission . " AND line_number=" . $intLine . ")";
				}
			}
			$strSql = "
				DELETE IGNORE FROM
					" . tanya_tasks_table . "
				WHERE
					user_id=" . $this->objUserHandle->intTableID . "
					AND " . join(" OR ", $arrSql);
			if ($this->VERBOSE)
				print $strSql . "<br>\n";
			$this->objDBIHandle->query($strSql);
		}
		if (isset($arrParams[1])) {
			//var_dump($arrParams);
			$arrSql = array();
			foreach ($arrParams[1] as $intMission => $arrLines) {
				foreach ($arrLines as $intLine) {
					$arrSql[] = "(" . $this->objUserHandle->intTableID . "," . $intMission . "," . $intLine . ")";
				}
			}
			$strSql = "
				INSERT IGNORE INTO
					" . tanya_tasks_table . "
					(user_id, mission, line_number)
				VALUES
					" . join(",", $arrSql);
			if ($this->VERBOSE)
				print $strSql . "<br>\n";
			$this->objDBIHandle->query($strSql);
		}
		$this->objDBIHandle->close();
		return 1;
	}

	/* Function arrTasks
	 * @Params: array(mission id list);
	 * @Return: 1 if successful
	 * Load the latest mission into state
	 */
	public function arrTasks($arrMissions) {
		$arrMissions2 = $this->missionEntryRangeStyle2($arrMissions);
		if (
			!$this->objUserHandle->intTableID
		) {
			if ($this->VERBOSE)
				die("Could not load user id to save tasks<br>\n");
			return 0;
		}
		$strSql = "
			SELECT
				mission, line_number
			FROM
				" . tanya_tasks_table . "
			WHERE
				user_id=" . $this->objUserHandle->intTableID . "
				AND mission IN (" . join(",", array_keys($arrMissions2)) . ");
		";
		$this->objDBIHandle->open();
		$objResult = $this->objDBIHandle->query($strSql);
		$arrResult = array();
		while ($objRow = mysql_fetch_assoc($objResult)) {
			$arrResult[$objRow["mission"]][$objRow["line_number"]] = 1;
		}
		$this->objDBIHandle->close();
		return $arrResult;
	}

	/* Function procLadderList
	 * @Params: NULL
	 * @Return: An associative array containing the ladders with 8 year goal
	 * Retrieve the table of ladders
	 */
	public function procLadderList() {
		$strSql = "
			SELECT
				*
			FROM
				" . tanya_goals_table;
		$intCurrentLine = $this->objUserHandle->intLinesBeforeEnrollment + $this->objUserHandle->intLinesAfterEnrollment;
		$intRemainingWeeks = ceil($this->procRemainingDays()/real_week);
		$arrLines = array();
		$this->objDBIHandle->open();
		$objResult = $this->objDBIHandle->query($strSql);
		$arrAdjustedLines = array();
		while ($objRow = mysql_fetch_assoc($objResult)) {
			$arrLines[$objRow["year"]] = $objRow["id"];
			$arrAdjustedLines[] = round($intCurrentLine + ($objRow["year"] / 416 * $intRemainingWeeks));
		}
		//var_dump($arrLines);
		//var_dump($arrAdjustedLines);
		$arrLineKeys = array_keys($arrLines);
		$strSql = "
			SELECT
				*
			FROM
				" . tanya_lines_table . "
			WHERE
				Line IN (" . join(",", $arrAdjustedLines) . ")";
		if ($this->VERBOSE)
			print "strSql: $strSql<br>\n";
		$arrResult = array();
		$this->objDBIHandle->query("SET NAMES 'utf8'");
		$objResult = $this->objDBIHandle->query($strSql);
		$intItr = 0;
		while ($objRow = mysql_fetch_assoc($objResult)) {
			$arrResult[] = "'" . $arrLines[$arrLineKeys[$intItr]] . "' : {
				\"Page\" : {$objRow["Page"]},
				\"Perek\" : \"{$objRow["Perek"]}\",
				\"Line\" : " . $arrLineKeys[$intItr] . ",
				\"EndGoal\" : " . $objRow["Line"] . "
			}";
			$intItr++;
		}
		$this->objDBIHandle->close();
		return "{" . join(",", $arrResult) . "}";
	}

	public function procRemainingMissions() {
		return ceil($this->procRemainingDays() / 365.25 * 52);
	}

	/* Function loadOldestPendingMission
	 * @Params: NULL
	 * @Return: 1 if successful
	 * Load the latest completed mission into state
	 */
	public function loadOldestPendingMission() {
		if (
			!isset($this->objUserHandle->intTableID)
		) {
			if ($this->VERBOSE)
				die("Could not load mission, no user handle was implemented.<br>\n");
			return 0;
		}
		$strSql = "
			SELECT
				*
			FROM
				" . tanya_missions_table . "
			WHERE
				user_id = " . $this->objUserHandle->intTableID . "
				AND tested=0
			ORDER BY
				mission_number ASC
			LIMIT 1
		";
		if ($this->VERBOSE)
			print $strSql . "<br>\n";
		$this->objDBIHandle->open();
		$objResult = $this->objDBIHandle->query($strSql);
		if (
			$objResult
			&& mysql_num_rows($objResult)
			&& $objRow = mysql_fetch_assoc($objResult)
		) {
			$this->intMissionId = $objRow["mission_id"];
			$this->intUserId = $objRow["user_id"];
			$this->foreign = $objRow["foreign"];
			$this->intMissionNumber = $objRow["mission_number"];
			$this->intLadder = $objRow["ladder"];
			$this->intReal = $objRow["real"];
			$this->intSum = $objRow["sum"];
			$this->intVirtualSum = $objRow["virtual_sum"];
			$this->intDateCreated = $objRow["date_created"];
			$this->objDBIHandle->close();
			return $this->intMissionNumber;
		}
		$this->objDBIHandle->close();
		return 0;
	}

	/* Function loadNewestMissions
	 * @Params: NULL
	 * @Return: 1 if successful
	 * Load the latest completed mission into state
	 */
	public function loadNewestMission() {
		if (
			!isset($this->objUserHandle->intTableID)
		) {
			if ($this->VERBOSE)
				die("Could not load mission, no user handle was implemented.<br>\n");
			return 0;
		}
		$strSql = "
			SELECT
				*
			FROM
				" . tanya_missions_table . "
			WHERE
				user_id = " . $this->objUserHandle->intTableID . "
				AND tested=1
			ORDER BY
				mission_number DESC
			LIMIT 1
		";
		if ($this->VERBOSE)
			print $strSql . "<br>\n";
		$this->objDBIHandle->open();
		$objResult = $this->objDBIHandle->query($strSql);
		if (
			$objResult
			&& mysql_num_rows($objResult)
		) {
			if ($objRow = mysql_fetch_assoc($objResult)) {
				$this->intMissionNumber = $objRow["mission_number"];
				$this->intSum = $objRow["sum"];
				$this->intVirtualSum = $objRow["virtual_sum"];
				$this->objDBIHandle->close();
				return $this->intMissionNumber;
			}
		}
		$this->objDBIHandle->close();
		return 0;
	}

	/* Function missionEntryRange
	 * @Params: NULL
	 * @Return: array("current"=>array(RANGE1,RANGE2), "future"=>array(RANGE1,RANGE2))
	 * Calculates the required missions to be inserted. Could be a range; ex: mission: 1-5
	 *
	 * Math for tasks per mission
	 * $intReal; // Defines the amount of task within a mission; a task represents an amount of a line or lines
	 * $intSum; // The lines for this week after remainder from previous mission is subtracted
	 * $intVirtual; // Number of lines the user was tested on
	 * $intRemainder; // Real minus the remainder from the previous mission
	 * $intVirtualSum; // The number of lines completed up till this point
	 * Construct result array with mission & line ranges
	 *
	 */
	public function missionEntryRange($intMissionPages=1, $intIncludeLines=0, $boolVerbose=0) {

		/*
		 * First; define the conditions
		 */
		$boolVerbose = 0;
		$intCurrentLine = $this->objUserHandle->intLinesBeforeEnrollment + $this->objUserHandle->intLinesAfterEnrollment;
		$this->intCurrentLine = $intCurrentLine;
		if ($boolVerbose)
			print "intCurrentLine: $intCurrentLine<br>\n";

		$this->intReal = $this->procLinesPerMission();
		if ($boolVerbose)
			print "intReal: {$this->intReal}<br>\n";

		if (
			!isset($this->intReal)
			|| !$this->intReal
		) {
			return 0;
		}

		if ($this->intReal == 0)
		{
			return 0;
		}
		
		$intMissionsPerLine = ceil(1/ceil($this->intReal));
		if ($boolVerbose)
			print "intMissionsPerLine: $intMissionsPerLine<br>\n";

		$intCurrentMission = $this->loadNewestMission()+1; // intSum, intVirtualSum created.
		if ($boolVerbose)
			print "intCurrentMission: $intCurrentMission<br>\n";

		$intSum =  isset($this->intSum) ? $this->intSum : 0;
		if ($boolVerbose)
			print "intSum: $intSum<br>\n";

		$intVirtual = ceil($intSum);
		if ($boolVerbose)
			print "intVirtual: $intVirtual<br>\n";

		$intRemainder = !$intCurrentMission ? 0 : $intSum - ceil($intSum);
		if ($boolVerbose)
			print "intRemainder: $intRemainder<br>\n";

		$intVirtualSum = $intCurrentLine;//$this->intVirtualSum;
		if ($boolVerbose)
			print "intVirtualSum: $intVirtualSum<br>\n";

		$intMissionsRemaining = $this->procRemainingMissions();
		if ($boolVerbose)
			print "intMissionsRemaining: " . $intMissionsRemaining . "<br>\n";

		$intPagesToDisplay = $intMissionsRemaining >= $intMissionPages ? $intMissionPages : $intMissionsRemaining;
		if ($boolVerbose)
			print "intPagesToDisplay: " . $intPagesToDisplay . "<br>\n";
		if ($intPagesToDisplay < 1)
			die("Invalid missionEntryRange amount.<br>\n");

		$arrLines = array();
		$arrResults = array();

		/*
		 * Then; loop for each page that is needed to be displayed
		 * intPage represents a single or group of missions which will be listed as pages from mission entry
		 * Up until this point we have only established values from missions that have been approved, so the
		 * following loop will drill into the future.
		 */

		$intMission = $intCurrentMission;
		for ($intPage=$intCurrentMission; $intPage!=$intCurrentMission+$intPagesToDisplay; $intPage++) {
			if ($boolVerbose)
				print "Loop; <br>\n";
			if ($boolVerbose)
				print "intPage: $intPage<br>\n";
			$intReal = 0;
			$intSum = 0 - $intRemainder;
			if ($boolVerbose)
				print "intSum: $intSum<br>\n";
			$intItrMission = 0; // The number of mission that have been iterated at this level hierarchy
			$intMissionStart = $intMission;
			do {
				$intSum += $this->intReal;
				if ($boolVerbose)
					print "intSum: $intSum (mission $intItrMission)<br>\n";
				$intReal = $this->intReal;
				$intMission++;
				$arrResults[$intPage]["mission_dates"][] = round($this->objUserHandle->intEnrolledDate + (($intMissionStart) * 86400 * 7.019230769230769) + (($intItrMission-1) * 86400 * 7.019230769230769));
				$intItrMission++;
			} while ($intSum <= 1);
			$arrResults[$intPage]["mission_dates"][] = round($this->objUserHandle->intEnrolledDate + (($intMissionStart) * 86400 * 7.019230769230769) + (($intItrMission-1) * 86400 * 7.019230769230769));
			if ($boolVerbose)
				print "intReal: $intReal<br>\n";
			$arrResults[$intPage]["real"] = $intReal;
			$arrResults[$intPage]["sum"] = $intSum;

			$arrResults[$intPage]["mission_count"] = $intItrMission;
			if ($boolVerbose)
				print "intMissionCount: " . $arrResults[$intPage]["mission_count"] . "<br>\n";

			$intVirtual = floor($intSum);
			if ($boolVerbose)
				print "intVirtual: $intVirtual<br>\n";
			$arrResults[$intPage]["line_count"] = $intVirtual;

			for ($intItr=$intVirtualSum;$intItr!=$intVirtualSum+$intVirtual;$intItr++) {
				$arrLines[] = $intItr+1;
			}
			$arrResults[$intPage]["line_start"] = $intVirtualSum;
			$intVirtualSum += $intVirtual;
			if ($boolVerbose)
				print "intVirtualSum: $intVirtualSum<br>\n";
			$arrResults[$intPage]["virtual_sum"] = $intVirtualSum;
			$intRemainder = floor($intSum) - $intSum;
			if ($boolVerbose)
				print "intRemainder: $intRemainder<br>\n";
			$arrResults[$intPage]["remainder"] = $intRemainder;

			// Dates
			$strFirst = array_shift($arrResults[$intPage]["mission_dates"]);
			$strLast = array_pop($arrResults[$intPage]["mission_dates"]);
			unset($arrResults[$intPage]["mission_dates"]);
			$arrResults[$intPage]["mission_dates"][0] = $strFirst;
			$arrResults[$intPage]["mission_dates"][1] = $strLast-86400;
			if ($boolVerbose)
				print "Date start: " . date("M jS, Y", $arrResults[$intPage]["mission_dates"][0]) . "<br>\n";
			if ($boolVerbose)
				print "Date end: " . date("M jS, Y", $arrResults[$intPage]["mission_dates"][1]) . "<br>\n";

			$arrResults[$intPage]["mission_start"] = $intMissionStart;
			$arrResults[$intPage]["mission_end"] = $intMissionStart + $arrResults[$intPage]["mission_count"] - 1;
		}

		if ($intIncludeLines) {

			/*
			 * And now; load the lines of tanya that are required to complete the tasks being displayed
			 */

			$strSql = "
				SELECT
					*
				FROM
					" . tanya_lines_table . "
				WHERE
					Line IN (" . join(",", $arrLines) . ")
			";
			$this->objDBIHandle->open();
			$this->objDBIHandle->query("SET NAMES 'utf8'");
			$objResult = $this->objDBIHandle->query($strSql);
			if (
				$objResult
				&& mysql_num_rows($objResult)
			) {
				while ($objRow = mysql_fetch_assoc($objResult)) {
					$arrLines[$objRow["Line"]-1] = array(
						"Page" => $objRow["Page"],
						"Perek" => $objRow["Perek"],
						"Text" => $objRow["Text"]
					);
				}
			}
		}

		$arrReturn = array(
			"design" => $arrResults
		);
		if ($intIncludeLines)
			$arrReturn["lines"] = $arrLines;
		return $arrReturn;
	}

	public function procLinesPerMission() {
		//$("#ladder_this_2").html((Math.round(intLadderLines / (52 * intYearsRemaining) * 100) / 100) + " Lines");
		$intLadderLines = number_format($this->ladderLines($this->objUserHandle->intLadder)/416, 2);
		return $intLadderLines;
	}

	public function ladderLines($intLadder) {
		if ($intLadder) {
			$strSql = "
				SELECT
					`year`
				FROM
					" . tanya_goals_table . "
				WHERE
					`id`=" . $intLadder;
			$this->objDBIHandle->open();
			$objResult = $this->objDBIHandle->query($strSql);
			if (
				$objResult
				&& mysql_num_rows($objResult)
			) {
				$intLadderLines = mysql_result($objResult, 0);
				$this->objDBIHandle->close();
				return $intLadderLines;
			}
		}
	}

	public function procRemainingDays() {
		if (
			!isset($this->objUserHandle->intBirthDate)
			|| !is_numeric($this->objUserHandle->intBirthDate)
		) {
			die("Cannot run know the remaining years unless a user is loaded with a valid birthdate.<br>\n");
		}
		$intSecondsAlive = time() - $this->objUserHandle->intBirthDate;
		$intSecondsRemaining = 13 * 365.25 * 86400 - $intSecondsAlive;
		$intDaysRemaining = $intSecondsRemaining / 86400;
		return $intDaysRemaining;
	}
}
?>