<?php
ini_set('display_errors',1);
require_once 'db.php';
require_once 'yearly_prize/classes/TotalWeeklyTasks.php';

// get all registered users
$users = array();
$sql = "select u.user_id, u.first, u.last, s.school_name, c.class_grade, c.class_sub
        from users u
        join schools s using (school_id)
        join classes c on c.class_id = u.class_id
        where u.user_registered > 0
        group by school_name, class_grade, class_sub, last, first
        limit 0, 50";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row;
}

foreach ($users as $user_id => $info) {
    $t = new TotalWeeklyTasks($user_id, unixtojd());
    $t->get_week_dates();
    $total = $t->total_weeks_with_task();
    $users[$user_id]['total'] = $total;
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