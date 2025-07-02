<?
require '../db.php';

$user = mysql_real_escape_string($_POST['username']);
$pass = mysql_real_escape_string($_POST['password']);
$sql = "select * from admins where username = '" . $user . "'";

$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	if ( password_verify($pass, $row['hashed_pass']) ) {
		echo json_encode($row);
		exit;
	}
} 
echo json_encode(0);
exit;
?>