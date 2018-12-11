<?php
class add_on_option {
	public $add_on_option_id;
	public $description;
	
	function __construct($row) {
		$this->add_on_option_id = $row["add_on_option_id"];
		$this->description = $row["description"];
	}
		
}
?>