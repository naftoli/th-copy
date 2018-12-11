<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			tr, th, td {
                padding: 5px;
                border: 1px solid black;
                font-size: 12px;
            }
		</style>
	</head>
	
	<body>
	<?
	require_once 'db.php';
	
	$parshos = array();
	$sql = "select * from parshos where start > 2456641";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$parshos[$row['end']] = $row['name'];
	}
	
	if (isset($_POST['submit'])) {
		$subjects = array();
		$sql = "select * from subjects";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$subjects[$row['subject_id']] = $row['subject_name'];
		}
		
		$tasks = array();
		$possibleTasks = array();
		$sql = "select * from date_tasks_missions dtm 
				join date_tasks dt using (date_tasks_mission_id) 
				where dtm.start_date > 2456641 
				and dt.cat != 'Birthday' 
				and dt.cat not like 'My Personal Task%' 
				and dtm.school_type_id = " . $_POST['type'] . " 
				and dtm.end_date <= " . $_POST['parsha'] . " 
				and dtm.start_date >= " . ($_POST['parsha'] - 6) . " 
				order by start_date, school_type_id, level, track_id, subject_id";
		//echo $sql;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
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
			
			$level = $row['level'];
			$subject = $row['subject_id'];	
			//$track = "Track " . $row['track_id'];
			$task = $row['name'];			
			$tasks[$level][$subject][] = $task;
			if (isset($possibleTasks[$subject])) {
				if (!in_array($task, $possibleTasks[$subject])) {
					$possibleTasks[$subject][] = $task;
				}
			} else {
				$possibleTasks[$subject][] = $task;
			}
		}
		
		echo "<pre>";
		//print_r($tasks);
		echo "</pre>";
		
		$parsha = $parshos[$_POST['parsha']];
		
		echo "School Type: " . $type . "<br />";
		echo "Parsha: " . $parsha . "<br />";
		echo "<table>";
		echo "<tr><th>Subject</th><th>Task</th>";
		for ($i = 6; $i < 15; $i++) {
			echo "<th>Level " . $i . "</th>";
		}
		echo "</tr>";
		foreach ($possibleTasks as $subject => $info) {
			foreach ($info as $task) {
				echo "<tr><td>" . $subjects[$subject] . "</td><td>" . $task . "</td>";
				for ($i = 6; $i < 15; $i++) {
					if (in_array($task, $tasks[$i][$subject], true)) {
						echo "<td>&#10004;</td>";
					} else {
						echo "<td>&nbsp;</td>";
					}	
				}
				echo "</tr>";
			}
		}
		echo "</tr>";
		echo "</table>"; 

	} else {
	?>
	<form action="allTasks.php" method="post">
		<select name="type">
			<option value='2'>Yeshiva Boys</option>
			<option value='3'>Yeshiva Girls</option>
			<option value='12'>Day School Boys</option>
			<option value='13'>Day School Girls</option>
		</select><br />
		
		<select name="parsha">
			<?
			foreach ($parshos as $date => $parsha) {
				echo "<option value='" . $date . "'>" . $parsha . "</option>";
			}
			?>
		</select><br />
		
		<input type="submit" name="submit" value="submit" />
	</form>
	<? } ?>
	</body>
</html>