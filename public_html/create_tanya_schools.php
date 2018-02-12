<?
require 'db.php';

$schools = array();
$sql = "select school_id, school_name from schools where tanya = 1 and school_id > 334";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['school_id']] = $row['school_name'];
}

$i = 1;
$qrys = array();
$passwords = array();
foreach ($schools as $id => $name) {
	do {
		$pass = rand(1111, 9999);
	} while (in_array($pass, $passwords));
	$passwords[] = $pass;
	
	$sql = "insert into admins set username = '" . $i++ . "770', password = '" . $pass . "'";
	$result = mysql_query($sql) or die(mysql_error());
	$admin_id = mysql_insert_id();
	
	if ($admin_id) {
		$sql = "insert into admin_auths set admin_id = " . $admin_id . ", auth='school', id = " . $id . ", role_id = 16";
		mysql_query($sql);
	}
}
?>