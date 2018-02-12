<?php
require 'db.php';

$info = array();
$sql = "select c.class_grade, c.class_sub, u.first, u.last, dtm.mission_name, dtm.end_date, dt.name, dtmm.mark_date, s.school_name 
        from date_tasks_marks dtmm 
        join users u using (user_id)
        join schools s using (school_id)
        join classes c on c.class_id = u.class_id 
        join date_tasks dt using (date_task_id) 
        join date_tasks_missions dtm using (date_tasks_mission_id) 
        where dtmm.mark_date > (dtm.end_date+1)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_name']][] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            th, td {
                padding: 5px;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <?php
        foreach ($info as $school => $other) {
            ?>
            <table>
                <caption><?=$school?></caption>
                <tr>
                    <th>Grade</th>
                    <th>Student Name</th>
                    <th>Mission Name</th>
                    <th>Task Name</th>
                    <th>Task End Date</th>
                    <th>Mark Date</th>
                </tr>
                <?php
                foreach ($other as $row) {
                    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                    echo "<tr><td>" . $grade . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" .
                        $row['mission_name'] . "</td><td>" . $row['name'] . "</td><td>" . jdtogregorian($row['end_date']) .
                        "</td><td>" . jdtogregorian($row['mark_date']) . "</td></tr>";
                }
                ?>
            </table>
        <?php } ?>
    </body>
</html>