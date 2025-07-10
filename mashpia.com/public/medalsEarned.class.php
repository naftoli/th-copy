<?php
require_once 'class.earned.php';

class MedalsEarned extends Earned
{
	private $subjects;
	private $medals;
	private $report;
	
	public function __construct() {
		parent::__construct();
		$this->subjects = array();
		$this->medals = array();
		$this->report = array();
	}
	
	public function getReport() {
		if (empty($this->report)) {
			$this->createReport();
		}
		return $this->report;
	}
	
	private function createReport() {
		$this->setSubjects();
		$this->setMedals();

		foreach ($this->dates as $year => $date) {
			foreach ($this->subjects as $subject => $subject_id) {
				if (is_array($subject_id)) {
					$s = implode(',', $subject_id);
				} else {
					$s = (string)$subject_id;
				}
				foreach ($this->medals as $medal_ord => $medal_name) {
					//get sum of medals earned per year
					$sql = "select count(user_id) as total from medal_marks 
							where subject_id in (" . $s . ")
							and medal_ord = $medal_ord 
							and date_awarded >= $date";
					if (isset($this->dates[$year+1])) 
						$sql .= " and date_awarded < " . $this->dates[$year+1];
					$result = mysql_query($sql);
					$row = mysql_fetch_assoc($result);
					$this->report[$year][$s][$medal_ord] = $row['total'];
				}
			}
		}
	}
	
	private function setSubjects() {
		$this->subjects = array(
			'Yomei Depagra / Yom Tov'		=>	array(40,94), 
			'WWTC'							=>	1, 
			'Mivtzoim / Assisting Others'	=> 	array(12,93), 
			'Sefer Hamitzvos'				=>	21, 
			'Avos Ubanim'					=>	41, 
			'Cheshbon Hanefesh'				=>	45, 
			'Veholachto Bidrochov'			=>	42, 
			'Tefillah'						=>	4, 
			'Niggunim / Jewish Songs'		=> array(13,92), 
			'Hiskashrus'					=>	16, 
			'Tanya Baal Peh'				=>	27, 
			'Chitas'						=>	90,
			'Brius Haguf'					=>	100,
			'Old Sefer Hamitzvos'			=>	106
		);
	}
	
	private function setMedals() {
		$sql = "select medal_ord, medal_name from medals order by medal_ord";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->medals[$row['medal_ord']] = $row['medal_name'];
		}
	}
	
	public function getMedals() {
		return $this->medals;
	}
	
	public function getSubjects() {
		return $this->subjects;
	}
}
?>