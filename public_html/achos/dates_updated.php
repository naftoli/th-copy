<? 
$admin_auth = array('school','user'); 
require('header.php');
include_once('db.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Dates Updated</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Dates Updated</h1>
<?
$medals_sql = "
	SELECT date_awarded, COUNT( date_awarded ) AS total
	FROM  `medal_marks` 
	GROUP BY date_awarded
	ORDER BY total DESC";

$ranks_sql = "
	SELECT date_promoted, COUNT( date_promoted ) AS total
	FROM  `rank_marks` 
	GROUP BY date_promoted
	ORDER BY total DESC";
	
$medals_res = mysql_query($medals_sql);
$ranks_res = mysql_query($ranks_sql);

function processDB($handle) {
	$temp = array();
	while ($row = mysql_fetch_assoc($handle)) {
		$temp[] = $row;
	}
	return $temp;
}

$medals = processDB($medals_res);
$ranks = processDB($ranks_res);
?>
<table>
<tr>
<th>Date</th>
<th>Medal Total</th>
<th>Date</th>
<th>Rank Total</th>
</tr>
<?
for ($i = 0; $i < 5; $i++) {
	echo "<tr><td>" . jdtogregorian($medals[$i]['date_awarded']) . "</td><td>" . $medals[$i]['total'] . "</td><td>" . 
	jdtogregorian($ranks[$i]['date_promoted']) . "</td><td>" . $ranks[$i]['total'] . "</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
