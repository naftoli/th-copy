<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$ladder = $_POST['ladder'];

$success = true;
if (isset($_POST['user'])) {
    $user_id = $_POST['users'];
    $sql = "update user_tracks set track_id = " . $ladder . " where user_id = " . $user_id . " and subject_id = 27";
    if (!mysql_query($sql)) {
        $success = false;
    }
} else {
    $users = [];
    $grade = $_POST['grade'];
    $class_id = $_POST['id'];
    if ($grade) {
        $sql = "select user_id from users u 
                join classes c using (class_id) 
                where c.class_grade = '$grade'";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $users[] = $row['user_id'];
        }
    } else if ($class_id) {
        $sql = "select user_id from users where class_id = " . $class_id;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $users[] = $row['user_id'];
        }
    }

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
}
echo $success;