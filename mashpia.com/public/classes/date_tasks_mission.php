<?
class date_tasks_mission {
	public $date_tasks_mission_id;
	public $school_type_id;
	public $subject_id;
	public $level;
	public $track_id;
	public $mission_name;
	public $mission_number;
	public $mission_group;
	public $mission_description;
	public $mission_value;
	public $start_date;
	public $end_date;
	
	public $subject_name;
	public $subject_image_id;
	
	public $mission_completed;
	public $future_mission;
	
	public $week_string;
	public $tasks = array();
	
	public $e;
	public $allowPersonalization;
	
	function __construct($row, $tasks = array(), $allowPersonalization = true) {
		$this->date_tasks_mission_id = $row["date_tasks_mission_id"];
		$this->school_type_id = $row["school_type_id"];
		$this->subject_id = $row["subject_id"];
		$this->level = $row["level"];
		$this->track_id = $row["track_id"];
		$this->mission_name = $row["mission_name"];
		$this->mission_number = $row["mission_number"];
		$this->mission_group = $row["mission_group"];
		$this->mission_description = $row["mission_description"];
		$this->mission_value = $row["mission_value"];
		$this->start_date = $row["start_date"];
		$this->end_date = $row["end_date"];
		$this->week_string = "";
		$this->e = new TaskExceptions();
		$this->tasks = $tasks;
		$this->allowPersonalization = $allowPersonalization;
	}
	
	function set_subject($subject_name, $subject_image_id)
	{
		$this->subject_name = $subject_name;
		$this->subject_image_id = $subject_image_id;
	}
		
	function get_daily_tasks($start_date, $end_date, $user_id, $subject_id, $subject_name, $track_id, $level, $subject_image_id) {
		
		//if ($user_id == 52048 && $this->date_tasks_mission_id == 1500663){
		//	echo "<input type='hidden' name='get_daily_tasks - input' value='".$start_date.",".$end_date.",".$subject_id.",".$subject_name.",".$track_id.",".$level.","."'/>";
		//}
		
		$daily_tasks = array();

		$sql  = "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.* ";
		$sql .= "FROM date_tasks AS dt ";
		$sql .= "JOIN labels AS l USING (label_id) ";
		$sql .= "JOIN frequencies AS f USING (frequency_id) ";
		$sql .= "JOIN frequency_periods AS fp USING (frequency_period_id) ";
		$sql .= "WHERE dt.date_tasks_mission_id=" . $this->date_tasks_mission_id;
		$sql .= " AND f.frequency_name = \"Daily\" ";
		if ($start_date >= 2457641) $sql .= "and dt.mission_marking = 1 ";
		//$sql .= "ORDER BY f.frequency_id, fp.frequency_period_id, dt.sequence_number";
		//$sql .= "ORDER BY dt.ord, dt.label_ord";
		$sql .= "ORDER BY dt.label_ord, dt.grid_id";
		//echo "<input type='hidden' name='SQL TWO' value='" . $sql . "'>\n";
		//if ($subject_id == 100 && $user_id == 55248) echo $sql . "<br />";

		$query = mysql_query($sql);
        $d = new Defaults($user_id);
		while ($row = mysql_fetch_assoc($query)) {
			
			//if (($user_id == 51364 ) && $row['name'] == "Test"){
			//	echo "<input type='hidden' name='get_daily_tasks - input' value=\"";
			//	print_r($row);
			//	echo "\"/>";
			//}
			
			if ($this->allowPersonalization) {
				if ( $row['default_on'] == 0 && !$d->isOn($row['date_task_id'], 'task')) continue;
				if ( $this->e->isException( $row['date_task_id'], $user_id ) ) continue;
			} else {
				if ( $row['default_on'] == 0 ) continue;
			}
			
            if ( $row['name'] == 'I said קריאת שמע before the זמן.' ) continue;
            
			if ( !empty($this->tasks )) {
				if (!in_array($row['name'], $this->tasks)) continue;
			}
			
			$daily_task = new daily_task($row, $subject_id, $row['name']);
			$daily_task->set_subject_image_id($subject_image_id);
			$daily_task->set_dates($start_date, $end_date);
			$daily_task->set_date_tasks_marks($user_id, $start_date, $end_date);
			array_push($daily_tasks, $daily_task);
		}
        //echo "<pre>" . print_r($daily_tasks) . "</pre>";
		return $daily_tasks;
	}
	
	function get_weekly_tasks($start_date, $end_date, $user_id, $subject_id, $subject_name, $track_id, $level, $subject_image_id) {	
		$weekly_tasks = array();
		
		$sql = "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.* ";
		$sql = $sql . "FROM date_tasks AS dt ";
		$sql = $sql . "JOIN labels AS l USING (label_id) ";
		$sql = $sql . "JOIN frequencies AS f USING (frequency_id) ";
		$sql = $sql . "JOIN frequency_periods AS fp USING (frequency_period_id) ";
		$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $this->date_tasks_mission_id . " ";
		$sql = $sql . "AND f.frequency_name = \"Weekly\" ";
		if ($start_date >= 2457641) $sql .= "and dt.mission_marking = 1 ";
		//$sql = $sql . "ORDER BY dt.ord, dt.label_ord";
		$sql = $sql . "ORDER BY dt.label_ord, dt.grid_id";
        //echo "<input type='hidden' name='weekly tasks' value='$sql' />";
		$query = mysql_query($sql);
        $d = new Defaults($user_id);
		while ($row = mysql_fetch_assoc($query)) {
		    if ($this->allowPersonalization) {
				if ($row['default_on'] == 0 && !$d->isOn($row['date_task_id'], 'task')) continue;
				if ( $this->e->isException( $row['date_task_id'], $user_id ) ) continue;
			} else {
				if ( $row['default_on'] == 0 ) continue;
			}
			
			if (!empty($this->tasks)) {
				if (!in_array($row['name'], $this->tasks)) continue;
			}
			
			$weekly_task = new weekly_task($row);
			$weekly_task->set_subject_id($subject_id);
			$weekly_task->set_subject_image_id($subject_image_id);
			$weekly_task->set_date_task_mark($user_id, $start_date, $end_date);
			$weekly_task->set_mark_date($end_date);
			array_push($weekly_tasks, $weekly_task);
		}
		
		return $weekly_tasks;
	}
	
	function get_shabbos_tasks($start_date, $end_date, $user_id, $subject_id, $subject_name, $track_id, $level, $subject_image_id) {	
		$shabbos_tasks = array();
	
		$sql = "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.* ";
		$sql = $sql . "FROM date_tasks AS dt ";
		$sql = $sql . "JOIN labels AS l USING (label_id) ";
		$sql = $sql . "JOIN frequencies AS f USING (frequency_id) ";
		$sql = $sql . "JOIN frequency_periods AS fp USING (frequency_period_id) ";
		$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $this->date_tasks_mission_id . " ";
		$sql = $sql . "AND f.frequency_name = \"Shabbos\" ";
		if ($start_date >= 2457641) $sql .= "and dt.mission_marking = 1 ";
		//$sql = $sql . "ORDER BY dt.ord, dt.label_ord";
		$sql = $sql . "ORDER BY dt.label_ord, dt.grid_id";
		//echo "<input type='hidden' name='shabbos tasks' value='$sql'>";
			
		$query = mysql_query($sql);
        $d = new Defaults($user_id);
		while ($row = mysql_fetch_assoc($query)) {
		    if ($this->allowPersonalization) {
				if ($row['default_on'] == 0 && !$d->isOn($row['date_task_id'], 'task')) continue;
				if ( $this->e->isException( $row['date_task_id'], $user_id ) ) continue;
			} else {
				if ( $row['default_on'] == 0 ) continue;
			}
			
			if (!empty($this->tasks)) {
				if (!in_array($row['name'], $this->tasks)) continue;
			}
			
			$shabbos_task = new shabbos_task($row);
			$shabbos_task->set_mark_date($start_date, $end_date);
			$shabbos_task->set_subject_id($subject_id);
			$shabbos_task->set_subject_image_id($subject_image_id);
			$shabbos_task->set_date_task_mark($user_id, $start_date, $end_date);
			array_push($shabbos_tasks, $shabbos_task);
		}
			
		return $shabbos_tasks;
	}
	
	function get_no_label_tasks($start_date, $end_date, $mission_name, $mission_number, $user_id, $subject_name, $subject_image_id, $subject_id) {	
		$no_label_tasks = array();
		 
		$sql = "SELECT * ";
		$sql = $sql . "FROM date_tasks AS dt ";
		$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $this->date_tasks_mission_id . " ";
		$sql = $sql . "AND (dt.label_id IS NULL or dt.label_id = 0) ";
		if ($start_date >= 2457641) $sql .= "and dt.mission_marking = 1 ";
		$sql = $sql . "ORDER BY dt.grid_id, dt.ord, dt.date_task_id";
		//$sql = $sql . "ORDER BY dt.date_task_id";
        //if ($subject_id == 40 && $user_id == 15025) echo "<input type='hidden' name='No Label Sql' value='" . $sql . "' />";

		$query = mysql_query($sql);
        $d = new Defaults($user_id);		
		while ($row = mysql_fetch_assoc($query)) {
		    if ($this->allowPersonalization) {
				if ($row['default_on'] == 0 && !$d->isOn($row['date_task_id'], 'task')) continue;
				if ( $this->e->isException( $row['date_task_id'], $user_id ) ) continue;
			} else {
				if ( $row['default_on'] == 0 ) continue;
			}

			if (!empty($this->tasks)) {
				if (!in_array($row['name'], $this->tasks)) continue;
			}

			$no_label_task = new no_label_task($row);
			$no_label_task->set_dates($start_date, $end_date);
			$no_label_task->set_mark_date($end_date);
			$no_label_task->set_subject_name($subject_name);
			$no_label_task->set_subject_image_id($subject_image_id);
			$no_label_task->set_mission_name($mission_name);
			$no_label_task->set_mission_number($mission_number);
			$no_label_task->set_date_task_mark($user_id, $start_date, $end_date);
			$no_label_task->set_subject_id($subject_id);
			array_push($no_label_tasks, $no_label_task);
		}
		
		return $no_label_tasks;				
	}

	function set_week_string()
	{
		for ($day = $this->start_date; $day <= $this->end_date; $day++) 
		{
			$day_of_the_week = $day % 7;
			
			if ($day_of_the_week == 6)
				$this->week_string =  $this->week_string . "S";
			elseif ($day_of_the_week == 0)
				$this->week_string =  $this->week_string . "M";
			elseif ($day_of_the_week == 1)
				$this->week_string =  $this->week_string . "T";
			elseif ($day_of_the_week == 2)
				$this->week_string =  $this->week_string . "W";
			elseif ($day_of_the_week == 3)
				$this->week_string =  $this->week_string . "T";
			elseif ($day_of_the_week == 4)
				$this->week_string =  $this->week_string . "F";
			elseif ($day_of_the_week == 5)
				$this->week_string =  $this->week_string . "S";	
		}
	
	}
	
	function get_tasks()
	{
		$sql = "SELECT * FROM date_tasks WHERE date_tasks_mission_id=" . $this->date_tasks_mission_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$date_task = new date_task($row);
			array_push($this->tasks, $date_task);
		}
	}
	
	function set_mission_completed($user_id)
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM date_tasks AS dt ";
		//$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dt.date_task_id=dtm.date_task_id AND dtm.user_id=" . $user_id . ") ";
		$sql = $sql . "JOIN date_tasks_marks AS dtm ON (dt.date_task_id=dtm.date_task_id AND dtm.user_id=" . $user_id . ") ";
		$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $this->date_tasks_mission_id . " ";
		$sql = $sql . "AND dt.mandatory_qty=1 ";
		//$sql = $sql . "AND dtm.date_task_id IS NULL";

		//find number of entries and if it's equal or greater than needed then set mission completed to true
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);	
		if ($num_rows) {
			$row = mysql_fetch_assoc($query);
			$needed = $row['needed'];
			
			if ($num_rows < $needed)
				$this->mission_completed = false;
			else
				$this->mission_completed = true;
		}
		/* old code
		if ($num_rows == 0)
			$this->mission_completed = true;
		else
			$this->mission_completed = false;
		*/
	}
		
	function set_future_mission($user_id)
	{
		$this->tasks_completed_percentage = 0;
		
		$current_date = unixtojd();
		if ($this->start_date > $current_date)
			$this->future_mission = true;
		else
			$this->future_mission = false;
					
		if ($this->mission_completed == false && $this->future_mission == false)
		{
			$total_tasks = 0;
			$tasks_done = 0;
			
			$sql = "SELECT dt.date_task_id AS task, dtm.date_task_id AS done ";
			$sql = $sql . "FROM date_tasks AS dt ";
			$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dtm.date_task_id=dt.date_task_id AND dtm.user_id=" . $user_id . ") ";
			$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $this->date_tasks_mission_id . " ";
			$sql = $sql . "AND dt.mandatory_qty=1";
			$query = mysql_query($sql);
			while ($row = mysql_fetch_assoc($query))
			{
				$total_tasks++;
				if ($row['done'] > 0)
					$tasks_done++;
			}
			
			if ($tasks_done > 0)
				$this->tasks_completed_percentage = $total_tasks / $tasks_done;
			else
				$this->tasks_completed_percentage = 100;
		}
	}
} 
?>