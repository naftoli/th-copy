<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tehillim Report</title>
</head>

<body>
<? include('admin_header.php');?>

<h1>Tehillim Report</h1>
<table border="1" cellspacing="5" style="font-size:12px" width="100%">
<tr>
<th>Task</th>
<th>School</th>
<th align="right">Total</th>
</tr>
<?
include_once('db.php');
function getTask($task, $date) {
	$sql = "SELECT sum(done_qty), u.school_id, dt.name, s.school_name   
			FROM date_tasks AS dt
			JOIN date_tasks_marks AS dtm using (date_task_id) 
			JOIN users AS u on (u.user_id = dtm.user_id) 
			JOIN schools AS s on (u.school_id = s.school_id) 
			JOIN date_tasks_missions as dtms using (date_tasks_mission_id) 
			WHERE dt.name like '%" . $task . "%'  
			and dtms.start_date > $date 
			group by u.school_id";
	
	$i = 0;
	$total = 0;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_row($result)) {
		$total += $row[0];
		if (!$i++) echo "<tr><td>$row[2]</td><td>$row[3]</td><td align='right'>" . number_format($row[0]) . "</td></tr>";
		else echo "<tr><td>&nbsp;</td><td>$row[3]</td><td align='right'>" . number_format($row[0]) . "</td></tr>";
	}
	
	echo "<tr><td>&nbsp;</td><td align='right'><b>Grand Total:</b></td><td align='right'><b>" . number_format($total) . "</b></td></tr>";
	echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
}

$tasks = array(
	"How many Kapitlach did you say?", 
	"How many minutes did you spend saying תהלים?"
	);

foreach ($tasks as $task) 
		getTask($task, 2455437);
?>
</table>
</body>
</html>
