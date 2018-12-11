<?php
require 'db.php';

$info = array();
$sql = "select * from date_tasks_mission_marks where subject_id = 1 and mark_date > 2457941";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

echo "<pre>";
foreach ($info as $row) {
    $sql = "select date_task_id from date_tasks where date_task_mission_id = " . $row['date_tasks_mission_id'] . " and ord = 1";
    $result = mysql_query($sql);
    $row2 = mysql_fetch_assoc($result);
    
    $taskSql = "select * from date_tasks_marks where user_id = " . $row['user_id'] . " and date_task_id = " . $row2['date_task_id'];
    $taskRes = mysql_query($taskSql);
    $taskRow = mysql_fetch_assoc($taskRes);
    if ($taskRow['done_qty'] < $taskRow['mark_quantity']) {
        print_r($taskRow);
    }
}
echo "</pre>";
echo "done";