<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$cur_year = GlobalSettings::getChidonRegYear();
$start_yr = 5777;

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

$users = [];
$grades = [];
$schoolGrades = [];
$sql = "
    SELECT 
        u.user_id, 
        u.user_serial,
        u.first,
        u.last,
        u.school_id,
        c.class_id, 
        c.class_grade,
        c.class_sub
    FROM
        users u
            JOIN
        classes c ON c.class_id = u.class_id
            JOIN
        th_chidon tc USING (user_id)
    WHERE
        u.school_id in (" . implode(',', array_keys($schools)) . ") 
            AND tc.reg_date > 0
";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['school_id']][$row['class_id']][$row['user_id']] = $row;
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $grades[$row['class_id']] = $grade;
    $schoolGrades[$row['school_id']][] = $row['class_id'];
}

$chidonClassTotals = [];
foreach ($users as $school_id => $more) {
    foreach ($more as $class_id => $other) {
        foreach ($other as $user_id => $user) {
            $sql = "select year from th_chidon where reg_date > 0 and user_id = " . $user_id;
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if (isset($chidonClassTotals[$school_id][$class_id][$row['year']])) $chidonClassTotals[$school_id][$class_id][$row['year']]++;
                else $chidonClassTotals[$school_id][$class_id][$row['year']] = 1;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=""utf8" />
        <title>Chidon History Report</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 10px;
                border: 1px solid lightgrey;
            }
        </style>
    </head>
    <body>
        <h1>Chidon History Report</h1>
        <?php foreach ($schools as $school_id => $name) : ?>
            <h2><a href="reg_history_details.php?id=$school_id&type=school"><?= $name ?></a></h2>
            <table>
                <tr>
                    <th>Grade</th>
                    <?php
                    $totals = []; // initialize totals per year
                    for ($i = $start_yr; $i <= $cur_year; $i++) {
                        echo "<th>" . $i . "</th>";
                        $totals[$i] = 0;
                    }
                    ?>
                </tr>
                <?php
                if (isset($chidonClassTotals[$school_id])) {
                    foreach ($chidonClassTotals[$school_id] as $class_id => $info) {
                        echo "<tr><td><a href='reg_history_details.php?id=" . $class_id . "&type=class'>" . $grades[$class_id] . "</a></td>";
                        for ($i = $start_yr; $i <= $cur_year; $i++) {
                            if (isset($info[$i])) {
                                echo "<td>" . $info[$i] . "</td>";
                                $totals[$i] += $info[$i];
                            } else {
                                echo "<td></td>";
                            }
                        }
                        echo "</tr>";
                    }
                    echo "<tr><th>Totals:</th>";
                    for ($i = $start_yr; $i <= $cur_year; $i++) echo "<th>" . $totals[$i] . "</th>";
                    echo "</tr>";
                }
                ?>
            </table>
        <?php endforeach; ?>
    </body>
</html>
