<?php
class RanksEarned extends Earned
{
	private $subjects;
	private $medals;
	private $report;
	
	public function __construct() {
		parent::__construct();
		$this->ranks = array();
		$this->report = array();
	}
	
	public function getReport() {
		if (empty($this->report)) {
			$this->createReport();
		}
		return $this->report;
	}
	
	private function createReport() {
		$this->setRanks();

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
	
	public function getRanks() {
		return $this->ranks;
	}
}
?>