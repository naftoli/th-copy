<?
require_once 'class.report.php';

class MedalReport extends Report{
    private $medalSummary;
    private $medalsTotal;
    private $medalTotals;
    private $medalDetails;
    
    public function __construct() {
        parent::__construct();
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
            AND mm.date_awarded <= $end ";
        if ( !is_null( $this->school_id ) ) 
            $sql .= " AND sch.school_id = $this->school_id ";
        $sql .= "
            GROUP BY sch.school_name, s.subject_id, mm.medal_ord
            ORDER BY sch.school_name, s.subject_id, mm.medal_ord
        ";
        //echo $sql;
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
                foreach ( $user as $student ) {
                    //echo $school_id . ":" . $name . ":" . $student;
                    $sql = "
                        SELECT s.subject_name, m.medal_name, u.last, u.first, 
                        c.class_grade, c.class_sub, c.class_teacher    
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
                        and u.user_id = $student  
                        ORDER BY s.subject_id, mm.medal_ord
                    ";
                    //echo $sql . "<br />";
                    $result = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($result)) {
                        if ($row['last'] != "") { 
                            $teacher = $row['class_teacher']; 
                            $grade = $row['class_grade'] . (empty( $row['class_sub']) ? '' : "-" . $row['class_sub']);
                            $user_name = $row['first'] . " " . $row['last']; 
                            $subject = $row['subject_name'];
                            if ( $subject == 'שבת מברכים תהילים' ) $subject = "WWTC";
                            $this->medalDetails[$name][$teacher][$grade][$user_name][$subject] = $row['medal_name'];        
                        }
                    }
                }
            }
        }
    }
    
    public function getMedalDetails() {
        return $this->medalDetails;
    }
}
?>