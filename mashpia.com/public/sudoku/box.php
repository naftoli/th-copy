<?
class box
{
	public $cells = array();
	public $boxes = array(array(0,1,2,9,10,11,18,19,20), array(3,4,5,12,13,14,21,22,23), array(6,7,8,15,16,17,24,25,26), array(27,28,29,36,37,38,45,46,47), array(30,31,32,39,40,41,48,49,50), array(33,34,35,42,43,44,51,52,53), array(54,55,56,63,64,65,72,73,74), array(57,58,59,66,67,68,75,76,77), array(60,61,62,69,70,71,78,79,80));
	
	public function __construct($box_no) 
	{
		for ($cell_no = 0; $cell_no < 9; $cell_no++)
		{
			$cell_number = $this->boxes[$box_no][$cell_no];
			$this->cells[$cell_no] = array($cell_number, true, true, true, true, true, true, true, true, true);
		}
	}	
	
}
?>
