<?php
/*
// redirection for ckids websites
if (
	preg_match("/^kids\.(.+)/", $_SERVER['HTTP_HOST'], $arrMatched)
	&& !(
		preg_match("/^\/(missionsapp|imgs|css|js|images|fonts|kiosk)\//", $_SERVER['REQUEST_URI'])
		|| preg_match("/^\/missionsapp$/", $_SERVER['REQUEST_URI'])
	)
) {
    header("Location: http://www.".$arrMatched[1]."/kids");
	exit;
}

if (
	(
		!isset($_SERVER['HTTPS'])
		|| $_SERVER['HTTPS'] == ""
	) && $_SERVER['HTTP_HOST'] == 'v2.mashpia.com'
) {
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location: $redirect");
	exit;
}
*/
function is_dev() {
	return 1;
}

define("dev", is_dev());
define("devel", FALSE);

if (0&&!dev)
{
	print text("We are down for maintenance, check back in a few minutes.");
	exit;
}

define('www', preg_match('/^www\./', $_SERVER['HTTP_HOST']) ? 'www.' : '');
define('secure',
	@$_SERVER['REQUEST_SCHEME'] == 'https' || 
	( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == "on" ? 's' : '' )
);
define('http', secure ? "https" : "http");

define("IMAGE_UPLOADER_DIRECTORY", "/home/mashpia/public_html/v2/images/uploads");
define("IMAGE_UPLOADER_URL", "//mashpia.com/v2/images/uploads");
//define("DEV_ENV", "staging");
define("DEV_ENV", strpos($_SERVER['HTTP_HOST'], "mashpia.com") === false ? "staging" : "production");
define("bp", "/v2");
define("images", bp . "/images");
define("imgs", bp . "/imgs/v");
define("js", bp . "/js");
define("css", bp . "/css");
define("WEB_ROOT", "/v2");
define("SERVER_ROOT", isset( $_SERVER['DOCUMENT_ROOT'] ) ?
	$_SERVER['DOCUMENT_ROOT']."/"."v2/"
	: "/home/mashpia/public_html/v2/"
);
define("MASTER_PASSWORD_X32G0SS8P", "9CXVC9h39tASDSt4h8ta4K9");
define("capture_start_date", mktime(0, 0, 0, 1, 6, 2013)); // school year start date
define("capture_end_date", mktime(23, 59, 59, 12, 27, 2013)); // goal date
define("date_milestone", "Chof Daled Teves");
define("date_hebrew_year", 5777);
define("serial_offset", 454321);
define("institution_offset", 1616161);
define("NO_PUSH", "NO_PUSH_KEY_81a2457FG69h42s");
define("legacy_link", TRUE);

$arrTemplateTypes = array(
	"Hebrew Schools" => "hebrewschool1",
	"Legacy Schools" => "schoolstemplate",
	"Camps" => "campstemplate"
);
$arrAppDetails = array(
	"tzivoshashem" => array(
		"name" => "Tzivos Hashem",
		'terminology' => 'School',
		'registration' => array(
			'kioskaccessories' => "kioskaccessories_chabadhebrewschool"
		),
		'default_institution_logo' => '/imgs/v/src/2157149_5970.jpg',
		'admin_emails' => array(
			'andyware@gmail.com',
			'CTH@tzivoshashem.org',
			'shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'shayausa@gmail.com'
		)
	),
	"releasedtime" => array(
		"name" => "Released Time",
		'terminology' => 'School',
		'default_institution_logo' => '/imgs/v/src/0.gif',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com'
		)
	),
	"friendshipcircle" => array(
		"name" => "Friendship Circle",
		'terminology' => 'School',
		'default_institution_logo' => '/imgs/v/src/0.gif',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com'
		)
	),
	"hoo" => array(
		"name" => "HOO",
		'terminology' => 'School',
		'default_institution_logo' => '/imgs/v/src/9725046_3404.jpg',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com'
		)
	),
	"school01" => array(
		"name" => "Schools",
		'terminology' => 'School',
		'default_institution_logo' => '/imgs/v/src/0.gif',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com'
		)
	),
	"hebrewschool1" => array(
		"name" => "Camps",
		'terminology' => 'Camp',
		'default_institution_logo' => '/imgs/v/src/3326716_6334.jpg',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com',
			'office@chabadchildren.com'
		)
	),
	"schoolstemplate" => array(
		"name" => "Legacy Schools",
		'default_institution_logo' => '/imgs/v/src/0.gif',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com'
		)
	),
	"schoolstemplate1" => array(
		"name" => "Legacy Schools",
		'default_institution_logo' => '/imgs/v/src/0.gif',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com'
		)
	),
	"campstemplate1" => array(
		"name" => "Camp Schools",
		'default_institution_logo' => '/imgs/v/src/0.gif',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com'
		)
	),
	"chabadhebrewschool" => array(
		"name" => "Chabad Hebrew School",
		'terminology' => 'School',
		'registration' => array(
			'kioskaccessories' => "kioskaccessories_chabadhebrewschool"
		),
		'default_institution_logo' => '/imgs/v/src/5461539_1916.png',
		'admin_emails' => array(
			'andyware@gmail.com',
			'thckids@chabadchildren.com',
			'naftolir@gmail.com',
			'shayausa@gmail.com',
			'chasid@gmail.com',
			'info@chabadchildren.com',
			'Shimmy@jcm.museum',
			'shamaichein@gmail.com',
			'help@chabadchildren.com',
			'office@chabadchildren.com'
		)
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
		"registration_fee" => 10
	)
);

$arrAdministrationEmails = array(
	'andyware@gmail.com',
	'thckids@chabadchildren.com',
	'naftolir@gmail.com',
	'shayausa@gmail.com',
	'chasid@gmail.com',
	'info@chabadchildren.com',
	'Shimmy@jcm.museum',
	'shamaichein@gmail.com'
);

$arrInstituionDetails = array(
	241 => array(
		'logout_link' => 'http://www.sbchabad.org/kids'
	),
	240 => array(
		'logout_link' => 'http://www.jewishsmonica.com/kids'
	),
	239 => array(
		'logout_link' => 'http://www.campsgi.com/kids'
	),
	238 => array(
		'logout_link' => 'http://www.jewishraleigh.org/kids'
	),
	237 => array(
		'logout_link' => 'http://www.chabadedmonton.org/kids'
	),
	230 => array(
		'logout_link' => 'http://www.cgibeachwood.com/kids'
	)
);

require "./application/bootstrap.php";
?>
