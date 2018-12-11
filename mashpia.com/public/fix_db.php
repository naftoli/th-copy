<?
require_once 'db.php';
$sql = "select * from admins2";
$result = mysql_query($sql);
$admins = array();
$admin_ids = array();
while ($row = mysql_fetch_assoc($result)) {
	$admins[$row['admin_id']] = $row;
	$admin_ids[] = $row['admin_id'];
}

foreach ($admin_ids as $id) {
	$admin = $admins[$id];
	$sql = "update admins set 
			first = '" . $admin['first'] . "', 
			last = '" . $admin['last'] . "', 
			admin_phone_work = '" . $admin['admin_phone_work'] . "', 
			admin_phone_home = '" . $admin['admin_phone_home'] . "', 
			admin_phone_mobile = '" . $admin['admin_phone_mobile'] . "', 
			admin_email = '" . $admin['admin_email'] . "' 
			where admin_id = " . $id;
	mysql_query($sql) or die($sql . "<br />" . mysql_error());
}
?>