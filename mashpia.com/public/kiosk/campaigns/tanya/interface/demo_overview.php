<?
	if (
		isset($_POST)
		&& is_array($_POST)
		&& count($_POST)
	) {
		if (
			isset($_GET["enroll"])
			&& $_GET["enroll"] == "true"
		) {
			$objTanya->objUserHandle->intEnrolled = 1;
			$objTanya->objUserHandle->intEnrolledDate = time();
			$objTanya->objUserHandle->setLinesBeforeEnrollment($_POST["chapter_offset"]);
			$objTanya->objUserHandle->setChapterGoal($_GET["line_goal"]);
			$objTanya->objUserHandle->setLadder($_POST["spinner-ladder-week"]);
			//$objTanya->updateUser();
			$objTanya->insertUser();
			//$objTanya->objUserHandle->setChapterGoal($_POST["line_goal"]);
			//$objTanya->objUserHandle->setLadder($_POST["ladder"]);
			//$objTanya->updateUser();
			$intElapsed = $objTanya->objUserHandle->getEnrolledElapsedDays();
			$intRequestKey = $objTanya->objUserHandle->setLadderRequest($_GET["line_goal"], $_POST["spinner-ladder-week"]);
			if ($intElapsed <= 30) {
				// Use the ajax approval file to save time
				$strResult = file_get_contents("http://www.mashpia.com/camps/content.php?output=approvals_ladder_upgrades&request_id=" . $intRequestKey);
				if ($strResult != "") {
					if (VERBOSE)
						die("approvals_ladder_upgrades fail: $strResult");
				}
			}
			print 1;
			exit; // ajax
			//header("Location: " . path);
		}
		else if (
			isset($_GET["mission_ajax"])
			&& $_GET["mission_ajax"] == "true"
		) {
			$objTemplates->load(template_user_overview_medals_ajax); // Load a template
			$objTemplates->replace("__!medal_id!__", $arrMedalData[$arrMedalData[$_GET["medal"]]]);
			$objMissions = new TanyaMissions($objTanya->objUserHandle, 0);
			$arrMissionRanges = $objMissions->missionEntryRange(
				416, // Number of pages to scale
				0, // Include lines
				0  // Verbose
			);
			//=var_dump($arrMissionRanges);
			$strSql = "
				SELECT
					*
				FROM
					" . tanya_missions_table . "
				WHERE
					user_id=" . $user_row["user_id"] . "
					AND mission_number >= " . ($arrMedalData[$_GET["medal"]-1]+1) . "
					AND mission_number <= " . $arrMedalData[$_GET["medal"]] . "
				ORDER BY
					mission_number ASC
			";
			//print $strSql;
			$objDBIHandle->open();
			$objResult = $objDBIHandle->query($strSql);
			
			$intStartDate = 0;
			$arrPending = $arrComplete = array();
			while ($objRow = mysql_fetch_assoc($objResult)) {
				if (!$intStartDate)
					$intStartDate = $objRow["mission_date"];
				if ($objRow["tested"]) {
					$arrComplete[$objRow["mission_number"]] = 1;
				} else {
					$arrPending[$objRow["mission_number"]] = 1;
				}
			}
			$intWeekElapsed = 0;
			$strListHtml = "";
			$intDateToday = mktime(0,0,0,date("m"),date("d"),date("y"));
			$intDateToStart = $intDateToday+((7-date("N"))*86400);
			$intLineToStart = $_GET["start_line"] + ($arrMedalData[$_GET["medal"]-1] * $_GET["lines_per_mission"]);
			$intLinesPerMission = $_GET["lines_per_mission"];
			$intPrviousLine = $intLineToStart;
			for ($intMission=$arrMedalData[$_GET["medal"]-1]+1; $intMission!=$arrMedalData[$_GET["medal"]]+1; $intMission++) {
				$intLineToStart += $intLinesPerMission;
				$intCurrentDate = $intDateToStart + ceil(($intMission) * 7.024038461538462 * 86400);
				// 
				//<div class="date">' . jdtojewish(unixtojd ($intCurrentDate)) . '</div>
				$strDate = mb_convert_encoding( jdtojewish( unixtojd($intCurrentDate), true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH), "UTF-8", "ISO-8859-8");
				$strListHtml .= '
					<div class="task_items mission_boxes task_col">
						<div class="mission">
							<div class="number">#' . ($intMission) . '</div>
							<div class="date">' . $strDate . '</div>
							<div class="date">Line: ' . (
								(
									1==2
									&& ceil($intLineToStart) + 1 - $intPrviousLine > 1
								)
									? (ceil($intPrviousLine) + 1) . "-" . ceil($intLineToStart)
									: ceil($intLineToStart)
							) . '</div>
							<div class="check_on"></div>
						</div>
						<div class="math_big">' . ($intMission!=$arrMedalData[$_GET["medal"]] ? '+' : '=') . '</div>
					</div>
				';
				$intPrviousLine = $intLineToStart;
			}
			$objTemplates->replace("__!mission_remaining!__", $intWeekElapsed-count($arrComplete));
			$objTemplates->replace("__!mission_button_list!__", $strListHtml);
			$objTemplates->replace("__!medal name!__", $arrMedalNames[$_GET["medal"]]);
			$objTemplates->process(1); // This applies generic replacements to the template
			print $objTemplates->toString(); // Display the template
			exit;
		}
	}


	$objTemplates->load(template_user_overview); // Load a template
	
	
	$objMission = new TanyaMissions($objTanya->objUserHandle);
	$intDaysRemaining = $objMission->procRemainingDays(); // Process the time remaining
	$intDateToday = mktime(0,0,0,date("m"),date("d"),date("y"));
	$intDateToStart = $intDateToday+((7-date("N"))*86400);
	$intCurrentDate = $intDateToStart + ceil(7.024038461538462 * 86400);
	$intEnrollmentOffset = $intCurrentDate-($objTanya->objUserHandle->intBirthDate+5*86400*365.25);
	$intMissionEnd = ceil(416 - $intEnrollmentOffset / 86400 / 7.019230769230769);
	$strHtml = "";
	$intCount = 0;
	for ($intItr=1;$intItr!=11;$intItr++) {
		if ($arrMedalData[$intItr] <= $intMissionEnd)
		{
			$strHtml .= '<div class="medal12' . $intItr . '"><span class="badge">' . ($arrMedalData[$intItr] - $arrMedalData[$intItr-1]) . '</span></div>';
			$intCount++;
		}
	}
	$objTemplates->replace("__!medal_count!__",$intCount);
	$objTemplates->replace("__!earn_medals!__",$strHtml);
	$intYearsRemaining = ceil($intDaysRemaining / 365.25);
	$objTemplates->replace("__!post_url!__", "__!BASE_URI!__&action=overview");
	$objTemplates->replace("__!chapter_goal!__", $objTanya->objUserHandle->strDesiredChapterGoal);
	$objTemplates->replace("__!intLadder!__", $objTanya->objUserHandle->intLadder-1);
	$objTemplates->replace("__!JSON Ladder!__", preg_replace("/[ \t\n\r]+/", "", $objMission->procLadderList()));
	$objTemplates->replace("__!intLinesBeforeEnrollment!__", $objTanya->objUserHandle->intLinesBeforeEnrollment);
	$objTemplates->replace("__!intLinesAfterEnrollment!__", $objTanya->objUserHandle->intLinesAfterEnrollment);
	$objTemplates->replace("__!intCurrentLine!__", $objTanya->objUserHandle->intLinesBeforeEnrollment+$objTanya->objUserHandle->intLinesAfterEnrollment);
	$objTemplates->replace("__!intYearsRemaining!__", $intYearsRemaining);
	$objTemplates->replace("__!intAge+One!__", floor((time()-$objTanya->objUserHandle->intBirthDate)/86400/365.25)+1);
	$objTemplates->replace("__!intRemainingWeeks!__", ceil($objTanya->objUserHandle->procRemainingDays()/real_week));
	$objDBIHandle->open();
	$objDBIHandle->query("SET NAMES `utf8`");
	$strSql = "
		SELECT
			*
		FROM
			" . tanya_chapters_table;
	$objResult = $objDBIHandle->query($strSql);
	$arrJSON = array();
	$intItr = 0;
	while ($arrRow = mysql_fetch_assoc($objResult))
	{
		$arrJSON[] = $intItr . ':{"name":"' . $arrRow['name'] . '","line":' . $arrRow['line'] . '}';
		$intItr++;
	}
	$objTemplates->replace("__!Chapter JSON List!__", "{" . join(",", $arrJSON) . "}");
	$strSql = "
		SELECT
			Line,
			Page
		FROM
			" . tanya_lines_table . "
		WHERE
			1
		GROUP BY
			Page";
	$objResult = $objDBIHandle->query($strSql);
	$arrJSON = array();
	$intItr = 0;
	while ($arrRow = mysql_fetch_assoc($objResult)) {
		$arrJSON[] = $intItr . ':{"page":"' . $arrRow['Page'] . '","line":' . $arrRow['Line'] . '}';
		$intItr++;
	}
	$objTemplates->replace("__!Page JSON List!__", "{" . join(",", $arrJSON) . "}");
	$strSql = "SELECT * FROM " . tanya_goals_table;
	$objResult = $objDBIHandle->query($strSql);
	$strLadderOptionList = "";
	$arrItems = array();
	while ($objRow = mysql_fetch_assoc($objResult)) {
		$strLadderOptionList .= "<div>" . number_format($objRow['year'] / 416, 2) . "</div>";
		$arrItems[] = $objRow['id'];
		//$strLadderOptionList .= "<option value='" . $objRow['id'] . "'>" . round($objRow['year'] / 416, 2) . " Lines</option>";
	}
	$objTemplates->replace("__!User Ladder JSON!__", json_encode($arrItems));
	$objTemplates->replace("__!User Ladder Option List!__", $strLadderOptionList);
	mysql_data_seek($objResult, 0);
	$strLadderOptionList2 = "";
	while ($objRow = mysql_fetch_assoc($objResult)) {
		$strLadderOptionList2 .= "<div>" . round($objRow['year'] / 8) . "</div>";
		//$strLadderOptionList2 .= "<option value='" . $objRow['id'] . "'>" . round($objRow['year'] / 8) . " Lines</option>";
	}
	$objTemplates->replace("__!User Ladder Option List 2!__", $strLadderOptionList2);
	
	
	
	
	
	
	
	
	$strLadderOptionList = "";
	$arrItems = array();
	mysql_data_seek($objResult, 0);
	while ($objRow = mysql_fetch_assoc($objResult)) {
		//$strLadderOptionList .= "<div>" . number_format($objRow['year'] / 416, 2) . "</div>";
		$strLadderOptionList .= "<option value='" . $objRow['id'] . "'>" . round($objRow['year'] / 416, 2) . " Lines</option>";
	}
	$objTemplates->replace("__!User Ladder Option ListHTML!__", $strLadderOptionList);
	mysql_data_seek($objResult, 0);
	$strLadderOptionList2 = "";
	while ($objRow = mysql_fetch_assoc($objResult)) {
		//$strLadderOptionList2 .= "<div>" . round($objRow['year'] / 8) . "</div>";
		$strLadderOptionList2 .= "<option value='" . $objRow['id'] . "'>" . round($objRow['year'] / 8) . " Lines</option>";
	}
	$objDBIHandle->close();
	$objTemplates->replace("__!User Ladder Option List 2HTML!__", $strLadderOptionList2);
	
	
	
	
	
	
	
	
	
	
	
	
	
	$strYearsOptionList = "";
	for ($intItr=1;$intItr!=$intYearsRemaining+1;$intItr++) {
		$strYearsOptionList .= "<div"
			. ($intItr == $intYearsRemaining ? " class=\"active\"" : "")
			. ">Age " . (13-($intYearsRemaining-$intItr)) . "</div>";
	}
	$objTemplates->replace("__!Remaining Years List!__", $strYearsOptionList);
	$objTemplates->process(); // This applies generic replacements to the template
	print $objTemplates->toString(); // Display the template
?>