<? 
include("../db.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
	<table border=1 cellspacing=2 cellpadding=1>
	<tr>
	<?
	$start = gregoriantojd(7,3,2010);
	$end = gregoriantojd(10, 31, 2010);
	$i = 0;
	$sql = "select * from date_tasks_missions where (subject_id = 12 or subject_id = 40) 
			and (start_date > $start and end_date < $end) order by subject_id, level";
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	echo "Total records: " . $num . "<br />";
	$row = mysql_fetch_assoc($result);
	echo "<th>Row</th>";
	foreach ($row as $k => $v) echo "<th>" . $k . "</th>";
	echo "</tr><tr>";
	while ($rows = mysql_fetch_assoc($result)) {
		echo "<td>" . ++$i . "</td>";
		foreach ($rows as $k => $v) {
			if ($k == 'mission_name') echo "<td>" . $v . "</td>";
			else echo "<td>" . $v . "</td>";
		}
		echo "</tr>";
	}
	?>
	</table>
	</BODY>
</HTML>
