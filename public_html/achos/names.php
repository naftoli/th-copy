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
<h1>Child List</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School</th>
<th>Grade</th>
<th>Last Name</th>
<th>First Name</th>
</tr>
<?
//get list of schools
include_once('db.php');
$sql = "select u.first, u.last, c.class_grade, c.class_sub, s.school_name  
	from users as u, classes as c, schools as s 
	where u.school_id = s.school_id 
	and u.class_id = c.class_id   
	and u.user_registered > 0 
	order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
//$sql = "select school_id, school_name from schools order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['class_grade'] . "-" . $row['class_sub'] . "</td><td>" . $row['last'] . "</td><td>" 
	. $row['first'] . "</td></tr>";
}
?>
</table>
<? echo "<br />Total: " . mysql_num_rows($result); ?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
