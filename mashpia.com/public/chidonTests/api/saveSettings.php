<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$school_id = $_POST['school_id'];
$class_id = $_POST['class_id'];
$user_id = $_POST['user_id'];

if (!$school_id) {
    echo json_encode(['error' => 'No school selected']);
    exit;
}

$users = [];
if ($user_id > 0) $users[] = $user_id;
else if ($class_id > 0) {
    // find all users in this class
    $stmt = $MASHPIA_DB->prepare("
        select user_id 
        from users 
        where class_id = :class 
        and user_registered > 0 
    ");
    $stmt->execute(['class' => $class_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $users[] = $row['user_id'];
    }
}
if (empty($users)) $users[] = 0;
else $school_id = 0; // only include school id if no users are being set

$success = true;
$elem = $_POST['elem'];
switch ($elem) {
    case 'avgScore':
        $tracks = $_POST['tracks'];
        $avg = $_POST['avg'];
        $stmt = $MASHPIA_DB->prepare("
            INSERT IGNORE INTO chidon_passing_avgs 
            SET 
                school_id = :school, 
                user_id = :user, 
                track = :track, 
                avg = :avg, 
                year = :year 
            ON DUPLICATE KEY UPDATE 
                avg = :avg 
        ");
        $MASHPIA_DB->beginTransaction();
        foreach ($users as $user_id) {
            foreach ($tracks as $track) {
                $res = $stmt->execute([
                    'school' => $school_id,
                    'user' => $user_id,
                    'track' => $track,
                    'avg' => $avg,
                    'year' => $year
                ]);
                if (!$res) {
                    $success = false;
                    break 2;
                }
            }
        }
        break;
    case 'avgScoreFinal':
        $tracks = $_POST['tracks'];
        $avg = $_POST['avgFinal'];
        $stmt = $MASHPIA_DB->prepare("
            INSERT IGNORE INTO chidon_final_passing_avgs 
            SET 
                school_id = :school, 
                user_id = :user, 
                track = :track, 
                avg = :avg, 
                year = :year 
            ON DUPLICATE KEY UPDATE 
                avg = :avg 
        ");
        $MASHPIA_DB->beginTransaction();
        foreach ($users as $user_id) {
            foreach ($tracks as $track) {
                $res = $stmt->execute([
                    'school' => $school_id,
                    'user' => $user_id,
                    'track' => $track,
                    'avg' => $avg,
                    'year' => $year
                ]);
                if (!$res) {
                    $success = false;
                    break 2;
                }
            }
        }
        break;
    case 'levels':
        $level = $_POST['level'];
        $types = [];
        if (isset($_POST['tests'])) $types[] = 'tests';
        if (isset($_POST['finals'])) $types[] = 'finals';
        $stmt = $MASHPIA_DB->prepare("
            INSERT IGNORE INTO chidon_test_levels 
            SET 
                school_id = :school, 
                user_id = :user, 
                test_type = :type, 
                test_level = :level, 
                year = :year 
            ON DUPLICATE KEY UPDATE 
                test_level = :level 
        ");
        $MASHPIA_DB->beginTransaction();
        foreach ($users as $user_id) {
            foreach ($types as $type) {
                $res = $stmt->execute([
                    'school' => $school_id,
                    'user' => $user_id,
                    'type' => $type,
                    'level' => $level,
                    'year' => $year
                ]);
                if (!$res) {
                    $success = false;
                    break 2;
                }
            }
        }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode(['success' => true]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode(['error' => 'Failed to save settings']);
}