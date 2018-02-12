<?PHP
if (VERBOSE)
	print "Validation passed<br>\n";
$objTemplates->load(template_enrollment_ladder); // Load a template
$objTemplates->process(); // This applies generic replacements to the template
$arrChapterGoals = $objNewUser->chapterGoals();
$intDaysRemaining = $objNewUser->procRemainingDays(); // Process the time remaining
$objNewUser->procLadderFromGoal();
$intYearsRemaining = ceil($intDaysRemaining / 365.25);
$objTemplates->replace("__!chapter_goal!__", $objNewUser->strDesiredChapterGoal);
$objTemplates->replace("__!JSON Ladder!__", $arrChapterGoals);
$objTemplates->replace("__!intLinesBeforeEnrollment!__", $objNewUser->intLinesBeforeEnrollment);
$objTemplates->replace("__!intLinesAfterEnrollment!__", $objNewUser->intLinesAfterEnrollment);
$objTemplates->replace("__!intYearsRemaining!__", $intYearsRemaining);
$objTemplates->replace("__!chapterOptionList!__", $objNewUser->chapterOptionList());
$objTemplates->replace("__!Form History Place Holder!__", serialize($_POST));
$objDBIHandle->open();
$strSql = "SELECT * FROM " . tanya_goals_table;
$objResult = $objDBIHandle->query($strSql);
$strLadderOptionList = "";
while ($objRow = mysql_fetch_assoc($objResult)) {
	$strLadderOptionList .= "<option value=\"{$objRow['id']}\"" . ($objNewUser->intLadderEstimation == $objRow['id'] ? " selected" : "") . ">Ladder {$objRow['id']}</option>";
}
$objDBIHandle->close();
$objTemplates->replace("__!User Ladder Option List!__", $strLadderOptionList);
for ($intItr=1;$intItr!=$intYearsRemaining+1;$intItr++) {
	$strYearsOptionList .= "<option value=\"$intItr\">Year $intItr</option>";
}
$objTemplates->replace("__!Remaining Years List!__", $strYearsOptionList);
$objTemplates->replace("__!FormData!__", serialize($objNewUser->toHash()));
print $objTemplates->toString(); // Display the template
?>