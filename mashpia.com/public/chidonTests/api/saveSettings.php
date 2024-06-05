<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$school_id = $_POST['school_id'];
$class_id = $_POST['class_id'];
$user_id = $_POST['user_id'];

if (!$school_id) {
    echo json_encode(['error' => 'No school selected']);
    exit;
}

$super_admin = $admin_user['auth'] == 'super';

if ($user_id > 0) {
    $school_id = 0;
    $class_id = 0;
} else if ($class_id > 0) {
    $school_id = 0;
}

echo "<pre>"; print_r($_POST); echo "</pre>"; exit;

$error_msg = '';
$success = true;
$elem = $_POST['elem'];
switch ($elem) {
    case 'avgScore':
    case 'avgScoreIyun':
        $tracks = $_POST['tracks'];
        $avg = $_POST['avg'];
        $stmt = $MASHPIA_DB->prepare("
            INSERT IGNORE INTO chidon_passing_avgs 
            SET 
                school_id = :school, 
                class_id = :class, 
                user_id = :user, 
                track = :track, 
                avg = :avg, 
                year = :year 
            ON DUPLICATE KEY UPDATE 
                avg = :avg 
        ");
        $MASHPIA_DB->beginTransaction();
        foreach ($tracks as $track) {
            if (!$super_admin && $track == 'genius' && $avg < 80) {
                $success = false;
                $error_msg = 'Iyun test average must be at least 80';
                break;
            }
            $res = $stmt->execute([
                'school' => $school_id,
                'class' => $class_id,
                'user' => $user_id,
                'track' => $track,
                'avg' => $avg,
                'year' => $year
            ]);
            if (!$res) {
                $success = false;
                break;
            }
        }
        break;
    case 'avgScoreFinal':
    case 'avgFinalIyun':
        $tracks = $_POST['tracks'];
        $avg = $_POST['avgFinal'];
        $stmt = $MASHPIA_DB->prepare("
            INSERT IGNORE INTO chidon_final_passing_avgs 
            SET 
                school_id = :school, 
                class_id = :class,
                user_id = :user, 
                track = :track, 
                avg = :avg, 
                year = :year 
            ON DUPLICATE KEY UPDATE 
                avg = :avg 
        ");
        $MASHPIA_DB->beginTransaction();
        foreach ($tracks as $track) {
            if (!$super_admin && $track == 'genius' && $avg < 80) {
                $success = false;
                $error_msg = 'Iyun test average must be at least 80';
                break;
            }
            $res = $stmt->execute([
                'school' => $school_id,
                'class' => $class_id,
                'user' => $user_id,
                'track' => $track,
                'avg' => $avg,
                'year' => $year
            ]);
            if (!$res) {
                $success = false;
                break;
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
                class_id = :class,
                user_id = :user, 
                test_type = :type, 
                test_level = :level, 
                year = :year 
            ON DUPLICATE KEY UPDATE 
                test_level = :level 
        ");
        $MASHPIA_DB->beginTransaction();
        foreach ($types as $type) {
            $res = $stmt->execute([
                'school' => $school_id,
                'class' => $class_id,
                'user' => $user_id,
                'type' => $type,
                'level' => $level,
                'year' => $year
            ]);
            if (!$res) {
                $success = false;
                break;
            }
        }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode(['success' => true]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode(['error' => $error_msg ?? 'Failed to save settings']);
}