<?
require_once '../db.php';
$teachers = $_POST['teachers'];

foreach ($teachers as $value) {
	$flag = strpos($value, ':');
	$id = substr($value, 0, $flag);
	$name = substr($value, $flag + 1);
	$sql = "update classes set class_teacher = '" . mysql_real_escape_string($name) . "' where class_id = " . mysql_real_escape_string($id);
	if (!mysql_query($sql)) {
		echo 0;
	}
}
echo 1;
?>