<?php
class ReportBasic {
    protected $dates;
    protected $reportDates;
    protected $heReportDates;
    protected $users;
    protected $school_id;
    protected $exceptions;
    protected $grades;
    
    public function __construct() {
        $this->users = array();
        $this->school_id = null;
        $this->exceptions = array(61, 82, 66, 112, 110, 180, 269);
        $this->grades = [];
	}
    
    public function setReportDates($previousStart) {
        //find out which date we are today and set dates for report accordingly
        $today = unixtojd();
        $start = null;
        $end = null;
        foreach ( $this->dates as $date ) {
            if ( $today <= ($date + 25) ) {
            //if ( $today <= $date ) {
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
		
		//if ($previousStart && $start == 2456689) {
		if ($previousStart) {
            $k = array_search(($start-1), $this->dates);
            $end = $this->dates[$k];
			$start = $this->dates[--$k]+1;;
		}
			
        $this->reportDates['start'] = $start;
        $this->reportDates['end'] = $end;
		
        $str1 = jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['start_he'] = iconv('WINDOWS-1255', 'UTF-8', $str1);

        $str2 = jdtojewish($end, true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['end_he'] = iconv('WINDOWS-1255', 'UTF-8', $str2);
    }

	public function overrideDates( $start, $end ) {
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
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end'];
        $first = array_search( $start-1, $this->dates );
        $second = array_search( $end, $this->dates );
        if ( $first != 0 ) {
            $this->reportDates['start'] = $this->dates[$first-1]+1;
            $this->reportDates['end'] = $this->dates[$second-1];
        } else {
            $this->reportDates['start'] = $this->dates[$first]+1;
            $this->reportDates['end'] = $this->dates[$second];
        }
        
        $str1 = jdtojewish($this->reportDates['start'], true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['start_he'] = iconv('WINDOWS-1255', 'UTF-8', $str1);

        $str2 = jdtojewish($this->reportDates['end'], true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['end_he'] = iconv('WINDOWS-1255', 'UTF-8', $str2);
    }
    
    public function setNextDates() {
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end'];
        $first = array_search( $start-1, $this->dates );
        $second = array_search( $end, $this->dates );
        $this->reportDates['start'] = $this->dates[$first+1]+1;
        $this->reportDates['end'] = $this->dates[$second+1];
        
        $str1 = jdtojewish($this->reportDates['start'], true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['start_he'] = iconv('WINDOWS-1255', 'UTF-8', $str1);

        $str2 = jdtojewish($this->reportDates['end'], true, CAL_JEWISH_ADD_GERESHAYIM);
        $this->heReportDates['end_he'] = iconv('WINDOWS-1255', 'UTF-8', $str2);
    }
    
    public function setException( $id ) {
        if (!in_array($id, $this->exceptions)) $this->exceptions[] = $id;
    }
    
    public function setSchoolId( $id ) {
        $this->school_id = $id;
    }

    public function setGrades( $grades ) {
        if ( is_array( $grades ) ) $this->grades = $grades;
        else $this->grades = [$grades];
    }
    
    protected function setUsers() {
        $sql = "
            SELECT s.school_id, s.school_name, c.class_grade, c.class_sub, u.user_id
            FROM users u
            JOIN schools s
            ON s.school_id = u.school_id 
            JOIN classes c 
            ON c.class_id = u.class_id 
            WHERE u.user_registered >0 ";
        if (is_null($this->school_id)) {
            $sql .= "
                AND s.school_era IS NULL 
                AND s.school_id not in (" . implode(',', $this->exceptions) . ") 
                ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first 
            ";
        } else {
            if ( empty( $this->grades ) ) {
                $sql .= "
                    AND s.school_id = $this->school_id  
                    ORDER BY c.class_grade, c.class_sub, u.last, u.first
                ";
            } else {
                $sql .= "
                    AND c.class_id in (" . implode(',', $this->grades) . ") 
                    ORDER BY c.class_grade, c.class_sub, u.last, u.first
                ";
            }
        }
        
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc($result)) {
            $id =  $row['school_id'];
            $name = $row['school_name'];
            $this->users[$id][$name][] = $row['user_id'];
        }
    }    
}
?>