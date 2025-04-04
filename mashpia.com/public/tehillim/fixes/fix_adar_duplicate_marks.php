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
            AND user_id = :user_id
");

$stmtMissions = $MASHPIA_DB->prepare('
    SELECT * FROM date_tasks_mission_marks WHERE date_tasks_mission_id = :mission_id
');

$stmtMissionDelete = $MASHPIA_DB->prepare("
    DELETE FROM date_tasks_mission_marks WHERE date_tasks_mission_id = :mission_id AND user_id = :user_id 
");

$stmt3 = $MASHPIA_DB->prepare("
    UPDATE date_tasks_marks 
    SET 
        date_task_id = :new_task_id 
    WHERE
        date_task_id = :old_task_id
            AND user_id = :user_id
");

$stmtTasks = $MASHPIA_DB->prepare("
    SELECT * FROM date_tasks_marks WHERE date_task_id = :task_id
");

$stmtTaskDelete = $MASHPIA_DB->prepare("
    DELETE FROM date_tasks_marks WHERE date_task_id = :task_id AND user_id = :user_id
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

                    if ($old['date_tasks_mission_id'] != $new['date_tasks_mission_id']) {
                        // get users that have marks with old mission id
                        $stmtMissions->execute([
                            'mission_id' => $old['date_tasks_mission_id']
                        ]);
                        $users = $stmtMissions->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($users as $user) {
                            $user_id = $user['user_id'];
                            $res2 = $stmt2->execute([
                                'new_mission_id' => $new['date_tasks_mission_id'],
                                'old_mission_id' => $old['date_tasks_mission_id'],
                                'user_id' => $user_id
                            ]);
                            if (!$res2) {
                                // check error to see if it's duplicate
                                $error = $stmt2->errorInfo()[2];
                                if (strstr($error, 'Duplicate') !== false || strstr($error, 'duplicate') !== false) {
                                    // delete old mission b/c user aleady has it for new one
                                    $del = $stmtMissionDelete->execute([
                                        'mission_id' => $old['date_tasks_mission_id'],
                                        'user_id' => $user_id
                                    ]);
                                    if (!$del) {
                                        $success = false;
                                        echo $stmtMissionDelete->errorInfo()[2] . "<br />";
                                        $stmtMissionDelete->debugDumpParams();
                                        $stmt2->debugDumpParams();
                                        break 5;
                                    }
                                } else {
                                    echo $error . "<br />";
                                    $success = false;
                                    break 5;
                                }
                            }
                        }
                    }

                    if ($old['date_task_id'] != $new['date_task_id']) {
                        $stmtTasks->execute([
                            'task_id' => $old['date_task_id']
                        ]);
                        $users = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($users as $user) {
                            $user_id = $user['user_id'];
                            $res3 = $stmt3->execute([
                                'new_task_id' => $new['date_task_id'],
                                'old_task_id' => $old['date_task_id'],
                                'user_id' => $user_id
                            ]);
                            if (!$res3) {
                                // check error to see if it's duplicate
                                $error2 = $stmt2->errorInfo()[2];
                                if (strstr($error2, 'Duplicate') !== false || strstr($error2, 'duplicate') !== false) {
                                    $del2 = $stmtTaskDelete->execute([
                                        'task_id' => $old['date_task_id'],
                                        'user_id' => $user_id
                                    ]);
                                    if (!$del2) {
                                        $success = false;
                                        echo $stmtTaskDelete->errorInfo()[2] . "<br />";
                                        $stmtTaskDelete->debugDumpParams();
                                        $stmt3->debugDumpParams();
                                        break 5;
                                    }
                                } else {
                                    echo $error2 . "<br />";
                                    $success = false;
                                    break 5;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

//echo "Done.";
if ($success) {
    $MASHPIA_DB->commit();
    echo "Done.";
} else {
    $MASHPIA_DB->rollBack();
    echo "There were errors.";
}