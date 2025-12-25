<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$info = json_decode(file_get_contents("php://input"), true);
$school_id = $info['school_id'] ?? $_GET['school_id'];
if (! $school_id) {
    echo json_encode(['error' => 'School ID is required']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/ranks/future/class.futureRanks.php';

$info = [];
$end_date = 2461174; // 26 Iyar 5786
$fr = new FutureRanks($year, [$school_id], $end_date);
$future_ranks = $fr->getFutureRanks();

// get school / grade info
$users = [];
$sql = "SELECT s.school_id, s.school_name, c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.first_he, u.last_he, u.user_serial
        FROM users u
        JOIN schools s ON s.school_id = u.school_id
        JOIN classes c ON c.class_id = u.class_id
        WHERE u.school_id = :school_id AND u.user_registered IS NOT NULL AND u.user_registered > '0000-00-00 00:00:00'";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    ':school_id' => $school_id
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $users[$row['user_id']] = $row;
}

$info['future'] = [];
foreach ($future_ranks as $user_id => $rank) {
    if (in_array($rank, [9, 12])) {
        $user_info = $users[$user_id];
        $info['future'][$user_info['school_id']][$user_info['class_grade']][$user_info['class_sub']][] = $user_info;
    }
}

echo json_encode($info);