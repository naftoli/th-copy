<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Principal List</title>
<style type='text/css'>
th, td {
	border: 1px dashed black;
	padding: 5px;
	font-size: 12px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Principal List</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School</th>
<th>Principal</th>
<th>Email</th>
<th>Phone</th>
</tr>
<?
//get list of schools
include_once('db.php');
$sql = "SELECT a.title, a.first, a.last, a.admin_email, a.admin_phone_work, a.admin_phone_home, a.admin_phone_mobile, s.school_name, aa.role_id
		FROM `admins` a
		JOIN admin_auths aa
		USING ( admin_id )
		JOIN schools s ON ( s.school_id = aa.id )
		WHERE aa.auth = 'school'
		ORDER BY s.school_name, a.last, a.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$name = $row['title'] . " " . $row['first'] . " " . $row['last'];
	echo "<tr><td>" . $row['school_name'] . "</td><td>" . $name . "</td><td>" . $row['admin_email'] . 
	"</td><td>Work: " . $row['admin_phone_work'] . "<br />Cell: " . $row['admin_phone_mobile'] . "<br />Home: " . 
	$row['admin_phone_home'] . "</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
