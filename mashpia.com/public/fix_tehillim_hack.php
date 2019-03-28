<?php
require 'db.php';

$info = array();
$sql = "select dtmm.* from mashpiadb_sf01220566.date_tasks_marks dtmm 
        join date_tasks dt using (date_task_id)
        join date_tasks_missions dtm using (date_tasks_mission_id) 
        where dt.grid_id in (8001,8002)
        and dtm.start_date = 2458013 
        and dtm.end_date = 2458013";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

//echo "<pre>";
//print_r($info);
//echo "</pre>";

$updated = 0;
foreach ($info as $row) {
    $sql = "update date_tasks_marks
            set done_qty = " . $row['done_qty'] . ",
            mark_quantity = " . $row['mark_quantity'] . " 
            where date_task_id = " . $row['date_task_id'] . "
            and user_id = " . $row['user_id'];
    if (mysql_query($sql)) {
        $updated++;
    } else {
        echo $sql . "<br />" . mysql_error() . "<br />";
    }
}
echo "Updated: " . $updated;