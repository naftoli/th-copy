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
	
	public function __construct($row){
		$this->class_id = $row["class_id"];
		$this->school_id = $row["school_id"];
		$this->class_grade = $row["class_grade"];
		$this->class_sub = $row["class_sub"];
		$this->class_teacher = $row["class_teacher"];
		$this->default_level = $row["default_level"];
		$this->gender_view = $row["gender_view"];
		$this->class_era = $row["class_era"];
	}
	
}
?>