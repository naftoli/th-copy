<?php
class group_marking {
	public $campaigns = array();
	public $camp_campaign_id;
	public $campaign_name;
	public $divisions = array();
	
	public function __construct($camp_campaign_id, $campaign_name) {
		$this->camp_campaign_id = $camp_campaign_id;
		$this->campaign_name = $campaign_name;
		$campaign = compact('camp_campaign_id', 'campaign_name');
		array_push($this->campaigns, $campaign);
	}
	
	public function get_divisions($task_date, $group_type_id) {
		/*$groups = array();
		$missions = array();
		
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
		while ($row = mysql_fetch_assoc($query)) {
			$prev_division_id = $row["division_id"];
			$prev_group_id = $row["group_id"];
			$prev_camp_mission_id = $row["camp_mission_id"];
			$row_num++;
			
			if ($prev_group_id != $group_id && $group_id != "") {
				//$muissions = array();
				//$sql2 = "SELECT camp_mission_id, mission_name ";
				//$sql2 = $sql2 . "FROM group_task_dates AS gtd ";	
				//$sql2 = $sql2 . "JOIN camp_tasks AS ct ON (gtd.camp_task_id=ct.camp_task_id) ";
				//$sql2 = $sql2 . "JOIN camp_missions AS cm USING (camp_mission_id) ";
				//$sql2 = $sql2 . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";				
				//$sql2 = $sql2 . "WHERE cc.camp_campaign_id=" . $this->camp_campaign_id . " AND gtd.task_date=" . $task_date . " AND gtd.group_id=" . $group_id .  " ";
				//$sql2 = $sql2 . "GROUP BY camp_mission_id";
				//$query2 = mysql_query($sql2);
				//while ($row2 = mysql_fetch_assoc($query2)) {
				//	$camp_mission_id = $row2["camp_mission_id"];
				//	$mission_name = $row2["mission_name"];
				//	$mission = compact('camp_mission_id', 'mission_name');
				//	array_push($missions, $mission);
				//}
				
				$group = compact('group_id', 'group_name');
				array_push($groups, $group);
			}
			
			if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
				$mission = compact('camp_mission_id', 'mission_name');
				array_push($missions, $mission);			
			}
			
			if ($prev_division_id != $division_id && $division_id != "") {
				$division = compact('division_id', 'division_name', 'groups', 'missions');
				array_push($this->divisions, $division);
				$groups = array();
				$missions = array();
			}
			
			$division_id = $prev_division_id;
			$division_name = $row["division_name"];
			$group_id = $prev_group_id;
			$group_name = $row["group_name"];
			$camp_mission_id = $prev_camp_mission_id;
			$mission_name = $row["mission_name"];
			
			if ($row_num == $num_rows) {
				$group = compact('group_id', 'group_name');
				array_push($groups, $group);	
				$mission = compact('camp_mission_id', 'mission_name');
				array_push($missions, $mission);
				$division = compact('division_id', 'division_name', 'groups');
				array_push($this->divisions, $division);			
			}
		}*/
	}
	

}
?>