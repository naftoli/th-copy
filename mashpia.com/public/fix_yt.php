<?php
require 'db.php';

$ids = array();
$sql = "select date_tasks_mission_id from date_tasks_missions 
        join date_tasks using (date_tasks_mission_id) 
        where cat = 'Sefiros Haomer' 
        and start_date >= 2457851";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ids[] = $row['date_tasks_mission_id'];
}

$qry = "delete from date_tasks where date_tasks_mission_id in (" . implode(',', $ids) . ")";
$qry2 = "delete from date_tasks_missions where date_tasks_mission_id in (" . implode(',', $ids) . ")";

//if (mysql_query($qry)) echo "Tasks Deleted.<br />";
//if (mysql_query($qry2)) echo "Missions Deleted.";