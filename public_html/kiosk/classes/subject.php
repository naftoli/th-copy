<?php
class subject 
{
	public $subject_id;
	public $subject_name;
	public $inst_id;
	public $subject_type;
	public $subject_ord;
	public $subject_image_id;
	public $subject_gold_image_id;
	public $subject_black_image_id;
	public $subject_slogan;
	public $subject_description;
	public $subject_commitments;
	
	function __construct($row) 
	{
		$this->subject_id = $row['subject_id'];
		$this->subject_name = $row['subject_name'];
		$this->inst_id = $row['inst_id'];
		$this->subject_type = $row['subject_type'];
		$this->subject_ord = $row['subject_ord'];
		$this->subject_image_id = $row['subject_image_id'];
		$this->subject_gold_image_id = $row['subject_gold_image_id'];
		$this->subject_black_image_id = $row['subject_black_image_id'];
		$this->subject_slogan = $row['subject_slogan'];
		$this->subject_description = $row['subject_description'];
		$this->subject_commitments = $row['subject_commitments'];
	}
		
}
?>