<?php
class school_add_on {
  	public $school_add_on_id;
	public $title;
	public $line_1;
	public $description_1;
	public $line_2;
	public $description_2;
	public $value;
	public $price;
	
	public function __construct($row){
		$this->school_add_on_id = $row["school_add_on_id"];
		$this->title = $row["title"];
		$this->line_1 = $row["line_1"];
		$this->description_1 = $row["description_1"];
		$this->line_2 = $row["line_2"];
		$this->description_2 = $row["description_2"];
		$this->value = $row["value"];
		$this->price = $row["price"];
	}
	
}
?>