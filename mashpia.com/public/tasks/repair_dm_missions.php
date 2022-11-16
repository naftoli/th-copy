<?php
$admin_auth = ['school'];
require '../header.php';
require '../classes/medal_updater.php';
require '../classes/rank_updater.php';

$info = [];
$sql = "SELECT * FROM date_tasks_mission_marks where user_id in (
select user_id from users where school_id = 33) and mark_date > 2459824";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $date_task_mission_id = $row['date_tasks_mission_id'];
    $user_id = $row['user_id'];
    $mark_date = $row['mark_date'];
    $info[$user_id][$date_task_mission_id] = $mark_date;
}

$info2 = [];
$sql2 = "SELECT * FROM mashpia_backup.date_tasks_mission_marks where user_id in (
select user_id from users where school_id = 33) and mark_date > 2459824";
$result = mysql_query($sql2);
while ($row = mysql_fetch_assoc($result)) {
    $date_task_mission_id = $row['date_tasks_mission_id'];
    $user_id = $row['user_id'];
    $mark_date = $row['mark_date'];
    $info2[$user_id][$date_task_mission_id] = $mark_date;
}

$diff = [];
foreach ($info as $user => $missions) {
    foreach ($missions as $mission_id => $date) {
        if (! isset($info2[$user][$mission_id])) {
            $diff[$user][$mission_id] = $date;
        }
    }
}

$qrys = [];
$users = [];
foreach ($diff as $user => $missions) {
    foreach ($missions as $id => $date) {
        $qrys[] = "delete from date_tasks_mission_marks where user_id = $user and date_tasks_mission_id = $id and mark_date = $date";
        $users[] = $user_id;
    }
}

foreach ($qrys as $qry) {
    mysql_query($qry);
}
echo "done";

$medal_updater = new medal_updater();
$rank_updater = new rank_updater();
foreach ($users as $user_id) {
    $medal = $medal_updater->update_medal_two($user_id);
    $rank = $rank_updater->update_rank_two($user_id);
}
echo "updated.";