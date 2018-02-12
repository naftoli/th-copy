<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Student List</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Student List</h1>

<?
//get list of schools
include_once('db.php');
$sql = "select school_id, school_name from schools order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo "<p><b>" . $row['school_name'] . "</b></p>";
	?>
	<table>
	<tr>
	<th>Name</th>
	<th>Grade</th>
	</tr>
	<?
	$sql2 = "select u.first, u.last, c.class_grade, c.class_sub 
			from users as u, classes as c 
			where u.school_id = " . $row['school_id'] . "
			and u.school_id = c.school_id 
			order by c.class_grade, c.class_sub, u.last, u.first";
	$result2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_assoc($result2)) {
		echo "<tr><td>" . $row2['last'] . ", " . $row2['first'] . "</td><td>" . $row2['class_grade'] . "-" . $row2['class_sub'] . "</td></tr>";
	}
	echo "</table><br /><br />";
}
?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
