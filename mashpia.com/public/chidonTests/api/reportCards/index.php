<?php
//ini_set('display_errors',1);
// set headers
header('Access-Control-Allow-Origin: '. ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : "*" ) ); // CORS

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$totalTests = 3;
$test_num = intval($_GET['test']);
$school_id = intval($_GET['school_id']);
$class_id = intval($_GET['class_id']);
$user_id = intval($_GET['user_id']);

if ($school_id > 0) {
    $sql = "select school_name from schools where school_id = " . $school_id;
    $result = mysql_query($sql);
    $school_name = mysql_fetch_assoc($result)['school_name'];
    $schools = [$school_id => $school_name];
} else {
    $as = new AdminSchools(175069, 'super', true, true); // add chidon schools
    $schools = $as->getSchools();
}

$ct = new ChidonTests();
$types = $ct->getTypes();
$testQuestions = $ct->getTestQuestions();

$info = [];
$marks = [];
$scores = [];

foreach ($schools as $id => $school) {
    $ct->setStudents($id, $class_id, $user_id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks[$id] = $ct->getMarks();
    $scores[$id] = $ct->getScores();
}

$result = [];
foreach ($info as $school => $users) {
    foreach ($users as $user) {
        $id = $user['th_chidon_id'];
        $name = $user['first'] . ' ' . $user['last'];
        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');

        $tests = [];
        $totalMarks = 0;
        $avgRequired = 0;
        $test_type = $user['test_type'];
        for ($i = 1; $i <= $test_num; $i++) {
            $totalMarks += floatval($marks[$school][$id][$i][$test_type]);
            $tests[$i] = [
                'maven'     => $marks[$school][$id][$i]['maven'],
                'pro'       => $marks[$school][$id][$i]['pro'],
                'expert'    => $marks[$school][$id][$i]['expert'],
                'genius'    => $marks[$school][$id][$i]['genius']
            ];
        }
        $testsLeft = $totalTests - $test_num;
        $avgs = $ct->getPassingAvgs($user['user_id']);
        // need an {avg} for all tests to maintain the average
        $avgRequired = $test_num < $totalTests ? round((($avgs[$test_type] * $totalTests) - $totalMarks) / $testsLeft) : 0;

        $highestTrack = '';
        $trackMarks = [];
        foreach ($types as $type => $value) {
            $trackMarks[$type] = 0;
            for ($i = 1; $i <= $test_num; $i++) {
                $trackMarks[$type] += $marks[$school][$id][$i][$type];
            }
            $trackMarks[$type] /= $test_num;
            if ($trackMarks[$type] >= $avgs[$type]) $highestTrack = $value;
        }

        $result[] = [
            'id'                    => $id,
            'name'                  => $name,
            'grade'                 => $grade,
            'avgs'                  => $avgs,
            'avgRequired'           => $avgRequired,
            'highestTrackPassed'    => $highestTrack,
            'tests'                 => $tests,
            'scores'                => $scores[$school][$id],
            'questions'             => $testQuestions,
            'school'                => $schools[$school],
            'currentTrack'          => $types[$test_type],
            'types'                 => $types,
            'user_id'               => $user['user_id'],
            'track'                 => $test_type
        ];
    }
}

echo json_encode($result);