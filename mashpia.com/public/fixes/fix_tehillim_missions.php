<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/mission_marks_updater.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/medal_updater.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/rank_updater.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$mm = new mission_marks_updater();
$m = new medal_updater();
$r = new rank_updater();

$users = [];
$sql = "select user_id from users where user_registered > 0 and school_type_id in (12,13)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

foreach ($users as $user_id) {
    $mm->mission_marks_update_by_subject_id($user_id, 4, 2459027);
    $m->update_medal_two($user_id);
    $r->update_rank_two($user_id);
}
echo "Done.";