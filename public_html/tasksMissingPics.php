<?
require_once 'db.php';

$typeDesc = array(
	2	=>	'Yeshiva Boys',
	3	=>	'Yeshiva Girls', 
	12	=>	'Frum Boys', 
	13	=>	'Frum Girls'
);

$tasks = array();
$sql = "select s.subject_name, s.subject_id, dt.short_name, dt.name, dt.medium_pic, dt.task_id from date_tasks dt 
		join date_tasks_missions dtm using (date_tasks_mission_id) 
		join subjects s using (subject_id) 
		where dtm.start_date > 2457266  
		and dt.short_name != '' 
		and dt.medium_pic is null 
		group by dt.name";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$sql2 = "select school_type_id from date_tasks_missions dtm 
			join date_tasks dt using (date_tasks_mission_id) 
			where dtm.subject_id = " . $row['subject_id'] . " 
			and dt.task_id = " . $row['task_id'] . " 
			group by school_type_id";
	$result2 = mysql_query( $sql2 );
	$types = array();
	while ($row2 = mysql_fetch_assoc($result2)) {
		$types[] = $typeDesc[$row2['school_type_id']];
	}
	$desc = implode(',', $types);
	$tasks[$row['subject_id']][$row['subject_name']][$row['short_name']][][$row['name']][$row['task_id']][$desc] = $row['medium_pic'];
}
//echo "<pre>"; print_r( $tasks ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Missing Pictures</title>
		<style>
			table {
				width: auto;
				margin: auto;
			}
			th {
				text-align: left;
			}
			tr, th, td {
				padding: 5px;
				font-size: 12px;
			}
			img {
				height: 50px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<th>School Types</th>
			<th>Campaign ID</th>
			<th>Campaign</th>
			<th>Task ID</th>
			<th>Short Name</th>
			<th>Task Name</th>
			<?
			foreach ($tasks as $subjectID => $subjects) {
				foreach ($subjects as $subject => $arr) {
					foreach ($arr as $short => $info) {
						foreach ($info as $arr) {
							foreach ($arr as $task => $info) {
								foreach ($info as $taskID => $arr) {
									foreach ($arr as $desc => $pic)
									//if ($taskID == 99.99) $taskID = '';
									echo "<tr><td>" . $desc . "</td><td>" . $subjectID . "</td><td>" . $subject . "</td><td>" . 
										$taskID . "</td><td>" . $short . "</td><td>" . $task . "</td></tr>";
								}
							}
						}
					}
				}
			}
			?>
		</table>
	</body>
</html>