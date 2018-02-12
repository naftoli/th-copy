<? 
include("db.php");

$school_type_id = $_GET["school_type_id"];
$subject_id = $_GET["subject_id"];
$level = $_GET["level"];
$track_id = $_GET["track_id"];
$start_date = $_GET["start_date"];
$end_date = $_GET["end_date"];

$sql = "SELECT * FROM date_tasks_missions WHERE school_type_id=" . $school_type_id . " AND subject_id=" . $subject_id . " AND level=" . $level . " AND track_id=" . $track_id . " AND start_date > " . $start_date . " AND end_date < " . $end_date;
$query = mysql_query($sql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>

	<body>	
		<TABLE>
			<TR style="text-align:left; color:blue;">
				<TH>MISSION</TH>
				<TH>START</TH>
				<TH>END</TH>
				<TH>TASKS</TH>
			</TR>
			
			<? while ($row = mysql_fetch_assoc($query)) : ?>
			<TR>				
				<TD><?=$row["mission_name"];?></TD>
				<TD><?=jdtogregorian($row["start_date"]);?></TD>
				<TD><?=jdtogregorian($row["end_date"]);?></TD>
				<TD><a href="tasks_report.php?date_tasks_mission_id=<?=$row['date_tasks_mission_id'];?>">TASKS</a></TD>
			<TR>
			<? endwhile; ?>
		</TABLE>
	</body>	
</html>
