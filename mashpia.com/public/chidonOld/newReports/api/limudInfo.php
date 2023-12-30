<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$input = json_decode(file_get_contents('php://input'), true);
$year = intval($input['year']);
$school_id = intval($input['school']);
$class_id = intval($input['grade']);
$test_num = intval($input['test_num']);

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

// calculate total days
$days = 0;
for ($i = 0; $i < $test_num; $i++) {
    $days += $total_days[$i];
}
$untilToday = new DateTime() <= new DateTime($dates[$test_num - 1][2]); // for getting total minutes learned

$info = [];
foreach ($students as $student) {
    $learned = $ct->getTotalMinutesLearned($student['user_id'], $dates[0], true, $untilToday);
    $track_passed = isset($marks[$student['th_chidon_id']]) ? $ct->getHighestTrack($marks[$student['th_chidon_id']], $student['user_id']) : '';
    $avgs = calculateAvgs($student['th_chidon_id']);
    $passing_avg = $ct->getPassingAvgs($student['user_id'])[$student['test_type']];

    $info[] = [
        'user_id'   => $student['user_id'],
        'school_id' => $student['school_id'],
        'school_name'   => $student['school_name'],
        'serial'    => $student['user_serial'],
        'name'      => $student['first'] . ' ' . $student['last'],
        'grade'     => $student['class_grade'] . ($student['class_sub'] ? '-' . $student['class_sub'] : ''),
        'track'     => $student['test_type'],
        'reward'    => $student['reward_type'] === 'highest track passed' || empty($student['reward_type']) ? $track_passed : $student['reward_type'],
        'award'     => empty($student['award_type']) ? $track_passed : $student['award_type'],
        'passing_avg'   => $passing_avg,
        'yesod'     => $avgs['maven'],
        'yediah'    => $avgs['pro'],
        'havonah'   => $avgs['expert'],
        'iyun'      => $avgs['genius'],
        'non_cumulative_track_passed'   => $track_passed ? $types[$track_passed] : '',
        'cumulative_track_passed'   => calculateCumulative($student['th_chidon_id']),
        'time_committed'    => $learning_time[$student['test_type']] * $days,
        'time_learned'  => $learned,
        'dropped_out'   => intval($student['dropped_out']),
        'reason'        => $student['reason']
    ];
}

echo json_encode([
    'info'      => $info,
]);

function calculateAvgs($id) {
    global $scores, $test_num, $num_questions, $types;

    // initialize total score
    $total = [];
    foreach ($types as $type => $desc) {
        $total[$type] = 0;
    }

    // calculate total score
    for ($i = 1; $i <= $test_num; $i++) {
        foreach ($types as $type => $desc) {
            if (isset($scores[$id][$i][$type])) $total[$type] += intval($scores[$id][$i][$type]);
        }
    }

    // calculate average
    $avgs = [];
    foreach ($types as $type => $desc) {
        $avgs[$type] = round(($total[$type] / ($num_questions[$type] * $test_num)) * 100);
    }

    return $avgs;
}

function calculateCumulative($id) {
    global $scores, $test_num, $num_questions, $types;

    $questions = [];
    $cumulative_scores = [];

    // initialize cumulative scores
    foreach ($types as $type => $desc) {
        $cumulative_scores[$type] = 0;
    }

    $questions['maven'] = $num_questions['maven'];
    $questions['pro'] = $num_questions['maven'] + $num_questions['pro'];
    $questions['expert'] = $num_questions['maven'] + $num_questions['pro'] + $num_questions['expert'];
    $questions['genius'] = $num_questions['maven'] + $num_questions['pro'] + $num_questions['expert'] + $num_questions['genius'];

    if (! isset($scores[$id])) return '';

    foreach ($scores[$id] as $testNum => $details) {
        if ($testNum > $test_num) {
            $testNum--;
            break;
        }
        foreach ($types as $type => $desc) {
            $cumulative_scores[$type] += isset($details[$type]) ? $details[$type] : 0;
        }
    }

    $cumulative_scores['genius'] += $cumulative_scores['expert'] + $cumulative_scores['pro'] + $cumulative_scores['maven'];
    $cumulative_scores['expert'] += $cumulative_scores['pro'] + $cumulative_scores['maven'];
    $cumulative_scores['pro'] += $cumulative_scores['maven'];

    $cumulative = [];
    foreach ($types as $type => $desc) {
        $cumulative[$type] = round(($cumulative_scores[$type] / ($questions[$type] * $testNum)) * 100);
    }

    $reg_avg = 70;
    $iyun_avg = 90;
    if ($cumulative['genius'] >= $iyun_avg) return $types['genius'];
    else if ($cumulative['expert'] >= $reg_avg) return $types['expert'];
    else if ($cumulative['pro'] >= $reg_avg) return $types['pro'];
    else if ($cumulative['maven'] >= $reg_avg) return $types['maven'];
    else return '';
}