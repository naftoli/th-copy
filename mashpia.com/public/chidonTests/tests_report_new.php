<?php
function parseFields($fields) {
    foreach ($fields as $field) {
        switch ($field) {
            case 'user_serial':
                addTo('list', 'u.user_serial');
                break;
            case 'admin_id':
                addTo('list', 'a.admin_id');
                break;
            case 'school_name':
                addTo('list', 's.school_name');
                break;
            case 'admin_email':
                addTo('list', 'a.admin_email');
                break;
            case 'test_type':
                addTo('list', 'tc.test_type');
                break;
            case 'reward_type':
                addTo('list', 'tc.reward_type');
                break;
            case 'name':
                addTo('list', ['u.first', 'u.last']);
                break;
            case 'class':
                addTo('list', ['c.class_grade', 'c.class_sub']);
                break;
            case 'highest':
            case 'avg':
            case 'avgPerTest':
            case 'khk_avg':
            case 'final_award':
                addTo('aggregates', $field);
                break;
            case 'test_1':
            case 'test_2':
            case 'test_3':
            case 'test_4':
            case 'khk_1':
            case 'khk_2':
            case 'khk_3':
            case 'khk_4':
            case 'khk_final':
            case 'final_mark':
                addTo('tests', $field);
                break;
        }
    }
}

function addTo($type, $field) {
    global $list_of_fields, $aggregates, $tests;

    if (is_array($field)) {
        foreach ($field as $value) addTo($type, $value);
    } else {
        switch ($type) {
            case 'list':
                $list_of_fields[] = $field;
                break;
            case 'aggregates':
                $aggregates[] = $field;
                break;
            case 'tests':
                $tests[] = $field;
                break;
        }
    }
}

function createSql() {
    global $list_of_fields, $aggregates, $tests, $year;
    echo "<pre>"; print_r($list_of_fields); echo "</pre>";

    $tables = [];
    // SELECT
    $sql = "select ";
    foreach ($list_of_fields as $field) {
        $sql .= $field . ", ";
        $field_details = explode('.', $field);
        $alias = $field_details[0];
        switch ($alias) {
            case 'a':
                if (! in_array('admins', $tables)) $tables[] = 'admins';
                break;
            case 'u':
                if (! in_array('users', $tables)) $tables[] = 'users';
                break;
            case 's':
                if (! in_array('schools', $tables)) $tables[] = 'schools';
                break;
            case 'c':
                if (! in_array('classes', $tables)) $tables[] = 'classes';
                break;
            case 'tc':
                if (! in_array('th_chidon', $tables)) $tables[] = 'th_chidon';
                break;
            default:
                break;
        }
    }
    $sql = rtrim($sql, " ,");

    // FIGURE OUT ROOT TABLE
    if (in_array('th_chidon', $tables)) $root = 'th_chidon tc ';
    else if (in_array('users', $tables)) $root = 'users u ';

    // FROM
    $sql .= " from $root ";
    foreach ($tables as $table) {
        if (strpos($table, $root)) continue;
        switch ($table) {
            case 'users':
                $sql .= " left join users u using (user_id) ";
                break;
            case 'schools':
                $sql .= "left join schools s on s.school_id = u.school_id ";
                break;
            case 'classes':
                $sql .= "left join classes c on c.class_id = u.class_id ";
                break;
            case 'admins':
                $sql .= "left join admin_auths aa on aa.id = u.user_id left join admins a using (admin_id) ";
                break;
        }
    }

    // WHERE
    $sql .= " where year = " . $year . " and u.school_id = " . $_POST['school_id'];

    return $sql;
}

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

echo "<pre>"; print_r($_POST); echo "</pre>";

$fields = $_POST['fields'];
$tests = [];
$aggregates = [];
$list_of_fields = [];

parseFields($fields);

$sql = createSql();
echo $sql; exit;

$result = mysql_query($sql);

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
echo "<pre>"; print_r($marks); echo "</pre>"; exit;
//$show_avg = in_array('avg', $fields);
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
            $mark = isset($marks[$id][$test_num][$type]) ? $marks[$id][$test_num][$type] : 0;
            echo "<td>" . $mark . "</td>";
            $total += $mark;
        }
        // if we need avg, calculate it
        if ($show_avg && count($tests)) {
            $avg = $total / count($tests);
            echo "<td>" . $avg . "</td>";
        }
        // show trophies
        $trophy_total = 0;
        foreach ($trophies as $test_num) {
            $mark = isset($marks[$id][$test_num]['trophy']) ? $marks[$id][$test_num]['trophy'] : 0;
            echo "<td>" . $mark . "</td>";
            $trophy_total += $mark;
        }
        // if we need avg, calculate it
        if ($show_avg && count($trophies)) {
            $trophy_avg = $trophy_total / count($trophies);
            echo "<td>" . $trophy_avg . "</td>";
        }
        echo "</tr>";
    }
    ?>
</table>