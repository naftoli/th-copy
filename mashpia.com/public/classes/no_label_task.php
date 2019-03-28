<?
class no_label_task {
	public $date_task_id;
	public $label_name;
	public $short_name;
	public $task_name;
	public $mandatory_qty;
	public $quantity;
	public $points;
	public $sequence_number;
	public $focus_task;
	
	public $subject_name;
	public $subject_image_id;
	public $mission_name;
	public $mission_number;
	
	public $start_date;
	public $end_date;
	
	public $date_task_marks = array();
	
	public $subject_id;
	public $medium_pic;
	
	public $grid_id;
	
	function __construct($row){
		$this->subject_id = 40;
		$this->date_task_id = $row["date_task_id"];
		$this->short_name = $row["short_name"];
		$this->task_name = $row["name"];
		$this->mandatory_qty = $row["mandatory_qty"];	
		$this->quantity = $row["quantity"];
		$this->points = $row["points"];			
		$this->sequence_number = $row["sequence_number"];
		$this->focus_task = $row["focus_task"];
		$this->medium_pic = $row["medium_pic"];
		$this->grid_id = $row['grid_id'];
	}
	
	function set_dates($start_date, $end_date) {
		$this->start_date = $start_date;
		$this->end_date = $end_date;
	}
	
	function set_subject_name($subject_name) {
		$this->subject_name = $subject_name;
		$this->setLabel();
	}
	
	function set_subject_image_id($subject_image_id) {
		$this->subject_image_id = $subject_image_id;
	}
	
	function set_mission_name($mission_name) {
		$this->mission_name = $mission_name;
		$this->setLabel();
	}
	
	function set_mission_number($mission_number) {
		$this->mission_number = $mission_number;
	}
	
	function set_subject_id($subject_id) {
		$this->subject_id = $subject_id;
	}

	function set_date_task_mark($user_id, $start_date, $end_date) {
		$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $this->date_task_id . " AND (mark_date >= " . $start_date . " AND mark_date <= " . $end_date . ")";				
		if ($this->grid_id) {
			$sql = "select * from date_tasks_marks dtm
					join date_tasks dt using (date_task_id) 
					where dtm.user_id = " . $user_id . "
					and dt.grid_id = " . $this->grid_id . "
					and (mark_date >= " . $start_date . " AND mark_date <= " . $end_date . ")";
		}
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);

		$row = mysql_fetch_assoc($query);
		$date_task_mark = new date_tasks_mark($row);
		
		if ($num_rows == 0) {
			$date_task_mark->set_date_task_id($this->date_task_id);
		} else { 
			$date_task_mark->set_marked(true);
		}
		$this->date_task_mark = $date_task_mark;
	}
	
	function set_mark_date($end_date)
	{
		$today = gregoriantojd(date("n"), date("j"), date("Y"));
	
		//echo "TODAY:" . $today . " END DATE:" . $end_date . "<br />";
		
		if ($today <= $end_date)
			$this->mark_date = $today;
		else
			$this->mark_date = $end_date;
	}

	private function setLabel() {
		if ( $this->mission_name && $this->subject_name )
			$this->label_name = "$this->subject_name - $this->mission_name";
	}
} 
?>