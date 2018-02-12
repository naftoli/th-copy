<? 
$url = $_SERVER['SCRIPT_URI'];
$info = explode('/', $url);
if ($info[0] == 'http:') {
	header("Location: https://mashpia.com/school_list2.php");
}
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>School List</title>
<style>
	td {
		vertical-align: top;
	}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<?
$schools = array();
$sql = "select * from schools where school_era is null order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[] = $row;
}
?>
<h1>School List</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>School</th>
<th>Card Info</th>
</tr>
<?
foreach ($schools as $school) {
	$info = $school['cc_first'] . ' ' . $school['cc_last'] . "<br />";
	$info .= $school['cc_address'] . "<br />";
	$info .= $school['cc_state'] . ', ' . $school['cc_zip'] . "<br />";
	$info .= 'number: ' . $school['cc_number'] . "<br />" . 'exp: ' . $school['cc_exp'] . "<br />" . 
			'cvv: ' . $school['cc_cvv'];
	echo "<tr><td>" . $school['school_name'] . "</td><td>" . $info . "</td></tr>";
	echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
