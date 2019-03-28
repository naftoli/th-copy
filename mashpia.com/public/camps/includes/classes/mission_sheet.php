<?php
class mission_sheet {
	public $task_date;
	public $greg_task_date;
	public $date_title;
	public $group_type_name;
	public $missions = array();
	public $groups = array();
	
	public function __construct(){
	}
	
	public function new_mission_sheet($task_date) {
		$months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep". "Oct", "Nov", "Dec");
		$this->task_date = $task_date;
		$this->greg_task_date = jdtogregorian($task_date);
		$date_array =explode("/", $this->greg_task_date); 
		$this->date_title = $months[$date_array[0] - 1] . " " . $date_array[1] . ", " . $date_array[2];
	}
	
	public function get_groups($group_type_id) {	
		$sql = "SELECT d.division_name, g.* ";
		$sql = $sql . "FROM member_tasks AS mt ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
		$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
		$sql = $sql . "JOIN groups AS g USING (group_id) ";
		$sql = $sql . "JOIN divisions AS d USING (division_id) ";
		$sql = $sql . "JOIN group_types AS gt USING (group_type_id) ";
		$sql = $sql . "WHERE mt.task_date=" . $this->task_date . " AND d.group_type_id=" . $group_type_id . " ";
		$sql = $sql . "GROUP BY gt.group_type_id, d.division_id, g.group_id ";
		$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {	
			$group_id = $row['group_id'];
			$group_name = $row['group_name'];
			$division_name = $row['division_name'];
			
			$group = new group($row);
			$group->get_members();
			$members = $group->members;
			
			//$element = compact('group_id', 'group_name', 'members');
			$element = compact('division_name', 'group_id', 'group_name', 'members');			
			array_push($this->groups, $element);
		}		
	}
	
	function get_missions($group_type_id) {
		$tasks = array();
		
		$sql = "SELECT cc.camp_campaign_id, cc.campaign_name, cm.camp_mission_id, cm.mission_name, ct.camp_task_id, ct.task_name ";
		$sql = $sql . "FROM member_tasks AS mt ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
		$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
		$sql = $sql . "JOIN groups AS g USING (group_id) ";
		$sql = $sql . "JOIN divisions AS d USING (division_id) ";
		$sql = $sql . "JOIN group_types AS gt USING (group_type_id) ";
		$sql = $sql . "WHERE mt.task_date=" . $this->task_date. " AND d.group_type_id=" . $group_type_id . " ";
		$sql = $sql . "GROUP BY cc.camp_campaign_id, cm.camp_mission_id, ct.camp_task_id ";
		$sql = $sql . "ORDER BY cc.camp_campaign_id, cm.camp_mission_id, ct.camp_task_id";
		
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);

		$camp_mission_id = "";
		$row_num = 0;
		while ($row = mysql_fetch_assoc($query)) {
			$row_num++;
			$prev_camp_mission_id = $row['camp_mission_id'];
		
			if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
				$element = compact('campaign_name', 'mission_name', 'tasks');
				array_push($this->missions, $element);
				$tasks = array();
			}
		
			$campaign_name = $row['campaign_name'];
			$mission_name = $row['mission_name'];
			$camp_task_id = $row['camp_task_id'];
			$task_name = $row['task_name'];
			$element = compact('camp_task_id', 'task_name');
			array_push($tasks, $element);
				
			if ($row_num == $num_rows) {
				$element = compact('campaign_name', 'mission_name', 'tasks');
				array_push($this->missions, $element);		
			}
		
			$camp_mission_id = $prev_camp_mission_id;
		}
	
	}
	
}
?>