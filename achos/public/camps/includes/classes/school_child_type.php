<?php
class school_child_type {
	public $school_child_type_id;
	public $school_id;
	public $child_type_id;	
	public $is_default;
	public $no_of_children;
	public $child_type_name;
	
	public function __construct($row){
		$this->school_child_type_id = $row['school_child_type_id'];
		$this->school_id = $row['school_id'];
		$this->child_type_id = $row['child_type_id'];
		$this->is_default = $row['is_default'];
		//$this->no_of_children = $row['no_of_children'];
	}

	public function get_child_type_name() {
		$sql = "SELECT child_type_name FROM child_types WHERE child_type_id=" . $this->child_type_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->child_type_name = $row["child_type_name"];
	}
}
?>