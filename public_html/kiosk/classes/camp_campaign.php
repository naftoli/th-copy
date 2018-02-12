<?php
class camp_campaign {
  	public $camp_campaign_id;
	public $campaign_id;
	public $camp_id;
	public $campaign_name;
	public $points;
	public $group_task;
	public $active;
	public $camp_missions = array();
	
	public function __construct(){
	}

	public function new_camp_campaign($row) {
		$this->camp_campaign_id = $row['camp_campaign_id'];
		$this->campaign_id = $row['campaign_id'];
		$this->camp_id = $row['camp_id'];
		$this->campaign_name = $row['campaign_name'];
		$this->points = $row['points'];
		$this->group_task = $row['group_task'];
		$this->active = $row['active'];	
	}
	
	public function get_missions() {	
		$sql = "SELECT * FROM camp_missions WHERE camp_campaign_id=" . $this->camp_campaign_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$camp_mission = new camp_mission();
			$camp_mission->new_camp_mission($row);
			array_push($this->camp_missions, $camp_mission);
		}
	}
	
	public function install_global_campaign($camp_id, $campaign_id, $group_task) {
		$sql = "SELECT * FROM global_campaigns WHERE campaign_id=" . $campaign_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$insert = "INSERT INTO camp_campaigns SET campaign_id=" . $campaign_id . ", camp_id=" . $camp_id . ", campaign_name='" . mysql_real_escape_string($row['campaign_name']) . "', points=" . $row['points'] . ", group_task=" . $group_task . ", active=1";
		$insert_query = mysql_query($insert);
		$camp_campaign_id = mysql_insert_id();
		
		$sql = "SELECT * FROM global_missions WHERE campaign_id=" . $campaign_id;
		//echo $sql . "<br />";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$insert = "INSERT INTO camp_missions SET camp_mission_id=" . $row['mission_id'] . ", camp_campaign_id=" . $camp_campaign_id . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', points=" . $row['points']  .  ", sequence=" . $row['sequence'] ;
			//echo $insert . "<br />";
			$insert_query = mysql_query($insert);
			$camp_mission_id = mysql_insert_id();
		
			$sql2 = "SELECT * FROM global_tasks WHERE mission_id=" . $row['mission_id'];
			$query2 = mysql_query($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$insert = "INSERT INTO camp_tasks SET task_id=" . $row2['task_id'] . ", camp_mission_id=" . $camp_mission_id . ", camp_type_id=" . $row2['camp_type_id'] . ", level_id=" . $row2['level_id'] . ", task_name='" . mysql_real_escape_string($row2['task_name']) . "', period_id=" . $row2['period_id'] . ", points=" . $row2['points'];
				//echo $insert . "<br />";
				$insert_query = mysql_query($insert);			
			}
		}
		
		return $camp_campaign_id;
		
	}
}
?>