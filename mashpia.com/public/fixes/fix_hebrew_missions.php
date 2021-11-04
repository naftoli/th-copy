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

$names = ['נטילת ידיים', 'להתלבש בצניעות', 'צניעות'];

$duplicateTasks = [];
//foreach ($grids as $id) {
    foreach ($types as $type) {
        foreach ($names as $name) {
            $sql = "SELECT 
                        date_task_id 
                    FROM
                        date_tasks_missions dtm
                            JOIN
                        date_tasks dt USING (date_tasks_mission_id)
                    WHERE
                        dtm.created_by_school IS NULL
                            AND dtm.created_by_parent IS NULL
                            AND dtm.subject_id = 45
                            AND dtm.lang_id = 4
                            AND updated_at < '2021-09-01'
                            AND dt.short_name = '$name'
                            AND dtm.school_type_id = $type";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $duplicateTasks[] = $row['date_task_id'];
            }
        }
    }
//}

$deletedTasks = 0;
foreach ($duplicateTasks as $id) {
    $sql = "delete from date_tasks where date_task_id = " . $id;
    if (mysql_query($sql)) {
        $deletedTasks++;
    }
}
echo "Deleted Tasks: " . $deletedTasks;