<?php 
require_once '../db.php';
$id = $_POST['id'];
$sql = "update schools set school_logo_id = null where school_id = " . $id;
mysql_query($sql);
//echo $sql;
?>
