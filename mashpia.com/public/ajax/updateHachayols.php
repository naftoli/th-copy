<?
require '../db.php';
$admin_id = intval($_POST['id']);
$val = intval($_POST['val']);

if ($admin_id > 0) {
	$sql = "update admins set num_hachayols = " . mysql_real_escape_string($val) . " 
			where admin_id = " . mysql_real_escape_string($admin_id);
	if (mysql_query($sql))
		echo 1;
	else 
		echo 0;
}
?>