<?php
require_once 'earned.class.php';

class TasksEarned extends Earned
{
	private $subjects;
	
	public function __construct() {
        parent::__construct();
		$this->subjects = array();
	}
	
	protected function createReport() {
        $this->setDates();
        $this->setSubjects();

		foreach ($this->dates as $year => $date) {
            foreach ($this->subjects as $subject => $subject_id) {
				if (is_array($subject_id)) {
					$s = implode(',', $subject_id);
				} else {
					$s = (string)$subject_id;
				}
                //get sum of tasks earned per year per subject
                $sql = "select count(date_task_id) as total from date_tasks_marks
                        join date_tasks using (date_task_id)
                        join date_tasks_missions using (date_tasks_mission_id) 
                        where subject_id in (" . $s . ")
                        and mark_date >= $date";
                if (isset($this->dates[$year+1])) 
                    $sql .= " and mark_date < " . $this->dates[$year+1];
				//echo $sql . "<br /><br />";
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                $this->report[$year][$subject] = $row['total'];
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
    
    public function getSubjects() {
		return $this->subjects;
	}
}
?>