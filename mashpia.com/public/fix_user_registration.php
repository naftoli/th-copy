<?php
require 'db.php';

$users = array();
$sql = "select u.user_id, u.school_id, u.user_registered, s.reg_type
        from users u
        join schools s using (school_id)
        where u.user_registered > '2017-07-01'
        and u.user_id not in(
        select user_id from user_registration where year = 5778)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row;
}
echo "<pre>";
//print_r($users);
echo "</pre>";

foreach ($users as $user) {
    $paid = 0;
    if ($user['reg_type'] == 2) $paid = 45;
    else if ($user['reg_type'] == 3) $paid = 50;
    $sql = "insert ignore into user_registration
            set user_id = " . $user['user_id'] . ",
            admin_id = 0,
            year = 5778,
            reg_date = '" . $user['user_registered'] . "',
            paid = " . $paid . ",
            school_id = " . $user['school_id'];
    //echo $sql . "<br />";
    mysql_query($sql) or die( mysql_error() );
}
echo "done.";