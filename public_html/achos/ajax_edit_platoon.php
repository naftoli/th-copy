<?
include("db.php");

$class_id = $_GET['class_id'];
$class_grade = $_GET['class_grade'];
$class_sub = $_GET['class_sub'];
$class_teacher = $_GET['class_teacher'];
$default_level = $_GET['default_level'];
							
$sql = "UPDATE classes SET class_grade='" . mysql_real_escape_string($class_grade) . "', ";
$sql = $sql . "class_sub='" . mysql_real_escape_string($class_sub) . "', ";
$sql = $sql . "class_teacher='" . mysql_real_escape_string($class_teacher) . "', ";
$sql = $sql . "default_level=" . $default_level . " ";
$sql = $sql . "WHERE class_id=" . $class_id;
$query = mysql_query($sql);
if ($query)
	echo true;
else
	echo false;