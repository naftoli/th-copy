<?
class AchosPoints {
	private $student;
	private $d;
	private $debug;
	
	public function __construct( $userID ) {
		$this->student = $userID;
		require_once 'class.defaults.php';
		$this->d = new Defaults( $userID );
		$this->debug = false;
	}
	
	public function setDebug( $bool ) {
		$this->debug = $bool;
	}
	
	public function calcDailyPoints() {
		$date = unixtojd();
		return $this->calcPoints( $date, $date );
	}
	
	public function calcWeeklyPoints() {
		$today = unixtojd();
		$day = date('w');
		$start = $today - $day;
		$end = $start + 6;
		return $this->calcPoints( $start, $end );
	}
	
	public function calcPoints( $start = 0, $end = 0 ) {
		$tasks = array();
		$sqlP = "select count(dt.date_task_id) as numDone, dt.* from date_tasks_marks 
				join date_tasks dt using (date_task_id) 
				join date_tasks_missions using (date_tasks_mission_id) 
				where user_id = " . $this->student . "   
				and date_tasks_mission_id > 95";
		if ($start && $end) {
			$sqlP .= " and mark_date >= $start and mark_date <= $end";
		} else {
			$sqlP .= " and mark_date > 2457667";
		}
		$sqlP .= " group by dt.date_task_id";
		if ( $this->debug ) echo $sqlP . "<br />";
		$resP = mysql_query($sqlP);
		while ($row = mysql_fetch_assoc($resP)) {
			$tasks[] = $row;
		}
		
		//all marks are only subtasks
		//if subtask has point value add it
		//otherwise we need to find parent task point value and add it
		$total = 0;
		//$masterTask = 0;
		foreach ($tasks as $task) {
			if ($task['points']) {
				$total += ($task['points'] * $task['numDone']);
			} else {
				/*
				//only give points if ALL subtasks chosen have been marked
				$award = false;
				$sql = "select * from date_tasks dt 
		                where master_task_id = " . $task['master_task_id'] . " 
		                and (created_by is null or created_by = " . $this->student . ")";
		        if ( $this->debug ) echo $sql . "<br />";
				$result = mysql_query($sql);
				$num = mysql_num_rows($result);
				if ( $this->debug ) echo $num . "<br />";
				
				if ($num > 1) {
					$taskIDs = array();
					while ($row = mysql_fetch_assoc($result)) {
						if (!$row['default_on'] && !$row['mandatory_qty'] && !$this->d->isOn($row['date_task_id'], 'task')) continue;
						$taskIDs[] = $row['date_task_id'];
					}
					if (!empty($taskIDs)) {
						$numTasks = count( $taskIDs );
						$sql = "select count(date_task_id) as marked from date_tasks_marks where date_task_id in (" . implode(',', $taskIDs) . ") 
								and user_id = " . $this->student;
						if ( $this->debug ) echo $sql . "<br />";
						$result = mysql_query($sql);
						$row = mysql_fetch_assoc($result);
						$marked = $row['marked'];
						$amount = floor($marked / $numTasks);
						if ( $this->debug ) {
							echo "Num marked: " . $marked . "<br />";
							echo "Amount: " . $amount . "<br />";
						}
						if ($amount) {
							$award = true;
						}
					}
				} else {
					$award = true;
				}
				if ($award) {
					if ($masterTask != $task['master_task_id']) {
						$masterTask = $task['master_task_id'];
						$sqlP = "select points from date_tasks where date_task_id = " . $task['master_task_id'];
						$resP = mysql_query( $sqlP );
						$rowP = mysql_fetch_assoc( $resP );
						$points = $rowP['points'];
						if ( $num > 1 ) {
							$total += ( $points * $amount );
						} else {
							$total += ( $points * $task['numDone'] );
						}
						if ( $this->debug ) echo "Total: " . $total . "<br />";
					}
				}
				 * 
				 */
			}
		}
		
		return $total;
	}
}
?>