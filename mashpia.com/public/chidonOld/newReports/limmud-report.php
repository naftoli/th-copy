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

$test_num = isset($_GET['num']) ? $_GET['num'] : 1;

$ct = new ChidonTests();
$types = $ct->getTypes();

// quicker to get all info for all kids in one sql query
$limmud = $ct->getLearned($learningDays[$test_num], true);
$limmudInfo = [];
foreach ($limmud as $row) {
    $limmudInfo[$row['user_id']][] = $row;
}


$info = [];
foreach ($schools as $school_id => $name) {
    $sql = "select u.user_id, u.user_serial, u.first, u.last, c.school_id, c.class_id, c.class_grade, c.class_sub, 
                tc.th_chidon_id, tc.test_type, tc.reward_type 
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
            <?php
            $caption = "Test # " . $test_num;
            if ($test_num == 4) $caption = "Final";
            ?>
            <table>
                <caption><?= $caption ?></caption>
                <tr>
                    <?php foreach ($fields as $desc => $field) echo "<th>" . $desc . "</th>"; ?>
                </tr>
                <?php
                if (isset($info[$school_id])) {
                    foreach ($info[$school_id] as $grade => $rows) {
                        foreach ($rows as $row) {
                            $totalDays = count($limmudInfo[$row['user_id']]);
                            $totalMinutes = 0;
                            array_walk($limmudInfo[$row['user_id']], function($child) use ($totalMinutes) {
                                $totalMinutes += $child['done_qty'];
                            });
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
                                            // if ($test_num == 1) echo '';
                                            // else echo $ct->getHighestTrackPassed($row, $test_num)['highest_track'];
                                            $track_passed = $ct->getHighestTrackPassed($row, $test_num)['highest_track'];
                                            if ($track_passed == '') echo '';
                                            else echo $types[$track_passed];
                                            break;
                                        case 'calc-lpassed':
                                            $days = daysPassed();
                                            echo $days;
                                            break;
                                        case 'calc-dlogged':
                                            echo $totalDays;
                                            break;
                                        case 'calc-mrequired':
                                            $track = $row['test_type'];
                                            $required = intval($days) * $minutes[$track];
                                            echo $required;
                                            break;
                                        case 'calc-mlogged':
                                            echo $totalMinutes;
                                            break;
                                        case 'calc-behind':
                                            if ($required > intval($totalMinutes)) echo $required - intval($totalMinutes);
                                            else echo 0;
                                            break;
                                        case 'calc-ahead':
                                            if (intval($totalMinutes) > $required) echo intval($totalMinutes) - $required;
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