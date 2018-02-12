<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);
require_once 'db.php';

//$date = 2457956;
$updated = 0;
$date = $_POST['date'];
$fields = array('date_task_id', 'user_id', 'mark_date', 'done_qty', 'mark_description', 'mark_points', 'mark_quantity', 'mark_inactive');

$sql = "select dtmm.* from mashpiadb_sf01220566.date_tasks_marks dtmm 
        left join date_tasks_marks dtm using (date_task_id, user_id, mark_date) 
        where dtm.date_task_id is null 
        and dtmm.mark_date = " . $date;
$result = mysql_query($sql) or die(mysql_error());
if (mysql_num_rows($result)) {
    while ($row = mysql_fetch_assoc($result)) {
        $sql2 = "insert into date_tasks_marks set ";
        foreach ($fields as $field) {
            if ($field == 'mark_description') {
                $sql2 .= $field . "='" . $row[$field] . "', ";
            } else {
                $sql2 .= $field . "=" . $row[$field] . ", ";
            }
        }
        $sql2 = substr($sql2, 0, strlen($sql2) - 2);
        //echo $sql2 . "<br />";
        if (mysql_query($sql2)) {
            $updated++;
        } else {
            echo mysql_error();
        }
    }
}
echo "Updated: " . $updated;
/*
do {
    $sql = "select * from mashpiadb_sf01220566.date_tasks_marks dtmm 
            left join date_tasks_marks dtm using (date_task_id, user_id, mark_date) 
            where dtm.date_task_id is null 
            and dtmm.mark_date = " . $date;
    $result = mysql_query($sql) or die(mysql_error());
    if (mysql_num_rows($result)) {
        while ($row = mysql_fetch_assoc($result)) {
            $sql2 = "insert into date_tasks_marks set ";
            foreach ($fields as $field) {
                $sql2 .= $field . "=" . $row[$field] . ", ";
            }
            $sql2 = substr($sql2, 0, strlen($sql2) - 2);
            echo $sql2 . "<br />";
        }
    }
} while (++$date <= 2458021);
*/