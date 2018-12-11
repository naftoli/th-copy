<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>School List</title>
<style type='text/css'>
tr, th, td {
	border: 1px dashed black;
	padding: 6px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>School List</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School ID</th>
<th>School</th>
<th>Phone</th>
<th>Admin</th>
<th>username</th>
<th>password</th>
</tr>
<?
//get list of schools
include_once('db.php');
$sql = "select school_id, school_name, school_phone from schools order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['school_id'] . "</td><td>" . $row['school_name'] . "</td><td>" . $row['school_phone']. "</td>";
	$sql2 = "select admin_id from admin_auths where id = $row[school_id] and auth = 'school'";
	$result2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_row($result2)) {
		$sql3 = "select title, first, last, username, password from admins where admin_id = $row2[0]";
		$result3 = mysql_query($sql3);
		$flag = true;
		while ($row3 = mysql_fetch_assoc($result3)) {
			if ($flag) echo "</tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
			echo "<td>" . $row3['title'] . " " . $row3['first'] . " " . $row3['last'] . "</td><td>" . $row3['username'] . "</td><td>" . $row3['password'] . "</td></tr>";
			$flag = false;
		}
	}
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
