<?
class task {
	public $frequency_name;
	public $frequency_period_name;
	public $date_task_id;
	public $task_name;
	public $mandatory_qty;
	public $quantity;
	public $sequence_number;
	public $subject_image_id;
	public $date_tasks_marks = array();
	
	function __construct($row){
		$this->frequency_name = $row["frequency_name"];
		$this->frequency_period_name = $row["frequency_period_name"];
		$this->date_task_id = $row["date_task_id"];
		$this->task_name = $row["name"];
		$this->mandatory_qty = $row["mandatory_qty"];
		$this->quantity = $row["quantity"];
		$this->sequence_number = $row["sequence_number"];			
	}
	
	function set_subject_image_id($subject_image_id) {
		$this->subject_image_id = $subject_image_id;
	}
			
	function set_date_tasks_marks($user_id, $start_date, $end_date) {
		for ($mark_date = $start_date; $mark_date <= $end_date; $mark_date++) {
			//////////$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $this->date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;
			$sql = "SELECT * FROM task_marks WHERE date_task_id=" . $this->date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$date_tasks_mark = new date_tasks_mark($row);
			if (is_null($date_tasks_mark->date_task_id)) {
				$date_tasks_mark->set_date_task_id($this->date_task_id);
				$date_tasks_mark->set_mark_date($mark_date);
			}
			array_push($this->date_tasks_marks, $date_tasks_mark);
		}
	}	
} 
?>