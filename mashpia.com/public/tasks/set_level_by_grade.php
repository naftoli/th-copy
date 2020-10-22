<?php
require_once '../db.php';

$users = [];
$sql = "select * from users u 
        join classes c on c.class_id = u.class_id 
        where user_registered > 0 and user_start_date > 2459000"; // June 1, 2020
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[] = $row;
}

$updated = 0;
foreach ( $users as $row ) {
    $level = 6;
    $grade = $row['class_grade'];
    if ( is_numeric( $grade ) ) $level += intval( $grade );
    $sql = "update user_tracks set level = " . $level . " where user_id = " . $row['user_id'];
    if ( mysql_query( $sql ) ) $updated++;
}
echo "Updated: " . $updated;