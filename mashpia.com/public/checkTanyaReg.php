<?php
require 'db.php';

$users = array();
$sql = "select user_id, dob from users";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row['dob'];
}

$sbj = "select * from subjects where subject_type NOT IN ('school_points', 'home_points')";
$sub_res = mysql_query($sbj);
$subjects = array();
while ($subject = mysql_fetch_assoc($sub_res)) {
    $subjects[] = $subject['subject_id'];
}

$subjects = array(27); // tanya bal peh

foreach ($users as $user => $dob) {
    if (empty($dob)) continue;
    $d1 = new DateTime();
    $d2 = new DateTime($dob);
    $age = $d2->diff($d1);
    $level = $age->format('%y');
    if ($level < 6) $level = 6;
    if ($level > 14) $level = 14;
            
    foreach ($subjects as $subject) {
        $track_id = 1;
        if ($subject == 1) {
            $track_id = 5;
        }
        
        $sql = "select * from user_tracks where subject_id = " . $subject . " and user_id = " . $user;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) == 0) {
            $ins = "insert into user_tracks values ($user, $subject, $track_id, $level, 1)";
            mysql_query($ins);
        }
    }
}

echo 'done';