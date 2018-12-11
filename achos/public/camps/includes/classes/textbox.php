<?php
class textbox {
	public $boxes = array(array(0, 1, 2, 9, 10, 11, 18, 19, 20), array(3, 4, 5, 12, 13, 14, 21, 22, 23), array(6, 7, 8, 15, 16, 17, 24, 25, 26), array(27, 28, 29, 36, 37, 38, 45, 46, 47), array(30, 31, 32, 39, 40, 41, 48, 49, 50), array(33, 34, 35, 42, 43, 44, 51, 52, 53), array(54, 55, 56, 63, 64, 65, 72, 73, 74), array(57, 58, 59, 66, 67, 68, 75, 76, 77), array(60, 61, 62, 69, 70, 71, 78, 79, 80));
	public $cell_number;
	public $row_number;
	public $column_number;
	public $box_number;
	public $background_color;
	public $value;
	public $inner_html;
	public $solved;
	public $class_name;
	public $color;
	public $length;
	public $span;
	
	public function __construct($cell_number) {
		$this->cell_number = $cell_number;
		$this->row_number = floor($cell_number / 9);
		$this->column_number = floor($cell_number % 9);
		$this->span = false;
		
		for ($box_no = 0; $box_no < 9; $box_no++) {
			for ($textbox_number = 0; $textbox_number < 9; $textbox_number++) {
				if ($cell_number == $this->boxes[$box_no][$textbox_number]) {
					$this->box_number = $box_no;
					break;
				}
			}
		}
		
		$remainder = $this->box_number % 2;		
		if ($remainder == 0) 
			$this->background_color = "#CCCCFF";
		else
			$this->background_color = "#CCFFCC";
		
		$this->length = 9;
	}
	
	public function set_value_one($value) {
		$this->value = $value;
		if (strlen($this->value) == 1) {
			$this->solved = true;
			$this->color = "red";
		}
		else {
			$this->solved = false;
			$this->color = "blue";
		}	
		
		$this->length = strlen($this->value);		
	}
	
	public function set_value_two($value) {
		$this->value = $value;
		if (strlen($this->value) == 1) {
			$this->solved = true;
		}
		else {
			$this->solved = false;
		}
		
		$this->length = strlen($this->value);
	}
	
	
	public function set_inner_html() {
		if ($this->span == false) {
			if ($this->solved == true) {
				$this->class_name = "solved";
				$this->inner_html = $this->value;
			}
			else {
				$this->inner_html = "<table cellspacing='0' align='center' class='candtb' id='cl00'><tbody><tr>";
				
				$this->inner_html = $this->inner_html . "<tr>";
				for ($cntr = 1; $cntr < 4; $cntr++) {
					$needle = $cntr . "";
					if (strpos($this->value, $needle) > -1)
						$this->inner_html = $this->inner_html . "<td>" . $cntr . "</td>";
					else
						$this->inner_html = $this->inner_html . "<td>&nbsp;</td>";
				}
				$this->inner_html = $this->inner_html . "</tr>";
				
				$this->inner_html = $this->inner_html . "<tr>";
				for ($cntr = 4; $cntr < 7; $cntr++) {
					$needle = $cntr . "";
					if (strpos($this->value, $needle) > -1)
						$this->inner_html = $this->inner_html . "<td>" . $cntr . "</td>";
					else
						$this->inner_html = $this->inner_html . "<td>&nbsp;</td>";
				}
				$this->inner_html = $this->inner_html . "</tr>";
				
				$this->inner_html = $this->inner_html . "<tr>";
				for ($cntr = 7; $cntr < 10; $cntr++) {
					$needle = $cntr . "";
					if (strpos($this->value, $needle) > -1)
						$this->inner_html = $this->inner_html . "<td>" . $cntr . "</td>";
					else
						$this->inner_html = $this->inner_html . "<td>&nbsp;</td>";
				}			
				$this->inner_html = $this->inner_html . "</tr>";			
				
				$this->inner_html = $this->inner_html . "</tr></tbody></table>";
			}
		}
	}
	
	public function eliminate_value($value) {
		$new_value = "";
		
		for ($cntr = 0; $cntr < strlen($this->value); $cntr++) {
			$number = substr($this->value, $cntr, 1);
			if ($number != $value)
				$new_value = $new_value . $number;
		}
		
		$this->value = $new_value;
		if (strlen($this->value) == 1) {
			$this->solved = true;
			$this->color = "blue";
			return 1;
		}
		else {
			$this->solved = false;
			return 0;
		}
		
		$this->length = strlen($this->value);
	}
	
	public function insert_span($number) {				
		$this->inner_html = "<table cellspacing='0' align='center' class='candtb' id='cl00'><tbody><tr>";
			
		$this->inner_html = $this->inner_html . "<tr>";
		for ($cntr = 1; $cntr < 4; $cntr++) {
			$needle = $cntr . "";
			if (strpos($this->value, $needle) > -1) {
				if ($cntr == $number) {
					$this->span = true;
					$this->inner_html = $this->inner_html . "<td><span style='background-color:yellow;'>" . $cntr . "</span></td>";
				}
				else {
					$this->inner_html = $this->inner_html . "<td>" . $cntr . "</td>";
				}
			}
			else {
				$this->inner_html = $this->inner_html . "<td>&nbsp;</td>";
			}
		}
		$this->inner_html = $this->inner_html . "</tr>";
			
		$this->inner_html = $this->inner_html . "<tr>";
		for ($cntr = 4; $cntr < 7; $cntr++) {
			$needle = $cntr . "";
			if (strpos($this->value, $needle) > -1) {
				if ($cntr == $number) {
					$this->inner_html = $this->inner_html . "<td><span style='background-color:yellow;'>" . $cntr . "</span></td>";
					$this->span = true;
				}
				else {
					$this->inner_html = $this->inner_html . "<td>" . $cntr . "</td>";
				}
			}
			else {
				$this->inner_html = $this->inner_html . "<td>&nbsp;</td>";
			}
		}
		$this->inner_html = $this->inner_html . "</tr>";
			
		$this->inner_html = $this->inner_html . "<tr>";
		for ($cntr = 7; $cntr < 10; $cntr++) {
			$needle = $cntr . "";
			if (strpos($this->value, $needle) > -1) {
				if ($cntr == $number) {
					$this->inner_html = $this->inner_html . "<td><span style='background-color:yellow;'>" . $cntr . "</span></td>";
					$this->span = true;
				}
				else {
					$this->inner_html = $this->inner_html . "<td>" . $cntr . "</td>";
				}
			}
			else {
				$this->inner_html = $this->inner_html . "<td>&nbsp;</td>";
			}
		}			
		$this->inner_html = $this->inner_html . "</tr>";
			
		$this->inner_html = $this->inner_html . "</tr></tbody></table>";
		
		$this->eliminate_value($number);
	}
	
}
?>