<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<?php
		if (isset($_POST['submit'])) {
			if (file_exists($_FILES['tasks']['tmp_name'])) {
				require_once 'db.php';
				require_once 'PHPExcel/IOFactory.php';
				
				//load spreadsheet
				$file = $_FILES['tasks']['tmp_name'];
	            $objPHPExcel = PHPExcel_IOFactory::load( $file );
				$objWorksheet = $objPHPExcel->getActiveSheet();
				
				$columns = array('labelID', 'task', 'description', 'mandatory', 'points', 'daily', 'qty', 'ord');
				$tasks = array();
				$subtasks = array();
				
				$k = 0;
				$first = true;
				foreach ($objWorksheet->getRowIterator() as $row) {
					if ( $first ) {
						$first = false;
						continue;
					}
                	$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false);
					$i = 0;
					foreach($cellIterator as $cell) {
						if ($i == 8) break; 
                    	$val = mysql_real_escape_string(trim($cell->getValue()));
						$tasks[$k][$columns[$i++]] = $val;
					}
					$k++;
				}
				
				$allTasks = array();
				foreach ($tasks as $task) {
					if ($task['labelID'] == 17) {
						$subjects = array(2,3,4);
						foreach ($subjects as $subject) {
							$allTasks[$subject][] = $task;
						}
					} else {
						// all other tasks are only for FC
						$allTasks[3][] = $task;
					}
				}
				
				//echo "<pre>"; print_r($allTasks); echo "</pre>"; exit;
				/*
				foreach ($tasks as $task) {
					if (!empty($task['subtaskID'])) {
						$subtasks[$task['subtaskID']][] = $task;
					}
				}
				*/
				//$ord = 1;			
				function addToSql($task, $id, $taskID = 0) {
					global $ord;
					
					if ($task['mandatory']) {
						$mand = 1;
						$opt = 0;
					} else {
						$mand = 0;
						$opt = 1;	
					}
					
					if ($task['daily']) {
						$needed = 1;
					} else {
						$needed = 0;
					}
					
					$default = $mand;
					
					$sql = "insert into date_tasks 
							set date_tasks_mission_id = $id, 
							name = \"" . $task['task'] . "\", 
							description = \"" . $task['description'] . "\", 
							mandatory_qty = $mand, 
							optional_qty = $opt, 
							label_id = " . $task['labelID'] . ", 
							points = " . $task['points'] . ", 
							daily_task = " . $task['daily'] . ", 
							needed = " . $needed . ", 
							default_on = 1, 
							ord = " . $task['ord'];
					
					if ($task['qty']) {
						$sql .= ", quantity = " . $task['qty'];
					}
					/*
					if ($taskID) {
						$sql .= ", master_task_id = " . $taskID;
					}
					*/
					return $sql;
				}
				
				$created['tasks'] = 0;
				//$created['subtasks'] = 0;
				
				//$start = 2458007; // nitzavim - vayelech 5777/5778
				//$end = 2458013; // try for one week
				$start = 2458014; // haazinu 5778
				$end = 2458363; // ki tavo 5778
				$qrys = array();
				do {
				//foreach ($missionIDs as $missionID) {	
					foreach ($allTasks as $subject => $info) {
						//find out mission ID
						$sql = "select date_tasks_mission_id from date_tasks_missions 
								where start_date = " . $start . " 
								and end_date = " . ($start + 6) . "
								and level = 1 
								and subject_id = " . $subject;
						$result = mysql_query($sql);
						$row = mysql_fetch_assoc($result);
						$missionID = $row['date_tasks_mission_id'];
						
						foreach ($info as $task) {
							$qrys[] = addToSql($task, $missionID);
						}
						/*
						if (empty($task['subtaskID'])) {							
							$taskID = mysql_insert_id();							
							$tasksSql[] = addToSql($task, $missionID);
							$taskID = $taskNum++;
							if (array_key_exists($task['taskID'], $subtasks)) {
								foreach ($subtasks[$task['taskID']] as $subtask) {
									$sql = addToSql($subtask, $missionID, $taskID);
									mysql_query($sql) or die(mysql_error());
									$created['subtasks']++;
									//$tasksSql[] = addToSql($subtask, $missionID, $taskID);
								}
							}
						}
						 * 
						 */
					}
					$start += 7;
				//}
				} while ($start < $end);
				
				// echo "<pre>"; print_r($qrys); echo "</pre>"; exit;
				
				mysql_query("set autocommit = 0");
				mysql_query("begin");
				
				foreach ($qrys as $qry) {
					//echo $qry;
					//continue;
					if (!mysql_query($qry)) {
						echo mysql_error();
						mysql_query("rollback");
						break;
					}
					$created['tasks']++;
				}
				mysql_query("commit");
				mysql_query("set autocommit=1");
				
				echo "<pre>"; print_r($created); echo "</pre>";
				//echo "<pre>"; print_r($tasksSql); echo "</pre>";
			}
		} else {
		?>
		<form action="createAchosTasks.php" method="post" enctype="multipart/form-data">
			<input type="file" name="tasks" /><br />
			<input type="submit" name="submit" value="submit" />
		</form>
		<? } ?>
	</body>
</html>