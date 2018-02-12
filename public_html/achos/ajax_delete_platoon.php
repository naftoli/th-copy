<?
include("db.php");

$class_id = $_GET['class_id'];

$sql = "DELETE FROM classes WHERE class_id=" . $class_id;
$query = mysql_query($sql);

if ($query)
{
	$sql = "DELETE FROM admin_auths WHERE auth='class' AND id=" . $class_id;
	$query = mysql_query($sql);
	echo "1";
}
else
{
	echo "0";
}
?>
