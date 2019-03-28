<?php
class cell 
{
	public $cell_no;
	public $row_no;
	public $column_no;
	public $box_no;
	public $entered;
	public $value;
	public $values = array();
	public $background_colors = array();
	
	public $single;
	public $rcb_single;
	
	public $box_nos = array(0,0,0,1,1,1,2,2,2,0,0,0,1,1,1,2,2,2,0,0,0,1,1,1,2,2,2,3,3,3,4,4,4,5,5,5,3,3,3,4,4,4,5,5,5,3,3,3,4,4,4,5,5,5,6,6,6,7,7,7,8,8,8,6,6,6,7,7,7,8,8,8,6,6,6,7,7,7,8,8,8);
	
	public $numbers;
	
	public function __construct($cell_no, $value, $first_time, $puzzle) 
	{
		global $action;
		
		$this->cell_no = $cell_no;
		
		$value = str_replace(" ", "", $value);	
		
		$this->row_no = floor($cell_no / 9);
		$this->column_no = floor($cell_no % 9);
		$this->box_no = $this->box_nos[$cell_no];
		
		if (strlen($value) == 1)
		{
			$this->value = $value;
			if ($first_time == true)
			{
				$this->entered = true;
			}
			else
			{
				$number = substr($puzzle, $cell_no, 1);
				if ($number != " ")
					$this->entered = true;
			}
		}
		else
		{
			if ($first_time == true)
			{
				$this->values = array(true, true, true, true, true, true, true, true, true);
			}
			else
			{
				$this->values = array(true, true, true, true, true, true, true, true, true);
				for ($number = 0; $number < 9; $number++)
				{
					$needle = ($number + 1) . "";
					$strpos = strpos($value, $needle);
					
					if ($strpos === false)
						$this->values[$number] = false;
				}
				$this->set_numbers();
			}
		}
		
		$this->background_colors = array("", "", "". "", "", "", "", "", "");
		$this->single = false;
		$this->rcb_single = false;
	}

	public function set_numbers()
	{
		for ($number = 0; $number < 9; $number++)
		{
			if ($this->values[$number] == true)
			{
				$this->numbers = $this->numbers . ($number + 1) . "";
			}
		}
	}
	
	public function draw_cell()
	{
		global $action;
		
		$cell = "";
		
		if ($this->value != "")
		{
			if ($this->entered == true)
				$color = "red";
			else
				$color = "blue";
				
			$cell = "\n\t\t\t\t\t\t<div name='values' class='solved'>";
			
			$cell = $cell . "\n\t\t\t\t\t\t\t<div name='value' style='text-align:center; font-size:14pt; color:" . $color . ";'>";
			$cell = $cell . ($this->value);
			$cell = $cell . "\n\t\t\t\t\t\t\t</div>";
			
			$cell = $cell . "\n\t\t\t\t\t\t</div>";
		}
		else
		{
			$cell = $cell . "\n\t\t\t\t\t\t<div name='values' class='unsolved'>";
			
			for ($number = 0; $number < 9; $number++)
			{
				$remainder = $number % 3;
				
				if ($number == 0 || $remainder == 0)
					$cell = $cell . "\n\t\t\t\t\t\t\t<div><div name='value' style='text-align:center; font-size:7pt;'>";

				if ($this->values[$number] == true)
				{
					if ($this->background_colors[$number] != "")
						$cell = $cell . "<span style='width:40px; background-color:" . $this->background_colors[$number] . "; color:white;'>" . ($number + 1) . "</span>";
					else
						$cell = $cell . "<span style='width:40px;'>" . ($number + 1) . "</span>";
				}
				else
				{
					$cell = $cell . "<span style='width:40px;'> </span>";
				}
				
				if ($remainder == 2 || $number == 8)
					$cell = $cell . "\n\t\t\t\t\t\t\t</div></div>";
			}
			
			$cell = $cell . "\n\t\t\t\t\t\t</div>";
		}
		
		return $cell;
	}
	
}
?>