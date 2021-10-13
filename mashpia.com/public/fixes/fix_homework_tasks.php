<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require '../header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$i = 0;
$start = 2459581;
$end = $start + 6;
do {
    $sql = "update date_tasks dt 
            join date_tasks_missions dtm using (date_tasks_mission_id) 
            set dtm.end_date = $end
            where dtm.start_date = $start 
            and dtm.end_date = $start
            and dt.short_name = 'Homework'";
    echo $sql;
    $result = mysql_query($sql);
    $start = $end + 1;
    $end = $start + 6;
    $i++;
} while ($end <= 2459852);

echo "updated $i weeks.";