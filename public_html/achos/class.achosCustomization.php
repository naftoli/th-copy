<? 
require_once 'class.parshos.php'; 
class AchosCustomization {
    private $studentID;
    private $labels;
    private $tasks;
    private $dates;
    private $taskPoints;
    private $enrolledTasks;
    private $mandatoryTasks;
    private $errors;
    private $userTasks;
	private $level;
    private $p;

    public function __construct() {
        $this->studentID = null;
        //$this->setLabels();
        $this->dates = array(
            'start' =>  2457321, 
            'end'   =>  2457656
        );
        $this->tasks = array();
        $this->taskPoints = array();
        $this->enrolledTasks = array();
        $this->mandatoryTasks = array();
        $this->errors = array();
        $this->userTasks = array();
		$this->level = 0;
        $this->p = new Parshos;
    }
    
    public function setStudent($id) {
        $this->studentID = $id;
		$this->setLevel();
    }
	
	private function setLevel() {
		$sql = "select level from user_tracks where user_id = " . $this->studentID;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->level = $row['level'];
	}
    
    private function setLabels() {
    	$labelIDs = array_keys($this->labels);
        $sql = "select * from labels where label_id in (" . implode(',', $labelIDs) . ")";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->labels[$row['label_id']] = $row['label_name'];
        }
    }
    
    public function getLabels() {
        return $this->labels;
    }
    
    public function setTasks() {
    	//get this weeks tasks/subtasks
    	$jd = unixtojd();
		$today = intval(date('w'));
		$start = $jd - $today;
        $sql = "select * from date_tasks dt 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                where dtm.start_date >= " . $start . " 
                and dtm.end_date <= " . $this->dates['end'];
        if (!is_null($this->studentID)) {
        	$sql .= " and dtm.level = " . $this->level;
            $sql .= " and (dt.created_by is null or dt.created_by = " . $this->studentID . ")";
			$sql .= " and deleted = 0";
		}
        $sql .= " group by dt.name, dt.label_id 
                order by dt.label_id, dt.label_ord";
        echo "<input type='hidden' name='tasks sql' value='" . $sql . "' />";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
        	$this->labels[$row['label_id']] = 1;
            $this->tasks[$row['label_id']][$row['date_task_id']] = $row;
            $this->taskPoints[$row['label_id']][$row['date_task_id']] = (int)$row['points'];
            $this->mandatoryTasks[$row['label_id']][$row['date_task_id']] = $row['mandatory_qty'];
            if (is_null($this->studentID)) {
                if ($row['created_by']) {
                    $sql2 = "select first, last from users where user_id = " . $row['created_by'];
                    $result2 = mysql_query($sql2);
                    $row2 = mysql_fetch_assoc($result2); 
                    $this->userTasks[$row['label_id']][$row['date_task_id']] = $row2['first'] . ' ' . $row2['last'];
                }
            }
        }
		//set labels
		$this->setLabels();
        
		//sort tasks to have tasks with subtasks
		//$this->sortTasks();
    }
    
    public function getTasks() {
        return $this->tasks; 
    }
    
    public function getTaskPoints() {
        return $this->taskPoints;
    }
    
    public function getMandatoryTasks() {
        return $this->mandatoryTasks;
    } 
    
    public function getUserTasks() {
        return $this->userTasks;
    }
	
	public function getTasksForLabel($label) {
		$tasks = array();
		$sql = "select dt.name from date_tasks dt 
				join date_tasks_missions dtm using (date_tasks_mission_id) 
                where dtm.start_date >= " . $this->dates['start'] . " 
                and dtm.end_date <= " . $this->dates['end'] . " 
                and dtm.level = " . $this->level . " 
				and dt.label_id = " . $label . " 
				and (dt.created_by is null or dt.created_by = " . $this->studentID . ") 
				and dt.deleted = 0 
				group by dt.name";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$tasks[] = $row['name'];
		}
		return $tasks;
	}
    
    public function setEnrolledTasks() {
        $enrolled = array();
        $sql = "select * from user_tasks ut 
                join date_tasks dt on (dt.date_task_id = ut.task_id) 
                where user_id = " . $this->studentID . " 
                and (dt.created_by is null or dt.created_by = " . $this->studentID . ") 
                group by label_id, name";
        //echo $sql;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->enrolledTasks[$row['label_id']][$row['name']][] = $row['date_task_id'];
        }
		/*
        foreach ($this->tasks[0] as $label => $tasks) {
            if (key_exists($label, $enrolled)) { 
                foreach ($tasks as $task) {
                    if (key_exists($task, $enrolled[$label])) {
                        $this->enrolledTasks[$label][$task] = 1;
                    } else {
                        $this->enrolledTasks[$label][$task] = 0;
                    }
                }
            }
        } 
		 * 
		 */       
    }
    
    public function getEnrolledTasks() {
        return $this->enrolledTasks;
    }
    
    public function setDates($start, $end) {
        $this->dates['start'] = $start;
        $this->dates['end'] = $end;
    }
    
    public function addTasksToUser(array $tasks) {
        //add tasks by getting task ids and setting default on for user
        $ids = array();
        foreach ($tasks as $label => $tasks) {
            foreach ($tasks as $task) {
                $task .= ".";
                $sql = "select date_task_id from date_tasks dt 
                        join date_tasks_missions dtm using (date_tasks_mission_id) 
                        where dt.label_id = $label 
                        and dt.name = \"" . mysql_real_escape_string($task) . "\" 
                        and dtm.start_date >= " . $this->dates['start'] . " 
                        and dtm.end_date <= " . $this->dates['end'] . " 
                        and dtm.level = " . $this->level;
                //echo $sql;
                //echo "<input type='hidden' name='addSql' value='" . $sql . "' />";
                $result = mysql_query($sql);
                while ($row = mysql_fetch_assoc($result)) {
                    $ids[] = $row['date_task_id'];
                }
            }
        }
        require_once 'class.defaults.php';
        $d = new Defaults($this->studentID);
        foreach ($ids as $id) {
            $d->addOn($id, 'task');
        }
    }
    
    public function createNewTask( array $tasks ) {
    	$missionStart = 49;
		$missionEnd = 95;
		
		$points = 1;
		$default = 0;
		$mand = 0;
		$opt = 1;
		
		mysql_query("set autocommit=0");
		mysql_query("begin");
		
		$success = true;
		$defaultIDs = array();
		
		do {
			$id = 0;
			foreach ($tasks as $task) {
				$label = $task['label'];
				switch ($label) {
					case 7:
					case 8:
					case 9:
					case 10:
					case 11:
					case 12:
						$daily = 1;
						$needed = 6;
						break;
					case 13:
					case 14:
					case 15:
						$daily = 0;
						$needed = 1;
						break;
				}		
				$sql = "insert into date_tasks 
						set date_tasks_mission_id = $missionStart, 
						name = \"" . $task['task'] . "\", 
						mandatory_qty = $mand, 
						optional_qty = $opt, 
						label_id = " . $label . ", 
						points = " . $points . ", 
						daily_task = " . $daily . ", 
						needed = $needed, 
						default_on = $default, 
						master_task_id = " . $id . ", 
						created_by = " . $task['created_by'];
				if (mysql_query($sql)) {
					$id = mysql_insert_id();
					$defaultIDs[] = $id;
				} else {
					$success = false;
					break;
				}
			}
		} while ($missionStart++ < $missionEnd); 
		
		if ($success) {
			mysql_query("commit");
			mysql_query("set autocommit=1");
			
			require_once 'class.defaults.php';
	        $d = new Defaults($this->studentID);
	        foreach ($defaultIDs as $id) {
	            $d->addOn($id, 'task');
	        }
			
			return true;
		} else {
			mysql_query("rollback");
			mysql_query("set autocommit=1");
			return false;
		}
    }
    
    public function getErrors() {
        return $this->errors;
    }
    /*
    public function deleteTask( $task, $label, $is_task = true ) {
    	//find starting mission based on today's date
		$jd = unixtojd();
		$today = intval(date('w'));
		$start = $jd - $today;
		
		if ($is_task) {
			$masterTasks = array();
	    	$sql = "select dt.date_task_id  
	    			from date_tasks dt 
	    			join date_tasks_missions dtm using (date_tasks_mission_id) 
	    			where dt.name = \"" . mysql_real_escape_string( $task ) . "\" 
	    			and dt.label_id = " . mysql_real_escape_string( $label ) . " 
					and dtm.start_date >= " . $start . " 
					and dtm.end_date <= " . $this->dates['end'] . " 
					and dtm.level = " . $this->level . " 
					and created_by = " . $this->studentID;
			$result = mysql_query( $sql );
			while ($row = mysql_fetch_assoc( $result )) {
				$masterTasks[$row['date_task_id']] = 1;
			}
			
			$taskIDs = array_keys( $masterTasks );
			//update subtasks
			$sql = "update date_tasks  
					set deleted = 1 
					where date_task_id in (" . implode(',', $taskIDs) . ") 
					or master_task_id in (" . implode(',', $taskIDs) . ")";
			return mysql_query( $sql );						
		} else {
			$sql = "update date_tasks dt 
					join date_tasks_missions dtm using (date_tasks_mission_id) 
					set dt.deleted = 1 
					where dt.name = \"" . mysql_real_escape_string( $task ) . "\" 
					and dt.label_id = " . mysql_real_escape_string( $label ) . " 
					and dtm.start_date >= " . $start . " 
					and dtm.end_date <= " . $this->dates['end'] . " 
					and dtm.level = " . $this->level . " 
					and created_by = " . $this->studentID;
			return mysql_query( $sql );
		}
    }
	*/
	public function getTaskNames() {
		$tasks = array();
		$sql = "select dt.*, u.first, u.last 
				from date_tasks dt 
				join date_tasks_missions dtm using (date_tasks_mission_id) 
				join users u on (dt.created_by = u.user_id) 
				where dtm.start_date >= " . $this->dates['start'] . " 
				group by u.user_id";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$tasks[] = $row;
		}		
		return $tasks;
	} 
	
	public function sortTasks() {
		$tasks = array();
		$subtasks = array();
		
		//echo "<pre>"; print_r($this->tasks); echo "</pre>"; exit;
		
		foreach ($this->tasks as $label => $info) {
			foreach ($info as $id => $task) {
				if ($subtask = $task['master_task_id']) {
					$subtasks[$subtask][] = $task;
				} else {
					$tasks[$label][$id] = $task;
				}
			}
		}
		
		$this->tasks = array($tasks, $subtasks);
	} 
	
	public function getTaskIDs(array $ids) {
		$allTasks = array();
		
		$tasks = array();
		$sql = "select * from date_tasks where date_task_id in (" . implode(',', $ids) . ")";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$tasks[] = $row;
		}
		
		foreach ($tasks as $task) {
			$sql = "select dt.date_task_id 
					from date_tasks dt 
					join date_tasks_missions dtm 
					using (date_tasks_mission_id) 
					where dtm.start_date >= " . $this->dates['start'] . " 
					and dtm.end_date <= " . $this->dates['end'] . " 
					and dtm.level = " . $this->level . " 
					and dt.name = \"" . $task['name'] . "\"";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$allTasks[] = $row['date_task_id'];
			}
		}
		
		return $allTasks;
	}
	
	public function createNewUserTask( array $task ) {
		//find starting mission based on today's date
		$jd = unixtojd();
		$today = intval(date('w'));
		$start = $jd - $today;
		$end = 2457656; //ki tavo 5776
		
		/*
		//check if we are creating a new task or the task exists and we are just creating the subtask
		$masters = array();
		$sql = "select dt.date_task_id, dt.date_tasks_mission_id from date_tasks dt 
				join date_tasks_missions dtm using (date_tasks_mission_id)
				where dt.name = '" . mysql_real_escape_string( $task['task'] ) . "' 
				and dt.created_by = " . $this->studentID . " 
				and dtm.start_date >= " . $start;
		$result = mysql_query( $sql );
		if (mysql_num_rows( $result ) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$masters[$row['date_tasks_mission_id']] = $row['date_task_id'];
			}
		} else {
			//find mission start number
			$sql = "select date_tasks_mission_id from date_tasks_missions where start_date = " . $start;
			$result = mysql_query( $sql );
			$row = mysql_fetch_assoc( $result );
			$missionStart = $row['date_tasks_mission_id'];
			$missionEnd = 95;
		}
		*/
		$points = 1;
		$default = 0;
		$mand = 0;
		$opt = 1;
		
		$label = $task['label'];
		switch ($label) {
			case 7:
			case 8:
			case 9:
			case 10:
			case 11:
			case 12:
				$daily = 1;
				$needed = 7;
				break;
			case 13:
			case 14:
			case 15:
				$daily = 0;
				$needed = 1;
				break;
		}
		
		//find ord and label_ord		
		$sql = "select ord, label_ord from date_tasks where label_id = " . $label . " order by label_ord desc limit 1";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$ord = $row['ord'];
		$ord++;
		$label_ord = $row['label_ord'];
		$label_ord++;
		
		mysql_query("set autocommit=0");
		mysql_query("begin");
		
		$success = true;
		$defaultIDs = array();		
		
		/*
		if (!empty( $masters )) {
			foreach ( $masters as $missionID => $masterTaskID ) {
				$sql = "insert into date_tasks 
						set date_tasks_mission_id = " . $missionID . ", 
						name = \"" . mysql_real_escape_string($task['subtask']) . "\", 
						mandatory_qty = $mand, 
						optional_qty = $opt, 
						label_id = " . $label . ", 
						daily_task = " . $daily . ", 
						needed = $needed, 
						default_on = $default, 
						label_ord = " . $label_ord++ . ", 
						master_task_id = " . $masterTaskID . ", 
						points = 1, 
						created_by = " . $task['created_by'];
				if (mysql_query($sql)) {
					$id = mysql_insert_id();
					$defaultIDs[] = $id;
				} else {
					$success = false;
					break;
				}
			}
		} else {
		 * 
		 */
			//we need to create task for each week
			do {
				//find out mission ID
				$sql = "select date_tasks_mission_id from date_tasks_missions 
						where start_date = " . $start . " 
						and end_date = " . ($start + 6) . " 
						and level = " . $this->level;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$missionID = $row['date_tasks_mission_id'];
				
				$sql = "insert into date_tasks 
						set date_tasks_mission_id = " . $missionID . ", 
						name = \"" . mysql_real_escape_string($task['task']) . "\", 
						description = \"" . mysql_real_escape_string($task['desc']) . "\", 
						mandatory_qty = $mand, 
						optional_qty = $opt, 
						label_id = " . $label . ", 
						points = $points, 
						daily_task = " . $daily . ", 
						needed = $needed, 
						default_on = $default, 
						ord = " . $ord++ . ", 
						label_ord = " . $label_ord++ . ", 
						created_by = " . $task['created_by'];
				//echo $sql;
				if (mysql_query($sql)) {
					$id = mysql_insert_id();
					$defaultIDs[] = $id;
					/*
					$sql = "insert into date_tasks 
							set date_tasks_mission_id = $missionStart, 
							name = \"" . mysql_real_escape_string($task['subtask']) . "\", 
							mandatory_qty = $mand, 
							optional_qty = $opt, 
							label_id = " . $label . ", 
							daily_task = " . $daily . ", 
							needed = $needed, 
							default_on = $default, 
							label_ord = " . $label_ord++ . ", 
							master_task_id = " . $id . ", 
							points = 1, 
							created_by = " . $task['created_by'];
					if (mysql_query($sql)) {
						$id = mysql_insert_id();
						$defaultIDs[] = $id;
					} else {
						$success = false;
						break;
					}
					 * 
					 */
				} else {
					$success = false;
					break;
				}
				$start += 7;
			} while ($start < $end);
		//} 
			
		if ($success) {
			mysql_query("commit");
			mysql_query("set autocommit=1");
			
			require_once 'class.defaults.php';
	        $d = new Defaults($this->studentID);
	        foreach ($defaultIDs as $id) {
	            $d->addOn($id, 'task');
	        }
			
			return true;
		} else {
			mysql_query("rollback");
			mysql_query("set autocommit=1");
			return false;
		}
	}
}
?>
