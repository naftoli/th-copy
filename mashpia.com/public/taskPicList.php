<?
require 'db.php';

$tasks = array();
$sql = "select dtm.subject_id, s.subject_name, dt.cat, dt.short_name, dt.name, dt.medium_pic 
		from date_tasks dt 
		join date_tasks_missions dtm using (date_tasks_mission_id) 
		join subjects s using (subject_id) 
		where dt.medium_pic is not null 
		and subject_id != 1 
		and lang_id = 1 
		group by dt.name 
		order by dtm.subject_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$tasks[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Task Picture List</title>
		<style>
			th, td {
				padding: 3px;
				border: 1px solid black;
				margin: 0;
				font-size: 12px;
			}
			img {
				width: 50px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Subject ID</th>
				<th>Subject</th>
				<th>Category</th>
				<th>Short Name</th>
				<th>Task Name</th>
				<th>Pic</th>
				<th>Pic Name</th>
			</tr>
			<?
			foreach ($tasks as $row) {
				echo "<tr><td>" . $row['subject_id'] . "</td><td>" . $row['subject_name'] . 
					"</td><td>" . $row['cat'] . "</td><td>" . $row['short_name'] . "</td><td>" . 
					$row['name'] . "</td><td><img src='mission_report/color/" . $row['medium_pic'] . ".jpg' />" . 
					"</td><td>" . $row['medium_pic'] . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>