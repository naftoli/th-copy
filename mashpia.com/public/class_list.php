<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once 'header.php';

if ($admin_user['auth'] != 'super') {
    die('Unauthorized');
}

require_once '../includes/globals.php';
$key = ENCRYPTION_KEY;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Class List</title>
<style type='text/css'>
tr, th, td {
	border: 1px dashed black;
	padding: 6px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<h1>Class List</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>Class ID</th>
<th>School</th>
<th>Grade</th>
<th>Teacher</th>
<th>Admin</th>
<th>username</th>
<th>password</th>
</tr>
<?
//get list of classes
$sql = "select class_id, class_grade, class_sub, class_teacher, school_name from classes c join schools s on c.school_id = s.school_id order by c.school_id, class_grade, class_sub";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['class_id'] . "</td><td>" . $row['school_name'] . "</td><td>" . 
    ($row['class_grade'] . ($row['class_sub'] ? ' - ' . $row['class_sub'] : '')) . 
    "</td><td>" . $row['class_teacher'] . "</td><td>&nbsp;</td><td>&nbsp;</td>";
	$sql2 = "select admin_id from admin_auths where id = $row[class_id] and auth = 'class'";
	$result2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_row($result2)) {
		$sql3 = "select title, first, last, username, password from admins where admin_id = $row2[0]";
		$result3 = mysql_query($sql3);
		$flag = true;
		while ($row3 = mysql_fetch_assoc($result3)) {
			if ($flag) echo "</tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
			echo "<td>" . $row3['title'] . " " . $row3['first'] . " " . $row3['last'] . "</td><td>" . 
				$row3['username'] . "</td><td>";
				if (!empty(trim($row3['password']))) {
					echo decryptPassword($row3['password'], $key);
				} else {
					echo "";
				}
				echo "</td></tr>";
			$flag = false;
		}
	}
}
?>
</table>
</body>
</html>
