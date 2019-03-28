<?php
require '../db.php';

$users = array();
//$sql = "select * from users where user_id in (18638,23069,24427,24428,13210,25611,20090,23374)";
$sql = "select * from users where user_id in (select id from admin_auths where admin_id = 955) and user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $user = $row['user_id'];
    $school = $row['school_id'];
    $grade = $row['class_id'];
    $points = 180;
    $sql = "insert into pointsDB.user_points
            set user_id = " . $user . ",
            institution_id = " . $school . ",
            class_id = " . $grade . ",
            points = " . $points . ",
            created = now(),  
            resource_name = 'admin_users_manual'";
    //mysql_query($sql) or die(mysql_error());
}