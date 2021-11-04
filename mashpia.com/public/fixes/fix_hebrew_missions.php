<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$grids = [];
for ($i = 13001; $i <= 13012; $i++) $grids[] = $i;

$duplicates = [];
foreach ($grids as $id) {
    $sql = "SELECT 
                date_task_id
            FROM
                date_tasks_missions dtm
                    JOIN
                date_tasks dt USING (date_tasks_mission_id)
            WHERE
                dtm.subject_id = 45
                    AND dtm.created_by_school IS NULL
                    AND dtm.created_by_parent IS NULL
                    AND dtm.lang_id = 4
                    AND label_id < 50
                    AND dt.grid_id = " . $id;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $duplicates[] = $row['date_task_id'];
    }
}

$deleted = 0;
foreach ($duplicates as $id) {
    $sql = "delete from date_tasks where date_task_id = " . $id;
    if (mysql_query($sql)) {
        $deleted++;
    }
}
echo "Deleted: " . $deleted;