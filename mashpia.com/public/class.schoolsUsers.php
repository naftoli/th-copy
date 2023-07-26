<?
class SchoolsUsers {
    private $school;
    private $users;
    private $usersByClass;
    private $userIDs;
	private $userInfo;
	private $userNames;
	private $currentClasses;
	private $year;
    
    public function __construct( $school ) {
        $this->school = $school;
        $this->classes = array();
        $this->users = array();
        $this->usersByClass = array();
        $this->userIDs = array();
		$this->userInfo = array();
		$this->userNames = array();
		$this->currentClasses = false;
		$this->year = 0;
    }
	
	public function setYear( $year ) {
		$this->year = $year;
	}
    
    public function setClasses( $classes ) {
        $this->classes = $classes;
    }
    
    private function setUsers($registered, $only) {
        $sql = "select u.*, s.shorthand, ";
		if ($this->year > 0) $sql .= "ur.*, ";
		$sql .= "c.class_grade, c.class_sub, c.class_teacher from users u 
                join classes c using (class_id) ";
		if ($this->year > 0) {
			$sql .= "left join user_registration ur using (user_id) ";
		}
        $sql .= "join schools s on s.school_id = u.school_id ";
		$sql .= "where u.school_id = " . $this->school;
		if ($this->year > 0) {
			$sql .= " and ur.year = " . $this->year;
		}
        else if ($registered && $only) {
			$sql .= " and u.user_registered > 0 ";
        }
        else if (!$registered && $only)
            $sql .= " and u.user_registered is null ";
        if ( !empty( $this->classes ) && $this->classes != 'all' ) 
            $sql .= "and class_id in (" . implode( ',', $this->classes ) . ") ";        
        $sql .= " order by c.class_grade, c.class_sub, u.last, u.first";
		//echo $sql;
        //echo "<input type='hidden' name='classSql' value='$sql' />";
        $result = mysql_query( $sql );
        
        if ( empty( $this->classes ) ) {
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $this->users[] = $row;
            }
        } else {
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $grade = $row['class_grade'] . ( empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'] );
                $this->usersByClass[$grade][] = $row['first'] . " " . $row['last'];
                $this->userIDs[$grade][] = $row['user_id'];
                $this->userNames[$grade][$row['user_id']] = $row['first'] . " " . $row['last'];
            }
        }
    }
        
    public function getUsers($registered = true, $only = true) {
        if ( empty ( $this->users ) && empty( $this->usersByClass ) ) 
            $this->setUsers($registered, $only);
        //find out which users array to get    
        if ( empty( $this->classes ) )
            return $this->users;
        else 
            return $this->usersByClass;
    }
    
    public function getUserIDs() {
        return $this->userIDs;
    }
	
	public function getUserNames() {
		return $this->userNames;
	}
	
	public function getUsersAfterDate($date) {
		if (empty($this->users)) {
			$sql = "select u.*, c.class_grade, c.class_sub, c.class_teacher from users u 
	                join classes c using (class_id) 
	                where u.school_id = " . $this->school . "
					and u.user_registered > '" . $date . "'  
					order by c.class_grade, c.class_sub, u.last, u.first";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$this->users[] = $row;
			}
		}
		return $this->users;
	}
	
	public function getNotRegisteredUsers($getParentInfo = false) {
		if (empty($this->users)) {
			$sql = "select u.*, c.class_grade, c.class_sub, c.class_teacher from users u 
	                join classes c using (class_id) 
	                where u.school_id = " . $this->school . "
					and (u.user_registered is null or u.user_registered = 0 or u.user_registered = '')
					order by u.last, u.first";
			$result = mysql_query($sql);
			$i = 0;
			while ($row = mysql_fetch_assoc($result)) {
				if ($getParentInfo) {
					$this->users[$i]['user'] = $row;
					$pSql = "select a.* from admins a 
							join admin_auths aa using (admin_id) 
							where aa.id = " . $row['user_id'] . " and aa.auth = 'user'";
					$pRes = mysql_query($pSql);
					if ($pRow = mysql_fetch_assoc($pRes)) {
						$this->users[$i]['parent'] = $pRow;
					}
					$i++;
				} else {
					$this->users[] = $row;
				}
			}
		}
		return $this->users;
	}
	
	public function getUserInfo() {
		if (empty($this->userInfo)) {
			$sql = "select u.*, c.class_grade, c.class_sub from users u 
					join classes c using (class_id) 
					where u.school_id = $this->school and u.user_registered > 0 
					order by c.class_grade, c.class_sub, u.last, u.first";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$this->userInfo[] = $row;
			}
		}
		return $this->userInfo;
	}
}
?>