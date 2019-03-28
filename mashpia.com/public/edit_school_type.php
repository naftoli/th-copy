<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>School Types</title>
<style>
tr, th, td {
	vertical-align: text-top;
	border: 1px solid black;
	padding: 2px;
}
.save {
	color:red;
	font-weight:bold;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? //if ($admin->auth == 'super' || $admin->admin_id == 60) : ?>
<? if ($admin->auth == 'super') : ?>

<h1>School Types</h1>

<?
$msg = null;
if (isset($_POST['submit'])) {
	$sql = "update users set school_type_id = $_POST[type] where user_id = $_POST[id]";
	if ($result = mysql_query($sql)) echo "<p class='save'>Saved</p>";
}
$sql = "select * from schools order by school_name";
$schools = mysql_query($sql) or die(mysql_error());
?>
<form method='post' action='edit_school_type.php'>
Please choose school: 
<select name='school'>
<?
while ($school = mysql_fetch_assoc($schools)) {
	echo "<option value=" . $school['school_id'] . ">" . $school['school_name'] . "</option>";
}
?>
</select>
<input type='submit' value='submit' name='action'>
</form>
<? 
if (isset($_POST['submit']) || isset($_POST['action'])) {
?>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School</th>
<th>Name</th>
<th>Grade</th>
<th>School Type</th>
<th>Change To</th>
<th>&nbsp;</th>
</tr>
<?
//get list of types
include_once('db.php');
$sql = "
SELECT s.school_name, u.user_id, u.first, u.last, st.school_type_name, c.class_grade, c.class_sub 
from schools as s, users as u, school_types as st, classes as c 
where s.school_id = u.school_id 
and u.school_type_id = st.school_type_id 
and u.class_id = c.class_id 
and s.school_id = $_POST[school] 
order by s.school_name, u.last, u.first
";
$result = mysql_query($sql) or die(mysql_error());

while ($row = mysql_fetch_assoc($result)) {
	echo "<form method='post' action='edit_school_type.php'>
	<tr><td>$row[school_name]</td><td>$row[first] $row[last]</td>
	<td>$row[class_grade]$row[class_sub]</td>
	<td>$row[school_type_name]</td>
	<td><select name='type'>";

	$types = "select * from school_types";
	$res = mysql_query($types);
	while ($type = mysql_fetch_assoc($res)) {	
		echo '<option value='. $type['school_type_id'] . '>'.$type['school_type_name'] .'</option>';
	}
	echo "</select><td><input type='hidden' name='id' value=" . $row['user_id'] . ">
	<input type='hidden' name='school' value='" . $_POST['school'] . "'><input name='submit' type='submit' value='update'></td></tr></form>";
}
?>
</table>
<? } ?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
