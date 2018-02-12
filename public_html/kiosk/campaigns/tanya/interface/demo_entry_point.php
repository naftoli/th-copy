<?
	$objTemplates->load(template_demo_entry_point); // Load a template
	$objTemplates->replace("__!User Id!__", $objTanya->objUserHandle->intTableID);
	if (!$objTanya->objUserHandle->intEnrolled) {
		$strEnrollmentButton = '<li><a href="__!BASE_URI!__&action=enrollment_form" class="icon_enroll">Enroll</a></li>';
	} else {
		$strEnrollmentButton = '<li><a href="__!BASE_URI!__&action=unenroll" class="icon_enroll">Unenroll</a></li>';
	}
	$objTemplates->replace("__!Enrollment button!__", $strEnrollmentButton);
	if (!$objTanya->objUserHandle->intEnrolled) {
		$objTemplates->preg("/__!<Enrolled>!__.+?__!<\/Enrolled>!__/", "");
	} else {
		$objTemplates->preg("/__!<\/?Enrolled>!__/", "");
	}
	if ($objTanya->objUserHandle->intEnrolled) {
		$objTemplates->preg("/__!<Not Enrolled>!__.+?__!<\/Not Enrolled>!__/", "");
	} else {
		$objTemplates->preg("/__!<\/?Not Enrolled>!__/", "");
	}
	$objTemplates->process(); // This applies generic replacements to the template
	print $objTemplates->toString(); // Display the template
?>