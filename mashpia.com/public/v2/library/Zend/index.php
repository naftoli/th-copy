<?php
define("IMAGE_UPLOADER_DIRECTORY", "/home/mashpia2/public_html/images/uploads");
define("IMAGE_UPLOADER_URL", "http://mashpia2.icorpa.com/images/uploads");
//define("DEV_ENV", "staging");
define("DEV_ENV", "production");
define("WEB_ROOT", "http://mashpia2.icorpa.com/");
define("SERVER_ROOT", "/home/mashpia2/public_html/");
define("MASTER_PASSWORD_X32G0SS8P", "9CXVC9h39tASDSt4h8ta4K9");
define("capture_start_date", mktime(0, 0, 0, 4, 3, date("Y"))-1); // school year start date
define("capture_end_date", mktime(23, 59, 59, 6, 23, date("Y"))); // goal date
define("date_milestone", "Gimmul Tamuz");
define("serial_offset", 454321);
define("institution_offset", 1616161);

$arrTemplateTypes = array(
	"Hebrew Schools" => "hebrewschool1",
	"Hebrew Schools" => "hebrewschool2",
	"Legacy Schools" => "schoolstemplate",
	"Camps" => "campstemplate"
);
$arrAppDetails = array(
	"hebrewschool1" => array(
		"name" => "Camps"
	),
	"hebrewschool2" => array(
		"name" => "Hebrew Schools"
	),
	"schoolstemplate" => array(
		"name" => "Legacy Schools"
	),
	"campstemplate1" => array(
		"name" => "Camp Schools"
	)
);

// Global variables
$arrUserTypes = array(
	"super" => "Super Administrator",
	"admin" => "Institution Administrator",
	"teacher" => "Teacher",
	"parent" => "Parent",
	"student" => "Student"
);

$arrConfirmationCodePrefixes = array(
	"Registration" => "A",
	"ID Cards" => "B",
	"Sutdent Registration" => "C"
);

$arrSystemPrices = array(
	"hebrewschool1" => array(
		"registration_fee" => 5
	)
);

require "./application/bootstrap.php";
?>
