<?php
if (!$_COOKIE['admin_id']) { 
	$page = '/home.php';
	header('Location: http://www.mashpia.com/admin.php');
}

function get_camp_id() {
	include ("db.php");
	
	$sql = "SELECT camp_id FROM admins WHERE admin_id=" . $_COOKIE['admin_id'];
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$camp_id = $row['camp_id'];

	if ($camp_id == 0) {
		$sql = "SELECT aa.id, aa.auth, r.role_auth FROM admin_auths AS aa JOIN roles AS r USING (role_id) WHERE admin_id=" . $_COOKIE['admin_id'];
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$camp_id = $row['id'];
	}
	
	return $camp_id;
}
?>