<?php
/*
 * what does the class need to do?
 * we need a way of marking sefer hamitzvos missions for children
 * when a child learns all the mitzvos associated with that mission,
 * the child gets the mission marked as done and gets his/her rank checked for update
 * 
 */

class seferHamitzvos {
        
    public $mitzvos;
	public $subject_id;
	public $user_id;
    public $missions = array();
	public $mission_numbers = array();
	public $Connection;
	
    public function __construct(){
	
		include('connection.class.php');
		$this->Connection = new Connection();
		
		$this->subject_id = 21;
		
		for ($mno = 1; $mno <= 90; $mno++){
			array_push($this->missions, $mno);
		}
		
		$this->mission_numbers = array(
			'9-20' => array(9,10,11,12,13,14,15,16,17,18,19,20),
			'31-40' => array(31,32,33,34,35,36,37,38,39,40),
			'49-60' => array(49,50,51,52,53,54,55,56,57,58,59,60),
			'69-80' => array(69,70,71,72,73,74,75,76,77,78,79,80)
		);
		
        $this->mitzvos = array(
			'1'	=>	'1-10',
            '2'	=>	'11-18',
            '3'	=>	'19-26',
            '4'	=>	'27-46',
            '5'	=>	'47-56',
            '6'	=>	'57-75',
            '7'	=>	'76-83',
            '8'	=>	'84-86',
            '9-20'	=>	'1-86',
            '21'	=>	'87-91',
            '22'	=>	'92-115',
            '23'	=>	'116-120',
            '24'	=>	'121',
            '25'	=>	'122-138',
            '26'	=>	'139-175',
            '27'	=>	'176-191',
			
            '28'	=>	'192-200',
            '29'	=>	'201-206',
            '30'	=>	'207-231',
            '31-40'	=>	'87-231',
            '41'	=>	'232-250',
            '42'	=>	'251-274',
            '43'	=>	'275-299',
            '44'	=>	'300-311',
            '45'	=>	'312-324',
            '46'	=>	'325-348',
            '47'	=>	'349-363',
            '48'	=>	'364-376',
            '49-60'	=>	'232-376',
            '61'	=>	'377-391',
            '62'	=>	'392-402',
            '63'	=>	'403-429',
			
            '64'	=>	'430-441',
            '65'	=>	'442-452',
            '66'	=>	'453-461',
            '67'	=>	'462-478',
            '68'	=>	'479-498',
            '69-80'	=>	'377-498',
            '81'	=>	'499-503',
            '82'	=>	'392-503',
            '83'	=>	'504-523',
            '84'	=>	'524-539',
            '85'	=>	'540-567',
            '86'	=>	'568-577',
            '87'	=>	'578-595',
            '88'	=>	'596-613',
            '89'	=>	'504-613',
            '90'	=>	'499-613'
        );
		
    }

	public function get_student_missions($user, $mission_id){
	
		if ($mission_id == 0) {
			$student_missions = '<table>';
			$student_missions .= '<tr><th>Mission</th><th>Mitzvos</th><th>Done</th><th>Mission</th><th>Mitzvos</th><th>Done</th><th>Mission</th><th>Mitzvos</th><th>Done</th></tr>';
			
			$mno = 0;
			foreach($this->mitzvos as $key => $mission){
				if ($mno % 3 == 0)
					$student_missions .= '<tr>';
					
				$student_missions .= '<td>' . $key . '</td><td>' . $mission . '</td><td data="' . $user['user_id'] . '"><input class="studentcheckbox" type="checkbox" data="' . $key . '" ' . $this->get_user_mission($user['user_id'], $key) . ' /></td>';
				
				if ($mno % 3 == 2)
					$student_missions .= '</tr>';

				$mno++;
			}
		
			$student_missions .= '</table>';		
		}
		else{
		
			$student_missions = '';
			foreach($this->mitzvos as $key => $mission){
				if ($key == $mission_id){
					$student_missions .= '<tr>';
                    $student_missions .= '<td>' . $user['class_grade'] . '-' . $user['class_sub'] . '</td>';
					$student_missions .= '<td style="text-align:left; color:blue;">' . $user['first'] . ' ' . $user['last'] . '</td>';
					$student_missions .= '<td>' . $key . '</td>';
					$student_missions .= '<td>' . $mission . '</td>';
					$student_missions .= '<td data="' . $user['user_id'] . '"><input class="studentcheckbox" type="checkbox" data="' . $key . '" ' . $this->get_user_mission($user['user_id'], $key) . ' /></td>';
					$student_missions .= '<tr>';
					break;
				}
			}
			
		}	
		
		echo $student_missions;
	
	}
	
	public function get_user_mission($user_id, $mission){
	
		$mission_numbers = explode('-', $mission);
				
		if (count($mission_numbers) == 1){
			$sql = "SELECT id FROM user_sefer_hamitzvos WHERE user_id=" . $user_id . " AND mission=" . $mission;
			$query = $this->Connection->query($sql);
			$id = mysql_fetch_assoc($query);
			if ($id)
				return ' checked="checked" ';
			else
				return '';
		}
		else{
			$start = $mission_numbers[0];
			$end = $mission_numbers[1];
			
			$all_done = true;
			for ($mno = $start; $mno <= $end; $mno++){
				$sql = "SELECT id FROM user_sefer_hamitzvos WHERE user_id=" . $user_id . " AND mission=" . $mno;
				$query = $this->Connection->query($sql);
				$id = mysql_fetch_assoc($query);
				if (!$id){
					$all_done = false;
					break;
				}
			}
			if ($all_done == true)
				return ' checked="checked" ';
			else
				return '';
		}
	}
	
	public function get_left_missions(){
		$left_missions = '';
	}
	
	public function getMitzvosByMission($mission) {
		if (key_exists($mission, $this->mitzvos)) {
			return $this->mitzvos[$mission];
		} else {
			throw new Exception("No such mission number");
		}
	}
	
	public function getAllMitzvos() {
		return $this->mitzvos;
	}
}
?>