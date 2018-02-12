<?php
class group_task {
	public $group_task_id;
	public $group_type_id;
	public $division_id;
	public $group_id;
	public $camp_task_id;
	public $period_id;
	public $group_task;
	
	public function __construct(){
	}

	public function new_group_tasks($row) {
		$this->group_task_id = $row['group_task_id'];
		$this->group_type_id = $row['group_type_id'];
		$this->division_id = $row['division_id'];
		$this->group_id = $row['group_id'];
		$this->camp_task_id = $row['camp_task_id'];
		$this->period_id = $row['period_id'];
		$this->group_task = $row['group_task'];	
	}
	
	public function add_group_task($group_type_id, $division_id, $group_id, $camp_task_id, $period_id, $group_task) {
		$sql = "INSERT INTO group_tasks SET group_type_id=" . $group_type_id . ", division_id=" . $division_id . ", group_id=" . $group_id . ", camp_task_id=" . $camp_task_id . ", period_id=" . $period_id . ", group_task=" . $group_task;
		$query = mysql_query($sql);
		return mysql_insert_id();
	}
	
	public function get_group_task_id($group_id, $camp_task_id, $group_task) {
		$sql = "SELECT group_task_id FROM group_tasks WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id . " AND group_task=" . $group_task;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		return $row['group_task_id'];
	}
	
	public function add_new_group_task($group_type_id, $division_id, $group_id, $camp_task_id, $period_id, $group_task) {
		$sql = "SELECT group_task_id FROM group_tasks WHERE group_type_id=0 AND division_id=" . $division_id . " AND camp_task_id=" . $camp_task_id . " AND group_task=" . $group_task;
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);
		
		if ($num_rows > 0) {
			$row = mysql_fetch_assoc($query);
			$group_task_id = $row['group_task_id'];
			$sql = "UPDATE group_tasks SET group_type_id=" . $group_type_id . " WHERE group_task_id=" . $group_task_id;
			$result = mysql_query($sql);
		}
		else {
			$sql = "INSERT INTO group_tasks SET group_type_id=" . $group_type_id . ", division_id=" . $division_id . ", group_id=" . $group_id . ", camp_task_id=" . $camp_task_id . ", period_id=" . $period_id . ", group_task=" . $group_task;
			$result = mysql_query($sql);			
		}
		
		if ($result) 
			return true;
		else 
			return false;
		
	}
		
	public function update_group_task($group_task_id, $period_id) {
		$sql = "UPDATE group_tasks SET period_id=" . $period_id . " WHERE group_task_id=" . $group_task_id;
		$query = mysql_query($sql);
		if ($query)
			return true;
		else
			return false;
	}
	
	public function get_todays_julian_date() {
		$todays_day = date("j"); 
		$todays_month = date("n"); 
		$todays_year = date("Y"); 
		$today_jd = cal_to_jd(CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);
		return $today_jd;	
	}
}
?>