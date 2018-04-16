<?php
ini_set('display_errors',1);
require_once 'db.php';
require_once 'yearly_prize/classes/TotalWeeklyTasks.php';

// get all registered users
$users = array();
$sql = "select u.user_id, u.first, u.last, s.school_name, c.class_grade, c.class_sub, count(yg.user_id) as total 
        from users u
        join schools s using (school_id)
        join classes c on c.class_id = u.class_id
        join user_yearly_gift yg using (user_id)
        group by user_id 
        order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row;
}
//echo "<pre>"; print_r($users); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Yearly Prize Eligibility Status</title>
        <style>
            body, table {
                font-size: 12px;
            }
            tr, th, td {
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
                <th>Total</th>
                <th>Eligibility</th>
            </tr>
            <?php
            foreach ($users as $info) {
                $name = $info['first'] . ' ' . $info['last'];
                $grade = $info['class_grade'] . (empty($info['class_sub']) ? '' : '-' . $info['class_sub']);
                echo "<tr><td>" . $info['school_name'] . "</td><td>" . $grade . "</td><td>" . $name . "</td><td>" . $info['total'] . "</td><td>";
                if ($info['total'] >= 12) echo "eligible / received";
                else echo 12 - $info['total'] . " weeks left to eligibility";
                echo "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>