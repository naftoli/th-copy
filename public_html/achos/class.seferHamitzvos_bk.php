<?php
/*
 * what does the class need to do?
 * we need a way of marking sefer hamitzvos missions for children
 * when a child learns all the mitzvos associated with that mission,
 * the child gets the mission marked as done and gets his/her rank checked for update
 * 
 */

class seferHamitzvos {
        
    private $mitzvos;
	private $subject_id;
	private $user_id;
    
    public function __construct($user_id) {
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
		$this->subject_id = 21;
		$this->user_id = $user_id;
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
	
	public function updateMedal() {
		
	}

}
?>