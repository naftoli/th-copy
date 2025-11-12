<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$schools = [];
$sql = "select school_id from schools where chayolei = 1";
$result = $MASHPIA_DB->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $schools[] = $row['school_id'];
}

$users = [];
foreach ($schools as $school_id) {
    $sql = "select * from users 
            where lang_id = 1 and school_id = " . $school_id;
    $result = $MASHPIA_DB->query($sql);
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $users[$row['school_id']][] = $row['user_id'];
    }
}

$qrys = [];
foreach ($users as $school_id => $users) {
    foreach ($users as $user_id) {
        $sql = "select level from user_tracks where user_id = " . $user_id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $level = $row['level'];
        $qrys[] = "insert ignore into user_tracks set user_id = " . $user_id . ", subject_id = 136, level = " . $level . ", track_id = 1, enrolled = 1";
    }
}

foreach ($qrys as $qry) {
    $MASHPIA_DB->query($qry);
}

echo "Done";