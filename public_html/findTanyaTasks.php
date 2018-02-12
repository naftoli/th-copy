<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
	<?
	require_once 'db.php';
	
	$tasks = array();
	$sql = "select * from date_tasks_missions dtm 
			join date_tasks dt using (date_tasks_mission_id) 
			where dtm.start_date > 2456641   
			and dtm.subject_id = 27 
			and dt.cat not like 'My Personal Task%' 
			order by start_date, school_type_id, level";
	$result = mysql_query($sql);
	$date = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$getParsha = false;
		if ($date != $row['start_date']) {
			$date = $row['start_date'];
			$getParsha = true;
		}
		$type = $row['school_type_id'];
		switch ($type) {
			case '2':
				$type = "Yeshiva Boys";
				break;
			case '3':
				$type = "Yeshiva Girls";
				break;
			case '12':
				$type = "Day School Boys";
				break;
			case '13':
				$type = "Day School Girls";
				break;
		}
		
		$level = "Year " . $row['level'];
		
		if ($getParsha) {
			$sql2 = "select * from parshos where start = " . $date;
			$result2 = mysql_query($sql2);
			if (mysql_num_rows($result2) > 0) {
				$row2 = mysql_fetch_assoc($result2);
				$parsha = $row2['name'];
			} else {
				$parsha = "Yoma Depagras";
			}
		}
		$tasks[$parsha][$type][$level][] = $row['name'];
	}
	
	echo "<pre>";
	print_r($tasks);
	echo "</pre>";
	?>
	</body>
</html>