<?
	if (
		isset($_POST)
		&& is_array($_POST)
		&& count($_POST)
		&& $_POST["ladder"]
		&& is_numeric($_POST["ladder"])
	) {
		$objTanya->objUserHandle->intEnrolled = 1;
		$objTanya->objUserHandle->intEnrolledDate = time();
		$objTanya->objUserHandle->setLinesBeforeEnrollment($_POST["chapter_offset"]);
		$objTanya->objUserHandle->setChapterGoal($_POST["ladder"], 1);
		$objTanya->objUserHandle->setLadder($objTanya->objUserHandle->procLadderFromGoal());
		//$objTanya->updateUser();
		$objTanya->insertUser();
		header("Location: " . BASE_URI . "&action=simulator");
	}
	$strSql = "
		SELECT
			`line`
		FROM
			" . tanya_chapters_table;
	$objDBIHandle->open();
	$objDBIHandle->query("SET NAMES 'utf8'");
	$objResult = $objDBIHandle->query($strSql);
	$strLadderOptionList = "";
	while ($objRow = mysql_fetch_assoc($objResult)) {
		$strLadderOptionList .= "<div>" . round($objRow['line'] / 416,2) . " Lines</div>";
	}
	$objTemplates->load(template_demo_enrollment_form); // Load a template
	$objTemplates->process(); // This applies generic replacements to the template
	$objTemplates->replace("__!User Ladder Option List!__", $strLadderOptionList);
	$objDBIHandle->close();
	print $objTemplates->toString(); // Display the template
?>
