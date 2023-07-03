<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$disabled = false;
if ($admin_user['auth'] != 'super') {
    $today = new DateTime();
    $shutdown = new DateTime('2022-12-28 05:00:00');
    if ($today >= $shutdown) {
        $disabled = true;
    }
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();
$types = $ct->getTypes();

$info = [];
$marks = [];

if (isset($_POST['submit'])) {
    $numTests = $_POST['test'];
    foreach ($schools as $id => $school) {
        $ct->setStudents($id);
        $info[$id] = $ct->getStudents();
        $ct->setScores();
        $ct->calculateMarks();
        $marks[$id] = $ct->getMarks();
    }
}

if (isset($_POST['save'])) {
    $ct->setTestTypes($_POST['type']);
    $ct->setRewardTypes($_POST['reward_type']);
    header("Location: enterScores.php");
    exit;
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
if (isset($_POST['submit'])) {
    switch ($numTests) {
        case 1:
            $message = "Averages and highest passing track is based off <b>Test 1</b>.";
            break;
        case 2:
            $message = "Averages and highest passing track is based off of a combined mark on <b>Tests 1 & 2</b>.";
            break;
        case 3:
            $message = "Averages and highest passing track is based off of a combined mark on <b>All 3 Tests</b>.";
            break;
//                case 4:
//                    $message = "Averages and highest passing track is based off of a combined mark on <b>all 4 tests</b>.";
//                    break;
    }
    $message .= "<br />To see for other tests, go back to the previous screen.";
    echo "<p>$message</p>";
    echo "<form action='' method='post'>";
    echo "<div style='float: right'><input type='submit' name='save' value='Save & go to Test Scoring' style='padding: 12px; font-size: large' /></div>";
    echo "<div style='clear: both'></div>";
    $ct = new ChidonTests();
    foreach ($info as $school => $children) {
        if (empty($children)) continue;
        echo "<h2>" . $schools[$school] . "</h2>";
        echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Track Chosen</th><th>Avg Mark</th>
                    <th>Highest Track Passed</th><th>Avg Mark</th><th>Reward Type for Child</th></tr>";
        foreach ($children as $child) {
            $markInfo = $ct->getHighestTrackPassed($child, $numTests);
            $highestTrack = $markInfo['highest_track'];
            $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
            $name = $child['first'] . ' ' . $child['last'];
            echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td><td class='type'>";
            echo $child['test_type'];
            echo "</td><td>" . number_format($markInfo['avg'], 2) . "</td><td>";
            if (!empty($highestTrack)) echo $types[$highestTrack];
            echo "</td><td>" . number_format($markInfo['highest_track_avg'],2) . "</td><td>";
            if ($markInfo['reward_type'] == 'highest track passed') echo 'highest track passed';
            else if ($child['reward_type']) echo $child['reward_type'];
            echo "</td></tr>";
        }
        echo "</table>";
    }
    echo "<div style='float: right'><input type='submit' name='submit' value='Save & go to Test Scoring' style='padding: 12px; font-size: large' /></div>";
    echo "</form>";
} else {
    echo "<form action='' method='post'>";
    echo "<p>Please select based on which tests you would like to see track averages & highest passing track</p>";
    echo "Choose Test: <select name='test'>";
    echo "<option value='1'>Test 1</option>";
    echo "<option value='2'>Combine tests 1 & 2</option>";
    echo "<option value='3'>Combine tests 1 - 3</option>";
    echo "</select><br /><br />";
    echo "<input type='submit' name='submit' value='Generate Report' />";
    echo "</form>";
}
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
    const show = <?= isset($_POST['submit']) ? 1 : 0 ?>;
    if (show) alert('Please make sure to SAVE after making changes.');
  })
</script>
</html>
