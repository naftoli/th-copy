<?php
require '../db.php';

$mark_date = 2458104;
$info = array();
$sql = "select * from mashpia_backup.date_tasks_marks dtm
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
            
            $sql = "select * from date_tasks_marks
                    where date_task_id = " . $task_id . "
                    and user_id = " . $user_id . "
                    and mark_date = " . $mark_date;
            $result = mysql_query( $sql );
            if (mysql_num_rows($result)) {
                $row = mysql_fetch_assoc( $result );
                if ($row['done_qty'] < $done_qty) {
                    $qry = "update date_tasks_marks
                            set done_qty = " . $done_qty . "
                            where date_task_id = " . $task_id . "
                            and user_id = " . $user_id . "
                            and mark_date = " . $mark_date;
                }
            } else {
                $qry = "insert into date_tasks_marks
                        set date_task_id = " . $task_id . ",
                        user_id = " . $user_id . ",
                        mark_date = " . $mark_date . ",
                        done_qty = " . $done_qty . ",
                        mark_points = 0.5";
            }
            if (!empty($qry)) $qrys[] = $qry;
            
            // update backup table
            $sql = "select * from tehillim_backups 
                    where date_task_id = " . $task_id . "
                    and user_id = " . $user_id . "
                    and mark_date = " . $mark_date;
            $result = mysql_query( $sql );
            if (mysql_num_rows($result)) {
                $row = mysql_fetch_assoc( $result );
                if ($row['done_qty'] < $done_qty) {
                    $qry2 = "update tehillim_backups 
                            set done_qty = " . $done_qty . "
                            where date_task_id = " . $task_id . "
                            and user_id = " . $user_id . "
                            and mark_date = " . $mark_date;
                }
            } else {
                $qry2 = "insert into tehillim_backups 
                        set date_task_id = " . $task_id . ",
                        user_id = " . $user_id . ",
                        mark_date = " . $mark_date . ",
                        done_qty = " . $done_qty . ",
                        grid_id = " . $grid_id . ",
                        sm_date = " . $mark_date . ", 
                        year = 5778";
            }
            if (!empty($qry2)) $qrys[] = $qry2;
        }
    }
}

//echo "<pre>"; print_r( $qrys ); echo "</pre>";
foreach ($qrys as $qry) {
    mysql_query( $qry );
}
echo "done.";