<?php
class Books
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

	public function _books_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"book_id"				=> @$arrParams["book_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"book_name"				=> @$arrParams["book_name"],
			"line_numbers_enabled"	=> @$arrParams["line_numbers_enabled"],
			"paragraphs_enabled"	=> @$arrParams["paragraphs_enabled"],
			"pages_enabled"			=> @$arrParams["pages_enabled"],
			"chapters_enabled"		=> @$arrParams["chapters_enabled"],
			"volumes_enabled"		=> @$arrParams["volumes_enabled"]
		);

		$strSql = "
			SELECT
				*
			FROM
				books
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

	public function _books_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"book_id"				=> @$arrParams["book_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"book_name"				=> @$arrParams["book_name"],
			"line_numbers_enabled"	=> @$arrParams["line_numbers_enabled"],
			"paragraphs_enabled"	=> @$arrParams["paragraphs_enabled"],
			"pages_enabled"			=> @$arrParams["pages_enabled"],
			"chapters_enabled"		=> @$arrParams["chapters_enabled"],
			"volumes_enabled"		=> @$arrParams["volumes_enabled"],
			"created"				=> date("Y-m-d H:i:S"),
			"created_by"			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("books", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _books_update($arrParams)
	{
		$arrValuesParams = array("institution_id","book_name","line_numbers_enabled","paragraphs_enabled","pages_enabled","chapters_enabled","volumes_enabled");
		$arrWhereParams = array("book_id","institution_id","book_name","line_numbers_enabled","paragraphs_enabled","pages_enabled","chapters_enabled","volumes_enabled","created","modified","created_by");

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
		$boolResult = $this->_db->update("books", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _book_lines_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"book_line_id"		=> @$arrParams["book_line_id"],
			"book_id"			=> @$arrParams["book_id"],
			"line_hierarchy"	=> @$arrParams["line_hierarchy"],
			"line_data"			=> @$arrParams["line_data"],
			"line_number"		=> @$arrParams["line_number"],
			"paragraphs"		=> @$arrParams["paragraphs"],
			"pages"				=> @$arrParams["pages"],
			"chapters"			=> @$arrParams["chapters"],
			"volumes"			=> @$arrParams["volumes"]
		);

		$strSql = "
			SELECT
				*
			FROM
				book_lines
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (is_array($Value))
			{
				$arrValues = array();
				foreach ($Value as $SubValue)
				{
					if (
						$SubValue === "0"
						|| $SubValue === 0
						|| $SubValue
					) {
						if (!is_int($SubValue))
						{
							$SubValue = '"' . $SubValue . '"';
						}
						$arrValues[] = $SubValue;
					}
				}
				if (count($arrValues))
				{
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
			}
			else if (
				isset($Value)
				&& (
					$Value === "0"
					|| $Value === 0
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
		if (isset($arrParams["ORDER"]))
		{
			$strSql .= "
				ORDER BY
					" . $arrParams["ORDER"];
		}
		else
		{
			$strSql .= "
				ORDER BY
					line_hierarchy+0 ASC";
		}

		if (isset($arrParams["LIMIT"]))
		{
			$strSql .= "
				LIMIT " . $arrParams["LIMIT"];
		}

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _book_lines_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"book_line_id"		=> @$arrParams["book_line_id"],
			"book_id"			=> @$arrParams["book_id"],
			"line_hierarchy"	=> @$arrParams["line_hierarchy"],
			"line_data"			=> @$arrParams["line_data"],
			"line_number"		=> @$arrParams["line_number"],
			"paragraphs"		=> @$arrParams["paragraphs"],
			"pages"				=> @$arrParams["pages"],
			"chapters"			=> @$arrParams["chapters"],
			"volumes"			=> @$arrParams["volumes"],
			"created"			=> date("Y-m-d H:i:S"),
			"created_by"		=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("book_lines", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _book_lines_update($arrParams)
	{
		$arrValuesParams = array("line_data","line_hierarchy","line_number","paragraphs","pages","chapters","volumes");
		$arrWhereParams = array("book_line_id","book_id","line_hierarchy","line_data","line_number","paragraphs","pages","chapters","volumes","created","modified","created_by");

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

		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams["where"][$strKey]);
		}

		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MB-BLU101-ASDASD";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("book_lines", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _book_lines_delete($arrParams)
	{
		$arrWhereParams = array("book_line_id","book_id","line_hierarchy","line_data","line_number","paragraphs","pages","chapters","volumes","created","modified","created_by");
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
		$boolResult = $this->_db->delete("book_lines", $arrFeilds);
		return $boolResult;
	}

	// Generic functions end

	public function books_select_hierarchy($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"book_id"				=> @$arrParams["book_id"],
			"book_name"				=> @$arrParams["book_name"],
			"line_numbers_enabled"	=> @$arrParams["line_numbers_enabled"],
			"paragraphs_enabled"	=> @$arrParams["paragraphs_enabled"],
			"pages_enabled"			=> @$arrParams["pages_enabled"],
			"chapters_enabled"		=> @$arrParams["chapters_enabled"],
			"volumes_enabled"		=> @$arrParams["volumes_enabled"]
		);

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

		$strSql = "
			SELECT
				*
			FROM
				books
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

		$strSql .= "
			ORDER BY
				modified DESC, created DESC";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/*
	 * Once a column has been deleted there is a gap created in the line_hierarchy
	 * column, this column must be kept as a constant interation without gaps.
	 * Warning: The logic in the function currently doesn't handel more than one
	 * row at a time.
	 */
	public function book_lines_delete_fix($arrParams)
	{
		$arrWhereParams = array("book_line_id","book_id","line_hierarchy","line_data","line_number","paragraphs","pages","chapters","volumes","created","modified","created_by");
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
		// Before deleting first select whats being deleted
		$objLine = current($this->_book_lines_select($arrParams));
		// Delete the line
		$boolResult = $this->_db->delete("book_lines", $arrFeilds);
		if ($boolResult)
		{
			// Incrament everything after the deleted line down by one,
			// therefor auto-correcting the hierarchy.
			$strSql = "
				UPDATE
					book_lines
				SET
					line_hierarchy = line_hierarchy - 1
				WHERE
					line_hierarchy > " . $objLine->line_hierarchy;
			$this->_db->query($strSql);
		}
		return $boolResult;
	}

	public function book_lines_select_count($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"book_line_id"		=> @$arrParams["book_line_id"],
			"book_id"			=> @$arrParams["book_id"],
			"line_hierarchy"	=> @$arrParams["line_hierarchy"],
			"line_data"			=> @$arrParams["line_data"],
			"line_number"		=> @$arrParams["line_number"],
			"paragraphs"		=> @$arrParams["paragraphs"],
			"pages"				=> @$arrParams["pages"],
			"chapters"			=> @$arrParams["chapters"],
			"volumes"			=> @$arrParams["volumes"]
		);

		$strSql = "
			SELECT
				count(book_line_id)
			FROM
				book_lines
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

		$arrResult = (array) $this->_db->fetchRow($strSql);
		return $arrResult["count(book_line_id)"];
	}

	/*
	public function range_from_book_params($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["book_id"]))
		{
			print "Sorry, there was an error: MB-RFBP101-SD78DS";
			exit;
		}
		$objBook = (array) current($this->_books_select(array(
			"book_id" => $arrParams["book_id"]
		)));

		// Order of item implaments hierarchy
		$arrBookCols = array(
			"line_number" => "line_numbers_enabled",
			"paragraphs" => "paragraphs_enabled",
			"pages" => "pages_enabled",
			"chapters" => "chapters_enabled",
			"volumes" => "volumes_enabled"
		);

		$strSql = "
			SELECT
				*
			FROM
				book_lines
			WHERE
				book_id = " . $arrParams["book_id"];
		foreach ($arrBookCols as $strBookLineCol => $strBookParam)
		{
			if (
				$objBook[$strBookParam] // Book settings allow for this condition
				&& isset($arrParams[$strBookLineCol]) // Parameters were provided
			) {

			}
		}


		$arrBookLines = $this->_book_lines_select(array(
			"book_id" => $arrParams["book_id"]
		));
		//var_dump($objBook);exit;
	}
	*/

	/*
	 *
	public function range_from_date($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["book_id"]))
		{
			print "Sorry, there was an error: MB-RFD101-SD78DS";
			exit;
		}
		if (!isset($arrParams["mission_id"]))
		{
			print "Sorry, there was an error: MB-RFD102-ASD32S";
			exit;
		}
		if (!isset($arrParams["intDate"]))
		{
			print "Sorry, there was an error: MB-RFD103-A1DASA";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MB-RFD104-SDFSD2";
			exit;
		}
		$objScheduler = new Scheduler();
		$objSchedulerProc = new SchedulerProc($objScheduler);
		$arrProcParams = array();
		$arrProcParams["time"] = date("U", mktime(0,0,0,1,1,date("Y", time())));
		$objSchedulerProc->params($arrProcParams);
		$Result = $objSchedulerProc->process_mission_book(array(
			"mission_id" => $arrParams["mission_id"],
			"campaign_id" => $arrParams["campaign_id"],
			"user_id" => $arrParams["user_id"]
		));
		var_dump($Result);
		exit;
	}
	 *
	 */

	/*
	 * Produce the line location (line number, page, chapter...)
	 * of a given ladder and line offset on the date of a given
	 * age of user.
	 * Required params: user_id, mission_id, institution_id
	 * Note: This function is only designed to handle users in a single grade
	 * at once. Will default also to highest grade.
	 */
	public function book_location_by_date ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MB-BLBD101-SD98S9";
			exit;
		}
		if (!isset($arrParams["mission_id"]))
		{
			print "Sorry, there was an error: MB-BLBD102-8FD9F8";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MB-BLBD103-H877T7";
			exit;
		}

		$objScheduler = new Scheduler();
		$objMissions = new Missions();

		// Load the mission
		$objMission = current($objMissions->_missions_select(array(
			"mission_id" => $arrParams["mission_id"]
		)));

		// Loop through all the ladders
		$arrSchedule = current($objScheduler->load_book_schedule(array(
			"mission_id" => $arrParams["mission_id"],
			"user_id" => $arrParams["user_id"],
			"ladder" => @$arrParams["ladder"],

			// Optional
			"capture_start_date" => @$arrParams["capture_start_date"],
			"capture_end_date" => @$arrParams["capture_end_date"],
			"task_offset" => @$arrParams["task_offset"],
			'future' => true
		)));

		// The line number the user is will be on by the given date
		$intLineNumber = current($arrSchedule["tasks"]);

		$objLineEnd = current($this->_book_lines_select(array(
			"book_id" => $objMission->book_id,
			"LIMIT" => 1,
			"ORDER" => "line_hierarchy+0 DESC"
		)));
		// Select the record from the database and return the available data
		$objLine = current($this->_book_lines_select(array(
			"book_id" => $objMission->book_id,
			"line_number" => round($intLineNumber)
		)));

		if (!$objLine)
		{
			$objLine = $objLineEnd;
		}
		return $objLine;
	}

	/*
		Old bugs have caused the book library hierarchy to go out of sync.
		This function will fix this problem to the best of its ability.
	*/
	public function fix_book_hierarchy($arrParams)
	{
		if (!isset($arrParams["book_id"]))
		{
			print "Sorry, there was an error: MB-FBH101-D9DS9D";
			exit;
		}
		$arrBookLines = $this->_book_lines_select(array(
			"book_id" => $arrParams["book_id"],
			"ORDER" => "line_number + 0"
		));
		$intItr = 0;
		$arrSql = array();
		foreach ($arrBookLines as $objBookLine)
		{
			if ($objBookLine->line_hierarchy != $intItr)
			{
				$arrSql[] = array(
					"where" => array(
						"book_line_id" => $objBookLine->book_line_id
					),
					"values" => array(
						"line_hierarchy" => $intItr
					)
				);
			}
			$intItr++;
		}
		foreach ($arrSql as $arrUpdate)
		{
			$this->_book_lines_update($arrUpdate);
		}
		//var_dump($arrSql);
		print 1;
	}

	/*
	 * Find the users current bat/bar mitzvah.
	 */
	public function batbar_user_goal($arrParams)
	{
		if (!isset($arrParams['user_id']))
		{
			print "Sorry, there was an error: MB-BGL101-SD89F7";
			exit;
		}
		if (!isset($arrParams['institution_id']))
		{
			print "Sorry, there was an error: MB-BGL102-BSDG21";
			exit;
		}
		$arrYearSchedule = $objScheduler->load_book_schedule(array(
			"user_id" => $this->_kiosk_user_session_data->user_id,
			"institution_id" => $this->_kiosk_user_session_data->institution_id,
			"mission_id" => $objMission->mission_id,
			"capture_start_date" => $intYearStart,
			"capture_end_date" => capture_end_date,
			"kiosk" => true
		));
		$arrYearEnd = end($arrYearSchedule);
		$intLineEnd = end($arrYearEnd["tasks"])+1;
		$intBatBarEpoch = $objUsers->user_batbar_in_epoch(array(
			"user_id" => $this->_kiosk_user_session_data->user_id
		));
		$objBarBatLocation = $objBooks->book_location_by_date(array(
			"user_id" => $arrParams['user_id'],
			"institution_id" => $arrParams['institution_id'],
			"mission_id" => $arrParams['mission_id'],
			// Bar/Bat Mitzvah date
			"capture_start_date" => mktime(0, 0, 0, date("n", $intBatBarEpoch), date("j", $intBatBarEpoch), date("Y", $intBatBarEpoch)),
			"capture_end_date" => mktime(0, 0, 0, date("n", $intBatBarEpoch), date("j", $intBatBarEpoch), date("Y", $intBatBarEpoch)) - 7 * 86400
		));
		$intGoal = floor($this->intLineEnd > @$this->objBarBatLocation->line_number ? @$this->objBarBatLocation->line_number : $this->intLineEnd);
	}
}
?>