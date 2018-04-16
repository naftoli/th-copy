<?php
require 'db.php';
require 'class.parshos.php';
$parshos = Parshos::getParshos(5778);

function update($start, $end, $user_id) {
    $sql = "SELECT * FROM user_yearly_gift WHERE user_id = " . $user_id . " AND start_date = $start AND end_date = $end"; // check if there is a mark for this user on this week
    $query = mysql_query($sql);
    if (mysql_num_rows($query) > 0 ) { // if we have an entry in the table
        $row = mysql_fetch_assoc($query);
        if ($row['marked'] == 1) return; // if it is set to one, then return true otherwise keep checking the marks
    }
    // check if there is any marks during the week period
    $sql = "select dtmarks.date_task_id, count(*) as 'total', dtmarks.done_qty, dt.needed, dt.quantity from user_tracks ut "
        ."join date_tasks_missions dtm on ut.level = dtm.level and ut.track_id = dtm.track_id and ut.subject_id = dtm.subject_id "
        ."join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id) "
        ."where dtmarks.user_id = ".$user_id." and ut.user_id = ".$user_id." "
        ."and dtmarks.mark_date >= $start and dtmarks.mark_date <= $end "
        ."group by dtmarks.date_task_id";
        
    $query = mysql_query($sql);
    while ($row = mysql_fetch_assoc($query)){
        if ($row['total'] >= 1 && // if the amount of rows is equal to what is needed (covers daily tasks)
            ($row['quantity'] ? $row['done_qty'] >= $row['quantity'] : true)){ // make sure that the quanity is good (covers non daily tasks)
            $sql = "INSERT INTO user_yearly_gift (user_id, start_date, end_date, marked) VALUES ('".$user_id."', '$start', '$end', 1)";
            mysql_query($sql);
        }
    }
}

$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

foreach ($parshos as $parsha) {
    foreach ($users as $user_id) {
        echo "updating $user_id from $start to $end<br />";
        update($parsha['start'], $parsha['end'], $user_id);
        sleep(0.5);
    }
    echo "Sleeping<br /><br />";
    sleep(1);
}