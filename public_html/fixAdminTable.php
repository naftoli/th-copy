<?
exit;
require 'db.php';
$admins = array();
$sql = "select * from admins2";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$admins[] = $row;
}

$num = 0;
$updated = 0;
foreach ($admins as $admin) {
	$num++;
	$sql = "update admins set 
			title = '" . $admin['title'] . "', 
			first = '" . $admin['first'] . "', 
			last = '" . $admin['last'] . "', 
			admin_phone_work = '" . $admin['admin_phone_work'] . "', 
			admin_phone_home = '" . $admin['admin_phone_home'] . "', 
			admin_phone_mobile = '" . $admin['admin_phone_mobile'] . "', 
			admin_email = '" . $admin['admin_email'] . "'  
			where admin_id = " . $admin['admin_id'];
	//echo $sql; exit;
	if (mysql_query($sql)) {
		$updated++;
	}
}
echo "Queries: " . $num . "<br />";
echo "Updated: " . $updated;
