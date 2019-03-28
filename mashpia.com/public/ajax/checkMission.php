<?
require_once '../db.php';
$id = mysql_real_escape_string($_POST['id']);
$type = mysql_real_escape_string($_POST['type']);
$field = mysql_real_escape_string($_POST['field']);

if ($id > 0) {
	$sql = "select * from users where {$type}_id = " . $id;
	$result = mysql_query($sql);
	$num1 = mysql_num_rows($result);
	
	$sql = "select * from users where {$field} = 1 and {$type}_id = " . $id;
	$result = mysql_query($sql);
	$num2 = mysql_num_rows($result);
	
	$sql = "select * from users where {$field} = 2 and {$type}_id = " . $id;
	$result = mysql_query($sql);
	$num3 = mysql_num_rows($result);
	
	if ($num1 == $num2) {
		echo 1;
	} else if ($num1 == $num3 ) {
		echo 2;
	} else {
		echo 0;
	}
} else {
	echo 0;
}
?>