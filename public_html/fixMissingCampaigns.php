<?php
require_once 'db.php';

function fixGrade( $class_id ) {
    $info = array();
    $sql = "select user_id from users where class_id = " . $class_id;
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $info[] = $row['user_id'];
    }
    
    foreach ($info as $user_id) {
        $sql = "select level from user_tracks where subject_id = 4 and user_id = " . $user_id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $level = $row['level'];
        
        $sql = "insert ignore into user_tracks
                set user_id = " . $user_id . ",
                subject_id = 12,
                track_id = 1,
                enrolled = 1,
                level = " . $level;
        mysql_query( $sql );
        
        if ($class_id == 5800) {
            $sql = "insert ignore into user_tracks
                    set user_id = " . $user_id . ",
                    subject_id = 40,
                    track_id = 1,
                    enrolled = 1,
                    level = " . $level;
            mysql_query( $sql );
        }
    }
}

$grades = array(5800,5801);
foreach ($grades as $class_id) {
    fixGrade( $class_id );
}
echo "done.";