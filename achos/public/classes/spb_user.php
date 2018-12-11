<?php
class spb_user {
	public $user_id;
	public $first;
	public $last;
	public $school_type_id;
	public $class_id;
	public $class_grade;
	public $class_sub;
	
	public $completed_missions;
	public $incomplete_missions;
	public $possible_missions;
	
	public $medal_awarded;
	
	public $show_user;
	
	public $medal_name;
	
	public $user_tracks = array();
	
	function __construct($row)
	{
		$this->user_id = $row['user_id'];
		$this->first = $row['first'];
		$this->last = $row['last'];	
		$this->school_type_id = $row['school_type_id'];
		$this->class_id = $row['class_id'];
		$this->class_grade = $row['class_grade'];
		$this->class_sub = $row['class_sub'];
		$this->show_user = false;
	}
	
	function get_completed_missions($subject_id)
	{
		$sql = "SELECT count(*) AS completed_missions ";
		$sql = $sql . "FROM user_tracks AS ut ";
		$sql = $sql . "JOIN date_tasks_mission_marks AS dtmm ON (dtmm.user_id=" . $this->user_id . " AND dtmm.subject_id=" . $subject_id . ") ";
		$sql = $sql . "WHERE ut.user_id=" . $this->user_id . " ";
		$sql = $sql . "AND ut.subject_id=" . $subject_id . " ";
		$sql = $sql . "GROUP BY ut.user_id, ut.subject_id  ";
		
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		if ($row['completed_missions'] > 0)
			$this->completed_missions = $row['completed_missions'];
		else
			$this->completed_missions = 0;	
	}
	
	function get_incomplete_missions($subject_id, $start_date, $end_date)
	{
		$sql = "SELECT count(*) AS incomplete_missions ";
		$sql = $sql . "FROM user_tracks AS ut ";
		$sql = $sql . "JOIN date_tasks_missions AS dtm ON (dtm.school_type_id=" . $this->school_type_id . " AND dtm.subject_id=ut.subject_id AND dtm.track_id=ut.track_id AND dtm.level=ut.level) ";
		$sql = $sql . "LEFT JOIN date_tasks_mission_marks AS dtmm ON (dtmm.user_id=" . $this->user_id . " AND dtmm.date_tasks_mission_id=dtm.date_tasks_mission_id) ";
		$sql = $sql . "WHERE ut.user_id=" . $this->user_id . " ";
		$sql = $sql . "AND ut.subject_id=" . $subject_id . " ";
		$sql = $sql . "AND dtm.start_date < " . $end_date . " ";
		$sql = $sql . "AND dtm.end_date > " . $start_date . " ";
		$sql = $sql . "AND dtmm.user_id IS NULL ";
		$sql = $sql . "GROUP BY ut.user_id, ut.subject_id  ";
		
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		
		if ($row['incomplete_missions'] > 0)
			$this->incomplete_missions = $row['incomplete_missions'];	
		else
			$this->incomplete_missions = 0;			
	}
	
	function set_possible_missions()
	{
		$this->possible_missions = $this->completed_missions + $this->incomplete_missions;
	}
	
	function set_medal_awarded($subject_id, $user_id, $medal_ord)
	{
		$sql = "SELECT count(*) AS awarded FROM medal_marks WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id . " AND medal_ord=" . $medal_ord;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		
		if ($row['awarded'] > 0) 
			$this->medal_awarded = true;
		else
			$this->medal_awarded = false;
	}
	
	function set_show_user($show_user)
	{
		$this->show_user = $show_user;
	}
	
	function set_medal_name($medal_name)
	{
		$this->medal_name = $medal_name;
	}
	
	function get_user_tracks($start_date, $end_date)
	{
		$sql = "SELECT * FROM user_tracks AS ut WHERE user_id = " . $this->user_id . " ";
		$sql = $sql . "AND enrolled = 1 ";
		$sql = $sql . "AND subject_id != 91";
		
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$user_track = new user_track($row);
			$user_track->get_subject_info();
			
			$user_track->get_completed_missions();
			$user_track->get_incomplete_missions($this->school_type_id, $start_date, $end_date);
			$user_track->set_possible_missions();
			
			$user_track->get_subject_medals();
			
			array_push($this->user_tracks, $user_track);
		}
	}
}
?>