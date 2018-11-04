<?php
require '../db.php';

$info = [];
$sql = "select * from date_tasks dt 
        join date_tasks_missions dtm using (date_tasks_mission_id) 
        where dtm.start_date >= 2458432 
        and dtm.subject_id = 27 
        and dt.grid_id in (21009,21010)";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['date_tasks_mission_id']][] = $row['date_task_id'];
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
$missions = array_keys( $info );

$sql1 = "delete from date_tasks where date_tasks_mission_id in (" . implode(',', $missions) . ")";
$sql2 = "delete from date_tasks_missions where date_tasks_mission_id in (" . implode(',', $missions) . ")";

mysql_query('set autocommit=0');
mysql_query('begin');
if (mysql_query($sql1) && mysql_query($sql2)) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');
echo "done.";