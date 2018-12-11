<?
$admin_auth = array('user');
require('header.php'); 

$d = unixtojd();
$day = date("N");
$end = $d;

switch ($day) {
    case 1:
        $end += 6;
        break;
    case 2:
        $end += 5;
        break;
    case 3:
        $end += 4;
        break;
    case 4:
        $end += 3;
        break;
    case 5:
        $end += 2;
        break;
    case 6:
        $end++;
        break;
    case 7:
        break;
    default:
        break;
}
if ($page = 'print') {
	$start = ($end - 34); //end is shabbos so start is 6 days back for sunday plus 4 weeks
} else if ($page == 'mark') {
	$start = ($end - 48); //end is shabbos so start is 6 days back for sunday plus 6 weeks
}
$report_start_date = ($end - 7);

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \camps\classes\admin($row);
//$admin->get_children();
$admin->get_markable_children();
$children = array();
foreach ($admin->children as $child) {
	//filter out children with no school/class id
	if (!empty($child->school_id) && !empty($child->class_id)) {
		$children[] = $child;
	}
}

$days_of_the_week = array("M", "T", "W", "T", "F", "ש", "S");	

$selected_dates = "";
$action = "";
$subject_id = -1;

//include("classes/user.php");
include("classes/user_track.php");
include 'class.taskExceptions.php';
include("classes/date_tasks_mission.php");
include("classes/daily_task.php");
include("classes/weekly_task.php");
include("classes/shabbos_task.php");
include("classes/no_label_task.php");
include("classes/task.php");
include("classes/date_tasks_mark.php");

if (isset($_POST['date_list'])) {		
	$date_list = explode(":", $_POST['date_list']);
	$start_date = $date_list[0]; 
	$end_date = $date_list[1];
	$selected_dates = $start_date . ":" . $end_date;
}
else {
	$start_date = $report_start_date;
	$end_date = $end;
}

include("classes/report.php");
$reports = array();
if ($page = 'print') {
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date >= " . $start . " ORDER BY start_date";	
} else if ($page == 'mark') {
	$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' AND start_date >= " . $start . " and end_date <= " . $end . " ORDER BY start_date";		
}
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$report = new report($row);
	//hide pesach
	if ($report->start_date == 2456376) continue;
	array_push($reports, $report);
	if ($selected_dates == "") {
		$selected_dates = $row['start_date'] . ":" . $row['end_date'];				
	}
}

$users = array();
foreach ($children as $child) {
	if (isset($_POST['child_id']) && $_POST['child_id'] > 0) {
		if ($_POST['child_id'] != $child->user_id) {
			continue;
		}
	}
	$sql = "SELECT * FROM users WHERE user_id=" . $child->user_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$user = new user($row);
	$user->get_school();
	$school_id = $user->school->school_id;
	$user->get_school_class();		
	$user->get_rank();
	$user->get_user_tracks($subject_id, $start_date, $end_date);
	$users[] = $user;
}
?>