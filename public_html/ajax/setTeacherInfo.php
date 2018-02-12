<?php
$classID = $_POST['id'];
$email = $_POST['email'];
$cell = $_POST['cell'];

require_once '../db.php';
$sql = "update classes set email = '$email', cell = '$cell' where class_id = " . $classID;
mysql_query($sql);
echo $sql;
?>
