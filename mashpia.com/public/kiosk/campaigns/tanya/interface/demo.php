<?PHP
	DEFINE("BASE", "/home/mashpia/public_html/kiosk/campaigns/tanya");
	DEFINE("VERBOSE", 0); // Does not scale to the classes unless specified
	DEFINE("BASE_URI", "camp_goal.php?subject=" . $_GET["subject"] . "&i=" . $_GET["i"]);

	// Load useful files
	require_once(BASE . "/config.php");
	require_once(BASE . "/../classes/class.DBI.php");
	require_once(BASE . "/../classes/class.Templates.php");
	require_once(BASE . "/source/class.Tanya.php");

	$objDBIHandle = new DBI(VERBOSE);
	$objTemplates = new Templates(VERBOSE); // New template handle
	$objTanya = new Tanya(0); // New tanya object handle
	$objTanya->loadUser(); // Load a user id into the object handler (if a valid & existing id is provided)
	if (VERBOSE)
		print "User `{$_GET["user"]}` is loaded<br>\n";
	if (VERBOSE)
		print "Name: " . $objTanya->objUserHandle->strFirstName . " " . $objTanya->objUserHandle->strLastName . "<br>\n";
	if (isset($_GET["action"])) {
		if ($_GET["action"] == "enroll") {
			$objTanya->objUserHandle->enrollUser(1);
			header("Location: " . path);
		} else if ($_GET["action"] == "unenroll") {
			$objTanya->objUserHandle->enrollUser(0);
			header("Location: " . path);
		}
		if ($_GET["action"] == "overview") {
			require("demo_overview.php");
		} else if ($_GET["action"] == "simulator") {
			require("demo_simulator.php");
		} else if ($_GET["action"] == "mission_entry") {
			require("demo_mission_entry.php");
		} else if ($_GET["action"] == "enrollment_form") {
			if ($objTanya->objUserHandle->intEnrolledDate) {
				header("Location: " . path . "&action=enroll");
			} else {
				require("demo_enrollment_form2.php");
			}
		} else if ($_GET["action"] == "medals") {
			require("demo_medals.php");
		} else if ($_GET["action"] == "medal_missions") {
			require("demo_medal_missions.php");
		} else if ($_GET["action"] == "medal_missions_ajax1") {
			require("demo_medal_missions_ajax1.php");
		} else if ($_GET["action"] == "medal_missions_ajax2") {
			require("demo_medal_missions_ajax2.php");
		} else if ($_GET["action"] == "medal_tasks") {
			require("demo_medal_tasks.php");
		}
	} else {
		require("demo_entry_point.php");
	}
?>