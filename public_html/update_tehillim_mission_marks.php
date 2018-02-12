<?php
require 'db.php';

$marks = array();
$sql = "select * from date_tasks_marks dtm
        join date_tasks dt using (date_task_id) 
        where dt.grid_id = 8001
        and dtm.mark_date = 2458076";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $marks[$row['user_id']] = $row['date_tasks_mission_id'];
}

$updated = 0;
foreach ($marks as $user => $missionID) {
    // find out quota
    $sql = "select * from date_tasks_missions where date_tasks_mission_id = " . $missionID;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $quota = $row['quantity'];
    
    // check if any mission was marked
    $sql = "select * from date_tasks_mission_marks dtmm
            join date_tasks_missions dtm using (date_tasks_mission_id)
            join date_tasks dt using (date_task_id) 
            where dtmm.user_id = " . $user . "
            and dtm.subject_id = 1 
            and dt.grid_id = 8001
            and dtm.start_date = 2458076
            and dtm.end_date = 2458076";
    $result = mysql_query( $sql );
    
    // check if quota was reached 
    if (intval($val) >= $quota && mysql_num_rows( $result ) == 0) {
        $sql = "insert into date_tasks_mission_marks
                set user_id = " . $user . ",
                date_tasks_mission_id = " . $missionID . ",
                mission_value = 1.0, 
                subject_id = 1,
                mission_name = \"" . mysql_real_escape_string( $row['mission_name'] ) . "\",
                mark_date = 2458076";
        //echo $sql . "<br />";
        if (mysql_query( $sql )) $updated++;
    } 
}
echo "Missions updated: " . $updated;