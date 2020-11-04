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
    $ct->insertMarks($_POST['marks']);
}

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
    $ct->setMarks();
    $marks[$id] = $ct->getMarks();
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
        $types = ['maven', 'pro', 'expert'];
        echo "<form action='' method='post'>";
        echo "<input type='submit' name='submit' value='Save' />";
        foreach ($info as $school => $children) {
            if (!empty($children)) {
                echo "<h2>" . $schools[$school] . "</h2>";
                echo "<table><tr><th>Chidon ID</th><th>Grade</th><th>Student</th><th>Test Type</th>";
                foreach ($types as $type) {
                    echo "<th>" . ucwords($type) . " Mark</th>";
                }
                echo "<th>Trophy Mark</th></th></tr>";
                foreach ($children as $child) {
                    $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
                    $name = $child['first'] . ' ' . $child['last'];
                    $id = $child['th_chidon_id'];
                    echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $name . "</td><td class='type'>";
                    if (empty($child['test_type'])) $default = true;
                    else $default = false;
                    foreach ($types as $type) {
                        echo "<input type='radio' name='type[" . $child['th_chidon_id'] . "]' value='" . $type . "'";
                        if ($child['test_type'] == $type) echo " checked";
                        if ($type == 'expert' && $default) echo " checked";
                        echo " disabled />" . ucwords($type) . "<br />";
                    }
                    foreach ($types as $type) {
                        $class = 'mark';
                        if ($type == 'expert') $class = 'expert';
                        $mark = isset($marks[$school][$id][$testNumber][$type]) ? $marks[$school][$id][$testNumber][$type] : 0;
                        echo "<td><input type='text' name='marks[$id][$testNumber][$type]' value='" . $mark . "' size='4' class='$class' /></td>";
                    }
                    $trophy_mark = isset($marks[$school][$id][$testNumber]['trophy']) ? $marks[$school][$id][$testNumber]['trophy'] : 0;
                    echo "<td><input type='text' name='marks[$id][$testNumber][trophy]' value='" . $trophy_mark . "' size='4' /></td>";
                    echo "</td></tr>";
                }
                echo "</table>";
            }
        }
        echo "<input type='submit' name='submit' value='Save' />";
        echo "</form>";
        ?>
    </body>
    <script>
        $(".mark").keyup( function() {
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
