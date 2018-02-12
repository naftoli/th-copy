<?php
class medal 
{
	public $medal_ord;
	public $medal_name;
	public $medal_on_image_id;
	public $medal_off_image_id;
	
	public $missions_required;
	public $profile_photo_id;
	
	function __construct($row) 
	{
		$this->medal_ord = $row['medal_ord'];
		$this->medal_name = $row['medal_name'];
		$this->medal_on_image_id = $row['medal_on_image_id'];
		$this->medal_off_image_id = $row['medal_off_image_id'];
	}
		
	function set_subject_info($row)
	{
		$this->missions_required = $row['missions_required'];
		$this->profile_photo_id = $row['profile_photo_id'];	
	}
}
?>