<?
class AdminSchools {
    private $admin_id;
    private $auth;
    private $schools;
    private $registeredOnly;
    private $chidon;
    private $noTest;
    
    public function __construct( $id, $auth, $registered = true, $chidon = false, $noTest = true ) {
        $this->admin_id = $id;
        $this->auth = $auth;
        $this->schools = array();
        $this->registeredOnly = $registered;
        $this->chidon = $chidon;
        $this->noTest = $noTest;
    }
    
    private function setSchools() {
        if ( $this->auth == 'super' ) {
            $sql = "select s.school_id, s.school_name 
                    from schools s 
                    where s.chayolei = 1 ";
            if ($this->chidon) $sql .= "or s.chidon = 1 ";
            if ($this->registeredOnly) 
                $sql .= "and s.school_era is null ";
            if ( $this->noTest ) $sql .= 'and s.test_school = 0 ';
            $sql .= "order by school_name";
        } else if ( $this->auth == 'ckidssuper' ) {
            $sql = "select s.school_id, s.school_name 
                    from schools s 
                    where s.ckids = 1 ";
        } else { 
            $sql = "select s.school_id, s.school_name 
                    from schools s 
                    join admin_auths aa on (aa.id = s.school_id) 
                    join admins a using (admin_id) 
                    where aa.auth = 'school' 
                    and a.admin_id = " . $this->admin_id . "
                    order by s.school_name";
        }
		//echo "<input type='hidden' name='schoolSql' value='" . $sql . "' />";
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
	
	public static function getAllSchools($arrExcept) {
		$notIn = implode(',', $arrExcept);
		$schools = array();
		$sql = "select school_id, school_name from schools where school_era is null and school_id not in ($notIn) order by school_name";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$schools[$row['school_id']] = $row['school_name'];
		}
		return $schools;
	}
}
?>