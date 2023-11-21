<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks += $ct->getMarks();
}
//echo "<pre>"; print_r($info); echo "</pre>";
$testNumber = isset($_GET['test_num']) ? $_GET['test_num'] : 1;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Review Marks</title>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
            td:not(.type) {
                vertical-align: top;
            }
            body {
                display: none;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Review Marks</h1>
        <h2>Test #<?= $testNumber ?></h2>
        <div class="infobox">The mark has been calculated by the system based on the number of questions answered correctly.</div>
        <?php
        $types = $ct->getTypes();
        echo "<a href='setTypes.php'><input type='button' value='Edit Test Type' style='padding: 12px; font-size: large' /></a>";
        echo "<div style='float: right'><a href='enterScores.php?test_num=" . $testNumber . "'><input type='button' value='Edit Test " . $testNumber . " Scores' style='padding: 12px; font-size: large' /></a></div>";
        foreach ($info as $school => $children) {
            if (empty($children)) continue;
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Track Chosen</th>";
            foreach ($types as $type => $value) {
                echo "<th>" . ucwords($value) . " Correct</th>";
            }
            echo "<th>Avg To Date</th>";
            echo "</tr>";
            foreach ($children as $child) {
                $avgs = $ct->getPassingAvgs($child['user_id']);
                $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                $name = $child['first'] . ' ' . $child['last'];
                $id = $child['th_chidon_id'];
                echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
                foreach ($types as $type => $value) {
                    if ($child['test_type'] == $type) echo "<td>" . ucwords($value) . "</td>";
                }
                foreach ($types as $type => $value) {
                    $mark = isset($marks[$id][$testNumber][$type]) ? $marks[$id][$testNumber][$type] : 0;
                    $color = 'grey';
                    if ($child['test_type'] == $type) {
                        $color = 'black';
                        if ($mark < $avgs[$type]) $color = 'red';
                    }
                    echo "<td style='color: $color;'>" . $mark . "%</td>";
                }
                // figure out avg
                $avg = 0;
                $total = 0;
                if (isset($marks[$id])) {
                    for ($i = 1; $i <= $testNumber; $i++) {
                        $total += $marks[$id][$i][$child['test_type']];
                    }
                    if ($total > 0) $avg = number_format($total / $testNumber, 2);
                }
                echo "<td>" . $avg . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </body>
    <?php if ($admin_user['auth'] != 'super') : ?>
    <script>
        // BCM IA wants to have the page only show when entering a password. not secure but makes her beleive it's secure.
        const school_id = <?=$admin_user['auths']['school'][0]?>;
        if (school_id == 176) {
            // password protect
            const password = 'laky';
            let pass = '';
            while (pass != password) {
                pass = prompt('Please enter password.');
            }
        }
        $('body').show();
    </script>
    <?php endif; ?>
</html>