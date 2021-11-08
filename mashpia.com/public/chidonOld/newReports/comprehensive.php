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
            foreach ($info as $user) {
                $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
                echo "<tr><td>" . $user['th_chidon_id'] . "</td><td>" . $user['user_serial'] . "</td><td>";
                if ($admin_user['auth'] == 'super') echo $user['school_name'] . "</td><td>";
                echo $grade . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>";
                if ($user['th_chidon_id']) {
                    echo $user['reg_date'] . "</td><td>";
                    if ($user['khk_reg']) echo "YES";
                    else echo "NO";
                    echo "</td><td>";
                    if ($user['marks'][$user['th_chidon_id']][1]['maven'] > 0) echo "YES";
                    else echo "NO";
                    echo "</td><td>" . $user['marks'][$user['th_chidon_id']][1]['maven'] . "</td><td>" .
                        $user['marks'][$user['th_chidon_id']][1]['pro'] . "</td><td>" .
                        $user['marks'][$user['th_chidon_id']][1]['expert'] . "</td><td>" .
                        $user['marks'][$user['th_chidon_id']][1]['genius'] . "</td><td>";
                    echo $types[$user['test_type']] . "</td><td>";
                    if (in_array($user['reward_type'], array_keys($types))) echo $types[$user['reward_type']];
                    else echo $types[$user['test_type']];
                    echo "</td><td>";
                    echo $t->getHighestTrackEligible($user['th_chidon_id'], $user['marks'][$user['th_chidon_id']]) . "</td><td>";
                    $t->setLimmudDates(2459469, 2459514);
                    echo $t->getTotalMinutesLearned( $user['user_id'] ) . "</td></tr>";
                }
                else echo "NOT REGISTERED" . "</td><td colspan='10'></td></tr>";
            }
            ?>
        </table>
    </body>
</html>
