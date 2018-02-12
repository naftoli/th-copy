<?php
class spms_school 
{
	public $school_id;
	public $school_name;
	public $display;	
	public $subjects = array();
	
	function __construct($row)
	{
		$this->school_id = $row['school_id'];
		$this->school_name = $row['school_name'];
		$this->display = false;
	}	
	
	function get_subjects($start_date, $end_date, $registered)
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM school_subjects ";
		$sql = $sql . "JOIN subjects USING (subject_id) ";
		$sql = $sql . "WHERE school_id=" . $this->school_id . " ";
		$sql = $sql . "ORDER BY subject_id";
		$query = mysql_query($sql);	
		while ($row = mysql_fetch_assoc($query)) 
		{	
			$subject = new upm_subject($row);
			$subject->get_subject_medals();
			if ($registered) $subject->get_subject_users_registered($this->school_id, 0, $start_date, $end_date);
			else $subject->get_subject_users($this->school_id, 0, $start_date, $end_date);
			array_push($this->subjects, $subject);
		}
	
	}
	
	function set_display($display)
	{
		$this->display = $display;
	}
	
}
?>