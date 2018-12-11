<?php
require 'db.php';
require 'class.heDob.php';

$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

foreach ($users as $user) {
    $dob = new HeDob($user);
    $dob->setHeDob();
}
echo "Done.";