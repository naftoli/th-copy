<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			th, tr, td {
				border: 1px solid black;
				padding: 5px;
			}
			table {
				border: 1px solid black;
			}
			
		</style>
	</HEAD>
	
	<BODY>
<?php
include("../db.php");

//start and end dates for report
$start = 2455948;
$end = 2455956;
$num_weeks = 24;

$query = array();
for ($i = 0; $i < $num_weeks; $i++) {
	//get report for each of the 4 weeks
	$sql = "
		SELECT user_id 
		FROM date_tasks_marks 
		WHERE mark_date > $start  
		AND mark_date < $end  
		GROUP BY user_id";
	//echo $sql . "<br />";
	$query[$i] = $sql;
	$start -= 7;
	$end -= 7;
}

for ($i = 0; $i < $num_weeks; $i++) {
	//echo $query[$i] . "<br />";
	/*
?>
	<table>
		<tr>
			<th>
				Mark Date
			</th>
			<th>
				User ID
			</th>
			<th>
				Total Marked Tasks
			</th>
		</tr>
<?
	while ($row = mysql_fetch_assoc($result)) {
		echo "<tr><td>" . $row['mark_date'] . "</td><td>" . 
			$row['user_id'] . "</td><td>" . $row['total'] . "</td></tr>";
		$total += $row['total'];
	}
	echo "</table>";
	 * 
	 */
	//echo $query[$i] . "<br />";
	$result = mysql_query($query[$i]) or die(mysql_error());
	echo $i + 1 . " week(s) ago<br />";
	echo "Total marked sheets: " . number_format(mysql_num_rows($result)) . "<br />";
	echo "---------------------------<br />";
}
?>
	</BODY>
</HTML>