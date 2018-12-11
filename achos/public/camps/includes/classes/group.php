<?php
class group {
	public $group_id;
	public $division_id;
	public $group_name;
	public $division;
	public $no_of_campers;
	public $no_of_staff;
	public $points;
	public $member_points;
	public $group_points;
	public $average_points;
	public $member_group_id;
	public $members = array();
	public $missions = array();
	public $admins = array();
	
	public function __construct($row){
		$this->group_id = $row['group_id'];
		$this->division_id = $row['division_id'];
		$this->group_name = $row['group_name'];	
	}

	public function get_missions($task_date, $group_id) {
		$tasks = array();
		
		$sql = "SELECT * ";
		$sql = $sql . "FROM member_tasks AS mt ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
		$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND mt.group_id=" . $group_id . " ";
		$sql = $sql . "ORDER BY cm.camp_mission_id, ct.camp_task_id ";
		//echo $sql . "<br />";
		$query = mysql_query($sql);	
		$num_rows = mysql_num_rows($query);
		
		$row_num = 0;
		$camp_mission_id = "";
		$camp_task_id = "";
		while ($row = mysql_fetch_assoc($query)) {
			$prev_camp_mission_id = $row['camp_mission_id'];
			$prev_camp_task_id = $row['camp_task_id'];
			$row_num++;
			
			if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
				//echo $sql . "<br />";
				//echo "1) # OF TASKS:" . count($tasks) . "<br />";
				$mission = compact('camp_mission_id', 'mission_name', 'tasks');
				array_push($this->missions, $mission);
				$tasks = array();
			}
						
			if ($prev_camp_task_id != $camp_task_id && $camp_task_id != "") {
				$task = compact('camp_task_id', 'task_name');
				array_push($tasks, $task);
			}
						
			$camp_mission_id = $prev_camp_mission_id;
			$mission_name = $row['mission_name'];
			$camp_task_id = $prev_camp_task_id;	
			$task_name = $row['task_name'];						
			
			if ($row_num == $num_rows) {
				$task = compact('camp_task_id', 'task_name');
				array_push($tasks, $task);
				//echo "2) # OF TASKS:" . count($tasks) . "<br />";
				$mission = compact('camp_mission_id', 'mission_name', 'tasks');
				array_push($this->missions, $mission);			
			}
		}
	}
	
	public function get_members_info($group_id) {
		$sql = "SELECT u.user_id, u.first, u.last ";
		$sql = $sql . "FROM member_groups AS mg ";
		$sql = $sql . "JOIN users AS u USING (user_id) ";
		$sql = $sql . "WHERE mg.group_id=" . $group_id . " AND mg.end_date=0";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$user_id = $row['user_id'];
			$first = $row['first'];
			$last = $row['last'];			
			$user = compact('user_id', 'first', 'last');
			array_push($this->members, $user);
		}	
	}
	
	public function get_members() {
		$sql = "SELECT u.*, mg.member_group_id ";
		$sql = $sql . "FROM member_groups AS mg ";
		$sql = $sql . "JOIN users AS u USING (user_id) ";
		$sql = $sql . "WHERE mg.group_id=" . $this->group_id . " AND mg.end_date=0";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$this->member_group_id = $row['member_group_id'];
			$member = new user($row);
			array_push($this->members, $member);
		}	
	}
	
	public function get_staff() {
		$sql = "SELECT * FROM staff_groups WHERE group_id=" . $this->group_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$admin = new admin($row);
			array_push($this->admins, $admin);		
		}		
	}
	
	public function get_division() {
		$sql = "SELECT * FROM divisions WHERE division_id=" . $this->division_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->division = new division($row);
	}
	
	public function	get_number_of_campers($camp_id) {
		$sql = "SELECT COUNT(*) AS no_of_campers ";
		$sql = $sql . "FROM member_groups AS mg ";
		$sql = $sql . "WHERE mg.camp_id=" . $camp_id . " AND mg.group_id=" . $this->group_id . " AND mg.end_date=0";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->no_of_campers = $row['no_of_campers'];		
	}
	
	public function	get_number_of_staff() {
		$sql = "SELECT COUNT(*) AS no_of_staff ";
		$sql = $sql . "FROM staff_groups AS sg ";
		$sql = $sql . "WHERE sg.group_id=" . $this->group_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->no_of_staff = $row['no_of_staff'];		
	}
	
	public function get_group_points($camp_id) {
		$sql = "SELECT SUM(points) AS points ";
		$sql = $sql . "FROM group_task_dates ";
		$sql = $sql . "JOIN camp_tasks USING (camp_task_id) ";
		$sql = $sql . "WHERE group_id=" . $this->group_id . " AND completed=1 ";
		$sql = $sql . "GROUP BY group_id";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->group_points = $row['points'];

		$sql = "SELECT SUM(ct.points) AS points ";
		$sql = $sql . "FROM member_groups AS mg ";
		$sql = $sql . "JOIN member_tasks AS mt ON (mt.user_id=mg.user_id AND mt.completed=1) ";	
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		$sql = $sql . "WHERE mg.camp_id=" . $camp_id . " AND mg.group_id=" . $this->group_id . " AND mg.end_date=0";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->member_points = $row['points'];
		
		if ($this->no_of_campers > 0)
			$this->average_points = round(($this->member_points / $this->no_of_campers), 2);
		else
			$this->average_points = 0;
			
		$this->points = $this->group_points + $this->average_points;
	}
	
}
?>