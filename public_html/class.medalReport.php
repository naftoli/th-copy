<?
//if (in_array($admin_user['auths']['school'][0], array(55,66,110,112)))
//	require_once 'class.reportAustralia.php';
//else 
require_once 'class.report.php';

class MedalReport extends Report {
    private $medalSummary;
    private $medalsTotal;
    private $medalTotals;
    private $medalDetails;
    private $medalsInfo;
    private $userInfo;
    private $medalOrds;
    private $subjects;
    
    public function __construct($previousStart = false) {
        parent::__construct($previousStart);
        $this->medalsInfo = array();
        $this->userInfo = array();
        $this->medalOrds = array();
        $this->subjects = array();
    }
    
    public function setMedalSummary() {
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end']; 
        $sql = "
            SELECT sch.school_name, s.subject_name, m.medal_name, count( u.user_id ) as total 
            FROM medal_marks mm
            JOIN medals m
            USING ( medal_ord )
            JOIN users u
            USING ( user_id )
            JOIN subjects s
            USING ( subject_id )
            JOIN schools sch
            USING ( school_id )
            WHERE mm.date_awarded >= $start 
            AND mm.date_awarded <= $end
			and s.subject_id != 106";
        if ( !is_null( $this->school_id ) ) 
            $sql .= " AND sch.school_id = $this->school_id ";
        $sql .= "
            GROUP BY sch.school_name, s.subject_id, mm.medal_ord  
            ORDER BY sch.school_name, s.subject_id, mm.medal_ord
        ";
        //echo $sql; return;
        $this->medalsTotal = array();
        $this->medalTotals = array();
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->medalSummary[$row['school_name']][$row['subject_name']][$row['medal_name']] = $row['total'];        
            if ( isset( $this->medalsTotal[$row['school_name']] ) ) 
                $this->medalsTotal[$row['school_name']] += $row['total'];
            else
                $this->medalsTotal[$row['school_name']] = $row['total'];
            if ( isset( $this->medalTotals[$row['school_name']][$row['subject_name']] ) ) 
                $this->medalTotals[$row['school_name']][$row['subject_name']] += $row['total'];
            else 
                $this->medalTotals[$row['school_name']][$row['subject_name']] = $row['total'];
        }
    }
    
    public function getMedalSummary() {
        return $this->medalSummary;
    }
    
    public function getMedalsTotal() {
        return $this->medalsTotal;
    }
    
    public function getMedalTotals() {
        return $this->medalTotals;
    }
    
    public function setMedalDetails() {
        $this->setUsers();
		        
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end']; 

        foreach ( $this->users as $school_id => $school ) {
            foreach ( $school as $name => $user ) {
            	$students = $this->users[$school_id][$name];
                //foreach ( $user as $student ) {
                    //echo $school_id . ":" . $name . ":" . $student;
                    $sql = "
                        SELECT s.subject_name, m.medal_name, u.user_id, u.last, u.first, 
                        c.class_grade, c.class_sub, c.class_teacher, mm.*, s.subject_id
                        FROM medal_marks mm
                        JOIN medals m
                        USING ( medal_ord )
                        JOIN users u
                        USING ( user_id )
                        JOIN subjects s
                        USING ( subject_id )
                        JOIN schools sch
                        USING ( school_id ) 
                        LEFT JOIN classes c using (class_id)  
                        WHERE mm.date_awarded >= $start 
                        AND mm.date_awarded <= $end  
                        and sch.school_id = $school_id  
                        and u.user_id in (" . implode(',', $students) . ")
						and s.subject_id != 106 
                        ORDER BY u.user_id, s.subject_id, mm.medal_ord
                    ";
                    //echo $sql . "<br />"; continue;
                    $result = mysql_query($sql) or die(mysql_error());
                    while ($row = mysql_fetch_assoc($result)) {
                        if ($row['last'] != "") {
                            $user_id = $row['user_id'];
                            $teacher = $row['class_teacher']; 
                            $grade = $row['class_grade'] . (empty( $row['class_sub']) ? '' : "-" . $row['class_sub']);
                            $user_name = $row['first'] . " " . $row['last']; 
                            $subject = $row['subject_name'];
                            if ( $subject == 'שבת מברכים תהילים' ) $subject = "WWTC";
                            $this->medalDetails[$name][$teacher][$grade][$user_id][$subject][] = $row['medal_name']; 
                            
                            $this->medalsInfo[$user_id]['earned'] = $row['date_awarded'];
                            $this->medalsInfo[$user_id]['shipped'] = $row['date_shipped'];
                            $this->medalsInfo[$user_id]['received'] = $row['date_received'];
							
							$this->medalsInfo[$user_id]['shipped_specific'][$subject][$row['medal_name']] = ['medal_ord' => $row['medal_ord'],
																											 'shipped' => $row['date_shipped'],
																											 'subject_id' => $row['subject_id'],
																											 'date_awarded' => $row['date_awarded']]; 
                            
                            //$this->userInfo[$user_name] = $row['user_id'];
                            $this->userInfo[$user_id] = $user_name;
                        }
                    }
					//sleep(2);
                //}
            }
        }
    }
    
    public function getMedalDetails() {
        return $this->medalDetails;
    }
    
    public function getMedalsInfo() {
        return $this->medalsInfo;
    }
    
    public function getUserInfo() {
        return $this->userInfo;
    }
    
    public function getMedalOrds() {
        if (empty($this->medalOrds)) {
            $sql = "select * from medals";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $this->medalOrds[$row['medal_name']] = $row['medal_ord'];
            }
        }
        return $this->medalOrds;
    }
    
    public function getSubjects() {
        if (empty($this->subjectOrds)) {
            $sql = "select * from subjects";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $subject = $row['subject_name'];
                if ($row['subject_name'] == 'שבת מברכים תהילים') 
                    $subject = "WWTC";
                $this->subjects[$subject] = $row['subject_id'];
            }
        }
        return $this->subjects;
    }
}
?>