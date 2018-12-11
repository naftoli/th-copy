<?
class weekly_task {
	public $label_name;
	public $frequency_id;
	public $frequency_name;
	public $frequency_period_name;
	public $date_task_id;
	public $label_id;
	public $task_name;
	public $description;
	public $mandatory_qty;
	public $quantity;
	public $sequence_number;
	public $focus_task;
	
	public $subject_image_id;
	public $date_task_mark;
	
	public $default_on; 
	
	public $mark_date;
	
	public $master_task_id;
	
	public $points;
	
	function __construct($row){
		$this->label_id = $row["label_id"];
		$this->label_name = $row["label_name"];
		$this->frequency_id = $row["frequency_id"];
		$this->frequency_name = $row["frequency_name"];
		$this->frequency_period_name = $row["frequency_period_name"];
		$this->date_task_id = $row["date_task_id"];
		$this->task_name = $row["name"];
		$this->description = $row["description"];
		$this->mandatory_qty = $row["mandatory_qty"];
		$this->quantity = $row["quantity"];
		$this->sequence_number = $row["sequence_number"];
		$this->focus_task = $row["focus_task"];
		$this->default_on = $row['default_on'];
		$this->master_task_id = $row['master_task_id'];
		$this->points = $row['points'];
	}
	
	function set_subject_image_id($subject_image_id) {
		$this->subject_image_id = $subject_image_id;
	}
	
	function set_date_task_mark($user_id, $start_date, $end_date) {
		$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $this->date_task_id . " AND user_id=" . $user_id;				
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$num_rows = mysql_num_rows($query);
		$date_task_mark = new date_tasks_mark($row);
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