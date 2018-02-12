<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 12px;
            }
        </style>
    </head>
</html>

<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
require 'db.php';

$users = array();
$sql = "select user_id from users where user_registered > 0";
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
            join users u using (user_id) 
            where user_id = " . $user_id . "
            and u.school_id = 255 
            and mark_date > 2458004 
            order by mark_date desc";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['subject_id']][$row['mission_name']][$row['task_id']][$row['start_date'] . '-' . $row['end_date']][$row['lang_id']][$row['date_task_id']] = $row['date_tasks_mission_id'];
    }
    //if ($user_id == 18967) {
    //    echo "User ID: " . $user_id;
    //    echo "<pre>"; print_r($info); echo "</pre>";
    //}
    
    // find out user's lang id
    $sql = "select lang_id from users where user_id = " . $user_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $lang = $row['lang_id'];
    if ($lang == 1) {
        $notLang = 2;
    } else if ($lang == 2) {
        $notLang = 1;   
    } else {
        //echo $lang . "-lang.<br />";
        continue;
    }
    
    foreach ($info as $subject => $more) {
        foreach ($more as $mission => $other) {
            foreach ($other as $task => $more) {
                foreach ($more as $date => $other) {
                    if (count($other) > 1) {
                        // remove task(s) not associated with correct lang
                        if (count($other[$notLang]) > 1) {
                            foreach ($other[$notLang] as $id => $mission) {
                                $extra[$user_id][$mission][] = $id;
                            }
                        }
                        /*
                        if (count($other[$lang]) > 1) {
                            // remove extra tasks within correct lang - not necessarily a problem 
                            $i = 0;
                            foreach ($other[$lang] as $id => $mission) {
                                if ($i++) {
                                    $extra[$user_id][$mission][] = $id;
                                }
                            }
                        }
                        */
                    }
                }
            }
        }
    }
    
    if ($user_id == 18967) {
        echo "<pre>"; print_r($extra); echo "</pre>";
    }
    
    $str = json_encode($extra);
    // save to file
    if (!empty($extra)) {
        $file = "deleted/tasks5_" . $user_id;
        $fp = fopen($file, "w");
        fwrite($fp, $str);
        fclose($fp);
    }  
}
//echo "<pre>"; print_r($extra); echo "</pre>"; exit;

$numTasks = array();
foreach ($extra as $user => $other) {
    foreach ($other as $mission => $tasks) {
        $num = count($tasks);
        if (isset($numTasks[$user])) $numTasks[$user] += $num;
        else $numTasks[$user] = $num;
    }
}
//echo "<pre>"; print_r($numTasks); echo "</pre>";
$fraud = array_keys($extra);
//echo "<pre>"; print_r($fraud); echo "</pre>";

if (!empty($fraud)) {
    $fraudInfo = array();
    $sql = "select u.user_id, u.first, u.last, s.school_name, c.class_grade, c.class_sub
            from users u
            join schools s using (school_id)
            join classes c on c.class_id = u.class_id
            where u.user_id in (" . implode(',', $fraud) . ")
            order by school_name, class_grade, class_sub, last, first";
    //echo $sql;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $fraudInfo[$row['user_id']] = $row;
    }
    //echo "<pre>"; print_r($fraudInfo); echo "</pre>";
}
?>
<table>
    <tr>
        <th>School</th>
        <th>Class</th>
        <th>Student</th>
        <th>Extra Tasks</th>
    </tr>
<?php
if (!empty($fraudInfo)) {
    foreach ($fraudInfo as $user => $row) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        echo "<tr><td>" . $row['school_name'] . "</td><td>" . $grade . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" .
            $numTasks[$user] . "</td></tr>";
    }
}
echo "</table>";
exit;
//echo "<pre>"; print_r($extra); echo "</pre>";
$missions = array();
$tasks = array();
foreach ($extra as $user => $more) {
    foreach ($more as $mission => $other) {
        $missions[] = $mission;
        foreach ($other as $task) {
            $tasks[] = $task;
        }
    }
}

$deleted = array(
    'tasks' => 0,
    'missions' => 0
);

mysql_query("set autocommit=0");
mysql_query("begin");

$success = true;
foreach ($tasks as $task) {
    $sql = "delete from date_tasks_marks where date_task_id = " . $task . " and user_id = " . $user_id;
    if (mysql_query($sql)) $deleted['tasks']++;
    else {
        echo mysql_error() . "<br />";
        $success = false;
        break;
    }
}

if ($success) {
    // check each mission whether it's still completed or not
    foreach ($missions as $mission) {
        $sql = "SELECT * ";
        $sql .= "FROM date_tasks AS dt ";
        $sql .= "LEFT JOIN date_tasks_marks AS dtm ON (dtm.user_id=" . $user_id . " AND dtm.date_task_id=dt.date_task_id) ";
        $sql .= "WHERE dt.date_tasks_mission_id=" . $mission . " ";
        $sql .= "AND dtm.date_task_id IS NULL ";
        $sql .= "AND dt.mandatory_qty=1 ";
    
        if ($query = mysql_query($sql)) {
            $num_rows = mysql_num_rows($query);
            if ($num_rows > 0) {
                // delete mission mark
                $sql = "delete from date_tasks_mission_marks where date_tasks_mission_id = " . $mission . " and user_id = " . $user_id;
                if (mysql_query($sql)) $deleted['missions']++;
                else {
                    echo mysql_error() . "<br />";
                    $success = false;
                    break;
                }
            }
        } else {
            echo mysql_error() . "<br />";
            $success = false;
            break;
        }
    }
}

if ($success) {
    mysql_query("commit");
    mysql_query("set autocommit=1");

    require 'classes/medal_updater.php';
    require 'classes/rank_updater.php';
    
    $m = new medal_updater();
    $m->update_medal_two($user_id);
    $r = new rank_updater();
    $r->update_rank_two($user_id);
    
    echo "Total Tasks: " . count($tasks) . "<br />";
    echo "Total Missions: " . count($missions) . "<br />";
    echo "Total Tasks Deleted: " . $deleted['tasks'] . "<br />";
    echo "Total Missions Deleted: " . $deleted['missions'] . "<br /><br />";
} else {
    mysql_query("rollback");
    mysql_query("set autocommit=1");
    echo "There were errors." . mysql_error() . "<br />";
}
