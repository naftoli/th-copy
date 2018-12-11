<?php
class group_type {
	public $group_type_id;
	public $camp_id;
	public $group_type_name;
	public $has_divisions;
	public $logo_id;
	public $no_of_campers;	
	public $points;
	public $divisions = array();

	public function __construct($row) {
		$this->group_type_id = $row['group_type_id'];
		$this->camp_id = $row['camp_id'];
		$this->group_type_name = $row['group_type_name'];
		$this->has_divisions = $row['has_divisions'];
		$this->logo_id = $row['logo_id'];	
	}

	public function get_divisions() {
		$sql = "SELECT * FROM divisions WHERE group_type_id=" . $this->group_type_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$division = new division($row);
			array_push($this->divisions, $division);
		}	
	}
	
	public function get_group_task($camp_task_id, $group_task) {
		$sql = "SELECT group_task_id FROM group_tasks WHERE group_type_id=" . $this->group_type_id . " AND camp_task_id=" . $camp_task_id . " AND group_task=" . $group_task;		
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);	
		if ($num_rows == 0)
			return "";
		else 
			return " selected ";
	}
	
	public function	get_number_of_campers($camp_id) {
		$sql = "SELECT COUNT(*) AS no_of_campers ";
		$sql = $sql . "FROM member_groups AS mg ";
		$sql = $sql . "WHERE mg.camp_id=" . $camp_id . " AND mg.group_type_id=" . $this->group_type_id . " AND mg.end_date=0 ";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->no_of_campers = $row['no_of_campers'];	
	}
	
	public function get_group_type_points($camp_id) {
		$sql = "SELECT SUM(ct.points) AS points ";
		$sql = $sql . "FROM group_task_dates AS gtd ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";		
		$sql = $sql . "JOIN groups AS g USING (group_id) ";
		$sql = $sql . "JOIN divisions AS d ON (g.division_id=d.division_id AND d.group_type_id=" . $this->group_type_id . ") ";
		$sql = $sql . "WHERE completed=1 ";
		$sql = $sql . "GROUP BY d.group_type_id";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->points = $row['points'];
	
		//$sql = "SELECT SUM(ct.points) AS points ";
		//$sql = $sql . "FROM member_groups AS mg ";
		//$sql = $sql . "JOIN member_tasks AS mt ON (mt.user_id=mg.user_id AND mt.completed=1) ";	
		//$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		//$sql = $sql . "WHERE mg.camp_id=" . $camp_id . " AND mg.group_type_id=" . $this->group_type_id . " AND mg.end_date=0";
		//$query = mysql_query($sql);
		//$row = mysql_fetch_assoc($query);
		//$this->points = $row['points'];
	}
	
}
?>