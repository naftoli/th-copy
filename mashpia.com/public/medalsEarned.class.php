<?php
class MedalsEarned 
{
	private $subjects;
	private $medals;
	private $dates;
	private $report;
	
	public function __construct() {
		$this->subjects = array();
		$this->medals = array();
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
		$this->setSubjects();
		$this->setMedals();
		$this->setDates();

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
			5778	=>	2457885, // May 11, 2017
			5779	=>	2458236, // April 27, 2018
			5780	=>	2458628, // May 24, 2019
            5781    =>  2458983, // May 13, 2020,
            5782    =>  2459363, // May 28, 2021
            5783    =>  2459719, // May 19, 2022
		);
	}
	
	public function getMedals() {
		return $this->medals;
	}
	
	public function getSubjects() {
		return $this->subjects;
	}
	
	public function getDates() {
		return $this->dates;
	}
}
?>