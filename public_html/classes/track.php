<?php
class track 
{
	public $track_id;
	public $track_name;
	
	function __construct($row)
	{
		$this->track_id = $row["track_id"];
		$this->track_name = $row["track_name"];
	}
		
}
?>