<?PHP
$objTemplates->load(template_home); // Load a template
$objTemplates->process(); // This applies generic replacements to the template
$objDBIHandle->open();
// Add user id option elements to the template
$strSql = "SELECT `id`, `first_name`, `last_name` FROM " . tanya_user_table;
$objResult = $objDBIHandle->query($strSql);
$strUserOptionList = "";
while ($objRow = mysql_fetch_assoc($objResult)) {
	$strUserOptionList .= "<option value=\"" . $objRow["id"] . "\">{$objRow["first_name"]} {$objRow["last_name"]}</option>";
}
$objDBIHandle->close();
$objTemplates->replace("__!User Options List!__", $strUserOptionList); // Apply content
print $objTemplates->toString(); // Display the template
?>