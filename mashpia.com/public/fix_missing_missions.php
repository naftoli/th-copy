<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);
require 'db.php';

$start = isset($_GET['num']) ? $_GET['num'] : 0;
$limit = 100;
$start *= $limit;

$qrys = array();
$sql = "select * from mission_marks_backup limit $start, $limit";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
    $sql = "select * from date_tasks_mission_marks
            where date_tasks_mission_id = " . $row['date_tasks_mission_id'] . "
            and user_id = " . $row['user_id'];
    //echo $sql . "<br />";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) == 0) {
        $sql2 = "insert into date_tasks_mission_marks
                set user_id = " . $row['user_id'] . ",
                date_tasks_mission_id = " . $row['date_tasks_mission_id'] . ",
                subject_id = " . $row['subject_id'] . ",
                mission_value = " . $row['mission_value'] . ",
                mission_name = \"" . mysql_real_escape_string($row['mission_name']) . "\",
                mark_date = " . $row['mark_date'] . ",
                mark_override = " . $row['mark_override']  .",
                mission_updated = " . $row['missions_updated'];
        $qrys[] = $sql2;
    }
}
echo "Done";
echo "<pre>";
print_r($qrys);