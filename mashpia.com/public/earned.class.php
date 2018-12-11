<?php
abstract class Earned
{
    protected $dates;
	protected $report;
    
    public function __construct() {
		$this->dates = array();
        $this->report = array();
	}
    
    public function getReport() {
		if (empty($this->report)) {
			$this->createReport();
		}
		return $this->report;
	}
	
	abstract protected function createReport();
    
	protected function setDates() {
		$this->dates = array(
			5769	=>	2454718, 
			5770	=>	2455082, 
			5771	=>	2455439, 
			5772	=>	2455810, 
			5773	=>	2456174, 
			5774	=>	2456531, 
			5775	=>	2456908,
			5776	=>	2457265,
			5777	=>	2457636,
			//5778	=>	2457888
            5778    =>  2457993
		);
	}
	
	public function getDates() {
		return $this->dates;
	}
}