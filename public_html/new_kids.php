<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>New Children</title>
<style>
tr, th, td {
	vertical-align: text-top;
	border: 1px solid black;
	padding: 5px;
	font-size: 14px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? //if ($admin->auth == 'super' || $admin->admin_id == 60) : ?>
<? if ($admin->auth == 'super') : ?>

<h1>New Children</h1>


<table>
<tr>
<th>&nbsp;</th>
<th>School</th>
<th>Name</th>
<th>Grade</th>
<th>Sub</th>
</tr>
<?
//get list of new kids
include_once('db.php');
$sql = "SELECT u.user_id, u.first, u.last, u.first_he, u.last_he, s.school_name, c.class_grade, c.class_sub  
from users as u, schools as s, classes as c 
where u.school_id = s.school_id 
and u.class_id = c.class_id 
and u.user_serial >= 7740730  
order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";

$result = mysql_query($sql) or die(mysql_error());
$num = 0;
while ($row = mysql_fetch_assoc($result)) {
	$first = $row['first'];
	$last = $row['last'];
	echo "<tr><td>" . ++$num . "</td><td>$row[school_name]</td><td>$first $last</td><td>$row[class_grade]</td><td>$row[class_sub]</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
