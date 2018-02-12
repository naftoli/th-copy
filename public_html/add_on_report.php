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
<h1>Add-ons Report</h1>
<?
include_once('db.php');
$sql = "select u.school_id, s.school_name, count(u.add_on_one) as total from users as u, schools as s 
		where user_registered > 0 
		and u.school_id = s.school_id 
		and u.add_on_one = 1 
		group by s.school_name 
		order by s.school_name";
$result = mysql_query($sql) or die(mysql_error());
echo "<p><b>Total children that have purchases add-on one:</b></p>";
$total = 0;
while ($row = mysql_fetch_assoc($result)) {
	echo $row['school_name'] . ": <a href='add_on_details.php?id=$row[school_id]'>" . $row['total'] . "</a><br />";
	$total += $row['total'];
}
echo "<br />total: " . $total;
/*
echo "<br /><br />";
echo "<p><b>Total Shirt Sizes by School:</b></p>";
$sql = "select shirt_size, count(users.shirt_size) as total, school_name from users, schools 
		where user_registered > 0 
		and users.add_on_one = 1 
		and users.school_id = schools.school_id 
		group by school_name, shirt_size  
		order by school_name, shirt_size desc";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
	echo $row['school_name'] . ": " . $row['shirt_size'] . " - " . $row['total'] . "<br />";
}
*/
?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
