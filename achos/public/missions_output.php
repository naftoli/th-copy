<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>School List</title>
<style type='text/css'>
tr, th, td {
	border: 1px dashed black;
	padding: 6px;
}
</style>
</head>

<body>
<h1>Next year's missions</h1>
<?
$sql = "select s.subject_name, dtm.date_tasks_mission_id, dtm.start_date, dtm.end_date,  dtm.mission_name, 
		l.label_name, dt.date_task_id, dt.name, dt.mandatory_qty, dt.optional_qty, dtm.level, st.school_type_name 
		from date_tasks dt 
		join date_tasks_missions dtm using (date_tasks_mission_id) 
		join subjects s on (s.subject_id = dtm.subject_id) 
		join labels l on (l.label_id = dt.label_id) 
		join school_types st on (st.school_type_id = dtm.school_type_id) 
		where dtm.start_date > 2456154 
		and dtm.end_date < 2456540 
		and s.subject_id = 94    
		order by subject_name, mission_name";

$result = mysql_query($sql);

echo "<table>";
echo "<th>Subject</th>";
echo "<th>Mission ID</th>";
echo "<th>Start Date</th>";
echo "<th></th>";
echo "<th>End Date</th>";
echo "<th></th>";
echo "<th>Mission</th>";
echo "<th>Label</th>";
echo "<th>Task ID</th>";
echo "<th>Task</th>";
echo "<th>Mandatory</th>";
echo "<th>Optional</th>";
echo "<th>Level</th>";
echo "<th>School Type</th>";

while ($row = mysql_fetch_assoc($result)) {
	echo "<tr>";
	foreach ($row as $k => $v) {
		if ($k == 'start_date') {
			echo "<td>$v</td>";
			echo "<td>" . date('Y-m-d', jdtounix($v)) . "</td>";
			continue; 
		}
		if ($k == 'end_date') {
			echo "<td>$v</td>";
			echo "<td>" . date('Y-m-d', jdtounix($v)) . "</td>";
			continue; 
		}
		else 
			echo "<td>$v</td>";
	}
	echo "</tr>";
}
echo "</table>";
echo "<hr />";
?>
</body>
</html>