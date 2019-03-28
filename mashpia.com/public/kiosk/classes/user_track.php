<?php
class user_track 
{
	public $user_id;
	public $subject_id;
	public $track_id;
	public $level; 
	public $enrolled;	
	
	function __construct($row) 
	{
		$this->user_id = $row['user_id'];
		$this->subject_id = $row['subject_id'];
		$this->track_id = $row['track_id'];
		$this->level = $row['level']; 
		$this->enrolled = $row['enrolled'];		
	}
	
}
?>