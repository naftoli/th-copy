<?php
require 'db.php';

$users = array();
$sql = "select u.user_id, c.class_grade
        from users u
        join classes c using (class_id) 
        where u.school_id = 87";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $users[$row['user_id']] = $row['class_grade'];
}

foreach ($users as $id => $grade) {
    switch ($grade) {
        case 1:
            $level = 6;
            break;
        case 2:
            $level = 7;
            break;
        case 3:
            $level = 8;
            break;
        case 4:
            $level = 9;
            break;
        case 5:
            $level = 10;
            break;
        case 6:
            $level = 11;
            break;
        case 7:
            $level = 12;
            break;
        case 8:
            $level = 13;
            break;
        default:
            $level = 6;
            break;
    }
    $sql = "update user_tracks set level = " . $level . " where user_id = " . $id;
    mysql_query( $sql );
}
echo "Done.";