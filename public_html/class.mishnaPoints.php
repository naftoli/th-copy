<?
class MishnaPoints {
	private $user;
	private $grade;
	private $school;
	private $type;
	
	public function __construct( $user_id, $type ) {
		$this->user = $user_id;
		$this->getSchoolClassInfo();
		$this->type = $type;
	}
		
	private function getSchoolClassInfo() {
		$sql = "select school_id, class_id from users where user_id = " . $this->user;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->school = $row['school_id'];
		$this->grade = $row['class_id'];
	}
	
	public function calculatePPL() {
		$points = 0;
		
		$select = '';
		switch ($this->type) {
			case 'points':
				$select = "points";
				break;
			case 'p_points':
				$select = "points, p_points";
				break;
			case 'm_points':
				$select = "p_points, m_points";
				break;
			case 's_points':
				$select = "m_points, s_points";
				break;
			case 'shas_points':
				$select = "s_points, shas_points";
				break;
		}
		//find out user / grade / school (specifically in this order) point per line
		$sql = "select " . $select . " from mishna_ppl where user_id = " . $this->user;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
		} else {
			$sql = "select " . $select . " from mishna_ppl where class_id = " . $this->grade . " and user_id is null";
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
			} else {
				$sql = "select " . $select . " from mishna_ppl where school_id = " . $this->school . " 
						and class_id is null and user_id is null";
				//echo $sql . "<br />";
				$result = mysql_query($sql);
				if (mysql_num_rows($result) == 0) {
					throw new Exception('Points per line has not been setup.');
				}
				$row = mysql_fetch_assoc($result);
			}
		}
		
		switch ($this->type) {
			case 'points':
				$points = $row['points'];
				break;
			case 'p_points':
				$points = $row['p_points'] - $row['points'];
				break;
			case 'm_points':
				$points = $row['m_points'] - $row['p_points'];
				break;
			case 's_points':
				$points = $row['s_points'] - $row['m_points'];
				break;
			case 'shas_points':
				$points = $row['shas_points'] - $row['s_points'];
				break;
		}
		
		return $points;
	}
	
	public static function setPoints( $school, $grade, $user, $points, $type = 'points' ) {
		$sql = "select " . $type . " from mishna_ppl 
				where school_id = " . $school . " 
				and class_id " . (empty($grade) ? "is null" : " = " . $grade) . " 
				and user_id " . (empty($user) ? "is null" : " = " . $user);
		//echo $sql;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$sql2 = "update mishna_ppl 
					set " . $type . " = " . $points . "  
					where school_id = " . $school . " 
					and class_id " . (empty($grade) ? "is null" : " = " . $grade) . " 
					and user_id " . (empty($user) ? "is null" : " = " . $user);		
		} else {
			$sql2 = "insert into mishna_ppl 
					set " . $type . " = " . $points . ",  
					school_id = " . $school;
			if ($grade > 0) {
				$sql2 .= ", class_id = " . $grade;
			}
			if ($user > 0) {
				$sql2 .= ", user_id = " . $user;
			}
		}
		//echo $sql2;
		if (mysql_query($sql2)) {
			return 1;
		} else {
			return 0;
		}
	}
}
