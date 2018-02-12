<?
class daily_task {
	public $date_task_id;
	public $short_name;
	public $task_name;
	public $mandatory_qty;
	public $quantity;
	public $points;
	public $focus_task;
	
	public $label_name;
	public $frequency_id;
	public $frequency_name;
	public $frequency_period_name;
	
	public $subject_image_id;
	
	public $start_date;
	public $end_date;
        
    public $label_ord;
	public $needed;
	
	public $date_task_marks = array();
	
	public $subject_id;
	public $medium_pic;
	
	public $grid_id;
	
	function __construct($row, $subject_id, $task_name){
		$this->subject_id = $subject_id;
		$this->label_name = $row["label_name"];
		$this->frequency_id = $row["frequency_id"];
		$this->frequency_name = $row["frequency_name"];
		$this->frequency_period_name = $row["frequency_period_name"];
		
		//////////if ($subject_id == 91)
		//////////	echo "<input type='hidden' name='TASK NAME' value='" . $row["label_name"] . "->" . $row["frequency_name"] . "->" . $task_name . "'>\n";
		
		$this->date_task_id = $row["date_task_id"];
		$this->short_name = $row["short_name"];
		$this->task_name = $row["name"];
		$this->mandatory_qty = $row["mandatory_qty"];
		$this->quantity = $row["quantity"];
		$this->points = $row["points"];
		$this->focus_task = $row["focus_task"];
        $this->label_ord = $row["label_ord"];
		$this->needed = $row["needed"];
		$this->medium_pic = $row["medium_pic"];
		$this->grid_id = $row['grid_id'];
	}
	
	function set_dates($start_date, $end_date) {
		$this->start_date = $start_date;
		$this->end_date = $end_date;
	}	
	
	function set_subject_image_id($subject_image_id) {
		$this->subject_image_id = $subject_image_id;
	}
	
	function set_date_tasks_marks($user_id, $start_date, $end_date) {
		for ($mark_date = $start_date; $mark_date <= $end_date; $mark_date++) {
			$sql = "SELECT * FROM date_tasks_marks WHERE user_id=" . $user_id . " AND date_task_id=" . $this->date_task_id . " AND mark_date=" . $mark_date;
			if ($this->grid_id) {
				$sql = "select * from date_tasks_marks dtm
						join date_tasks dt using (date_task_id) 
						where dtm.user_id = " . $user_id . "
						and dt.grid_id = " . $this->grid_id . "
						and dtm.mark_date = " . $mark_date;
			}	
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$num_rows = mysql_num_rows($query);
			$date_tasks_mark = new date_tasks_mark($row);
						
			if ($num_rows == 0) 
			{
				$date_tasks_mark->set_date_task_id($this->date_task_id);
				$date_tasks_mark->set_mark_date($mark_date);
				$date_tasks_mark->set_marked(false);
			}
			else 
			{
				$date_tasks_mark->set_marked(true);
			}
			
			array_push($this->date_task_marks, $date_tasks_mark);
		}
	}
	
} 
?>