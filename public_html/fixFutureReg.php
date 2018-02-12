<?php
require 'db.php';

$info = array();
$sql = "select * from user_registration ur 
        join users u using (user_id) 
        where year = 5777
        and ur.school_id is null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if ($row['school_id'])
        $info[$row['user_id']] = $row['school_id'];
}

foreach ($info as $user => $school) {
    $sql = "update user_registration
            set school_id = $school
            where user_id = $user
            and year = 5777";
    mysql_query($sql);
}