<?php
require 'db.php';

$users = array();
$sql = "select u.user_id, c.class_grade
        from users u 
        join classes c using (class_id) 
        where u.user_registered > 0
        and u.school_id not in (61,269)";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $users[$row['user_id']] = $row['class_grade'];
}
//echo "<pre>"; print_r( $users ); echo "</pre>";

$levels = array(
    1   =>  7,
    2   =>  8,
    3   =>  9,
    4   =>  10,
    5   =>  11,
    6   =>  12,
    7   =>  13,
    8   =>  14
);

foreach ($users as $id => $grade) {
    switch ($grade) {
        case 1:
            $level = 7;
            break;
        case 2:
            $level = 8;
            break;
        case 3:
            $level = 9;
            break;
        case 4:
            $level = 10;
            break;
        case 5:
            $level = 11;
            break;
        case 6:
            $level = 12;
            break;
        case 7:
            $level = 13;
            break;
        case 8:
            $level = 14;
            break;
        default:
            $level = 6;
            break;
    }
    $sql = "update user_tracks set level = " . $level . " where subject_id = 1 and user_id = " . $id;
    //echo $sql . "<br />";
    mysql_query( $sql );
}
echo "done";