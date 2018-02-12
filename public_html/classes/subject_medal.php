<?php
class subject_medal {
	public $subject_id;
	public $medal_ord; 
	public $medal_on_image_id;
	public $medal_off_image_id;
	public $medal_photo_id;
	public $missions_required;
	public $profile_photo_id;
	public $medal_name;
	
	function __construct($row)
	{
		$this->subject_id = $row['subject_id'];
		$this->medal_ord = $row['medal_ord'];
		$this->missions_required = $row['missions_required'];
		$this->medal_name = $row['medal_name'];
	}		
}
?>