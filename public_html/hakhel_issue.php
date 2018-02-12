<?php
require 'db.php';

$info = array();
$sql = "select * from date_tasks_mission_marks dtmm 
        left join date_tasks_missions dtm using (date_tasks_mission_id) 
        left join date_tasks dt using (date_tasks_mission_id) 
        where dtmm.user_id = 636 
        and dtm.subject_id = 15";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
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
        <p>Here's the list of tasks that were achieved by MM Heidingsfeld which are under the "Hakhel" campaign.<br />
        Total number of missions done in Hakhel: 61<br />
        Total number of missions needed for Blue Medal: 45<br />
        Total number of tasks done in Hakhel: <?=count($info)?></p>
        
        <table>
            <tr>
                <thead>
                    <th>Mission Name</th>
                    <th>Task Name</th>
                    <th>Date Completed</th>
                </thead>
                <tbody>
                    <?php
                    foreach ($info as $row) {
                        $date = jdtogregorian($row['mark_date']);
                        $task = $row['name'];
                        $mission = $row['mission_name'];
                        echo "<tr><td>" . $mission . "</td><td>" . $task . "</td><td>" . $date . "</td></tr>";
                    }
                    ?>
                </tbody>
            </tr>
        </table>
    </body>
</html>