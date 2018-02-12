<?php
class cell 
{
	public $row_no;
	public $column_no;
	public $box_no;
	public $value;	
	public $background_color;
	public $inner_html;
	public $solved;
	public $spans = array("", "", "", "", "", "", "", "", "");
	public $class_name;
	public $single;
	public $box_nos = array(0,0,0,1,1,1,2,2,2,0,0,0,1,1,1,2,2,2,0,0,0,1,1,1,2,2,2,3,3,3,4,4,4,5,5,5,3,3,3,4,4,4,5,5,5,3,3,3,4,4,4,5,5,5,6,6,6,7,7,7,8,8,8,6,6,6,7,7,7,8,8,8,6,6,6,7,7,7,8,8,8);
	
	public function __construct($cell_no, $value) 
	{
		$this->row_no = floor($cell_no / 9);
		$this->column_no = floor($cell_no % 9);
		$this->box_no = $this->box_nos[$cell_no];
		
		if ($value == "")
			$this->value = "123456789";
		else
			$this->value = $value;
		
		$remainder = $this->box_no % 2;		
		if ($remainder == 0) 
			$this->background_color = "#CCCCFF";
		else
			$this->background_color = "#CCFFCC";		
			
		$this->span = false;
		$this->single = false;
	}

	public function set_inner_html($cell_no) {
	
		if (strlen($this->value) == 1) 			
		{
			if (substr($_SESSION["puzzle"], $cell_no, 1) != ";")
				$this->inner_html = "<table><tr><td></tr></td><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<span style='color:red;'>" . $this->value . "</span></td></tr><tr><td></td></tr></table>";
			else
				$this->inner_html = "<table><tr><td></tr></td><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<span style='color:blue;'>" . $this->value . "</span></td></tr><tr><td></td></tr></table>";				
		}
		else 
		{
			$this->inner_html = "<table cellspacing='0' align='center' id='table_" . $cell_no . "' name='table_" . $cell_no . "'><tbody><tr>";
			
			// ***** Numbers 1 to 3 ***** //
			$this->inner_html = $this->inner_html . "<tr>";
			for ($cntr = 1; $cntr < 4; $cntr++) 
			{
				if ($this->spans[$cntr - 1] != "")
					$background_color = " background-color:" . $this->spans[$cntr - 1] . "; ";
				else
					$background_color = "";
			
				$needle = $cntr . "";
				if (strpos($this->value, $needle) > -1)
					$this->inner_html = $this->inner_html . "<td style='text-align:center; width:12px; font-size:7pt;" . $background_color . "'>" . $cntr . "</td>";
				else
					$this->inner_html = $this->inner_html . "<td style='text-align:center; width:12px; font-size:7pt;'>&nbsp;</td>";
			}
			$this->inner_html = $this->inner_html . "</tr>";
			// ***** Numbers 1 to 3 ***** //
			
			// ***** Numbers 4 to 6 ***** //
			$this->inner_html = $this->inner_html . "<tr>";
			for ($cntr = 4; $cntr < 7; $cntr++) 
			{
				if ($this->spans[$cntr - 1] != "")
					$background_color = " background-color:" . $this->spans[$cntr - 1] . "; ";
				else
					$background_color = "";
			
				$needle = $cntr . "";
				if (strpos($this->value, $needle) > -1)
					$this->inner_html = $this->inner_html . "<td style='text-align:center; width:12px; font-size:7pt;" . $background_color . "'>" . $cntr . "</td>";
				else
					$this->inner_html = $this->inner_html . "<td style='text-align:center; width:12px; font-size:7pt;'>&nbsp;</td>";
			}
			$this->inner_html = $this->inner_html . "</tr>";
			// ***** Numbers 4 to 6 ***** //
			
			// ***** Numbers 7 to 9 ***** //
			$this->inner_html = $this->inner_html . "<tr>";
			for ($cntr = 7; $cntr < 10; $cntr++) 
			{
				if ($this->spans[$cntr - 1] != "")
					$background_color = " background-color:" . $this->spans[$cntr - 1] . "; ";
				else
					$background_color = "";
					
				$needle = $cntr . "";
				if (strpos($this->value, $needle) > -1)
					$this->inner_html = $this->inner_html . "<td style='text-align:center; width:12px; font-size:7pt;" . $background_color . "'>" . $cntr . "</td>";
				else
					$this->inner_html = $this->inner_html . "<td style='text-align:center; width:12px; font-size:7pt;'>&nbsp;</td>";
			}			
			$this->inner_html = $this->inner_html . "</tr>";			
			// ***** Numbers 7 to 9 ***** //
			
			$this->inner_html = $this->inner_html . "</tr></tbody></table>";
		}
		
	}
	
	
}
?>