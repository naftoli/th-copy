<?php
require '../db.php';

$user_id = mysql_real_escape_string($_POST['user']);
$admin_id = $_POST['admin'] ? mysql_real_escape_string($_POST['admin']) : 0;

if ($admin_id) {
    $add = false;
    $sql = "select * from admin_auths
            where auth = 'user'
            and id = " . $user_id;
    $result = mysql_query($sql);
    if (!$result) {
        echo 0;
        exit;
    }
    if (mysql_num_rows($result) > 0) {
        $sql = "update admin_auths
                set admin_id = " . $admin_id . " 
                where id = " . $user_id . "
                and auth = 'user'";
    //echo $sql;
    } else {
        $add = true;
        $sql = "insert into admin_auths 
                set admin_id = " . $admin_id . ", 
                id = " . $user_id . ", 
                auth = 'user', 
                role_id = 1";
    }
    //echo $sql; exit;
    if (mysql_query($sql)) {
        echo $admin_id;
    } else {
        echo 0;
    }
} else {
    $sql = "delete from admin_auths 
            where id = " . $user_id . "
            and auth = 'user'";
    //echo $sql;
    if (mysql_query($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}