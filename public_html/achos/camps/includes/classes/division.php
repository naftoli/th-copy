<?php
class division {
	public $division_id;
	public $group_type_id;
	public $division_name;
	public $group_type;
	public $no_of_campers;
	public $points;
	public $groups = array();
	
	public function __construct($row){
		$this->division_id = $row['division_id'];
		$this->group_type_id = $row['group_type_id'];
		$this->division_name = $row['division_name'];	
	}

	public function get_groups() {
		$sql = "SELECT * FROM groups WHERE division_id=" . $this->division_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$group = new group($row);
			array_push($this->groups, $group);
		}		
	}
	
	public function get_division_task($camp_task_id, $group_task) {
		$sql = "SELECT group_task_id FROM group_tasks WHERE division_id=" . $this->division_id . " AND camp_task_id=" . $camp_task_id . " AND group_task=" . $group_task;
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);
		if ($num_rows > 0) 
			return " selected ";
		else 
			return "";
	}
	
	public function get_group_type() {
		$sql = "SELECT * FROM group_types WHERE group_type_id=" . $this->group_type_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->group_type = new group_type($row);
	}
	
	public function	get_number_of_campers($camp_id) {
		$sql = "SELECT COUNT(*) AS no_of_campers ";
		$sql = $sql . "FROM member_groups AS mg ";
		$sql = $sql . "WHERE mg.camp_id=" . $camp_id . " AND mg.division_id=" . $this->division_id . " AND mg.end_date=0";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->no_of_campers = $row['no_of_campers'];		
	}
	
	public function get_division_points($camp_id) {
		$sql = "SELECT SUM(ct.points) AS points ";
		$sql = $sql . "FROM group_task_dates AS gtd ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";		
		$sql = $sql . "JOIN groups AS g ON (gtd.group_id=g.group_id AND g.division_id=" . $this->division_id . ") ";
		$sql = $sql . "WHERE division_id=" . $this->division_id . " AND completed=1 ";
		$sql = $sql . "GROUP BY division_id";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->points = $row['points'];
	
		//$sql = "SELECT SUM(ct.points) AS points ";
		//$sql = $sql . "FROM member_groups AS mg ";
		//$sql = $sql . "JOIN member_tasks AS mt ON (mt.user_id=mg.user_id AND mt.completed=1) ";	
		//$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		//$sql = $sql . "WHERE mg.camp_id=" . $camp_id . " AND mg.division_id=" . $this->division_id . " AND mg.end_date=0";
		//$query = mysql_query($sql);
		//$row = mysql_fetch_assoc($query);
		//$this->points = $row['points'];
	}
	
}
?>