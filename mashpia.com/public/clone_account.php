<?php
$old = 8273;
$new = 17885;

$tasks = array();
$sql = "select * from date_task_marks where user_id = " . $old;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $tasks[] = $row;
}

foreach ($tasks as $task) {
    
}