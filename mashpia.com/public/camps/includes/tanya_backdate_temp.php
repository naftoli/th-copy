<?php
	$arrLadders = array(
		"0.15" => "1",
		"0.30" => "2",
		"0.39" => "3",
		"0.56" => "4",
		"0.69" => "5",
		"0.82" => "6",
		"1.01" => "7",
		"1.13" => "8",
		"1.27" => "9",
		"1.40" => "10",
		"1.49" => "11",
		"1.69" => "12",
		"1.89" => "13",
		"2.04" => "14",
		"2.16" => "15",
		"2.52" => "16",
		"3.59" => "17",
		"5.27" => "18",
		"7.02" => "19",
		"9.06" => "20"
	);
	//var_dump($_POST);exit;
	DEFINE("BASE", "/home/mashpia/public_html/kiosk/campaigns/tanya");
	DEFINE("BASE_URI", "");
	
	$VERBOSE = 0;
	require_once("/home/mashpia/public_html/kiosk/campaigns/tanya/config.php");
	require_once("/home/mashpia/public_html/kiosk/campaigns/classes/class.DBI.php");
	require_once(BASE . "/source/class.Tanya.php");
	
	$objDBIHandle = new DBI($VERBOSE);
	
	if (
		isset($_POST)
		&& count($_POST)
	) {
		if ($_POST["dob"] == "get_user_dobs")
		{
			unset($_POST["dob"]);
			$strSql = "
				SELECT
					user_id, dob
				FROM
					mashpia.users
				WHERE
					user_id IN (" . join(",", $_POST) . ")";
			$objDBIHandle->open();
			$objResult = $objDBIHandle->query($strSql);
			$arrDobs = array();
			while ($objRow = mysql_fetch_assoc($objResult))
			{
				$arrDobs[$objRow["user_id"]]["dob"] = isset($objRow["dob"]) ? $objRow["dob"] : "";
			}
			$strSql = "
				SELECT
					user_id, track
				FROM
					mashpia.tanya_users
				WHERE
					user_id IN (" . join(",", $_POST) . ")";
			$objResult = $objDBIHandle->query($strSql);
			while ($objRow = mysql_fetch_assoc($objResult))
			{
				$arrDobs[$objRow["user_id"]]["quota"] = isset($objRow["track"]) ? $objRow["track"] : "";
			}
			print serialize($arrDobs);
			exit;
		}
		foreach ($_POST as $strKey => $intLadder)
		{
			if (
				preg_match("/^user_([0-9]+)$/", $strKey)
				&& preg_match("/^[0-9]+$/", $intLadder)
			) {
				$intUser = preg_replace("/^user_/", "", $strKey);
				$intOffset = $_POST["user_offset_" . $intUser];
				$intLinesComplete = $_POST["user_done_" . $intUser];
				$intMissionCount = $_POST["user_mission_" . $intUser];
				if (
					isset($_POST["user_medal_dob_" . $intUser])
					&& $_POST["user_medal_dob_" . $intUser]
				) {
					// Update DOB
					$objDBIHandle->open();
					$strSql = "
						UPDATE
							mashpia.users
						SET
							dob=\"" . mysql_escape_string($_POST["user_medal_dob_" . $intUser]) . "\"
						WHERE
							user_id = " . $intUser;
					//print $strSql;
					$objDBIHandle->query($strSql);
				}
				$strSql = "
					SELECT
						user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2, dob,
						user_city, user_state, user_postal, user_country, user_phone,
						user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
						team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
					FROM
						users
						LEFT JOIN schools USING (school_id)
						LEFT JOIN institutions USING (inst_id)
						LEFT JOIN classes USING (school_id, class_id)
						LEFT JOIN teams USING (school_id, team_id)
						LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$intUser} GROUP BY user_id) rank USING (user_id)
						LEFT JOIN ranks USING (rank_ord)
					WHERE
						user_id = {$intUser}
					ORDER BY
						class_grade, class_sub, last, first";
					
				$objDBIHandle->open();
				mysql_select_db("mashpia", $objDBIHandle->objHandle);
				$objResult = $objDBIHandle->query($strSql);
				$user_row = mysql_fetch_assoc($objResult);
				// enroll user
				$objTanya = new Tanya($VERBOSE);
				$objTanya->loadUser();
				print "User `" . $intUser . "` <br>\n";
				if ($objTanya->objUserHandle->intEnrolled != 1)
				{
					$objTanya->objUserHandle->intEnrolled = 1;
					
					$objTanya->objUserHandle->intEnrolledDate = $objTanya->procEnrollDate();
					$objTanya->objUserHandle->setLinesBeforeEnrollment($intOffset);
					$objTanya->objUserHandle->setChapterGoal($intLadder, 2);
					$objTanya->objUserHandle->setLadder($intLadder);
					$objTanya->insertUser();
					print "Enrolled <br>\n";
				}
				$intEnroll = $objTanya->procEnrollDate();
				$intDate = $intEnroll - ($intMissionCount * 7 * 86400);
				$objTanya->setEnrollDate($intDate);
				print "Date of enrollment set to: " . date("F j, Y", $intDate) . " <br>\n";
				$objTanya->setLadder($intLadder);
				print "Set ladder to: " . $intLadder . " <br>\n";
				$objTanya->setLinesBefore($_POST["user_offset_" . $intUser]);
				$objTanya->setLinesAfter(0);
				$objTanya->deleteAllMissions();
				$objMissions = new TanyaMissions($objTanya->objUserHandle, $VERBOSE);
				$arrMissionRanges = $objMissions->missionEntryRange($intMissionCount, 0);
				print "Missions completed: " . count($arrMissionRanges["design"]) . " <br>\n";
				//var_dump($arrMissionRanges["design"]);exit;
				$objDBIHandle->open(); // Lost connection from previous function
				$intCurrentTime = time();
				foreach ($arrMissionRanges["design"] as $intMission => $arrRange)
				{
					if ($arrRange["mission_end"] <= $intMissionCount)
					{
						// Insert missing missions
						$strSql = "
							INSERT
								INTO " . tanya_missions_table . "
								(user_id, mission_number, tested, tested_date, ladder, `real`, sum, virtual_sum, date_created)
							VALUES
						";
						$arrSql = array();
						for (
							$intItr = $arrRange["mission_start"];
							$intItr != $arrRange["mission_end"]+1;
							$intItr++
						) {
							$arrSql[] = "({$intUser}, $intItr, 1, UNIX_TIMESTAMP(), "
								. $objTanya->objUserHandle->intLadder
								. ", " . $arrRange["real"]
								. ", " . $arrRange["sum"]
								. ", " . $arrRange["virtual_sum"]
								. ", " . $intCurrentTime
								. ")";
						}
						$strSql = $strSql . join(",", $arrSql) . "
							ON DUPLICATE KEY UPDATE
								tested=1,
								tested_date=VALUES(tested_date),
								ladder=VALUES(ladder),
								`real`=VALUES(`real`),
								sum=VALUES(sum),
								virtual_sum=VALUES(virtual_sum);
						";//print $strSql;
						$objDBIHandle->query($strSql);
					}
					$strSql = "
						UPDATE
							" . tanya_missions_table . "
						SET
							tested = 1,
							tested_date = UNIX_TIMESTAMP()
						WHERE
							mission_id=" . $intMission;
					$objDBIHandle->query($strSql);
				}
			}
		}
	}
	else
	{
		$objDBIHandle->open();
		$strSql = "
			SELECT
				id, year
			FROM
				" . tanya_goals_table;
		$objResult = $objDBIHandle->query($strSql);
		$arrLadders = array();
		while ($objRow = mysql_fetch_assoc($objResult))
		{
			$arrLadders[$objRow["id"]] = $objRow["year"];
		}
		
		print json_encode(
			array(
				"arrMedalData" => $arrMedalData,
				"arrLadders" => $arrLadders
			)
		);
		exit;
	}
?>