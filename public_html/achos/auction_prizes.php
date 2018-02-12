<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Auction Prizes</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Auction Prizes</h1>
<table>
<tr>
<th>Name</th>
<th>ID</th>
</tr>
<?
//get list of prizes
include_once('db.php');
$sql = "select pa.prize_id, pa.prize_name from prizes_auction as pa, auction_prizes as ap
		where pa.prize_id = ap.prize_id and ap.auction_id = 25 order by pa.prize_name";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['prize_name'] . "</td>";
	echo "<td>" . $row['prize_id'] . "</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
