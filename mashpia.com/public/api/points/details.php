<?php
ini_set('display_errors',1);
require_once( __DIR__ . '/../header/db.php' );

function convertToJD( $date ) {
    $temp = explode('-', $date);
    return gregoriantojd($temp[1], $temp[2], $temp[0]);
}

$start_date = $_POST['start'];
$end_date = $_POST['end'];
$user_id = intval($_POST['user_id']);

$start = convertToJD( $start_date );
$end = convertToJD( $end_date );

$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM date_tasks_marks dtm 
    LEFT JOIN date_tasks dt using (date_task_id) 
    WHERE mark_date >= :start
    AND mark_date <= :end
");
$stmt->execute([
    ':start'=>  $start, 
    ':end'  =>  $end
]);
$tasks = $stmt->fetchAll();

$stmt2 = $MASHPIA_DB->prepare("
    SELECT * FROM pointsDB.user_points 
    LEFT JOIN user_prizes using (user_prize_id) 
    LEFT JOIN prizes p using (prize_id) 
    WHERE user_id = :user
");
$stmt2->execute([':user' => $user_id]);
$other = $stmt2->fetchAll();

$info = [];
$info['tasks'] = $tasks;
$info['other'] = $other;

echo json_encode($info);