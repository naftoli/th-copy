<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$users = $_POST['users'];
$ladder = $_POST['ladder'];

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');
foreach ($users as $user_id) {
    $sql = "update user_tracks set track_id = " . $ladder . " where user_id = " . $user_id . " and subject_id = 27";
    if (!mysql_query($sql)) {
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query('commit');
    mysql_query('set autocommit=1');
} else {
    mysql_query('rollback');
    mysql_query('set autocommit=1');
}
echo $success;