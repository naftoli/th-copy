<?php
require '../../../db.php';
$key = $_POST['key'];

$sql = "select `val` from global_settings where `key` = '" . $key . "'";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

echo $row['val'];