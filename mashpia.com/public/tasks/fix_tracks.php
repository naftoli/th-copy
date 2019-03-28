<?php
ini_set('display_errors',1);
require '../db.php';

$users = array();
$sql = "select u.user_id, u.dob from users u 
        join user_tracks ut using (user_id) 
        where u.user_registered > 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[$row['user_id']] = $row['dob'];
}

foreach ( $users as $user => $dob ) {
    $d1 = new DateTime();
    $d2 = new DateTime( $dob );
    $age = $d2->diff($d1);
    $level = $age->format('%y');
    if ($level < 6) $level = 6;
    if ($level > 14) $level = 14;
    
    $sql = "update user_tracks 
            set level = " . $level . "
            where user_id = " . $user . " 
            and subject_id != 1";
    mysql_query( $sql );
}
echo "done";