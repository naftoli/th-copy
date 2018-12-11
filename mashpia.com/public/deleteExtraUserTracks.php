<?php
require_once 'db.php';

$users = array();
$sql = "select user_id from users";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$campaigns = array(1,4,12,13,16,21,27,40,41,42,45,90,92,93,94,100);
foreach ($users as $user_id) {
    $sql = "delete from user_tracks where user_id = " . $user_id . " and subject_id not in (" . implode(',', $campaigns) . ")";
    //echo $sql . "<br />";
    mysql_query($sql);
}