<?php
//ini_set('display_errors',1);
// set headers
header('Access-Control-Allow-Origin: '. ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : "*" ) ); // CORS

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();
$testQuestions = $ct->getTestQuestions();

$test_num = isset($_GET['test']) ? intval($_GET['test']) : $ct->figureOutTestNum();;
$school_id = intval($_GET['school_id']);
$class_id = intval($_GET['class_id']);
$user_id = intval($_GET['user_id']);

$info = [];
$marks = [];
$scores = [];

if ($user_id > 0 || $class_id > 0) {
    $ct->setStudents($school_id, $class_id, $user_id);
    $info[$school_id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks += $ct->getMarks();
    $scores += $ct->getScores();
} else {
    if ($school_id > 0) {
        $sql = "select school_name from schools where school_id = " . $school_id;
        $result = mysql_query($sql);
        $school_name = mysql_fetch_assoc($result)['school_name'];
        $schools = [$school_id => $school_name];
    } else {
        $as = new AdminSchools(175069, 'super', true, true); // add chidon schools
        $schools = $as->getSchools();
    }
    foreach ($schools as $id => $school) {
        $ct->setStudents($id, $class_id, $user_id);
        $info[$id] = $ct->getStudents();
        $ct->setScores();
        $ct->calculateMarks();
        $marks += $ct->getMarks();
        $scores += $ct->getScores();
    }
}

$result = [];
foreach ($info as $school => $users) {
    foreach ($users as $user) {
        $id = $user['th_chidon_id'];
        $name = $user['first'] . ' ' . $user['last'];
        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
        $tests = $marks[$id];

        $avgs = $ct->getPassingAvgs($user['user_id']);
        $highestTrack = $ct->getHighestTrack($marks[$id], $user['user_id'], false, $test_num);
        $highestTrackPassed = $types[ $highestTrack ];

        // find out if passed iyun
        $iyun_score = $ct->getCumulativeScore($scores[$id], $test_num);
        $iyun_passed = $iyun_score['genius'] >= 90;
        if ($highestTrack != 'genius' && $iyun_passed) {
            $highestTrack = 'genius';
            $highestTrackPassed = $types[ $highestTrack ];
        }

        $result[] = [
            'id'                    => $id,
            'name'                  => $name,
            'grade'                 => $grade,
            'avgs'                  => $avgs,
            'highestTrack'          => $highestTrack,
            'highestTrackPassed'    => $highestTrackPassed,
            'tests'                 => $tests,
            'scores'                => $scores[$id],
            'questions'             => $testQuestions,
            'school'                => $schools[$school],
            'currentTrack'          => $types[$user['test_type']],
            'types'                 => $types,
            'user_id'               => $user['user_id'],
            'track'                 => $user['test_type'],
        ];
    }
}

echo json_encode($result);