<?
class MedalsEarned {
    private $dates;
    private $medals;
    private $school_id;
    private $classes;
    
    public function __construct( $school_id ) {
        $this->school_id = $school_id; 
        $this->medals = array();
        $this->dates = array();
        $this->classes = array();
    }
    
    public function setDates( $start, $end ) {
        $this->dates['start'] = $start;
        $this->dates['end'] = $end;
    }
    
    public function setClasses( $classes ) {
        $this->classes = $classes;
    }
    
    public static function getAllSubjects() {
        $sql = "select subject_id, subject_name from subjects 
                where subject_type not in ('school_points', 'tanya', 'Hakhel') 
                and subject_id not in (91, 98)"; 
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $subjects[$row['subject_id']] = $row['subject_name'];
        }
        return $subjects;
    }
    
    public static function getAllMedals() {
        $sql = "select * from medals";
        $result = mysql_query( $sql );
        $medals = array();
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $medals[$row['medal_ord']] = $row['medal_name'];
        }
        return $medals;
    }
    
    public function setMedalsEarned( $subjects ) {
        $sql = "select mm.medal_ord, mm.date_awarded, su.subject_name, u.last, u.first, c.class_grade, c.class_sub 
                from medal_marks mm 
                join users u using (user_id) 
                join subjects su using (subject_id) 
                join classes c on (c.class_id = u.class_id) 
                join schools s on (u.school_id = s.school_id)   
                where u.user_registered > 0 
                and u.school_id = $this->school_id";
        if ( !empty( $this->dates ) ) {
            $sql .= " and date_awarded >= " . $this->dates['start'] . " 
                    and date_awarded <= " . $this->dates['end'];
        }
        if ( !empty( $this->classes ) ) {
            $sql .= " and c.class_id in (" . implode(',', $this->classes) . ")";
        }
        $sql .= " and mm.subject_id in (" . implode(',', $subjects) . ")";
        
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $grade = $row['class_grade'] . ( empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'] ); 
            $userName = $row['first'] . " " . $row['last'];
            $this->medals[$grade][$userName][$row['subject_name']][] = $row['medal_ord'] . ':' . $row['date_awarded'];
        }        
    }
    
    public function getMedalsEarned() {
        return $this->medals;
    }
}
?>