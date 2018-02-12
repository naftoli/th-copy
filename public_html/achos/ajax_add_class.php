<?
include("db.php");

$sql = "INSERT INTO classes ";
$sql = $sql . "SET school_id=" . $_GET['school_id'] . ", ";
$sql = $sql . "class_grade='" . $_GET['class_grade'] . "', ";
$sql = $sql . "class_sub='" . $_GET['class_sub'] . "', ";
$sql = $sql . "class_teacher='" . $_GET['class_teacher'] . "', ";
$sql = $sql . "default_level=" . $_GET['default_level'];
$query = mysql_query($sql);
if ($query)
	echo mysql_insert_id();
else
	echo "0";
?>