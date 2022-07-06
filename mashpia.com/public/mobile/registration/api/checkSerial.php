<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$sql = "select * from users where user_serial = " . mysql_real_escape_string($_POST['serial']);
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) echo true;
else echo false;