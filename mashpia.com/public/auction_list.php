<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<style>
table, td, th {
	border: 1px solid #000000;
	padding: 5px;
	font-size: 14px;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>School List</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Task List</h1>
<table>
<tr>
<th>Name</th>
<th>Prize</th>
</tr>
<?
include_once('db.php');
$sql = "select u.first, u.last, p.prize_name from users as u, prizes_auction as p, auction_winners as aw 
where aw.prize_id = p.prize_id 
and aw.user_id = u.user_id 
and aw.auction_id = 25 
and u.user_registered is null 
order by u.last, u.first";

$result = mysql_query($sql) or die(mysql_error());

while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['first'] . " " . $row['last'] . "</td><td>" . $row['prize_name'] . "</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
