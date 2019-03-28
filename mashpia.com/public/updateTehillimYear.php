<?php
require 'db.php';

$info = array();
$sql = "select user_id, dob from users u
        join user_tracks ut using (user_id)
        where ut.enrolled = 1
        and u.dob is not null
        and u.dob > 0 
        group by ut.user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

foreach ($info as $user) {
    $d1 = new DateTime();
    $d2 = new DateTime($user['dob']);
    $age = $d2->diff($d1);
    $level = $age->format('%y');
    if ($level < 6) $level = 6;
    if ($level > 14) $level = 14;
    $year = $level;
    
    $sql = "update user_tracks set level = " . $year . " where user_id = " . $user['user_id'];
    //echo $sql . "<br />";
    mysql_query($sql) or die(mysql_error());
}