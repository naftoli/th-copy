<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$types = [2, 3];

$levels = [];
for ($i = 0; $i < 15; $i++) $levels[] = $i;

$grids = [];
for ($i = 13001; $i <= 13012; $i++) $grids[] = $i;

$duplicateMissions = [];
$duplicateTasks = [];

//foreach ($grids as $id) {
    foreach ($types as $type) {
        foreach ($levels as $level) {
            $sql = "SELECT 
                        date_tasks_mission_id, date_task_id
                    FROM
                        date_tasks_missions dtm
                            JOIN
                        date_tasks dt USING (date_tasks_mission_id)
                    WHERE
                        dtm.created_by_school IS NULL
                            AND dtm.created_by_parent IS NULL
                            AND dtm.lang_id = 4
                            AND created_at < '2021-09-01'
                            AND dt.grid_id = 13001
                            AND school_type_id = $type
                            AND level = $level";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $duplicateTasks[] = $row['date_task_id'];
                $duplicateMissions = $row['date_tasks_mission_id'];
            }
        }
    }
//}

$deletedTasks = 0;
$deletedMissions = 0;
foreach ($duplicateTasks as $id) {
    $sql = "delete from date_tasks where date_task_id = " . $id;
    if (mysql_query($sql)) {
        $deletedTasks++;
    }
}
foreach ($deletedMissions as $id) {
    $sql = "delete from date_tasks_missions where date_tasks_mission_id = " . $id;
    if (mysql_query($sql)) {
        $deletedMissions++;
    }
}
echo "Deleted Missions: " . $deletedMissions . "<br />";
echo "Deleted Tasks: " . $deletedTasks;