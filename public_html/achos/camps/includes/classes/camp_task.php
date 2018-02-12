<?php
class camp_task {
	public $camp_task_id;
	public $task_id;
	public $camp_mission_id;
	public $camp_type_id;
	public $level_id;
	public $period_id;
	public $task_name;
	public $points;
	
	public function __construct(){
	}
	
	public function new_camp_task($row) {
		$this->camp_task_id = $row['camp_task_id'];
		$this->task_id = $row['task_id'];
		$this->camp_mission_id = $row['camp_mission_id'];
		$this->camp_type_id = $row['camp_type_id'];
		$this->level_id = $row['level_id'];
		$this->period_id = $row['period_id'];
		$this->task_name = $row['task_name'];
		$this->points = $row['points'];
	}
}
?>