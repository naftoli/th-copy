<?php
class subject {
	public $subject_id;
	public $subject_name;
	
	public $subject_medals = array();
	public $subject_users = array();
	
	public $show_subject;
	
	public $user_track;
	
	function __construct($row)
	{
		$this->subject_id = $row['subject_id'];
		$this->subject_name = $row['subject_name'];
	}
	
	// ****************************** ADMIN MISSIONS REPORT ****************************** //
	function set_show_subject($show_subject)
	{
		$this->show_subject = $show_subject;
	}
	
	function get_subject_medals()
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM medals_subjects AS ms ";
		$sql = $sql . "JOIN medals AS m USING (medal_ord) ";
		$sql = $sql . "WHERE ms.subject_id=" . $this->subject_id . " ";
		$sql = $sql . "ORDER BY ms.medal_ord";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$missions_required = $missions_required + $row['missions_required'];
			$subject_medal = new medal($row);
			$subject_medal->set_missions_required($missions_required);
			array_push($this->subject_medals, $subject_medal);
		}
	}
	
	function get_subject_users($school_id, $start_date, $end_date)
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM users AS u ";
		$sql = $sql . "JOIN user_tracks AS ut ON (ut.user_id=u.user_id AND ut.subject_id=" . $this->subject_id . ") ";
		$sql = $sql . "JOIN date_tasks_missions AS dtm ON (dtm.school_type_id=u.school_type_id AND dtm.subject_id=" . $this->subject_id . " AND dtm.track_id=ut.track_id AND dtm.level=ut.level) ";
		$sql = $sql . "WHERE u.school_id=" . $school_id . " ";
		$sql = $sql . "AND dtm.start_date < "  . $end_date . " ";
		$sql = $sql . "AND dtm.end_date > "  . $start_date . " ";
		$sql = $sql . "GROUP BY u.user_id ";
		$sql = $sql . "ORDER BY u.first, u.last ";
		
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 			
		{
			$subject_user = new subject_user($row);
			$subject_user->set_show_user_subject(false);
			$subject_user->get_completed_missions($this->subject_id);
			$subject_user->get_incomplete_missions($this->subject_id, $row['school_type_id'], $start_date, $end_date);
			$subject_user->set_possible_missions();
			array_push($this->subject_users, $subject_user);
		}
	}
	
	// ****************************** ADMIN MISSIONS REPORT ****************************** //
	
	function get_user_track($user_id)
	{
		$sql = "SELECT * FROM user_tracks WHERE user_id=" . $user_id . " AND subject_id=" . $this->subject_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->user_track = new user_track($row);		
	}
	
}
?>