<?php
//ini_set('display_errors',1);
// set headers
header('Access-Control-Allow-Origin: '. ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : "*" ) ); // CORS

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$test_num = intval($_GET['test']);
$school_id = intval($_GET['school_id']);
$class_id = intval($_GET['class_id']);
$user_id = intval($_GET['user_id']);

if ($school_id > 0) {
    $schools = [$school_id => 'school'];
} else {
    $as = new AdminSchools(175069, 'super', true, true); // add chidon schools
    $schools = $as->getSchools();
}

$ct = new ChidonTests();

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id, $class_id, $user_id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks = $ct->getMarks();
}

$result = [];
foreach ($info as $school => $users) {
    foreach ($users as $user) {
        $id = $user['th_chidon_id'];
        $name = $user['first'] . ' ' . $user['last'];
        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
        $avgRequired = 70;
        $test_type = $user['test_type'];
        if ($test_type == 'expert') $questions = 15;
        else $questions = 10;
        $tests = [];
        for ($i = 1; $i <= 4; $i++) {
            $test['mivtzahMaven'] = $marks[$id][$i]['maven'];
            $test['shabbatonMark'] = $marks[$id][$i][$test_type];
            $tests[] = $test;
        }
        $result[] = [
            'id' => $id,
            'name' => $name,
            'grade' => $grade,
            'avgRequired' => $avgRequired,
            'tests' => $tests
        ];
    }
}
echo json_encode($result);