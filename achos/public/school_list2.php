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
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>School List</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School</th>
<th>Admin</th>
<th>username</th>
<th>password</th>
</tr>
<?
//get list of schools
include_once('db.php');
$sql = "SELECT s.school_name, a.title, a.first, a.last, a.username, a.password
FROM schools AS s, admin_auths AS aa, admins AS a
WHERE aa.id = s.school_id
AND aa.auth =  'school'
AND a.admin_id = aa.admin_id
ORDER BY s.school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['title'] . " " . $row['first'] . " " . $row['last'] . "</td><td>" . $row['username'] . "</td><td>" . $row['password'] . "</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
