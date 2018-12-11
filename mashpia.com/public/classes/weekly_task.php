<?
class weekly_task {
	public $label_name;
	public $frequency_id;
	public $frequency_name;
	public $frequency_period_name;
	public $date_task_id;
	public $short_name;
	public $task_name;
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
	public $points;
	
	function __construct($row){
		$this->label_name = $row["label_name"];
		$this->frequency_id = $row["frequency_id"];
		$this->frequency_name = $row["frequency_name"];
		$this->frequency_period_name = $row["frequency_period_name"];
		$this->date_task_id = $row["date_task_id"];
		$this->short_name = $row["short_name"];
		$this->task_name = $row["name"];
		$this->mandatory_qty = $row["mandatory_qty"];
		$this->quantity = $row["quantity"];
		$this->sequence_number = $row["sequence_number"];
		$this->focus_task = $row["focus_task"];
		$this->medium_pic = $row["medium_pic"];
		$this->grid_id = $row['grid_id'];
		$this->points = $row['points'];
	}
	
	function set_subject_image_id($subject_image_id) {
		$this->subject_image_id = $subject_image_id;
	}
	
	function set_subject_id($id) {
		$this->subject_id = $id;
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
		//if ($user_id == 19970 && $this->date_task_id == 4000051) { echo $sql; exit; }
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$num_rows = mysql_num_rows($query);
		$date_task_mark = new date_tasks_mark($row);
		if ($this->date_task_id == 2211017) {
			echo "<input type='hidden' name='taskMarkSql' value='" . $sql . "' />";
		}
		if ($num_rows == 0) {
			$date_task_mark->set_marked(false);
            $date_task_mark->set_date_task_id($this->date_task_id);
        }
		else
			$date_task_mark->set_marked(true);		
		
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
	
	
} 
?>