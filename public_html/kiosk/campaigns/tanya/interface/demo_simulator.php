<?PHP
if (
	isset($_POST)
	&& is_array($_POST)
	&& count($_POST)
	&& $_POST["line_goal"]
	&& $_POST["ladder"]
	&& is_numeric($_POST["line_goal"])
	&& is_numeric($_POST["ladder"])
) {

	//$objTanya->objUserHandle->setChapterGoal($_POST["line_goal"]);
	//$objTanya->objUserHandle->setLadder($_POST["ladder"]);
	//$objTanya->updateUser();
	$intElapsed = $objTanya->objUserHandle->getEnrolledElapsedDays();
	$intRequestKey = $objTanya->objUserHandle->setLadderRequest($_POST["line_goal"], $_POST["ladder"]);
	if ($intElapsed <= 30) {
		// Use the ajax approval file to save time
		$strResult = file_get_contents("http://www.mashpia.com/camps/content.php?output=approvals_ladder_upgrades&request_id=" . $intRequestKey);
		if ($strResult != "") {
			if (VERBOSE)
				die("approvals_ladder_upgrades fail: $strResult");
		}
	}
	header("Location: " . path);
}

$objTemplates->load(template_user_simulator); // Load a template
$objTemplates->process(); // This applies generic replacements to the template
$objMission = new TanyaMissions($objTanya->objUserHandle);
$intDaysRemaining = $objMission->procRemainingDays(); // Process the time remaining
$intYearsRemaining = ceil($intDaysRemaining / 365.25);
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
$strSql = "SELECT * FROM " . tanya_goals_table;
$objResult = $objDBIHandle->query($strSql);
$strLadderOptionList = "";
while ($objRow = mysql_fetch_assoc($objResult)) {
	$strLadderOptionList .= "<div"
		. ($objTanya->objUserHandle->intLadder == $objRow['id'] ? " class=\"active\"" : "")
		. ">Ladder {$objRow['id']}</div>";
}
$objDBIHandle->close();
$objTemplates->replace("__!User Ladder Option List!__", $strLadderOptionList);
$strYearsOptionList = "";
for ($intItr=1;$intItr!=$intYearsRemaining+1;$intItr++) {
	$strYearsOptionList .= "<div"
		. ($intItr == $intYearsRemaining ? " class=\"active\"" : "")
		. ">Age " . (13-($intYearsRemaining-$intItr)) . "</div>";
}
$objTemplates->replace("__!Remaining Years List!__", $strYearsOptionList);
print $objTemplates->toString(); // Display the template
?>