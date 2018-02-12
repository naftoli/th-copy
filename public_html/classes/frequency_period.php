<?
class frequency_period {
	public $frequency_period_id;
	public $frequency_period_name;
	
	function __construct($row) {
		$this->frequency_period_id = $row["frequency_period_id"];
		$this->frequency_period_name = $row["frequency_period_name"];
	}	
}
?>