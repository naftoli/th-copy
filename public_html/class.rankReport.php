<?
//if (in_array($admin_user['auths']['school'][0], array(55,66,110,112)))
//	require_once 'class.reportAustralia.php';
//else 
require_once 'class.report.php';

class RankReport extends Report {
    protected $ranks;
    protected $rankNames;
    protected $rankInfo;
    protected $userInfo;
    protected $rankOrds;
	protected $userHeNames;
    
    public function __construct($previousStart = false) {
        parent::__construct($previousStart);
        $this->rankInfo = array();
        $this->userInfo = array();
		$this->userHeNames = array();
        $this->rankOrds['Private'] = 1;
    }
    
    public function setRanks($orderType = 'byGrade') {
		$this->ranks = array();
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end']; 
        $sql = "
            SELECT s.school_name, c.class_teacher, c.class_grade, c.class_sub, r.rank_name, u.user_id, u.last, u.first, u.first_he, u.last_he, rm.* 
            FROM rank_marks rm
            JOIN ranks r
            USING ( rank_ord )
            JOIN users u
            USING ( user_id )
            JOIN schools s
            USING ( school_id )
            JOIN classes c
            ON ( u.class_id = c.class_id ) 
            WHERE date_promoted >= $start 
            AND date_promoted <= $end 
            AND u.user_registered > 0 ";
        if (!is_null($this->school_id)) {
            $sql .= "AND s.school_id = $this->school_id ";
        }
        if ($orderType == 'byGrade') {
            $sql .= "ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first, r.rank_ord";
		} else {
            $sql .= "ORDER BY s.school_name, r.rank_ord, c.class_grade, c.class_sub, u.last, u.first";
        }
        echo "<input type='hidden' name='SQL' value='" . $sql . "' />";
        
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $user_id = $row['user_id'];
            $school = $row['school_name'];
            $teacher = $row['class_teacher'];
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $user = $row['first'] . " " . $row['last'];
            $rank = $row['rank_name'];
            if ( $orderType == 'byGrade' )
                $this->ranks[$school][$teacher][$grade][][$user_id] = $rank;
			else if ( $orderType == 'byGradeRank')
				$this->ranks[$school][$teacher][$grade][$rank][] = $user_id;
            else if ( $orderType == 'byRank' )
                $this->ranks[$school][$rank][$teacher][$grade][] = $user_id;
            
            $this->rankInfo[$user_id]['card_printed'] = $row['date_printed'];
            $this->rankInfo[$user_id]['card_shipped'] = $row['date_card_shipped'];
            $this->rankInfo[$user_id]['card_received'] = $row['date_card_received'];
            $this->rankInfo[$user_id]['book_shipped'] = $row['date_book_shipped'];
            $this->rankInfo[$user_id]['book_received'] = $row['date_book_received'];
            
            $this->userInfo[$user_id] = $user;
			$this->userHeNames[$row['user_id']] = $row['first_he'] . ' ' . $row['last_he'];
        }
    }
    
    public function getRanks() {
        return $this->ranks;
    }
    
    public function setRankNames() {
        //$sql = "select * from ranks where medals_required > 0 order by rank_ord";
        $sql = "select * from ranks order by rank_ord";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $rank = $row['rank_name'];
            $needed = $row['medals_required'];    
            $this->rankNames[$rank] = $needed;
            $this->rankOrds[$rank] = $row['rank_ord'];
        }
    }
    
    public function getRankNames() {
        return $this->rankNames;
    }
    
    public function getRankInfo() {
        return $this->rankInfo;
    }
    
    public function getUserInfo() {
        return $this->userInfo;
    }
	
	public function getUserHeNames() {
		return $this->userHeNames;
	}
    
    public function getRankOrds() {
        return $this->rankOrds;
    }
}
?>