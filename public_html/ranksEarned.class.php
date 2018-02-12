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
	
	public function getRanks() {
		return $this->ranks;
	}
	
	public function getDates() {
		return $this->dates;
	}
}
?>