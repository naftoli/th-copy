<?php
require_once 'db.php';
$sql = "select date_tasks_mission_id, mission_number from date_tasks_missions group by mission_number";
$result = mysql_query($sql);
$missions = array();
while ($row = mysql_fetch_assoc($result)) {
    $missions[$row['date_tasks_mission_id']] = $row['mission_number'];
}
foreach ($missions as $number) {
    $duplicate = array_keys($missions, $number);
    if (count($duplicate) > 0) {
        print_r($duplicate);
    }
}
?>
