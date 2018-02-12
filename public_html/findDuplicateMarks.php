<?php
require 'db.php';

$marks = array();
$gridIDs = array(8001,8002,8003);

foreach ($gridIDs as $grid_id) {
    $sql = "select dtmm.* from date_tasks_marks dtmm 
            join date_tasks dt using (date_task_id)
            join date_tasks_missions dtm using (date_tasks_mission_id) 
            where dtm.start_date = 2458076 
            and dtm.end_date = 2458076 
            and dtm.subject_id = 1 
            and dt.grid_id = " . $grid_id . "
            group by dtmm.user_id
            order by dtmm.done_qty desc";
    //echo $sql . "<br /><br />";
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $marks[$row['user_id']][$grid_id] = $row;
    }
}

//echo "<pre>";
//print_r( $marks );
//echo "</pre>";
//exit;

foreach ($marks as $user => $other) {
    foreach ($other as $grid => $row) {
        $sql = "delete dtm.* from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id) 
                where dtm.user_id = " . $user . "
                and dt.grid_id = " . $grid . "
                and dtm.mark_date = " . $row['mark_date'];
        //echo $sql . "<br />";
        mysql_query( $sql );
        // need to reinsert it b/c all marks were just deleted
        $sql = "insert into date_tasks_marks
                set user_id = " . $row['user_id'] . ",
                date_task_id = " . $row['date_task_id'] . ",
                mark_date = " . $row['mark_date'] . ",
                done_qty = " . $row['done_qty'] . ",
                mark_description = \"" . $row['mark_description'] . "\",
                mark_points = 0.5";
        //echo $sql . "<br /><br />";
        mysql_query( $sql );
    }
}
echo "done.";
