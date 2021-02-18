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
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Eligibility Report</title>
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
<h1>Eligibility Report</h1>
<div class="infobox">The eligibility has been calculated by the system based on the number of questions answered correctly.</div>
<?php
$types = $ct->getTypes();
$types['trophy'] = 'Trophy';
foreach ($info as $school => $children) {
    if (empty($children)) continue;
    echo "<h2>" . $schools[$school] . "</h2>";
    echo "<table><tr><th>Chidon ID</th><th>Grade</th><th>Student</th><th>Test Type</th><th>Sweater</th><th>Gifts</th>
        <th>Prize & Trips</th><th>Trophy Contestant</th>";
    echo "</tr>";
    foreach ($children as $child) {
        $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
        $name = $child['first'] . ' ' . $child['last'];
        $id = $child['th_chidon_id'];
        echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
        foreach ($types as $type => $value) {
            if ($child['test_type'] == $type) echo "<td>" . ucwords($value) . "</td>";
        }
        $pro_elig = '';
        foreach ($types as $type => $value) {
            $total = 0;
            for ($i = 1; $i <= 4; $i++) {
                $mark = isset($marks[$id][$i][$type]) ? $marks[$id][$i][$type] : 0;
                $total += $mark;
            }
            $final = round($total / 4, 2);
            switch ($type) {
                case 'maven':
                case 'pro':
                case 'expert':
                    if ($final >= 70) $eligible = 'yes';
                    else $eligible = 'no';
                    if ($type == 'pro') $pro_elig = $eligible;
                    if ($child['test_type'] == 'pro' && $pro_elig == 'yes' && $type == 'expert') $eligible = 'yes';
                    break;
                case 'trophy':
                    if ($final > 80) $eligible = 'yes';
                    else $eligible = 'no';
                    break;
            }
            echo "<td>" . $eligible . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
?>
</body>
</html>