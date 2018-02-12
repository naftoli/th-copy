<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 12px;
                vertical-align: top;
            }
        </style>
    </head>
    <body>

        <?php
        ini_set('display_errors', 1);
        ini_set('max_execution_time', 300);
        require 'db.php';
        
        $users = array();
        $sql = "select user_id from users where user_registered > 0 and school_id = 255";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $users[] = $row['user_id'];
        }
        
        $extra = array();
        foreach ($users as $user_id) {
        //$user_id = 19510;
            $info = array();
            $sql = "select * from date_tasks_mission_marks dtmm 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join date_tasks dt using (date_tasks_mission_id) 
                    where user_id = " . $user_id . " 
                    order by mark_date desc";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $info[$row['subject_id']][$row['mission_name']][$row['mark_date']][$row['task_id']][] = $row['name'];
            }
            //echo "<pre>"; print_r($info); echo "</pre>"; exit;
            $extra[$user_id] = $info;
        }
        ?>
        <table>
            <caption>Extra Tasks</caption>
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Mission Name</th>
                <th>Mark Date</th>
                <th>Task ID</th>
                <th>Tasks</th>
            </tr>
            <?php
            foreach ($extra as $user_id => $other) {
                foreach ($other as $subject => $more) {
                    foreach ($more as $mission => $other) {
                        foreach ($other as $date => $more) {
                            foreach ($more as $taskID => $tasks) {
                                if (count($tasks) > 1 && $taskID > 0 && $taskID != 99.99) {
                                    echo "<tr><td>" . $user_id . "</td><td>" . $subject . "</td><td>" . $mission . "</td><td>" .
                                        jdtogregorian($date) . "</td><td>" . $taskID . "</td><td>";
                                    foreach ($tasks as $task) {
                                        echo $task . "<br />";
                                    }
                                    echo "</td></tr>";
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
