<?
class Report {
    protected $dates;
    protected $reportDates;
    protected $heReportDates;
    protected $users;
    protected $school_id;
    
    public function __construct() {
        $this->dates = array( 2456224, 2456259, 2456300, 2456331, 2456359, 2456411, 2456441 );
        $this->users = array();
        $this->school_id = null;
        $this->setReportDates();
    }
    
    private function setReportDates() {
        //find out which date we are today and set dates for report accordingly
        $today = unixtojd();
        $start = null;
        $end = null;
        foreach ( $this->dates as $date ) {
            if ( $today <= ($date + 10) ) {
                $end = $date;
                break;
            } else {
                $start = $date+1;
            }
        }
		//if end hasn't been set yet, we're at the last date of year (before summer)
		if ( is_null( $end ) ) {
			$start = $this->dates[count($this->dates)-2] + 1;
			$end = $this->dates[count($this->dates)-1];
		}
			
        $this->reportDates['start'] = $start;
        $this->reportDates['end'] = $end;
 
        $str1 = jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['start_he'] = iconv('WINDOWS-1255', 'UTF-8', $str1);

        $str2 = jdtojewish($end, true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['end_he'] = iconv('WINDOWS-1255', 'UTF-8', $str2);
    }
    
    public function getReportDates() {
        return $this->reportDates;
    }
    
    public function getHeReportDates() {
        return $this->heReportDates;
    }
    
    public function setPreviousDates() {
        $start = $this->reportDates['start']-1;
        $end = $this->reportDates['end'];
        $first = array_search( $start, $this->dates );
        $second = array_search( $end, $this->dates );
        if ( $first != 0 ) {
            $this->reportDates['start'] = $this->dates[$first-1]+1;
            $this->reportDates['end'] = $this->dates[$second-1];
        } else {
            $this->reportDates['start'] = $this->dates[$first];
            $this->reportDates['end'] = $this->dates[$second];
        }
        
        $str1 = jdtojewish($this->reportDates['start'], true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['start_he'] = iconv('WINDOWS-1255', 'UTF-8', $str1);

        $str2 = jdtojewish($this->reportDates['end'], true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['end_he'] = iconv('WINDOWS-1255', 'UTF-8', $str2);
    }
    
    public function setSchoolId( $id ) {
        $this->school_id = $id;
    }
    
    protected function setUsers() {
        $sql = "
            SELECT s.school_id, s.school_name, c.class_grade, c.class_sub, u.user_id
            FROM users u
            JOIN schools s
            USING ( school_id ) 
            JOIN classes c 
            USING ( class_id ) 
            WHERE u.user_registered >0 ";
        if (is_null( $this->school_id )) {
            $sql .= "
                AND s.school_era IS NULL 
                AND s.school_id not in (61, 82, 66, 112, 110, 180)
                order by s.school_name, c.class_grade, c.class_sub, u.last, u.first 
            ";
        } else {
            $sql .= "
                AND s.school_id = $this->school_id  
                order by c.class_grade, c.class_sub, u.last, u.first;
            ";
        }
        
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc($result) ) {
            $id =  $row['school_id'];
            $name = $row['school_name'];
            $this->users[$id][$name][] = $row['user_id'];
        }
    }    
}
?>