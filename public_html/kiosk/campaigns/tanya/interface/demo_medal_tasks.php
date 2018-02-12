<?
$VERBOSE = 0;
$objTemplates->load(template_demo_medal_tasks); // Load a template
$objTemplates->process(); // This applies generic replacements to the template
$strSql = "
	SELECT
		*
	FROM
		" . tanya_missions_table . "
	WHERE
		user_id=" . $user_row["user_id"] . "
		AND mission_number = " . $_GET["mission"];
$objDBIHandle->open();
$objResult = $objDBIHandle->query($strSql);
$arrLines = array();
$boolCheckmark = 0;
if (
	$objResult
	&& mysql_num_rows($objResult)
) {
	$boolCheckmark = 1;
	$arrMission = mysql_fetch_assoc($objResult);
	$objDBIHandle->free();
	$strSql = "
		SELECT
			*
		FROM
			" . tanya_tasks_table . "
		WHERE
			mission = " . $arrMission["mission_number"];
	$objResult = $objDBIHandle->query($strSql);
	if (
		!$objResult
		|| !mysql_num_rows($objResult)
	) {
		print "$strSql\n";
		die("No tasks found in this mission!?");
	}
	while ($arrTask = mysql_fetch_assoc($objResult)) {
		$arrLines[] = $arrTask["line_number"];
	}
} else {
	$objMissions = new TanyaMissions($objTanya->objUserHandle, 0);
	$arrMissionRanges = $objMissions->missionEntryRange(
		416, // Number of pages to scale
		0, // Include lines
		0  // Verbose
	);
	//var_dump($arrMissionRanges["design"]);
	$arrMissionRanges["design"] = $objMissions->missionEntryRangeStyle2($arrMissionRanges["design"]);
	$objDBIHandle->open();
	//var_dump($arrMissionRanges["design"]);
	//exit;
	//var_dump($arrMissionRanges["design"][$_GET["mission"]]);
	//exit;
	if (isset($arrMissionRanges["design"][$_GET["mission"]])) {
		for ($intItr=$arrMissionRanges["design"][$_GET["mission"]]["line_start"]; $intItr!=$arrMissionRanges["design"][$_GET["mission"]]["line_start"]+$arrMissionRanges["design"][$_GET["mission"]]["line_count"]; $intItr++) {
			//$arrLines[] = $objTanya->objUserHandle->intLinesBeforeEnrollment+$intItr+1;
			$arrLines[] = $intItr+1;
		}
	}
}
$strHTML = "";
$strSql = "
	SELECT
		*
	FROM
		" . tanya_lines_table . "
	WHERE
		Line IN (" . join(",", $arrLines) . ")";
$objDBIHandle->query("SET NAMES 'utf8'");
$objResult = $objDBIHandle->query($strSql);
while ($objRow = mysql_fetch_assoc($objResult)) {

	$strHTML .= "
		<div class=\"question\" style='background:url(/file_view.php?id=2300380617) no-repeat;'>
			<p>" . $objRow["Text"] . "</p>

			<div class='mission_quota'>Perek: " . $objRow["Perek"] . " Page: " . $objRow["Page"] . " Line: " . $objRow["Line"] . "</div>"
		. (
			$boolCheckmark && $arrMission["tested"]
			? "<div class='check_on'></div><div class='mission_complete'>5.00 Miles</div>"
			: ""
		) . "
			<div class='clear'></div>
		</div>";
}
$objTemplates->replace("__!medal_pending!__", isset($arrMission["tested"]) ? "" : "Pending ");
$objTemplates->replace("__!medal_tasks!__", $strHTML);
$objTemplates->replace("__!medal_title!__", "Mission: " . $_GET["mission"] . " - Quota (Ladder " . $objTanya->objUserHandle->intLadder . ")");
$objTemplates->replace("__!medal_param!__", $_GET["medal"]);
print $objTemplates->toString(); // Display the template
?>