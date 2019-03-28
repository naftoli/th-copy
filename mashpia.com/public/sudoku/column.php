<?
class column
{
	public $cells = array();
	
	public function __construct($column_no) 
	{
		for ($cell_no = 0; $cell_no < 9; $cell_no++)
		{
			$cell_number = ($cell_no * 9) + $column_no;
			$this->cells[$cell_no] = array($cell_number, true, true, true, true, true, true, true, true, true);
		}
	}	
	
}
?>