<?PHP
$objTemplates->load(template_user_home); // Load a template
$objTemplates->process(); // This applies generic replacements to the template
print $objTemplates->toString(); // Display the template
?>