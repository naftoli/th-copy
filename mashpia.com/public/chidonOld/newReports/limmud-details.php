<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

if (!isset($_GET['id']) || !isset($_GET['test'])) {
    header('Location: limmud-report.php');
    exit;
}

function getHeDay($day) {
    $dateArr = explode('/', $day);
    $str = jdtojewish(gregoriantojd($dateArr[0], $dateArr[1], $dateArr[2]), true, CAL_JEWISH_ADD_GERESHAYIM); // for today
    $str1 = iconv ('WINDOWS-1255', 'UTF-8', $str); // convert to utf-8
    return $str1;
}

$user_id = $_GET['id'];
$test_num = $_GET['test'];

require_once 'codeForReport.php';

$year = GlobalSettings::getChidonYear();
$chidon = new ChidonTests();
$types = $chidon->getTypes();
$info = $chidon->getLimmudInfo($user_id);
$details = $chidon->getLimmudDetails($user_id, $learningDays[$test_num]);

$types = $chidon->getTypes();

// add to info variable
$info['grade'] = $info['class_grade'] . (empty($info['class_sub']) ? '' : '-' . $info['class_sub']);
$passed = $chidon->getHighestTrackPassed($info, $test_num)['highest_track'];
$info['track_passed'] = $passed ? $types[$passed] : '';
$info['required'] = daysPassed() * $minutes[$info['test_type']];
$info['learned'] = $chidon->getTotalMinutesLearned($user_id, $learningDays[$test_num]);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Limmud Detailed Report</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
          tr, th, td {
            font-size: 14px;
            padding: 6px;
          }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Limmud Detailed Report</h1>
        <p>
            Test #: <?= $test_num ?><br />
            Serial: <?= $info['user_serial'] ?><br />
            Name: <?= $info['first'] . ' ' . $info['last'] ?><br />
            Class: <?= $info['grade'] ?><br />
            Track Chosen: <?= $types[$info['test_type']] ?><br />
            Track Passed: <?= $info['track_passed'] ?><br />
            Daily Minutes Required: <?= $minutes[$info['test_type']] ?><br />
            Total Minutes Required: <?= $info['required'] ?><br />
            Total Minutes Learned: <?= $info['learned'] ?><br />
        </p>
        <table>
            <tr>
                <th>Learning Days Passed</th>
                <th>Date</th>
                <th>Minutes Logged</th>
                <th>Balance</th>
                <th>Up to Date</th>
            </tr>
            <?php
            $balance = 0;
            $required = $minutes[$info['test_type']];
            foreach ($details as $day => $row) {
                $min = intval($row['minutes']);
                $balance += $min - $required;
                $heDay = getHeDay($learningDays[$test_num][$day]);
                echo "<tr><td>" . $day . "</td><td>" . $heDay . "</td><td>" . $min . "</td><td>" . $balance .
                    "</td><td><td><input type='checkbox'";
                if ($row['upToDate']) echo " checked";
                echo " disabled /></td></tr>";
            }
            ?>
        </table>
    </body>
</html>