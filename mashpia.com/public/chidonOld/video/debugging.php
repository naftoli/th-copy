<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require 'functions.php';

$users = [7763230, 7754010, 7757183, 7756107, 7772704];
foreach ($users as $user) {
    $sql = "select user_id, highest_track from users u 
            join th_chidon_info using (user_id) 
            where user_serial = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $award = getAward($row);
    echo "Award for " . $user . ": " . $award . "<br />";
}