<?php
class row
{	
	public $cells = array();
	
	public function __construct($row_no) 
	{
		for ($cell_no = 0; $cell_no < 9; $cell_no++)
		{
			$cell_number = ($row_no * 9) + $cell_no;
			$this->cells[$cell_no] = array($cell_number, true, true, true, true, true, true, true, true, true, "");
		}
	}	
	
}
?>