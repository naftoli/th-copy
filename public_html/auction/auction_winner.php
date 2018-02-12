<?
class auction_winner
{
	public $first;
	public $last;
	public $school_name;
	public $class_grade;
	public $class_sub;
	public $class_name;
	public $prize_id;
	public $prize_name;
	public $prize_points;
	
	function __construct($row)
	{
		$this->first = $row['first'];
		$this->last = $row['last'];
		$this->school_name = $row['school_name'];
		$this->prize_id = $row['prize_id'];
		$this->prize_name = $row['prize_name'];
		$this->prize_points = $row['prize_points'];
		$this->class_grade = $row['class_grade'];
		$this->class_sub = $row['class_sub'];
		
		if ($this->class_sub != "")
			$this->class_name = $this->class_grade . " - " . $this->class_sub;
		else
			$this->class_name = $this->class_grade;
	}
}
?>