<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$user_id = $_POST['user'];
$ladder = $_POST['ladder'];

$success = false;
if ($user_id && $ladder) {
    $sql = "update user_tracks set track_id = " . $ladder . " where user_id = " . $user_id . " and subject_id = 27";
    if (mysql_query($sql)) $success = true;
}
echo $success;