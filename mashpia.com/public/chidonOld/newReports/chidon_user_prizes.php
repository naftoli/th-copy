<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

function getNeededMark($school_id, $class_id, $type = '') {
    return 70;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$prizes = [];
$sql = "select * from chidon_prizes where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}

$user_prizes = [];
$sql = "select * from chidon_user_prizes where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $user_prizes[$row['user_id']][] = $row['prize_id'];
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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Chidon Student Prizes</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
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
        <h1>Chidon Student Prizes</h1>
        <?php
        $types = $ct->getTypes();
        $types['trophy'] = 'Trophy';
        $types['trophy_extra'] = '';

        echo "<table><tr><th>Chidon ID</th><th>School</th></th><th>Grade</th><th>First Name</th><th>Last Name</th>
        <th>Test Type</th><th>Prize(s) Selected</th></tr>";
        foreach ($info as $school => $children) {
            if (empty($children)) continue;
            foreach ($children as $child) {
                $id = $child['th_chidon_id'];

                // figure out marks
                $child_marks = [];
                foreach ($types as $type => $value) {
                    $total = 0;
                    for ($i = 1; $i <= 4; $i++) {
                        $mark = isset($marks[$id][$i][$type]) ? $marks[$id][$i][$type] : 0;
                        $total += $mark;
                    }
                    $final = round($total / 4, 2);
                    $child_marks[$type] = $final;
                }
//        echo "<pre>"; print_r($child_marks); echo "</pre>";

                $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
                echo "<tr><td>" . $id . "</td><td>" . $schools[$school] . "</td><td>" . $grade . "</td><td>" .
                    $child['first'] . "</td><td>" . $child['last'] . "</td>";
                foreach ($types as $type => $value) {
                    if ($child['test_type'] == $type) echo "<td>" . ucwords($value) . "</td>";
                }

                $pro_elig = '';
                $expert_elig = '';
                foreach ($child_marks as $type => $final) {
                    if ($type == 'trophy_extra') continue;
                    switch ($type) {
                        case 'maven':
                        case 'pro':
                        case 'expert':
                            if ($final >= getNeededMark($child['school_id'], $child['class_id'], $type)) $eligible = 'yes';
                            else $eligible = 'no';
                            if ($type == 'pro') $pro_elig = $eligible;
                            if ($type == 'expert') $expert_elig = $eligible;
                            if ($child['test_type'] == 'pro' && $pro_elig == 'yes' && $type == 'expert') $eligible = 'yes';
                            break;
                        case 'trophy':
                            if (
                                ($expert_elig == 'yes' && $final >= 80) || ($child_marks['trophy_extra'] >= 80)
                            ) $eligible = 'yes';
                            else $eligible = 'no';
                            break;
                    }
                    echo "<td>";
                    if ($eligible == 'no') {
                        echo "Not Eligible for Prizes";
                    } else {
                        foreach ($user_prizes[$child['user_id']] as $prize_id) {
                            $prize = $prizes[$prize_id];
                            echo $prize['prize_name'] . ($prize['color'] ? ' - Color: ' . $prize['color'] : '') . ($prize['size'] ? ' - Size: ' . $prize['size'] : '') . ', ';
                        }
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        ?>
    </body>
</html>