<?php
require 'db.php';

// add to school subjects
$schools = array();
$sql = "select school_id from schools where chayolei = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $schools[] = $row['school_id'];
}

foreach ($schools as $id) {
    $sql = "insert into school_subjects set subject_id = 101, school_id = " . $id;
    //mysql_query($sql) or die(mysql_error());
}

// add to user tracks
$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

foreach ($users as $id) {
    // get level
    $sql = "select level from user_tracks where subject_id = 1 and user_id = " . $id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $level = $row['level'];
    } else {
        $level = 6;
    }
    // check if user already setup for this subject
    $sql = "select enrolled from user_tracks where subject_id = 101 and user_id = " . $id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        if (!$row['enrolled']) {
            $sql = "update user_tracks set enrolled = 1 where subject_id = 101 and user_id = " . $id;
        }
    } else {
        $sql = "insert into user_tracks set subject_id = 101, track_id = 1, enrolled = 1, user_id = " . $id . ", level = " . $level;
        mysql_query($sql) or die(mysql_error());
    }
}
echo "Done.";