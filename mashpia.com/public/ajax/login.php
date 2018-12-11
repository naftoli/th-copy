<?
require '../db.php';

$user = mysql_real_escape_string($_POST['username']);
$pass = mysql_real_escape_string($_POST['password']);
$sql = "select * from admins where username = '" . $user . "' and password = '" . $pass . "'";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	echo json_encode($row);
} else {
	echo json_encode(0);
}
?>