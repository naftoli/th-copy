<?php
class medal 
{
	public $medal_ord;
	public $medal_name;
	public $missions_required;
	public $show_medal;
	public $board;

	public $medal_subject;
	
	function __construct($row)
	{
		$this->medal_ord = $row['medal_ord'];
		$this->medal_name = $row['medal_name'];
	}
	
	function set_missions_required($missions_required)
	{
		$this->missions_required = $missions_required;
	}
	
	function set_show_medal($show_medal)
	{
		$this->show_medal = $show_medal;
	}
	
	function set_board()
	{
		$sql = "select medal_name from medals where medal_ord = " . ($this->medal_ord + 1);
		$result = mysql_query($sql);
		$row = mysql_fetch_row($result);
		$this->board = $row[0];
	}
	
	function get_medal_subject($subject_id)
	{
		$sql = "SELECT * FROM medals_subjects WHERE medal_ord=" . $this->medal_ord. " AND subject_id=" . $subject_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->medal_subject = new medal_subject($row);
	}

}
?>