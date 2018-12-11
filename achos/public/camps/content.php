<?
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

error_reporting (E_ALL ^ E_NOTICE);
$output = $_GET['output'];

function getJson($action,$params) {
	$path = 'http://' . $_SERVER['SERVER_NAME'] . dirname(dirname($_SERVER['PHP_SELF']));
	$file_name = $path . "camps/appInterface.php?action=" . $action . "&params=" . $params;
	$json = file_get_contents($file_name);
	$json_data = json_decode($json, true);
	return $json_data;
}

switch ($output) {

	case "overlay_assign_staff":
		include("includes/overlay_assign_staff.php");
	break;

	case "campers_register":
		include("includes/campers_register.php");
	break;

	case "mission_sheets":
		include("includes/mission_sheets.php");
	break;

	case "group_marking":
		include("includes/group_marking.php");
	break;

	case "group_points":
		include("includes/group_points.php");
	break;

	case "overlay_group_tasks":
		include("includes/overlay_group_tasks.php");
	break;

	case "group_missions":
		include("includes/group_missions.php");
	break;

	case "missions_dash":
		include("includes/missions_dash.php");
	break;

	case "missions":
		include("includes/missions.php");
	break;

	case "storescan":
		include("includes/store_scan.php");
	break;

	case "manage_prizes":
		 include("includes/manage_prizes.php");
	break;

	case "overlay_add_prize":
		 include("includes/overlay_add_prize.php");
	break;

	case "overlay_tasks":
		 include("includes/overlay_tasks.php");
	break;

	case "store":
		include("includes/store.php");
	break;

	case "my_profile":
		include("includes/my_profile.php");
	break;

	case "prizeadd":
		include("includes/prize_add.php");
	break;

	case "print_rank_cards":
		include("includes/print_rank_cards.php");
	break;

	case "rankcards":
		include("includes/rank_cards.php");
	break;

	case "overlay_tasks":
		 include("includes/overlay_tasks.php");
	break;

	case "gettingstarted":
		include("includes/setup_group_types.php");
	break;

	case "gettingstarted2":
		include("includes/setup_divisions.php");
	break;

	case "gettingstarted3":
		include("includes/setup_groups.php");
	break;

	case "gettingstarted4":
		include("includes/setup_campaigns.php");
	break;

	case "gettingstarted5":
		include("includes/setup_campaigns.php");
	break;

	case "gettingstarted6":
		include("includes/setup_prizes.php");
	break;

	case "gettingstarted5":
		include("includes/setupmissions.php");
	break;

	case "campers_list":
		include("includes/campers_list.php");
	break;

	case "staff_list":
		include("includes/staff_list.php");
	break;

	case "camper_profile":
		include("includes/camper_profile.php");
	break;

	case "groups":
		include("includes/groups.php");
	break;

	case "staff_profile":
		include("includes/staff_profile.php");
	break;

	case "overlay_assign_campers":
		include("includes/overlay_assign_campers.php");
	break;

	case "home":
		include("includes/home.php");
	break;

	case "campers":
		include("includes/campers.php");
	break;

	case "camperadd":
		include("includes/camper_add.php");
	break;

	case "staffadd":
		include("includes/staff_add.php");
	break;

	case "camperbulk":
		include("includes/camper_bulk.php");
	break;

	case "camperregister":
		include("includes/camper_register.php");
	break;

	case "campprofile":
		include("includes/camp_profile.php");
	break;

	case "grouptypes":
		include("includes/group_types.php");
	break;

	case "divisions":
		include("includes/divisions.php");
	break;

	case "group":
		include("includes/group.php");
	break;

	case "staff":
		include("includes/staff.php");
	break;

	case "staffbulk":
		include("includes/staff_bulk.php");
	break;

	case "userlist":
		include("includes/userlist.php");
	break;

	case "staff_member":
		include("includes/profile.php");
	break;

	case "camper":
		include("includes/profile.php");
	break;

	case "storeprint":
		include("includes/storeprint.php");
	break;

	case "points":
		include("includes/points.php");
	break;

	case "marking":
		include("includes/marking.php");
	break;

	case "gstarted":
		include("includes/gettingstarted.php");
	break;

	case "groupsoverlay":
		include("includes/overlay_groups_two.php");
	break;

	case "staff_overlay":
		include("includes/staff_overlay.php");
	break;

	case "customoverlay":
		include("includes/overlay_custom.php");
	break;

	/* tanya */

	case "approvals":
		include("includes/approvals.php");
	break;

	case "approvals_pending_missions":
		include("includes/approvals_pending_missions.php");
	break;

	case "approvals_ladder_upgrades":
		include("includes/approvals_ladder_upgrades.php");
	break;

	case "approvals_pending_missions_icorpa":
		include("includes/approvals_pending_missions_icorpa.php");
	break;

	case "tanya_backdate_temp":
		include("includes/tanya_backdate_temp.php");
	break;

	case "tanya_report_temp":
		include("includes/tanya_report_temp.php");
	break;

	/* end of tanaya */

	default:
?>
			<div class="slider">
				<div class="col_title"><span>Error</span><a class="slider_back">back</a></div>
			</div>

<?
	break;
}
?>