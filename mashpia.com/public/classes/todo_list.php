<?
class todo_list
{
	public $todo_id;
	public $recip;
	public $school_id;
	public $recip_id;
	public $todo_text;
	public $todo_priority;
	public $category_id;
	public $todo_due_date;
	public $todo_file_id;
	public $todo_url;
	public $visibility;
	public $creation_date;
	
	public $category_name;
	public $mark_date;
	
	function __construct($row) 
	{
		$this->todo_id = $row['todo_id'];
		$this->recip = $row['recip'];
		$this->school_id = $row['school_id'];
		$this->recip_id = $row['recip_id'];
		$this->todo_text = $row['todo_text'];
		$this->todo_priority = $row['todo_priority'];
		$this->category_id = $row['category_id'];
		$this->todo_due_date = $row['todo_due_date'];
		$this->todo_file_id = $row['todo_file_id'];
		$this->todo_url = $row['todo_url'];
		$this->visibility = $row['visibility'];
		$this->creation_date = $row['creation_date'];	
	}
	
	function set_category_name($category_name)
	{
		$this->category_name = $category_name;
	}
	
	function set_mark_date($mark_date)
	{
		$this->mark_date = $mark_date;
	}
	
	
} 
?>