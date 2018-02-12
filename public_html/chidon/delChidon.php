<?
require '../db.php';
$user = mysql_real_escape_string($_POST['user']);
$sql = "delete from chidon_reg where chidon_reg_id = " . $user;
if (mysql_query($sql)) {
	echo 1;
} else {
	echo 0;
}
?>