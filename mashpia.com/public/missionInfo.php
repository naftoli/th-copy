<?
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

require 'db.php';

$subjects = array();
$sql = "select subject_id, subject_name from subjects";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$subjects[$row['subject_id']] = $row['subject_name'];
}

$labels = array();
$sql = "select * from labels order by label_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$labels[$row['label_id']] = $row['label_name'];
}

$data = array();
$sql = "select * from date_tasks_missions dtm 
		join date_tasks dt using (date_tasks_mission_id) 
		where dtm.start_date >= 2456920 
		and dtm.created_by_school is null 
		and dtm.personal = 0 
		and subject_id != 1  
		group by subject_id, name 
		order by lang_id, subject_id, mission_name, cat, task_id, ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$data[] = $row;
}
//echo "<pre>"; print_r($data); echo "</pre>";

$types = array();
$levels = array();
foreach ($data as $row) {
	$sql = "select school_type_id, level from date_tasks_missions dtm 
			join date_tasks dt using (date_tasks_mission_id) 
			where dt.name = \"" . $row['name'] . "\"  
			and dtm.start_date >= 2456920 
			and dtm.created_by_school is null 
			and dtm.personal = 0 
			and dtm.subject_id = " . $row['subject_id'] . " 
			and dt.cat = \"" . $row['cat'] . "\" 
			group by school_type_id, level";
	$result = mysql_query($sql);
	while ($r = mysql_fetch_assoc($result)) {
		$types[$row['date_task_id']][$r['school_type_id']] = 1;
		$levels[$row['date_task_id']][$r['level']] = 1;
	}
}

echo "<pre>"; 
foreach ($data as $row) {
	//print_r($row);
	//print_r($info[$row['date_task_id']]);
}
echo "</pre>";

$lang = array(
	1	=>	'English', 
	2	=>	'Yiddish'
);

$arr = array('no', 'yes');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			table {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 12px;
				width: 90%;
				margin: auto;
			}
			td.stype {
				width: 90px;
			}
			td.ages {
				width: 60px;
			}
			th, td {
				padding: 5px 10px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Campaign ID</th>
				<th>Campaign</th>
				<th>Mission Name</th>
				<th>Mission Description</th>
				<th>Mission Value</th>
				<th>School Types</th>
				<th>From Age</th>
				<th>To Age</th>
				<th>Task Category</th>
				<th class="task">Task Name</th>
				<th>Label ID</th>
				<th>Label</th>
				<th>Task ID</th>
				<th>Task Order</th>
				<th>Short Name</th>
				<th>Pic</th>
				<th>Daily</th>
				<th>Qty Needed</th>
				<th>Points</th>
				<th>Default On</th>
				<th>Language</th>
			</tr>
			<?
			foreach ($data as $row) {
				echo "<tr><td>" . $row['subject_id'] . "</td><td>" . $subjects[$row['subject_id']] . 
					"</td><td>" . $row['mission_name'] . "</td><td>" . $row['mission_description'] . 
					"</td><td>" . $row['mission_value'] . "</td><td>";
				$str = '';
				foreach ($types[$row['date_task_id']] as $type => $val) 
					$str .= $type . ',';
				$str = substr($str, 0, (count($str) - 2));
				echo $str . "</td><td>";
				$keys = array_keys($levels[$row['date_task_id']]);
				echo $keys[0] . "</td><td>" . $keys[count($keys) - 1] . "</td><td>" . 
					$row['cat'] . "</td><td>" . $row['name'] . "</td><td>" . $row['label_id'] . 
					"</td><td>" . $labels[$row['label_id']] . "</td><td>" . $row['task_id'] . 
					"</td><td>" . $row['ord'] . "</td><td>" . $row['short_name'] . 
					"</td><td>" . $row['medium_pic'] . "</td><td>" . $arr[$row['daily_task']] . 
					"</td><td>" . $row['needed'] . "</td><td>" . $row['points'] . "</td><td>" . 
					$arr[$row['default_on']] . "</td><td>" . $lang[$row['lang_id']] . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>