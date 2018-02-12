<?php
class institution {
	public $inst_id;
	public $inst_name;
	public $inst_logo_id;
	
	public function __construct($row){
		$this->inst_id = $row['inst_id'];
		$this->inst_name = $row['inst_name'];
		$this->inst_logo_id = $row['inst_logo_id'];
	}
}
?>