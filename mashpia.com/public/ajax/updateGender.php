<?php
require '../db.php';

$user = mysql_real_escape_string($_POST['user']);
$val = mysql_real_escape_string($_POST['val']);

$sql = "update users set gender = '" . $val . "' where user_id = " . $user;
if (mysql_query($sql)) {
    echo 0;
} else {
    echo 1;
}