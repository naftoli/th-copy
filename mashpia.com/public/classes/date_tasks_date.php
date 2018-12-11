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
	
} 
?>