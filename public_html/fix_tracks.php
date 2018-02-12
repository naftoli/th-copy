<?php
require 'db.php';
$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

foreach ($users as $user) {
    $sql = "select level from user_tracks where subject_id = 4 and user_id = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $level = $row['level'];
    
    if ($level && $level >= 6) {
        $sql = "update user_tracks set level = " . $level . " where subject_id = 1 and user_id = " . $user;
        mysql_query($sql);
    }
}