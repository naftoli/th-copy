<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('No Permission');
}

$school_types = [12, 13];
$ages = [6, 7, 8, 9, 10, 11, 12, 13, 14];
$tracks = [3, 4, 5, 6, 7];
$langs = [1, 2, 3, 4];
$grids = [
    8001 => 80010,
    8002 => 80020,
    8003 => 80030
];

$stmt = $MASHPIA_DB->prepare("
    select date_tasks_mission_id, date_task_id from date_tasks_missions dtm 
    join date_tasks dt using (date_tasks_mission_id) 
    where dtm.start_date = 2460764 
    and dtm.end_date = 2460764 
    and school_type_id = :school_type 
    and level = :age 
    and track_id = :track  
    and lang_id = :lang  
    and grid_id in (:grid, :new_grid) 
    order by grid_id desc");

$stmt2 = $MASHPIA_DB->prepare("
    UPDATE date_tasks_mission_marks 
    SET 
        date_tasks_mission_id = :new_mission_id 
    WHERE
        date_tasks_mission_id = :old_mission_id
");

$stmt3 = $MASHPIA_DB->prepare("
    UPDATE date_tasks_marks 
    SET 
        date_task_id = :new_task_id 
    WHERE
        date_task_id = :old_task_id
");

$success = true;
$MASHPIA_DB->beginTransaction();
foreach ($school_types as $type) {
    foreach ($ages as $age) {
        foreach ($tracks as $track) {
            foreach ($langs as $lang) {
                foreach ($grids as $grid => $new_grid) {
                    $res = $stmt->execute([
                        'school_type' => $type,
                        'age' => $age,
                        'track' => $track,
                        'lang' => $lang,
                        'grid' => $grid,
                        'new_grid' => $new_grid
                    ]);
                    if (!$res) {
                        $success = false;
                        echo $stmt->debugDumpParams();
                        break 5;
                    }
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($rows) || count($rows) != 2) continue;
                    $old = $rows[0];
                    $new = $rows[1];

                    if ($old['date_tasks_mission_id'] != $new['date_task_id']) {
                        $res2 = $stmt2->execute([
                            'new_mission_id' => $new['date_tasks_mission_id'],
                            'old_mission_id' => $old['date_tasks_mission_id'],
                        ]);
                        if (!$res2) {
                            $success = false;
                            $stmt->debugDumpParams();
                            $stmt2->debugDumpParams();
                            break 5;
                        }
                    }

                    if ($old['date_task_id'] != $new['date_task_id']) {
                        $res3 = $stmt3->execute([
                            'new_task_id' => $new['date_task_id'],
                            'old_task_id' => $old['date_task_id'],
                        ]);
                        if (!$res3) {
                            $success = false;
                            $stmt->debugDumpParams();
                            $stmt2->debugDumpParams();
                            $stmt3->debugDumpParams();
                            break 5;
                        }
                    }
                }
            }
        }
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo "Done.";
} else {
    $MASHPIA_DB->rollBack();
    echo "<pre>"; print_r($MASHPIA_DB->errorInfo()); echo "</pre>";
    echo "Errors.";
}