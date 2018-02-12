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
$sql = "select u.school_id, s.school_name, count(u.add_on_two) as total from users as u, schools as s 
		where user_registered > 0 
		and u.school_id = s.school_id 
		and u.add_on_two = 1 
		group by s.school_name 
		order by s.school_name";
$result = mysql_query($sql) or die(mysql_error());
echo "<p><b>Total children that have purchases add-on two:</b></p>";
$total = 0;
while ($row = mysql_fetch_assoc($result)) {
	echo $row['school_name'] . ": <a href='add_on_details2.php?id=$row[school_id]'>" . $row['total'] . "</a><br />";
	$total += $row['total'];
}
echo "<br />total: " . $total;
?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
