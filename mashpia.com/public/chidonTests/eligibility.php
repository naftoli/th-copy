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

function getNeededMark($school_id, $class_id, $type = '') {
    if (in_array($school_id, [5])) return 85;
    if (in_array($school_id, [81])) return 80;
    if (in_array($school_id, [106]) && $type != 'maven') return 80;
    if (in_array($school_id, [255]) && $type != 'maven') return 75;
    if (in_array($school_id, [255]) && $type == 'maven') return 80;
    if (in_array($school_id, [4])) return 77;
    if (in_array($class_id, [6088, 6089, 6090])) return 75;
    return 70;
}

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
<div class="infobox">
    The eligibility has been calculated by the system based on the number of questions answered correctly on the four Chidon tests.
    <br /><br/>
    When it says 'Yes' in the Trophy Contestant column, it means that this child is eligible to take the Trophy Final
    because they have a passing average on their 4 Trophy tests. (Not that this child is a Representative).
</div>
<?php
$types = $ct->getTypes();
$types['trophy'] = 'Trophy';
echo "<table><tr><th>Chidon ID</th><th>School</th></th><th>Grade</th><th>First Name</th><th>Last Name</th>
        <th>Test Type</th><th>Sweater</th><th>Gifts</th><th>Prize & Trips</th><th>Trophy Contestant</th>";
echo "</tr>";
foreach ($info as $school => $children) {
    if (empty($children)) continue;
    foreach ($children as $child) {
        $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
        $id = $child['th_chidon_id'];
        echo "<tr><td>" . $id . "</td><td>" . $schools[$school] . "</td><td>" . $grade . "</td><td>" .
            $child['first'] . "</td><td>" . $child['last'] . "</td>";
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
                    if ($final >= getNeededMark($child['school_id'], $child['class_id'], $type)) $eligible = 'yes';
                    else $eligible = 'no';
                    if ($type == 'pro') $pro_elig = $eligible;
                    if ($child['test_type'] == 'pro' && $pro_elig == 'yes' && $type == 'expert') $eligible = 'yes';
                    break;
                case 'trophy':
                    if ($final >= 80) $eligible = 'yes';
                    else $eligible = 'no';
                    break;
            }
            echo "<td>" . $eligible . "</td>";
        }
        echo "</tr>";
    }
}
echo "</table>";
?>
</body>
</html>