<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

$info = [];
$sql = "SELECT 
            *
        FROM
            mashpiadb.platoon_transitions
        WHERE
            school_id = 66
                AND created_at > '2023-01-30'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$qrys = [];
foreach ($info as $row) {
    $sql = "update users 
            set school_id = {$row['school_id']}, 
            class_id = {$row['class_id']} 
            where user_id = {$row['user_id']}";
    $qrys[] = $sql;
}

foreach ($qrys as $qry) mysql_query($qry) or die(mysql_error() . "<br />" . $qry);
echo "done";