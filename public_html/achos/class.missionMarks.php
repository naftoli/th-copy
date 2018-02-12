<?
class MissionMarks {
    private $userID;
    private $start;
    private $end;
    private $added;
	private $debugInfo;
	private $points;
	private $date_tasks_mission_id;
	private $mission_name;
    
    public function __construct($user_id, $date_task_id = 0, $start = 0, $end = 0) {
        $this->userID = $user_id;
		if ($date_task_id) {
			$sql = "select start_date, end_date from date_tasks_missions dtm 
					join date_tasks dt using (date_tasks_mission_id) 
					where dt.date_task_id = " . $date_task_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
	        $this->start = $row['start_date'];
	        $this->end = $row['end_date'];
		} else {
			$this->start = $start;
			$this->end = $end;
		}
        $this->added = false;
    }
    
    public function checkMissionCompletion() {
		/*
        $daily = $user->user_tracks[0]->daily_tasks;
        foreach ($daily_tasks as $task) {
        	if ($task->default_on == 0 && !$d->isOn($task->date_task_id, 'task')) continue;
            //if ($e->isException($task->date_task_id, $this->userID)) continue;
            if ($task->mandatory_qty) {
                $daily[] = $task;
            }
        }
        
        $weekly = array();
        $weekly_tasks = $user->user_tracks[0]->weekly_tasks;
        foreach ($weekly_tasks as $task) {
        	if ($task->default_on == 0 && !$d->isOn($task->date_task_id, 'task')) continue;
            //if ($e->isException($task->date_task_id, $this->userID)) continue;
            if ($task->mandatory_qty) {
                $weekly[] = $task;
            }
        }
        
        $mandatory = array();        
        foreach ($daily as $task) {
            foreach ($task->date_task_marks as $mark) {
                if ($mark->marked) {
                    if (isset($mandatory[$task->date_task_id]))
                        $mandatory[$task->date_task_id]++;
                    else 
                        $mandatory[$task->date_task_id] = 1;
                }
            }
        }
        
        foreach ($weekly as $task) {
            if ($task->date_task_mark->marked) {
                if ($task->quantity) {
                    if ($task->quantity <= $task->date_task_mark->done_qty) {
                        $mandatory[$task->date_task_id] = 1;
                    }
                } else {
                    $mandatory[$task->date_task_id] = 1;
                }
            }
        }
		/*
		if ($this->userID == 42) {
			echo "<pre>";
			print_r($daily);
			print_r($weekly);
			echo "-----------------------------------------------<br />";
			print_r($mandatory);
			echo "</pre>";
		}
        
        $missionCompleted = true;
        foreach ($daily as $task) {
            if (!isset($mandatory[$task->date_task_id]) || $mandatory[$task->date_task_id] < 6) {
                $missionCompleted = false;
                break;
            }
        }
        
        foreach ($weekly as $task) {
            if (!isset($mandatory[$task->date_task_id])) {
                $missionCompleted = false;
                break;
            }
        }
		
		//if ($this->userID == 42) {
			//echo "Mission Completed: " . $missionCompleted;
			//exit;
		//}
		 * 
		 */
		 
        if ($this->isCompleted()) {
        	//calculate total points earned for mission
        	$totalPoints = 0;
			foreach ($this->points as $amount) {
				$totalPoints += $amount;
			}
            //update mission marks table
            $sql = "insert ignore into date_tasks_mission_marks values($this->userID, $this->date_tasks_mission_id, 1, 1.0, '" . 
                    mysql_real_escape_string($this->mission_name) . "', " . unixtojd() . ", 0, 0, " . $totalPoints . ")";
            if (mysql_query($sql)) {
                $this->added = true;
            }
        } else {
            //check if mission mark needs to be deleted
            $sql = "select * from date_tasks_mission_marks where user_id = " . $this->userID . " and date_tasks_mission_id = " . $this->date_tasks_mission_id;
            //echo $sql;
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0) {
                $sql = "delete from date_tasks_mission_marks where user_id = " . $this->userID . " and date_tasks_mission_id = " . $this->date_tasks_mission_id;
                mysql_query($sql);
            }
        }
    }

	public function isCompleted() {
		require_once("classes/user.php");
        require_once("classes/user_track.php");
        require_once("classes/school_class.php");
        require_once("class.taskExceptions.php");
        require_once("classes/date_tasks_mission.php");
        require_once("classes/daily_task.php");
        require_once("classes/weekly_task.php");
        require_once("classes/shabbos_task.php");
        require_once("classes/no_label_task.php");
        require_once("classes/task.php");
        require_once("classes/date_tasks_mark.php");
		require_once("class.defaults.php");
		
		//$e = new TaskExceptions();
        $d = new Defaults($this->userID);
		
        $sql = "SELECT * FROM users WHERE user_id = " . $this->userID;
        $query = mysql_query($sql);
        $row = mysql_fetch_assoc($query);
        $user = new user($row);
        $user->get_user_tracks(-1, $this->start, $this->end);
		
		$this->date_tasks_mission_id = $user->user_tracks[0]->date_tasks_missions[0]->date_tasks_mission_id;
        $this->mission_name = $user->user_tracks[0]->date_tasks_missions[0]->mission_name;
		
		/*****************************
		 * 	the following is the rules for the tasks:
		 * 	for tab 7 (My Morning) 5 points needed
		 *	for tab 8 (My Tefilah) 8 points BUT must include basic shachris as one
		 *	for tab 9 (My Self Respect) 6 points BUT all three self respect tasks should be on everyones checklist
		 *	for tab 10 (My Shiurim) 6 points- BUT girls must choose at least 1 of the following as mandatory standard:
		 *		1) Chumash basic/ possukim
		 *		2) Tehillim
		 *		3) Tanya (+ Hayom yom and sefer Hamitzvos)
		 * 	for tab 11 (My Sensitivity) 4 points BUT MUST CHOOSE ONE
		 *  for tab 12 - My Evening - 4 points
		 *  for tab 13 - Weekly - 5 points
		 *  for tab 14 - Shabbos - 6 points
		 *  for tab 15 - Monthly - 5 points
		 ******************************/
		 
		$allTasks = array_merge($user->user_tracks[0]->daily_tasks, $user->user_tracks[0]->weekly_tasks);
		//echo "<pre>"; print_r($allTasks); echo "</pre>"; exit;
		
		$tasks = array();
		$subtasks = array();
		foreach ($allTasks as $task) {
			//if task is NOT mandatory but IS default on, it's only for showing on the mission sheets, 
			//therefore check if user has the default on for this task and remove if not
			if ($task->default_on && !$task->mandatory_qty && !$d->isOn($task->date_task_id, 'task')) continue;
			if ($task->master_task_id) {
				$subtasks[$task->master_task_id][] = $task;
			} else {
				$tasks[$task->label_id][] = $task;
			}
		}
		
		if (1 == 2) {
			print_r($tasks);
			print_r($subtasks);
			exit;
		}
		
		$missionCompleted = true;
		//for each task in each label check that all subtasks chosen were done and calculate points
		foreach ($tasks as $label => $info) {
			foreach ($info as $task) {
				foreach ($subtasks[$task->date_task_id] as $subtask) {
					//check if task is daily or not
					if (isset($subtask->date_task_marks)) {
						foreach ($subtask->date_task_marks as $mark) {
							if (!$mark->marked) {
								$missionCompleted = false;
								break 4;
							}
						}
					} else if (isset($subtask->date_task_mark)) {
						if (!$subtask->date_task_mark->marked) {
							$missionCompleted = false;
							break 3;
						}
					} else {
						$missionCompleted = false;
						$this->debugInfo = 'No marking information available.';
						return false;
					}
				}
				//if we get here then all subtasks and all days were marked
				//we need to calculate points - for tasks that have qty we need to figure that out as well
				//for shabbos mevorchim the tasks does NOT have points only the subtasks DO
				if ($task->points) {
					$amount = $task->points;
				} else {
					$amount = 0;
					foreach ($subtasks[$task->date_task_id] as $subtask) {
						if (isset($subtask->date_task_mark)) {
							//echo $subtask->points . "<br />";
							$amount += $subtask->points;
						} 
					} 
				}
				/*
				if ($task->quantity) {
					//get qty entered from subtask
					$amount *= $subtasks[$task->date_task_id][0]->date_task_mark->done_qty;
				}
				 * 
				 */
				if (isset($this->points[$label])) {
					$this->points[$label] += $amount;
				} else {
					$this->points[$label] = $amount;
				}
			}
		}

		if (!$missionCompleted) {
			//echo "<pre>"; print_r($task); print_r($subtask); echo "</pre>";
			$this->debugInfo = "Not all tasks were completed.";
			return false;
		} else {
			//check that all labels come up
			$labelsShowing = array();		
			foreach ($this->points as $label => $amount) {
				$labelsShowing[] = $label;
				switch ($label) {
					case 11:
					case 12:
						$needed = 4;
						break;
					case 7:
					case 13:
					case 15:
						$needed = 5;
						break;
					case 9:
					case 10:
					case 14:
						$needed = 6;
						break;
					case 8:
						$needed = 8;
						break;
				}
				if ($amount < $needed) {
					$missionCompleted = false;
					break;
				}
			}
			sort($labelsShowing);
			if ($labelsShowing != array(7,8,9,10,11,12,13,14,15)) {
				$this->debugInfo = "Not all tabs have a task chosen.";
				return false;
			}
			
			if (!$missionCompleted) {
				$this->debugInfo = "Not all points per tab were accomplished.";
				return false;
			}
		}
		
		return true;
	}

    public function added() {
        return $this->added;
    }
	
	public function getDebugInfo() {
		echo "<pre>";
		print_r($this->points);
		echo "</pre>";
		return $this->debugInfo;
	}
}
?>
