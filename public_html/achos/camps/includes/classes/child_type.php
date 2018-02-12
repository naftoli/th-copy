<?php
class child_type {
	public $child_type_id;
	public $child_type_name;
	public $school_child_type_id;
	public $no_of_children;
	public $is_default;
	
	public function __construct($row){
		$this->child_type_id = $row['child_type_id'];
		$this->child_type_name = $row['child_type_name'];
	}

	public function get_school_child_type_id($school_id) {
		$sql = "SELECT * FROM school_child_types WHERE school_id=" . $school_id . " AND child_type_id=" . $this->child_type_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);		
		$this->school_child_type_id = $row["school_child_type_id"];
		//$this->no_of_children = $row["no_of_children"];
		if ($row["is_default"] == 1)
			$this->is_default = true;
	}
}
?>