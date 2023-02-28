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
$num_tests = 3;

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
$types = ['maven', 'pro', 'expert', 'genius'];
foreach ($info as $school => $children) {
    foreach ($children as $child) {
        $id = $child['th_chidon_id'];
        $grade = $child['class_grade'];
        $totalAvg = 0;
        foreach ($types as $type) {
            $total = 0;
            for ($i = 1; $i <= $num_tests; $i++) {
                $mark = isset($marks[$id][$i][$type]) ? $marks[$id][$i][$type] : 0;
                $total += $mark;
            }
            $avg = round($total / $num_tests, 2);
            $totalAvg += $avg;
        }
        $final = $totalAvg / count($types);
        $child_marks[$schools[$school]][$child['gender']][$grade][$id] = $final;

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
    foreach ($more as $gender => $other) {
        foreach ($other as $grade => $more) {
            arsort($child_marks[$school][$gender][$grade]);
        }
    }
}
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
foreach ($child_marks as $school => $other) {
    echo "<h2>" . $school . "</h2>";
    foreach ($other as $gender => $more) {
        if ($gender == 'M') echo "<h2>Boys</h2>";
        else if ($gender == 'F') echo "<h2>Girls</h2>";
        else echo "<h2></h2>";
        ?>
        <table>
            <thead>
            <tr>
                <th>Chidon ID</th>
                <th>Grade</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Avg</th>
                <th>School Rep</th>
                <th>KHK Rep</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($more as $grade => $other) {
                $i = 1;
                foreach ($other as $id => $avg) {
                    echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $child_info[$id]['first'] . "</td><td>" .
                        $child_info[$id]['last'] . "</td><td>" . $avg . "</td><td>";
                    echo "<input type='checkbox' class='rep' id='$id' ";
                    if (intval($child_info[$id]['rep'])) echo " checked ";
                    echo "disabled /></td><td>";
                    echo "<input type='checkbox' class='khk' id='$id' ";
                    if (intval($child_info[$id]['khk']) && intval($child_info[$id]['khk_rep'])) echo " checked ";
                    echo "disabled /></td></tr>";
                }
            }
            ?>
            </tbody>
        </table>
        <?php
    }
}
?>
</body>
</html>