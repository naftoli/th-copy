<?php
require_once 'db.php';

$info = array();
$sql = "select * from date_tasks where date_tasks_mission_id in (
        select date_tasks_mission_id from  date_tasks_missions dtm 
        join date_tasks dt using (date_tasks_mission_id) 
        where dtm.subject_id = 41 
        and dtm.start_date >= 2458005
        and dt.grid_id in (11003,11004,11005,11006,11008,11010,11011,11012,11013,11014)
        and dtm.lang_id in (1,2)) 
        group by date_tasks_mission_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row['date_tasks_mission_id'];
}
echo "<pre>";
//print_r($info);
echo "</pre>";

$deleted['missions'] = 0;
$deleted['tasks'] = 0;

mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;
foreach ($info as $id) {
    $sql = "delete from date_tasks_missions where date_tasks_mission_id = " . $id;
    if (mysql_query($sql)) {
        $deleted['missions']++;
    } else {
        $success = false;
        break;
    }
    
    $sql = "delete from date_tasks where date_tasks_mission_id = " . $id;
    if ($res = mysql_query($sql)) {
        $deleted['tasks']++;
    } else {
        $success = false;
        break;
    }
}
if ($success) {
    echo "Success.<br />";
    mysql_query('commit');
} else {
    echo "Failed.<br />";
    mysql_query('rollback');
}
mysql_query('set autocommit=1');

echo "Missions deleted: " . $deleted['missions'] . "<br />";
echo "Tasks deleted: " . $deleted['tasks'];