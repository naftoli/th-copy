<?php
class camp_mission {
	public $camp_mission_id;
	public $sequence;
	public $mission_id;
	public $camp_campaign_id;
	public $mission_name;
	public $points;
	
	public function __construct(){
	}

	public function new_camp_mission($row) {
		$this->camp_mission_id = $row['camp_mission_id'];
		$this->sequence = $row['sequence'];
		$this->mission_id = $row['mission_id'];
		$this->camp_campaign_id = $row['camp_campaign_id'];
		$this->mission_name = $row['mission_name'];
		$this->points = $row['points'];
	}
	
	public function get_number_of_tasks() {
		$sql = "SELECT COUNT(*) AS number_of_tasks FROM camp_tasks WHERE camp_mission_id=" . $this->camp_mission_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		return $row['number_of_tasks'];
	}
}
?>