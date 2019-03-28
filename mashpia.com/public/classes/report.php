<?
class report {
	public $report_id;
	public $report_name;
	public $report_type;
	public $creation_date;
	public $start_date;
	public $end_date;
	public $visibility;
	
	function __construct($row) {
		$this->report_id = $row["report_id"];
		$this->report_name = $row["report_name"];
		$this->report_type = $row["report_type"];
		$this->creation_date = $row["creation_date"];
		$this->start_date = $row["start_date"];
		$this->end_date = $row["end_date"];
		$this->visibility = $row["visibility"];	
	}
	
} 
?>