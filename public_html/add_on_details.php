<? 
$admin_auth = array('school','user'); 
require('header.php');
if (!isset($_GET['id'])) header("Location: add_on_report.php");
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
<h1>Add-ons Details</h1>
<table border='1' cellpadding='5' cellspacing='5'>
<tr>
<th>First Name</th>
<th>Last Name</th>
<th>Teacher</th>
<th>Grade</th>
<th>Shirt Size</th>
</tr>
<?
include_once('db.php');
$id = $_GET ['id'];
$sql = "select u.first, u.last, u.add_on_one, u.user_registered, u.shirt_size, c.class_teacher, c.class_grade, c.class_sub 
		from users as u, classes as c 
		where u.class_id = c.class_id 
		and u.school_id = $id 
		and u.user_registered > 0 
		and u.add_on_one = 1 
		order by c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['class_teacher'] . 
	"</td><td>" . $row['class_grade'] . "-" . $row['class_sub'] . "</td><td>" . $row['shirt_size'] . "</td></tr>";
}
?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
