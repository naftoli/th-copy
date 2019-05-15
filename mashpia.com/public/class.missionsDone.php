<?
class MissionsDone {
    private $school; 
    private $missions;
    private $missionsDone;
    private $dates;
    private $classes;
	private $userIDs;
	private $weeks;
    private $users;
	
    public function __construct( $school_id = null ) {
        $this->school = $school_id;
        $this->missions = array();
        $this->missionsDone = array();
        $this->dates = array();
        $this->classes = array();
		$this->userIDs = array();
		$this->weeks = array();
		$this->users = array();
    }
    
    public static function getAllMissions() {
        $sql = "select subject_id, subject_name from subjects 
                where subject_type not in ('school_points', 'Hakhel') 
                and subject_id not in (91, 99)"; 
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $missions[$row['subject_id']] = $row['subject_name'];
        }
        return $missions;
    }
    
    public static function getTanyaMission() {
        $sql = "select subject_name from subjects 
                where subject_id = 27"; 
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        return array( '27' => $row['subject_name'] );
    }
    
    public static function getAllMedals() {
        $sql = "select medal_ord, medal_name from medals";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $medals[$row['medal_ord']] = $row['medal_name'];
        }
        return $medals;
    }
    
    public function setDates( $start, $end ) {
        $this->dates['start'] = $start;
        $this->dates['end'] = $end;
    }
    
    public function setClasses( $classes ) {
        $this->classes = $classes;
    }
    
    public function setMissionsDone( $missions = array(), $children = array(), $all = false ) {
    	if ( empty( $children ) ) { 
	        $sql = "SELECT c.class_grade, c.class_sub, u.user_id, u.user_code, u.first, u.last, su.subject_id, su.subject_name, count(*) as total  
	                from date_tasks_mission_marks mm 
	                join users u using (user_id) 
	                join schools s using (school_id) 
	                join subjects su using (subject_id) 
	                join classes c on (c.class_id = u.class_id) 
	                where s.school_id = " . $this->school;
	            if ( !empty( $missions ) ) {
	                $sql .= " and subject_id in (";
	                $sql .= implode(',', $missions);
	                $sql .= ") ";
	            }
	            if ( !empty( $this->dates ) ) {
	                $sql .= " and mark_date >= " . $this->dates['start'];
	                $sql .= " and mark_date <= " . $this->dates['end'];
	            }
	            if ( !empty( $this->classes ) ) {
	                $sql .= " and c.class_id in (" . implode( ',', $this->classes ) . ")";
	            }
	            $sql .= " and u.user_registered > 0 
	                      group by u.user_id, su.subject_id ";
	            if ($all) {
	            	$sql .= "order by u.last, u.first";
	            } else {
	            	$sql .= "order by c.class_grade, c.class_sub, u.last, u.first";
	            }
	        echo "<input type='hidden' name='sql' value='" . $sql . "' />";
	        $result = mysql_query( $sql );
	        while ( $row = mysql_fetch_assoc( $result ) ) {
	        	$userID = $row['user_id']; 
	            $userName = $row['first'] . " " . $row['last'];
				$class = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
				$total = $row['total'];
				/*
				if ($row['subject_id'] == 27) {
					//get tanya missions done from new system and add to total
					$barcode = 3 . $row['user_code'];
					$newTotal = header_v2_missions(array('arrUserCodes' => array($barcode)));
					if (isset($newTotal[$barcode]) && !empty($newTotal[$barcode])) 
					    $total += $newTotal[$barcode];
				}
				*/
				if ($all) {
					$this->missionsDone[$userName][$row['subject_name']] = $total;
				} else {
	            	$this->missionsDone[$class][$userName][$row['subject_name']] = $total;
	            }
				$this->userIDs[$userID][] = $userName . ":" . $row['subject_id'] . ":" . $total;
				$this->users[$userID] = $userName;
			}
	    } else {
	    	$sql = "SELECT u.user_id, u.user_code, u.first, u.last, su.subject_id, su.subject_name, SUM( mm.mission_count ) as total  
	                from date_tasks_mission_marks mm 
	                join users u using (user_id) 
	                join subjects su using (subject_id) 
	                where u.user_id in (" . implode(',', $children) . ")";
	            if ( !empty( $missions ) ) {
	                $sql .= " and subject_id in (";
	                $sql .= implode(',', $missions);
	                $sql .= ") ";
	            }
	            if ( !empty( $this->dates ) ) {
	                $sql .= " and mark_date >= " . $this->dates['start'];
	                $sql .= " and mark_date <= " . $this->dates['end'];
	            }
	            $sql .= " and u.user_registered > 0 
	                      group by u.user_id, su.subject_id 
	                      order by u.last, u.first";
			//echo $sql;
			$result = mysql_query( $sql );
	        while ( $row = mysql_fetch_assoc( $result ) ) {
	        	$userID = $row['user_id']; 
	            $userName = $row['first'] . " " . $row['last'];
				$total = $row['total'];
				/*
				if ($row['subject_id'] == 27) {
					//get tanya missions done from new system and add to total
					$barcode = 3 . $row['user_code'];
					$newTotal = header_v2_missions(array('arrUserCodes' => array($barcode)));
					if (isset($newTotal[$barcode]) && !empty($newTotal[$barcode])) 
					    $total += $newTotal[$barcode];
				}
				*/
	            $this->missionsDone[$userName][$row['subject_name']] = $total;
				$this->userIDs[$userID][] = $userName . ":" . $row['subject_id'] . ":" . $total;
				$this->users[$userID] = $userName;
	        }
	    }
    }
    
    public function setMissionsDonePerChild($missions) {
    	//setup weeks array
    	$weeks = array();
		$start = $this->dates['start'];
		$end = $this->dates['end'];
		do {
			$sql = "select name from parshos where start = " . $start . " and end = " . ($start + 6);
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$this->weeks[] = array(
				'start'	=>	$start, 
				'end'	=>	$start + 6, 
				'name'	=>	$row['name']
			);
		} while (($start += 7) < $end);
		
    	foreach ($this->weeks as $week) {
	        $sql = "SELECT c.class_grade, c.class_sub, u.user_id, u.user_code, u.first, u.last, su.subject_id, su.subject_name, SUM( mm.mission_count ) as total  
	                from date_tasks_mission_marks mm 
	                join users u using (user_id) 
	                join schools s using (school_id) 
	                join subjects su using (subject_id) 
	                join classes c on (c.class_id = u.class_id) 
	                where s.school_id = " . $this->school . " 
	                and subject_id in (" . implode(',', $missions) . ") 
	                and mark_date >= " . $week['start'] . " 
	                and mark_date <= " . $week['end'];
	            if ( !empty( $this->classes ) ) {
	                $sql .= " and c.class_id in (" . implode( ',', $this->classes ) . ")";
	            }
	            $sql .= " and u.user_registered > 0 
	                      group by u.user_id, su.subject_id 
	                      order by c.class_grade, c.class_sub, u.last, u.first";
	    	$result = mysql_query( $sql );
	        while ( $row = mysql_fetch_assoc( $result ) ) {
	        	$userID = $row['user_id']; 
	            $userName = $row['first'] . " " . $row['last'];
	            $class = $row['class_grade'] . ( empty( $row['class_sub'] ) ? "" : "-" . $row['class_sub'] );
	            $total = $row['total'];
				//if ($row['subject_id'] == 27) {
				//	//get tanya missions done from new system and add to total
				//	$barcode = 3 . $row['user_code'];
				//	$newTotal = header_v2_missions(array('arrUserCodes' => array($barcode)));
				//	if (isset($newTotal[$barcode]) && !empty($newTotal[$barcode])) 
				//	    $total += $newTotal[$barcode];
				//}
	            $this->missionsDone[$class][$userName][$week['name']][$row['subject_name']] = $total;
	        }
	    } 
    }
        
    public function getMissionsDone() {
        return $this->missionsDone;
    }
	
	public function getUserIDs() {
		return $this->userIDs;
	}
	
	public function getWeeks() {
		return $this->weeks;
	}
	
	public function getUsers() {
		return $this->users;
	}
}
?>