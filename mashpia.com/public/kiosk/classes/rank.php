<?php 
class rank
{
	var $rank_ord;
	var $rank_name;
	var $medals_required;
	var $rank_image_id;
	var $rank_color;
	var $rank_background_image_id;
	var $prof_rank_image_id;
	
	function __construct($row) {
		$this->rank_ord = $row['rank_ord'];
		$this->rank_name = $row['rank_name'];
		$this->medals_required = $row['medals_required'];
		$this->rank_image_id = $row['rank_image_id'];
		$this->rank_color = $row['rank_color'];
		$this->rank_background_image_id = $row['rank_background_image_id'];
		$this->prof_rank_image_id = $row['prof_rank_image_id'];
	}

}
?>