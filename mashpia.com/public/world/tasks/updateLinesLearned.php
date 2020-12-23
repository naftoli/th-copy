<?php
require '../../db.php';

$marks = [];
$sql = "select user_id, MAX(done_qty) as learned, school_id, class_id from date_tasks_marks dtm 
        join date_tasks dt using (date_task_id) 
        join date_tasks_missions dtmm using (date_tasks_mission_id) 
        join users u using (user_id) 
        where dt.grid_id in (21001,21002,21003,21004,21005,21006,21007,21008,21013,21014) 
        and dtmm.start_date > 2459027 
        group by user_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $marks[] = $row;
}

$year = 5781;
$campaign = 17;
$qrys = [];
foreach ( $marks as $mark ) {
    $exists_query = mysql_query("SELECT mission_sheet_amount AS t FROM lines_learned WHERE campaign_id = " . $campaign . " AND user_id = " . $mark['user_id']);
    if (mysql_num_rows($exists_query) > 0) {
        $update_sql = "UPDATE lines_learned"
                ." SET mission_sheet_amount = " . $mark['learned']
                ." WHERE campaign_id = " . $campaign
                ." AND user_id = " . $mark['user_id'];
        $qrys[] = $update_sql;
    } else {
        $insert_sql = "INSERT INTO lines_learned SET "
                ."campaign_id = " . $campaign . ", "
                ."user_id = " . $mark['user_id'] . ", "
                ."mission_sheet_amount = " . $mark['learned'] . ", "
                ."school_id = " . $mark['school_id'] . ", "
                ."class_id = " . $mark['class_id'];
        $qrys[] = $insert_sql;
    }
}

foreach ( $qrys as $qry ) mysql_query( $qry ) or die( mysql_error() . "<br />" . $qry . "<br />" );
echo "done";