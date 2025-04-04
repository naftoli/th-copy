<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('No Permission');
}

$stmt = $MASHPIA_DB->prepare("
    update date_tasks_missions dtm 
    join date_tasks dt using (date_tasks_mission_id) 
    set grid_id = :new_grid 
    where dtm.start_date = '2460764' AND dtm.end_date = '2460764' 
    and dtm.subject_id = 1 
    and school_type_id = :school_type 
    and level = :level 
    and track_id = :track 
    and grid_id = :grid 
    and dt.lang_id = :lang
    limit 1
");

$school_types = [12, 13];
$ages = [6, 7, 8, 9, 10, 11, 12, 13, 14];
$tracks = [3, 4, 5, 6, 7];
$langs = [1, 2, 3, 4];
$grids = [
    8001 => 80010,
    8002 => 80020,
    8003 => 80030
];

foreach ($school_types as $school_type) {
    foreach ($ages as $age) {
        foreach ($tracks as $track) {
            foreach ($grids as $grid => $new_grid) {
                foreach ($langs as $lang) {
                    $stmt->execute([
                        'new_grid' => $new_grid,
                        'school_type' => $school_type,
                        'level' => $age,
                        'track' => $track,
                        'grid' => $grid,
                        'lang' => $lang
                    ]);
                }
            }
        }
    }
}

echo "Done";

