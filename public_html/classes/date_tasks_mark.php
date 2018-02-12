<?
class date_tasks_mark {
	public $date_task_id;
	public $user_id;
	public $mark_date;
	public $done_qty;
	public $mark_description;
	public $mark_points;
	public $mark_quantity;
	public $mark_inactive;
	
	public $marked;
	
	function __construct($row) {
		$this->date_task_id = $row["date_task_id"];
		$this->user_id = $row["user_id"];
		$this->mark_date = $row["mark_date"];
		$this->done_qty = $row["done_qty"];
		$this->mark_description = $row["mark_description"];
		$this->mark_points = $row["mark_points"];
		$this->mark_quantity = $row["mark_quantity"];
		$this->mark_inactive = $row["mark_inactive"];
	}
	
	function set_date_task_id($date_task_id) {
		$this->date_task_id = $date_task_id;
	}
	
	function set_mark_date($mark_date) {
		$this->mark_date = $mark_date;
	}
	
	function set_marked($marked) {
		$this->marked = $marked;
	}
} 
?>