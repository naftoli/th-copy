<?php
//ini_set('display_errors',1);
require '../db.php';
$year = mysql_real_escape_string($_POST['year']);
$school = mysql_real_escape_string($_POST['school']);

$students = array();
$result = mysql_query("SELECT * FROM th_chidon WHERE year = " . $year . " AND school_id = " . $school);
while ($row = mysql_fetch_assoc($result)) {
    $students[] = $row;
}

$chap_check = mysql_query("SELECT * FROM th_chidon_schools WHERE year = " . $year . " AND school_id = " . $school);
if(mysql_num_rows($chap_check) == 0){
    echo json_encode([
        "success"   => false,
        "chap"      => false
    ]);
    die();
}

foreach ($students as $student) {
    $chidon_id = $student['th_chidon_id'];
    $avg = (intval($student['test1a']) + intval($student['test2a']) + intval($student['test3a'])) / 3;
    //echo $avg . "<br />";
    if ($avg >= 70) {
        mysql_query("UPDATE th_chidon SET contestant = 1 WHERE th_chidon_id = " . $chidon_id);
    } if ($avg < 70 && $student['contestant'] == "1") {
        mysql_query("UPDATE th_chidon SET contestant = 0, school_rep = 0 WHERE th_chidon_id = " . $chidon_id);
    }
}

echo json_encode([
    "success"   => true,
    "chap"      => true
]);
die();