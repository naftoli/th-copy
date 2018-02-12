<?
class MaosChittim {
	private $year;
	
	public function __construct($year) {
		$this->year = $year;
	}
	
	public function getInfoFor($id, $type='school') {
		//if school, see if there's a school amount already entered
		if ($type == 'school') {
			$sql = "select raised from maos_chitim where school_id = $id";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			if ($row['raised']) {
				return $row['raised'];
			} else {
				$users = array();
				$sql = "select user_id from users where {$type}_id = $id and user_registered > 0";
				$result = mysql_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$users[] = $row['user_id'];
				}
			}
		} else {
			$users = array($id);
		}
		
		$info = $this->getInfo($users);	
		$typeInfo['pledged'] = 0;
		$typeInfo['raised'] = 0;
		if (!empty($info)) {
			foreach ($info as $user) {
				$typeInfo['pledged'] += $user['pledged'];
				$typeInfo['raised'] += $user['raised'];
			}
		}
		return $typeInfo;
	}
	
	public function getInfo($users) {
		//echo "<pre>"; print_r($users); echo "</pre>"; exit;
		if (!empty($users)) {
			if (is_array($users)) {
				$in = implode(",", $users);
			} else {
				$in = $users;
			}
			$info = array();
			$sql = "select * from maos_chitim where user_id in ($in) and year = " . $this->year;
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$info[$row['user_id']]['pledged'] = $this->checkStudentPledge($row['user_id'], $row['pledged'], $row['date']);
					$info[$row['user_id']]['raised'] = $row['raised'];
				}
			}
			
			//compare bc pledges with student pledges scanned
			foreach ($users as $user) {
				if (!isset($info[$user]['pledged'])) {
					$info[$user]['pledged'] = $this->checkStudentPledge($user, 0, 0);
					$info[$user]['raised'] = 0;
				}
			}
			
			return $info;
		} else {
			return 0;
		}
	}
	
	private function checkStudentPledge($id, $amount, $date) {
		$sql = "select sum(amount) as total, max(date) as date  
				from maos_chitim_student_pledges 
				where user_id = $id 
				and year = " . $this->year;
		//echo $sql;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			if ($row['date'] > $date) {
				return $row['total'];
			}
		}
		return $amount;
	}
	
	public function getStudentPledges($ids) {
		if (is_array($ids)) {
			$in = implode(",", $ids);
		} else {
			$in = $ids;
		}
		$pledges = array();
		$sql = "select user_id, sum(amount) as total 
				from maos_chitim_student_pledges 
				where user_id in ($in) 
				and year = " . $this->year . "
				group by user_id";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$pledges[$row['user_id']] = $row['total'];
		}
		if (!is_array($ids) && mysql_num_rows($result) == 0) {
			$pledges[$ids] = 0; //if there's no record in the database for a particular user set to 0
		}
		return $pledges;
	}
	
	public function setStudentPledge($id, $cardNum) {
		//get amount
		$sql = "select value from maos_chitim_cards where number = " . $cardNum;
		//echo $sql;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$amount = $row['value'];
		if ($amount) {
			$sql = "insert into maos_chitim_student_pledges values(null, $id, $amount, 5774, null)";
			if (mysql_query($sql)) {
				return true;
			}
		}
		return false;
	}
}
?>
