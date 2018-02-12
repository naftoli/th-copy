<?php
class date_tasks_mission_mark 
{
	public $user_id;
	public $date_tasks_mission_id;
	public $subject_id; 
	public $mission_value;
	public $mission_name;
	public $mark_date;
	public $mark_override;
	public $missions_updated;
	
	function __construct($row) 
	{
		$this->user_id = $row['user_id'];
		$this->date_tasks_mission_id = $row['date_tasks_mission_id'];
		$this->subject_id = $row['subject_id']; 
		$this->mission_value = $row['mission_value'];
		$this->mission_name = $row['mission_name'];
		$this->mark_date = $row['mark_date'];
		$this->mark_override = $row['mark_override'];
		$this->missions_updated = $row['missions_updated'];
	}
	
}
?>