<?php
class period {
	public $period_id;
	public $period_name;
	public $monday;
	public $tuesday;
	public $wednesday;
	public $thursday;
	public $friday;
	public $shabbos;
	public $sunday;
	
	public function __construct(){
	}
	
	public function new_period($row) { 
		$this->period_id = $row['period_id'];
		$this->period_name = $row['period_name'];
		$this->monday = $row['monday'];
		$this->tuesday = $row['tuesday'];
		$this->wednesday = $row['wednesday'];
		$this->thursday = $row['thursday'];
		$this->friday = $row['friday'];
		$this->shabbos = $row['shabbos'];
		$this->sunday = $row['sunday'];
	}
}
?>