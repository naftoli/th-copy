<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$input = json_decode(file_get_contents('php://input'), true);
$year = $input['year'];
$school_id = $input['school'];
$class_id = intval($input['grade']);
$test_num = $input['test_num'];

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests($year);
$ct->setStudents($school_id, $class_id);
$ct->setScores();
$ct->calculateMarks();

$students = $ct->getStudents();
$marks = $ct->getMarks();
$num_questions = $ct->getTestQuestions();
$dates = $ct->getDates();

$learning_time = [
    'maven'     => 10,
    'pro'       => 20,
    'expert'    => 30,
    'genius'    => 45,
];
$total_days = [32, 37, 37];

$info = [];
foreach ($students as $student) {
    $learned = $ct->getTotalMinutesLearned($student['user_id'], $dates[$test_num], true);
    $track_passed = isset($marks[$student['th_chidon_id']]) ? $ct->getHighestTrack($marks[$student['th_chidon_id']], $student['user_id']) : '';
    $info[] = [
        'user_id'   => $student['user_id'],
        'school_id' => $student['school_id'],
        'school_name'   => $student['school_name'],
        'serial'    => $student['user_serial'],
        'name'      => $student['first'] . ' ' . $student['last'],
        'grade'     => $student['class_grade'] . ($student['class_sub'] ? '-' . $student['class_sub'] : ''),
        'track'     => $student['test_type'],
        'reward'    => $student['reward_type'] === 'highest track passed' ? $track_passed : $student['reward_type'],
        'passing_avg'   => $ct->getPassingAvgs($student['user_id'])[$student['test_type']],
        'yesod'     => ($marks[$student['th_chidon_id']][$test_num]['maven'] ?? 0 . '/' . $num_questions['maven']),
        'yediah'    => ($marks[$student['th_chidon_id']][$test_num]['pro'] ?? 0 . '/' . $num_questions['pro']),
        'havonah'   => ($marks[$student['th_chidon_id']][$test_num]['expert'] ?? 0 . '/' . $num_questions['expert']),
        'iyun'      => ($marks[$student['th_chidon_id']][$test_num]['genius'] ?? 0 . '/' . $num_questions['genius']),
        'non_cumulative_track_passed'   => $track_passed,
        'cumulative_track_passed'   => '',
        'time_committed'    => $learning_time[$student['test_type']] * $total_days[$test_num],
        'time_learned'  => $learned,
        'dropped_out'   => 0,
        'reason'        => '',
    ];
}

echo json_encode([
    'success'   => true,
    'info'      => $info,
    'marks'     => $marks,
]);