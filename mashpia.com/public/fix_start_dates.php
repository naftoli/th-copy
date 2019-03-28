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
<? include_once('db.php'); ?>
<h1>List of children without start date</h1>
<?
if (isset($_POST['submit'])) {
	$success = 0;
	foreach ($_POST['id'] as $id) {
		$sql = "update users set user_start_date = 2455448 where user_id = $id";
		if (mysql_query($sql)) $success++;
	}
	echo "Successfully updated $success users.<br />";
}
?>
<form action='fix_start_dates.php' method='post'>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School</th>
<th>Name</th>
</tr>
<?
//get list of schools
$sql = "select s.school_name, u.user_id, u.first, u.last from schools as s, users as u 
		where s.school_id = u.school_id 
		and u.user_registered > 0 
		and (u.user_start_date = 0 or u.user_start_date is null)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$name = $row['first'] . " " . $row['last'];
	echo "<tr><td>" . $row['school_name'] . "</td><td>" . $name . "</td><input type='hidden' name='id[]' value=" . $row['user_id'] . "></tr>";
}
if (mysql_num_rows($result) > 0) echo "<input type='submit' name='submit' value='fix'>";
?>
</table>
</form>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
