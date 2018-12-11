<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Update Ranks</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Update Ranks</h1>
<?
include_once('db.php');

$sql = "SELECT u.user_id 
FROM users AS u
LEFT OUTER JOIN rank_marks AS r ON u.user_id = r.user_id
WHERE r.rank_ord IS NULL";

$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_row($result)) {
	$update = "insert into rank_marks values(1, $row[0], 2455448, null, null, null)";
	$res = mysql_query($update) or die(mysql_error());	
}

?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
