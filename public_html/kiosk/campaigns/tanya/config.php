<?PHP
// Template generic content
DEFINE ("path", "./camp_goal.php?subject=" . $_GET["subject"] .  "&i=" . $_GET["i"]);

// Database table associations
DEFINE ("tanya_user_table", "mashpia.demo_tanya_users");
DEFINE ("tanya_lines_table", "mashpia.demo_tanya_lines");
DEFINE ("tanya_goals_table", "mashpia.demo_tanya_goals");
DEFINE ("tanya_medals_table", "mashpia.demo_tanya_medals");
DEFINE ("tanya_chapters_table", "mashpia.demo_tanya_chapters");
DEFINE ("tanya_missions_table", "mashpia.demo_tanya_missions");
DEFINE ("tanya_tasks_table", "mashpia.demo_tanya_tasks");
DEFINE ("tanya_requests_table", "mashpia.demo_tanya_requests");

// MySql authentication
DEFINE ("tanya_db_host", "localhost");
DEFINE ("tanya_db_user", "mashpia");
DEFINE ("tanya_db_pass", "eZauPhy9CEqEdYDT");

// Template paths
DEFINE ("template_blank", BASE . "/templates/blank.tpl");
DEFINE ("template_wrapper", BASE . "/templates/CONTENT_WRAPPER.tpl");
DEFINE ("template_home", BASE . "/templates/home.tpl");
DEFINE ("template_enrollment_form", BASE . "/templates/enrollment_form.tpl");
DEFINE ("template_enrollment_ladder", BASE . "/templates/enrollment_ladder.tpl");
DEFINE ("template_user_home", BASE . "/templates/user_home.tpl");
DEFINE ("template_user_simulator", BASE . "/templates/demo_simulator.tpl");
DEFINE ("template_user_overview", BASE . "/templates/demo_overview.tpl");
DEFINE ("template_mission_entry", BASE . "/templates/mission_entry.tpl");
DEFINE ("template_demo_entry_point", BASE . "/templates/demo_entry_point.tpl");
DEFINE ("template_demo_enrollment_form", BASE . "/templates/demo_enrollment_form.tpl");
DEFINE ("template_demo_medals", BASE . "/templates/demo_medals.tpl");
DEFINE ("template_demo_medal_missions", BASE . "/templates/demo_medal_missions.tpl");
DEFINE ("template_demo_medal_tasks", BASE . "/templates/demo_medal_tasks.tpl");
DEFINE ("template_demo_medal_missions_ajax1", BASE . "/templates/demo_medal_ajax_1.tpl");
DEFINE ("template_demo_medal_missions_ajax2", BASE . "/templates/demo_medal_ajax_2.tpl");
DEFINE ("template_user_overview_medals_ajax", BASE . "/templates/demo_overview_medals_ajax.tpl");


// Math
DEFINE ("real_week", 7.019230769230769);
DEFINE ("real_week2", 7.024038461538462);

// Hard coded gangster stuff (really just trying to save time cause im not going to sleep tonight to hit the deadline)

$arrMedalData = array(
	15 => 1172604632,
	35 => 382230352,
	60 => 2688305289,
	90 => 3704900934,
	125 => 1869654345,
	165 => 2528536349,
	210 => 2738751549,
	260 => 1813181537,
	315 => 2080209677,
	375 => 128460476,
	0 => 0,
	1 => 15,
	2 => 35,
	3 => 60,
	4 => 90,
	5 => 125,
	6 => 165,
	7 => 210,
	8 => 260,
	9 => 315,
	10 => 375,
	11 => 416 // tanya mission end
);
$arrMedalNames = array(
	1 => "White",
	2 => "Red",
	3 => "Orange",
	4 => "Yellow",
	5 => "Green",
	6 => "Blue",
	7 => "Purple",
	8 => "Brown",
	9 => "Gray",
	10 => "Black"
);
?>