<?php
ini_set('display_errors',1);
require 'db.php';

$info = array();
$sql = "select user_id, count(*) as num from date_tasks_marks dtm 
        join date_tasks dt using (date_task_id) 
        where dt.grid_id = 8002 
        and dtm.mark_date = 2458104
        group by user_id
         order by num desc";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}
echo "<pre>"; print_r( $info ); echo "</pre>";