<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);
require 'db.php';

$users = array();
$qrys = array();
$sql = "select * from marks_backup 
        where done_qty = 0 
        and mark_quantity = 0";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
    // find out which subject this is connected to
    $sql2 = "select dtm.subject_id from date_tasks_missions dtm 
            join date_tasks dt using (date_tasks_mission_id)
            where dt.date_task_id = " . $row['date_task_id'];
    $result2 = mysql_query($sql2);
    if ($row2 = mysql_fetch_assoc($result2)) {
        if (isset($users[$row['user_id']])) {
            if (!in_array($row2['subject_id'], $users[$row['user_id']])) $users[$row['user_id']][] = $row2['subject_id'];
        } else {
            $users[$row['user_id']][] = $row2['subject_id'];
        }
    }
    $sql = "insert ignore into date_tasks_marks
            set date_task_id = " . $row['date_task_id'] . ",
            user_id = " . $row['user_id'] . ",
            mark_date = " . $row['mark_date'] . ",
            done_qty = " . $row['done_qty'] . ",
            mark_description = \"" . mysql_real_escape_string($row['mark_description']) . "\",
            mark_points = " . $row['mark_points'] . ",
            mark_quantity = " . $row['mark_quantity'];
    $qrys[] = $sql;
    //mysql_query($sql) or die(mysql_error());
}
echo "<pre>";
print_r($users);
//exit;
?>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
<?php
mysql_query("set autocommit = 0");
mysql_query("begin");
$success = true;
foreach ($qrys as $qry) {
    if (!mysql_query($qry)) {
        echo $qry . "<br />" . mysql_error() . "<br />";
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query("commit");
    echo "Success.<br />";
} else {
    mysql_query("rollback");
    echo "Rolled Back.<br />";
}
mysql_query("set autocommit = 1");

$str = json_encode($users);
$file = "missingMarks.txt";
$fp - fopen($file, "w");
if ($fp) {
    fwrite($fp, $str);
    fclose($fp);
    echo "File Written.";
}
/*
if ($success) {
    require_once('classes/mission_marks_updater.php');
    require_once('classes/medal_updater.php');
    require_once('classes/rank_updater.php');
    
    $mmupdater = new mission_marks_updater();
    $mupdater = new medal_updater();
    $rupdater = new rank_updater();
    
    foreach ($users as $user => $subjects) {
        foreach ($subjects as $subject) {
            $mmupdater->mission_marks_update_by_subject_id($user, $subject, false);
            $mupdater->update_medal_two($user);
            $rupdater->update_rank_two($user);
        }
    }
}
*/
echo "Done.";
?>
    </body>
</html>