<?php
//ini_set('display_errors',1);
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools( 175069, 'super', true, true ); // add chidon schools
$schools = $as->getSchools();

$ct = new ChidonTests();

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
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
            $test['mivtzahMaven'] = floatval(($marks[$id][$i]['maven'] / $questions) * 100);
            $test['shabbatonMark'] = floatval(($marks[$id][$i][$test_type] / $questions) * 100);
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