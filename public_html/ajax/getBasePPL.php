<?
require_once '../db.php';
$school = mysql_real_escape_string($_POST['school']);
$sql = "select * from mishna_ppl where school_id = $school and class_id is null and user_id is null";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	echo json_encode($row);
} else {
	echo 0;
}
?>