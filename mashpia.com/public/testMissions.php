<?php
require 'db.php';
$user_id = 20923;
$start = 2457872;
$end = 2457878;

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

$lang = 1;
$sql = "SELECT * FROM users WHERE user_id = " . $user_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$lang = $row['lang_id'];
$user = new user($row);
$user->get_rank();
$user->get_school_class();
$user->get_user_tracks( -1, $start, $end, array(), $lang );

$numLabels = count($user->daily_labels) + count($user->weekly_labels) + count($user->shabbos_labels) + count($user->no_label_subjects);
echo $numLabels . "<br />";
$tracks = $user->user_tracks;
$numTasks = 0;
$types = array('daily_tasks', 'weekly_tasks', 'shabbos_tasks', 'no_label_tasks');
foreach ($tracks as $track) {
    foreach ($types as $type) {
        echo "Track: " . $track->subject_id . " - " . $type . ':' . count($track->$type) . "<br />";
        $numTasks += count($track->$type);
    }
    echo $numTasks . "<br />";
    echo "<br />";
}
$totalRows = (floor($numLabels / 2)) + $numTasks;
echo $totalRows;
        
echo "<pre>"; print_r($user); echo "</pre>"; exit;