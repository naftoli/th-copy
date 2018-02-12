<?
require '../db.php';
$admin_id = intval($_POST['id']);
$shipping = $_POST['ship'] == 'true' ? 1 : 0;

if ($admin_id > 0) {
	$sql = "update admins set no_shipping = " . $shipping . " where admin_id = " . $admin_id;
	//echo $sql;
	if (mysql_query($sql))
		echo 1;
	else 
		echo 0;
}
?>