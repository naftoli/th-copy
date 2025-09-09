<?php
$admin_auth = array('school'); 
require('header.php');
if ( $admin_user['auth'] != 'super' && $admin_user['admin_id'] != 175069 ) {
	die('You are not authorized to access this page');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>List of Parents</title>
<style>
  body, table {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
  }
}
</style>
</head>

<body>
<h1>List of Super Users</h1>
<?php        
//get list of super users
include_once('db.php');
$sql = "select * from admins where auth = 'super' order by last";
//echo $sql;
$result = mysql_query($sql) or die(mysql_error());
//echo mysql_num_rows($result);
while ($row = mysql_fetch_assoc($result)) {
	$pass = decryptPassword($row['password'], ENCRYPTION_KEY);
	echo "
	<strong>Super User Account ID: $row[admin_id]</strong><br />
	First: <strong>$row[first]</strong><br />
	Last: <strong>$row[last]</strong><br />
	Username: $row[username]<br />
	Password: $pass<br />
	Email: $row[admin_email]<br />
	Cell: $row[admin_phone_mobile]<br />
	Cell 2: $row[admin_phone_mobile2]<br /><br />";
}
?>
</body>
</html>
