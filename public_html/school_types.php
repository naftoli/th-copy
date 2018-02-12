<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>List of Children without school types</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>

<?
$success = 0;
$string = null;
if (isset($_POST)) {
	foreach ($_POST as $k => $v) {
		if ($k == 'submit') break;
		if ($v == 0) continue;
		$sql = "update users set school_type_id = $v where user_id = $k";
		if (mysql_query($sql)) $success++;
	}
	if ($success > 0) $string = "Successfully updated $success record(s)";
}
?>

<h1>List of Children without school types</h1>
<? if ($string) echo $string . "<br /><br />"; ?>
<form action='school_types.php' method='post'>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>Child</th>
<th>DOB</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;School</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Choose Type</th>
</tr>
<?
//get list of children without school type
include_once('db.php');
$sql = "
SELECT u.user_id, u.first, u.last, u.dob, s.school_name
FROM users AS u
LEFT OUTER JOIN schools AS s ON u.school_id = s.school_id
WHERE u.school_type_id =0
OR u.school_type_id IS NULL 
ORDER BY u.last, u.first";
$result = mysql_query($sql) or die(mysql_error());

while ($row = mysql_fetch_assoc($result)) {
	if ($row['last'] == '') continue;
	$name = $row['first'] . " " . $row['last'];
	if ($row['dob']) $dob = "(" . $row['dob'] . ")";
	else $dob = '';
	$school = $row['school_name'];
	echo "
	<tr><td>$name</td>
	<td>$dob</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$school</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<select name='" . $row['user_id'] . "'>
	<option value='0'>Please select</option>
	<option value='2'>Yeshiva Boys</option>
	<option value='3'>Yeshiva Girls</option>
	<option value='12'>Day School Boys</option>
	<option value='13'>Day School Girls</option>
	</select>
	</td></tr>";
}
?>
<tr><td colspan='4' align='center'><input type='submit' value='submit' name='submit'></td></tr>
</table>
</form>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
