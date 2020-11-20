<?php
ini_set('display_errors',1);
require_once( __DIR__ . '/../header/db.php' );

function convertToJD( $date ) {
    list($year, $month, $day) = explode('-', $date);
    return gregoriantojd($month, $day, $year);
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
    AND user_id = :user
");
$stmt->execute([
    ':start'=>  $start, 
    ':end'  =>  $end, 
    ':user' =>  $user_id
]);
$tasks = $stmt->fetchAll();

$i = 0;
$details = [];
// add relevant task info to details array
foreach ($tasks as $task) {
    // convert dates from julian to regular
    list($month, $day, $year) = explode('/', jdtogregorian($task['mark_date']));
    $details[$i]['date'] = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $details[$i]['name'] = $task['short_name'];
    $details[$i]['type'] = 'task';
    $details[$i]['card'] = '';
    $details[$i]['points'] = 0.5;
    $i++;
}

$stmt2 = $MASHPIA_DB->prepare("
    SELECT up.*, p.prize_name, ac.card_serial as 'achievement card', CONCAT(sc.card_serial, sc.card_control) as 'scratch card' FROM pointsDB.user_points up 
    LEFT JOIN pointsDB.prizes p using (prize_id)
    LEFT JOIN pointsDB.scratch_cards sc using (scratch_card_id) 
    LEFT JOIN pointsDB.achievement_cards ac using (achievement_card_id)     
    WHERE up.user_id = :user 
    AND up.created >= :start 
    AND up.created <= :end
");
$stmt2->execute([
    ':user' => $user_id, 
    ':start'=> $start_date, 
    ':end'  => $end_date
]);
$other = $stmt2->fetchAll();
foreach ($other as $row) {
    switch ($row['resource_name']) {
        case 'store':
        case 'transaction_manager_store':
            $type = 'store purchase/return';
            break;
        case 'specific achievement card':
            $type = 'achievement card';
            break;
        case 'scratch_card':
            $type = 'scratch card';
            break;
        case 'admin_users_manual':
            $type = 'points added/subtracted by BC';
            break;      
    }
    $details[$i]['date'] = $row['created'];
    $details[$i]['name'] = $row['prize_name'];
    $details[$i]['type'] = $type;
    $details[$i]['card'] = $row[$type] ? $row[$type] : 0;
    $details[$i]['points'] = floatval($row['points']);
    $i++;
}

usort($details, function($a, $b) {
    if ($a == $b) return 0;
    return ($a < $b) ? -1 : 1;
});

echo json_encode($details);