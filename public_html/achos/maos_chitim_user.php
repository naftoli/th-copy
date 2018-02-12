<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Maos Chitim Campaign</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Maos Chitim Campaign</h1>
<table>
<tr>
<th>Student</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanya Lines</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mishna Lines</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pledges</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Collected</th>
</tr>
<?
include_once('db.php');

$id = $_GET['id'];
$users = array();
$sql = "select user_id, last, first  
		from users  
		where class_id = $id 
		#and user_registered > 0 
		order by last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

$total_tanya = 0;
$total_mishna = 0;
$total_pledges = 0;
$total_collected = 0;
for ($i = 0; $i < count($users); $i++) {

	$user_id = $users[$i]['user_id'];
	$last = $users[$i]['last'];
	$first = $users[$i]['first'];
	
	$sql = "select tanya_lines, mishna_lines, pledges, collected from tanya_users where user_id = $user_id";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$tanya_lines = $row['tanya_lines'];
	$mishna_lines = $row['mishna_lines'];
	$pledges = "$" . number_format($row['pledges']);
	$collected = "$" . number_format($row['collected']);

	echo "<tr><td>$first $last</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$tanya_lines</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$mishna_lines</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pledges</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$collected</td></tr>";
	
	$total_tanya += $row['tanya_lines'];
	$total_mishna += $row['mishna_lines'];
	$total_pledges += $row['pledges'];
	$total_collected += $row['collected'];
}
echo "<tr><td align='right'><b>Total:</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$total_tanya</b></td>
	<td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$total_mishna</b></td>
	<td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$" . number_format($total_pledges) . 
	"</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$" . number_format($total_collected) . "</b></td></tr>";
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
