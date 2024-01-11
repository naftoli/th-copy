<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$start = 2460293;
$end = 2460299;
$final = 2460574;

$update = $MASHPIA_DB->prepare("
    UPDATE date_tasks_missions dtm 
    JOIN date_tasks dt USING (date_tasks_mission_id) 
    SET start_date = :new_start, 
        end_date = :new_end 
    WHERE
        dtm.subject_id = :subject 
        AND dt.grid_id = :grid_id 
        AND dtm.start_date = :start 
        AND dtm.end_date = :end
");

$MASHPIA_DB->beginTransaction();

$info = getInfo();
foreach ($info as $subject => $more) {
    foreach ($more as $grid_id) {
        while ($start < $final) {
            $new_start = $start + 1;
            $new_end = $new_start + 6;
            $update->execute([
                'new_start' => $new_start,
                'new_end' => $new_end,
                'subject' => $subject,
                'grid_id' => $grid_id,
                'start' => $start,
                'end' => $end,
            ]);
            $update->debugDumpParams();
            $start += 7;
            $end += 7;
        }
    }
}

$MASHPIA_DB->rollBack();

function getInfo() {
    global $MASHPIA_DB;

    $info = [];
    $sql = "
        SELECT 
            dtm.subject_id, dt.grid_id
        FROM
            date_tasks_missions dtm
                JOIN
            date_tasks dt USING (date_tasks_mission_id)
        WHERE
            dtm.end_date in (2460299, 2460306, 2460313, 2460320, 2460327)
                AND subject_id NOT IN (40 , 94)
                AND dtm.created_by_parent IS NULL
                AND dtm.created_by_school IS NULL 
        GROUP BY grid_id
    ";
    $stmt = $MASHPIA_DB->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $info[$row['subject_id']][] = $row['grid_id'];
    }

    return $info;
}


