<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();
$year = GlobalSettings::getChidonYear();

require_once 'codeForReport.php';

$chidon = new ChidonTests();
$types = $chidon->getTypes();
// get learned info
$learnedInfo = [];
$learned = $chidon->getLearned($learningDays[1]);
foreach ($learned as $day) {
    $learnedInfo[$day['user_id']][] = $day['done_qty'];
}

$info = [];
foreach ($schools as $school_id => $name) {
    $sql = "select u.user_id, u.user_serial, u.first, u.last, c.class_grade, c.class_sub, tc.test_type, tc.reward_type 
            from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            join th_chidon tc using (user_id) 
            where tc.year = $year  
            and u.school_id = " . $school_id ." 
            order by c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $info[$school_id][$grade][] = $row; 
    }
}

$test_num = isset($_GET['num']) ? $_GET['num'] : 1;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Limmud Report</title>
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
        <h1>Limmud Report</h1>
        <form action="limmud-report.php" method="get">
          Please choose which test you would like to view:
          <select name="num">
            <?php
            for ($i = 1; $i < 5; $i++) {
              echo "<option value='" . $i . "'";
              if ($test_num == $i) echo " selected";
              if ($i == 4) echo ">Final</option>";
              else echo ">" . $i . "</option>";
            }
            ?>
          </select>
          <button>Update</button>
        </form>
        <?php foreach ($schools as $school_id => $name) : ?>
            <?= "<h2>" . $name . "</h2>"; ?>
            <table>
                <caption>Test #<?= $test_num ?></caption>
                <tr>
                    <?php foreach ($fields as $desc => $field) echo "<th>" . $desc . "</th>"; ?>
                </tr>
                <?php
                if (isset($info[$school_id])) {
                    foreach ($info[$school_id] as $grade => $rows) {
                        foreach ($rows as $row) {
                            $learned = $learnedInfo[$row['user_id']];
                            $totalLearned = count($learned);
                            $minutes = 0;
                            foreach ($learned as $done_qty) $minutes += $done_qty;
                            echo "<tr>";
                            foreach ($fields as $desc => $field) {
                                echo "<td>";
                                if (is_array($field)) {
                                    if ($desc == 'Class') echo getFieldDesc($row, $field, ' - ');
                                    else if ($desc == 'Name') {
                                        echo "<a href='limmud-details.php?id=" . $row['user_id'] . "&test=" . $test_num . "'>" .
                                            getFieldDesc($row, $field) . "</a>";
                                    }
                                    else echo getFieldDesc($row, $field);
                                } else if (strpos($field, 'calc-') !== false) {
                                    switch ($field) {
                                        case 'calc-tpassed':
                                            if ($test_num == 1) echo '';
                                            else echo $chidon->getHighestTrackPassed($row['user_id'], $test_num)['highest_track'];
                                            break;
                                        case 'calc-lpassed':
                                            $days = daysPassed();
                                            echo $days;
                                            break;
                                        case 'calc-dlogged':
//                                            $logged = $chidon->getTotalDaysLearned($row['user_id'], $learningDays[1]);
//                                            $logged = 0;
                                            echo $totalLearned;
                                            break;
                                        case 'calc-mrequired':
                                            $track = $row['test_type'];
                                            $required = intval($days) * $minutes[$track];
                                            echo $required;
                                            break;
                                        case 'calc-mlogged':
//                                            $num = $chidon->getTotalMinutesLearned($row['user_id'], $learningDays[1]);
//                                            $num = 0;
                                            echo $minutes;
                                            break;
                                        case 'calc-behind':
                                            if ($required > intval($minutes)) echo $required - intval($minutes);
                                            else echo 0;
                                            break;
                                        case 'calc-ahead':
                                            if (intval($minutes) > $required) echo intval($minutes) - $required;
                                            else echo 0;
                                            break;
                                    }
                                } else {
                                    if ($desc == 'Track Chosen') echo $types[$row[$field]];
                                    else echo $row[$field];
                                }
                                echo "</td>";
                            }
                            echo "</tr>";
                        }
                    }
                }
                ?>
            </table>
        <?php endforeach; ?>
    </body>
</html>