<?PHP
$objMissions = new TanyaMissions($objTanya->objUserHandle, 0);
$arrMissionRanges = $objMissions->missionEntryRange(
	6, // Number of pages to scale
	1, // Include lines
	0  // Verbose
);
if (
	isset($_POST)
	&& is_array($_POST)
	&& count($_POST)
) {
	$arrChecked = array();
	$arrPostChecked = array();
	foreach ($_POST as $strKey => $strValue) {
		if (preg_match("/^line\-([_0-9]+)\-([0-9]+)$/", $strKey, $arrMatched)) {
			$arrMissions = split("_", $arrMatched[1]);
			for ($intMission=$arrMissions[0];$intMission!=(isset($arrMissions[1]) ? $arrMissions[1]+1 : $arrMissions[0]+1);$intMission++) {
				$arrChecked[1][$intMission][] = $arrMatched[2];
				$arrPostChecked[$intMission][$arrMatched[2]-1] = 1;
			}
		}
	}
	foreach ($arrMissionRanges["design"] as $arrMission) {
		$intMission=$arrMission["mission_start"];
		for ($intTask=$arrMission["line_start"];$intTask!=$arrMission["line_start"]+$arrMission["line_count"];$intTask++) {
			if (!isset($arrPostChecked[$intMission][$intTask])) {
				$arrChecked[0][$intMission][] = $intTask+1;
			}
		}
	}
	//var_dump($arrChecked);
	$objMissions->mergeTasks($arrChecked);
	$objMissions->mergeMissions($arrMissionRanges["design"]);
	//header("Location: " . path . "&action=mission_entry");exit;
	$arrMissionRanges = $objMissions->missionEntryRange(
		6, // Number of pages to scale
		1, // Include lines
		0  // Verbose
	);
}
$arrChecked = $objMissions->arrTasks($arrMissionRanges["design"]);
// Loop through the mission data and output the line insertion interface
$strPages = "";
//var_dump($arrMissionRanges["design"]);
foreach ($arrMissionRanges["design"] as $intMission => $arrMission) {
	$strMissionTitle =
		$arrMission["mission_start"] == $arrMission["mission_end"]
		?
			$arrMission["mission_start"] . " (" . date("M jS, Y", $arrMission["mission_dates"][0]) . ")"
		:
			$arrMission["mission_start"] . " - " . $arrMission["mission_end"]
				. " (" . date("M jS, Y", $arrMission["mission_dates"][0])
				. " - " . date("M jS, Y", $arrMission["mission_dates"][1]) . ")"
	;
	$strLinesHTML = "";
	for ($intItr=$arrMission["line_start"]; $intItr!=$arrMission["line_start"]+$arrMission["line_count"]; $intItr++) {
		$strLinesHTML .= "
			<div class=\"question icon_book\">
				<div class=\"checkbox\"><input type=\"checkbox\" value=\"1\" name=\"line-"
					. (
						$arrMission["mission_start"] == $arrMission["mission_end"]
						? $arrMission["mission_start"]
						: $arrMission["mission_start"] . "_" . $arrMission["mission_end"]

					)
					. "-" . ($intItr+1) . "\""
					. (
						isset($arrChecked[$arrMission["mission_start"]][$intItr+1])
						? " checked"
						: ""
					)
					. " /></div>
				<p>" . $arrMissionRanges["lines"][$intItr]["Text"] . "</p>
				<div class=\"mission_quota miles\">Perek: " . $arrMissionRanges["lines"][$intItr]["Perek"] . " Page: " . $arrMissionRanges["lines"][$intItr]["Page"] . " Line: " . ($intItr+1) . "</div>
			</div>
		";
	}
	$strMission = '
		<li>
			<div class="slider_title">Mission: ' . $strMissionTitle . '</div>
			<div class="scroll-pane">
				<div class="boxes mainbox">
					<div class="title">Ladder: ' . $objTanya->objUserHandle->intLadder . '</div>
					' . $strLinesHTML . '
					<div class="button button_icons">
						<div><a href="#" class="icon_save" onclick="document.form01.submit();">Submit</a></div>
					</div>
				</div>
			</div>
		</li>
	';
	$strPages .= $strMission;
}
$objTemplates->load(template_mission_entry); // Load a template
$objTemplates->replace("__!Mission Pages!__", $strPages);
$objTemplates->process();
print $objTemplates->toString(); // Display the template
?>