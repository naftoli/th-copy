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
        where tc.year = " . $year . "
        order by s.school_name, c.class_grade, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$marks = array();
foreach ($info as $row) {
    if (intval($row['shabbaton'])) {
        $type = 'contestant';
        if (intval($row['contestant'])) $type = 'school rep';
        $marks[$row['school_id']][$row['class_grade']][$type][] = $row;
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
                <th>Type</th>
            </tr>
            <?php
            foreach ($marks as $school => $other) {
                foreach ($other as $grade => $more) {
                    foreach ($more as $type => $other) {
                        foreach ($other as $user) {
                            echo "<tr><td>" . $user['school_name'] . "</td><td>" . $grade . "</td><td>" .
                                $user['first'] . ' ' . $user['last'] . "</td><td>" . $type . "</td></tr>";
                        }
                    }
                }
            }
            ?>
        </table>
    </body>
</html>