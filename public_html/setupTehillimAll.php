<?php
require 'db.php';
$users = array();
$sql = "select * from users u
        join classes c on c.class_id = u.class_id 
        where u.user_registered > 0";
$result = mysql_query($sql);
while ($ow = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row;
}

foreach ($users as $user_id => $info) {
    $sql = "select * from user_tracks where subject_id = 1 and user_id = " . $user_id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) == 0) {
        // create tehillim track
        switch ($info['class_grade']) {
            case '1':
                $level = 7;
                break;
            case '2':
                $level = 8;
                break;
            case '3':
                $level = 9;
                break;
            case '4':
                $level = 10;
                break;
            case '5':
                $level = 11;
                break;
            case '6':
                $level = 12;
                break;
            case '7':
                $level = 13;
                break;
            case '8':
                $level = 14;
                break;
            default:
                $level = 6;
                break;
        }
        switch ($info['school_type_id']) {
            case '2':
            case '3':
                $track = 5;
                break;
            case '12':
            case '13':
                $track = 3;
                break;
            default:
                $track = 5;
                break;
        }
        $sql = "insert into user_tracks
                set user_id = " . $user_id . ",
                subject_id = 1,
                level = " . $level . ",
                track_id = " . $track . ",
                enrolled = 1";
        mysql_query($sql);
    }
    $sql = "update user_tracks set level = 6 where level < 6 and subject_id = 1";
    mysql_query($sql);
    $sql = "update user_tracks set level = 14 where level > 14 and subject_id = 1";
    mysql_query($sql);
    $sql = "update user_tracks set track_id = 3 where track_id < 3 and subject_id = 1";
    mysql_query($sql);
    $sql = "update user_tracks set track_id = 7 where track_id > 7 and subject_id = 1";
    mysql_query($sql);
    $sql = "update user_tracks set enrolled = 1 where enrolled = 0 and subject_id = 1";
    mysql_query($sql);
}
echo "Done.";