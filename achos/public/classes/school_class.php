<?php
class school_class {
	public $class_id;
	public $school_id;
	public $class_grade;
	public $class_sub;
	public $class_teacher;
	public $default_level;
	public $gender_view;
	public $class_era;
	
	public $number_of_soldiers;
	public $points;
	
	public $ranks = array();
	public $students = array();
	
	public function __construct($row) {
		$this->class_id = $row["class_id"];
		$this->school_id = $row["school_id"];
		$this->class_grade = $row["class_grade"];
		$this->class_sub = $row["class_sub"];
		$this->class_teacher = $row["class_teacher"];
		$this->default_level = $row["default_level"];
		$this->gender_view = $row["gender_view"];
		$this->class_era = $row["class_era"];
	}
	
	function get_number_of_soldiers()
	{
		$sql = "SELECT count(*) AS number_of_soldiers FROM users WHERE school_id=" . $this->school_id . " AND class_id=" . $this->class_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->number_of_soldiers = $row['number_of_soldiers'];
	}
	
	function get_points()
	{
		$sql = "JOIN users USING (user_id) WHERE school_id=" . $this->school_id . " AND class_id=" . $this->class_id;
		$this->points = number_format( mysql_result( mq( totalMarks($sql)), 0), 0);
	}
	
	function get_class_rank_totals()
	{
		$ranks = array();
		$sql = "SELECT ranks.*, COUNT(*) num ";
		$sql = $sql . "FROM ranks ";
		$sql = $sql . "LEFT JOIN rank_marks USING (rank_ord) ";
		$sql = $sql . "WHERE rank_ord <= (SELECT MAX(rank_ord) FROM rank_marks) ";
		$sql = $sql . "GROUP BY rank_ord, rank_name, rank_color ";
		$sql = $sql . "ORDER BY rank_ord";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$rank = new rank($row);
			array_push($ranks, $rank);
		}		
	
		$sql = "SELECT user_id, rank_ord ";
		$sql = $sql . "FROM users ";
		$sql = $sql . "JOIN rank_marks USING (user_id) ";
		$sql = $sql . "WHERE school_id=" . $this->school_id . " AND class_id=" . $this->class_id . " ";
		
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			foreach ($ranks as $rank)
			{
				if ($rank->rank_ord == $row['rank_ord'])
				{
					$rank->total_students++;
				}
			}
		}
		
		$this->ranks = $ranks;		
	}
	
	function get_students()
	{
		$sql = "SELECT * FROM users WHERE school_id=" . $this->school_id . " AND class_id=" . $this->class_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			$student = new user($row);
			array_push($this->students, $student);
		}
	}
	
	function set_ranks($ranks) {
		$this->ranks = $ranks;
	}
	
}
?>