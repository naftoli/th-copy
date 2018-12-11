<?
require_once '../db.php';
$id = mysql_real_escape_string($_POST['id']);
$setting = mysql_real_escape_string($_POST['setting']);
$type = mysql_real_escape_string($_POST['type']);
if (isset($_POST['field'])) {
	$field = $_POST['field'];
} else {
	$field = "parent_marking";
}

if ($id > 0) {
	$sql = "update users set $field = $setting where {$type}_id = " . $id;
	//echo $sql;
	if (mysql_query($sql)) {
		echo 1;
	} else {
		echo 0;
	}
} else {
	echo 0;
}
?>