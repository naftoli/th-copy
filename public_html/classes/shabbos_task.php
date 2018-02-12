<?
class shabbos_task {
	public $label_name;
	public $frequency_id;
	public $frequency_name;
	public $frequency_period_name;
	public $date_task_id;
	public $short_name;
	public $task_name;
	public $desc;
	public $mandatory_qty;
	public $quantity;
	public $sequence_number;
	public $focus_task;
	public $subject_image_id;
	public $date_task_mark;
	public $mark_date;
	public $start_date;
	public $end_date;
	public $subject_id;
	public $medium_pic;
	public $grid_id;
	
	function __construct($row) {
		$this->label_name = $row["label_name"];
		$this->frequency_id = $row["frequency_id"];
		$this->frequency_name = $row["frequency_name"];
		$this->frequency_period_name = $row["frequency_period_name"];
		$this->date_task_id = $row["date_task_id"];
		$this->short_name = $row["short_name"];
		$this->task_name = $row["name"];
		$this->desc = $row["description"];
		$this->mandatory_qty = $row["mandatory_qty"];
		$this->quantity = $row["quantity"];
		$this->sequence_number = $row["sequence_number"];
		$this->focus_task = $row["focus_task"];
		$this->medium_pic = $row["medium_pic"];
		$this->grid_id = $row["grid_id"];
	}
	
	function set_subject_image_id($subject_image_id) {
		$this->subject_image_id = $subject_image_id;
	}
	
	function set_subject_id($id) {
		$this->subject_id = $id;
	}
	
	function set_mark_date($start_date, $end_date) {
		for ($mark_date = $start_date; $mark_date <= $end_date; $mark_date++) {
			$remainder = $mark_date % 7;
			if ($remainder == 5)
				$this->mark_date = $mark_date;
		}
	}
	
	function set_date_task_mark($user_id, $start_date, $end_date) {
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		
		$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $this->date_task_id . " AND user_id=" . $user_id . " AND (mark_date >= " . $start_date . " AND mark_date <= " . $end_date . ")";				
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
		
		if ($num_rows > 0) {
			$date_task_mark->set_marked(true);
		}
		else {
			$date_task_mark->set_date_task_id($this->date_task_id);
			
			$remainder = $start_date % 7;
			if ($remainder == 6)
				$mark_date = $start_date + 6;
			elseif ($remainder == 5)
				$mark_date = $start_date;
			elseif ($remainder == 4)
				$mark_date = $start_date + 1;
			elseif ($remainder == 3)
				$mark_date = $start_date + 2;
			elseif ($remainder == 2)
				$mark_date = $start_date + 3;
			elseif ($remainder == 1)
				$mark_date = $start_date + 4;
			elseif ($remainder == 0)
				$mark_date = $start_date + 5;
				
			$date_task_mark->mark_date = $mark_date;
		}
		
		$this->date_task_mark = $date_task_mark;
	}
	
} 
?>