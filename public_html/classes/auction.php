<?
class auction 
{
	public $auction_id;
	public $auction_name;
	public $school_id;
	public $auction_points_start_date;
	public $auction_date;
	public $auction_points_trigger_date;
	public $auction_run_date;
	public $auction_message;
	public $max_prize_points;
	public $auction_ran;
	public $approved;
	
	public $total_students;
	
	function __construct($row)
	{
		$this->auction_id = $row['auction_id'];
		$this->auction_name = $row['auction_name'];
		$this->school_id = $row['school_id'];
		$this->auction_points_start_date = $row['auction_points_start_date'];
		$this->auction_date = $row['auction_date'];
		$this->auction_points_trigger_date = $row['auction_points_trigger_date'];
		$this->auction_run_date = $row['auction_run_date'];
		$this->auction_message = $row['auction_message'];
		$this->max_prize_points = $row['max_prize_points'];
		$this->auction_ran = $row['auction_ran'];
		$this->approved = $row['approved'];	
	}
	
	public function set_total_students($total_students)
	{
		$this->total_students = $total_students;
	}
	
} 
?>