<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type='text/css'>
		tr, th, td {
			border: 1px dashed black;
			padding: 6px;
			font-size: 12px;
		}
		</style>
	</HEAD>
	
	<BODY>
		<table>
			<tr>
				<th>Date Task ID</th>
				<th>School Type</th>
				<th>Year</th>
				<th>Ladder</th>
				<th>Mission Name</th>
				<th>Mission Description</th>
				<th>Start Date</th>
				<th>End Date</th>
				<th>Name</th>
				<th>Quantity</th>
				<th>Description</th>
			</tr>
<? 
include("db.php");

$sql = "
	SELECT dt.date_task_id, st.school_type_name, dtm.level, dtm.track_id, dtm.mission_name, 
	dtm.mission_description, dtm.start_date, dtm.end_date, dt.name, dt.quantity, dt.description
	FROM date_tasks dt
	JOIN date_tasks_missions dtm
	USING ( date_tasks_mission_id ) 
	JOIN school_types st 
	USING (school_type_id) 
	WHERE dtm.subject_id =1 
	AND dtm.start_date > 2455438 
	AND dtm.start_date < 2456188 
	AND dtm.start_date > " . unixtojd() . " 
	ORDER BY school_type_id,
	LEVEL , track_id, start_date, end_date";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
	echo "<tr><td>" . $row['date_task_id'] . "</td><td>" . $row['school_type_name'] . "</td><td>";
	echo $row['level'] . "</td><td>" . $row['track_id'] . "</td><td>" . $row['mission_name'] . "</td><td>" . $row['mission_description'];
	echo "</td><td>" . jdtogregorian($row['start_date']) . "</td><td>" . jdtogregorian($row['end_date']) . "</td><td>" . $row['name'];
	echo "</td><td>" . $row['quantity'] . "</td><td>" . $row['description'] . "</td></tr>";
}
?>
		</table>
	</BODY>
</HTML>