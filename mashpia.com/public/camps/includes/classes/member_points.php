<?php
class user {
	public member_points_id;
	public user_id;
	public points;
	public points_date;	 
	
	function __construct($row){
		$this->member_points_id = $row['user_id'];
		$this->user_id = $row['user_id'];
		$this->points = $row['user_id'];
		$this->points_date = $row['user_id'];	 
	}	
}
?>