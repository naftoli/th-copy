<?php
require 'db.php';

$arr1 = array();
$sql = "select date_tasks_mission_id from date_tasks_mission_marks where user_id = 21142";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $arr1[] = $row['date_tasks_mission_id'];
}

$arr2 = array();
$sql = "select date_tasks_mission_id from date_tasks_mission_marks where user_id = 15523";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $arr2[] = $row['date_tasks_mission_id'];
}

$arr4 = array_diff($arr2, $arr1);
$arr3 = array_intersect($arr1, $arr2);

echo count($arr4);
echo "<pre>";
//print_r($arr4);
//echo count($arr3);
//print_r($arr3);
echo "</pre>";

$ids = array();
foreach ($arr4 as $id) {
    $ids[] = $id;
}

$sql = "update date_tasks_mission_marks set user_id = 21142 where user_id = 15523 and date_tasks_mission_id in (" .
    implode(',', $ids) . ")";
if (mysql_query($sql)) echo 'done';
?>