<?
class user_mission_not_done {
	public $user_id;
	//public $school_type_id;
	public $subject_id;
	public $subject_name;
	//public $track_id;
	//public $level;
	//public $date_tasks_mission_id;
	//public $mission_name;
	public $number_of_missions;
	
	function __construct($row){
		$this->user_id = $row["user_id"];
		//$this->school_type_id = $row["school_type_id"];
		$this->subject_id = $row["subject_id"];
		$this->subject_name = $row["subject_name"];
		//$this->track_id = $row["track_id"];
		//$this->level = $row["level"];
		//$this->date_tasks_mission_id = $row["date_tasks_mission_id"];
		//$this->mission_name = $row["mission_name"];
		$this->number_of_missions = $row["number_of_missions"];
	}
	
	
} 
?>