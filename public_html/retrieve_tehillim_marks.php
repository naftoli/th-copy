<?php
require 'db.php';

$users = array();
$sql = "select user_id, school_type_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['school_type_id']][] = $row['user_id'];
}

// sm kislev 5777
$date = 2457719;
foreach ($users as $type => $info) {
    foreach ($info as $user) {
        // find out if there's a mark for this child in tehillim
        $sql = "select * from date_tasks_mission_marks dtmm
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                where dtm.start_date = " . $date . " 
                and dtm.end_date = " . $date . "
                and dtm.subject_id = 1
                and dtm.school_type_id = " . $type . " 
                and dtmm.user_id = " . $user;
        //echo $sql . "<br />";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            // find out year / ladder
            $row = mysql_fetch_assoc($result);
            $level = $row['level'];
            $track = $row['track_id'];
            // update user tracks for tehillim for this child
            $update = "update user_tracks
                        set level = " . $level . ",
                        track_id = " . $track . "
                        where subject_id = 1
                        and user_id = " . $user;
            //echo $update . "<br />";
            mysql_query($update);
        }
    }
}
echo "Done";