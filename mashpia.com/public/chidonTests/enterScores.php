<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
if (isset($_POST['yr'])) $ct = new ChidonTests($_POST['yr']);
else $ct = new ChidonTests();

$testNumber = isset($_REQUEST['test_num']) ? $_REQUEST['test_num'] : 1;

// check if there's avgs that were set for the school
// if not, route them to settings page
if ($admin_user['auth'] != 'super') {
    $settingsSet = true;
    $school_id = $admin_user['auths']['school'][0];
    $settings = $ct->getSettings($school_id, 0, 0);
    foreach(['chidon_passing_avgs', 'chidon_final_passing_avgs', 'chidon_test_levels'] as $table) {
        if ($table == 'chidon_test_levels') $details = ['tests', 'finals'];
        else $details = ['maven', 'pro', 'expert', 'genius'];
        foreach ($details as $type) {
            if (empty($settings[$school_id][$table][$type])) {
                $settingsSet = false;
                break;
            }
        }
    }
    if (!$settingsSet) {
        header("Location: settings.php?fromMarks=1");
        exit;
    }
}

if (isset($_POST['scores'])) {
    if (isset($_POST['yr']) && $_POST['yr'] != $year) {} // make sure not to save marks from prev yrs
    else $ct->insertScores($_POST['scores'], $_POST['levels']);
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
        $levels[$id] = $ct->getLevels();
    }
}

//echo "<pre>"; print_r($info); echo "</pre>"; exit;

// initialize all tests to not be disabled
$disabled = false;
$exceptions = [];
// disable marking after certain dates for bc's
if ($admin_user['auth'] != 'super') {
    $today = new DateTime();
    $shutdown = [];
//    $shutdown[1] = new DateTime('2023-12-06 04:59:00');
//    $shutdown[2] = new DateTime('2024-01-12 04:59:00');
//    $shutdown[3] = new DateTime('2024-02-17 04:59:00');
//    $shutdown[3] = new DateTime('2024-03-19 04:59:00');
//    if ($shutdown[$testNumber] && $today >= $shutdown[$testNumber] && !in_array($admin_user['auths']['school'][0], $exceptions)) {
//        $disabled = true;
//    }
}

if (isset($_POST['yr']) && $_POST['yr'] != $year) $disabled = true;
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
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Enter Test Score</h1>
        <?php
        if ($admin_user['auth'] == 'super') {
            $selectedYr = isset($_POST['yr']) ? $_POST['yr'] : $year;
            echo '<form action="enterScores.php" method="post">';
            echo "Change Year: <select name='yr' onchange='this.form.submit()'>";
            for ($i = 5782; $i <= $year; $i++) {
                echo "<option value='$i'";
                if ($i == $selectedYr) echo " selected";
                echo ">$i</option>";
            }
            echo "<input type='hidden' name='test_num' value=" . $testNumber . ">";
            echo "</form>";
        }
        ?>
        <?php if ($admin_user['auth'] == 'super' || isset($_POST['submit'])) { ?>
            <h2>Test #<?= $testNumber ?></h2>
            <div class="infobox">Please enter the <strong>number</strong> of questions scored correctly. The system will calculate the correct mark.</div>
            <?php
            $types = $ct->getTypes();
            echo "<form action='' method='post'>";
            echo "<div style='float: right'><input type='submit' name='submit' value='Save & Review Marks' style='padding: 12px; font-size: large' /></div>";
            echo "<a href='settings.php'><input type='button' value='Marks/Levels Settings' style='padding: 12px; font-size: large' /></a>";
            foreach ($info as $school => $children) {
                if (empty($children)) continue;
                echo "<h2>" . $schools[$school] . "</h2>";
                echo "<p><input type='checkbox' class='report_cards' id='" . $school . "' /> Show Report Cards on Parent Accounts</p>";
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
                        $levelValue = $ct->getLevel($child['user_id'], 'tests');
                        if ($score > 0 && isset($levels[$school][$id][$testNumber][$type]))
                            $levelValue = $levels[$school][$id][$testNumber][$type];
                        echo "<td><input type='text' name='scores[$id][$testNumber][$type]' value='" . $score . "' size='4' class='$class' ";
                        if ($disabled) echo "readonly ";
                        echo "/>";
                        echo "<select name='levels[$id][$testNumber][$type]'>";
                        echo "<option value='1'";
                        if ($levelValue == 1) echo " selected";
                        echo ">1</option>";
                        echo "<option value='2'";
                        if ($levelValue == 2) echo " selected";
                        echo ">2</option>";
                        echo "</select></td>";
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
            const showAlert = <?= isset($_POST['submit']) ? 1 : 0?>;
            if (showAlert) alert('Please make sure to SAVE after entering scores.');
            checkShowReportCards()
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

        async function checkShowReportCards() {
          const res = await fetch('api/getReportCardsInfo.php')
          const info = await res.json()
          if (info.success) {
            const data = info.info
            const ids = Object.keys(data)
            for (let id of ids) {
              if (data[id] && document.getElementById(id)) document.getElementById(id).checked = parseInt(data[id]) ? true : false
            }
          }
        }

        $(".report_cards").click( function() {
          let id = $(this).attr('id')
          let isChecked = $(this).is(":checked")
          fetch('api/setReportCardsInfo.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ school_id: id, value: isChecked })
          })
            .then(res => res.json())
            .then(res => {
              if (res.success) alert('Saved.')
              else alert('Error saving.')
            })
        })
    </script>
</html>
