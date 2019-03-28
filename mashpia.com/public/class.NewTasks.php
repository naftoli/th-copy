<?
class NewTasks {
    private $user_id;
    private $user;
    private $dates;
    private $mission_id;
    private $subject_id;
    private $userTrack;
    private $createTasks;
    private $category;
	private $lang;
    
    public function __construct( $user_id, $lang = 1 ) {
        $this->user_id = $user_id;
		$this->lang = $lang;
        $this->createTasks = true;
        $this->category = null;
    }
    
    private function setSubject( $s = 0 ) {
		if ($s) $this->subject_id = $s;
		else {
			// figure out if it's a chabad child or frum child
			$sql = "select school_type_id from users where user_id = " . $this->user_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			if (in_array($row['school_type_id'],array(12,13))) $this->subject_id = 94;
			else $this->subject_id = 40;
		}
    }
    
    public function setDates( $start, $end ) {
        $this->dates['start'] = $start;
        $this->dates['end'] = $end;
    }
    
    public function setUserInfo() {
        $sql = "select * from users where user_id = " . $this->user_id;
        if ( $result = mysql_query( $sql ) ) {
            $row = mysql_fetch_assoc( $result );
            $this->user = $row; 
            return true; 
        } 
        return false;       
    }
    
    public function getUserInfo() {
        return $this->user;
    }
    
    private function getUserTrack() {         
        $this->userTrack['track'] = 1;
        
        //find level based on class_id
        /*
        if ($this->user['class_id']) {
            $sql = "select class_grade from classes where class_id = " . $this->user['class_id'];
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            $grade = $row['class_grade'];
            switch ($grade)  {
                case 'Pre1a':
                    $this->userTrack['level'] = 6;
                    break;
                case '1':
                    $this->userTrack['level'] = 7;
                    break;
                case '2':
                    $this->userTrack['level'] =  8;
                    break;
                case '3':
                    $this->userTrack['level'] = 9;
                    break;
                case '4':
                    $this->userTrack['level'] = 10;
                    break;
                case '5':
                    $this->userTrack['level'] = 11;
                    break;
                case '6':
                    $this->userTrack['level'] = 12;
                    break;
                case '7':
                    $this->userTrack['level'] = 13;
                    break;
                case '8':
                    $this->userTrack['level'] = 14;
                    break;
                default:
                    $this->userTrack['level'] = 6;
                    break;
            }
            return true; 
        } else {
		 * 
		 */
            $sql = "select * from user_tracks where user_id = " . $this->user_id . " 
                and subject_id = " . $this->subject_id;
            $result = mysql_query( $sql );
            if ( mysql_num_rows( $result ) > 0 ) {
                $row = mysql_fetch_assoc( $result );
                if ( is_null( $row['track_id'] ) || is_null( $row['level'] ) ) {
                    echo $this->user_id . " has no year and/or level for yoma depagra / yom tov.<br />"; 
                    return false;
                } else {
                    //echo $this->user_id . " level: " . $row['level'] . " track: " . $row['track_id'] . "<br />";
                    $this->userTrack['track'] = $row['track_id'];
                    $this->userTrack['level'] = ($row['level'] > 5 ? $row['level'] : 6);
                    return true;
                }
            } else {
                return false;
            }
        //}       
    }
    
    public function createMission( $name, $description ) {
        $this->setSubject(); 
        if ( $this->getUserTrack() ) {
            //check if mission with this school_type_id, name, level and track_id exists
            $sql = "select * from date_tasks_missions 
                    where school_type_id = " . $this->user['school_type_id'] . " 
                    and subject_id = " . $this->subject_id . "  
                    and level = " . $this->userTrack['level'] . "  
                    and track_id = " . $this->userTrack['track'] . "  
                    and mission_name = \"" . $name . "\" 
                    and mission_description = \"" . $description . "\"  
                    and start_date = " . $this->dates['start'] . " 
                    and end_date = " . $this->dates['end'] . "
					and lang_id = " . $this->lang . " 
                    and personal = 1";
            $result = mysql_query( $sql ) or die( $this->user_id . "<br />" . $sql . "<br />" );
            $num = mysql_num_rows( $result );
            if ( $num > 0 ) {
                $row = mysql_fetch_assoc( $result );
                $this->mission_id = $row['date_tasks_mission_id'];
                $this->createTasks = false;
                return true;
            } else {
                $sql = "insert into date_tasks_missions set 
                        school_type_id = " . $this->user['school_type_id'] . ", 
                        subject_id = " . $this->subject_id . ", 
                        level = " . $this->userTrack['level'] . ", 
                        track_id = " . $this->userTrack['track'] . ", 
                        mission_name = \"" . $name . "\" , 
                        mission_description = \"" . $description . "\", 
                        start_date = " . $this->dates['start'] . ", 
                        end_date = " . $this->dates['end'] . ",
						lang_id = " . $this->lang . ", 
                        personal = 1";
                if ( $result = mysql_query( $sql ) ) {
                    $this->mission_id = mysql_insert_id();
                    return true;
                } else {
                    echo mysql_error() . "<br />";
                    return false;
                }
            }
        } 
        return false; 
    }
    
    public function setCategory($cat) {
        $this->category = $cat;
    }
    
    public function createTask( $name, $points, $mandatory, $qty = null ) {
        if ( !$this->createTasks ) {
            return false;
        }
        $sql = "insert into date_tasks set 
                date_tasks_mission_id = " . $this->mission_id . ", 
                name = \"" . $name . "\",
				mission_marking = 1, 
                points = " . $points; 
        if ( $mandatory ) {
            $sql .= ", mandatory_qty = 1, 
                optional_qty = 0";
        } else {
            $sql .= ", mandatory_qty = 0, 
                optional_qty = 1";
        }
        if ( !is_null( $qty ) ) {  
            $sql .= ", description = '" . $qty . "', 
                quantity = 1"; 
        }
        if ( !is_null($this->category) ) {
            $sql .= ", cat = '" . $this->category . "'";
        }
        if ( $result = mysql_query( $sql ) ) {
            return true;
        } 
        return false;
    }
    
    public function getMissionID() {
        return $this->mission_id;
    }
    
    public function needToCreateTasks() {
        return $this->createTasks;
    }
}
?>