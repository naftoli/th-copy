<?
$admin_auth = array('school');
require_once 'header.php';
?>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<pre>
			<?
			$sm = calculateSM(5774);

			$years = array();
			for ($j = 6; $j < 15; $j++) {
				$years[] = $j;
			}
			
			$dtm = array();
			$sql = "show columns from date_tasks_missions";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$dtm[] = $row['Field'];
			}
			
			$dt = array();
			$sql = "show columns from date_tasks";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$dt[] = $row['Field'];
			}
			
			$i = 0;
			$missions = array();
			foreach ($sm as $date) {
				foreach ($years as $year) {
					$sql = "select * from date_tasks_missions 
							where start_date = $date 
							and end_date = $date 
							and school_type_id = 2 
							and level = $year 
							and track_id = 5  
							and subject_id = 1";
					$result = mysql_query($sql);
					$mission = mysql_fetch_assoc($result);
					
					$sql2 = "select * from date_tasks where date_tasks_mission_id = " . $mission['date_tasks_mission_id'];
					$result2 = mysql_query($sql2);
					if ($result2) {
						$tasks = array();
						while ($row = mysql_fetch_assoc($result2)) {
							$tasks[] = $row;
						}
						$missions[$i]['mission'] = $mission;
						$missions[$i++]['tasks'] = $tasks; 
					} else {
						//$errors[] = mysql_error() . "<br />" . $sql;
					}
				}
			}
			//print_r($missions);
			//print_r($errors);
			$errors = array();
			mysql_query("set autocommit=0");
			mysql_query("begin");
			$types = array(12,13);
			foreach ($types as $type) {
				foreach ($missions as $mission) {
					$sql = "insert ignore into date_tasks_missions set ";
					foreach ($dtm as $field) {
						if ($field == 'date_tasks_mission_id') {
							continue;
						} else if ($field == 'school_type_id') {
							$sql .= "$field = $type, ";
						} else if ($field == 'mission_name' || $field == 'mission_description') {
							$sql .= "$field = '" . $mission['mission'][$field] . "', ";
						} else if (empty($mission['mission'][$field])) {
							$sql .= "$field = '', ";
						} else {
							$sql .= "$field = " . $mission['mission'][$field] . ", ";
						}
					}
					$sql = substr_replace($sql, "", -2);
					//echo $sql . "<br />";
					if (mysql_query($sql)) {
						$id = mysql_insert_id();
						foreach ($mission['tasks'] as $task) {
							$sql = "insert into date_tasks set ";
							foreach ($dt as $field) {
								if ($field == 'date_task_id') {
									continue;
								} else if ($field == 'date_tasks_mission_id') {
									$sql .= "$field = $id, ";
								} else if ($field == 'name' || $field == 'description' || $field == 'cat') {
									$sql .= "$field = '" . $task[$field] . "', ";
								} else if ($field == 'sequence_number' || ($field == 'quantity') && empty($task[$field])) {
									$sql .= "$field = null, ";
								} else {
									$sql .= "$field = " . $task[$field] . ", ";
								}
							}
							$sql = substr_replace($sql, "", -2);
							if (!mysql_query($sql)) {
								$errors[] = mysql_error() . "<br />" . $sql;
							}
						}
					} else {
						$errors[] = mysql_error() . "<br />" . $sql;
					}
				}
			}
			if ($errors) {
				mysql_query("rollback");
				print_r($errors);
			} else {
				mysql_query("commit");
			}
			mysql_query("set autocommit=1");
		?>
		</pre>
	</body>
</html>			
			