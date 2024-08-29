<?php
require_once 'class.globalSettings.php';

class Hachayol {
    private $db;
    private $schools;
    private $schoolDetails;
    private $chidonYear;
    private $chidonNumbers;
    private $schoolExceptions;
    
    public function __construct() {
        require_once 'class.db.php';
        $this->db = DB::getInstance();
        $this->schools = array();
        $this->schoolDetails = array();
        $this->chidonYear = GlobalSettings::getRegistrationYear();
        $this->chidonNumbers = array();
        $this->schoolExceptions = [55, 66, 110, 112, 180, 256, 584, 585, 588, 612, 432, 713, 709, 427, 434, 690, 480];
    }
    
    public function setSchools( $id = null ) {
        //get list of schools with totals per school of registered students
        $sql = "SELECT s.school_id, s.school_name, s.hachayol_name, count( u.user_id ) AS total, s.shipping_address1, s.shipping_address2, 
                    s.shipping_city, s.shipping_state, s.shipping_country, s.shipping_postal, s.shipping_method, s.principal, 
                    s.shipping_requests, s.school_gender, s.shipping_first, s.shipping_last, s.chidon_posters_boys, s.chidon_posters_girls, 
                    s.res_address1, s.res_address2, s.res_city, s.res_state, s.res_postal, s.res_country 
                FROM schools s
                JOIN users u
                USING ( school_id )
                WHERE u.user_registered > 0 
                AND u.hachayol = 1 
                AND s.test_school = 0 ";
        if ( !is_null( $id ) ) $sql .= " AND s.school_id = " . $id; 
        $sql .= " AND s.school_id not in (" . implode(',', $this->schoolExceptions) . ")";
        $sql .= " GROUP BY s.school_id ORDER BY s.shipping_method, s.school_name ";
        //echo $sql; exit;
        
        foreach ( $this->db->query( $sql ) as $row ) {
            $school = $row['school_id']; 
            $method = $row['shipping_method'];
            $total = $row['total'];

            $this->schools[$method][$school]['total'] = $total;
            $this->schools[$method][$school]['principal'] = $row['principal'];
            $this->schools[$method][$school]['name'] = $row['hachayol_name'] ? $row['hachayol_name'] : $row['school_name'];
            $this->schools[$method][$school]['address'] = $row['shipping_address1'] .
                ($row['shipping_address2'] == "" ? "<br />" . $row['shipping_address2'] : "<br />") . 
                $row['shipping_city'] . ", " . $row['shipping_state'] . "<br />" . $row['shipping_postal'] . "<br />" . 
                $row['shipping_country'];
            $this->schools[$method][$school]['shipping_requests'] = $row['shipping_requests'];
            $this->schools[$method][$school]['type'] = $row['school_gender'] == 'M' ? 'boys' : ( $row['school_gender'] == 'F' ? 'girls' : 'mixed' );
            $this->schools[$method][$school]['shipping_name'] = $row['shipping_first'] . ' ' . $row['shipping_last'];
            $this->schools[$method][$school]['chidon_posters_boys'] = $row['chidon_posters_boys'];
            $this->schools[$method][$school]['chidon_posters_girls'] = $row['chidon_posters_girls'];
            $this->schools[$method][$school]['res_address'] = $row['res_address1'] .
                ($row['res_address2'] == "" ? "<br />" . $row['res_address2'] : "<br />") .
                $row['res_city'] . ", " . $row['res_state'] . "<br />" . $row['res_postal'] . "<br />" .
                $row['res_country'];
            
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
            
            // find out how many kids in school are registered for chayolei / chidon
            $sqlReg = "select count(*) as registered from users where user_registered > 0 and school_id = " . $school;
            $stmtReg = $this->db->query($sqlReg);
            $resReg = $stmtReg->fetch(PDO::FETCH_OBJ);
            $this->schools[$method][$school]['totalReg'] = $resReg->registered;

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
                and u.hachayol = 1   
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

    public function setChidonNumbers() {
        $sql = "
            SELECT s.school_id, s.school_name, count( u.user_id ) AS total 
            FROM users u 
            JOIN schools s ON s.school_id = u.school_id 
            JOIN classes c ON c.class_id = u.class_id 
            WHERE (
                (u.chayolei = 1 AND u.user_registered > 0) 
                or u.chidon = 1
            ) 
            AND c.class_grade in ('4','5','6','7','8') 
            AND s.test_school = 0
            GROUP BY s.school_name";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $this->chidonNumbers[$row['school_id']] = $row['total'];
        }
    }

    public function getChidonNumber( $school_id ) {
        return ( isset( $this->chidonNumbers[$school_id] ) ? $this->chidonNumbers[$school_id] : 0 );
    }
}
?>