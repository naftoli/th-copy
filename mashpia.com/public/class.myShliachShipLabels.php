<?
require_once 'class.report.php';

class MyShliachShipLabels extends Report {
	private $admins;
	private $parents;
	protected $users;
	private $userInfo;
	private $medals;
	private $ranks;
	private $start;
	private $end;
	private $school; 
	
	public function __construct($previousStart = false, $id = null) {
        parent::__construct($previousStart);
		$this->start = $this->reportDates['start'];
        $this->end = $this->reportDates['end'];
		$this->admins = array();
		$this->parents = array();
		$this->users = array();
		$this->userInfo = array();
		$this->medals = array();
		$this->ranks = array();
		if ($id) {
			$this->school = $id;
		} else {
			$this->school = 61;
		}
		$this->setMedals();
		$this->setRanks();
		$this->setParents();
	}
	
	private function setMedals() {
		$sql = "SELECT s.subject_name, m.medal_name, u.user_id, u.last, u.first, mm.* 
				FROM medal_marks mm 
				JOIN medals m USING ( medal_ord ) 
				JOIN users u USING ( user_id ) 
				JOIN subjects s USING ( subject_id ) 
				WHERE u.user_registered > 0 
				and mm.date_awarded >= " . $this->start . "  
				AND mm.date_awarded <= " . $this->end . "  
				and u.school_id = " . $this->school . " 
				ORDER BY s.subject_id, mm.medal_ord";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->medals[$row['user_id']][] = $row;
			if (array_search($row['user_id'], $this->users) === false) {
				$this->users[] = $row['user_id'];
				$this->userInfo[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			}
		}
	}
	
	private function setRanks() {
		$sql = "SELECT r.rank_name, u.user_id, u.user_serial, u.first, u.last, rm.*  
				FROM rank_marks rm  
				JOIN ranks r USING ( rank_ord ) 
				JOIN users u USING ( user_id ) 
				WHERE u.user_registered > 0 
				and rm.date_promoted >= " . $this->start . "  
				AND rm.date_promoted <= " . $this->end . "  
				and u.school_id = " . $this->school . "   
				ORDER BY rm.rank_ord";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->ranks[$row['user_id']][] = $row;
			if (array_search($row['user_id'], $this->users) === false) {
				$this->users[] = $row['user_id'];
				$this->userInfo[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			}
		}
	}
	
	private function setParents() {
		$sql = "select * from admins a 
				join admin_auths aa using (admin_id) 
				where aa.id in (" . implode(',', $this->users) . ")  
				and aa.auth = 'user'";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->admins[$row['admin_id']] = $row;
			$this->parents[$row['admin_id']][] = $row['id'];
		}
	}
	
	public function getParents() {
		return $this->parents;
	}
	
	public function getMedals() {
		return $this->medals;
	}
	
	public function getRanks() {
		return $this->ranks;
	}
	
	public function getAdmins() {
		return $this->admins;
	}
	
	public function getUsers() {
		return $this->users;
	}
	
	public function getUserInfo() {
		return $this->userInfo;
	}
}
?>