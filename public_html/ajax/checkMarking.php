<?
$id = $_POST['id'];
$type = $_POST['type'];

if ($id > 0) {
	require_once '../db.php';
	$sql = "select * from users where {$type}_id = " . $id;
	$result = mysql_query($sql);
	$num1 = mysql_num_rows($result);
	
	$sql = "select * from users where parent_marking = 1 and {$type}_id = " . $id;
	$result = mysql_query($sql);
	$num2 = mysql_num_rows($result);
	
	if ($num1 == $num2) {
		echo 1;
	} else {
		echo 0;
	}
} else {
	echo 0;
}
?>