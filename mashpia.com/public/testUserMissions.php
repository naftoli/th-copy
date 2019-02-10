<?php
$admin_auth = array('user');
require('header.php');

include("classes/user.php");
include("classes/user_track.php");
include 'class.taskExceptions.php';
include("classes/date_tasks_mission.php");
include("classes/daily_task.php");
include("classes/weekly_task.php");
include("classes/shabbos_task.php");
include("classes/no_label_task.php");
include("classes/task.php");
include("classes/date_tasks_mark.php");

$user_id = $_GET['id'];
$start = $_GET['start'];
$end = $_GET['end'];

$sql = "SELECT * FROM users WHERE user_id = " . $user_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);
$user->get_school();
$school_id = $user->school->school_id;
$user->get_school_class();		
$user->get_rank();
$user->get_user_tracks(-1, $start, $end);
echo "<pre>";
print_r($user);
echo "</pre>";
?>