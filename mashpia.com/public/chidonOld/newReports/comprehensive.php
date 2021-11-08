<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "SELECT 
            u.user_id, u.first, u.last, u.user_serial, s.school_id, s.school_name, c.class_id, c.class_grade, c.class_sub, 
            tc.th_chidon_id, tc.reg_date, tc.khk_reg, tc.reward_type, tc.test_type
        FROM
            users u 
                LEFT JOIN 
            th_chidon tc USING (user_id)
                JOIN
            schools s ON s.school_id = u.school_id
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            year = $year AND u.school_id IN (" . implode(',', array_keys($schools)) . ") 
        ORDER BY s.school_id, c.class_grade, c.class_sub, u.last, u.first";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
foreach ($info as $idx => $user) {
    $t = new ChidonTests();
    $t->setStudents($user['school_id'], $user['class_id'], $user['user_id']);
    $t->setScores();
    $t->calculateMarks();
    $marks = $t->getMarks();
    $info[$idx]['marks'] = $marks;
}

$t = new ChidonTests();
$types = $t->getTypes();
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Comprehensive Chidon Report</title>
        <style>
            body {
                font-size: 14px;
                font-family: Arial, Helvetica, sans-serif;
            }
            tr, th, td {
                padding: 5px;
                text-size: 12px;
                border: 1px solid grey;
            }
        </style>
    </head>
    <body>
        <h1>Comprehensive Chidon Report</h1>
        <table>
            <tr>
                <th>Chidon ID</th>
                <th>Serial Number</th>
                <?php if ($admin_user['auth'] == 'super') : ?>
                <th>School</th>
                <?php endif; ?>
                <th>Grade</th>
                <th>Student</th>
                <th>Registered</th>
                <th>KHK Registered</th>
                <th>Took First Test</th>
                <th>Yesod Mark</th>
                <th>Yediah Mark</th>
                <th>Havanah Mark</th>
                <th>Iyun Mark</th>
                <th>Current Track</th>
                <th>Prize Track</th>
                <th>Highest Track Eligible</th>
                <th>Total Minutes Studied</th>
            </tr>
            <?php
            $totals = [];
            foreach ($info as $user) {
                $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
                echo "<tr><td>" . $user['th_chidon_id'] . "</td><td>" . $user['user_serial'] . "</td><td>";
                if ($admin_user['auth'] == 'super') echo $user['school_name'] . "</td><td>";
                echo $grade . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>";
                if ($user['th_chidon_id']) {
                    if ($totals['reg']) $totals['reg']++;
                    else $totals['reg'] = 1;
                    echo $user['reg_date'] . "</td><td>";

                    if ($user['khk_reg']) {
                        echo "YES";
                        if ($totals['khk']) $totals['khk']++;
                        else $totals['khk'] = 1;
                    }
                    else echo "NO";
                    echo "</td><td>";

                    $mark = parseInt($user['marks'][$user['th_chidon_id']][1]['maven'], 10);
                    if ($mark > 0) echo "YES";
                    else {
                        echo "NO";
                        if ($totals['missedTest']) $totals['missedTest']++;
                        else $totals['missedTest'] = 1;
                    }

                    // keep track of how many kids failed first test
                    if ($mark > 0 && $mark < 70) {
                        if ($totals['failedTest']) $totals['failedTest']++;
                        else $totals['failedTest'] = 1;
                    }

                    echo "</td><td>" . $user['marks'][$user['th_chidon_id']][1]['maven'] . "</td><td>" .
                        $user['marks'][$user['th_chidon_id']][1]['pro'] . "</td><td>" .
                        $user['marks'][$user['th_chidon_id']][1]['expert'] . "</td><td>" .
                        $user['marks'][$user['th_chidon_id']][1]['genius'] . "</td><td>";

                    echo $types[$user['test_type']] . "</td><td>";

                    // keep track of how many kids passed/failed their track
                    if ($user['test_type'] != 'genius') $needed = 90;
                    else $needed = 70;
                    if (parseInt($user['marks'][$user['th_chidon_id']][1][$user['test_type']], 10) < $needed) {
                        if ($totals['track']['failed']) $totals['track']['failed']++;
                        else $totals['track']['failed'] = 1;
                    } else {
                        if ($totals['track']['passed']) $totals['track']['passed']++;
                        else $totals['track']['passed'] = 1;
                    }

                    // keep track of how many children passed each type
                    foreach ($types as $type) {
                        if ($type == 'genius') $needed = 90;
                        else $needed = 70;
                        if (parseInt($user['marks'][$user['th_chidon_id']][1][$type], 10) >= $needed) {
                            if ($totals[$type]) $totals[$type]++;
                            else $totals[$type] = 1;
                        }
                    }

                    //keep track of how many kids did better than their track
                    if ($user['test_type'] != 'genius') {
                        $tracks = array_keys($types);
                        $index = array_search($user['test_type'], $tracks) + 1;
                        $next = $tracks[$index];
                        if ($next == 'genius') $needed = 90;
                        else $needed = 70;
                        if (parseInt($user['marks'][$user['th_chidon_id']][1][$next]) >= $needed) {
                            if ($totals['betterThanTrack']) $totals['betterThanTrack']++;
                            else $totals['betterThanTrack'] = 1;
                        }
                    }

                    if (in_array($user['reward_type'], array_keys($types))) echo $types[$user['reward_type']];
                    else echo $types[$user['test_type']];
                    echo "</td><td>";

                    echo $t->getHighestTrackEligible($user['th_chidon_id'], $user['marks'][$user['th_chidon_id']]) . "</td><td>";

                    $t->setLimmudDates(2459469, 2459514);
                    $timeLearned = $t->getTotalMinutesLearned($user['user_id']);
                    echo $timeLearned . "</td></tr>";

                    $timeNeeded = [
                        'maven' => 480,
                        'pro' => 960,
                        'expert' => 1440,
                        'genius' => 1920
                    ];
                    if ($timeLearned >= $timeNeeded[$user['test_type']]) {
                        if ($totals['time']['passed']) $totals['time']['passed']++;
                        else $totals['time']['passed'] = 1;
                    } else {
                        if ($totals['time']['failed']) $totals['time']['failed']++;
                        else $totals['time']['failed'] = 1;
                    }
                }
                else {
                    if ($totals['unreg']) $totals['unreg']++;
                    else $totals['unreg'] = 1;
                    echo "NOT REGISTERED" . "</td><td colspan='10'></td></tr>";
                }
            }
            ?>
        </table>
        <p></p>
        <table>
            <caption>Totals</caption>
            <tr>
                <th>Registered</th>
                <th>Not Registered</th>
                <th>Registered for KHK</th>
                <th>Missed first Test</th>
                <th>Failed first Test</th>
                <th>Failed Own Track</th>
                <th>Passed Own Track</th>
                <th>Did better than Track</th>
                <th>Passed Yesod</th>
                <th>Passed Yediah</th>
                <th>Passed Havanah</th>
                <th>Passed Iyun</th>
                <th>Children that completed their minutes</th>
                <th>Children that didn't complete their minutes</th>
            </tr>
            <?php
            echo "<tr><td>" . $totals['reg'] . "</td><td>" . $totals['unreg'] . "</td><td>" . $totals['khk'] . "</td><td>" .
                $totals['missedTest'] . "</td><td>" . $totals['failedTest'] . "</td><td>" . $totals['track']['failed'] .
                "</td><td>" . $totals['track']['passed'] . "</td><td>" . $totals['betterThanTrack'] . "</td><td>";
            foreach ($types as $type => $value) {
                echo $totals[$type] . "</td><td>";
            }
            echo $totals['time']['passed'] . "</td><td>" . $totals['time']['failed'] . "</td></tr>";
            ?>
        </table>
    </body>
</html>
