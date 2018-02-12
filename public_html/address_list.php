<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Address List</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Address List</h1>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
<th>Name</th>
<th>Address</th>
<th>City</th>
<th>State</th>
<th>Zip</th
</tr>
<?
//get list of addresses
include_once('db.php');
$sql = "select ";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {

	}
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
