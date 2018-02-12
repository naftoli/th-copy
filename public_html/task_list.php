<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<style>
table, td, th {
	border: 1px solid #000000;
	padding: 5px;
	font-size: 14px;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>School List</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Task List</h1>
<table>
<tr>
<th>Task ID</th>
<th>Subject</th>
<th>Mission</th>
<th>Task</th>
<th>Label</th>
<th>Description</th>
<th>Qty</th>
<th>Mandatory</th>
<th>Optional</th>
<th>School Type ID</th>
<th>School Type</th>
</tr>
<?
//get list of schools
include_once('db.php');
$sql = "
select s.subject_name, dtm.date_tasks_mission_id, 
dtm.start_date, dtm.end_date, dtm.mission_name, l.label_name, 
dt.date_task_id, dt.name, dt.description, dt.quantity, dt.mandatory_qty, dt.optional_qty, 
dtm.level as year, dtm.track_id as ladder, dtm.school_type_id, st.school_type_name 
from date_tasks dt 
join date_tasks_missions dtm using (date_tasks_mission_id) 
join subjects s on (s.subject_id = dtm.subject_id) 
left join labels l on (l.label_id = dt.label_id) 
join school_types st on (st.school_type_id = 
dtm.school_type_id) 
where dtm.start_date > 2456154 
and dtm.end_date < 2456540 
and s.subject_id = 1  
and dtm.school_type_id in (2,3) 
order by start_date, end_date, mission_name, label_name, year, ladder
";

$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
	$mandatory = $row['mandatory_qty'] == 0 ? 'no' : 'yes';
	$optional = $row['optional_qty'] == 0 ? 'no' : 'yes';
	echo "<tr><td>" . $row['date_task_id'] . "</td><td>" . $row['subject_name'] . 
		"</td><td>" . $row['mission_name'] . "</td><td>" . $row['name'] . 
			"</td><td>" . $row['label_name'] . "</td><td>" . $row['description'] . 
				"</td><td>" . $row['quantity']. "</td><td>$mandatory</td><td>$optional</td><td>" . 
				$row['school_type_id'] . "</td><td>" . $row['school_type_name'] . "</td></tr>";
}
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
