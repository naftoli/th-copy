<?php
include ("db.php");

$sql = "SELECT COUNT(*) AS total, s.subject_name, dt.date_task_id, dt.name, dt.description, l.label_name, l.label_description, dtm.mission_name ";
$sql = $sql . "FROM date_tasks AS dt ";
$sql = $sql . "LEFT JOIN labels AS l USING (label_id) ";
$sql = $sql . "JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
$sql = $sql . "JOIN subjects AS s USING (subject_id) ";
$sql = $sql . "GROUP BY dt.name, l.label_name";
$query = mysql_query($sql);

//$periods = "<SELECT>";
//$periods_sql = "SELECT * FROM periods";
//$periods_query = mysql_query($periods_sql);
//while ($periods_row = mysql_fetch_assoc($periods_query)) {
//	$periods = $periods . "<OPTION value='" . $periods_row['period_id'] . "'>" . $periods_row['period_name'] . "</OPTION>";
//}
//$periods = $periods . "</SELECT>";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<HTML xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
	</head>
	
	<BODY>
		<TABLE style="width:1600px;">
			<TR>
				<TH align="left"><span style="color:darkblue; text-decoration:underline;">Task ID</span></TH>	
				<TH align="left"><span style="color:darkblue; text-decoration:underline;">SUBJECT</span></TH>							
				<TH align="left"><span style="color:darkblue; text-decoration:underline;">MISSION</span></TH>
				<TH align="left"><span style="color:darkgreen; text-decoration:underline;"># OF TASKS</span></TH>
				<TH align="left"><span style="color:darkgreen; text-decoration:underline;">Task Name</span></TH>
			</TR>
			
			<? while ($row = mysql_fetch_assoc($query)) : ?>
				<? if ($row["label_name"] == "") : ?>
				<TR>
					<TD><span style="color:blue;"><?=$row["date_task_id"]?></span></TD>	
					<TD><span style="color:blue;"><?=$row["subject_name"]?></span></TD>	
					<TD><span style="color:blue;"><?=$row["mission_name"];?></span></TD>	
					<TD><span style="color:green;"><?=$row["total"];?></span></TD>			
					<TD style="width:1000px;"><span style="color:green;"><?=$row["name"];?></span></TD>
				</TR>
				<? endif; ?>
			<? endwhile; ?>
		<TABLE>
	</BODY>
<HTML>