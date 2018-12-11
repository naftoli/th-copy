<?
class AchosStudent {
    private $studentID;
	private $schoolID;
	private $photo;
	private $level;
	private $subject;
     
    public function __construct($id) {
        $sql = "select id from admin_auths where admin_id = " . $id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $this->studentID = $row['id'];
		
		$sql = "select school_id from users where user_id = " . $this->studentID;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->schoolID = $row['school_id'];
		
		$p = "select photo from admins where admin_id = " . $id;
        $res = mysql_query($p);
        $pRow = mysql_fetch_assoc($res);
        $this->photo = $pRow['photo']; 
		
		$level = "select subject_id, level from user_tracks where user_id = " . $this->studentID;
		$res = mysql_query($level);
		$row = mysql_fetch_assoc($res);
		$this->level = $row['level'];
		$this->subject = $row['subject_id'];
    }
    
    public function getStudentID() {
        return $this->studentID;
    } 
	
	public function getSchoolID() {
		return $this->schoolID;
	}
    
    public function getMedal() {
        $sql = "select medal_ord from medal_marks where user_id = " . $this->studentID . " order by medal_ord desc limit 1";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['medal_ord'];
    }  
	
	public function getInfo() {
		/*		
	    $sqlP = "select IFNULL(sum(points), 0) as total 
	    		from date_tasks_mission_marks 
	    		where date_tasks_mission_id > 48 
	    		and user_id = " . $this->studentID;
		
	    $resP = mysql_query($sqlP);
	    $rowP = mysql_fetch_assoc($resP);
	    $points = $rowP['total'];
		
		//for each medal there are additional points
	    $additional = array(
	        1   =>  100, 
	        2   =>  250, 
	        3   =>  450, 
	        4   =>  700, 
	        5   =>  1000, 
	        6   =>  1350, 
	        7   =>  1750, 
	        8   =>  2250, 
	        9   =>  2850, 
	        10  =>  3620
	    );
		 * 
		 */
		 
		$medal = $this->getMedal();
		//if ($medal) {
		//	$points += $additional[$medal];
		//}
		
		$points = $this->getPoints();
		//$points = '';
		
		$weekly = $this->getPoints('weekly');
		$daily = $this->getPoints('daily');
		
		return array('points' => $points, 'medal' => $medal, 'weekly' => $weekly, 'daily' => $daily);
	}
	/*
	public function getPoints( $type = 'total' ) {
		require_once 'class.achosPoints.php';
		$p = new AchosPoints( $this->studentID );
		switch ($type) {
			case 'total':
				return $p->calcPoints();
				break;
			case 'weekly':
				return $p->calcWeeklyPoints();
				break;
			case 'daily':
				return $p->calcDailyPoints();
		}
	}
	*/	
	public function getSchoolName() {
		$sql = "select * from schools where school_id = " . $this->schoolID;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		return $row['school_name'] . ' . ' . $row['school_state'];
	}
	
	public function getGrade() {
		$sql = "select * from classes where class_id = (
			select class_id from users where user_id = " . $this->studentID. ")";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		return $row['class_grade'] . ':' . $row['class_sub'];
	}
	
	public function getName() {
		$sql = "select * from users where user_id = " . $this->studentID;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		return $row['first'] . ' ' . $row['last'];
	}
	
	public function getPhoto() {
		return $this->photo;
	}
	
	public function getLevel() {
		return $this->level;
	}
	
	public function getSubject() {
		return $this->subject; 
	}
}
?>
