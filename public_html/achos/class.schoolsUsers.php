<?
class SchoolsUsers {
    private $school;
    private $users;
    private $usersByClass;
    private $userIDs;
    
    public function __construct( $school ) {
        $this->school = $school;
        $this->classes = array();
        $this->users = array();
        $this->usersByClass = array();
        $this->userIDs = array();
    }
    
    public function setClasses( $classes ) {
        $this->classes = $classes;
    }
    
    private function setUsers($registered) {
        $sql = "select * from users u 
                join classes c using (class_id) 
                where u.school_id = " . $this->school;
        if ($registered)
            $sql .= " and u.user_registered > 0 ";
        else 
            $sql .= " and u.user_registered is null ";
        if ( !empty( $this->classes ) && $this->classes != 'all' ) 
            $sql .= "and class_id in (" . implode( ',', $this->classes ) . ") ";        
        $sql .= "order by c.class_grade, c.class_sub, u.last, u.first";
        //echo $sql;
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
            }
        }
    }
        
    public function getUsers($registered = true) {
        if ( empty ( $this->users ) && empty( $this->usersByClass ) ) 
            $this->setUsers($registered);
        //find out which users array to get    
        if ( empty( $this->classes ) )
            return $this->users;
        else 
            return $this->usersByClass;
    }
    
    public function getUserIDs() {
        return $this->userIDs;
    }
}
?>