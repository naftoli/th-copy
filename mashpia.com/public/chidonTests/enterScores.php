<?php
//ini_set('display_errors', 1);
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
$scores = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $scores[$id] = $ct->getScores();
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
        echo "<div style='float: right'><input type='submit' name='submit' value='Save' style='padding: 12px; font-size: large' /></div>";
        echo "<a href='setTypes.php'><input type='button' value='Go back to change test level' style='padding: 12px; font-size: large' /></a>";
        foreach ($info as $school => $children) {
            if (empty($children)) continue;
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table><tr><th>Chidon ID</th><th>Grade</th><th>Student</th><th>Test Type</th>";
            foreach ($types as $type => $value) {
                echo "<th>" . ucwords($value) . " Score</th>";
            }
            echo "<th>Trophy Score</th></th></tr>";
            foreach ($children as $child) {
                $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
                $name = $child['first'] . ' ' . $child['last'];
                $id = $child['th_chidon_id'];
                echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
                if (empty($child['test_type'])) $default = true;
                else $default = false;
                foreach ($types as $type => $value) {
                    if ($child['test_type'] == $type) echo "<td class='type'>" . ucwords($value) . "</td>";
                }
                foreach ($types as $type => $value) {
                    $class = 'score';
                    if ($type == 'expert') $class = 'expert';
                    $score = isset($scores[$school][$id][$testNumber][$type]) ? $scores[$school][$id][$testNumber][$type] : 0;
                    echo "<td><input type='text' name='scores[$id][$testNumber][$type]' value='" . $score . "' size='4' class='$class' /></td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "<div style='float: right'><input type='submit' name='submit' value='Save' style='padding: 12px; font-size: large' /></div>";
        echo "</form>";
        ?>
    </body>
    <script>
        $(function() {
            alert('Please make sure to SAVE after entering scores.');
        })
        $(".score").keyup( function() {
            const max = 10;
            let val = $(this).val();
            if (parseInt(val) > max) {
                alert('Please be sure that you are entering the number of questions scored correctly, and NOT the test mark. It should not be higher than ' + max);
                $(this).val(0);
                $(this).focus();
            }
        });
        $(".expert").keyup( function() {
            const max = 15;
            let val = $(this).val();
            if (parseInt(val) > max) {
                alert('Please be sure that you are entering the number of questions scored correctly, and NOT the test mark. It should not be higher than ' + max);
                $(this).val(0);
                $(this).focus();
            }
        });
    </script>
</html>
