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
$scores = $ct->getScores();
$marks = $ct->getMarks();
$num_questions = $ct->getTestQuestions();
$dates = $ct->getDates();
$types = $ct->getTypes();

$learning_time = [
    'maven'     => 10,
    'pro'       => 20,
    'expert'    => 30,
    'genius'    => 45,
];
$total_days = [32, 37, 37];

$info = [];
foreach ($students as $student) {
//    $totals = [];
//    $days = 0;
//    for ($i = 0; $i < $test_num; $i++) {
//        $days += $total_days[$i];
//        $totals[$i] = $ct->getTotalMinutesLearned($student['user_id'], $dates[$i]);
//    }
//    $learned = array_sum($totals);
    $days = $total_days[$test_num - 1];
    $learned = $ct->getTotalMinutesLearned($student['user_id'], $dates[$test_num - 1]);
    $track_passed = isset($marks[$student['th_chidon_id']]) ? $ct->getHighestTrack($marks[$student['th_chidon_id']], $student['user_id']) : '';
    if ($track_passed != '') $track_passed = $types[$track_passed];
    $info[] = [
        'user_id'   => $student['user_id'],
        'school_id' => $student['school_id'],
        'school_name'   => $student['school_name'],
        'serial'    => $student['user_serial'],
        'name'      => $student['first'] . ' ' . $student['last'],
        'grade'     => $student['class_grade'] . ($student['class_sub'] ? '-' . $student['class_sub'] : ''),
        'track'     => $types[ $student['test_type'] ],
        'reward'    => $student['reward_type'] === 'highest track passed' || empty($student['reward_type']) ? $track_passed : $types[ $student['reward_type'] ],
        'award'     => $track_passed,
        'passing_avg'   => $ct->getPassingAvgs($student['user_id'])[$student['test_type']],
        'yesod'     => ($marks[$student['th_chidon_id']][$test_num]['maven'] ?? 0 . '/' . $num_questions['maven']),
        'yediah'    => ($marks[$student['th_chidon_id']][$test_num]['pro'] ?? 0 . '/' . $num_questions['pro']),
        'havonah'   => ($marks[$student['th_chidon_id']][$test_num]['expert'] ?? 0 . '/' . $num_questions['expert']),
        'iyun'      => ($marks[$student['th_chidon_id']][$test_num]['genius'] ?? 0 . '/' . $num_questions['genius']),
        'non_cumulative_track_passed'   => $track_passed,
        'cumulative_track_passed'   => calculateCumulative($student['th_chidon_id']),
        'time_committed'    => $learning_time[$student['test_type']] * $days,
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

function calculateCumulative($id) {
    global $scores, $test_num, $num_questions, $types;

    $cumulative_score = [];
    $cumulative_questions = [];
    foreach ($scores[$id] as $testNum => $details) {
        if ($testNum > $test_num) break;
        $cumulative_score['maven'] += $details[$testNum]['maven'];
        $cumulative_score['pro'] += $details[$testNum]['pro'];
        $cumulative_score['expert'] += $details[$testNum]['expert'];
        $cumulative_score['genius'] += $details[$testNum]['genius'];
        $cumulative_questions['maven'] += $num_questions['maven'];
        $cumulative_questions['pro'] += $num_questions['pro'];
        $cumulative_questions['expert'] += $num_questions['expert'];
        $cumulative_questions['genius'] += $num_questions['genius'];
    }

    $reg_avg = 70;
    $iyun_avg = 90;
    $cumulative = [];
    $cumulative['maven'] = round(($cumulative_score['maven'] / $cumulative_questions['maven']) * 100);
    $cumulative['pro'] = round(($cumulative_score['pro'] / $cumulative_questions['pro']) * 100);
    $cumulative['expert'] = round(($cumulative_score['expert'] / $cumulative_questions['expert']) * 100);
    $cumulative['genius'] = round(($cumulative_score['genius'] / $cumulative_questions['genius']) * 100);

    if ($cumulative['genius'] >= $iyun_avg) return $types['genius'];
    else if ($cumulative['expert'] >= $reg_avg) return $types['expert'];
    else if ($cumulative['pro'] >= $reg_avg) return $types['pro'];
    else if ($cumulative['maven'] >= $reg_avg) return $types['maven'];
    else return '';
}