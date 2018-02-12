<?php
ini_set('display_errors',1);
require 'db.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = array();
$sql = "select * from th_chidon tc 
        join schools s using (school_id)
        join users u using (user_id)
        join classes c on c.class_id = u.class_id 
        where tc.year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$avgs = array();
$marks = array();
foreach ($info as $row) {
    $avg1 = (intval($row['test1a']) + intval($row['test2a'])) / 2;
    $avg2 = (intval($row['test1b']) + intval($row['test2b'])) / 2;
    if ($avg1 >= 70 && $avg2 >= 70) {
        $avg = ($avg1 + $avg2) / 2;
        $marks[$row['school_id']][$row['class_grade']][$avg][] = $row;
    }
}

foreach ($marks as $school => $other) {
    foreach ($other as $grade => $more) {
        arsort($marks[$school][$grade]);
    }
}
//echo "<pre>"; print_r($marks); echo "</pre>"; exit;
$contestants = array();
foreach ($marks as $school => $other) {
    foreach ($other as $grade => $results) {
        $i = 0;
        foreach ($results as $avg => $users) {
            if (++$i > 2) break;
            foreach ($users as $user) {
                $contestants[$school][$grade][$avg][] = $user;
            }        
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>Student</th>
                <th>Average</th>
            </tr>
            <?php
            foreach ($contestants as $school => $other) {
                foreach ($other as $grade => $more) {
                    foreach ($more as $avg => $other) {
                        foreach ($other as $user) {
                            echo "<tr><td>" . $user['school_name'] . "</td><td>" . $grade . "</td><td>" .
                                $user['first'] . ' ' . $user['last'] . "</td><td>" . $avg . "</td></tr>";
                        }
                    }
                }
            }
            ?>
        </table>
    </body>
</html>