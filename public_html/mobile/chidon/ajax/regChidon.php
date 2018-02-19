<?php
require '../../../db.php';

$admin_id = mysql_real_escape_string($_POST['admin']);
$year = mysql_real_escape_string($_POST['year']);
$user = mysql_real_escape_string($_POST['user']);
$school = mysql_real_escape_string($_POST['school']);
$size = mysql_real_escape_string($_POST['size']);

require '../../reg/ajax/encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

// check if there's already a child registered (but possibly identified as deleted)
$sql = "select * from th_chidon where user_id = " . intval($user) . " and year = " . intval($year);
$result = mysql_query( $sql );
if (mysql_num_rows( $result ) > 0) {
    $sql = "update th_chidon 
            set school_id = " . intval($school) . ",
            size = '" . $size . "',
            parent_id = " . $admin_id . ", 
            reg_date = now(),
            deleted = 0 
            where user_id = " . intval($user) . "
            and year = " . intval($year);
} else {
    $sql = "insert into th_chidon 
            set user_id = " . intval($user) . ",
            school_id = " . intval($school) . ",
            year = " . intval($year) . ",
            size = '" . $size . "',
            parent_id = " . $admin_id . ", 
            reg_date = now()";
}

if (mysql_query($sql)) {
    echo 0;
} else {
    echo $sql . "<br />";
}
?>