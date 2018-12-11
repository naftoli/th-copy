<?php
ini_set('display_errors',1);
require 'db.php';
$g = $_GET['g'];
$year = 5777;

$info = array();
$sql = "select * from th_chidon tc
        join schools s using (school_id)
        join users u using (user_id) 
        join classes c on c.class_id = u.class_id 
        where tc.year = " . $year;
if (isset($g)) $sql .= " and u.gender = '" . strtoupper($g) . "'";
$sql .= " order by school_name, class_grade, gender";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $avg1 = (intval($row['test1a']) + intval($row['test2a']) + intval($row['test3a'])) / 3;
    $avg2 = (intval($row['test1b']) + intval($row['test2b']) + intval($row['test3b'])) / 3;
    if ($avg1 >= 70) {
        $name = $row['first'] . ' ' . $row['last'];
        $info[$row['school_name']][$row['class_grade']][$row['gender']][$avg2][$avg1][] = $name;
    }
}
foreach ($info as $school => $more) {
    foreach ($more as $grade => $other) {
        foreach ($other as $gender => $more) {
            krsort($info[$school][$grade][$gender]);
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
                <th>Gender</th>
                <th>Avg Part 1</th>
                <th>Avg Part 2</th>
            </tr>
            <?php
            foreach ($info as $school => $more) {
                foreach ($more as $grade => $other) {
                    foreach ($other as $gender => $more) {
                        foreach ($more as $avg2 => $other) {
                            foreach ($other as $avg1 => $more) {
                                foreach ($more as $name) {
                                    echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . $name . "</td><td>" .
                                        $gender . "</td><td>" . $avg1 . "</td><td>" . $avg2 . "</td></tr>";
                                }
                            }
                        }
                    }
                }
            }
            ?>
        </table>
    </body>
</html>