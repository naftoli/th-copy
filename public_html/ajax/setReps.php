<?php
//ini_set('display_errors',1);
require '../db.php';
$year = mysql_real_escape_string($_POST['year']);
$school = mysql_real_escape_string($_POST['school']);

$students = array();
$sql = "SELECT c.class_grade, tc.* FROM th_chidon tc "
        ."JOIN schools s USING (school_id) "
        ."JOIN users u USING (user_id) "
        ."JOIN classes c ON c.class_id = u.class_id "
        ."WHERE tc.year = " . $year . " "
        ."AND tc.school_id = " . $school;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $students[] = $row;
}
// make sure there is a chaperone for the school in question
$chap_check = mysql_query("SELECT * FROM th_chidon_schools WHERE year = " . $year . " AND school_id = " . $school);
if(mysql_num_rows($chap_check) == 0){
    echo json_encode([
        "success"   => false,
        "chap"      => false
    ]);
    die();
}

$avgs = array();
$marks = array();
foreach ($students as $row) {
    $avg1 = (intval($row['test1a']) + intval($row['test2a']) + intval($row['test3a'])) / 3;
    $avg2 = (intval($row['test1b']) + intval($row['test2b']) + intval($row['test3b'])) / 3;
    if ($avg1 >= 70 && $avg2 >= 70) {
        $avg = ($avg1 + $avg2) / 2;
        $marks[$row['class_grade']][$avg][] = $row['th_chidon_id'];
    }
}
//echo "<pre>"; print_r( $marks ); echo "</pre>";

foreach ($marks as $grade => $more) {
    krsort($marks[$school]);
}
//echo "<pre>"; print_r($marks); echo "</pre>"; 
$i = 1;
$reps = array();
foreach ($marks as $grade => $results) {
    foreach ($results as $avg => $users) {
        foreach ($users as $user) {
            $reps[$grade][] = $user;
            // allow 2 reps per grade
            if (++$i == 3) {
                $i = 1;
                break 2; 
            }
        }        
    }
}
//echo "<pre>"; print_r($reps); echo "</pre>";

$qrys = array();
// first reset reps
$qrys[] = "UPDATE th_chidon SET school_rep = 0 WHERE year = " . $year . " AND school_id = " . $school;
foreach ($reps as $grade => $winners) {
    foreach ($winners as $id) {
         $qrys[] = "UPDATE th_chidon SET school_rep = 1 WHERE th_chidon_id = " . $id;
    }
}

$success = true;
mysql_query("set autocommit=0");
mysql_query("begin");
foreach ($qrys as $qry) {
    //echo $qry . "<br />";
    if (!mysql_query($qry)) {
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query("commit");
    mysql_query("set autocommit=1");
    echo json_encode([
        "success"   => true,
        "chap"      => true
    ]);
    die();
} else {
    mysql_query("rollback");
    mysql_query("set autocommit=1");
    echo json_encode([
        "success"   => false,
        "chap"      => true
    ]);
    die();
}