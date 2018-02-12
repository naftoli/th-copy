<?php
$classID = $_POST['class_id'];
require_once '../db.php';
$sql = "select email, cell from classes where class_id = " . $classID;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo $row['email'] . ":" . $row['cell'];
?>
