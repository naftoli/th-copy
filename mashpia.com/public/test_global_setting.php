<?php
require 'db.php';

$sql = "select `val` from global_settings where `key` = 'birthday_year'";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$year = intval($row['val']);

echo $year;