<?php
class Task {
    private $start;
    private $end;
    private $type;
    private $id;
    private $schoolType;
    private $school_id;

    public function __construct() {
        $this->start = null;
        $this->end = null;
        $this->type = null;
        $this->id = null;
        $this->schoolType = null;
        $this->school_id = null;
    }
    
    public function setStart( $start ) {
        $this->start = $start;
    }

    public function setEnd( $end ) {
        $this->end = $end;
    }
    
    public function setType( $user, $class, $school ) {
        if ( $user > 0 ) {
            $this->type = 'user';
            $this->id = $user;
        } else if ( $class > 0 ) {
            $this->type = 'class'; 
            $this->id = $class;
        } else if ( $school > 0 ) {
            $this->type = 'school';
            $this->id = $school;
        }

        //if we're coming from a parent account
        if ( $user > 0 && $school == 0 ) {
            $sql = "select school_id from users where user_id = " . $user;
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            $school = $row['school_id'];
        }
        
        $this->setSchoolType( $school );
    }
	
    private function setSchoolType( $id ) {
        $sql = "select inst_id from schools where school_id = " . $id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $instType = $row['inst_id'];
        if ( $instType == 2 ) {
            $this->schoolType = "(2,3)";
        } else if ( $instType == 4 ) {
            $this->schoolType = "(12,13)";
        }
        $this->school_id = $id;
    }
}
?>
