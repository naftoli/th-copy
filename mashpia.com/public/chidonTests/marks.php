<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$testNumber = isset($_GET['test_num']) ? $_GET['test_num'] : 1;

if (isset($_POST['submit'])) {
    $ct->insertScores($_POST['scores']);
}

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks = $ct->getMarks();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Enter Test Score</title>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
            td:not(.type) {
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Enter Test Score</h1>
        <h2>Test #<?= $testNumber ?></h2>
        <div class="infobox">Please enter the <strong>number</strong> of questions scored correctly. The system will calculate the correct mark.</div>
        <?php
        $types = $ct->getTypes();
        $types['trophy'] = 'Trophy';
        echo "<form action='' method='post'>";
        echo "<a href='setTypes.php'><input type='button' value='Edit Level' style='padding: 12px; font-size: large' /></a>";
        echo "<div style='float: right'><a href='enterScores.php'><input type='button' value='Edit Test 1 Scores' style='padding: 12px; font-size: large' /></a></div>";
        foreach ($info as $school => $children) {
            if (empty($children)) continue;
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table><tr><th>Chidon ID</th><th>Grade</th><th>Student</th><th>Test Type</th>";
            foreach ($types as $type => $value) {
                echo "<th>" . ucwords($value) . " Mark</th>";
            }
            echo "<th>Trophy Mark</th></th></tr>";
            foreach ($children as $child) {
                $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
                $name = $child['first'] . ' ' . $child['last'];
                $id = $child['th_chidon_id'];
                echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
                foreach ($types as $type => $value) {
                    if ($child['test_type'] == $type) echo "<td>" . ucwords($value) . "</td>";
                }
                foreach ($types as $type => $value) {
                    $mark = isset($marks[$id][$testNumber][$type]) ? $marks[$id][$testNumber][$type] : 0;
                    $color = 'grey';
                    if ($child['test_type'] == $type) {
                        $color = 'black';
                        if ($mark < 70) $color = 'red';
                    }
                    echo "<td style='color: $color;'>" . $mark . "%</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "</form>";
        ?>
    </body>
</html>