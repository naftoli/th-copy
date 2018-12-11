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
ini_set('max_execution_time', 600);
require 'db.php';

$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$extra = array();
foreach ($users as $user_id) {
    //$user_id = 8273;
    $info = array();
    $sql = "select * from date_tasks_mission_marks dtmm 
            join date_tasks_missions dtm using (date_tasks_mission_id) 
            join date_tasks dt using (date_tasks_mission_id) 
            where user_id = " . $user_id . " 
            order by mark_date desc";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['subject_id']][$row['mission_name']][$row['task_id']][$row['start_date'] . '-' . $row['end_date']][$row['lang_id']][$row['date_task_id']] = $row['date_tasks_mission_id'];
    }
    //echo "<pre>"; print_r($info); echo "</pre>"; exit;
    
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
        continue;
    }
    
    foreach ($info as $subject => $more) {
        foreach ($more as $mission => $other) {
            foreach ($other as $task => $more) {
                foreach ($more as $date => $other) {
                    if (count($other) > 1) {
                        // remove task(s) not associated with correct lang
                        if (count($other[$notLang])) {
                            foreach ($other[$notLang] as $id => $mission) {
                                $extra[$user_id][$mission][] = $id;
                            }
                        }
                        if (count($other[$lang]) > 1) {
                            // remove extra tasks within correct lang
                            $i = 0;
                            foreach ($other[$lang] as $id => $mission) {
                                if ($i++) {
                                    $extra[$user_id][$mission][] = $id;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    $str = json_encode($extra);
    // save to file
    if (!empty($extra)) {
        $file = "deleted/tasks4_" . $user_id;
        $fp - fopen($file, "w");
        if ($fp) {
            fwrite($fp, $str);
            fclose($fp);
        }
    }
}
//echo "<pre>"; print_r($extra); echo "</pre>"; exit;

foreach ($extra as $user_id => $more) {
    $missions = array();
    $tasks = array();
    foreach ($more as $mission => $other) {
        $missions[] = $mission;
        foreach ($other as $task) {
            $tasks[] = $task;
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
                        $success = false;
                        break;
                    }
                }
            } else {
                $success = false;
                break;
            }
        }
    }
    
    if ($success) {
        mysql_query("commit");
        mysql_query("set autocommit=1");
        
        //require_once 'classes/mission_marks_updater.php';
        require_once 'classes/medal_updater.php';
        require_once 'classes/rank_updater.php';
        
        //$mmupdater = new mission_marks_updater();
        //$mmupdater->mission_marks_update($user_id);
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
}
