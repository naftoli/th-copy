<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$fields = $_POST['fields'];
$tests = [];
$trophies = [];
$email = false;
foreach ($fields as $field) {
    if (strpos($field, '_') !== false) {
        $info = explode('_', $field);
        $number = $info[1];
        if ($info[0] == 'test') $tests[] = $number;
        else if ($info[0] == 'trophy') $trophies[] = $number;
    } else if ($field == 'email') {
        $email = true;
    }
}
//echo "<pre>"; print_r($tests); print_r($trophies); echo "</pre>";

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
        <th>Family ID</th>
        <th>School</th>
        <th>Grade</th>
        <th>Student</th>
        <?php if ($email) : ?><th>Email Address</th><?php endif; ?>
        <th>Test Type</th>
        <?php
        if (!empty($tests)) {
            foreach ($tests as $test_num) {
                echo "<th>Test #" . $test_num . "</th>";
            }
            if ($show_avg) echo "<th>Test Avg</th>";
        }
        if (!empty($trophies)) {
            foreach ($trophies as $num) {
                echo "<th>Trophy Test #" . $num . "</th>";
            }
            if ($show_avg) echo "<th>Trophy Avg</th>";
        }
        ?>
    </tr>
    <?php
    foreach ($users as $user) {
        $total = 0; // for avg if needed
        $id = $user['th_chidon_id'];
        $type = $user['test_type'];
        $admin_id = $user['parent_id'];
        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
        echo "<tr><td>" . $id . "</td><td>" . $admin_id . "</td><td>" . $user['school_name'] . "</td><td>" .
                $grade . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>";
        if ($email) echo $user['admin_email'] . "</td><td>";
        echo $type . "</td>";
        // show tests
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
        // show trophies
        $trophy_total = 0;
        foreach ($trophies as $test_num) {
            $mark = $marks[$id][$test_num]['trophy'];
            echo "<td>" . $mark . "</td>";
            $trophy_total += $mark;
        }
        // if we need avg, calculate it
        if ($show_avg) {
            $trophy_avg = $trophy_total / count($trophies);
            echo "<td>" . $trophy_avg . "</td>";
        }
        echo "</tr>";
    }
    ?>
</table>