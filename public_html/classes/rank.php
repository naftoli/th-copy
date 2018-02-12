<?
class rank 
{
	public $rank_ord;
	public $rank_name;
	public $medals_required;
	public $rank_image_id;
	public $rank_color;
	public $rank_background_image_id;
	public $prof_rank_image_id;
	
	public $total_students;
	
	function __construct($row) 
	{
		$this->rank_ord = $row['rank_ord'];
		$this->rank_name = $row['rank_name'];
		$this->medals_required = $row['medals_required'];
		$this->rank_image_id = $row['rank_image_id'];
		$this->rank_color = $row['rank_color'];
		$this->rank_background_image_id = $row['rank_background_image_id'];
		$this->prof_rank_image_id = $row['prof_rank_image_id'];
		$this->total_students = 0;
	}
	
}
?>