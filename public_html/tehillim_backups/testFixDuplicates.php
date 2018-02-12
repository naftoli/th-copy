<?php
ini_set('display_errors',1);
require '../db.php';

$mark_date = 2458132;
$info = array();
$sql = "select * from date_tasks_marks dtm
        join date_tasks dt using (date_task_id) 
        where dt.grid_id in (8001,8002) 
        and mark_date = " . $mark_date;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $task_id = $row['date_task_id'];
    $user_id = $row['user_id'];
    $done_qty = $row['done_qty'];
    $grid_id = $row['grid_id'];
    $info[$user_id][$task_id][$grid_id][] = $done_qty;
}
//echo "<pre>"; print_r($info); echo "</pre>";

$qrys = array();
foreach ($info as $user_id => $other) {
    foreach ($other as $task_id => $more) {
        foreach ($more as $grid_id => $marks) {
            $qry = '';
            $qry2 = '';
            // get highest mark
            rsort( $marks );
            $done_qty = $marks[0];
            
            // if there's more than one mark in the system
            if (count($marks) > 1) {
                // delete all marks
                $qrys[] = "delete from date_tasks_marks
                            where date_task_id = " . $task_id . "
                            and user_id = " . $user_id . "
                            and mark_date = " . $mark_date;
                
                // insert correct mark
                $qrys[] = "insert into date_tasks_marks
                            set date_task_id = " . $task_id . ",
                            user_id = " . $user_id . ",
                            mark_date = " . $mark_date . ",
                            done_qty = " . $done_qty . ",
                            mark_points = 0.5";
            }
        }
    }
}
echo "<pre>"; print_r( $qrys ); echo "</pre>";