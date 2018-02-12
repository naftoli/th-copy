<?php
class medal_subject 
{
	public $subject_id;
	public $medal_ord;
	public $medal_on_image_id;
	public $medal_off_image_id;
	public $medal_photo_id;
	public $missions_required;
	public $profile_photo_id;
	
	function __construct($row)
	{
		$this->subject_id = $row['subject_id'];
		$this->medal_ord = $row['medal_ord'];
		$this->medal_on_image_id = $row['medal_on_image_id'];
		$this->medal_off_image_id = $row['medal_off_image_id'];
		$this->medal_photo_id = $row['medal_photo_id'];
		$this->missions_required = $row['missions_required'];
		$this->profile_photo_id = $row['profile_photo_id'];
	}
}
?>