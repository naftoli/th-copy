<?php
class Reports
{
    private $year;
    private $fields;
    private $aliases;
    private $checkAvg;
    private $avgs;
    
    public function __construct($year)
    {
        $this->year = $year;
        $this->checkAvg = false;
        $this->avgs = array();
        $this->aliases = array(
            'th_chidon'             =>  'tc',
            'th_chidon_chaps'       =>  'tcc',
            'th_chidon_sponsors'    =>  'tcs',
            'counselors'            =>  'c',
            'schools'               =>  's',
            'users'                 =>  'u',
            'admins'                =>  'a',
            'th_chidon_teams'       =>  'tct',
            'th_chidon_bunks'       =>  'tcb'
        );
        $this->fields = array(
            'chidon_id'     =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'th_chidon_id as chidon_id'
            ),
            'first_name'   =>  array(
                'table'     =>  'users',
                'column'    =>  'first as first_name',
            ),
            'last_name'   =>  array(
                'table'     =>  'users',
                'column'    =>  'last as last_name',
            ),
            'school'    =>  array(
                'table'     =>  'schools',
                'column'    =>  'school_name as school',
            ),
            'grade'     =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'grade'
            ),
            'book'      =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'book'
            ),
            'he_first_name'   =>  array(
                'table'     =>  'users',
                'column'    =>  'first_he as he_first_name',
            ),
            'he_last_name'   =>  array(
                'table'     =>  'users',
                'column'    =>  'last_he as he_last_name',
            ),
            'gender'    =>  array(
                'table'     =>  'users',
                'column'    =>  'gender',
            ),
            'accomodations' =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  array('host', 'host_address1', 'host_address2')
            ),
            'between_streets'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'between_streets'
            ),
            'allergies' =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'allergies'
            ),
            'sandwich'  =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'sandwich'
            ),
            'shoe_size' =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'shoe_size'
            ),
            'sweater_size'  =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'size as sweater_size'
            ),
            'walking'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  array('walk_day', 'walk_night')
            ),
            'winner_type'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  array('contestant', 'school_rep')
            ),
            'chap_name'     =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'name as chap_name',
            ),
            'chap_first_name'    =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'first_name as chap_first_name',
            ),
            'chap_last_name'    =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'last_name as chap_last_name',
            ),
            'chap_phone'    =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'phone as chap_phone',
            ),
            'chap_email'    =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'email as chap_email',
            ),
            'chap_sweater'  =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'sweater_size as chap_sweater',
            ),
            'chap_acc_name' =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'acc_name as chap_acc_name',
            ),
            'chap_acc_addr' =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'acc_address as chap_acc_addr',
            ),
            'chap_acc_phone'=>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'acc_phone as chap_acc_phone',
            ),
            'chap_vehicle'  =>  array(
                'table'     =>  'th_chidon_chaps',
                'column'    =>  'vehicle as chap_vehicle',
            ),
            'chap_school'   =>  array(
                'table'     =>  'schools',
                'column'    =>  'school_name as chap_school'
            ),
            'bunk'      =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'bunk'
            ),
            'team'      =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'team'
            ),
            'counselor_info'    =>  array(
                'table'     =>  'counselors',
                'column'    =>  array('counselor_name', 'counselor_gender', 'address', 'between_streets', 'number', 'email', 'sweater_size', 'grade', 'school')
            ),
            'medal'     =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  array('medal', 'medal_number')
            ),
            'plaque'    =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  array('plaque', 'plaque_number')
            ),
            'bus'       =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'bus_number as bus'
            ),
            'seat'      =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'seat_number as seat'
            ),
            'waiver'    =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'waiver'
            ),
            'parent_id'   =>  array(
                'table'     =>  'admins',
                'column'    =>  'admin_id as parent_id',
            ),
            'parent_name'   =>  array(
                'table'     =>  'admins',
                'column'    =>  array('first', 'last'),
            ),
            'parent_email'  =>  array(
                'table'     =>  'admins',
                'column'    =>  'admin_email as parent_email',
            ),
            'parent_number' =>  array(
                'table'     =>  'admins',
                'column'    =>  array('admin_phone_mobile', 'admin_phone_mobile2'),
            ),
            'parent_login'  =>  array(
                'table'     =>  'admins',
                'column'    =>  array('username', 'password'),
            ),
            'donations'     =>  array(
                'table'     =>  'th_chidon_sponsors',
                'column'    =>  'num_trips as donations',
            ),
            'test1a'        =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'test1a'
            ),
            'test1b'        =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'test1b'
            ),
            'test2a'        =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'test2a'
            ),                
            'test2b'        =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'test2b'
            ),
            'test3a'        =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'test3a'
            ),
            'test3b'        =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'test3b'
            ),
            'avg1'      =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  array('test1a', 'test2a', 'test3a')
            ),
            'avg2'      =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  array('test1b', 'test2b', 'test3b')
            ),
            'paid'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'paid'
            ),
            'date_paid'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'date_paid'
            ),
            'history'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'history'
            ),
            'dob'       =>  array(
                'table'     =>  'users',
                'column'    =>  'dob'
            ),
            'admin_city'      =>  array(
                'table'     =>  'admins',
                'column'    =>  'admin_city'
            ),
            'admin_state'     =>  array(
                'table'     =>  'admins',
                'column'    =>  'admin_state'
            ),
            'team'      =>  array(
                'table'     =>  'th_chidon_teams',
                'column'    =>  'team'
            ),
            'bunk_name'      =>  array(
                'table'     =>  'th_chidon_bunks',
                'column'    =>  'bunk_name'
            ),
            'bunk_counselor'     =>  array(
                'table'     =>  'th_chidon_bunks',
                'column'    =>  'counselor as bunk_counselor'
            ),
            'bunk_c_number'  =>  array(
                'table'     =>  'th_chidon_bunks',
                'column'    =>  'c_number as bunk_c_number'
            ),
            'test_table'    =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'test_table'
            ),
            'bowling_lane'  =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'bowling_lane'
            ),
            'dropoff_bus'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'dropoff_bus'
            ),
            'dropoff_seat'  =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'dropoff_seat'
            ),
            'coach_bus'     =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'coach_bus'
            ),
            'school_bus'    =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'school_bus'
            ),
            'double_decker' =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'double_decker'
            ),
            'seat_type'     =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'seat_type'
            ),
            'seat_number'   =>  array(
                'table'     =>  'th_chidon',
                'column'    =>  'seat_number'
            )
        );
    }
    
    public function setAvgs( $avgs ) {
        $this->avgs = $avgs;
        $this->checkAvg = true;
    }
    
    public function createSQL($data, $root = 'th_chidon', $gender = false, $limit = false, $chidonType = '')
    {
        if (!empty($data)) {
            // build tables / columns
            $tables = array();
            foreach ($data as $field) {
                if (isset($this->fields[$field])) {
                    $table = $this->fields[$field]['table'];
                    $tables[$table][] = $this->fields[$field]['column'];
                } 
            }
            //echo "<pre>"; print_r($tables); echo "</pre>";
            
            $sql = "select ";
            foreach ($tables as $table => $other) {
                foreach ($other as $columns) {
                    if (is_array($columns)) {
                        foreach ($columns as $column) {
                            $sql .= $this->aliases[$table] . "." . $column . ", ";
                        }
                    } else {
                        $sql .= $this->aliases[$table] . "." . $columns . ", ";
                    }
                }
            }
            $sql = substr($sql, 0, strlen($sql) - 2);
            $sql .= " from " . $root . " " . $this->aliases[$root];
            foreach ($tables as $table => $other) {
                if ($root == $table) continue;
                switch ($root) {
                    case 'th_chidon':
                        switch ($table) {
                            case 'users':
                                $sql .= " join users u on u.user_id = tc.user_id";
                                break;
                            case 'schools':
                                $sql .= " join schools s on s.school_id = tc.school_id";
                                break;
                            case 'admins':
                                $sql .= " join admins a on a.admin_id = tc.parent_id";
                                break;
                            case 'th_chidon_chaps':
                                $sql .= " left join th_chidon_chaps tcc on tcc.school_id = tc.school_id";
                                break;
                            case 'th_chidon_sponsors':
                                $sql .= " left join th_chidon_sponsors tcs on tcs.sponsor = tc.paid_by";
                                break;
                            case 'th_chidon_teams':
                                $sql .= " left join th_chidon_teams tct on tct.team_id = tc.team_id";
                                break;
                            case 'th_chidon_bunks':
                                $sql .= " left join th_chidon_bunks tcb on tcb.bunk_id = tc.bunk_id";
                                break;
                        }
                        break;
                    case 'th_chidon_chaps':
                        $sql .= " join schools s on s.school_id = tcc.school_id";
                        break;
                }
            }
            
            // if limiting to gender, ensure we have the join to users table
            if ($root == 'th_chidon' && $gender) {
                if (! array_key_exists( 'users', $tables )) {
                    $sql .= " join users u on u.user_id = tc.user_id";
                }
            }
            
            $sql .= " where " . $this->aliases[$root] . ".year = " . $this->year;
            
            if ($root == 'th_chidon_chaps' && $chidonType) {
                $sql .= " and tcc.chidon_type = '" . $chidonType . "'";
            }

            if ($gender) $sql .= " and u.gender = '" . $gender . "'";
            if ($limit) {
                switch ($limit) {
                    case "contestant":
                        $sql .= " and (tc.contestant = 1 || tc.school_rep = 1)";
                        break;
                    case "confirmed":
                        $sql .= " and tc.confirmed = 1";
                        break;
                    case "paid":
                        $sql .= " and tc.date_paid > 0";
                        break;
                    case "activated":
                        $sql .= " and tc.can_enroll = 1";
                        break;
                }
            }
            if ($this->checkAvg) {
                $numTests = $this->avgs['tests'];
                if ($numTests == 2) {
                    $sql .= " and (test1a + test2a) / 2 >= " . $this->avgs['low'];
                    $sql .= " and (test1a + test2a) / 2 <= " . $this->avgs['high'];
                } else if ($numTests == 3) {
                    $sql .= " and (test1a + test2a + test3a) / 3 >= " . $this->avgs['low'];
                    $sql .= " and (test1a + test2a + test3a) / 3 <= " . $this->avgs['high'];
                }
            }
            //echo $sql; exit;
            if (count($tables) > 1) {
                if ($root == 'th_chidon' && in_array('users', array_keys($tables))) {
                    $sql .= " group by tc.user_id";
                }
            }

            return $sql;
        } else {
            return false;
        }
    }
}