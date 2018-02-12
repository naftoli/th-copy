<?
class date_task {
	public $date_task_id;
	public $date_tasks_mission_id;
	public $ord;
	public $name;
	public $description;
	public $mandatory_qty;
	public $optional_qty;
	public $is_bonus;
	public $label_id;
	public $quantity;
	public $points;
	public $sequence_number;
	public $label;
	public $date_tasks_marks = array();
	public $subject_image_id;
	public $date_tasks_mark;
	public $subject;
	public $mission_number;
	public $mission_name;
	public $track_id;
	public $level;
	public $start_date;
	public $end_date;
    public $default_on;
	
	function __construct($row){
		$this->date_task_id = $row["date_task_id"];
		$this->date_tasks_mission_id = $row["date_tasks_mission_id"];
		$this->ord = $row["ord"];
		$this->name = $row["name"];
		$this->description = $row["description"];
		$this->mandatory_qty = $row["mandatory_qty"];
		$this->optional_qty = $row["optional_qty"];
		$this->is_bonus = $row["is_bonus"];
		$this->label_id = $row["label_id"];
		$this->quantity = $row["quantity"];
		$this->points = $row["points"];
		$this->sequence_number = $row["sequence_number"];	
        $this->default_on = $row["default_on"];
	}
	
	function get_label() {
		if ($this->label_id > 0) {
			$sql = "SELECT * FROM labels WHERE label_id=" . $this->label_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$label = new label($row);
			$this->label = new label($row);
			$this->label->get_frequency();
		}
	}
	
	function get_date_tasks_marks($user_id, $start_date, $end_date) {
		$date_task_id = $this->date_task_id;
		for ($mark_date = $start_date; $mark_date <= $end_date; $mark_date++) {
			$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $this->date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$mark_points = $row["mark_points"];
			$element = compact('date_task_id', 'mark_date', 'mark_points');
			array_push($this->date_tasks_marks, $element);
		}
	}
	
	function get_date_tasks_mark($user_id) {
		$date_task_id = $this->date_task_id;
		$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $this->date_task_id . " AND user_id=" . $user_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		//$date_tasks_mark = new date_tasks_mark($row);
		$this->date_tasks_mark = new date_tasks_mark($row);
	}
	
	function get_subject_image_id() {
		$sql = "SELECT s.subject_image_id FROM date_tasks_missions AS dtm JOIN subjects AS s USING (subject_id) WHERE date_tasks_mission_id=" . $this->date_tasks_mission_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->subject_image_id = $row["subject_image_id"];
	}
	
	function get_subject() {
		$sql = "SELECT s.* FROM date_tasks_missions AS dtm JOIN subjects AS s USING (subject_id) WHERE date_tasks_mission_id=" . $this->date_tasks_mission_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->subject = new subject($row);
	}
	
	function set_mission_number($mission_number) {
		$this->mission_number = $mission_number;
	}
	
	function set_mission_name($mission_name) {
		$this->mission_name = $mission_name;
	}

	function set_track_id($track_id) {
		$this->track_id = $track_id;
	}
	
	function set_level($level) {
		$this->level = $level;
	}
	
	function set_start_date($start_date) {
		$this->start_date = $start_date;
	}
	
	function set_end_date($end_date) {
		$this->end_date = $end_date;
	}
	
	function get_status($user_id)
	{
		$sql = "SELECT * FROM date_tasks WHERE date_task_id=" . $this->date_task_id . " AND user_id=" . $user_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);		
	}
		
} 
?>