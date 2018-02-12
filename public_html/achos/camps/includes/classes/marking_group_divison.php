<?php
class marking_group_divison {
	public $divisions = array();
	
	public function __construct(){
	}

	public function get_divisions($task_date, $group_type_id) {
		$groups = array();
		
		$sql = "SELECT d.division_id, d.division_name, g.group_id, g.group_name, cm.camp_mission_id, cm.mission_name ";
		$sql = $sql . "FROM group_task_dates AS gtd ";
		$sql = $sql . "JOIN groups AS g USING (group_id) ";
		$sql = $sql . "JOIN divisions AS d USING (division_id) ";
		$sql = $sql . "JOIN camp_tasks AS ct ON (gtd.camp_task_id=ct.camp_task_id) ";
		$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
		$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
		$sql = $sql . "WHERE cc.camp_campaign_id=" . $this->camp_campaign_id . " AND gtd.task_date=" . $task_date . " AND d.group_type_id=" . $group_type_id .  " ";
		$sql = $sql . "ORDER BY d.division_id, g.group_id, camp_mission_id ";
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);
		
		$division_id = "";
		$group_id = "";
		$row_num++;
		
		while ($row = mysql_fetch_assoc($query)) {	
			$row_num++;
			
			if ($prev_group_id != $group_id && $group_id != "") {
				$group = compact('group_id', 'group_name');
				array_push($groups, $group);			
			}
			
			if ($prev_division_id != $division_id && $division_id != "") {
				$division = compact('division_id', 'division_name', 'groups');
				array_push($this->divisions, $division);
				$groups = array();
			}
			
			$division_id = $prev_division_id;
			$division_name = $row["division_name"];
			$group_id = $prev_group_id;
			$group_name = $row["group_name"];
			
			if ($row_num == $num_rows) {
				$group = compact('group_id', 'group_name');
				array_push($groups, $group);	
				$division = compact('division_id', 'division_name', 'groups');
				array_push($this->divisions, $division);			
			}
			
		}
	}
	
}
?>