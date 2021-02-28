<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
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
//echo "<pre>"; print_r($marks); echo "</pre>"; exit;
$child_marks = [];
$child_info = [];
foreach ($info as $school => $children) {
    foreach ($children as $child) {
        $id = $child['th_chidon_id'];
        $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
        $total = 0;
        for ($i = 1; $i <= 4; $i++) {
            $mark = isset($marks[$id][$i]['trophy_extra']) ? $marks[$id][$i]['trophy_extra'] : 0;
            $total += $mark;
        }
        $final = round($total / 4, 2);
        $child_marks[$schools[$school]][$grade][$id] = $final;
        $child_info[$id] = [
            'first' =>  $child['first'],
            'last'  =>  $child['last'],
            'khk'   =>  $child['khk_rep'],
            'rep'   =>  $child['school_rep']
        ];
    }
}
// sort by mark desc
foreach ($child_marks as $school => $more) {
    foreach ($more as $grade => $other) {
        arsort($child_marks[$school][$grade]);
    }
}
//echo "<pre>"; print_r($child_marks); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>School Representatives Report</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 14px;
            padding: 5px;
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>School Representatives Report</h1>
<?php
foreach ($child_marks as $school => $more) {
    echo "<h2>" . $school . "</h2>";
    ?>
    <table>
        <thead>
            <tr>
                <th>Chidon ID</th>
                <th>Grade</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Avg (3 Parts)</th>
                <th>KHK Rep</th>
                <th>Actual School Rep</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($more as $grade => $other) {
                foreach ($other as $id => $avg) {
                    echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $child_info[$id]['first'] . "</td><td>" .
                        $child_info[$id]['last'] . "</td><td>" . $avg . "</td><td>";
                    echo "<input type='checkbox' class='khk' id='$id' ";
                    if ($child_info[$id]['khk']) echo " checked ";
                    echo " disabled /></td><td>";
                    echo "<input type='checkbox' class='contestant' id='$id' ";
                    if ($child_info[$id]['rep']) echo " checked ";
                    echo " disabled /></td></tr>";
                }
                ?>
                <tr>
                    <th>Chidon ID</th>
                    <th>Grade</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Avg (3 Parts)</th>
                    <th>KHK Rep</th>
                    <th>Actual School Rep</th>
                </tr>
            <?php
            }
        ?>
        </tbody>
    </table>
    <?php
}
?>
</body>
</html>