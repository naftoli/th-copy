<?
class SchoolClasses {
    
    private $school_id;
    private $classes;
    
    public function __construct( $id ) {
        $this->school_id = $id; 
        $this->classes = array();
    }
    
    private function setClasses() {
        $sql = "select * from classes where class_era = 0 and school_id = $this->school_id order by class_grade, class_sub";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $this->classes[] = $row;
        }
    }
    
    public function getClasses() {
        if ( empty( $this->classes ) ) {
            $this->setClasses();
        }
        return $this->classes;
    }
}
?>