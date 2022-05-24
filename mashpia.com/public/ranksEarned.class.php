<?php
class RanksEarned 
{
	private $subjects;
	private $medals;
	private $dates;
	private $report;
	
	public function __construct() {
		$this->ranks = array();
		$this->report = array();
		$this->dates = array();
	}
	
	public function getReport() {
		if (empty($this->report)) {
			$this->createReport();
		}
		return $this->report;
	}
	
	private function createReport() {
		$this->setRanks();
		$this->setDates();

		foreach ($this->dates as $year => $date) {
			foreach ($this->ranks as $rank_ord => $rank_name) {
					//get sum of ranks earned per year
					$sql = "select count(user_id) as total from rank_marks 
									where rank_ord = $rank_ord  
									and date_promoted >= $date"; 
					if (isset($this->dates[$year+1])) {
							$sql .= " and date_promoted < " . $this->dates[$year+1];
					}
					$result = mysql_query($sql);
					$row = mysql_fetch_assoc($result);
					$this->report[$year][$rank_ord] = $row['total'];
			}
		}
	}
	
	private function setRanks() {
		$sql = "select rank_ord, rank_name from ranks order by rank_ord";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->ranks[$row['rank_ord']] = $row['rank_name'];
		}
	}
	
	private function setDates() {
		$this->dates = array(
			5769	=>	2454718, // Sep 8, 2008 
			5770	=>	2455082, // Sep 7, 2009 
			5771	=>	2455439, // Aug 30, 2010 
			5772	=>	2455810, // Sep 5, 2011 
			5773	=>	2456174, // Sep 3, 2012 
			5774	=>	2456531, // Aug 26, 2013
			5775	=>	2456908, // Sep 7, 2014
			5776	=>	2457265, // Aug 30, 2015
			5777	=>	2457636, // Sep 4, 2016
			5778	=>	2457885, // May 11, 2017 - was changed to 2457993 at some point but put back to original
			5779	=>	2458236, // April 27, 2018
			5780	=>	2458628, // May 24, 2019,
            5781    =>  2458983, // May 13, 2020
            5782    =>  2459363, // May 28, 2021
		);
	}
	
	public function getRanks() {
		return $this->ranks;
	}
	
	public function getDates() {
		return $this->dates;
	}
}
?>