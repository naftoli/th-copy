<?
include("db.php");

$school_id = $_GET['school_id'];
$class_id = $_GET['class_id'];

$class_id = 0;
if (isset($_GET['class_id']))
	$class_id = $_GET['class_id'];


$sql = "SELECT * FROM users WHERE school_id=" . $school_id . " AND class_id=" . $class_id;
$query = mysql_query($sql);
?>
