<? 
include("db.php");

$date_tasks_mission_id = $_GET["date_tasks_mission_id"];
$sql = "SELECT dt.name, dt.sequence_number, l.label_name, f.frequency_name ";
$sql = $sql . "FROM date_tasks AS dt ";
$sql = $sql . "LEFT JOIN labels AS l USING (label_id) ";
$sql = $sql . "LEFT JOIN frequencies AS f USING (frequency_id) ";
$sql = $sql . "WHERE date_tasks_mission_id=" . $date_tasks_mission_id;

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
				<TH>TASK</TH>
				<TH>LABEL</TH>
				<TH>SEQUENCE #</TH>
				<TH>FREQUENCY</TH>
			</TR>
			
			<? while ($row = mysql_fetch_assoc($query)) : ?>
			<TR>
				<TD><?=$row["name"];?></TD>
				<TD><?=$row["label_name"];?></TD>
				<TD><?=$row["sequence_number"];?></TD>
				<TD><?=$row["frequency_name"];?></TD>
			</TR>
			<? endwhile; ?>			
		</TABLE>
		
	</body>	
</html>
