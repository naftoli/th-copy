<?php
require_once( dirname(__FILE__) . '/../../../db.php' );

$user_id = mysql_real_escape_string($_POST['user_id']);
$sql = "select user_registered from users where user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$registered = $row['user_registered'];
if ($registered) echo 1;
else echo 0;