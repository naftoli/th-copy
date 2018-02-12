<?
$VERBOSE = 0;
$objTemplates->load(template_demo_medal_missions); // Load a template
$objTemplates->replace("__!medal_number!__", $_GET["medal"]);
$objTemplates->replace("__!medal_id!__", $arrMedalData[$arrMedalData[$_GET["medal"]]]);
$objMissions = new TanyaMissions($objTanya->objUserHandle, 0);
$arrMissionRanges = $objMissions->missionEntryRange(
	416, // Number of pages to scale
	0, // Include lines
	0  // Verbose
);
//=var_dump($arrMissionRanges);
$strListHtml = "";
if (
	!isset($arrMedalData[$_GET["medal"]])
	&& !isset($arrMedalData[$arrMedalData[$_GET["medal"]]])
) {

}
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
$intDateToStart = $objTanya->objUserHandle->intEnrolledDate;
$intLinesPerMission = $objTanya->procTasksPerMission($objTanya->objUserHandle->ladderLines());
$intLineToStart = 0 + ($arrMedalData[$_GET["medal"]-1] * $intLinesPerMission);
for ($intMission=$arrMedalData[$_GET["medal"]-1]+1; $intMission!=$arrMedalData[$_GET["medal"]]+1; $intMission++) {
	$intLineToStart += $intLinesPerMission;
	$intCurrentDate = $intDateToStart + ceil(($intMission-1) * 7.024038461538462 * 86400);
	//$strDate = date("M jS, Y", $intCurrentDate);
	$strDate = mb_convert_encoding( jdtojewish( unixtojd($intCurrentDate), true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH), "UTF-8", "ISO-8859-8");
	$strDate = preg_replace("/([^ ]+ [^ ]+) ([^ ]+)/", "$1<br>$2", $strDate);
	$strListHtml .= "
		<div class='mission'><a href='__!BASE_URI!__&action=medal_tasks&medal=" . $_GET["medal"] . "&mission=$intMission'>
				<div class='number'>"
			/*. (
				isset($arrPending[$intMission])
				? "p "
				: ""
			) */. "#" . ($intWeekElapsed + 1) . "</div>
				<div class='date'>" . $strDate . "</div>
				<div class='date'>Line: " . ceil($intLineToStart) . "</div>
				<!--div class='meter' style='background-position:0% 0;'></div-->
				</a>"
			. (
				isset($arrComplete[$intMission])
				? "<div class='check_on'></div>"
				: ""
			) . "
		</div>
	";
	$intWeekElapsed++;
}
$objTemplates->replace("__!mission_remaining!__", $intWeekElapsed-count($arrComplete));
if ($_GET["medal"] > 1)
	$strListHtml .= "
		<div class=\"mission button_back\">
			<a href=\"__!BASE_URI!__&action=medal_missions&medal=" . ($_GET["medal"]-1) . "\">Previous<br />Medal</a>
		</div>";
if ($_GET["medal"] < $objMissions->procTotalMedals()-1)
	$strListHtml .= "
		<div class=\"mission button_back\">
			<a href=\"__!BASE_URI!__&action=medal_missions&medal=" . ($_GET["medal"]+1) . "\">Next<br />Medal</a>
		</div>";
$strListHtml .= "
	<div class=\"mission button_back\">
		<a href=\"__!BASE_URI!__&action=medals\">Back to Medals</a>
	</div>";
$objTemplates->replace("__!mission_button_list!__", $strListHtml);
$objTemplates->replace("__!medal name!__", $arrMedalNames[$_GET["medal"]]);
$objTemplates->process(); // This applies generic replacements to the template
print $objTemplates->toString(); // Display the template
?>