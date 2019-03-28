<?php
ini_set('display_errors',1);
require 'db.php';

$date = 2458076;
$grids = array(8001,8002);

$info = array();
$sql = "select dtm.* from date_tasks_marks dtm
        join date_tasks dt using (date_task_id) 
        where dtm.mark_date = " . $date . " 
        and dt.grid_id in (" . explode(',', $grids) . ")";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[$row['user_id']][] = $row;        
}

foreach ($users as $user_id => $marks) {
    // find highest mark
    foreach ($marks as $mark) {
        
    }
}
echo "done.";