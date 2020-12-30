<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$fields = $_POST['fields'];
$tests = [];
foreach ($fields as $field) {
    if (strpos($field, '_')) {
        $info = explode('_', $field);
        $tests[] = $info[1];
    }
}
//echo "<pre>"; print_r($_POST); echo "</pre>";

require 'class.chidonTests.php';
$ct = new ChidonTests();
// limit to gender if applicable
if (in_array('M', $fields)) {
    $ct->setGender('M');
} else if (in_array('F', $fields)) {
    $ct->setGender('F');
}
$school_id = $_POST['school_id'];
if ($school_id > 0) $ct->setStudents($school_id);
else $ct->setStudents();
$ct->setScores();
$ct->calculateMarks();

$users = $ct->getStudents();
$marks = $ct->getMarks();

$show_avg = in_array('avg', $fields);
?>
<table>
    <tr>
        <th>Chidon ID</th>
        <th>School</th>
        <th>Grade</th>
        <th>Student</th>
        <th>Test Type</th>
        <?php
        foreach ($tests as $test_num) {
            echo "<th>Test #" . $test_num . "</th>";
        }
        if ($show_avg) echo "<th>Avg</th>";
        ?>
    </tr>
    <?php
    foreach ($users as $user) {
        $total = 0; // for avg if needed
        $id = $user['th_chidon_id'];
        $type = $user['test_type'];
        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
        echo "<tr><td>" . $id . "</td><td>" . $user['school_name'] . "</td><td>" . $grade . "</td><td>" .
            $user['first'] . ' ' . $user['last'] . "</td><td>" . $type . "</td>";
        foreach ($tests as $test_num) {
            $mark = $marks[$id][$test_num][$type];
            echo "<td>" . $mark . "</td>";
            $total += $mark;
        }
        // if we need avg, calculate it
        if ($show_avg) {
            $avg = $total / count($tests);
            echo "<td>" . $avg . "</td>";
        }
        echo "</tr>";
    }
    ?>
</table>