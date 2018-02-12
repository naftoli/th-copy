<?php
ini_set('display_errors', 1);
require 'db.php';
$filename = "deleted/tasks_50605";
$contents = file_get_contents($filename);
$info = json_decode($contents);
echo "<pre>";
//print_r($info);
echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-size: 12px;
                padding: 5px;
                font-family: sans-serif;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>School</th>
                <th>User</th>
                <th>Mission ID</th>
                <th>Task ID</th>
            </tr>
            <?php
            $oldSchool = 0;
            $oldUser = 0;
            $oldMission = 0;
            foreach ($info as $user => $other) {
                $sql = "select s.school_id, s.school_name, u.first, u.last
                        from users u 
                        join schools s using (school_id)
                        where u.user_id = " . $user;
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                $school = $row['school_id'];
                $schoolName = $row['school_name'];
                $userName = $row['first'] . ' ' . $row['last'];
                
                foreach ($other as $mission => $tasks) {                  
                    foreach ($tasks as $task) {
                        // check whether we need to show school / user / mission
                        if ($oldSchool != $school) $schoolChanged = true;
                        else $schoolChanged = false;
                        if ($oldUser != $user) $userChanged = true;
                        else $userChanged = false;
                        if ($oldMission != $mission) $missionChanged = true;
                        else $missionChanged = false;
                        
                        // get task info
                        $sqlTask = "select name from date_tasks where date_task_id = " . $task;
                        $resultTask = mysql_query($sqlTask);
                        $rowTask = mysql_fetch_assoc($resultTask);
                
                        echo "<tr><td>";
                        if ($schoolChanged) {
                            echo $schoolName;
                            $oldSchool = $school;
                        }
                        echo "</td><td>";
                        if ($userChanged) {
                            echo $userName;
                            $oldUser = $user;
                        }
                        echo "</td><td>";
                        if ($missionChanged) {
                            echo $mission;
                            $oldMission = $mission;
                        }
                        echo "</td><td>" . $task .  " (" . $rowTask['name'] . ")</td></tr>";
                    }
                }
            }
            ?>
        </table>
    </body>
</html>