<?
$VERBOSE = 0;
$objTemplates->load(template_demo_medals); // Load a template
$objMissions = new TanyaMissions($objTanya->objUserHandle, $VERBOSE);
$intMission = $objMissions->loadNewestMission();
$intMissionEnd = $objMissions->procAvailableMission();
$strHtml = "";
for ($intItr=1;$intItr!=11;$intItr++) {
	if ($arrMedalData[$intItr] <= $intMissionEnd)
		$strHtml .= "
			<div"
			. (
				$arrMedalData[$intItr] <= $intMission
				? " class='active'><div class='check_on'></div>"
				: ">"
			) . "
				<A HREF=\"__!BASE_URI!__&action=medal_missions&medal=$intItr\"><IMG SRC='/file_view.php?id={$arrMedalData[$arrMedalData[$intItr]]}' WIDTH='96' HEIGHT='100'  ALT='medal $intItr'></A>
			</div>";
}
$objTemplates->replace("__!Medal Page!__", $strHtml);
$objTemplates->process(); // This applies generic replacements to the template
print $objTemplates->toString(); // Display the template
?>