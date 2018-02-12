<?php
require '../db.php';

foreach ($_POST as $k => $v) {
    $_POST[$k] = mysql_real_escape_string($v);
}

$admin_id = $_POST['id'];
$first = $_POST['fname'];
$last = $_POST['lname'];
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$type = $_POST['type'];
$grade = $_POST['grade'];

// get user id
$sql = "select id from admin_auths where admin_id = " . $admin_id . " and role_id = 1 and auth = 'user'";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$user_id = $row['id'];

$sql1 = "update admins set
        first = '" . $first . "',
        last = '" . $last . "',
        username = '" . $username . "',
        password = '" . $password . "',
        admin_email = '" . $email . "',
        admin_phone_mobile = '" . $phone . "'
        where admin_id = " . $admin_id;
$sql2 = "update users set
        first = '" . $first . "',
        last = '" . $last . "',
        email = '" . $email . "',
        user_phone = '" . $phone . "',
        class_id = " . $grade . " 
        where user_id = " . $user_id;
$sql3 = "update user_tracks
        set subject_id = " . $type . "
        where user_id = " . $user_id;
        
mysql_query("set autocommit=0");
mysql_query("begin");
if (mysql_query($sql1) && mysql_query($sql2) && mysql_query($sql3)) {
    mysql_query("commit");
    mysql_query("set autocommit=1");
    echo 0;
} else {
    mysql_query("rollback");
    mysql_query("set autocommit=1");
    echo 'Error updating';
}