<?
require('slimDB.php'); 
chdir("../"); 

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
	$school_id = $_GET['school_id'];
	$class_id = $_GET['class_id'];
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$showDate = isset($_GET['showDate']) ? $_GET['showDate'] : 0;
	$dblSided = isset($_GET['dblSided']) ? $_GET['dblSided'] : 0;
} else {
	$school_id = 49;
	$class_id = 2906;
	$user_id = 8273;
	$start_date = 2457011;
	$end_date = 2457017;
	$showDate = 0;
	$dblSided = 1;
}

$heDates = array();
$heDatesDisp = array();
$temp = $start_date;
do {
	$he = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($temp, true, CAL_JEWISH_ADD_GERESHAYIM));
	$heArr = explode(' ', $he);
	$heDates[] = $heArr[0] . ' ' . $heArr[1];
	$heDatesDisp[] = $heArr[0];
} while (++$temp <= $end_date);

$sql = "select name from parshos where start = " . $start_date . " and end = " . $end_date;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$parsha = $row['name'];
} else {
	$parsha = '';
}

include("classes/user.php");
include("classes/user_track.php");
include("classes/school_class.php");
include("class.taskExceptions.php");
include("classes/date_tasks_mission.php");
include("classes/daily_task.php");
include("classes/weekly_task.php");
include("classes/shabbos_task.php");
include("classes/no_label_task.php");
include("classes/task.php");
include("classes/date_tasks_mark.php");

$users = array();
if ($user_id > 0) {
    $sql = "SELECT * FROM users WHERE user_id=" . $user_id;
} else {
if ($class_id > 0) {
    $sql = "SELECT * FROM users WHERE school_id=" . $school_id . " AND class_id=" . $class_id . " and user_registered > 0 order by last, first";
}
else {
    $sql = "SELECT * FROM users u 
            join classes c using (class_id) 
            WHERE u.school_id=" . $school_id . " 
            and u.user_registered > 0 
            order by c.class_grade, c.class_sub, u.last, u.first";
    }
}
$query = mysql_query($sql);

while ($row = mysql_fetch_assoc($query)) {
    $user = new user($row);
    $user->get_rank();
    $user->get_school_class();
    $user->get_user_tracks(-1, $start_date, $end_date);
    array_push($users, $user);
}
chdir("mission_report");
?>