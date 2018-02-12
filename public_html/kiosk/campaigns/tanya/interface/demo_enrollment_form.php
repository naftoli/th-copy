<?PHP
$objTemplates->load(template_enrollment_form); // Load a template
$objTemplates->process(); // This applies generic replacements to the template
$objTemplates->replace("__!Form Validation!__",
	strlen($strValidation)
	? "<div style=\"color:red;font-weight:bold\">$strValidation</div>&nbsp;<br>"
	: ""
);
$objTemplates->replace("__!first_name!__", $objNewUser->strFirstName);
$objTemplates->replace("__!last_name!__", $objNewUser->strLastName);
if (isset($objNewUser->intBirthDate) && $objNewUser->intBirthDate > 0) {
	$objTemplates->replace("__!birth_date!__", date("d/m/y", $objNewUser->intBirthDate));
} else {
	$objTemplates->replace("__!birth_date!__", date("d/m/y", time() - round(8 * 365.25 * 24 * 60 * 60)));
}
print $objTemplates->toString(); // Display the template
?>