<?
class AdminSchools {
    private $admin_id;
    private $auth;
    private $schools;
    private $registeredOnly;
    
    public function __construct( $id, $auth, $registered = true ) {
        $this->admin_id = $id;
        $this->auth = $auth;
        $this->schools = array();
        $this->registeredOnly = $registered;
    }
    
    private function setSchools() {
        /*
        if ( $this->auth == 'super' ) {
            $sql = "select s.school_id, s.school_name 
                    from schools s ";
            if ($this->registeredOnly) 
                $sql .= "where s.school_era is null ";
            $sql .= "order by school_name";
        } else {
        */
            $sql = "select s.school_id, s.school_name 
                    from schools s 
                    join admin_auths aa on (aa.id = s.school_id) 
                    join admins a using (admin_id) 
                    where aa.auth = 'school' 
                    and a.admin_id = " . $this->admin_id . "
                    order by s.school_name";
        //}
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $this->schools[$row['school_id']] = $row['school_name'];
        }
    } 
    
    public function getSchools() {
        if ( empty( $this->schools ) ) {
            $this->setSchools();
        }
        return $this->schools;
    }
}
?>