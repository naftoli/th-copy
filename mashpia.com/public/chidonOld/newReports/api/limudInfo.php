<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$input = json_decode(file_get_contents('php://input'), true);
$year = intval($input['year']);
$school_id = intval($input['school']);
$class_id = intval($input['grade']);
$test_num = intval($input['test_num']);

// make sure that a non super admin cannot see all schools/children
if ($school_id == 0 && $admin_user['auth'] != 'super') {
    // get admin's school id
    $school_id = $admin_user['auths']['school'][0];
}

// find out if school info was confirmed and should be locked
$locked = 0;
if ($admin_user['auth'] != 'super') {
    $sql = "SELECT * FROM chidon_confirmations WHERE school_id = :school_id AND year = :year";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':school_id' => $school_id,
        ':year' => $year,
    ]);
    $result = $stmt->fetchAll();
    if (count($result) > 0) $locked = 1;
}

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

//$learning_time = [
//    'maven'     => 10,
//    'pro'       => 20,
//    'expert'    => 30,
//    'genius'    => 45,
//];
//$total_days = [32, 37, 37];

$summary = [];
$outcomes = ['maven', 'pro', 'expert', 'genius', 'did not take test', 'did not pass', 'dropped out'];
foreach ($types as $type => $desc) {
    foreach ($outcomes as $outcome) {
        $summary[$type][$outcome] = 0;
    }
}

// calculate total days
//$days = 0;
//for ($i = 0; $i < $test_num; $i++) {
//    $days += $total_days[$i];
//}
//$untilToday = new DateTime() <= new DateTime($dates[$test_num - 1][2]); // for getting total minutes learned

// calculate khk eligibility
$ids = [];
$khk = [];
foreach ($students as $student) {
    // only 8th graders are eligible for KHK
    if (intval($student['class_grade']) == 8) $ids[] = $student['user_id'];
}
if ($ids) $khk = KHK::getUltimateTripEligibility($ids, 0, 4, $marks)[0];

$info = [];
foreach ($students as $student) {
    if (! isset($marks[$student['th_chidon_id']])) {
//        $learned = 0;
        $avgs = ['maven' => 0, 'pro' => 0, 'expert' => 0, 'genius' => 0];
        $passing_avg = getPassingAvg($student['user_id']);
        $track_passed = '';
    } else {
//        $learned = $ct->getTotalMinutesLearned($student['user_id'], $test_num, true);
        $avgs = calculateAvgs($student['th_chidon_id']);
        $passing_avg = getPassingAvg($student['user_id']);
        $track_passed = isset($marks[$student['th_chidon_id']]) ? $ct->getHighestTrack($marks[$student['th_chidon_id']], $student['user_id'], false, $test_num) : '';
        $cumulative_track_passed = strtolower(calculateCumulative($student));
        if ($cumulative_track_passed == 'iyun' || $cumulative_track_passed == 'genius') $track_passed = 'genius';
    }

    // summary
    if ($student['dropped_out']) $track = 'dropped out';
    else if ($track_passed == '') {
        if (isset($scores[$student['th_chidon_id']][$test_num][$type]) && $scores[$student['th_chidon_id']][$test_num][$type] > 0) $track = 'did not pass';
        else $track = 'did not take test';
    }
    else $track = $track_passed;
    $summary[$student['test_type']][$track]++;

    $info[] = [
        'user_id'   => $student['user_id'],
        'school_id' => $student['school_id'],
        'school_name'   => $student['school_name'],
        'serial'    => $student['user_serial'],
        'name'      => $student['first'] . ' ' . $student['last'],
        'grade'     => $student['class_grade'] . ($student['class_sub'] ? '-' . $student['class_sub'] : ''),
        'track'     => $student['test_type'],
        'reward'    => $student['reward_type'] === 'highest track passed' || empty($student['reward_type']) ? 'highest track passed' : $student['reward_type'],
        'award'     => empty($student['award_type']) ? 'highest final passed' : $student['award_type'],
        'final_type' => (empty($student['final_type']) || $student['final_type'] === 'highest track passed') ? 'highest track passed' : $student['final_type'],
        'passing_avg'   => $passing_avg,
        'yesod'     => $avgs['maven'],
        'yediah'    => $avgs['pro'],
        'havonah'   => $avgs['expert'],
        'iyun'      => $avgs['genius'],
        'non_cumulative_track_passed'   => $track_passed ? $types[$track_passed] : '',
        'cumulative_track_passed'   => ucwords(calculateCumulative($student)),
//        'time_committed'    => $learning_time[$student['test_type']] * $days,
//        'time_learned'  => $learned,
        'dropped_out'   => intval($student['dropped_out']),
        'reason'        => $student['reason'] ?? '',
        'khk_eligible'  => isset($khk[$student['user_id']]) && $khk[$student['user_id']] ? true : false,
        'khk_experience'    => $student['khk_experience'] ?? '',
    ];
}

echo json_encode([
    'info'      => $info,
    'summary'   => $summary,
    'school_confirmed' => $locked,
    'super'     => ($admin_user['auth'] == 'super'),
]);

function getPassingAvg($id) {
    global $ct;

    $avgs = $ct->getPassingAvgs($id);
    $passing = $avgs['maven'];
    foreach ($avgs as $avg) {
        if (intval($avg) != intval($passing)) {
            $passing = 'different per track';
            break;
        }
    }
    return $passing;
}

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
        if ($total[$type] > 0) $avgs[$type] = round(($total[$type] / ($num_questions[$type] * $test_num)) * 100);
        else $avgs[$type] = 0;
    }

    return $avgs;
}

function calculateCumulative($child) {
    global $ct, $scores, $test_num;
    if (isset($scores[$child['th_chidon_id']]))
        return $ct->calculateCumulative($child, $scores[$child['th_chidon_id']], $test_num);
    else
        return '';
}