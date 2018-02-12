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
	
	public function __construct($row){
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
	
}
?>