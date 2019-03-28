<?php
require '../../../db.php';

$user_id = mysql_real_escape_string($_POST['user']);
$fname = mysql_real_escape_string($_POST['fname']);
$lname = mysql_real_escape_string($_POST['lname']);
$hfname = mysql_real_escape_string($_POST['hfname']);
$hlname = mysql_real_escape_string($_POST['hlname']);

$sql = "update users set
        first = '" . $fname . "',
        last = '" . $lname . "',
        first_he = '" . $hfname . "',
        last_he = '" . $hlname . "'
        where user_id = " . $user_id;
mysql_query($sql);
?>