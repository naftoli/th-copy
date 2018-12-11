<?
class auction_winner
{
	public $first;
	public $last;
	public $school_name;
	public $school_number;
	public $class_grade;
	public $class_sub;
	public $class_name;
	public $prize_id;
	public $prize_number;
	public $prize_name;
	public $prize_points;
	public $quantity;
    public $user_id;
	
	function __construct($row)
	{
		$this->first = $row['first'];
		$this->last = $row['last'];
		$this->school_name = $row['school_name'];
		$this->school_number = $row['school_number'];
		$this->prize_id = $row['prize_id'];
		$this->prize_number = $row['prize_number'];
		$this->prize_name = $row['prize_name'];
		$this->prize_points = $row['prize_points'];
		$this->class_grade = $row['class_grade'];
		$this->class_sub = $row['class_sub'];
		$this->quantity = $row['quantity'];
        $this->user_id = $row['user_id'];
		
		if ($this->class_sub != "")
			$this->class_name = $this->class_grade . " - " . $this->class_sub;
		else
			$this->class_name = $this->class_grade;
	}
}
?>