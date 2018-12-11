<?php
if (!$_COOKIE['admin_id']) { 
	$page = '/home.php';
	header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $page);
}

function get_camp_id() {
	//include ("db.php");
	$sql = "SELECT aa.id, aa.auth, r.role_auth FROM admin_auths AS aa JOIN roles AS r USING (role_id) WHERE admin_id=" . $_COOKIE['admin_id'];
	$query = mysql_query($sql);
	$role = mysql_fetch_assoc($query);
	$camp_id = $role['id'];
			
	return $camp_id;
}
?>
