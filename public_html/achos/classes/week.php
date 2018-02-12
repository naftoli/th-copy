<?
class week
{
	public $start_date;
	public $end_date;
	public $start_date_greg;
	public $end_date_greg;
	
	public $subjects = array();
	public $missions = array();
	
	function __construct($start_date, $end_date)
	{
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		$this->start_date_greg = jdtogregorian($start_date);
		$this->end_date_greg = jdtogregorian($end_date);	
	}
}
?>