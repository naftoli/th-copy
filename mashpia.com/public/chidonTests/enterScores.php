<?php
//ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$testNumber = isset($_REQUEST['test_num']) ? $_REQUEST['test_num'] : 1;

if (isset($_POST['scores'])) {
    $ct->insertScores($_POST['scores']);
    header("Location: marks.php?test_num=" . $testNumber);
    exit;
}

if ($admin_user['auth'] == 'super' || isset($_POST['submit'])) {
    $class_id = $_POST['grade'] ?? 0;
    $info = [];
    $scores = [];
    foreach ($schools as $id => $school) {
        $ct->setStudents($id, $class_id);
        $info[$id] = $ct->getStudents();
        $ct->setScores();
        $scores[$id] = $ct->getScores();
    }
}

echo "<pre>"; print_r($info); echo "</pre>"; exit;

// initialize all tests to not be disabled
$disabled = false;
$school_id = implode('', array_keys($schools));
// disable marking after certain dates for bc's
if ($admin_user['auth'] != 'super') {
    $today = new DateTime();
    $shutdown = [];
//    $shutdown[1] = new DateTime('2021-11-20 05:00:00');
//    $shutdown[2] = new DateTime('2021-12-26 05:00:00');
//    $shutdown[3] = new DateTime('2022-01-21 05:00:00');
//    $shutdown[4] = new DateTime('2022-02-16 14:00:00');
//    if ($shutdown[$testNumber] && $today >= $shutdown[$testNumber]) {
//        $disabled = true;
//    }
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
            body {
                display: none;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Enter Test Score</h1>
        <?php if ($admin_user['auth'] == 'super' || isset($_POST['submit'])) { ?>
            <h2>Test #<?= $testNumber ?></h2>
            <div class="infobox">Please enter the <strong>number</strong> of questions scored correctly. The system will calculate the correct mark.</div>
            <?php
            $types = $ct->getTypes();
            echo "<form action='' method='post'>";
            echo "<div style='float: right'><input type='submit' name='submit' value='Save & Review Marks' style='padding: 12px; font-size: large' /></div>";
            echo "<a href='setTypes.php'><input type='button' value='Edit Test Type' style='padding: 12px; font-size: large' /></a>";
            foreach ($info as $school => $children) {
                if (empty($children)) continue;
                echo "<h2>" . $schools[$school] . "</h2>";
                echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Track Chosen</th>";
                foreach ($types as $type => $value) {
                    echo "<th>" . ucwords($value) . " Score</th>";
                }
                echo "</tr>";
                foreach ($children as $child) {
                    $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                    $name = $child['first'] . ' ' . $child['last'];
                    $id = $child['th_chidon_id'];
                    echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
                    if (empty($child['test_type'])) $default = true;
                    else $default = false;
                    if (! in_array($child['test_type'], array_keys($types))) echo "<td></td>";
                    else {
                        foreach ($types as $type => $value) {
                            if ($child['test_type'] == $type) echo "<td class='type'>" . ucwords($value) . "</td>";
                        }
                    }
                    foreach ($types as $type => $value) {
                        $class = 'score';
                        if ($type == 'expert') $class = 'expert';
                        $score = isset($scores[$school][$id][$testNumber][$type]) ? $scores[$school][$id][$testNumber][$type] : 0;
                        echo "<td><input type='text' name='scores[$id][$testNumber][$type]' value='" . $score . "' size='4' class='$class' ";
                        if ($disabled) echo "readonly ";
                        echo "/></td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
            echo '<input type="hidden" name="test_num" value=' . $testNumber . '>';
            echo "<div style='float: right'><input type='submit' name='submit' value='Save & Review Marks' style='padding: 12px; font-size: large' /></div>";
            echo "</form>";
        } else {
            ?>
            <form action="enterScores.php" method="post">
                Choose Class: <select name="grade">
                    <option value="0">All Classes</option>
                    <?php
                    $sql = "select class_id, class_grade, class_sub from classes where school_id = " . $admin_user['auths']['school'][0];
                    $result = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($result)) {
                        if (intval($row['class_grade']) >= 4) {
                            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                            echo "<option value='" . $row['class_id'] . "'>" . $grade . "</option>";
                        }
                    }
                    ?>
                </select><br /><br />
                <input type="hidden" name="test_num" value="<?=$testNumber?>">
                <input type="submit" name="submit" value="Submit" />
            </form>
            <?php
        }
        ?>
    </body>
    <script>
        $(function() {
            // BCM IA wants to have the page only show when entering a password. not secure but makes her believe it's secure.
            const school_id = <?=$admin_user['auths']['school'][0]?>;
            const showAlert = <?= isset($_POST['submit']) ? 1 : 0?>;
            if (school_id == 176 && showAlert) {
                // password protect
                const password = 'laky';
                let pass = '';
                while (pass != password) {
                    pass = prompt('Please enter password.');
                }
            }
            $('body').show();
            if (showAlert) alert('Please make sure to SAVE after entering scores.');
        })
        $(".score").focus( function() {
            let val = $(this).val()
            if (parseInt(val) == 0) {
                $(this).val('')
            }
        })
        $(".score").keyup( function() {
            const max = 10;
            let val = $(this).val();
            if (parseInt(val) > max) {
                alert('Please be sure that you are entering the number of questions scored correctly, and NOT the test mark. It should not be higher than ' + max);
                $(this).val(0);
                $(this).focus();
            }
        });
        $(".expert").focus( function() {
            let val = $(this).val()
            if (parseInt(val) == 0) {
                $(this).val('')
            }
        })
        $(".expert").keyup( function() {
            const max = 20;
            let val = $(this).val();
            if (parseInt(val) > max) {
                alert('Please be sure that you are entering the number of questions scored correctly, and NOT the test mark. It should not be higher than ' + max);
                $(this).val(0);
                $(this).focus();
            }
        });
    </script>
</html>
