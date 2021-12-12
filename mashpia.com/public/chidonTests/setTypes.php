<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    $today = new DateTime();
//    $shutdown = new DateTime('2020-12-18 05:00:00');
//    if ($today >= $shutdown) {
//        echo "This page is now closed.";
//        exit;
//    }
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

if (isset($_POST['submit'])) {
    $ct->setTestTypes($_POST['type']);
    $ct->setRewardTypes($_POST['reward_type']);
    header("Location: enterScores.php");
    exit;
}

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks[$id] = $ct->getMarks();
}

function markInfo( $child, $school_id ) {
    global $marks;

    // get child mark info
    $childMarkInfo = $marks[$school_id][$child['th_chidon_id']];

    // get marks / avgs for child per type
    $ct = new ChidonTests();
    $types = $ct->getTypes();
    $marksPerType = [];
    $avgs = [];
    foreach ($types as $type => $val) {
        $marksPerType[$type] = 0;
        $avgs[$type] = 0;
    }

    $numTests = count($childMarkInfo);
    for ($i = 1; $i <= 4; $i++) {
        if (isset($childMarkInfo[$i])) {
            foreach ($childMarkInfo[$i] as $type => $mark) {
                if ($mark > 0) {
                    $marksPerType[$type] += $mark;
                }
            }
        }
    }

    // calculate avgs; highest type currently eligible for
    $highest_type = '';
    $highest_mark = 0;
    foreach ($types as $type => $val) {
        if (isset($marksPerType[$type])) {
            $avg = $marksPerType[$type] / $numTests;
            $avgs[$type] = $avg;
            if ($avg >= 70) {
                $highest_type = $type;
                $highest_mark = $avg;
            }
        }
    }

    $markInfo = [];
    $markInfo['avg'] = $avgs[$child['test_type']] ?? 0;
    $markInfo['highest_track'] = $highest_type;
    $markInfo['highest_track_avg'] = $highest_mark;

    return $markInfo;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Set Chidon Test Type</title>
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
        <h1>Chidon Test Types</h1>
        <?php
        $types = $ct->getTypes();
        echo "<form action='' method='post'>";
        echo "<div style='float: right'><input type='submit' name='submit' value='Save & go to Test Scoring' style='padding: 12px; font-size: large' /></div>";
        echo "<div style='clear: both'></div>";
        foreach ($info as $school => $children) {
            if (empty($children)) continue;
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Test Type</th><th>Avg Mark</th>
                <th>Highest Track Passed</th><th>Avg Mark</th><th>Reward Type for Child</th></tr>";
            foreach ($children as $child) {
                $markInfo = markInfo($child, $school);
                $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
                $name = $child['first'] . ' ' . $child['last'];
                echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td><td class='type'>";
                $default = 'expert';
                echo "<select name='type[" . $child['th_chidon_id'] . "]'>";
                foreach ($types as $type => $value) {
                    echo "<option value='" . $type . "'";
                    if ($child['test_type'] == $type) echo " selected ";
                    echo ">" . $value . "</option>";
//                    echo "<input type='radio' name='type[" . $child['th_chidon_id'] . "]' value='" . $type . "'";
//                    if ($child['test_type'] == $type) echo " checked";
//                    if ($type == $default && empty($child['test_type'])) echo " checked";
//                    echo " />" . ucwords($value) . ' ';
                }
                echo "</select></td><td>" . $markInfo['avg'] . "</td><td>" . $types[$markInfo['highest_track']] . "</td><td>";
                echo $markInfo['highest_track_avg'] . "</td><td>";
                echo "<select name='reward_type[" . $child['th_chidon_id'] . "]'>";
                $types[] = ['highest track passed' => 'Highest Track Passed'];
                foreach ($types as $type => $value) {
                    if ($type == 'genius') continue;
                    echo "<option value='" . $type . "'";
                    if ($type == $child['reward_type']) echo " selected ";
//                    else if (
//                        ($markInfo['highest_track'] != '' && $type == $markInfo['highest_track']) ||
//                        ($markInfo['highest_track'] == '' && $type == $child['test_type'])
//                    )
//                        echo " selected ";
//                    echo ">" . $value . "</option>";
                }
                echo "</select></td></tr>";
            }
            echo "</table>";
        }
        echo "<div style='float: right'><input type='submit' name='submit' value='Save & go to Test Scoring' style='padding: 12px; font-size: large' /></div>";
        echo "</form>";
        ?>
    </body>
    <script>
        $(function() {
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
            alert('Please make sure to SAVE after making changes.');
        })
    </script>
</html>
