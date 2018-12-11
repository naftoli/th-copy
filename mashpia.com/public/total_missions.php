<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Mission Report</title>
</head>

<body>
<? include('admin_header.php');?>

<h1>Mission Report</h1>
<table border="1" cellspacing="5" style="font-size:12px" width="100%">
<tr>
<th>Campaign</th>
<th>Total</th>
</tr>
<?
include_once('db.php');

$sql = "SELECT s.subject_name, SUM( dtmm.mission_count )
	FROM  `date_tasks_mission_marks` AS dtmm, subjects AS s, date_tasks_missions AS dtm
	WHERE dtm.start_date >2455437
	AND s.subject_id = dtmm.subject_id
	AND dtm.date_tasks_mission_id = dtmm.date_tasks_mission_id
	GROUP BY dtmm.subject_id";
	
$result = mysql_query($sql);
$total = 0;
while ($row = mysql_fetch_row($result)) {
	echo "<tr><td>$row[0]</td><td>" . number_format($row[1]) . "</td></tr>";
	$total += $row[1];
}
echo "<tr><td align='right'><b>Grand Total:</b></td><td><b>" . number_format($total) . "</b></td></tr>";
?>
</table>
</body>
</html>
