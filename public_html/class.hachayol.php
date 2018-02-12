<?php
require_once 'class.globalSettings.php';

class Hachayol {
    private $db;
    private $schools;
    private $schoolDetails;
    private $chidonYear;
    
    public function __construct() {
        require_once 'class.db.php';
        $this->db = DB::getInstance();
        $this->schools = array();
        $this->schoolDetails = array();
        $this->chidonYear = GlobalSettings::getChidonYear();
    }
    
    public function setSchools( $id = null ) {
        //get list of schools with totals per school of registered students
        $sql = "
            SELECT s.school_id, s.school_name, s.hachayol_name, count( u.user_id ) AS total, s.school_address1, s.school_address2, 
            s.school_city, s.school_state, s.school_country, s.school_postal, s.shipping_method, s.principal, s.shipping_requests  
            FROM schools s
            JOIN users u
            USING ( school_id )
            WHERE s.school_era IS NULL
            and s.chayolei = 1 
            AND u.user_registered > 0
            and s.test_school = 0 ";
        if ( !is_null( $id ) ) $sql .= "AND s.school_id = " . $id; 
        else $sql .= "AND s.school_id not in (82)";
        $sql .= " GROUP BY s.school_id ORDER BY s.shipping_method, s.school_name";
        //echo $sql; exit;
        
        foreach ( $this->db->query( $sql ) as $row ) {
            $school = $row['school_id']; 
            $method = $row['shipping_method'];
            $total = $row['total'];
            
            // add 45 to montreal boys, 75 to houston, and 2 to London Girls
            if ($school == 58) $total += 45;
            if ($school == 84) $total += 75;
            if ($school == 265) $total += 2;
            
            $this->schools[$method][$school]['principal'] = $row['principal'];
            $this->schools[$method][$school]['name'] = $row['hachayol_name'] ? $row['hachayol_name'] : $row['school_name'];
            $this->schools[$method][$school]['total'] = $total;
            $this->schools[$method][$school]['address'] = $row['school_address1'] . 
                ($row['school_address2'] == "" ? "<br />" . $row['school_address2'] : "<br />") . 
                $row['school_city'] . ", " . $row['school_state'] . "<br />" . $row['school_postal'] . "<br />" . 
                $row['school_country'];
            $this->schools[$method][$school]['shipping_requests'] = $row['shipping_requests'];
            
            $sql2 = "select a.first, a.last 
                     from admins a 
                     join admin_auths aa using (admin_id) 
                     join schools s on (s.school_id = aa.id) 
                     where aa.auth = 'school' 
                     and s.school_id = " . $school;
            $i = 0;
            foreach( $this->db->query( $sql2 ) as $row2 ) {
                if ( $i++ > 0 ) {
                    if ( $row2['first'] == 'Update' ) continue;
                } 
                $this->schools[$method][$school]['admins'][] = $row2['first'] . " " . $row2['last'];
            }
        
            $sql3 = "select DISTINCT c.class_teacher  
                     from classes c 
                     join schools s using (school_id) 
                     where c.class_era = 0  
                     and s.school_id = " . $school;
            $stmt3 = $this->db->query( $sql3 );
            $this->schools[$method][$school]['teachers'] = $stmt3->rowCount();
            
            // find out how many kids in school already registered for chidon
            $sql4 = "select count(*) as registered from th_chidon
                    where year = " . $this->chidonYear . "
                    and school_id = " . $school;
            //echo "<input type='hidden' name='ChidonSql' value='$sql4' />";
            $stmt4 = $this->db->query( $sql4 );
            $result = $stmt4->fetch( PDO::FETCH_OBJ );
            $this->schools[$method][$school]['chidonReg'] = $result->registered;
        }
    }
    
    public function getSchools() {
        return $this->schools;
    }
    
    public function setSchoolDetails( $id = null ) {
        $sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub 
                from users u 
                join classes c using (class_id) 
                join schools s on (u.school_id = s.school_id) 
                where u.user_registered > 0 
                and s.school_id = ?  
                order by c.class_grade, c.class_sub, u.last, u.first";
        $stmt = $this->db->prepare( $sql );
         
        if ( is_null( $id ) ) { 
            foreach ( $this->schools as $schools ) {
                foreach ( $schools as $id => $school ) {
                    $stmt->execute( array( $id ) );                
                    $rows = $stmt->fetchAll();
                    foreach ( $rows as $row ) {
                        $user = $row['first'] . " " . $row['last'];
                        $class = $row['class_grade'] . ( empty( $row['class_sub'] ) ? "" : "-" . $row['class_sub'] );
                        $this->schoolDetails[$id][$class][] = $user;
                    }
                }
            }
        } else {             
             $stmt->execute( array( $id ) );
             $rows = $stmt->fetchAll();
             foreach ( $rows as $row ) {             
                 $user = $row['first'] . " " . $row['last'];
                 $class = $row['class_grade'] . ( empty( $row['class_sub'] ) ? "" : "-" . $row['class_sub'] );
                 $this->schoolDetails[$id][$class][] = $user;
             }
        } 
    }
    
    public function getSchoolDetails() {
        return $this->schoolDetails;
    }
}
?>